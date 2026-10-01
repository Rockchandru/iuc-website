<?php
/* Official WhatsApp Business course-enquiry automation. */

if (!function_exists('wa_config')) {
    function wa_config() {
        static $config = null;
        if ($config !== null) return $config;

        $local = [];
        $localFile = __DIR__ . '/whatsapp-config.php';
        if (is_file($localFile)) {
            $loaded = require $localFile;
            if (is_array($loaded)) $local = $loaded;
        }
        $env = static function ($name, $fallback = '') {
            $value = getenv($name);
            return $value !== false && $value !== '' ? $value : $fallback;
        };
        $bool = static function ($value) {
            return in_array(strtolower(trim((string)$value)), ['1', 'true', 'yes', 'on'], true);
        };

        $config = [
            'enabled' => $bool($env('WHATSAPP_ENABLED', $local['enabled'] ?? false)),
            'provider' => strtolower(trim((string)$env('WHATSAPP_PROVIDER', $local['provider'] ?? 'meta_cloud'))),
            'api_base_url' => rtrim(trim((string)$env('WHATSAPP_API_URL', $local['api_base_url'] ?? 'https://graph.facebook.com')), '/'),
            'access_token' => trim((string)$env('WHATSAPP_ACCESS_TOKEN', $local['access_token'] ?? '')),
            'phone_number_id' => preg_replace('/\D+/', '', (string)$env('WHATSAPP_PHONE_NUMBER_ID', $local['phone_number_id'] ?? '')),
            'business_account_id' => preg_replace('/\D+/', '', (string)$env('WHATSAPP_BUSINESS_ACCOUNT_ID', $local['business_account_id'] ?? '')),
            'app_secret' => trim((string)$env('WHATSAPP_APP_SECRET', $local['app_secret'] ?? '')),
            'verify_token' => trim((string)$env('WHATSAPP_VERIFY_TOKEN', $local['verify_token'] ?? '')),
            'graph_version' => trim((string)$env('WHATSAPP_GRAPH_VERSION', $local['graph_version'] ?? '')),
            'template_name' => trim((string)$env('WHATSAPP_TEMPLATE_NAME', $local['template_name'] ?? 'iuc_course_enquiry_confirmation')),
            'template_language' => trim((string)$env('WHATSAPP_TEMPLATE_LANGUAGE', $local['template_language'] ?? 'en_US')),
            'max_attempts' => max(1, min(10, (int)$env('WHATSAPP_MAX_ATTEMPTS', $local['max_attempts'] ?? 5))),
            'timeout' => max(3, min(20, (int)$env('WHATSAPP_HTTP_TIMEOUT', $local['timeout'] ?? 8))),
            // Available only to PHP's local development server for isolated HTTP integration tests.
            'allow_local_test_endpoint' => PHP_SAPI === 'cli-server'
                && $bool($env('WHATSAPP_ALLOW_LOCAL_TEST_ENDPOINT', $local['allow_local_test_endpoint'] ?? false)),
        ];
        return $config;
    }

    function wa_new_request_key() {
        return bin2hex(random_bytes(16));
    }

    function wa_safe_text($value, $limit = 500) {
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/u', '', (string)$value);
        $value = preg_replace('/\s+/u', ' ', trim($value));
        return mb_substr($value, 0, max(1, (int)$limit));
    }

    function wa_normalize_phone($phone) {
        $raw = trim((string)$phone);
        $digits = preg_replace('/\D+/', '', $raw);
        if (strpos($digits, '00') === 0) $digits = substr($digits, 2);
        if (strlen($digits) === 10) $digits = '91' . $digits;
        if (strlen($digits) < 8 || strlen($digits) > 15 || $digits[0] === '0') return null;
        return '+' . $digits;
    }

    function wa_resolve_course($submittedCourse) {
        global $courses;
        $needle = mb_strtolower(trim((string)$submittedCourse));
        if ($needle === '' || $needle === 'other') return null;
        foreach (($courses ?? []) as $slug => $course) {
            $values = [$slug, $course['slug'] ?? '', $course['title'] ?? '', $course['short_title'] ?? ''];
            foreach ($values as $value) {
                if ($needle === mb_strtolower(trim((string)$value))) {
                    $course['slug'] = $slug;
                    return $course;
                }
            }
        }
        return null;
    }

    function wa_course_template_data($studentName, array $course) {
        $slug = (string)($course['slug'] ?? '');
        if ($slug === '' || empty($course['title']) || empty($course['duration']) || empty($course['price'])) return null;
        $highlights = array_values(array_filter(array_map(static function ($item) {
            return wa_safe_text($item, 90);
        }, array_slice((array)($course['highlights'] ?? []), 0, 5))));
        if (!$highlights && !empty($course['syllabus'])) {
            foreach (array_slice((array)$course['syllabus'], 0, 4) as $module) {
                $highlights[] = wa_safe_text(is_array($module) ? ($module[0] ?? '') : $module, 90);
            }
            $highlights = array_values(array_filter($highlights));
        }
        return [
            'student_name' => wa_safe_text($studentName, 100),
            'course_name' => wa_safe_text($course['title'], 150),
            'course_summary' => wa_safe_text($course['short_desc'] ?? $course['description'] ?? '', 350),
            'duration' => wa_safe_text($course['duration'], 80),
            'course_highlights' => wa_safe_text(implode('; ', $highlights), 500),
            'course_fee' => wa_safe_text($course['price'], 80),
            'course_url' => rtrim(defined('SITE_URL') ? SITE_URL : '', '/') . '/course/' . rawurlencode($slug),
            'contact_phone' => wa_normalize_phone(defined('SITE_PHONE') ? SITE_PHONE : '') ?: wa_safe_text(defined('SITE_PHONE') ? SITE_PHONE : '', 30),
            'contact_email' => wa_safe_text(defined('SITE_EMAIL') ? SITE_EMAIL : '', 150),
        ];
    }

    function wa_ensure_schema($conn, &$error = null) {
        static $ready = null;
        if ($ready !== null) return $ready;
        if (!$conn || !($conn instanceof mysqli)) {
            $error = 'Database connection is unavailable.';
            return $ready = false;
        }
        $sql = "CREATE TABLE IF NOT EXISTS whatsapp_enquiry_messages (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            enquiry_id INT UNSIGNED NOT NULL,
            request_key VARCHAR(64) NOT NULL,
            course_slug VARCHAR(100) DEFAULT NULL,
            phone_e164 VARCHAR(20) DEFAULT NULL,
            template_name VARCHAR(128) DEFAULT NULL,
            template_language VARCHAR(16) DEFAULT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'PENDING',
            delivery_status VARCHAR(20) DEFAULT NULL,
            provider_message_id VARCHAR(191) DEFAULT NULL,
            attempt_count INT UNSIGNED NOT NULL DEFAULT 0,
            is_retryable TINYINT(1) NOT NULL DEFAULT 1,
            next_attempt_at DATETIME DEFAULT NULL,
            error_code VARCHAR(80) DEFAULT NULL,
            error_message VARCHAR(500) DEFAULT NULL,
            opted_in_at DATETIME DEFAULT NULL,
            created_at DATETIME NOT NULL,
            sent_at DATETIME DEFAULT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uk_whatsapp_enquiry (enquiry_id),
            UNIQUE KEY uk_whatsapp_request (request_key),
            UNIQUE KEY uk_whatsapp_provider_message (provider_message_id),
            KEY idx_whatsapp_retry (status, is_retryable, next_attempt_at),
            KEY idx_whatsapp_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        if (!$conn->query($sql)) {
            $error = wa_safe_text($conn->error, 500);
            error_log('WhatsApp schema unavailable: ' . $error);
            return $ready = false;
        }
        return $ready = true;
    }

    function wa_get_message($conn, $messageId) {
        $stmt = $conn->prepare("SELECT w.*, e.full_name, e.phone, e.course
            FROM whatsapp_enquiry_messages w
            JOIN enquiries e ON e.id = w.enquiry_id
            WHERE w.id = ? LIMIT 1");
        if (!$stmt) return null;
        $messageId = (int)$messageId;
        $stmt->bind_param('i', $messageId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    function wa_enqueue_enquiry($conn, $enquiryId, $requestKey, $optedIn) {
        if (!$optedIn) return ['ok' => 1, 'queued' => false, 'status' => 'SKIPPED', 'reason' => 'no_opt_in'];
        $error = null;
        if (!wa_ensure_schema($conn, $error)) return ['ok' => 0, 'queued' => false, 'error' => $error];

        $enquiryId = max(0, (int)$enquiryId);
        if ($enquiryId < 1) return ['ok' => 0, 'queued' => false, 'error' => 'invalid_enquiry_id'];
        $requestKey = strtolower(trim((string)$requestKey));
        if (!preg_match('/^[a-f0-9-]{16,64}$/', $requestKey)) $requestKey = hash('sha256', 'enquiry:' . $enquiryId);

        $stmt = $conn->prepare("SELECT full_name, phone, course FROM enquiries WHERE id = ? LIMIT 1");
        if (!$stmt) return ['ok' => 0, 'queued' => false, 'error' => 'enquiry_lookup_failed'];
        $stmt->bind_param('i', $enquiryId);
        $stmt->execute();
        $enquiry = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$enquiry) return ['ok' => 0, 'queued' => false, 'error' => 'enquiry_not_found'];

        $course = wa_resolve_course($enquiry['course']);
        $phone = wa_normalize_phone($enquiry['phone']);
        $status = 'PENDING';
        $retryable = 1;
        $errorCode = null;
        $errorMessage = null;
        if (!$course) {
            $status = 'FAILED'; $retryable = 0; $errorCode = 'COURSE_NOT_FOUND';
            $errorMessage = 'The submitted course could not be matched to the course catalogue.';
        } elseif (empty($course['price'])) {
            $status = 'FAILED'; $retryable = 0; $errorCode = 'COURSE_FEE_MISSING';
            $errorMessage = 'The selected course does not have a configured fee.';
        } elseif (!$phone) {
            $status = 'FAILED'; $retryable = 0; $errorCode = 'INVALID_PHONE';
            $errorMessage = 'The submitted phone number is not valid for WhatsApp delivery.';
        }
        $courseSlug = $course['slug'] ?? null;
        $config = wa_config();
        $template = $config['template_name'];
        $language = $config['template_language'];
        $now = date('Y-m-d H:i:s');
        $nextAttempt = $status === 'PENDING' ? $now : null;

        $insert = $conn->prepare("INSERT IGNORE INTO whatsapp_enquiry_messages
            (enquiry_id, request_key, course_slug, phone_e164, template_name, template_language,
             status, is_retryable, next_attempt_at, error_code, error_message, opted_in_at, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$insert) return ['ok' => 0, 'queued' => false, 'error' => 'outbox_prepare_failed'];
        $insert->bind_param('issssssisssss', $enquiryId, $requestKey, $courseSlug, $phone, $template, $language,
            $status, $retryable, $nextAttempt, $errorCode, $errorMessage, $now, $now);
        $saved = $insert->execute();
        $insertError = $insert->error;
        $insert->close();
        if (!$saved) {
            error_log('WhatsApp outbox insert failed: ' . wa_safe_text($insertError, 300));
            return ['ok' => 0, 'queued' => false, 'error' => 'outbox_insert_failed'];
        }

        $find = $conn->prepare("SELECT id, status FROM whatsapp_enquiry_messages WHERE enquiry_id = ? OR request_key = ? ORDER BY id LIMIT 1");
        if (!$find) return ['ok' => 0, 'queued' => false, 'error' => 'outbox_lookup_failed'];
        $find->bind_param('is', $enquiryId, $requestKey);
        $find->execute();
        $row = $find->get_result()->fetch_assoc();
        $find->close();
        return ['ok' => 1, 'queued' => true, 'message_id' => (int)($row['id'] ?? 0), 'status' => $row['status'] ?? $status];
    }

    function wa_provider_ready(array $config, &$errorCode = null, &$errorMessage = null) {
        if (!$config['enabled']) {
            $errorCode = 'PROVIDER_DISABLED'; $errorMessage = 'WhatsApp delivery is not enabled.'; return false;
        }
        if ($config['provider'] !== 'meta_cloud') {
            $errorCode = 'PROVIDER_UNSUPPORTED'; $errorMessage = 'The configured WhatsApp provider is not supported.'; return false;
        }
        if ($config['access_token'] === '' || $config['phone_number_id'] === '' || $config['template_name'] === '') {
            $errorCode = 'PROVIDER_CONFIG_MISSING'; $errorMessage = 'Required WhatsApp provider configuration is missing.'; return false;
        }
        if (!preg_match('/^v\d+\.\d+$/', $config['graph_version'])) {
            $errorCode = 'GRAPH_VERSION_MISSING'; $errorMessage = 'A supported Meta Graph API version must be configured.'; return false;
        }
        return true;
    }

    function wa_meta_template_payload($phoneE164, array $data, array $config) {
        $order = ['student_name', 'course_name', 'course_summary', 'duration', 'course_highlights', 'course_fee', 'course_url', 'contact_phone', 'contact_email'];
        $parameters = [];
        foreach ($order as $key) $parameters[] = ['type' => 'text', 'text' => wa_safe_text($data[$key] ?? '', 900)];
        return [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => ltrim((string)$phoneE164, '+'),
            'type' => 'template',
            'template' => [
                'name' => $config['template_name'],
                'language' => ['policy' => 'deterministic', 'code' => $config['template_language']],
                'components' => [['type' => 'body', 'parameters' => $parameters]],
            ],
        ];
    }

    function wa_send_meta_template($phoneE164, array $data, array $config) {
        $errorCode = null; $errorMessage = null;
        if (!wa_provider_ready($config, $errorCode, $errorMessage)) {
            return ['ok' => 0, 'error_code' => $errorCode, 'error_message' => $errorMessage, 'retryable' => true];
        }
        if (!function_exists('curl_init')) {
            return ['ok' => 0, 'error_code' => 'CURL_MISSING', 'error_message' => 'PHP cURL is unavailable.', 'retryable' => true];
        }
        $apiBase = rtrim((string)($config['api_base_url'] ?? 'https://graph.facebook.com'), '/');
        $isOfficialEndpoint = preg_match('~^https://graph\.facebook\.com$~i', $apiBase) === 1;
        $isLocalTestEndpoint = !empty($config['allow_local_test_endpoint'])
            && preg_match('~^http://(?:127\.0\.0\.1|localhost)(?::\d+)?$~i', $apiBase) === 1;
        if (!$isOfficialEndpoint && !$isLocalTestEndpoint) {
            return ['ok' => 0, 'error_code' => 'API_URL_INVALID',
                'error_message' => 'The WhatsApp API endpoint is not permitted.', 'retryable' => false];
        }
        $url = $apiBase . '/' . rawurlencode($config['graph_version']) . '/'
            . rawurlencode($config['phone_number_id']) . '/messages';
        $payload = json_encode(wa_meta_template_payload($phoneE164, $data, $config), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $config['access_token'], 'Content-Type: application/json'],
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_CONNECTTIMEOUT => min(5, $config['timeout']),
            CURLOPT_TIMEOUT => $config['timeout'],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        $raw = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $networkError = $raw === false ? curl_error($ch) : '';
        curl_close($ch);
        if ($raw === false) {
            return ['ok' => 0, 'error_code' => 'NETWORK_ERROR', 'error_message' => wa_safe_text($networkError, 400), 'retryable' => true];
        }
        $response = json_decode($raw, true);
        if ($status < 200 || $status >= 300 || !is_array($response)) {
            $metaError = is_array($response) ? ($response['error'] ?? []) : [];
            $code = (string)($metaError['code'] ?? ('HTTP_' . $status));
            $message = wa_safe_text($metaError['message'] ?? 'WhatsApp provider rejected the request.', 400);
            $permanentCodes = ['100', '131008', '132001', '132012'];
            return ['ok' => 0, 'error_code' => $code, 'error_message' => $message, 'retryable' => !in_array($code, $permanentCodes, true)];
        }
        $providerId = (string)($response['messages'][0]['id'] ?? '');
        if ($providerId === '') return ['ok' => 0, 'error_code' => 'MESSAGE_ID_MISSING', 'error_message' => 'Provider did not return a message ID.', 'retryable' => true];
        return ['ok' => 1, 'provider_message_id' => wa_safe_text($providerId, 191)];
    }

    function wa_fail_message($conn, $messageId, $attemptCount, $errorCode, $errorMessage, $retryable) {
        $config = wa_config();
        $retryable = $retryable && $attemptCount < $config['max_attempts'];
        $delayMinutes = min(1440, 5 * (2 ** max(0, $attemptCount - 1)));
        $nextAttempt = $retryable ? date('Y-m-d H:i:s', time() + ($delayMinutes * 60)) : null;
        $stmt = $conn->prepare("UPDATE whatsapp_enquiry_messages SET status = 'FAILED', is_retryable = ?,
            next_attempt_at = ?, error_code = ?, error_message = ? WHERE id = ?");
        if ($stmt) {
            $retryInt = $retryable ? 1 : 0;
            $errorCode = wa_safe_text($errorCode, 80);
            $errorMessage = wa_safe_text($errorMessage, 500);
            $stmt->bind_param('isssi', $retryInt, $nextAttempt, $errorCode, $errorMessage, $messageId);
            $stmt->execute();
            $stmt->close();
        }
        return ['ok' => 0, 'status' => 'FAILED', 'error_code' => $errorCode, 'retryable' => $retryable];
    }

    function wa_process_message($conn, $messageId) {
        $error = null;
        if (!wa_ensure_schema($conn, $error)) return ['ok' => 0, 'status' => 'FAILED', 'error_code' => 'SCHEMA_UNAVAILABLE'];
        $messageId = max(0, (int)$messageId);
        if ($messageId < 1) return ['ok' => 0, 'status' => 'FAILED', 'error_code' => 'INVALID_MESSAGE_ID'];

        $claim = $conn->prepare("UPDATE whatsapp_enquiry_messages
            SET status = 'PROCESSING', attempt_count = attempt_count + 1, error_code = NULL, error_message = NULL
            WHERE id = ? AND is_retryable = 1 AND status IN ('PENDING','FAILED')
              AND (next_attempt_at IS NULL OR next_attempt_at <= NOW())");
        if (!$claim) return ['ok' => 0, 'status' => 'FAILED', 'error_code' => 'CLAIM_PREPARE_FAILED'];
        $claim->bind_param('i', $messageId);
        $claim->execute();
        $claimed = $claim->affected_rows === 1;
        $claim->close();
        $message = wa_get_message($conn, $messageId);
        if (!$claimed) {
            return ['ok' => !empty($message) && $message['status'] === 'SENT', 'status' => $message['status'] ?? 'NOT_FOUND', 'duplicate_prevented' => true];
        }

        $attemptCount = (int)($message['attempt_count'] ?? 1);
        $course = wa_resolve_course($message['course_slug'] ?: $message['course']);
        $data = $course ? wa_course_template_data($message['full_name'], $course) : null;
        if (!$course) return wa_fail_message($conn, $messageId, $attemptCount, 'COURSE_NOT_FOUND', 'Course information is unavailable.', false);
        if (!$data) return wa_fail_message($conn, $messageId, $attemptCount, 'COURSE_DATA_MISSING', 'Required course information or fee is unavailable.', false);
        $phone = wa_normalize_phone($message['phone_e164'] ?: $message['phone']);
        if (!$phone) return wa_fail_message($conn, $messageId, $attemptCount, 'INVALID_PHONE', 'Phone number is invalid for WhatsApp delivery.', false);

        $result = wa_send_meta_template($phone, $data, wa_config());
        if (empty($result['ok'])) {
            return wa_fail_message($conn, $messageId, $attemptCount, $result['error_code'] ?? 'PROVIDER_ERROR',
                $result['error_message'] ?? 'WhatsApp provider request failed.', !empty($result['retryable']));
        }
        $providerId = $result['provider_message_id'];
        $sentAt = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("UPDATE whatsapp_enquiry_messages SET status = 'SENT', delivery_status = 'accepted',
            provider_message_id = ?, is_retryable = 0, next_attempt_at = NULL, error_code = NULL,
            error_message = NULL, sent_at = ? WHERE id = ?");
        if (!$stmt) return ['ok' => 0, 'status' => 'FAILED', 'error_code' => 'STATUS_UPDATE_FAILED'];
        $stmt->bind_param('ssi', $providerId, $sentAt, $messageId);
        $saved = $stmt->execute();
        $stmt->close();
        return ['ok' => (bool)$saved, 'status' => $saved ? 'SENT' : 'FAILED', 'provider_message_id' => $providerId];
    }

    function wa_process_pending($conn, $limit = 20) {
        $error = null;
        if (!wa_ensure_schema($conn, $error)) return ['processed' => 0, 'sent' => 0, 'failed' => 0, 'error' => $error];
        $conn->query("UPDATE whatsapp_enquiry_messages SET status = 'FAILED', is_retryable = 1,
            next_attempt_at = NOW(), error_code = 'STALE_PROCESS', error_message = 'A previous send attempt did not finish.'
            WHERE status = 'PROCESSING' AND updated_at < DATE_SUB(NOW(), INTERVAL 10 MINUTE)");
        $limit = max(1, min(100, (int)$limit));
        $result = $conn->query("SELECT id FROM whatsapp_enquiry_messages
            WHERE is_retryable = 1 AND status IN ('PENDING','FAILED')
              AND (next_attempt_at IS NULL OR next_attempt_at <= NOW())
            ORDER BY created_at ASC LIMIT " . $limit);
        $ids = [];
        if ($result) while ($row = $result->fetch_assoc()) $ids[] = (int)$row['id'];
        $summary = ['processed' => 0, 'sent' => 0, 'failed' => 0];
        foreach ($ids as $id) {
            $outcome = wa_process_message($conn, $id);
            $summary['processed']++;
            if (($outcome['status'] ?? '') === 'SENT') $summary['sent']++;
            else $summary['failed']++;
        }
        return $summary;
    }
}
