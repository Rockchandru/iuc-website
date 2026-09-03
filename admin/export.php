<?php
/* ═══════════════════════════════════════════════════════════════
   IUC Edu — Report Export (Excel / PDF / CSV)
   ═══════════════════════════════════════════════════════════════ */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/analytics.php';
require_once __DIR__ . '/_auth.php';

admin_require();

if (!$conn) { die('Database unavailable.'); }
an_ensure_tables($conn);

$format = isset($_GET['format']) ? preg_replace('/[^a-z]/', '', strtolower($_GET['format'])) : 'csv';
if (!in_array($format, ['csv', 'excel', 'pdf'], true)) $format = 'csv';

$fromRaw = isset($_GET['from']) ? preg_replace('/[^0-9\-]/', '', $_GET['from']) : date('Y-m-d', strtotime('-30 days'));
$toRaw   = isset($_GET['to'])   ? preg_replace('/[^0-9\-]/', '', $_GET['to'])   : date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromRaw)) $fromRaw = date('Y-m-d', strtotime('-30 days'));
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $toRaw))   $toRaw = date('Y-m-d');
if ($fromRaw > $toRaw) list($fromRaw, $toRaw) = [$toRaw, $fromRaw];
$fromDt = $fromRaw . ' 00:00:00';
$toDt   = $toRaw . ' 23:59:59';

$convList = AN_CONVERSION_TYPES;

function ex_q($conn, $sql, $types = '', $args = []) {
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

$sessions = ex_q($conn, "SELECT s.visitor_id, s.channel, s.source, s.medium, s.campaign, s.search_engine, s.search_term,
    s.landing_page, s.exit_page, s.referrer, s.device, s.browser, s.os, s.country, s.state, s.city,
    COALESCE(pv.c,0) AS page_views, s.duration_sec,
    CASE WHEN COALESCE(pv.c,0) <= 1 THEN 1 ELSE 0 END AS is_bounce, s.started_at, s.last_activity
    FROM analytics_sessions s
    LEFT JOIN (SELECT session_id, COUNT(*) AS c FROM analytics_pageviews GROUP BY session_id) pv ON pv.session_id = s.session_id
    WHERE s.started_at BETWEEN ? AND ? ORDER BY s.last_activity DESC", 'ss', [$fromDt, $toDt]);

$kpis = ex_q($conn, "SELECT COUNT(*) AS sessions, COUNT(DISTINCT s.visitor_id) AS visitors, COALESCE(SUM(COALESCE(pv.c,0)),0) AS views,
    COALESCE(SUM(CASE WHEN COALESCE(pv.c,0) <= 1 THEN 1 ELSE 0 END),0) AS bounce, COALESCE(AVG(s.duration_sec),0) AS dur
    FROM analytics_sessions s LEFT JOIN (SELECT session_id, COUNT(*) AS c FROM analytics_pageviews GROUP BY session_id) pv ON pv.session_id = s.session_id
    WHERE s.started_at BETWEEN ? AND ?", 'ss', [$fromDt, $toDt]);
$kpi = $kpis[0] ?? ['sessions' => 0, 'visitors' => 0, 'views' => 0, 'bounce' => 0, 'dur' => 0];
$convTotal = ex_q($conn, "SELECT COUNT(*) AS c FROM analytics_events WHERE event_type IN ($convList) AND created_at BETWEEN ? AND ?", 'ss', [$fromDt, $toDt]);
$convCount = (int)($convTotal[0]['c'] ?? 0);

$sources = ex_q($conn, "SELECT COALESCE(NULLIF(TRIM(source),''), channel) AS label, COUNT(*) AS c FROM analytics_sessions WHERE started_at BETWEEN ? AND ? GROUP BY label ORDER BY c DESC LIMIT 10", 'ss', [$fromDt, $toDt]);
$devices = ex_q($conn, "SELECT COALESCE(device,'Unknown') AS label, COUNT(*) AS c FROM analytics_sessions WHERE started_at BETWEEN ? AND ? GROUP BY device ORDER BY c DESC", 'ss', [$fromDt, $toDt]);
$browsers = ex_q($conn, "SELECT COALESCE(browser,'Unknown') AS label, COUNT(*) AS c FROM analytics_sessions WHERE started_at BETWEEN ? AND ? GROUP BY browser ORDER BY c DESC LIMIT 8", 'ss', [$fromDt, $toDt]);
$topPages = ex_q($conn, "SELECT page_url AS page, COUNT(*) AS c FROM analytics_pageviews WHERE created_at BETWEEN ? AND ? GROUP BY page_url ORDER BY c DESC LIMIT 8", 'ss', [$fromDt, $toDt]);
$campaigns = ex_q($conn, "SELECT campaign, COUNT(*) AS c FROM analytics_sessions WHERE started_at BETWEEN ? AND ? AND COALESCE(campaign,'') <> '' GROUP BY campaign ORDER BY c DESC LIMIT 8", 'ss', [$fromDt, $toDt]);
$convTypes = ex_q($conn, "SELECT event_type, COUNT(*) AS c FROM analytics_events WHERE event_type IN ($convList) AND created_at BETWEEN ? AND ? GROUP BY event_type ORDER BY c DESC", 'ss', [$fromDt, $toDt]);

$periodLabel = "$fromRaw  to  $toRaw";

/* ════════════════════════════ CSV ════════════════════════════ */
if ($format === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="iuc-analytics-' . $fromRaw . '_' . $toRaw . '.csv"');
    echo "\xEF\xBB\xBF"; // BOM for Excel
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Visitor ID', 'Channel', 'Source', 'Medium', 'Campaign', 'Search Engine', 'Search Keyword', 'Landing Page', 'Exit Page', 'Referrer', 'Device', 'Browser', 'OS', 'Country', 'State', 'City', 'Page Views', 'Duration (sec)', 'Bounce', 'Started', 'Last Activity']);
    foreach ($sessions as $r) {
        fputcsv($out, [
            $r['visitor_id'], $r['channel'], $r['source'], $r['medium'], $r['campaign'], $r['search_engine'], $r['search_term'],
            $r['landing_page'], $r['exit_page'], $r['referrer'], $r['device'], $r['browser'], $r['os'],
            $r['country'], $r['state'], $r['city'], $r['page_views'], $r['duration_sec'],
            $r['is_bounce'] ? 'Yes' : 'No', $r['started_at'], $r['last_activity'],
        ]);
    }
    fclose($out);
    exit;
}

/* ═══════════════════════════ EXCEL ══════════════════════════ */
if ($format === 'excel') {
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="iuc-analytics-' . $fromRaw . '_' . $toRaw . '.xls"');
    echo "\xEF\xBB\xBF";
    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel"><head><meta charset="utf-8"><style>
        table { border-collapse: collapse; } td, th { border: 1px solid #ccc; padding: 5px 8px; font-size: 11px; }
        th { background: #1e3a8a; color: #fff; font-weight: bold; }
        .hdr { font-size: 18px; font-weight: bold; color: #1e3a8a; }
        .sub { font-size: 12px; color: #555; }
    </style></head><body>';
    echo '<div class="hdr">IUC Edu — Digital Marketing Analytics Report</div>';
    echo '<div class="sub">Period: ' . htmlspecialchars($periodLabel) . ' · Generated: ' . date('d M Y H:i') . '</div><br/>';

    echo '<table><tr><th>Metric</th><th>Value</th></tr>';
    echo '<tr><td>Sessions</td><td>' . $kpi['sessions'] . '</td></tr>';
    echo '<tr><td>Unique Visitors</td><td>' . $kpi['visitors'] . '</td></tr>';
    echo '<tr><td>Page Views</td><td>' . $kpi['views'] . '</td></tr>';
    echo '<tr><td>Bounce Rate</td><td>' . ($kpi['sessions'] ? round($kpi['bounce'] / $kpi['sessions'] * 100, 1) : 0) . '%</td></tr>';
    echo '<tr><td>Avg Session Duration</td><td>' . round($kpi['dur']) . ' sec</td></tr>';
    echo '<tr><td>Conversions</td><td>' . $convCount . '</td></tr>';
    echo '<tr><td>Conversion Rate</td><td>' . ($kpi['sessions'] ? round($convCount / $kpi['sessions'] * 100, 2) : 0) . '%</td></tr></table><br/>';

    echo '<table><tr><th>Traffic Source</th><th>Sessions</th></tr>';
    foreach ($sources as $r) echo '<tr><td>' . htmlspecialchars((string)$r['label']) . '</td><td>' . $r['c'] . '</td></tr>';
    echo '</table><br/>';

    echo '<table><tr><th>Device</th><th>Sessions</th></tr>';
    foreach ($devices as $r) echo '<tr><td>' . htmlspecialchars((string)$r['label']) . '</td><td>' . $r['c'] . '</td></tr>';
    echo '</table><br/>';

    echo '<table><tr><th>Top Campaign</th><th>Sessions</th></tr>';
    foreach ($campaigns as $r) echo '<tr><td>' . htmlspecialchars((string)$r['campaign']) . '</td><td>' . $r['c'] . '</td></tr>';
    echo '</table><br/>';

    echo '<table><tr><th>Conversion Type</th><th>Count</th></tr>';
    foreach ($convTypes as $r) echo '<tr><td>' . htmlspecialchars((string)$r['event_type']) . '</td><td>' . $r['c'] . '</td></tr>';
    echo '</table><br/>';

    echo '<table><tr><th>Channel</th><th>Source</th><th>Medium</th><th>Campaign</th><th>Search Keyword</th><th>Landing Page</th><th>Device</th><th>Browser</th><th>OS</th><th>Country</th><th>City</th><th>Views</th><th>Duration(sec)</th><th>Bounce</th><th>Started</th></tr>';
    foreach ($sessions as $r) {
        echo '<tr><td>' . htmlspecialchars((string)$r['channel']) . '</td><td>' . htmlspecialchars((string)$r['source']) . '</td><td>' . htmlspecialchars((string)$r['medium']) . '</td><td>' . htmlspecialchars((string)$r['campaign']) . '</td><td>' . htmlspecialchars((string)$r['search_term']) . '</td><td>' . htmlspecialchars((string)$r['landing_page']) . '</td><td>' . htmlspecialchars((string)$r['device']) . '</td><td>' . htmlspecialchars((string)$r['browser']) . '</td><td>' . htmlspecialchars((string)$r['os']) . '</td><td>' . htmlspecialchars((string)$r['country']) . '</td><td>' . htmlspecialchars((string)$r['city']) . '</td><td>' . $r['page_views'] . '</td><td>' . $r['duration_sec'] . '</td><td>' . ($r['is_bounce'] ? 'Yes' : 'No') . '</td><td>' . $r['started_at'] . '</td></tr>';
    }
    echo '</table></body></html>';
    exit;
}

/* ═══════════════════════════ PDF ════════════════════════════ */

/* geometry constants (accessible inside functions) */
define('RPW', 595.28); define('RPH', 841.89); define('RML', 44); define('RMR', 44); define('RTOP', 46); define('RBOT', 46);

function pe($s) { return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $s); }
function px($h) { return [hexdec(substr($h, 1, 2)) / 255, hexdec(substr($h, 3, 2)) / 255, hexdec(substr($h, 5, 2)) / 255]; }
function pw() {
    $W = [' ' => 278, '!' => 278, '"' => 355, '#' => 556, '$' => 556, '%' => 889, '&' => 667, "'" => 191,
        '(' => 333, ')' => 333, '*' => 389, '+' => 584, ',' => 278, '-' => 333, '.' => 278, '/' => 278,
        ':' => 278, ';' => 278, '<' => 584, '=' => 584, '>' => 584, '?' => 556, '@' => 1015,
        '0' => 556, '1' => 556, '2' => 556, '3' => 556, '4' => 556, '5' => 556, '6' => 556, '7' => 556, '8' => 556, '9' => 556,
        'A' => 667, 'B' => 667, 'C' => 722, 'D' => 722, 'E' => 667, 'F' => 611, 'G' => 778, 'H' => 722,
        'I' => 278, 'J' => 500, 'K' => 667, 'L' => 556, 'M' => 833, 'N' => 722, 'O' => 778, 'P' => 667,
        'Q' => 778, 'R' => 722, 'S' => 667, 'T' => 611, 'U' => 722, 'V' => 667, 'W' => 944, 'X' => 667,
        'Y' => 667, 'Z' => 611, 'a' => 556, 'b' => 556, 'c' => 500, 'd' => 556, 'e' => 556, 'f' => 278,
        'g' => 556, 'h' => 556, 'i' => 222, 'j' => 222, 'k' => 500, 'l' => 222, 'm' => 833, 'n' => 556,
        'o' => 556, 'p' => 556, 'q' => 556, 'r' => 333, 's' => 500, 't' => 278, 'u' => 556, 'v' => 500,
        'w' => 722, 'x' => 500, 'y' => 500, 'z' => 500];
    return $W;
}
function psw($s, $size) {
    $W = pw(); $t = 0;
    for ($i = 0, $n = strlen($s); $i < $n; $i++) $t += isset($W[$s[$i]]) ? $W[$s[$i]] : 556;
    return $t / 1000 * $size;
}
function pclean($s) {
    $repl = ['₹' => 'Rs ', '–' => '-', '—' => '-', '“' => '"', '”' => '"', '‘' => "'", '’' => "'", '•' => '-', '&' => 'and', '✓' => ''];
    $s = strtr((string)$s, $repl);
    return preg_replace('/[^\x20-\x7E]/', '', $s);
}

$pages = []; $content = ''; $y = RPH - RTOP;
function r_new_page() { global $pages, $content, $y; if ($content !== '') $pages[] = $content; $content = ''; $y = RPH - RTOP; }
function r_ensure($h) { global $y; if ($y - $h < RBOT) r_new_page(); }
function r_push($ops) { global $content; $content .= $ops . "\n"; }
function r_txt($text, $font, $size, $x, $yB, $hex) {
    list($r, $g, $b) = px($hex);
    r_push(sprintf('BT %.3f %.3f %.3f rg /%s %.1f Tf %.1f %.1f Td (%s) Tj ET', $r, $g, $b, $font, $size, $x, $yB, pe(pclean($text))));
}
function r_box($x, $yB, $w, $h, $hex) {
    list($r, $g, $b) = px($hex);
    r_push(sprintf('%.3f %.3f %.3f rg %.1f %.1f %.1f %.1f re f', $r, $g, $b, $x, $yB, $w, $h));
}
function r_line($x1, $yy, $x2, $hex, $wdt = 0.7) {
    list($r, $g, $b) = px($hex);
    r_push(sprintf('%.3f %.3f %.3f RG %.1f w %.1f %.1f m %.1f %.1f l S', $r, $g, $b, $wdt, $x1, $yy, $x2, $yy));
}
function r_section($t) {
    global $y;
    r_ensure(22);
    $y -= 4;
    r_box(RML, $y - 3.4, 5, 5, '#06B6D4');
    r_txt($t, 'F2', 11.5, RML + 11, $y, '#1D4ED8');
    $y -= 16;
    r_line(RML, $y, RPW - RMR, '#D9E2EC');
    $y -= 9;
}
function r_kv($k, $v) {
    global $y;
    r_ensure(15);
    $y -= 13;
    r_txt($k, 'F1', 9, RML, $y, '#64748B');
    r_txt((string)$v, 'F2', 9, RML + 130, $y, '#0F172A');
}
function r_table($cols, $rows, $colW) {
    global $y;
    $h = 18;
    r_ensure(count($rows) * $h + 22);
    $y -= 14;
    $x = RML;
    foreach ($cols as $i => $c) {
        r_box($x, $y, $colW[$i], $h, '#1E3A8A');
        r_txt($c, 'F2', 8.5, $x + 5, $y + 5, '#FFFFFF');
        $x += $colW[$i];
    }
    $y -= $h;
    foreach ($rows as $row) {
        r_ensure($h);
        $x = RML;
        foreach ($row as $i => $v) {
            r_box($x, $y, $colW[$i], $h, ($i % 2) ? '#F8FAFC' : '#FFFFFF');
            r_txt((string)$v, 'F1', 8.5, $x + 5, $y + 5, '#334155');
            $x += $colW[$i];
        }
        r_line(RML, $y, RML + array_sum($colW), '#E2E8F0', 0.4);
        $y -= $h;
    }
    $y -= 8;
}

/* header band */
r_box(0, RPH - 44, RPW, 44, '#1E3A8A');
r_box(0, RPH - 44, RPW, 3, '#06B6D4');
r_txt('IUC EDU — DIGITAL MARKETING ANALYTICS REPORT', 'F2', 16, RML, RPH - 26, '#FFFFFF');
$tag = 'Period: ' . $periodLabel . '   |   Generated: ' . date('d M Y H:i');
r_txt($tag, 'F1', 9, RPW - RMR - psw($tag, 9), RPH - 14, '#BFDBFE');

$y -= 26;

r_section('Key Performance Indicators');
r_kv('Total Sessions', number_format((int)$kpi['sessions']));
r_kv('Unique Visitors', number_format((int)$kpi['visitors']));
r_kv('Total Page Views', number_format((int)$kpi['views']));
r_kv('Bounce Rate', ($kpi['sessions'] ? round($kpi['bounce'] / $kpi['sessions'] * 100, 1) : 0) . '%');
r_kv('Avg Session Duration', round($kpi['dur']) . ' seconds');
r_kv('Conversions', number_format($convCount));
r_kv('Conversion Rate', ($kpi['sessions'] ? round($convCount / $kpi['sessions'] * 100, 2) : 0) . '%');
$y -= 6;

r_section('Traffic Sources');
$rows = [];
foreach ($sources as $r) $rows[] = [$r['label'], number_format((int)$r['c'])];
r_table(['Source', 'Sessions'], $rows, [330, 100]);

r_section('Devices & Browsers');
$rows = [];
$maxD = max(1, count($devices)); $maxB = max(1, count($browsers));
for ($i = 0; $i < max($maxD, $maxB); $i++) {
    $rows[] = [
        ($devices[$i]['label'] ?? '') . '  —  ' . ($devices[$i]['c'] ?? 0),
        ($browsers[$i]['label'] ?? '') . '  —  ' . ($browsers[$i]['c'] ?? 0),
    ];
}
r_table(['Device — Sessions', 'Browser — Sessions'], $rows, [220, 210]);

r_section('Top Campaigns');
$rows = [];
foreach ($campaigns as $r) $rows[] = [$r['campaign'], number_format((int)$r['c'])];
r_table(['Campaign', 'Sessions'], $rows, [330, 100]);

r_section('Conversions by Type');
$rows = [];
foreach ($convTypes as $r) $rows[] = [$r['event_type'], number_format((int)$r['c'])];
r_table(['Conversion', 'Count'], $rows, [330, 100]);

r_section('Top Pages');
$rows = [];
foreach ($topPages as $r) $rows[] = [pclean($r['page']), number_format((int)$r['c'])];
r_table(['Page', 'Views'], $rows, [330, 100]);

r_section('Recent Visits (latest 20)');
$rows = [];
foreach (array_slice($sessions, 0, 20) as $r) {
    $rows[] = [pclean($r['channel']), pclean($r['landing_page']), pclean($r['device']), pclean($r['country']), $r['page_views'], pclean($r['started_at'])];
}
r_table(['Channel', 'Landing Page', 'Device', 'Country', 'Views', 'Started'], $rows, [95, 155, 65, 75, 45, 100]);

/* contact band */
r_ensure(64);
$y -= 6;
r_box(RML, $y - 58, RPW - RML - RMR, 58, '#1E3A8A');
r_box(RML, $y - 58, RPW - RML - RMR, 3, '#06B6D4');
r_txt('IUC EDU', 'F2', 12, RML + 16, $y - 16, '#FFFFFF');
r_txt('Call +91 ' . SITE_PHONE . '   |   ' . SITE_EMAIL . '   |   ' . SITE_URL, 'F1', 9, RML + 16, $y - 31, '#E2E8F0');
$y -= 62;

/* footers */
$footerOps = function ($i, $n) {
    $left = 'IUC Edu  |  ' . SITE_EMAIL . '  |  +91 ' . SITE_PHONE;
    $pg = 'Page ' . $i . ' of ' . $n;
    $ops = sprintf("0.85 0.88 0.92 RG 0.6 w %.1f %.1f m %.1f %.1f l S\n", RML, 34, RPW - RMR, 34);
    $ops .= sprintf('BT /F1 7 Tf %.1f %.1f Td (%s) Tj ET', RML, 24, pe($left)) . "\n";
    $ops .= sprintf('BT /F1 7 Tf %.1f %.1f Td (%s) Tj ET', RPW - RMR - psw($pg, 7), 24, pe($pg)) . "\n";
    return $ops;
};

if ($content !== '') $pages[] = $content;
if (!$pages) $pages[] = '';
$n = count($pages);
for ($i = 0; $i < $n; $i++) $pages[$i] .= "\n" . $footerOps($i + 1, $n);

$f1 = 3 + 2 * $n; $f2 = 4 + 2 * $n; $total = $f2;
$objects = [1 => '<< /Type /Catalog /Pages 2 0 R >>'];
$kids = [];
for ($i = 0; $i < $n; $i++) {
    $po = 3 + $i * 2; $co = $po + 1;
    $kids[] = $po . ' 0 R';
    $objects[$po] = sprintf('<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %.2f %.2f] /Resources << /Font << /F1 %d 0 R /F2 %d 0 R >> >> /Contents %d 0 R >>', RPW, RPH, $f1, $f2, $co);
    $objects[$co] = sprintf("<< /Length %d >>\nstream\n%s\nendstream", strlen($pages[$i]), $pages[$i]);
}
$objects[2] = '<< /Type /Pages /Kids [' . implode(' ', $kids) . '] /Count ' . $n . ' >>';
$objects[$f1] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
$objects[$f2] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';

$pdf = "%PDF-1.4\n";
$offsets = [];
for ($i = 1; $i <= $total; $i++) {
    $offsets[$i] = strlen($pdf);
    $pdf .= $i . " 0 obj\n" . $objects[$i] . "\nendobj\n";
}
$xref = strlen($pdf);
$pdf .= "xref\n0 " . ($total + 1) . "\n0000000000 65535 f \n";
for ($i = 1; $i <= $total; $i++) $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
$pdf .= "trailer\n<< /Size " . ($total + 1) . " /Root 1 0 R >>\nstartxref\n" . $xref . "\n%%EOF";

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="iuc-analytics-' . $fromRaw . '_' . $toRaw . '.pdf"');
header('Content-Length: ' . strlen($pdf));
header('Cache-Control: no-store');
echo $pdf;
