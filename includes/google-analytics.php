<?php
/* Google Analytics 4 Realtime client for the admin dashboard. */

if (!function_exists('ga4_config')) {
    function ga4_config() {
        static $config = null;
        if ($config !== null) return $config;

        $local = [];
        $localFile = __DIR__ . '/search-console-config.php';
        if (is_file($localFile)) {
            $loaded = require $localFile;
            if (is_array($loaded)) $local = $loaded;
        }
        $env = static function ($name, $fallback = '') {
            $value = getenv($name);
            return $value !== false && $value !== '' ? $value : $fallback;
        };
        $propertyId = preg_replace('/\D+/', '', (string)$env('GA4_PROPERTY_ID', $local['ga4_property_id'] ?? ''));
        $measurementId = strtoupper(trim((string)$env('GA4_MEASUREMENT_ID', $local['ga4_measurement_id'] ?? 'G-H9L990V9Z2')));
        $config = [
            'property_id' => $propertyId,
            'measurement_id' => preg_match('/^G-[A-Z0-9]+$/', $measurementId) ? $measurementId : '',
            'cache_ttl' => max(10, min(60, (int)$env('GA4_REALTIME_CACHE_TTL', $local['ga4_realtime_cache_ttl'] ?? 15))),
        ];
        return $config;
    }

    function ga4_http_json($url, $method, array $headers, $body, &$error = null) {
        if (!function_exists('curl_init')) {
            $error = 'PHP cURL is required for Google Analytics.';
            return null;
        }
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 12,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        if ($body !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        $raw = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($raw === false) {
            $error = 'Google Analytics network error: ' . curl_error($ch);
            curl_close($ch);
            return null;
        }
        curl_close($ch);
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            $error = 'Google Analytics returned an invalid response.';
            return null;
        }
        if ($status < 200 || $status >= 300) {
            $error = 'Google Analytics API: ' . ($decoded['error']['message'] ?? ('HTTP ' . $status));
            return null;
        }
        return $decoded;
    }

    function ga4_access_token(&$error = null) {
        if (!empty($_SESSION['ga4_access_token']['token'])
            && (int)($_SESSION['ga4_access_token']['expires_at'] ?? 0) > time() + 90) {
            return $_SESSION['ga4_access_token']['token'];
        }
        if (!function_exists('gsc_credentials')) {
            $error = 'The existing Google service account is unavailable.';
            return null;
        }
        $credentials = gsc_credentials($error);
        if (!$credentials) return null;

        $tokenUri = $credentials['token_uri'] ?? 'https://oauth2.googleapis.com/token';
        $now = time();
        $header = gsc_base64url(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $claims = gsc_base64url(json_encode([
            'iss' => $credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/analytics.readonly',
            'aud' => $tokenUri,
            'iat' => $now - 30,
            'exp' => $now + 3600,
        ], JSON_UNESCAPED_SLASHES));
        $unsigned = $header . '.' . $claims;
        $privateKey = openssl_pkey_get_private($credentials['private_key']);
        $signature = '';
        if (!$privateKey || !openssl_sign($unsigned, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            $error = 'The Google Analytics OAuth assertion could not be signed.';
            return null;
        }
        $response = ga4_http_json(
            $tokenUri,
            'POST',
            ['Content-Type: application/x-www-form-urlencoded'],
            http_build_query([
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $unsigned . '.' . gsc_base64url($signature),
            ], '', '&', PHP_QUERY_RFC3986),
            $error
        );
        if (!$response || empty($response['access_token'])) {
            if (!$error) $error = 'Google OAuth did not return an access token.';
            return null;
        }
        $_SESSION['ga4_access_token'] = [
            'token' => $response['access_token'],
            'expires_at' => $now + max(300, (int)($response['expires_in'] ?? 3600)),
        ];
        return $response['access_token'];
    }

    function ga4_cache_realtime_result($cacheKey, array $data, $ttl) {
        if (empty($_SESSION['ga4_realtime_cache']) || !is_array($_SESSION['ga4_realtime_cache'])) {
            $_SESSION['ga4_realtime_cache'] = [];
        }
        $_SESSION['ga4_realtime_cache'][$cacheKey] = [
            'expires_at' => time() + max(10, (int)$ttl),
            'data' => $data,
        ];
        return $data;
    }

    function ga4_friendly_error($error) {
        $error = trim((string)$error);
        if (stripos($error, 'has not been used in project') !== false || stripos($error, 'it is disabled') !== false) {
            return 'Google Analytics Data API is disabled in the existing Google Cloud project.';
        }
        if (stripos($error, 'does not have sufficient permissions') !== false) {
            return 'The existing service account needs Viewer access to this GA4 property.';
        }
        return $error !== '' ? $error : 'Google Analytics Realtime is unavailable.';
    }

    function ga4_realtime_active_users($forceRefresh = false) {
        $config = ga4_config();
        $base = [
            'ok' => 0,
            'measurement_id' => $config['measurement_id'],
            'active_users_30m' => null,
            'source' => 'google_analytics_4',
        ];
        if ($config['property_id'] === '') {
            return array_merge($base, ['error' => 'The GA4 numeric Property ID is not configured.']);
        }

        $cacheKey = 'property_' . $config['property_id'];
        if (!$forceRefresh && !empty($_SESSION['ga4_realtime_cache'][$cacheKey])) {
            $cached = $_SESSION['ga4_realtime_cache'][$cacheKey];
            if ((int)($cached['expires_at'] ?? 0) > time() && isset($cached['data'])) {
                $cached['data']['cache_hit'] = true;
                return $cached['data'];
            }
        }

        $error = null;
        $token = ga4_access_token($error);
        if (!$token) {
            return ga4_cache_realtime_result($cacheKey, array_merge($base, [
                'error' => ga4_friendly_error($error ?: 'Google Analytics authentication failed.'),
            ]), 300);
        }
        $url = 'https://analyticsdata.googleapis.com/v1beta/properties/'
            . rawurlencode($config['property_id']) . ':runRealtimeReport';
        $response = ga4_http_json(
            $url,
            'POST',
            ['Authorization: Bearer ' . $token, 'Content-Type: application/json'],
            json_encode(['metrics' => [['name' => 'activeUsers']]], JSON_UNESCAPED_SLASHES),
            $error
        );
        if (!$response) {
            return ga4_cache_realtime_result($cacheKey, array_merge($base, [
                'error' => ga4_friendly_error($error),
            ]), 300);
        }

        $data = array_merge($base, [
            'ok' => 1,
            'active_users_30m' => (int)($response['rows'][0]['metricValues'][0]['value'] ?? 0),
            'fetched_at' => date('c'),
            'cache_hit' => false,
        ]);
        return ga4_cache_realtime_result($cacheKey, $data, $config['cache_ttl']);
    }
}
