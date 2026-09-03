<?php
/* Google Search Console Search Analytics client. */

if (!function_exists('gsc_config')) {
    function gsc_config() {
        static $config = null;
        if ($config !== null) return $config;

        $local = [];
        $localFile = __DIR__ . '/search-console-config.php';
        if (is_file($localFile)) {
            $loaded = require $localFile;
            if (is_array($loaded)) $local = $loaded;
        }

        $siteUrl = defined('SITE_URL') ? rtrim((string)SITE_URL, '/') . '/' : '';
        $host = $siteUrl !== '' ? (string)parse_url($siteUrl, PHP_URL_HOST) : '';
        $host = preg_replace('/^www\./i', '', $host);
        $env = static function ($name, $fallback = '') {
            $value = getenv($name);
            return $value !== false && $value !== '' ? $value : $fallback;
        };
        $sessionProperty = isset($_SESSION['gsc_session_property']) ? (string)$_SESSION['gsc_session_property'] : '';
        $sessionCredentials = isset($_SESSION['gsc_session_credentials']) ? (string)$_SESSION['gsc_session_credentials'] : '';
        $envCredentialsFile = getenv('GSC_CREDENTIALS_FILE');
        $envCredentialsJson = getenv('GSC_CREDENTIALS_JSON');
        $brandTerms = $local['brand_terms'] ?? ['iuc', 'iuc edu', 'iuc computers', 'iuc computer education'];
        $brandEnv = $env('GSC_BRAND_TERMS');
        if ($brandEnv !== '') $brandTerms = array_map('trim', explode(',', $brandEnv));
        if (!is_array($brandTerms)) $brandTerms = [];

        $config = [
            'property' => trim((string)$env('GSC_PROPERTY', $local['property'] ?? ($sessionProperty ?: $siteUrl))),
            'credentials_file' => trim((string)$env('GSC_CREDENTIALS_FILE', $local['credentials_file'] ?? '')),
            'credentials_json' => (string)$env('GSC_CREDENTIALS_JSON', $local['credentials_json'] ?? $sessionCredentials),
            'cache_ttl' => max(60, min(3600, (int)$env('GSC_CACHE_TTL', $local['cache_ttl'] ?? 600))),
            'row_limit' => max(100, min(25000, (int)$env('GSC_ROW_LIMIT', $local['row_limit'] ?? 5000))),
            'brand_terms' => array_values(array_unique(array_filter(array_map(static function ($term) {
                return mb_strtolower(trim((string)$term));
            }, $brandTerms)))),
            'credential_source' => (($envCredentialsFile !== false && $envCredentialsFile !== '') || ($envCredentialsJson !== false && $envCredentialsJson !== ''))
                ? 'environment'
                : (!empty($local['credentials_file']) || !empty($local['credentials_json']) ? 'config' : ($sessionCredentials !== '' ? 'session' : 'none')),
        ];
        return $config;
    }

    function gsc_setup_status() {
        $config = gsc_config();
        $hasCredentials = trim($config['credentials_json']) !== ''
            || ($config['credentials_file'] !== '' && is_readable($config['credentials_file']));
        return [
            'configured' => $config['property'] !== '' && $hasCredentials,
            'property' => $config['property'],
            'credentials_available' => $hasCredentials,
            'credential_source' => $config['credential_source'],
            'setup' => [
                'Enable the Google Search Console API in Google Cloud.',
                'Create a service account and download its JSON key.',
                'Add the service-account email as a user on the Search Console property.',
                'Upload the JSON below for this admin session, or configure GSC_CREDENTIALS_FILE for a persistent server connection.',
            ],
        ];
    }

    function gsc_base64url($value) {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    function gsc_credentials(&$error = null) {
        $config = gsc_config();
        $raw = trim($config['credentials_json']);
        if ($raw === '' && $config['credentials_file'] !== '') {
            if (!is_readable($config['credentials_file'])) {
                $error = 'The configured Search Console credentials file is not readable.';
                return null;
            }
            $raw = (string)file_get_contents($config['credentials_file']);
        }
        if ($raw === '') {
            $error = 'Google Search Console credentials are not configured.';
            return null;
        }
        $credentials = json_decode($raw, true);
        if (!is_array($credentials) || empty($credentials['client_email']) || empty($credentials['private_key'])) {
            $error = 'The Search Console service-account JSON is invalid.';
            return null;
        }
        return $credentials;
    }

    function gsc_http_json($url, $method, array $headers, $body, &$error = null) {
        if (!function_exists('curl_init')) {
            $error = 'PHP cURL is required for Search Console.';
            return null;
        }
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        if ($body !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        $raw = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($raw === false) {
            $error = 'Search Console network error: ' . curl_error($ch);
            curl_close($ch);
            return null;
        }
        curl_close($ch);
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            $error = 'Search Console returned an invalid response.';
            return null;
        }
        if ($status < 200 || $status >= 300) {
            $error = 'Search Console API: ' . ($decoded['error']['message'] ?? ('HTTP ' . $status));
            return null;
        }
        return $decoded;
    }

    function gsc_access_token(&$error = null) {
        if (!empty($_SESSION['gsc_access_token']['token'])
            && (int)($_SESSION['gsc_access_token']['expires_at'] ?? 0) > time() + 90) {
            return $_SESSION['gsc_access_token']['token'];
        }
        $credentials = gsc_credentials($error);
        if (!$credentials) return null;

        $tokenUri = $credentials['token_uri'] ?? 'https://oauth2.googleapis.com/token';
        $now = time();
        $header = gsc_base64url(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $claims = gsc_base64url(json_encode([
            'iss' => $credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/webmasters.readonly',
            'aud' => $tokenUri,
            'iat' => $now - 30,
            'exp' => $now + 3600,
        ], JSON_UNESCAPED_SLASHES));
        $unsigned = $header . '.' . $claims;
        $privateKey = openssl_pkey_get_private($credentials['private_key']);
        $signature = '';
        if (!$privateKey || !openssl_sign($unsigned, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            $error = 'The Search Console OAuth assertion could not be signed.';
            return null;
        }
        $assertion = $unsigned . '.' . gsc_base64url($signature);
        $response = gsc_http_json(
            $tokenUri,
            'POST',
            ['Content-Type: application/x-www-form-urlencoded'],
            http_build_query([
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $assertion,
            ], '', '&', PHP_QUERY_RFC3986),
            $error
        );
        if (!$response || empty($response['access_token'])) {
            if (!$error) $error = 'Google OAuth did not return an access token.';
            return null;
        }
        $_SESSION['gsc_access_token'] = [
            'token' => $response['access_token'],
            'expires_at' => $now + max(300, (int)($response['expires_in'] ?? 3600)),
        ];
        return $response['access_token'];
    }

    function gsc_query_rows($token, $startDate, $endDate, array $dimensions, $rowLimit, &$error = null) {
        $config = gsc_config();
        if ($config['property'] === '') {
            $error = 'The Search Console property is not configured.';
            return null;
        }
        $url = 'https://www.googleapis.com/webmasters/v3/sites/'
            . rawurlencode($config['property']) . '/searchAnalytics/query';
        $response = gsc_http_json(
            $url,
            'POST',
            ['Authorization: Bearer ' . $token, 'Content-Type: application/json'],
            json_encode([
                'startDate' => $startDate,
                'endDate' => $endDate,
                'dimensions' => array_values($dimensions),
                'type' => 'web',
                'aggregationType' => 'auto',
                'dataState' => 'all',
                'rowLimit' => max(1, min(25000, (int)$rowLimit)),
                'startRow' => 0,
            ], JSON_UNESCAPED_SLASHES),
            $error
        );
        if (!$response) return null;

        $rows = [];
        foreach (($response['rows'] ?? []) as $row) {
            $item = [
                'clicks' => (float)($row['clicks'] ?? 0),
                'impressions' => (float)($row['impressions'] ?? 0),
                'ctr' => (float)($row['ctr'] ?? 0),
                'position' => (float)($row['position'] ?? 0),
            ];
            foreach ($dimensions as $index => $dimension) {
                $item[$dimension] = (string)($row['keys'][$index] ?? '');
            }
            $rows[] = $item;
        }
        return $rows;
    }

    function gsc_summary(array $rows) {
        $clicks = 0.0;
        $impressions = 0.0;
        $weightedPosition = 0.0;
        foreach ($rows as $row) {
            $rowImpressions = (float)$row['impressions'];
            $clicks += (float)$row['clicks'];
            $impressions += $rowImpressions;
            $weightedPosition += (float)$row['position'] * $rowImpressions;
        }
        return [
            'clicks' => round($clicks),
            'impressions' => round($impressions),
            'ctr' => $impressions > 0 ? $clicks / $impressions : 0,
            'position' => $impressions > 0 ? $weightedPosition / $impressions : 0,
        ];
    }

    function gsc_change($current, $previous, $position = false) {
        $absolute = $position ? ((float)$previous - (float)$current) : ((float)$current - (float)$previous);
        return [
            'absolute' => $absolute,
            'percent' => (float)$previous != 0.0 ? $absolute / abs((float)$previous) * 100 : null,
        ];
    }

    function gsc_is_brand_query($query, array $brandTerms) {
        $query = mb_strtolower(trim((string)$query));
        foreach ($brandTerms as $term) {
            if ($term !== '' && mb_strpos($query, $term) !== false) return true;
        }
        return false;
    }

    function gsc_performance($from, $to, $forceRefresh = false) {
        $setup = gsc_setup_status();
        if (!$setup['configured']) {
            return array_merge(['ok' => 0, 'error' => 'Google Search Console is not connected.'], $setup);
        }

        $fromDate = DateTime::createFromFormat('!Y-m-d', (string)$from);
        $toDate = DateTime::createFromFormat('!Y-m-d', (string)$to);
        if (!$fromDate || !$toDate || $fromDate > $toDate) {
            return ['ok' => 0, 'configured' => true, 'error' => 'Invalid Search Console date range.'];
        }
        $config = gsc_config();
        $cacheKey = hash('sha256', $config['property'] . '|' . $from . '|' . $to . '|' . $config['row_limit']);
        if (!$forceRefresh && !empty($_SESSION['gsc_performance_cache'][$cacheKey])) {
            $cached = $_SESSION['gsc_performance_cache'][$cacheKey];
            if ((int)($cached['expires_at'] ?? 0) > time() && !empty($cached['data'])) {
                $cached['data']['cache']['hit'] = true;
                return $cached['data'];
            }
        }

        $days = (int)$fromDate->diff($toDate)->days + 1;
        $previousEnd = clone $fromDate;
        $previousEnd->modify('-1 day');
        $previousStart = clone $previousEnd;
        $previousStart->modify('-' . max(0, $days - 1) . ' days');
        $previousFrom = $previousStart->format('Y-m-d');
        $previousTo = $previousEnd->format('Y-m-d');

        $error = null;
        $token = gsc_access_token($error);
        if (!$token) return array_merge(['ok' => 0, 'error' => $error ?: 'Search Console authentication failed.'], $setup);

        $limit = $config['row_limit'];
        $requests = [
            'daily' => [$from, $to, ['date'], min(5000, $limit)],
            'previous_daily' => [$previousFrom, $previousTo, ['date'], min(5000, $limit)],
            'queries' => [$from, $to, ['query'], $limit],
            'previous_queries' => [$previousFrom, $previousTo, ['query'], $limit],
            'query_pages' => [$from, $to, ['query', 'page'], $limit],
            'pages' => [$from, $to, ['page'], $limit],
            'devices' => [$from, $to, ['device'], 20],
            'countries' => [$from, $to, ['country'], 250],
        ];
        $data = [];
        foreach ($requests as $name => $args) {
            $data[$name] = gsc_query_rows($token, $args[0], $args[1], $args[2], $args[3], $error);
            if ($data[$name] === null) {
                return array_merge([
                    'ok' => 0,
                    'configured' => true,
                    'property' => $config['property'],
                    'error' => $error ?: 'Search Console data could not be loaded.',
                    'failed_dataset' => $name,
                ], $setup);
            }
        }

        $warnings = [];
        $appearanceError = null;
        $appearances = gsc_query_rows($token, $from, $to, ['searchAppearance'], 100, $appearanceError);
        if ($appearances === null) {
            $appearances = [];
            $warnings[] = $appearanceError ?: 'Search appearance data is unavailable.';
        }

        $summary = gsc_summary($data['daily']);
        $previousSummary = gsc_summary($data['previous_daily']);
        $previousQueries = [];
        foreach ($data['previous_queries'] as $row) $previousQueries[$row['query']] = $row;
        $topPages = [];
        foreach ($data['query_pages'] as $row) {
            $query = $row['query'];
            if (!isset($topPages[$query]) || $row['clicks'] > $topPages[$query]['clicks']
                || ($row['clicks'] == $topPages[$query]['clicks'] && $row['impressions'] > $topPages[$query]['impressions'])) {
                $topPages[$query] = $row;
            }
        }

        $queries = [];
        $brand = ['clicks' => 0, 'impressions' => 0];
        $nonBrand = ['clicks' => 0, 'impressions' => 0];
        foreach ($data['queries'] as $row) {
            $previous = $previousQueries[$row['query']] ?? ['clicks' => 0, 'impressions' => 0, 'ctr' => 0, 'position' => 0];
            $row['page'] = $topPages[$row['query']]['page'] ?? '';
            $row['is_brand'] = gsc_is_brand_query($row['query'], $config['brand_terms']);
            $row['changes'] = [
                'clicks' => gsc_change($row['clicks'], $previous['clicks']),
                'impressions' => gsc_change($row['impressions'], $previous['impressions']),
                'ctr' => gsc_change($row['ctr'], $previous['ctr']),
                'position' => gsc_change($row['position'], $previous['position'], true),
            ];
            if ($row['is_brand']) {
                $brand['clicks'] += $row['clicks'];
                $brand['impressions'] += $row['impressions'];
            } else {
                $nonBrand['clicks'] += $row['clicks'];
                $nonBrand['impressions'] += $row['impressions'];
            }
            $queries[] = $row;
        }

        $opportunities = array_values(array_filter($queries, static function ($row) {
            return $row['impressions'] >= 10 && $row['position'] >= 4 && $row['position'] <= 20 && $row['ctr'] < 0.08;
        }));
        usort($opportunities, static function ($a, $b) {
            $aScore = $a['impressions'] * (1 - min(1, $a['ctr'])) / max(1, $a['position']);
            $bScore = $b['impressions'] * (1 - min(1, $b['ctr'])) / max(1, $b['position']);
            return $bScore <=> $aScore;
        });

        $payload = [
            'ok' => 1,
            'configured' => true,
            'property' => $config['property'],
            'credential_source' => $config['credential_source'],
            'range' => ['from' => $from, 'to' => $to],
            'comparison_range' => ['from' => $previousFrom, 'to' => $previousTo],
            'summary' => $summary,
            'previous_summary' => $previousSummary,
            'changes' => [
                'clicks' => gsc_change($summary['clicks'], $previousSummary['clicks']),
                'impressions' => gsc_change($summary['impressions'], $previousSummary['impressions']),
                'ctr' => gsc_change($summary['ctr'], $previousSummary['ctr']),
                'position' => gsc_change($summary['position'], $previousSummary['position'], true),
            ],
            'daily' => $data['daily'],
            'queries' => $queries,
            'opportunities' => array_slice($opportunities, 0, 50),
            'pages' => $data['pages'],
            'devices' => $data['devices'],
            'countries' => $data['countries'],
            'appearances' => $appearances,
            'brand' => ['brand' => $brand, 'non_brand' => $nonBrand, 'terms' => $config['brand_terms']],
            'warnings' => $warnings,
            'row_limit' => $limit,
            'cache' => ['hit' => false, 'ttl' => $config['cache_ttl'], 'generated_at' => date('c')],
            'notes' => [
                'Search Console reports aggregated data and cannot identify an individual searcher.',
                'Google can omit anonymized queries and the API returns top rows rather than a guaranteed exhaustive export.',
                'Recent data can be preliminary because dataState is all.',
            ],
        ];

        if (empty($_SESSION['gsc_performance_cache']) || !is_array($_SESSION['gsc_performance_cache'])) {
            $_SESSION['gsc_performance_cache'] = [];
        }
        $_SESSION['gsc_performance_cache'][$cacheKey] = [
            'expires_at' => time() + $config['cache_ttl'],
            'data' => $payload,
        ];
        if (count($_SESSION['gsc_performance_cache']) > 4) {
            $_SESSION['gsc_performance_cache'] = array_slice($_SESSION['gsc_performance_cache'], -4, null, true);
        }
        return $payload;
    }
}
