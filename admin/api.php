<?php
/* ═══════════════════════════════════════════════════════════════
   IUC Edu — Admin Analytics API (JSON)
   ═══════════════════════════════════════════════════════════════ */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/analytics.php';
require_once __DIR__ . '/_auth.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (!admin_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if (!$conn) {
    echo json_encode(['error' => 'DB unavailable']);
    exit;
}
an_ensure_tables($conn);

$action = isset($_GET['action']) ? preg_replace('/[^a-z_]/', '', $_GET['action']) : 'dashboard';

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
    $stmt = $conn->prepare($sql);
    if (!$stmt) return [];
    if ($types) $stmt->bind_param($types, ...$args);
    $stmt->execute();
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

if ($action === 'live') {
    $cut = date('Y-m-d H:i:s', time() - 300);
    $active = an_q($conn, "SELECT session_id, visitor_id, channel, source, medium, campaign, exit_page, device, browser, os, country, city, page_views, started_at, last_activity
        FROM analytics_sessions WHERE last_activity >= ? ORDER BY last_activity DESC LIMIT 100", 's', [$cut]);
    $count = an_one($conn, "SELECT COUNT(*) AS c FROM analytics_sessions WHERE last_activity >= ?", 's', [$cut]);
    $total = an_one($conn, "SELECT COUNT(*) AS c FROM analytics_visitors");
    echo json_encode([
        'active_count' => (int)($count['c'] ?? 0),
        'total_visitors' => (int)($total['c'] ?? 0),
        'active' => $active,
        'now' => date('Y-m-d H:i:s'),
    ]);
    exit;
}

if ($action === 'save_cost') {
    $campaign = an_clean_utm(isset($_POST['campaign']) ? $_POST['campaign'] : '');
    $cost = isset($_POST['cost']) ? max(0, (float)$_POST['cost']) : 0;
    if ($campaign !== '') {
        $stmt = $conn->prepare("INSERT INTO analytics_campaign_meta (campaign, cost) VALUES (?, ?) ON DUPLICATE KEY UPDATE cost = VALUES(cost)");
        $stmt->bind_param('sd', $campaign, $cost);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['ok' => 1]);
    } else {
        echo json_encode(['ok' => 0, 'error' => 'missing campaign']);
    }
    exit;
}

/* ── KPIs ───────────────────────────────────────────────────── */
$totalVisitors = an_one($conn, "SELECT COUNT(*) AS c FROM analytics_visitors");
$periodVisitors = an_one($conn, "SELECT COUNT(DISTINCT visitor_id) AS c FROM analytics_sessions WHERE started_at BETWEEN ? AND ?", 'ss', [$fromDt, $toDt]);
$todayVisitors = an_one($conn, "SELECT COUNT(*) AS c FROM analytics_visitors WHERE last_seen >= ?", 's', [date('Y-m-d 00:00:00')]);
$uniqueVisitors = an_one($conn, "SELECT COUNT(DISTINCT visitor_id) AS c FROM analytics_sessions WHERE started_at BETWEEN ? AND ?", 'ss', [$fromDt, $toDt]);
$returningVisitors = an_one($conn, "SELECT COUNT(DISTINCT visitor_id) AS c FROM analytics_sessions s JOIN analytics_visitors v ON v.visitor_id = s.visitor_id WHERE v.is_returning = 1 AND s.started_at BETWEEN ? AND ?", 'ss', [$fromDt, $toDt]);
$newVisitors = max(0, (int)($uniqueVisitors['c'] ?? 0) - (int)($returningVisitors['c'] ?? 0));
$totalViews = an_one($conn, "SELECT COUNT(*) AS c FROM analytics_pageviews WHERE created_at BETWEEN ? AND ?", 'ss', [$fromDt, $toDt]);
$sessions = an_one($conn, "SELECT COUNT(*) AS c, COALESCE(SUM(page_views),0) AS pv, COALESCE(SUM(is_bounce),0) AS bounce, COALESCE(AVG(duration_sec),0) AS dur
    FROM analytics_sessions WHERE started_at BETWEEN ? AND ?", 'ss', [$fromDt, $toDt]);
$convTotal = an_one($conn, "SELECT COUNT(*) AS c FROM analytics_events WHERE event_type IN ($convList) AND created_at BETWEEN ? AND ?", 'ss', [$fromDt, $toDt]);
$activeNow = an_one($conn, "SELECT COUNT(*) AS c FROM analytics_sessions WHERE last_activity >= ?", 's', [date('Y-m-d H:i:s', time() - 300)]);

$sessionCount = (int)($sessions['c'] ?? 0);
$bounceRate = $sessionCount ? round(($sessions['bounce'] ?? 0) / $sessionCount * 100, 1) : 0;
$avgDur = (float)($sessions['dur'] ?? 0);
$convRate = $sessionCount ? round(($convTotal['c'] ?? 0) / $sessionCount * 100, 2) : 0;

/* ── Daily / weekly / monthly / yearly ──────────────────────── */
$dailyRaw = an_q($conn, "SELECT DATE(started_at) AS d, COUNT(*) AS sessions, COUNT(DISTINCT visitor_id) AS visitors, SUM(page_views) AS views
    FROM analytics_sessions WHERE started_at BETWEEN ? AND ? GROUP BY DATE(started_at) ORDER BY d", 'ss', [$fromDt, $toDt]);
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
$sources = an_q($conn, "SELECT COALESCE(NULLIF(TRIM(source),''), channel) AS label, COUNT(*) AS sessions, COALESCE(SUM(page_views),0) AS views
    FROM analytics_sessions WHERE started_at BETWEEN ? AND ? GROUP BY label ORDER BY sessions DESC LIMIT 12", 'ss', [$fromDt, $toDt]);
$mediums = an_q($conn, "SELECT COALESCE(NULLIF(TRIM(medium),''), channel) AS label, COUNT(*) AS sessions, COALESCE(SUM(page_views),0) AS views
    FROM analytics_sessions WHERE started_at BETWEEN ? AND ? GROUP BY label ORDER BY sessions DESC LIMIT 12", 'ss', [$fromDt, $toDt]);
$channels = an_q($conn, "SELECT COALESCE(channel,'Direct') AS label, COUNT(*) AS value
    FROM analytics_sessions WHERE started_at BETWEEN ? AND ? GROUP BY channel ORDER BY value DESC", 'ss', [$fromDt, $toDt]);

$campaigns = an_q($conn, "SELECT s.campaign, s.source, s.medium, COUNT(*) AS sessions,
        COALESCE(SUM(s.page_views),0) AS views,
        COALESCE(SUM(conv.c),0) AS conversions
    FROM analytics_sessions s
    LEFT JOIN (SELECT session_id, COUNT(*) AS c FROM analytics_events WHERE event_type IN ($convList) AND created_at BETWEEN ? AND ? GROUP BY session_id) conv ON conv.session_id = s.session_id
    WHERE s.started_at BETWEEN ? AND ? AND COALESCE(s.campaign,'') <> ''
    GROUP BY s.campaign, s.source, s.medium ORDER BY sessions DESC LIMIT 15", 'ssss', [$fromDt, $toDt, $fromDt, $toDt]);

$campaignCosts = an_q($conn, "SELECT campaign, cost, note FROM analytics_campaign_meta");
$costMap = [];
foreach ($campaignCosts as $cm) $costMap[$cm['campaign']] = (float)$cm['cost'];
foreach ($campaigns as &$cp) {
    $cost = $costMap[$cp['campaign']] ?? 0;
    $cp['cost'] = $cost;
    $cp['roi'] = $cost > 0 ? round((float)$cp['conversions'] / $cost * 100, 1) : null;
}
unset($cp);

/* ── Social media ───────────────────────────────────────────── */
$social = an_q($conn, "SELECT channel, COUNT(*) AS sessions, COALESCE(SUM(page_views),0) AS views,
        (SELECT COUNT(*) FROM analytics_events e JOIN analytics_sessions s2 ON s2.session_id = e.session_id WHERE e.event_type IN ($convList) AND s2.channel = s.channel AND e.created_at BETWEEN ? AND ?) AS conversions
    FROM analytics_sessions s WHERE channel IN (" . AN_SOCIAL_CHANNELS . ") AND started_at BETWEEN ? AND ?
    GROUP BY channel ORDER BY sessions DESC", 'ssss', [$fromDt, $toDt, $fromDt, $toDt]);

/* ── Devices / browsers / OS ────────────────────────────────── */
$devices = an_q($conn, "SELECT COALESCE(device,'Unknown') AS label, COUNT(*) AS value FROM analytics_sessions WHERE started_at BETWEEN ? AND ? GROUP BY device ORDER BY value DESC", 'ss', [$fromDt, $toDt]);
$browsers = an_q($conn, "SELECT COALESCE(browser,'Unknown') AS label, COUNT(*) AS value FROM analytics_sessions WHERE started_at BETWEEN ? AND ? GROUP BY browser ORDER BY value DESC", 'ss', [$fromDt, $toDt]);
$osList = an_q($conn, "SELECT COALESCE(os,'Unknown') AS label, COUNT(*) AS value FROM analytics_sessions WHERE started_at BETWEEN ? AND ? GROUP BY os ORDER BY value DESC", 'ss', [$fromDt, $toDt]);

/* ── Geography ──────────────────────────────────────────────── */
$countries = an_q($conn, "SELECT COALESCE(country,'Unknown') AS label, COUNT(*) AS value FROM analytics_sessions WHERE started_at BETWEEN ? AND ? GROUP BY country ORDER BY value DESC LIMIT 15", 'ss', [$fromDt, $toDt]);
$states = an_q($conn, "SELECT COALESCE(state,'Unknown') AS label, COUNT(*) AS value FROM analytics_sessions WHERE started_at BETWEEN ? AND ? GROUP BY state ORDER BY value DESC LIMIT 10", 'ss', [$fromDt, $toDt]);
$cities = an_q($conn, "SELECT COALESCE(city,'Unknown') AS label, COUNT(*) AS value FROM analytics_sessions WHERE started_at BETWEEN ? AND ? GROUP BY city ORDER BY value DESC LIMIT 15", 'ss', [$fromDt, $toDt]);

/* ── Pages ──────────────────────────────────────────────────── */
$landingPages = an_q($conn, "SELECT landing_page AS page, COUNT(*) AS sessions FROM analytics_sessions WHERE started_at BETWEEN ? AND ? GROUP BY landing_page ORDER BY sessions DESC LIMIT 10", 'ss', [$fromDt, $toDt]);
$exitPages = an_q($conn, "SELECT exit_page AS page, COUNT(*) AS sessions FROM analytics_sessions WHERE started_at BETWEEN ? AND ? GROUP BY exit_page ORDER BY sessions DESC LIMIT 10", 'ss', [$fromDt, $toDt]);
$topPages = an_q($conn, "SELECT page_url AS page, COUNT(*) AS views, COUNT(DISTINCT visitor_id) AS visitors
    FROM analytics_pageviews WHERE created_at BETWEEN ? AND ? GROUP BY page_url ORDER BY views DESC LIMIT 10", 'ss', [$fromDt, $toDt]);

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
$recent = an_q($conn, "SELECT visitor_id, channel, source, medium, campaign, landing_page, exit_page,
        device, browser, os, country, state, city, page_views, duration_sec, is_bounce, started_at, last_activity
    FROM analytics_sessions ORDER BY last_activity DESC LIMIT 60");

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
    'recent' => $recent,
]);
