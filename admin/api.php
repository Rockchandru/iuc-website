<?php
/* ═══════════════════════════════════════════════════════════════
   IUC Edu — Admin Analytics API (JSON)
   ═══════════════════════════════════════════════════════════════ */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/analytics.php';
require_once __DIR__ . '/../includes/search-console.php';
require_once __DIR__ . '/../includes/google-analytics.php';
require_once __DIR__ . '/../includes/whatsapp-enquiry.php';
require_once __DIR__ . '/_auth.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (!admin_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$action = isset($_GET['action']) ? preg_replace('/[^a-z_]/', '', $_GET['action']) : 'dashboard';

function admin_api_csrf_valid() {
    return !empty($_SESSION['admin_csrf_token'])
        && hash_equals($_SESSION['admin_csrf_token'], (string)($_POST['csrf_token'] ?? ''));
}

function gsc_admin_property_valid($property) {
    if (strlen($property) > 500 || preg_match('/[\x00-\x1F\x7F]/', $property)) return false;
    if (preg_match('/^sc-domain:[a-z0-9.-]+$/i', $property)) return true;
    if (!filter_var($property, FILTER_VALIDATE_URL)) return false;
    $scheme = strtolower((string)parse_url($property, PHP_URL_SCHEME));
    return in_array($scheme, ['http', 'https'], true);
}

/* Session-only connection: the private key is never written to the project or database. */
if ($action === 'gsc_connect') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => 0, 'error' => 'POST required.']);
        exit;
    }
    if (!admin_api_csrf_valid()) {
        http_response_code(403);
        echo json_encode(['ok' => 0, 'error' => 'Invalid admin session token. Reload the page and try again.']);
        exit;
    }
    $property = trim((string)($_POST['property'] ?? 'sc-domain:iucedu.com'));
    if (!gsc_admin_property_valid($property)) {
        http_response_code(422);
        echo json_encode(['ok' => 0, 'error' => 'Enter an exact Domain property such as sc-domain:iucedu.com or a valid URL-prefix property.']);
        exit;
    }
    $upload = $_FILES['credentials'] ?? null;
    if (!$upload || (int)($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        http_response_code(422);
        echo json_encode(['ok' => 0, 'error' => 'Choose the service-account JSON key downloaded from Google Cloud.']);
        exit;
    }
    $size = (int)($upload['size'] ?? 0);
    if ($size < 100 || $size > 102400 || !is_uploaded_file($upload['tmp_name'])) {
        http_response_code(422);
        echo json_encode(['ok' => 0, 'error' => 'The credential must be a valid JSON upload smaller than 100 KB.']);
        exit;
    }
    $rawCredentials = (string)file_get_contents($upload['tmp_name']);
    $credentials = json_decode($rawCredentials, true);
    if (!is_array($credentials)
        || ($credentials['type'] ?? '') !== 'service_account'
        || !filter_var($credentials['client_email'] ?? '', FILTER_VALIDATE_EMAIL)
        || empty($credentials['private_key'])) {
        http_response_code(422);
        echo json_encode(['ok' => 0, 'error' => 'This is not a valid Google service-account JSON key.']);
        exit;
    }
    $privateKey = openssl_pkey_get_private($credentials['private_key']);
    if (!$privateKey) {
        http_response_code(422);
        echo json_encode(['ok' => 0, 'error' => 'The private key in the service-account JSON is invalid.']);
        exit;
    }
    $_SESSION['gsc_session_credentials'] = $rawCredentials;
    $_SESSION['gsc_session_property'] = $property;
    unset($_SESSION['gsc_access_token'], $_SESSION['gsc_performance_cache']);
    echo json_encode([
        'ok' => 1,
        'property' => $property,
        'account' => $credentials['client_email'],
        'message' => 'Credential accepted for this admin session. Loading real Search Console data now.',
    ], JSON_UNESCAPED_SLASHES);
    exit;
}

if ($action === 'gsc_disconnect') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !admin_api_csrf_valid()) {
        http_response_code(403);
        echo json_encode(['ok' => 0, 'error' => 'Invalid request.']);
        exit;
    }
    unset(
        $_SESSION['gsc_session_credentials'],
        $_SESSION['gsc_session_property'],
        $_SESSION['gsc_access_token'],
        $_SESSION['gsc_performance_cache']
    );
    echo json_encode(['ok' => 1, 'message' => 'Session credential removed.']);
    exit;
}

/* Search Console uses its own API and remains available independently of MySQL. */
if ($action === 'gsc_performance') {
    $gscFrom = isset($_GET['from']) ? preg_replace('/[^0-9\-]/', '', $_GET['from']) : date('Y-m-d', strtotime('-30 days'));
    $gscTo = isset($_GET['to']) ? preg_replace('/[^0-9\-]/', '', $_GET['to']) : date('Y-m-d');
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $gscFrom)) $gscFrom = date('Y-m-d', strtotime('-30 days'));
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $gscTo)) $gscTo = date('Y-m-d');
    if ($gscFrom > $gscTo) list($gscFrom, $gscTo) = [$gscTo, $gscFrom];
    $forceRefresh = isset($_GET['refresh']) && $_GET['refresh'] === '1';
    $gscResponse = gsc_performance($gscFrom, $gscTo, $forceRefresh);
    if (empty($gscResponse['ok']) && !empty($gscResponse['configured'])) http_response_code(502);
    echo json_encode($gscResponse, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

if (!$conn) {
    http_response_code(503);
    echo json_encode(['ok' => 0, 'error' => 'Database unavailable']);
    exit;
}
$schemaReady = an_ensure_tables($conn);
$apiErrors = [];

/* ── Date range ─────────────────────────────────────────────── */
$fromRaw = isset($_GET['from']) ? preg_replace('/[^0-9\-]/', '', $_GET['from']) : date('Y-m-d', strtotime('-30 days'));
$toRaw   = isset($_GET['to'])   ? preg_replace('/[^0-9\-]/', '', $_GET['to'])   : date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromRaw)) $fromRaw = date('Y-m-d', strtotime('-30 days'));
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $toRaw))   $toRaw   = date('Y-m-d');
if ($fromRaw > $toRaw) list($fromRaw, $toRaw) = [$toRaw, $fromRaw];
$fromDt = $fromRaw . ' 00:00:00';
$toDt   = $toRaw . ' 23:59:59';
$convList = AN_CONVERSION_TYPES;

function an_q($conn, $sql, $types = '', $args = []) {
    global $apiErrors;
    try {
        $stmt = $conn->prepare($sql);
    } catch (Throwable $e) {
        $apiErrors[] = $e->getMessage();
        return [];
    }
    if (!$stmt) {
        $apiErrors[] = $conn->error;
        return [];
    }
    if ($types) $stmt->bind_param($types, ...$args);
    try {
        $executed = $stmt->execute();
    } catch (Throwable $e) {
        $apiErrors[] = $e->getMessage();
        $stmt->close();
        return [];
    }
    if (!$executed) {
        $apiErrors[] = $stmt->error;
        $stmt->close();
        return [];
    }
    $res = $stmt->get_result();
    $rows = [];
    while ($row = $res->fetch_assoc()) $rows[] = $row;
    $stmt->close();
    return $rows;
}

function an_one($conn, $sql, $types = '', $args = []) {
    $rows = an_q($conn, $sql, $types, $args);
    return $rows[0] ?? [];
}

function an_source_label($source, $channel = '') {
    $value = strtolower(trim((string)$source));
    $labels = [
        'facebook' => 'Facebook', 'instagram' => 'Instagram', 'youtube' => 'YouTube',
        'linkedin' => 'LinkedIn', 'whatsapp' => 'WhatsApp', 'twitter' => 'X / Twitter',
        'tiktok' => 'TikTok', 'google' => 'Google', 'bing' => 'Microsoft Bing',
        'yahoo' => 'Yahoo', 'duckduckgo' => 'DuckDuckGo', 'email' => 'Email',
        'qr' => 'QR Code', 'direct' => 'Direct',
    ];
    if (isset($labels[$value])) return $labels[$value];
    return $value !== '' ? ucwords(str_replace(['_', '-'], ' ', $value)) : ((string)$channel ?: 'Unattributed');
}

if ($action === 'live') {
    $cut5 = date('Y-m-d H:i:s', time() - 300);
    $cut30 = date('Y-m-d H:i:s', time() - 1800);
    $active = an_q($conn, "SELECT s.session_id, s.visitor_id, v.phone, s.channel, s.source, s.medium, s.campaign, s.campaign_id,
        s.landing_page, s.exit_page, s.device, s.browser, s.os, s.country, s.city,
        COALESCE(s.page_views,0) AS page_views, s.started_at, s.last_activity
        FROM analytics_sessions s
        LEFT JOIN analytics_visitors v ON v.visitor_id = s.visitor_id
        WHERE s.last_activity >= ?
          AND NOT EXISTS (
              SELECT 1 FROM analytics_sessions newer
              WHERE newer.visitor_id = s.visitor_id AND newer.last_activity >= ?
                AND (newer.last_activity > s.last_activity OR (newer.last_activity = s.last_activity AND newer.id > s.id))
          )
        ORDER BY s.last_activity DESC LIMIT 100", 'ss', [$cut5, $cut5]);
    foreach ($active as &$activeRow) $activeRow['source_label'] = an_source_label($activeRow['source'] ?? '', $activeRow['channel'] ?? '');
    unset($activeRow);
    $counts = an_one($conn, "SELECT
            COUNT(DISTINCT CASE WHEN last_activity >= ? THEN visitor_id END) AS active_5m,
            COUNT(DISTINCT visitor_id) AS active_30m,
            SUM(CASE WHEN last_activity >= ? THEN 1 ELSE 0 END) AS sessions_5m,
            (SELECT COUNT(*) FROM analytics_visitors) AS total_visitors
        FROM analytics_sessions WHERE last_activity >= ?", 'sss', [$cut5, $cut5, $cut30]);
    $trendRows = an_q($conn, "SELECT DATE_FORMAT(minute_at, '%Y-%m-%d %H:%i:00') AS minute_at,
            COUNT(DISTINCT visitor_id) AS active_users
        FROM analytics_live_activity WHERE minute_at >= ? GROUP BY minute_at ORDER BY minute_at", 's', [$cut30]);
    $trendMap = [];
    foreach ($trendRows as $trendRow) $trendMap[$trendRow['minute_at']] = (int)$trendRow['active_users'];
    $trend = [];
    $minute = new DateTime(date('Y-m-d H:i:00', time() - (29 * 60)));
    $minuteEnd = new DateTime(date('Y-m-d H:i:00'));
    while ($minute <= $minuteEnd) {
        $key = $minute->format('Y-m-d H:i:00');
        $trend[] = ['minute' => $key, 'active_users' => (int)($trendMap[$key] ?? 0)];
        $minute->modify('+1 minute');
    }
    $iucActive30m = (int)($counts['active_30m'] ?? 0);
    $ga4Realtime = ga4_realtime_active_users();
    $usesGa4 = !empty($ga4Realtime['ok']) && $ga4Realtime['active_users_30m'] !== null;
    $displayActive30m = $usesGa4 ? (int)$ga4Realtime['active_users_30m'] : $iucActive30m;
    echo json_encode([
        'active_count' => $displayActive30m,
        'active_users_5m' => (int)($counts['active_5m'] ?? 0),
        'active_users_30m' => $displayActive30m,
        'iuc_active_users_30m' => $iucActive30m,
        'active_sessions_5m' => (int)($counts['sessions_5m'] ?? 0),
        'total_visitors' => (int)($counts['total_visitors'] ?? 0),
        'active' => $active,
        'trend' => $trend,
        'live_source' => $usesGa4 ? 'ga4' : 'iuc',
        'ga4' => $ga4Realtime,
        'definition' => $usesGa4
            ? 'Google Analytics 4 Realtime activeUsers for the last 30 minutes.'
            : 'Distinct IUC visitor IDs with activity in the selected rolling window.',
        'now' => date('Y-m-d H:i:s'),
    ]);
    exit;
}

if ($action === 'save_cost') {
    if (!admin_api_csrf_valid()) {
        http_response_code(403);
        echo json_encode(['ok' => 0, 'error' => 'Invalid admin security token. Refresh the dashboard.']);
        exit;
    }
    $campaign = an_clean_utm(isset($_POST['campaign']) ? $_POST['campaign'] : '');
    $cost = isset($_POST['cost']) ? max(0, (float)$_POST['cost']) : 0;
    $landingPage = an_clean($_POST['landing_page'] ?? '', 512);
    $note = an_clean($_POST['note'] ?? '', 255);
    if ($campaign !== null) {
        $stmt = $conn->prepare("INSERT INTO analytics_campaign_meta (campaign, cost, landing_page, note) VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE cost = VALUES(cost), landing_page = VALUES(landing_page), note = VALUES(note)");
        if (!$stmt) {
            http_response_code(500);
            echo json_encode(['ok' => 0, 'error' => 'Campaign metadata schema is unavailable.', 'detail' => $conn->error]);
            exit;
        }
        $stmt->bind_param('sdss', $campaign, $cost, $landingPage, $note);
        $saved = $stmt->execute();
        $error = $stmt->error;
        $stmt->close();
        if (!$saved) {
            http_response_code(500);
            echo json_encode(['ok' => 0, 'error' => 'Campaign metadata was not saved.', 'detail' => $error]);
        } else {
            echo json_encode(['ok' => 1, 'campaign' => $campaign, 'updated_at' => date('Y-m-d H:i:s')]);
        }
    } else {
        echo json_encode(['ok' => 0, 'error' => 'missing campaign']);
    }
    exit;
}

if ($action === 'save_enquiry') {
    if (!admin_api_csrf_valid()) {
        http_response_code(403);
        echo json_encode(['ok' => 0, 'error' => 'Invalid admin security token. Refresh the dashboard.']);
        exit;
    }
    $enquiryId = max(0, (int)($_POST['enquiry_id'] ?? 0));
    $allowedStatuses = ['new', 'contacted', 'enrolled', 'closed', 'spam'];
    $status = strtolower(trim((string)($_POST['status'] ?? 'new')));
    $note = an_clean($_POST['admin_note'] ?? '', 2000);
    if ($enquiryId < 1 || !in_array($status, $allowedStatuses, true)) {
        http_response_code(422);
        echo json_encode(['ok' => 0, 'error' => 'Invalid enquiry update.']);
        exit;
    }
    $stmt = $conn->prepare("UPDATE enquiries SET status = ?, admin_note = ? WHERE id = ?");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['ok' => 0, 'error' => 'Enquiry schema is unavailable.', 'detail' => $conn->error]);
        exit;
    }
    $stmt->bind_param('ssi', $status, $note, $enquiryId);
    $saved = $stmt->execute();
    $error = $stmt->error;
    $stmt->close();
    if (!$saved) {
        http_response_code(500);
        echo json_encode(['ok' => 0, 'error' => 'Enquiry was not updated.', 'detail' => $error]);
    } else {
        echo json_encode(['ok' => 1, 'enquiry_id' => $enquiryId, 'status' => $status]);
    }
    exit;
}

/* ── KPIs ───────────────────────────────────────────────────── */
$totalVisitors = an_one($conn, "SELECT COUNT(*) AS c FROM analytics_visitors");
$periodVisitors = an_one($conn, "SELECT COUNT(DISTINCT visitor_id) AS c FROM analytics_sessions WHERE started_at BETWEEN ? AND ?", 'ss', [$fromDt, $toDt]);
$todayVisitors = an_one($conn, "SELECT COUNT(*) AS c FROM analytics_visitors WHERE last_seen >= ?", 's', [date('Y-m-d 00:00:00')]);
$uniqueVisitors = an_one($conn, "SELECT COUNT(DISTINCT visitor_id) AS c FROM analytics_sessions WHERE started_at BETWEEN ? AND ?", 'ss', [$fromDt, $toDt]);
$returningVisitors = an_one($conn, "SELECT COUNT(DISTINCT s.visitor_id) AS c FROM analytics_sessions s JOIN analytics_visitors v ON v.visitor_id = s.visitor_id WHERE v.is_returning = 1 AND s.started_at BETWEEN ? AND ?", 'ss', [$fromDt, $toDt]);
$newVisitors = max(0, (int)($uniqueVisitors['c'] ?? 0) - (int)($returningVisitors['c'] ?? 0));
$totalViews = an_one($conn, "SELECT COUNT(*) AS c FROM analytics_pageviews WHERE created_at BETWEEN ? AND ?", 'ss', [$fromDt, $toDt]);
$sessions = an_one($conn, "SELECT COUNT(*) AS c, COALESCE(SUM(COALESCE(pv.c,0)),0) AS pv,
        COALESCE(SUM(CASE WHEN COALESCE(pv.c,0) <= 1 THEN 1 ELSE 0 END),0) AS bounce,
        COALESCE(AVG(s.duration_sec),0) AS dur
    FROM analytics_sessions s
    LEFT JOIN (SELECT session_id, COUNT(*) AS c FROM analytics_pageviews GROUP BY session_id) pv ON pv.session_id = s.session_id
    WHERE s.started_at BETWEEN ? AND ?", 'ss', [$fromDt, $toDt]);
$convTotal = an_one($conn, "SELECT COUNT(*) AS c FROM analytics_events WHERE event_type IN ($convList) AND created_at BETWEEN ? AND ?", 'ss', [$fromDt, $toDt]);
$activeNow = an_one($conn, "SELECT COUNT(DISTINCT visitor_id) AS c FROM analytics_sessions WHERE last_activity >= ?", 's', [date('Y-m-d H:i:s', time() - 1800)]);

$sessionCount = (int)($sessions['c'] ?? 0);
$bounceRate = $sessionCount ? round(($sessions['bounce'] ?? 0) / $sessionCount * 100, 1) : 0;
$avgDur = (float)($sessions['dur'] ?? 0);
$convRate = $sessionCount ? round(($convTotal['c'] ?? 0) / $sessionCount * 100, 2) : 0;

/* ── Daily / weekly / monthly / yearly ──────────────────────── */
$dailyRaw = an_q($conn, "SELECT DATE(s.started_at) AS d, COUNT(*) AS sessions, COUNT(DISTINCT s.visitor_id) AS visitors,
        COALESCE(SUM(COALESCE(pv.c,0)),0) AS views
    FROM analytics_sessions s
    LEFT JOIN (SELECT session_id, COUNT(*) AS c FROM analytics_pageviews GROUP BY session_id) pv ON pv.session_id = s.session_id
    WHERE s.started_at BETWEEN ? AND ? GROUP BY DATE(s.started_at) ORDER BY d", 'ss', [$fromDt, $toDt]);
$dailyMap = [];
foreach ($dailyRaw as $r) $dailyMap[$r['d']] = $r;
$daily = [];
$d = new DateTime($fromRaw);
$dEnd = new DateTime($toRaw);
while ($d <= $dEnd) {
    $key = $d->format('Y-m-d');
    $daily[] = [
        'date' => $key,
        'sessions' => (int)($dailyMap[$key]['sessions'] ?? 0),
        'visitors' => (int)($dailyMap[$key]['visitors'] ?? 0),
        'views' => (int)($dailyMap[$key]['views'] ?? 0),
    ];
    $d->modify('+1 day');
}

$weekly = an_q($conn, "SELECT YEARWEEK(started_at) AS yw, MIN(DATE(started_at)) AS d, COUNT(*) AS sessions, COUNT(DISTINCT visitor_id) AS visitors
    FROM analytics_sessions WHERE started_at BETWEEN ? AND ? GROUP BY yw ORDER BY yw", 'ss', [$fromDt, $toDt]);
$monthly = an_q($conn, "SELECT DATE_FORMAT(started_at, '%Y-%m') AS m, MIN(DATE(started_at)) AS d, COUNT(*) AS sessions, COUNT(DISTINCT visitor_id) AS visitors
    FROM analytics_sessions WHERE started_at BETWEEN ? AND ? GROUP BY m ORDER BY m", 'ss', [$fromDt, $toDt]);
$yearly = an_q($conn, "SELECT YEAR(started_at) AS y, COUNT(*) AS sessions, COUNT(DISTINCT visitor_id) AS visitors
    FROM analytics_sessions WHERE started_at BETWEEN ? AND ? GROUP BY y ORDER BY y", 'ss', [$fromDt, $toDt]);

/* ── Sources / mediums / channels / campaigns ───────────────── */
$sources = an_q($conn, "SELECT COALESCE(NULLIF(TRIM(s.source),''), s.channel) AS label, COUNT(*) AS sessions,
        COALESCE(SUM(COALESCE(pv.c,0)),0) AS views
    FROM analytics_sessions s LEFT JOIN (SELECT session_id, COUNT(*) AS c FROM analytics_pageviews GROUP BY session_id) pv ON pv.session_id = s.session_id
    WHERE s.started_at BETWEEN ? AND ? GROUP BY label ORDER BY sessions DESC LIMIT 12", 'ss', [$fromDt, $toDt]);
foreach ($sources as &$sourceRow) $sourceRow['label'] = an_source_label($sourceRow['label'] ?? '');
unset($sourceRow);
$mediums = an_q($conn, "SELECT COALESCE(NULLIF(TRIM(s.medium),''), s.channel) AS label, COUNT(*) AS sessions,
        COALESCE(SUM(COALESCE(pv.c,0)),0) AS views
    FROM analytics_sessions s LEFT JOIN (SELECT session_id, COUNT(*) AS c FROM analytics_pageviews GROUP BY session_id) pv ON pv.session_id = s.session_id
    WHERE s.started_at BETWEEN ? AND ? GROUP BY label ORDER BY sessions DESC LIMIT 12", 'ss', [$fromDt, $toDt]);
$channels = an_q($conn, "SELECT COALESCE(channel,'Direct') AS label, COUNT(*) AS value
    FROM analytics_sessions WHERE started_at BETWEEN ? AND ? GROUP BY channel ORDER BY value DESC", 'ss', [$fromDt, $toDt]);

$campaigns = an_q($conn, "SELECT COALESCE(NULLIF(TRIM(s.campaign),''), CONCAT('Campaign ID ', s.campaign_id)) AS campaign,
        s.campaign_id, s.content, s.source, s.medium, s.click_id_type, MIN(SUBSTRING_INDEX(s.landing_page,'?',1)) AS landing_page,
        MIN(s.started_at) AS first_seen, MAX(s.last_activity) AS last_seen, COUNT(*) AS sessions,
        COALESCE(SUM(COALESCE(pv.c,0)),0) AS views,
        COALESCE(SUM(conv.c),0) AS conversions
    FROM analytics_sessions s
    LEFT JOIN (SELECT session_id, COUNT(*) AS c FROM analytics_pageviews GROUP BY session_id) pv ON pv.session_id = s.session_id
    LEFT JOIN (SELECT session_id, COUNT(*) AS c FROM analytics_events WHERE event_type IN ($convList) AND created_at BETWEEN ? AND ? GROUP BY session_id) conv ON conv.session_id = s.session_id
    WHERE s.started_at BETWEEN ? AND ? AND (COALESCE(s.campaign,'') <> '' OR COALESCE(s.campaign_id,'') <> '')
    GROUP BY campaign, s.campaign_id, s.content, s.source, s.medium, s.click_id_type ORDER BY sessions DESC LIMIT 50", 'ssss', [$fromDt, $toDt, $fromDt, $toDt]);

$campaignCosts = an_q($conn, "SELECT campaign, cost, landing_page, note, updated_at FROM analytics_campaign_meta");
$costMap = [];
foreach ($campaignCosts as $cm) $costMap[$cm['campaign']] = $cm;
foreach ($campaigns as &$cp) {
    $meta = $costMap[$cp['campaign']] ?? [];
    $cost = (float)($meta['cost'] ?? 0);
    $cp['cost'] = $cost;
    $cp['note'] = $meta['note'] ?? '';
    $cp['meta_updated_at'] = $meta['updated_at'] ?? null;
    if (!empty($meta['landing_page'])) $cp['landing_page'] = $meta['landing_page'];
    $cp['roi'] = $cost > 0 ? round((float)$cp['conversions'] / $cost * 100, 1) : null;
    $cp['source_label'] = an_source_label($cp['source'] ?? '');
}
unset($cp);

/* ── Social media ───────────────────────────────────────────── */
$social = an_q($conn, "SELECT s.channel, COUNT(*) AS sessions, COALESCE(SUM(COALESCE(pv.c,0)),0) AS views,
        (SELECT COUNT(*) FROM analytics_events e JOIN analytics_sessions s2 ON s2.session_id = e.session_id WHERE e.event_type IN ($convList) AND s2.channel = s.channel AND e.created_at BETWEEN ? AND ?) AS conversions
    FROM analytics_sessions s
    LEFT JOIN (SELECT session_id, COUNT(*) AS c FROM analytics_pageviews GROUP BY session_id) pv ON pv.session_id = s.session_id
    WHERE s.channel IN (" . AN_SOCIAL_CHANNELS . ") AND s.started_at BETWEEN ? AND ?
    GROUP BY s.channel ORDER BY sessions DESC", 'ssss', [$fromDt, $toDt, $fromDt, $toDt]);

/* ── Devices / browsers / OS ────────────────────────────────── */
$devices = an_q($conn, "SELECT COALESCE(device,'Unknown') AS label, COUNT(*) AS value FROM analytics_sessions WHERE started_at BETWEEN ? AND ? GROUP BY device ORDER BY value DESC", 'ss', [$fromDt, $toDt]);
$browsers = an_q($conn, "SELECT COALESCE(browser,'Unknown') AS label, COUNT(*) AS value FROM analytics_sessions WHERE started_at BETWEEN ? AND ? GROUP BY browser ORDER BY value DESC", 'ss', [$fromDt, $toDt]);
$osList = an_q($conn, "SELECT COALESCE(os,'Unknown') AS label, COUNT(*) AS value FROM analytics_sessions WHERE started_at BETWEEN ? AND ? GROUP BY os ORDER BY value DESC", 'ss', [$fromDt, $toDt]);

/* ── Geography ──────────────────────────────────────────────── */
$countries = an_q($conn, "SELECT COALESCE(country,'Unknown') AS label, COUNT(*) AS value FROM analytics_sessions WHERE started_at BETWEEN ? AND ? GROUP BY country ORDER BY value DESC LIMIT 15", 'ss', [$fromDt, $toDt]);
$states = an_q($conn, "SELECT COALESCE(state,'Unknown') AS label, COUNT(*) AS value FROM analytics_sessions WHERE started_at BETWEEN ? AND ? GROUP BY state ORDER BY value DESC LIMIT 10", 'ss', [$fromDt, $toDt]);
$cities = an_q($conn, "SELECT COALESCE(city,'Unknown') AS label, COUNT(*) AS value FROM analytics_sessions WHERE started_at BETWEEN ? AND ? GROUP BY city ORDER BY value DESC LIMIT 15", 'ss', [$fromDt, $toDt]);

/* ── Pages ──────────────────────────────────────────────────── */
$landingPages = an_q($conn, "SELECT COALESCE(NULLIF(SUBSTRING_INDEX(landing_page,'?',1),''),'/') AS page, COUNT(*) AS sessions FROM analytics_sessions WHERE started_at BETWEEN ? AND ? GROUP BY page ORDER BY sessions DESC LIMIT 10", 'ss', [$fromDt, $toDt]);
$exitPages = an_q($conn, "SELECT COALESCE(NULLIF(SUBSTRING_INDEX(exit_page,'?',1),''),'/') AS page, COUNT(*) AS sessions FROM analytics_sessions WHERE started_at BETWEEN ? AND ? GROUP BY page ORDER BY sessions DESC LIMIT 10", 'ss', [$fromDt, $toDt]);
$topPages = an_q($conn, "SELECT COALESCE(NULLIF(SUBSTRING_INDEX(page_url,'?',1),''),'/') AS page, COUNT(*) AS views, COUNT(DISTINCT visitor_id) AS visitors
    FROM analytics_pageviews WHERE created_at BETWEEN ? AND ? GROUP BY page ORDER BY views DESC LIMIT 10", 'ss', [$fromDt, $toDt]);

/* ── Conversions ────────────────────────────────────────────── */
$convByType = an_q($conn, "SELECT event_type, COUNT(*) AS c FROM analytics_events WHERE event_type IN ($convList) AND created_at BETWEEN ? AND ? GROUP BY event_type ORDER BY c DESC", 'ss', [$fromDt, $toDt]);
$convOverTime = an_q($conn, "SELECT DATE(created_at) AS d, COUNT(*) AS c FROM analytics_events WHERE event_type IN ($convList) AND created_at BETWEEN ? AND ? GROUP BY DATE(created_at) ORDER BY d", 'ss', [$fromDt, $toDt]);
$convMap = [];
foreach ($convOverTime as $r) $convMap[$r['d']] = (int)$r['c'];
$convSeries = [];
$d2 = new DateTime($fromRaw);
while ($d2 <= $dEnd) {
    $key = $d2->format('Y-m-d');
    $convSeries[] = ['date' => $key, 'count' => (int)($convMap[$key] ?? 0)];
    $d2->modify('+1 day');
}

/* ── Recent visits (table view) ─────────────────────────────── */
$recent = an_q($conn, "SELECT s.visitor_id, v.phone, s.channel, s.source, s.medium, s.campaign, s.campaign_id, s.landing_page, s.exit_page,
        s.device, s.browser, s.os, s.country, s.state, s.city, COALESCE(pv.c,0) AS page_views,
        s.duration_sec, CASE WHEN COALESCE(pv.c,0) <= 1 THEN 1 ELSE 0 END AS is_bounce, s.started_at, s.last_activity
    FROM analytics_sessions s
    LEFT JOIN analytics_visitors v ON v.visitor_id = s.visitor_id
    LEFT JOIN (SELECT session_id, COUNT(*) AS c FROM analytics_pageviews GROUP BY session_id) pv ON pv.session_id = s.session_id
    ORDER BY s.last_activity DESC LIMIT 60");
foreach ($recent as &$recentRow) $recentRow['source_label'] = an_source_label($recentRow['source'] ?? '', $recentRow['channel'] ?? '');
unset($recentRow);

/* SEO acquisition: real search keywords when available, plus honest not-provided counts. */
$searchChannels = "'Google Search','Bing Search','Yahoo Search','DuckDuckGo'";
$organicSessions = an_one($conn, "SELECT COUNT(*) AS c FROM analytics_sessions WHERE channel IN ($searchChannels) AND started_at BETWEEN ? AND ?", 'ss', [$fromDt, $toDt]);
$paidSearchSessions = an_one($conn, "SELECT COUNT(*) AS c FROM analytics_sessions WHERE channel = 'Google Ads' AND started_at BETWEEN ? AND ?", 'ss', [$fromDt, $toDt]);
$knownKeywordSessions = an_one($conn, "SELECT COUNT(*) AS c FROM analytics_sessions
    WHERE COALESCE(NULLIF(TRIM(search_term),''), NULLIF(TRIM(term),'')) IS NOT NULL AND started_at BETWEEN ? AND ?", 'ss', [$fromDt, $toDt]);
$notProvidedSessions = an_one($conn, "SELECT COUNT(*) AS c FROM analytics_sessions
    WHERE channel IN ($searchChannels) AND COALESCE(NULLIF(TRIM(search_term),''), NULLIF(TRIM(term),'')) IS NULL
      AND started_at BETWEEN ? AND ?", 'ss', [$fromDt, $toDt]);
$seoKeywords = an_q($conn, "SELECT COALESCE(NULLIF(TRIM(s.search_term),''), NULLIF(TRIM(s.term),'')) AS keyword,
        CASE
            WHEN s.channel = 'Google Ads' THEN 'Google Ads'
            WHEN s.search_engine = 'Google' OR s.channel = 'Google Search' THEN 'Google Organic'
            WHEN s.search_engine IS NOT NULL AND s.search_engine <> '' THEN s.search_engine
            ELSE s.channel
        END AS engine, COUNT(DISTINCT s.session_id) AS sessions,
        COUNT(DISTINCT s.visitor_id) AS visitors, COALESCE(SUM(COALESCE(pv.c,0)),0) AS views,
        COALESCE(SUM(COALESCE(conv.c,0)),0) AS conversions,
        COALESCE(NULLIF(SUBSTRING_INDEX(s.landing_page,'?',1),''),'/') AS landing_page,
        MAX(s.last_activity) AS last_seen
    FROM analytics_sessions s
    LEFT JOIN (SELECT session_id, COUNT(*) AS c FROM analytics_pageviews GROUP BY session_id) pv ON pv.session_id = s.session_id
    LEFT JOIN (SELECT session_id, COUNT(*) AS c FROM analytics_events WHERE event_type IN ($convList) GROUP BY session_id) conv ON conv.session_id = s.session_id
    WHERE COALESCE(NULLIF(TRIM(s.search_term),''), NULLIF(TRIM(s.term),'')) IS NOT NULL
      AND s.started_at BETWEEN ? AND ?
    GROUP BY keyword, engine, COALESCE(NULLIF(SUBSTRING_INDEX(s.landing_page,'?',1),''),'/')
    ORDER BY sessions DESC, conversions DESC LIMIT 50", 'ss', [$fromDt, $toDt]);
$searchEngines = an_q($conn, "SELECT CASE
            WHEN channel = 'Google Ads' THEN 'Google Ads'
            WHEN search_engine = 'Google' OR channel = 'Google Search' THEN 'Google Organic'
            WHEN search_engine IS NOT NULL AND search_engine <> '' THEN search_engine
            ELSE channel
        END AS engine, COUNT(*) AS sessions,
        COUNT(DISTINCT visitor_id) AS visitors
    FROM analytics_sessions WHERE (channel IN ($searchChannels) OR channel = 'Google Ads' OR search_engine IS NOT NULL)
      AND started_at BETWEEN ? AND ? GROUP BY engine ORDER BY sessions DESC", 'ss', [$fromDt, $toDt]);
$seoLandingPages = an_q($conn, "SELECT COALESCE(NULLIF(SUBSTRING_INDEX(landing_page,'?',1),''),'/') AS page, COUNT(*) AS sessions, COUNT(DISTINCT visitor_id) AS visitors,
        SUM(CASE WHEN COALESCE(NULLIF(TRIM(search_term),''), NULLIF(TRIM(term),'')) IS NOT NULL THEN 1 ELSE 0 END) AS known_keywords
    FROM analytics_sessions WHERE (channel IN ($searchChannels) OR channel = 'Google Ads' OR search_engine IS NOT NULL)
      AND started_at BETWEEN ? AND ? GROUP BY page ORDER BY sessions DESC LIMIT 20", 'ss', [$fromDt, $toDt]);

/* Saved enquiries and their attribution details. */
$whatsappSchemaError = null;
$whatsappSchemaReady = wa_ensure_schema($conn, $whatsappSchemaError);
$whatsappSelect = $whatsappSchemaReady
    ? ", w.status AS whatsapp_status, w.delivery_status AS whatsapp_delivery_status,
        w.attempt_count AS whatsapp_attempt_count, w.error_code AS whatsapp_error_code,
        w.error_message AS whatsapp_error_message, w.sent_at AS whatsapp_sent_at"
    : ", NULL AS whatsapp_status, NULL AS whatsapp_delivery_status, 0 AS whatsapp_attempt_count,
        NULL AS whatsapp_error_code, NULL AS whatsapp_error_message, NULL AS whatsapp_sent_at";
$whatsappJoin = $whatsappSchemaReady ? " LEFT JOIN whatsapp_enquiry_messages w ON w.enquiry_id = e.id" : '';
$enquiries = an_q($conn, "SELECT e.id, e.full_name, e.phone, e.email, e.course, e.message, e.page_url, e.landing_page, e.referrer,
        e.visitor_id, e.session_id, e.utm_source, e.utm_medium, e.utm_campaign, e.utm_content, e.utm_term,
        e.status, e.admin_note, e.created_at, e.updated_at,
        COALESCE(NULLIF(e.utm_source,''), NULLIF(s.source,'')) AS attributed_source,
        COALESCE(NULLIF(e.utm_medium,''), NULLIF(s.medium,'')) AS attributed_medium,
        COALESCE(NULLIF(e.utm_campaign,''), NULLIF(s.campaign,'')) AS attributed_campaign,
        COALESCE(NULLIF(e.utm_content,''), NULLIF(s.content,'')) AS attributed_content,
        s.campaign_id, s.channel AS attributed_channel $whatsappSelect
    FROM enquiries e LEFT JOIN analytics_sessions s ON s.session_id = e.session_id $whatsappJoin
    WHERE e.created_at BETWEEN ? AND ? ORDER BY e.created_at DESC LIMIT 200", 'ss', [$fromDt, $toDt]);
foreach ($enquiries as &$enquiryRow) {
    $enquiryRow['source_label'] = an_source_label($enquiryRow['attributed_source'] ?? '', $enquiryRow['attributed_channel'] ?? '');
}
unset($enquiryRow);
$enquiryStatus = an_q($conn, "SELECT status, COUNT(*) AS c FROM enquiries WHERE created_at BETWEEN ? AND ? GROUP BY status ORDER BY c DESC", 'ss', [$fromDt, $toDt]);
$enquiryTotal = an_one($conn, "SELECT COUNT(*) AS c, MAX(created_at) AS latest FROM enquiries");

/* Collector health makes silent failures visible in the dashboard. */
$healthTables = [
    'analytics_visitors' => ['last_seen'],
    'analytics_sessions' => ['last_activity'],
    'analytics_pageviews' => ['created_at'],
    'analytics_events' => ['created_at'],
    'analytics_live_activity' => ['last_seen'],
    'analytics_campaign_meta' => ['updated_at'],
    'enquiries' => ['created_at'],
];
if ($whatsappSchemaReady) $healthTables['whatsapp_enquiry_messages'] = ['created_at'];
$healthRows = [];
foreach ($healthTables as $table => $columns) {
    $timeColumn = $columns[0];
    $row = an_one($conn, "SELECT COUNT(*) AS row_count, MAX(`$timeColumn`) AS latest FROM `$table`");
    $healthRows[] = ['table' => $table, 'rows' => (int)($row['row_count'] ?? 0), 'latest' => $row['latest'] ?? null];
}
$health = [
    'database' => true,
    'schema_ready' => $schemaReady,
    'schema_errors' => an_schema_errors(),
    'schema_migrations' => an_schema_migrations(),
    'query_errors' => array_values(array_unique($apiErrors)),
    'tables' => $healthRows,
    'checked_at' => date('Y-m-d H:i:s'),
];

echo json_encode([
    'ok' => 1,
    'from' => $fromRaw,
    'to' => $toRaw,
    'kpis' => [
        'total_visitors' => (int)($totalVisitors['c'] ?? 0),
        'period_visitors' => (int)($periodVisitors['c'] ?? 0),
        'today_visitors' => (int)($todayVisitors['c'] ?? 0),
        'active_now' => (int)($activeNow['c'] ?? 0),
        'unique_visitors' => (int)($uniqueVisitors['c'] ?? 0),
        'returning_visitors' => (int)($returningVisitors['c'] ?? 0),
        'new_visitors' => $newVisitors,
        'total_views' => (int)($totalViews['c'] ?? 0),
        'sessions' => $sessionCount,
        'bounce_rate' => $bounceRate,
        'avg_duration' => round($avgDur, 0),
        'conversions' => (int)($convTotal['c'] ?? 0),
        'conversion_rate' => $convRate,
    ],
    'daily' => $daily,
    'weekly' => $weekly,
    'monthly' => $monthly,
    'yearly' => $yearly,
    'sources' => $sources,
    'mediums' => $mediums,
    'channels' => $channels,
    'campaigns' => $campaigns,
    'social' => $social,
    'devices' => $devices,
    'browsers' => $browsers,
    'os' => $osList,
    'countries' => $countries,
    'states' => $states,
    'cities' => $cities,
    'landing_pages' => $landingPages,
    'exit_pages' => $exitPages,
    'top_pages' => $topPages,
    'conversions' => [
        'by_type' => $convByType,
        'over_time' => $convSeries,
        'total' => (int)($convTotal['c'] ?? 0),
        'rate' => $convRate,
    ],
    'seo' => [
        'organic_sessions' => (int)($organicSessions['c'] ?? 0),
        'paid_search_sessions' => (int)($paidSearchSessions['c'] ?? 0),
        'known_keyword_sessions' => (int)($knownKeywordSessions['c'] ?? 0),
        'not_provided_sessions' => (int)($notProvidedSessions['c'] ?? 0),
        'keywords' => $seoKeywords,
        'engines' => $searchEngines,
        'landing_pages' => $seoLandingPages,
        'measurement_note' => 'Individual organic Google queries are normally hidden by the browser. Search Console supplies aggregate query performance; utm_term supplies session-level campaign attribution.',
    ],
    'enquiries' => [
        'rows' => $enquiries,
        'by_status' => $enquiryStatus,
        'total' => (int)($enquiryTotal['c'] ?? 0),
        'latest' => $enquiryTotal['latest'] ?? null,
    ],
    'health' => $health,
    'recent' => $recent,
]);
