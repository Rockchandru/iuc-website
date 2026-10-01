<?php
/* ═══════════════════════════════════════════════════════════════
   IUC Edu — Homepage v3.0
   ═══════════════════════════════════════════════════════════════ */

require_once __DIR__ . '/includes/functions.php';

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/analytics.php';
require_once __DIR__ . '/includes/whatsapp-enquiry.php';

// ── Contact Form Handler ──────────────────────────────────
$formSuccess = !empty($_SESSION['contact_success']);
unset($_SESSION['contact_success']);
$formError   = '';
$isModalSubmit = $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modal_submit']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $formError = 'Security validation failed. Please refresh and try again.';
    } else {
        $name    = trim($_POST['name']    ?? '');
        $phone   = trim($_POST['phone']   ?? '');
        $email   = trim($_POST['email']   ?? '');
        $course  = trim($_POST['course']  ?? '');
        $message = trim($_POST['message'] ?? '');
        $captcha = trim($_POST['captcha'] ?? '');

        if (!$name || !$phone || !$email || !$course) {
            $formError = 'Please fill in all required fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $formError = 'Please enter a valid email address.';
        } elseif ($captcha != $_SESSION['captcha']) {
            $formError = 'Invalid security code. Please try again.';
            $_SESSION['captcha'] = rand(1000, 9999);
        } elseif (!isset($conn) || !$conn) {
            $formError = 'Enquiry service is temporarily unavailable. Please call or WhatsApp us.';
            error_log('Enquiry insert skipped: database unavailable.');
        } else {
            $cleanField = static function ($key, $max = 512) {
                $value = trim((string)($_POST[$key] ?? ''));
                return mb_substr($value, 0, $max);
            };
            $pageUrl = $cleanField('page_url');
            $landingPage = $cleanField('landing_page');
            $referrer = $cleanField('referrer');
            $visitorId = $cleanField('visitor_id', 36);
            $sessionId = $cleanField('session_id', 36);
            $utmSource = $cleanField('utm_source', 100);
            $utmMedium = $cleanField('utm_medium', 100);
            $utmCampaign = $cleanField('utm_campaign', 100);
            $utmContent = $cleanField('utm_content', 100);
            $utmTerm = $cleanField('utm_term', 255);
            if (!an_valid_uuid($visitorId)) $visitorId = null;
            if (!an_valid_uuid($sessionId)) $sessionId = null;

            $stmt = $conn->prepare("INSERT INTO enquiries
                (full_name, phone, email, course, message, page_url, landing_page, referrer,
                 visitor_id, session_id, utm_source, utm_medium, utm_campaign, utm_content, utm_term)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                $formError = 'We could not save your enquiry right now. Please call or WhatsApp us.';
                error_log('Enquiry prepare failed: ' . $conn->error);
            } else {
                $stmt->bind_param('sssssssssssssss', $name, $phone, $email, $course, $message, $pageUrl,
                    $landingPage, $referrer, $visitorId, $sessionId, $utmSource, $utmMedium,
                    $utmCampaign, $utmContent, $utmTerm);
                $saved = $stmt->execute();
                $enquiryId = $saved ? (int)$stmt->insert_id : 0;
                $insertError = $stmt->error;
                $stmt->close();

                if (!$saved) {
                    $formError = 'We could not save your enquiry right now. Please call or WhatsApp us.';
                    error_log('Enquiry insert failed: ' . $insertError);
                } else {
                    if (an_ensure_tables($conn)) {
                        /* A phone number is only known after the visitor submits it. */
                        if ($visitorId) {
                            $visitorPhone = $conn->prepare("UPDATE analytics_visitors SET phone = ? WHERE visitor_id = ?");
                            if ($visitorPhone) {
                                $visitorPhone->bind_param('ss', $phone, $visitorId);
                                if (!$visitorPhone->execute()) error_log('Visitor phone link failed: ' . $visitorPhone->error);
                                $visitorPhone->close();
                            }
                        }

                        $eventType = 'contact_form';
                        $eventLabel = 'Enquiry #' . $enquiryId . ' saved';
                        $eventTime = date('Y-m-d H:i:s');
                        $event = $conn->prepare("INSERT INTO analytics_events
                            (visitor_id, session_id, event_type, event_label, event_value, page_url, enquiry_id, created_at)
                            VALUES (?, ?, ?, ?, 1, ?, ?, ?)");
                        if ($event) {
                            $event->bind_param('sssssis', $visitorId, $sessionId, $eventType, $eventLabel, $pageUrl, $enquiryId, $eventTime);
                            if (!$event->execute()) error_log('Enquiry analytics event failed: ' . $event->error);
                            $event->close();
                        }
                    }

                    /* WhatsApp is secondary: the saved enquiry and analytics conversion above
                       remain successful even if configuration or provider delivery fails. */
                    try {
                        $whatsappOptIn = isset($_POST['whatsapp_opt_in']) && (string)$_POST['whatsapp_opt_in'] === '1';
                        $whatsappRequestKey = trim((string)($_POST['enquiry_request_key'] ?? ''));
                        $whatsappQueue = wa_enqueue_enquiry($conn, $enquiryId, $whatsappRequestKey, $whatsappOptIn);
                        if (!empty($whatsappQueue['queued']) && !empty($whatsappQueue['message_id'])
                            && ($whatsappQueue['status'] ?? '') === 'PENDING') {
                            wa_process_message($conn, (int)$whatsappQueue['message_id']);
                        }
                        if (empty($whatsappQueue['ok'])) {
                            error_log('WhatsApp enquiry queue failed for enquiry ID ' . $enquiryId . '.');
                        }
                    } catch (Throwable $whatsappError) {
                        error_log('WhatsApp enquiry processing failed for enquiry ID ' . $enquiryId . '.');
                    }

                    $safeMailName = str_replace(["\r", "\n"], ' ', $name);
                    $to = SITE_EMAIL;
                    $subject = "New Inquiry from $safeMailName – " . SITE_NAME;
                    $body = "Enquiry ID: $enquiryId\nName: $name\nPhone: $phone\nEmail: $email\nCourse: $course\nMessage: $message\nPage: $pageUrl\nLanding page: $landingPage\nUTM campaign: $utmCampaign";
                    $headers = "From: noreply@" . str_replace('www.', '', parse_url(SITE_URL, PHP_URL_HOST));
                    @mail($to, $subject, $body, $headers);

                    if (!$isModalSubmit) $_SESSION['contact_success'] = true;
                    $_SESSION['captcha'] = rand(1000, 9999);
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    if ($isModalSubmit) {
                        header('Content-Type: application/json; charset=UTF-8');
                        echo json_encode(['success' => true, 'message' => 'Thank you! We will contact you within 24 hours.',
                            'csrf_token' => $_SESSION['csrf_token'], 'captcha' => $_SESSION['captcha'],
                            'enquiry_request_key' => wa_new_request_key()]);
                        exit;
                    }
                    header('Location: ' . BASE_URL . '/#contact', true, 303);
                    exit;
                }
            }
        }
    }
}

if ($isModalSubmit) {
    http_response_code(422);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['success' => false, 'message' => $formError ?: 'We could not submit your enquiry. Please try again.', 'csrf_token' => $_SESSION['csrf_token'], 'captcha' => $_SESSION['captcha']]);
    exit;
}

$pageTitle = 'IUC Edu | Computer Courses & IT Training in Chennai';
$metaDesc = 'Explore career-focused computer courses and IT training in Chennai at IUC Edu, with practical classes, live projects, flexible batches and placement assistance.';
$ogTitle = $pageTitle;
$ogDesc = $metaDesc;
$ogImage = SITE_URL . '/assets/images/about-education.jpg';
$ogImageAlt = 'Students learning technology skills at IUC Edu in Chennai';
$canonical = '';
$structuredData = [
    [
        '@type' => 'FAQPage',
        '@id' => SITE_URL . '/#faq',
        'mainEntity' => array_map(static function ($faq) {
            return [
                '@type' => 'Question',
                'name' => $faq['q'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $faq['a'],
                ],
            ];
        }, $faqs),
    ],
];

require_once __DIR__ . '/includes/header.php';
?>

<?php require __DIR__ . '/includes/hero.php'; ?>
<?php require __DIR__ . '/includes/about.php'; ?>
<?php require __DIR__ . '/includes/courses.php'; ?>
<?php require __DIR__ . '/includes/seo-training.php'; ?>
<?php require __DIR__ . '/includes/ai-course.php'; ?>
<?php require __DIR__ . '/includes/why-choose.php'; ?>
<?php require __DIR__ . '/includes/admissions.php'; ?>
<?php require __DIR__ . '/includes/certificates.php'; ?>
<?php require __DIR__ . '/includes/batches.php'; ?>
<?php require __DIR__ . '/includes/technologies.php'; ?>
<?php require __DIR__ . '/includes/projects.php'; ?>
<?php require __DIR__ . '/includes/internship.php'; ?>
<?php require __DIR__ . '/includes/placement.php'; ?>
<?php require __DIR__ . '/includes/testimonials.php'; ?>
<?php require __DIR__ . '/includes/companies.php'; ?>
<?php require __DIR__ . '/includes/faq.php'; ?>
<?php require __DIR__ . '/includes/blog.php'; ?>
<?php require __DIR__ . '/includes/contact.php'; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
