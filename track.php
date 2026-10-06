<?php
/* ═══════════════════════════════════════════════════════════════
   IUC Edu — Tracking Beacon
   Receives pageviews / heartbeats / conversion events from the
   tracker and stores them. Fast, minimal, no session overhead.
   ═══════════════════════════════════════════════════════════════ */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/analytics.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

if (!$conn) {
    echo json_encode(['ok' => 0]);
    exit;
}
if (!an_ensure_tables($conn)) {
    http_response_code(503);
    echo json_encode(['ok' => 0, 'error' => 'analytics schema unavailable']);
    exit;
}

$in = array_merge($_GET, $_POST);
if (empty($in) || (isset($_SERVER['CONTENT_TYPE']) && stripos($_SERVER['CONTENT_TYPE'], 'json') !== false)) {
    $raw = file_get_contents('php://input');
    if ($raw) {
        $j = json_decode($raw, true);
        if (is_array($j)) $in = array_merge($in, $j);
    }
}

$visitorId = isset($in['visitor_id']) ? (string)$in['visitor_id'] : '';
$sessionId = isset($in['session_id']) ? (string)$in['session_id'] : '';
$eventType = isset($in['event_type']) ? preg_replace('/[^a-z_]/', '', strtolower($in['event_type'])) : 'pageview';

if (!$visitorId || !an_valid_uuid($visitorId)) {
    echo json_encode(['ok' => 0, 'error' => 'bad visitor']);
    exit;
}
if (!$sessionId || !an_valid_uuid($sessionId)) {
    echo json_encode(['ok' => 0, 'error' => 'bad session']);
    exit;
}

$ip       = an_client_ip();
$now      = date('Y-m-d H:i:s');
$pageUrl  = an_normalize_page_url(isset($in['page_url']) ? $in['page_url'] : '');
$landingPage = an_normalize_page_url(isset($in['landing_page']) ? $in['landing_page'] : '') ?: $pageUrl;
$pageTitle= an_clean(isset($in['page_title']) ? $in['page_title'] : '', 255);
$referrer = an_clean(isset($in['referrer']) ? $in['referrer'] : '');
$ua       = an_clean(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '', 512);
$device   = an_clean(isset($in['device']) ? $in['device'] : '', 20);
$browser  = an_clean(isset($in['browser']) ? $in['browser'] : '', 40);
$os       = an_clean(isset($in['os']) ? $in['os'] : '', 40);
$screen   = an_clean(isset($in['screen']) ? $in['screen'] : '', 24);
$lang     = an_clean(isset($in['language']) ? $in['language'] : '', 16);

$utmSource  = an_clean_utm(isset($in['utm_source']) ? $in['utm_source'] : '');
$utmMedium  = an_clean_utm(isset($in['utm_medium']) ? $in['utm_medium'] : '');
$utmCampaign= an_clean_utm(isset($in['utm_campaign']) ? $in['utm_campaign'] : '');
$campaignId = an_clean_utm(isset($in['campaign_id']) ? $in['campaign_id'] : '');
$utmContent = an_clean_utm(isset($in['utm_content']) ? $in['utm_content'] : '');
$utmTerm    = an_clean_utm(isset($in['utm_term']) ? $in['utm_term'] : '');
$clickType  = strtolower((string)an_clean_utm(isset($in['click_id_type']) ? $in['click_id_type'] : ''));
$clickId    = an_clean_utm(isset($in['click_id']) ? $in['click_id'] : '', 255);
if ($clickType === '' && !empty($in['gclid'])) {
    $clickType = 'gclid';
    $clickId = an_clean_utm($in['gclid'], 255);
}
$allowedClickTypes = ['gclid', 'dclid', 'gbraid', 'wbraid', 'msclkid', 'fbclid', 'ttclid', 'twclid', 'li_fat_id', 'gad_source', 'gad_campaignid'];
if (!in_array($clickType, $allowedClickTypes, true)) {
    $clickType = '';
    $clickId = null;
}
$duration   = isset($in['duration']) ? max(0, min(86400, (int)$in['duration'])) : 0;
$label      = an_clean(isset($in['event_label']) ? $in['event_label'] : '', 255);
$pvId       = isset($in['pv_id']) ? (int)$in['pv_id'] : 0;

$isBot = false;
if ($ua) {
    $botMarkers = ['bot', 'crawl', 'spider', 'slurp', 'curl', 'wget', 'python-requests', 'go-http-client', 'headless', 'phantomjs', 'monitoring'];
    $lu = strtolower($ua);
    foreach ($botMarkers as $m) {
        if (strpos($lu, $m) !== false) { $isBot = true; break; }
    }
}
if ($isBot) {
    echo json_encode(['ok' => 1, 'ignored' => 'bot']);
    exit;
}

/* ── Geo ─────────────────────────────────────────────────────── */
$geo = an_geo($ip, $conn);

/* ── Visitor upsert ──────────────────────────────────────────── */
$stmt = $conn->prepare("SELECT id, total_sessions, total_views, is_returning FROM analytics_visitors WHERE visitor_id = ?");
$stmt->bind_param('s', $visitorId);
$stmt->execute();
$res = $stmt->get_result();
$visitor = $res->fetch_assoc();
$stmt->close();

if ($visitor) {
    $newTotalViews = (int)$visitor['total_views'] + ($eventType === 'pageview' ? 1 : 0);
    $isReturning = (int)$visitor['is_returning'];
    $stmt = $conn->prepare("UPDATE analytics_visitors
        SET last_seen = ?, ip = ?, user_agent = ?, device = ?, browser = ?, os = ?, screen = ?, language = ?,
            country = ?, country_code = ?, state = ?, city = ?, total_views = ?, is_returning = ?
        WHERE visitor_id = ?");
    $stmt->bind_param('ssssssssssssiis', $now, $ip, $ua, $device, $browser, $os, $screen, $lang,
        $geo['country'], $geo['country_code'], $geo['state'], $geo['city'], $newTotalViews, $isReturning, $visitorId);
    $stmt->execute();
    $stmt->close();
    $visitorSessions = $visitor['total_sessions'];
    $returningNow = $isReturning === 1;
} else {
    $initialViews = $eventType === 'pageview' ? 1 : 0;
    $stmt = $conn->prepare("INSERT INTO analytics_visitors
        (visitor_id, first_seen, last_seen, ip, user_agent, device, browser, os, screen, language,
         country, country_code, state, city, total_sessions, total_views, is_returning)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, 0)");
    $stmt->bind_param('ssssssssssssssi', $visitorId, $now, $now, $ip, $ua, $device, $browser, $os, $screen, $lang,
        $geo['country'], $geo['country_code'], $geo['state'], $geo['city'], $initialViews);
    $stmt->execute();
    $stmt->close();
    $visitorSessions = 0;
    $returningNow = false;
}

/* ── Channel attribution ─────────────────────────────────────── */
list($channel, $srcClean, $medClean) = an_channel($utmSource, $utmMedium, $referrer, $clickType);
if ($channel === 'Direct' && $utmSource) {
    list($channel, $srcClean, $medClean) = an_channel($utmSource, $utmMedium, $referrer, $clickType);
}
$search = an_search_details($referrer, $utmTerm, $channel);
$searchEngine = $search['engine'];
$searchTerm = $search['keyword'];

/* ── Session upsert ──────────────────────────────────────────── */
$stmt = $conn->prepare("SELECT id, page_views FROM analytics_sessions WHERE session_id = ?");
$stmt->bind_param('s', $sessionId);
$stmt->execute();
$res = $stmt->get_result();
$sess = $res->fetch_assoc();
$stmt->close();
$isNewSession = !$sess;

if ($sess) {
    $newPv = (int)$sess['page_views'] + ($eventType === 'pageview' ? 1 : 0);
    $bounce = ($newPv <= 1) ? 1 : 0;
    $stmt = $conn->prepare("UPDATE analytics_sessions
        SET last_activity = ?, ended_at = ?, page_views = ?, is_bounce = ?, exit_page = ?
        WHERE session_id = ?");
    $stmt->bind_param('ssisss', $now, $now, $newPv, $bounce, $pageUrl, $sessionId);
    $stmt->execute();
    $stmt->close();
    $sessPv = $newPv;
} else {
    $initialPageViews = $eventType === 'pageview' ? 1 : 0;
    $initialBounce = $initialPageViews === 1 ? 1 : 0;
    $stmt = $conn->prepare("INSERT INTO analytics_sessions
        (session_id, visitor_id, ip, started_at, last_activity, ended_at, page_views, is_bounce,
         channel, source, medium, campaign, campaign_id, content, term, search_engine, search_term, click_id_type, click_id, referrer, landing_page, exit_page,
         device, browser, os, country, state, city)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('ssssssiissssssssssssssssssss',
        $sessionId, $visitorId, $ip, $now, $now, $now, $initialPageViews, $initialBounce,
        $channel, $srcClean, $medClean, $utmCampaign, $campaignId, $utmContent, $utmTerm, $searchEngine, $searchTerm, $clickType, $clickId, $referrer, $landingPage, $pageUrl,
        $device, $browser, $os, $geo['country'], $geo['state'], $geo['city']);
    $stmt->execute();
    $stmt->close();

    $newSessionCount = $visitorSessions + 1;
    $stmt = $conn->prepare("UPDATE analytics_visitors SET total_sessions = ?, is_returning = ? WHERE visitor_id = ?");
    $ret = ($newSessionCount > 1) ? 1 : 0;
    $stmt->bind_param('iis', $newSessionCount, $ret, $visitorId);
    $stmt->execute();
    $stmt->close();
    $returningNow = $ret === 1;
    $sessPv = $initialPageViews;
}

/* One presence row per visitor session/minute powers a real 30-minute trend.
   Hidden tabs stop sending heartbeats, so they no longer stay active forever. */
$minuteAt = date('Y-m-d H:i:00');
$stmt = $conn->prepare("INSERT INTO analytics_live_activity (minute_at, visitor_id, session_id, page_url, last_seen)
    VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE page_url = VALUES(page_url), last_seen = VALUES(last_seen)");
if ($stmt) {
    $stmt->bind_param('sssss', $minuteAt, $visitorId, $sessionId, $pageUrl, $now);
    $stmt->execute();
    $stmt->close();
}

/* ── Pageview / heartbeat handling ───────────────────────────── */
$pvIdOut = 0;
if ($eventType === 'pageview') {
    $stmt = $conn->prepare("INSERT INTO analytics_pageviews (visitor_id, session_id, page_url, page_title, referrer, created_at) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('ssssss', $visitorId, $sessionId, $pageUrl, $pageTitle, $referrer, $now);
    $stmt->execute();
    $pvIdOut = $stmt->insert_id;
    $stmt->close();
} elseif ($eventType === 'heartbeat') {
    if ($pvId > 0) {
        $stmt = $conn->prepare("UPDATE analytics_pageviews SET time_on_page = ? WHERE id = ? AND session_id = ?");
        $stmt->bind_param('iis', $duration, $pvId, $sessionId);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt = $conn->prepare("UPDATE analytics_pageviews SET time_on_page = ? WHERE session_id = ? AND time_on_page = 0 ORDER BY id DESC LIMIT 1");
        $stmt->bind_param('is', $duration, $sessionId);
        $stmt->execute();
        $stmt->close();
    }
    if ($duration > 0) {
        $stmt = $conn->prepare("UPDATE analytics_sessions SET duration_sec = ? WHERE session_id = ?");
        $stmt->bind_param('is', $duration, $sessionId);
        $stmt->execute();
        $stmt->close();
    }
}

/* ── Campaign row (UTM present) ──────────────────────────────── */
if ($isNewSession && ($utmSource || $utmCampaign || $campaignId || $clickType)) {
    $stmt = $conn->prepare("INSERT INTO analytics_campaigns
        (visitor_id, session_id, source, medium, campaign, campaign_id, content, term, search_engine, search_term, click_id_type, click_id, landing_page, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE source = VALUES(source), medium = VALUES(medium), campaign = VALUES(campaign),
            campaign_id = VALUES(campaign_id), content = VALUES(content), term = VALUES(term), search_engine = VALUES(search_engine),
            search_term = VALUES(search_term), click_id_type = VALUES(click_id_type), click_id = VALUES(click_id), landing_page = VALUES(landing_page)");
    $stmt->bind_param('ssssssssssssss', $visitorId, $sessionId, $srcClean, $medClean, $utmCampaign, $campaignId, $utmContent, $utmTerm, $searchEngine, $searchTerm, $clickType, $clickId, $landingPage, $now);
    $stmt->execute();
    $stmt->close();
}

/* ── Conversion / event row ──────────────────────────────────── */
$allowedEventTypes = [
    'call_click', 'whatsapp_click', 'brochure_download', 'contact_form_attempt',
    'admission', 'registration', 'outbound_click', 'custom', 'scroll_depth',
    'course_card_click', 'enquiry_modal_open', 'enquiry_form_start',
    'form_validation_failure', 'thank_you_shown', 'cta_click', 'engaged_session'
];
if ($eventType !== 'pageview' && $eventType !== 'heartbeat' && in_array($eventType, $allowedEventTypes, true)) {
    $stmt = $conn->prepare("INSERT INTO analytics_events (visitor_id, session_id, event_type, event_label, event_value, page_url, created_at) VALUES (?, ?, ?, ?, 1, ?, ?)");
    $stmt->bind_param('ssssss', $visitorId, $sessionId, $eventType, $label, $pageUrl, $now);
    $stmt->execute();
    $stmt->close();
}

echo json_encode([
    'ok' => 1,
    'visitor_id' => $visitorId,
    'session_id' => $sessionId,
    'pv_id' => $pvIdOut,
    'channel' => $channel,
    'returning' => $returningNow,
]);
