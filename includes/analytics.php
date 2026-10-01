<?php
/* ═══════════════════════════════════════════════════════════════
   IUC Edu — Digital Marketing Analytics Engine
   Database schema + shared helpers (works without any 3rd-party
   Facebook / Instagram / YouTube APIs — pure UTM + referrer logic)
   ═══════════════════════════════════════════════════════════════ */

if (!defined('ANALYTICS_LOADED')) {
    define('ANALYTICS_LOADED', true);

    define('AN_CONVERSION_TYPES', "'contact_form','call_click','whatsapp_click','brochure_download','admission','registration'");
    define('AN_SOCIAL_CHANNELS', "'Facebook','Facebook Ads','Instagram','Instagram Ads','YouTube','YouTube Ads','LinkedIn','LinkedIn Ads','WhatsApp','Email','QR Code','Twitter','Twitter Ads','TikTok','TikTok Ads'");

    /* ── Schema ────────────────────────────────────────────── */
    function an_schema() {
        return [
            "CREATE TABLE IF NOT EXISTS analytics_visitors (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                visitor_id CHAR(36) NOT NULL,
                phone VARCHAR(20) DEFAULT NULL,
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
                campaign_id VARCHAR(100) DEFAULT NULL,
                content VARCHAR(100) DEFAULT NULL,
                term VARCHAR(100) DEFAULT NULL,
                search_engine VARCHAR(40) DEFAULT NULL,
                search_term VARCHAR(255) DEFAULT NULL,
                click_id_type VARCHAR(24) DEFAULT NULL,
                click_id VARCHAR(255) DEFAULT NULL,
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
                campaign_id VARCHAR(100) DEFAULT NULL,
                content VARCHAR(100) DEFAULT NULL,
                term VARCHAR(100) DEFAULT NULL,
                search_engine VARCHAR(40) DEFAULT NULL,
                search_term VARCHAR(255) DEFAULT NULL,
                click_id_type VARCHAR(24) DEFAULT NULL,
                click_id VARCHAR(255) DEFAULT NULL,
                landing_page VARCHAR(512) DEFAULT NULL,
                created_at DATETIME DEFAULT NULL,
                UNIQUE KEY uk_campaign_session (session_id),
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
                enquiry_id INT UNSIGNED DEFAULT NULL,
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
                landing_page VARCHAR(512) DEFAULT NULL,
                note VARCHAR(255) DEFAULT NULL,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS analytics_live_activity (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                minute_at DATETIME NOT NULL,
                visitor_id CHAR(36) NOT NULL,
                session_id CHAR(36) NOT NULL,
                page_url VARCHAR(512) DEFAULT NULL,
                last_seen DATETIME NOT NULL,
                UNIQUE KEY uk_live_session_minute (session_id, minute_at),
                KEY idx_live_minute_visitor (minute_at, visitor_id),
                KEY idx_live_last_seen (last_seen)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS analytics_migrations (
                migration_key VARCHAR(100) PRIMARY KEY,
                applied_at DATETIME NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        ];
    }

    function an_column_exists($conn, $table, $column) {
        $stmt = $conn->prepare("SELECT COUNT(*) AS c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
        if (!$stmt) return false;
        $stmt->bind_param('ss', $table, $column);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return !empty($row['c']);
    }

    function an_index_exists($conn, $table, $index) {
        $stmt = $conn->prepare("SELECT COUNT(*) AS c FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?");
        if (!$stmt) return false;
        $stmt->bind_param('ss', $table, $index);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return !empty($row['c']);
    }

    function an_schema_errors() {
        return $GLOBALS['AN_SCHEMA_ERRORS'] ?? [];
    }

    function an_schema_migrations() {
        return $GLOBALS['AN_SCHEMA_MIGRATIONS'] ?? [];
    }

    function an_migrate_schema($conn) {
        $columns = [
            ['analytics_visitors', 'phone', "ALTER TABLE analytics_visitors ADD COLUMN phone VARCHAR(20) DEFAULT NULL AFTER visitor_id"],
            ['analytics_sessions', 'search_engine', "ALTER TABLE analytics_sessions ADD COLUMN search_engine VARCHAR(40) DEFAULT NULL AFTER term"],
            ['analytics_sessions', 'search_term', "ALTER TABLE analytics_sessions ADD COLUMN search_term VARCHAR(255) DEFAULT NULL AFTER search_engine"],
            ['analytics_sessions', 'campaign_id', "ALTER TABLE analytics_sessions ADD COLUMN campaign_id VARCHAR(100) DEFAULT NULL AFTER campaign"],
            ['analytics_sessions', 'click_id_type', "ALTER TABLE analytics_sessions ADD COLUMN click_id_type VARCHAR(24) DEFAULT NULL AFTER search_term"],
            ['analytics_sessions', 'click_id', "ALTER TABLE analytics_sessions ADD COLUMN click_id VARCHAR(255) DEFAULT NULL AFTER click_id_type"],
            ['analytics_campaigns', 'search_engine', "ALTER TABLE analytics_campaigns ADD COLUMN search_engine VARCHAR(40) DEFAULT NULL AFTER term"],
            ['analytics_campaigns', 'search_term', "ALTER TABLE analytics_campaigns ADD COLUMN search_term VARCHAR(255) DEFAULT NULL AFTER search_engine"],
            ['analytics_campaigns', 'campaign_id', "ALTER TABLE analytics_campaigns ADD COLUMN campaign_id VARCHAR(100) DEFAULT NULL AFTER campaign"],
            ['analytics_campaigns', 'click_id_type', "ALTER TABLE analytics_campaigns ADD COLUMN click_id_type VARCHAR(24) DEFAULT NULL AFTER search_term"],
            ['analytics_campaigns', 'click_id', "ALTER TABLE analytics_campaigns ADD COLUMN click_id VARCHAR(255) DEFAULT NULL AFTER click_id_type"],
            ['analytics_events', 'enquiry_id', "ALTER TABLE analytics_events ADD COLUMN enquiry_id INT UNSIGNED DEFAULT NULL AFTER page_url"],
            ['analytics_campaign_meta', 'cost', "ALTER TABLE analytics_campaign_meta ADD COLUMN cost FLOAT DEFAULT 0"],
            ['analytics_campaign_meta', 'landing_page', "ALTER TABLE analytics_campaign_meta ADD COLUMN landing_page VARCHAR(512) DEFAULT NULL AFTER cost"],
            ['analytics_campaign_meta', 'note', "ALTER TABLE analytics_campaign_meta ADD COLUMN note VARCHAR(255) DEFAULT NULL AFTER landing_page"],
            ['analytics_campaign_meta', 'updated_at', "ALTER TABLE analytics_campaign_meta ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"],
        ];
        $phoneColumnAdded = false;
        foreach ($columns as $migration) {
            [$table, $column, $sql] = $migration;
            if (!an_column_exists($conn, $table, $column)) {
                if ($conn->query($sql)) {
                    $GLOBALS['AN_SCHEMA_MIGRATIONS'][] = $table . '.' . $column;
                    if ($table === 'analytics_visitors' && $column === 'phone') $phoneColumnAdded = true;
                } else {
                    $GLOBALS['AN_SCHEMA_ERRORS'][] = $table . '.' . $column . ': ' . $conn->error;
                }
            }
        }

        /* Link previously submitted enquiry numbers to their analytics visitor. */
        if ($phoneColumnAdded && an_column_exists($conn, 'enquiries', 'phone')) {
            $backfill = "UPDATE analytics_visitors v
                SET v.phone = (
                    SELECT e.phone FROM enquiries e
                    WHERE e.visitor_id = v.visitor_id AND COALESCE(e.phone, '') <> ''
                    ORDER BY e.created_at DESC, e.id DESC LIMIT 1
                )
                WHERE COALESCE(v.phone, '') = ''
                  AND EXISTS (
                    SELECT 1 FROM enquiries e2
                    WHERE e2.visitor_id = v.visitor_id AND COALESCE(e2.phone, '') <> ''
                  )";
            if (!$conn->query($backfill)) {
                $GLOBALS['AN_SCHEMA_ERRORS'][] = 'analytics_visitors.phone backfill: ' . $conn->error;
            }
        }

        $indexes = [
            ['analytics_sessions', 'idx_sessions_search_term', "ALTER TABLE analytics_sessions ADD KEY idx_sessions_search_term (search_term(191))"],
            ['analytics_campaigns', 'idx_campaigns_search_term', "ALTER TABLE analytics_campaigns ADD KEY idx_campaigns_search_term (search_term(191))"],
            ['analytics_events', 'idx_events_enquiry', "ALTER TABLE analytics_events ADD KEY idx_events_enquiry (enquiry_id)"],
        ];
        foreach ($indexes as $migration) {
            [$table, $index, $sql] = $migration;
            if (!an_index_exists($conn, $table, $index)) {
                if ($conn->query($sql)) {
                    $GLOBALS['AN_SCHEMA_MIGRATIONS'][] = $table . '.' . $index;
                } else {
                    $GLOBALS['AN_SCHEMA_ERRORS'][] = $table . '.' . $index . ': ' . $conn->error;
                }
            }
        }

        /* Correct old blank-source rows from evidence already stored on the
           session. This migration never invents a platform when neither a
           UTM source nor a recognisable referrer exists. */
        $migrationKey = '2026_09_08_attribution_sources_v4';
        $migrationDone = false;
        $check = $conn->prepare("SELECT migration_key FROM analytics_migrations WHERE migration_key = ? LIMIT 1");
        if ($check) {
            $check->bind_param('s', $migrationKey);
            $check->execute();
            $migrationDone = (bool)$check->get_result()->fetch_assoc();
            $check->close();
        }
        if (!$migrationDone) {
            $queries = [
                "UPDATE analytics_sessions SET source = NULL
                    WHERE LOWER(COALESCE(source,'')) REGEXP '^(gad_source|gad_campaignid|gclid|dclid|gbraid|wbraid|msclkid)[:=_-]'",
                "UPDATE analytics_sessions s
                    JOIN (SELECT session_id, MIN(id) AS first_referrer_id FROM analytics_pageviews
                          WHERE COALESCE(referrer,'') <> '' GROUP BY session_id) first_ref
                      ON first_ref.session_id = s.session_id
                    JOIN analytics_pageviews p ON p.id = first_ref.first_referrer_id
                    SET s.referrer = p.referrer
                    WHERE COALESCE(s.referrer,'') = ''",
                "UPDATE analytics_sessions SET campaign_id = SUBSTRING_INDEX(SUBSTRING_INDEX(landing_page, 'gad_campaignid=', -1), '&', 1)
                    WHERE COALESCE(campaign_id,'') = '' AND landing_page LIKE '%gad_campaignid=%'",
                "UPDATE analytics_sessions SET
                    click_id_type = CASE
                        WHEN landing_page LIKE '%gclid=%' THEN 'gclid'
                        WHEN landing_page LIKE '%msclkid=%' THEN 'msclkid'
                        WHEN landing_page LIKE '%fbclid=%' THEN 'fbclid'
                        WHEN landing_page LIKE '%gad_source=%' THEN 'gad_source'
                        ELSE click_id_type END,
                    click_id = CASE
                        WHEN landing_page LIKE '%gclid=%' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(landing_page, 'gclid=', -1), '&', 1)
                        WHEN landing_page LIKE '%msclkid=%' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(landing_page, 'msclkid=', -1), '&', 1)
                        WHEN landing_page LIKE '%fbclid=%' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(landing_page, 'fbclid=', -1), '&', 1)
                        WHEN landing_page LIKE '%gad_source=%' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(landing_page, 'gad_source=', -1), '&', 1)
                        ELSE click_id END
                    WHERE COALESCE(click_id_type,'') = '' AND landing_page REGEXP '[?&](gclid|msclkid|fbclid|gad_source)='",
                "UPDATE analytics_sessions SET
                    source = CASE
                        WHEN LOWER(COALESCE(referrer,'')) LIKE '%youtube%' OR LOWER(COALESCE(referrer,'')) LIKE '%youtu.be%' THEN 'youtube'
                        WHEN LOWER(COALESCE(referrer,'')) LIKE '%facebook%' OR LOWER(COALESCE(referrer,'')) LIKE '%fb.com%' THEN 'facebook'
                        WHEN LOWER(COALESCE(referrer,'')) LIKE '%instagram%' THEN 'instagram'
                        WHEN LOWER(COALESCE(referrer,'')) LIKE '%linkedin%' THEN 'linkedin'
                        WHEN LOWER(COALESCE(referrer,'')) LIKE '%tiktok%' THEN 'tiktok'
                        WHEN LOWER(COALESCE(referrer,'')) LIKE '%google.%' OR channel IN ('Google Search','Google Ads') THEN 'google'
                        WHEN LOWER(COALESCE(referrer,'')) LIKE '%bing.%' OR channel = 'Bing Search' THEN 'bing'
                        WHEN channel = 'Direct' THEN 'direct'
                        ELSE source END,
                    medium = CASE
                        WHEN (LOWER(COALESCE(referrer,'')) LIKE '%youtube%' OR LOWER(COALESCE(referrer,'')) LIKE '%youtu.be%')
                             AND (landing_page LIKE '%gclid=%' OR landing_page LIKE '%gad_source=%' OR landing_page LIKE '%gad_campaignid=%') THEN 'paid_video'
                        WHEN channel = 'Google Ads' THEN 'cpc'
                        WHEN channel IN ('Google Search','Bing Search','Yahoo Search','DuckDuckGo') THEN 'organic'
                        WHEN channel = 'Direct' THEN 'none'
                        ELSE medium END,
                    channel = CASE
                        WHEN (LOWER(COALESCE(referrer,'')) LIKE '%youtube%' OR LOWER(COALESCE(referrer,'')) LIKE '%youtu.be%')
                             AND (landing_page LIKE '%gclid=%' OR landing_page LIKE '%gad_source=%' OR landing_page LIKE '%gad_campaignid=%') THEN 'YouTube Ads'
                        ELSE channel END
                    WHERE COALESCE(NULLIF(TRIM(source),''), '') = ''",
                "UPDATE analytics_campaigns SET source = NULL
                    WHERE LOWER(COALESCE(source,'')) REGEXP '^(gad_source|gad_campaignid|gclid|dclid|gbraid|wbraid|msclkid)[:=_-]'",
                "UPDATE analytics_campaigns c JOIN analytics_sessions s ON s.session_id = c.session_id SET
                    c.source = COALESCE(NULLIF(c.source,''), s.source),
                    c.medium = COALESCE(NULLIF(c.medium,''), s.medium),
                    c.campaign_id = COALESCE(NULLIF(c.campaign_id,''), s.campaign_id),
                    c.click_id_type = COALESCE(NULLIF(c.click_id_type,''), s.click_id_type),
                    c.click_id = COALESCE(NULLIF(c.click_id,''), s.click_id)",
            ];
            $migrationOk = true;
            foreach ($queries as $sql) {
                if (!$conn->query($sql)) {
                    $GLOBALS['AN_SCHEMA_ERRORS'][] = $migrationKey . ': ' . $conn->error;
                    $migrationOk = false;
                    break;
                }
            }
            if ($migrationOk) {
                $mark = $conn->prepare("INSERT INTO analytics_migrations (migration_key, applied_at) VALUES (?, NOW())");
                if ($mark) {
                    $mark->bind_param('s', $migrationKey);
                    $mark->execute();
                    $mark->close();
                    $GLOBALS['AN_SCHEMA_MIGRATIONS'][] = $migrationKey;
                }
            }
        }

        /* Old tracker versions could write the same campaign session more
           than once. Archive every duplicate before enforcing one row/session. */
        if (!an_index_exists($conn, 'analytics_campaigns', 'uk_campaign_session')) {
            $archiveOk = $conn->query("CREATE TABLE IF NOT EXISTS analytics_campaigns_duplicate_archive LIKE analytics_campaigns");
            if ($archiveOk) {
                $archiveOk = $conn->query("INSERT IGNORE INTO analytics_campaigns_duplicate_archive
                    SELECT c.* FROM analytics_campaigns c
                    JOIN (SELECT session_id, MIN(id) AS keep_id FROM analytics_campaigns
                          WHERE session_id IS NOT NULL GROUP BY session_id HAVING COUNT(*) > 1) d
                      ON d.session_id = c.session_id AND c.id <> d.keep_id");
            }
            if ($archiveOk) {
                $archiveOk = $conn->query("DELETE c FROM analytics_campaigns c
                    JOIN (SELECT session_id, MIN(id) AS keep_id FROM analytics_campaigns
                          WHERE session_id IS NOT NULL GROUP BY session_id HAVING COUNT(*) > 1) d
                      ON d.session_id = c.session_id AND c.id <> d.keep_id");
            }
            if ($archiveOk && $conn->query("ALTER TABLE analytics_campaigns ADD UNIQUE KEY uk_campaign_session (session_id)")) {
                $GLOBALS['AN_SCHEMA_MIGRATIONS'][] = 'analytics_campaigns.uk_campaign_session';
            } else {
                $GLOBALS['AN_SCHEMA_ERRORS'][] = 'analytics_campaigns duplicate archive/index: ' . $conn->error;
            }
        }
    }

    function an_ensure_tables($conn = null) {
        static $done = false;
        if ($done) return true;
        if (!$conn) {
            global $conn;
        }
        if (!$conn) return false;

        /* Schema creation and information_schema checks are intentionally
           expensive and only need to run after a deployment changes the
           contract. The database marker keeps normal tracker beacons and live
           dashboard polls on the fast path across separate PHP requests. */
        $schemaContract = 'schema_contract_2026_09_24_v1';
        $contractCheck = $conn->prepare("SELECT migration_key FROM analytics_migrations WHERE migration_key = ? LIMIT 1");
        if ($contractCheck) {
            $contractCheck->bind_param('s', $schemaContract);
            if ($contractCheck->execute() && $contractCheck->get_result()->fetch_assoc()) {
                $contractCheck->close();
                $done = true;
                return true;
            }
            $contractCheck->close();
        }

        $GLOBALS['AN_SCHEMA_ERRORS'] = [];
        $GLOBALS['AN_SCHEMA_MIGRATIONS'] = [];
        foreach (an_schema() as $sql) {
            if (!$conn->query($sql)) {
                $GLOBALS['AN_SCHEMA_ERRORS'][] = $conn->error;
            }
        }
        an_migrate_schema($conn);
        foreach (an_schema_errors() as $error) error_log('Analytics schema error: ' . $error);
        $done = empty(an_schema_errors());
        if ($done) {
            $mark = $conn->prepare("INSERT INTO analytics_migrations (migration_key, applied_at) VALUES (?, NOW()) ON DUPLICATE KEY UPDATE applied_at = VALUES(applied_at)");
            if ($mark) {
                $mark->bind_param('s', $schemaContract);
                $mark->execute();
                $mark->close();
            }
        }
        return $done;
    }

    /* ── Channel attribution (UTM + referrer, no external APIs) ── */
    function an_channel($src, $med, $referrer, $clickType = '') {
        $s = strtolower(trim((string)$src));
        $m = strtolower(trim((string)$med));
        $r = strtolower(trim((string)$referrer));
        $clickType = strtolower(trim((string)$clickType));
        if ($clickType === '1') $clickType = 'gclid'; // compatibility with the old boolean argument

        $paidMediums = ['cpc', 'ppc', 'paid', 'paid_search', 'paid_social', 'paid_video', 'display', 'cpm'];
        /* fbclid can also appear on ordinary Facebook shares, so it proves
           the platform but does not by itself prove that the visit was paid. */
        $paidClickTypes = ['gclid', 'dclid', 'gbraid', 'wbraid', 'msclkid', 'ttclid', 'twclid', 'li_fat_id', 'gad_source', 'gad_campaignid'];
        $paid = in_array($clickType, $paidClickTypes, true) || in_array($m, $paidMediums, true);
        $platform = '';

        $sourceMap = [
            'facebook' => ['facebook', 'fb', 'meta', 'fb.me'],
            'instagram' => ['instagram', 'ig'],
            'youtube' => ['youtube', 'youtu.be', 'yt'],
            'linkedin' => ['linkedin'],
            'whatsapp' => ['whatsapp', 'wa.me'],
            'twitter' => ['twitter', 'x.com', 't.co'],
            'tiktok' => ['tiktok'],
            'google' => ['google', 'google-ads', 'adwords'],
            'bing' => ['bing', 'microsoft'],
            'email' => ['email', 'newsletter', 'mailchimp'],
            'qr' => ['qr', 'qrcode', 'qr-code'],
            'direct' => ['direct', 'none'],
        ];
        foreach ($sourceMap as $canonical => $aliases) {
            foreach ($aliases as $alias) {
                if ($s === $alias || strpos($s, $alias . '.') === 0 || strpos($s, $alias . '_') === 0 || strpos($s, $alias . '-') === 0) {
                    $platform = $canonical;
                    break 2;
                }
            }
        }

        $refHost = strtolower((string)parse_url($r, PHP_URL_HOST));
        if ($refHost === '') $refHost = $r;
        if ($platform === '') {
            $refMap = [
                'facebook' => ['facebook.', 'fb.com', 'fb.me'],
                'instagram' => ['instagram.'],
                'youtube' => ['youtube.', 'youtu.be'],
                'linkedin' => ['linkedin.'],
                'whatsapp' => ['wa.me', 'whatsapp.'],
                'twitter' => ['twitter.', 'x.com', 't.co'],
                'tiktok' => ['tiktok.'],
                'email' => ['mail.google.', 'outlook.', 'mail.yahoo.', 'mailchimp.'],
                'google' => ['google.'],
                'bing' => ['bing.'],
                'yahoo' => ['search.yahoo.'],
                'duckduckgo' => ['duckduckgo.'],
            ];
            foreach ($refMap as $canonical => $needles) {
                foreach ($needles as $needle) {
                    if (strpos($refHost, $needle) !== false) { $platform = $canonical; break 2; }
                }
            }
        }

        if ($platform === '' && $clickType === 'msclkid') $platform = 'bing';
        elseif ($platform === '' && $clickType === 'fbclid') $platform = 'facebook';
        elseif ($platform === '' && $clickType === 'ttclid') $platform = 'tiktok';
        elseif ($platform === '' && $clickType === 'twclid') $platform = 'twitter';
        elseif ($platform === '' && $clickType === 'li_fat_id') $platform = 'linkedin';
        elseif ($platform === '' && in_array($clickType, ['gclid', 'dclid', 'gbraid', 'wbraid', 'gad_source', 'gad_campaignid'], true)) $platform = 'google';

        $labels = [
            'facebook' => 'Facebook', 'instagram' => 'Instagram', 'youtube' => 'YouTube',
            'linkedin' => 'LinkedIn', 'whatsapp' => 'WhatsApp', 'twitter' => 'Twitter',
            'tiktok' => 'TikTok', 'email' => 'Email', 'qr' => 'QR Code',
        ];
        if (isset($labels[$platform])) {
            $channel = $labels[$platform];
            if ($paid && in_array($platform, ['facebook', 'instagram', 'youtube', 'linkedin', 'twitter', 'tiktok'], true)) {
                $channel .= ' Ads';
            }
        } elseif ($platform === 'google') {
            $channel = $paid ? 'Google Ads' : 'Google Search';
        } elseif ($platform === 'bing') {
            $channel = $paid ? 'Bing Ads' : 'Bing Search';
        } elseif ($platform === 'yahoo') {
            $channel = 'Yahoo Search';
        } elseif ($platform === 'duckduckgo') {
            $channel = 'DuckDuckGo';
        } elseif ($platform === 'direct' || ($platform === '' && $r === '')) {
            $channel = 'Direct';
            $platform = 'direct';
        } else {
            $channel = 'Referral';
            $platform = preg_replace('/^www\./', '', $refHost);
        }

        if ($s === '') $s = $platform;
        if ($m === '') {
            if ($channel === 'Direct') $m = 'none';
            elseif (substr($channel, -7) === ' Search') $m = 'organic';
            elseif ($channel === 'Google Ads' || $channel === 'Bing Ads') $m = 'cpc';
            elseif (substr($channel, -4) === ' Ads') $m = $platform === 'youtube' ? 'paid_video' : 'paid_social';
            else $m = 'referral';
        }
        return [$channel, $s, $m];
    }

    /* Search engines usually hide organic query text. Capture it when a
       referrer exposes it and always retain explicit utm_term values. */
    function an_search_details($referrer, $utmTerm = null, $channel = '') {
        $keyword = an_clean_utm($utmTerm, 255);
        $engine = null;
        $host = '';
        $query = [];
        if ($referrer) {
            $host = strtolower((string)parse_url((string)$referrer, PHP_URL_HOST));
            $rawQuery = (string)parse_url((string)$referrer, PHP_URL_QUERY);
            if ($rawQuery !== '') parse_str($rawQuery, $query);
        }
        $param = null;
        if (strpos($host, 'google.') !== false) { $engine = 'Google'; $param = 'q'; }
        elseif (strpos($host, 'bing.') !== false) { $engine = 'Bing'; $param = 'q'; }
        elseif (strpos($host, 'search.yahoo.') !== false) { $engine = 'Yahoo'; $param = 'p'; }
        elseif (strpos($host, 'duckduckgo.') !== false) { $engine = 'DuckDuckGo'; $param = 'q'; }
        elseif (strpos($host, 'ecosia.') !== false) { $engine = 'Ecosia'; $param = 'q'; }
        elseif (stripos((string)$channel, 'Search') !== false || $channel === 'Google Ads') { $engine = str_replace([' Search', ' Ads'], '', (string)$channel); }

        if ($keyword === null && $param && isset($query[$param])) {
            $keyword = an_clean_utm($query[$param], 255);
        }
        return ['engine' => $engine, 'keyword' => $keyword];
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
        $v = preg_replace('/[^\p{L}\p{N}_\-\+\s\.@%]/u', '', $v);
        if (mb_strlen($v) > $max) $v = mb_substr($v, 0, $max);
        return $v;
    }

    /* Store one stable URL for reporting. Advertising and social click IDs are
       useful for attribution, but they must not split one landing page into
       dozens of dashboard rows. Functional query parameters are retained. */
    function an_normalize_page_url($value) {
        $value = an_clean($value, 512);
        if ($value === null || $value === '') return null;

        $parts = @parse_url($value);
        if (!is_array($parts)) return $value;

        $path = isset($parts['path']) && $parts['path'] !== '' ? $parts['path'] : '/';
        $path = preg_replace('#/+#', '/', $path);
        if (preg_match('#/index\.php$#i', $path)) {
            $path = substr($path, 0, -9) ?: '/';
        }
        if ($path !== '/') $path = rtrim($path, '/');

        $kept = [];
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $query);
            $trackingKeys = [
                'gclid', 'dclid', 'gbraid', 'wbraid', 'gad_source', 'gad_campaignid',
                'fbclid', 'msclkid', 'ttclid', 'twclid', 'li_fat_id', 'mc_cid', 'mc_eid', '_ga', '_gl', 'ref',
            ];
            foreach ($query as $key => $item) {
                $lower = strtolower((string)$key);
                if (strpos($lower, 'utm_') === 0 || strpos($lower, 'utm-') === 0 || in_array($lower, $trackingKeys, true)) {
                    continue;
                }
                $kept[$key] = $item;
            }
            if ($kept) ksort($kept);
        }

        $origin = '';
        if (!empty($parts['host'])) {
            $scheme = !empty($parts['scheme']) ? strtolower($parts['scheme']) : 'https';
            $origin = $scheme . '://' . strtolower($parts['host']);
            if (!empty($parts['port']) && !in_array((int)$parts['port'], [80, 443], true)) {
                $origin .= ':' . (int)$parts['port'];
            }
        }
        return $origin . $path . ($kept ? '?' . http_build_query($kept, '', '&', PHP_QUERY_RFC3986) : '');
    }

    function an_valid_uuid($v) {
        return is_string($v) && preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/', $v);
    }

    function an_js_bool($v) { return $v ? 'true' : 'false'; }
}
