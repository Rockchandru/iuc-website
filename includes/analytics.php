<?php
/* ═══════════════════════════════════════════════════════════════
   IUC Edu — Digital Marketing Analytics Engine
   Database schema + shared helpers (works without any 3rd-party
   Facebook / Instagram / YouTube APIs — pure UTM + referrer logic)
   ═══════════════════════════════════════════════════════════════ */

if (!defined('ANALYTICS_LOADED')) {
    define('ANALYTICS_LOADED', true);

    define('AN_CONVERSION_TYPES', "'contact_form','call_click','whatsapp_click','brochure_download','admission','registration'");
    define('AN_SOCIAL_CHANNELS', "'Facebook','Instagram','YouTube','LinkedIn','WhatsApp','Email','QR Code','Twitter'");

    /* ── Schema ────────────────────────────────────────────── */
    function an_schema() {
        return [
            "CREATE TABLE IF NOT EXISTS analytics_visitors (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                visitor_id CHAR(36) NOT NULL,
                first_seen DATETIME DEFAULT NULL,
                last_seen DATETIME DEFAULT NULL,
                ip VARCHAR(45) DEFAULT NULL,
                user_agent VARCHAR(512) DEFAULT NULL,
                device VARCHAR(20) DEFAULT NULL,
                browser VARCHAR(40) DEFAULT NULL,
                os VARCHAR(40) DEFAULT NULL,
                screen VARCHAR(24) DEFAULT NULL,
                language VARCHAR(16) DEFAULT NULL,
                country VARCHAR(60) DEFAULT NULL,
                country_code VARCHAR(5) DEFAULT NULL,
                state VARCHAR(60) DEFAULT NULL,
                city VARCHAR(60) DEFAULT NULL,
                total_sessions INT UNSIGNED NOT NULL DEFAULT 0,
                total_views INT UNSIGNED NOT NULL DEFAULT 0,
                is_returning TINYINT NOT NULL DEFAULT 0,
                UNIQUE KEY uk_visitor (visitor_id),
                KEY idx_visitors_country (country),
                KEY idx_visitors_city (city),
                KEY idx_visitors_device (device),
                KEY idx_visitors_browser (browser),
                KEY idx_visitors_os (os),
                KEY idx_visitors_last_seen (last_seen)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS analytics_sessions (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                session_id CHAR(36) NOT NULL,
                visitor_id CHAR(36) DEFAULT NULL,
                ip VARCHAR(45) DEFAULT NULL,
                started_at DATETIME DEFAULT NULL,
                last_activity DATETIME DEFAULT NULL,
                ended_at DATETIME DEFAULT NULL,
                duration_sec INT UNSIGNED DEFAULT 0,
                page_views INT UNSIGNED DEFAULT 0,
                is_bounce TINYINT DEFAULT 0,
                channel VARCHAR(40) DEFAULT NULL,
                source VARCHAR(60) DEFAULT NULL,
                medium VARCHAR(60) DEFAULT NULL,
                campaign VARCHAR(100) DEFAULT NULL,
                content VARCHAR(100) DEFAULT NULL,
                term VARCHAR(100) DEFAULT NULL,
                referrer VARCHAR(512) DEFAULT NULL,
                landing_page VARCHAR(512) DEFAULT NULL,
                exit_page VARCHAR(512) DEFAULT NULL,
                device VARCHAR(20) DEFAULT NULL,
                browser VARCHAR(40) DEFAULT NULL,
                os VARCHAR(40) DEFAULT NULL,
                country VARCHAR(60) DEFAULT NULL,
                state VARCHAR(60) DEFAULT NULL,
                city VARCHAR(60) DEFAULT NULL,
                UNIQUE KEY uk_session (session_id),
                KEY idx_sessions_visitor (visitor_id),
                KEY idx_sessions_started (started_at),
                KEY idx_sessions_channel (channel),
                KEY idx_sessions_campaign (campaign),
                KEY idx_sessions_source (source),
                KEY idx_sessions_last_activity (last_activity),
                KEY idx_sessions_bounce (is_bounce)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS analytics_pageviews (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                visitor_id CHAR(36) DEFAULT NULL,
                session_id CHAR(36) DEFAULT NULL,
                page_url VARCHAR(512) DEFAULT NULL,
                page_title VARCHAR(255) DEFAULT NULL,
                referrer VARCHAR(512) DEFAULT NULL,
                created_at DATETIME DEFAULT NULL,
                time_on_page INT UNSIGNED DEFAULT 0,
                KEY idx_pv_session (session_id),
                KEY idx_pv_visitor (visitor_id),
                KEY idx_pv_created (created_at),
                KEY idx_pv_url (page_url(191))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS analytics_campaigns (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                visitor_id CHAR(36) DEFAULT NULL,
                session_id CHAR(36) DEFAULT NULL,
                source VARCHAR(60) DEFAULT NULL,
                medium VARCHAR(60) DEFAULT NULL,
                campaign VARCHAR(100) DEFAULT NULL,
                content VARCHAR(100) DEFAULT NULL,
                term VARCHAR(100) DEFAULT NULL,
                landing_page VARCHAR(512) DEFAULT NULL,
                created_at DATETIME DEFAULT NULL,
                KEY idx_campaigns_source (source),
                KEY idx_campaigns_campaign (campaign),
                KEY idx_campaigns_created (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS analytics_events (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                visitor_id CHAR(36) DEFAULT NULL,
                session_id CHAR(36) DEFAULT NULL,
                event_type VARCHAR(40) DEFAULT NULL,
                event_label VARCHAR(255) DEFAULT NULL,
                event_value FLOAT DEFAULT 0,
                page_url VARCHAR(512) DEFAULT NULL,
                created_at DATETIME DEFAULT NULL,
                KEY idx_events_type (event_type),
                KEY idx_events_created (created_at),
                KEY idx_events_session (session_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS analytics_geo_cache (
                ip VARCHAR(45) PRIMARY KEY,
                country VARCHAR(60) DEFAULT NULL,
                country_code VARCHAR(5) DEFAULT NULL,
                state VARCHAR(60) DEFAULT NULL,
                city VARCHAR(60) DEFAULT NULL,
                hits INT UNSIGNED DEFAULT 0,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS analytics_campaign_meta (
                campaign VARCHAR(100) PRIMARY KEY,
                cost FLOAT DEFAULT 0,
                note VARCHAR(255) DEFAULT NULL,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        ];
    }

    function an_ensure_tables($conn = null) {
        static $done = false;
        if ($done) return true;
        if (!$conn) {
            global $conn;
        }
        if (!$conn) return false;
        foreach (an_schema() as $sql) {
            if (!$conn->query($sql)) {
                error_log('Analytics schema error: ' . $conn->error);
            }
        }
        $done = true;
        return true;
    }

    /* ── Channel attribution (UTM + referrer, no external APIs) ── */
    function an_channel($src, $med, $referrer, $hasGclid = false) {
        $s = strtolower(trim((string)$src));
        $m = strtolower(trim((string)$med));
        $r = strtolower(trim((string)$referrer));

        $channel = '';
        $map = [
            'facebook' => 'Facebook', 'fb' => 'Facebook', 'meta' => 'Facebook', 'fb.me' => 'Facebook',
            'instagram' => 'Instagram', 'ig' => 'Instagram',
            'youtube' => 'YouTube', 'yt' => 'YouTube', 'youtu.be' => 'YouTube',
            'linkedin' => 'LinkedIn', 'linkedin.com' => 'LinkedIn',
            'whatsapp' => 'WhatsApp', 'wa' => 'WhatsApp', 'wa.me' => 'WhatsApp',
            'email' => 'Email', 'newsletter' => 'Email', 'mail' => 'Email', 'mailchimp' => 'Email',
            'qr' => 'QR Code', 'qrcode' => 'QR Code', 'qr-code' => 'QR Code',
            'twitter' => 'Twitter', 't.co' => 'Twitter', 'x.com' => 'Twitter',
            'google' => 'Google Search', 'google-ads' => 'Google Ads', 'ads' => 'Google Ads',
            'adwords' => 'Google Ads', 'ppc' => 'Google Ads', 'gclid' => 'Google Ads',
            'direct' => 'Direct', 'none' => 'Direct',
        ];
        foreach ($map as $k => $v) {
            if ($s !== '' && strpos($s, $k) !== false) { $channel = $v; break; }
        }
        if ($channel === 'Google Search' && ($m === 'cpc' || $m === 'ppc' || $m === 'paid' || $hasGclid)) {
            $channel = 'Google Ads';
        }
        if ($channel === '' && $hasGclid) $channel = 'Google Ads';

        if ($channel === '') {
            $rmap = [
                'facebook' => 'Facebook', 'instagram' => 'Instagram', 'l.instagram' => 'Instagram',
                'youtube' => 'YouTube', 'linkedin' => 'LinkedIn', 'wa.me' => 'WhatsApp',
                'api.whatsapp' => 'WhatsApp', 'mail.google' => 'Email', 'outlook' => 'Email',
                'mail.yahoo' => 'Email', 'mailchimp' => 'Email',
                'google.' => 'Google Search', 'bing' => 'Bing Search', 'yahoo' => 'Yahoo Search',
                'duckduckgo' => 'DuckDuckGo', 't.co' => 'Twitter', 'twitter' => 'Twitter',
            ];
            foreach ($rmap as $k => $v) {
                if ($r !== '' && strpos($r, $k) !== false) { $channel = $v; break; }
            }
        }
        if ($channel === '') {
            $channel = ($r === '') ? 'Direct' : 'Referral';
        }
        if ($m === '') {
            $m = ($channel === 'Direct') ? 'none' : 'referral';
        }
        return [$channel, $s, $m];
    }

    /* ── Client IP ─────────────────────────────────────────── */
    function an_client_ip() {
        foreach (['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP'] as $h) {
            if (!empty($_SERVER[$h])) {
                $parts = explode(',', $_SERVER[$h]);
                $ip = trim($parts[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? '';
    }

    /* ── Geo lookup (cached in DB, external call only once/IP) ── */
    function an_geo($ip, $conn = null) {
        if (!$conn) { global $conn; }
        $fallback = ['country' => '', 'country_code' => '', 'state' => '', 'city' => ''];

        if ($ip === '127.0.0.1' || $ip === '::1' || $ip === '' || preg_match('/^192\.168\./', $ip) || preg_match('/^10\./', $ip)) {
            return $fallback;
        }

        if ($conn) {
            $stmt = $conn->prepare("SELECT country, country_code, state, city FROM analytics_geo_cache WHERE ip = ?");
            if ($stmt) {
                $stmt->bind_param('s', $ip);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($row = $res->fetch_assoc()) {
                    $stmt->close();
                    $conn->query("UPDATE analytics_geo_cache SET hits = hits + 1 WHERE ip = '" . $conn->real_escape_string($ip) . "'");
                    return $row;
                }
                $stmt->close();
            }
        }

        $geo = null;
        $url = 'http://ip-api.com/json/' . rawurlencode($ip) . '?fields=status,country,countryCode,regionName,city&lang=en';
        $ctx = stream_context_create(['http' => ['timeout' => 3, 'ignore_errors' => true], 'ssl' => ['verify_peer' => false]]);
        $json = @file_get_contents($url, false, $ctx);
        if ($json) {
            $d = json_decode($json, true);
            if (is_array($d) && isset($d['status']) && $d['status'] === 'success') {
                $geo = [
                    'country' => (string)($d['country'] ?? ''),
                    'country_code' => (string)($d['countryCode'] ?? ''),
                    'state' => (string)($d['regionName'] ?? ''),
                    'city' => (string)($d['city'] ?? ''),
                ];
            }
        }
        if ($geo === null) $geo = $fallback;

        if ($conn) {
            $stmt = $conn->prepare("INSERT INTO analytics_geo_cache (ip, country, country_code, state, city) VALUES (?, ?, ?, ?, ?)
                                    ON DUPLICATE KEY UPDATE country = VALUES(country), country_code = VALUES(country_code), state = VALUES(state), city = VALUES(city)");
            if ($stmt) {
                $stmt->bind_param('sssss', $ip, $geo['country'], $geo['country_code'], $geo['state'], $geo['city']);
                $stmt->execute();
                $stmt->close();
            }
        }
        return $geo;
    }

    /* ── Helpers ───────────────────────────────────────────── */
    function an_clean($v, $max = 500) {
        $v = trim((string)$v);
        if ($v === '') return null;
        if (mb_strlen($v) > $max) $v = mb_substr($v, 0, $max);
        return $v;
    }

    function an_clean_utm($v, $max = 100) {
        $v = trim((string)$v);
        if ($v === '') return null;
        $v = preg_replace('/[^A-Za-z0-9_\-\+\s\.@%]/', '', $v);
        if (mb_strlen($v) > $max) $v = mb_substr($v, 0, $max);
        return $v;
    }

    function an_valid_uuid($v) {
        return is_string($v) && preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/', $v);
    }

    function an_js_bool($v) { return $v ? 'true' : 'false'; }
}
