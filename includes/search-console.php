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

    /* Map each query cluster to one preferred page. This does not rewrite
       Search Console data; it makes keyword cannibalisation and missing page
       targeting visible in the admin report. */
    function gsc_query_target($query, $isBrand = false) {
        $q = mb_strtolower(trim((string)$query));
        $targets = [
            ['#\b(login|log in|admin)\b#u', '/admin/', 'Admin navigation'],
            ['#\b(ai|artificial intelligence|machine learning|deep learning|generative ai)\b#u', '/course/ai-ml', 'AI & Machine Learning'],
            ['#\b(?:data science|data analytics|data scientist)\b#u', '/course/data-science', 'Data Science'],
            ['#\bpython\b#u', '/course/python', 'Python'],
            ['#\b(?:full[ -]?stack(?: java)?|java full[ -]?stack)\b#u', '/course/full-stack-java', 'Full Stack Java'],
            ['#\b(?:spring boot|microservices?)\b#u', '/course/spring-boot', 'Spring Boot'],
            ['#\bjava\b#u', '/course/java', 'Java'],
            ['#\breact(?:\.?js)?\b#u', '/course/react', 'React'],
            ['#\bangular(?:\.?js)?\b#u', '/course/angular', 'Angular'],
            ['#\bnode(?:\.?js)?\b#u', '/course/node-js', 'Node.js'],
            ['#\b(ui|ux|ui\/ux|user experience|user interface)\b#u', '/course/ui-ux', 'UI/UX Design'],
            ['#\b(software testing|selenium|manual testing|automation testing|qa course)\b#u', '/course/software-testing', 'Software Testing'],
            ['#\b(?:devops|docker|kubernetes|jenkins)\b#u', '/course/devops', 'DevOps'],
            ['#\b(?:cloud computing|aws course|azure course)\b#u', '/course/cloud-computing', 'Cloud Computing'],
            ['#\b(cyber ?security|ethical hacking)\b#u', '/course/cyber-security', 'Cyber Security'],
            ['#\b(digital marketing|seo course|google ads course)\b#u', '/course/digital-marketing', 'Digital Marketing'],
            ['#\b(c\+\+|c and c\+\+|c programming)\b#u', '/course/c-cpp', 'C & C++'],
            ['#\bonline\b#u', '/online-it-courses', 'Online IT Courses'],
            ['#\b(?:beginner|beginners|after 12th|fresher)\b#u', '/it-courses-for-beginners', 'Beginner IT Courses'],
            ['#\b(programming|coding|software development|developer course)\b#u', '/programming-courses-in-chennai', 'Programming Courses'],
            ['#\b(computer|it course|it training|it institute|software institute|training institute|academy|coaching centre|coaching center)\b#u', '/computer-training-in-chennai', 'Computer & IT Training'],
        ];
        foreach ($targets as $target) {
            if (preg_match($target[0], $q)) {
                return ['path' => $target[1], 'url' => rtrim(SITE_URL, '/') . $target[1], 'intent' => $target[2]];
            }
        }
        return ['path' => '/', 'url' => rtrim(SITE_URL, '/') . '/', 'intent' => $isBrand ? 'Brand / Homepage' : 'General / Review'];
    }

    function gsc_page_path($url) {
        $path = parse_url((string)$url, PHP_URL_PATH);
        if (!$path) return '/';
        return $path === '/' ? '/' : rtrim($path, '/');
    }

    function gsc_expected_ctr($position) {
        $position = (float)$position;
        if ($position <= 5) return 0.08;
        if ($position <= 10) return 0.05;
        return 0.02;
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
        $cacheKey = hash('sha256', 'seo-monitor-v2|' . $config['property'] . '|' . $from . '|' . $to . '|' . $config['row_limit']);
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
            $target = gsc_query_target($row['query'], $row['is_brand']);
            $row['recommended_page'] = $target['url'];
            $row['intent'] = $target['intent'];
            $row['target_match'] = gsc_page_path($row['page']) === gsc_page_path($target['url']);
            $row['is_actionable'] = $target['intent'] !== 'Admin navigation';
            $row['expected_ctr'] = gsc_expected_ctr($row['position']);
            $row['estimated_click_gap'] = max(0, (int)round($row['impressions'] * ($row['expected_ctr'] - $row['ctr'])));
            $row['opportunity_score'] = ($row['estimated_click_gap'] * max(0.25, (21 - min(20, (float)$row['position'])) / 17)) + (!$row['target_match'] ? 2 : 0);
            $row['priority'] = $row['opportunity_score'] >= 5 ? 'High' : ($row['opportunity_score'] >= 2 ? 'Medium' : 'Low');
            if (!$row['is_actionable']) {
                $row['recommendation'] = 'Monitor only. Keep the admin page noindex; this is a navigational query, not a public SEO target.';
            } elseif (!$row['target_match']) {
                $row['recommendation'] = 'Consolidate this search intent on the recommended page and strengthen contextual internal links to it.';
            } elseif ((float)$row['position'] <= 10) {
                $row['recommendation'] = 'Improve the page title and search snippet for this query while keeping the copy natural.';
            } else {
                $row['recommendation'] = 'Expand useful supporting content and internal links on the recommended page.';
            }
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
            return !empty($row['is_actionable']) && $row['impressions'] >= 10 && $row['position'] >= 4 && $row['position'] <= 20 && $row['ctr'] < $row['expected_ctr'];
        }));
        usort($opportunities, static function ($a, $b) {
            return $b['opportunity_score'] <=> $a['opportunity_score'];
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
