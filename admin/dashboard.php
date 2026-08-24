<?php
/* ═══════════════════════════════════════════════════════════════
   IUC Edu — Admin Analytics Dashboard
   ═══════════════════════════════════════════════════════════════ */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/analytics.php';
require_once __DIR__ . '/_auth.php';

admin_require();
an_ensure_tables($conn);

$from = isset($_GET['from']) ? preg_replace('/[^0-9\-]/', '', $_GET['from']) : date('Y-m-d', strtotime('-30 days'));
$to   = isset($_GET['to'])   ? preg_replace('/[^0-9\-]/', '', $_GET['to'])   : date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) $from = date('Y-m-d', strtotime('-30 days'));
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $to))   $to = date('Y-m-d');
$ADMIN_BASE = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$SITE_BASE  = BASE_URL;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Analytics Dashboard – IUC Edu</title>
<meta name="robots" content="noindex, nofollow" />
<link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/images/iuc_pyramid_logo.png" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
<link rel="stylesheet" href="admin.css?v=1" />
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>var ADMIN_BASE = '<?= $ADMIN_BASE ?>'; var SITE_BASE = '<?= $SITE_BASE ?>';</script>
</head>
<body>

<div class="app">
    <!-- ── Sidebar ─────────────────────────────────────────── -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <img src="<?= BASE_URL ?>/assets/images/iuc_pyramid_logo.png" alt="IUC" />
            <div>
                <div class="sidebar-brand-name">IUC <span>Analytics</span></div>
                <div class="sidebar-brand-sub">Marketing Dashboard</div>
            </div>
        </div>

        <nav class="sidebar-nav">
            <button class="nav-item active" data-tab="overview"><i class="bi bi-speedometer2"></i> Overview</button>
            <button class="nav-item" data-tab="visitors"><i class="bi bi-people"></i> Visitors</button>
            <button class="nav-item" data-tab="campaigns"><i class="bi bi-megaphone"></i> Campaigns</button>
            <button class="nav-item" data-tab="pages"><i class="bi bi-file-earmark-text"></i> Pages</button>
            <button class="nav-item" data-tab="conversions"><i class="bi bi-graph-up"></i> Conversions</button>
            <button class="nav-item" data-tab="live"><i class="bi bi-broadcast"></i> Live Visitors <span class="live-dot" id="liveDot"></span></button>
            <button class="nav-item" data-tab="reports"><i class="bi bi-filetype-xlsx"></i> Reports</button>
        </nav>

        <div class="sidebar-foot">
            <a class="nav-item" href="<?= $SITE_BASE ?>/" target="_blank"><i class="bi bi-globe"></i> View Website</a>
            <a class="nav-item" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
        </div>
    </aside>

    <!-- ── Main ────────────────────────────────────────────── -->
    <main class="main">
        <header class="topbar">
            <div class="topbar-title">
                <h1 id="pageTitle">Overview</h1>
                <p id="periodLabel"></p>
            </div>
            <div class="topbar-controls">
                <div class="range-group">
                    <input type="date" id="fromDate" value="<?= $from ?>" />
                    <span>to</span>
                    <input type="date" id="toDate" value="<?= $to ?>" />
                    <button class="btn btn-primary" id="applyRange"><i class="bi bi-funnel"></i> Apply</button>
                </div>
                <div class="export-group">
                    <a class="btn btn-export" id="btnExcel" href="export.php?format=excel&from=<?= $from ?>&to=<?= $to ?>" title="Export Excel"><i class="bi bi-file-earmark-excel"></i> Excel</a>
                    <a class="btn btn-export" id="btnPdf" href="export.php?format=pdf&from=<?= $from ?>&to=<?= $to ?>" title="Export PDF"><i class="bi bi-file-earmark-pdf"></i> PDF</a>
                    <a class="btn btn-export" id="btnCsv" href="export.php?format=csv&from=<?= $from ?>&to=<?= $to ?>" title="Export CSV"><i class="bi bi-filetype-csv"></i> CSV</a>
                </div>
            </div>
        </header>

        <div class="content">

            <!-- ══════════ OVERVIEW ══════════ -->
            <section class="tab-panel active" id="tab-overview">
                <div class="kpi-grid" id="kpiGrid"></div>

                <div class="chart-grid chart-grid-2">
                    <div class="card"><div class="card-title"><i class="bi bi-calendar-week"></i> Daily Visitors Trend</div><div class="chart-box"><canvas id="dailyTrend"></canvas></div></div>
                    <div class="card"><div class="card-title"><i class="bi bi-device-ssd"></i> Device Distribution</div><div class="chart-box"><canvas id="devicePie"></canvas></div></div>
                </div>
                <div class="chart-grid chart-grid-2">
                    <div class="card"><div class="card-title"><i class="bi bi-diagram-3"></i> Traffic by Source</div><div class="chart-box"><canvas id="sourceBar"></canvas></div></div>
                    <div class="card"><div class="card-title"><i class="bi bi-graph-up"></i> Monthly Growth</div><div class="chart-box"><canvas id="monthlyGrowth"></canvas></div></div>
                </div>
                <div class="chart-grid chart-grid-2">
                    <div class="card"><div class="card-title"><i class="bi bi-globe"></i> Browser Share</div><div class="chart-box"><canvas id="browserPie"></canvas></div></div>
                    <div class="card"><div class="card-title"><i class="bi bi-signpost"></i> Top Landing Pages</div><div class="chart-box"><canvas id="landingBar"></canvas></div></div>
                </div>
            </section>

            <!-- ══════════ VISITORS ══════════ -->
            <section class="tab-panel" id="tab-visitors">
                <div class="card">
                    <div class="card-title"><i class="bi bi-bar-chart-line"></i> Visitor Analytics
                        <div class="seg" id="periodSeg">
                            <button data-period="daily" class="active">Daily</button>
                            <button data-period="weekly">Weekly</button>
                            <button data-period="monthly">Monthly</button>
                            <button data-period="yearly">Yearly</button>
                        </div>
                    </div>
                    <div class="chart-box tall"><canvas id="periodBar"></canvas></div>
                </div>

                <div class="chart-grid chart-grid-2">
                    <div class="card"><div class="card-title"><i class="bi bi-person-plus"></i> New vs Returning Visitors</div><div class="chart-box"><canvas id="newReturningPie"></canvas></div></div>
                    <div class="card"><div class="card-title"><i class="bi bi-display"></i> Operating Systems</div><div class="chart-box"><canvas id="osBar"></canvas></div></div>
                </div>

                <div class="chart-grid chart-grid-2">
                    <div class="card"><div class="card-title"><i class="bi bi-geo-alt"></i> Top Countries</div><div class="chart-box"><canvas id="geoCountryPie"></canvas></div></div>
                    <div class="card"><div class="card-title"><i class="bi bi-building"></i> Top Cities</div><div class="chart-box"><canvas id="cityBar"></canvas></div></div>
                </div>

                <div class="card">
                    <div class="card-title"><i class="bi bi-table"></i> Recent Visitors <span class="muted" id="recentCount"></span></div>
                    <div class="table-wrap"><table class="table" id="recentTable"></table></div>
                </div>
            </section>

            <!-- ══════════ CAMPAIGNS ══════════ -->
            <section class="tab-panel" id="tab-campaigns">
                <div class="chart-grid chart-grid-2">
                    <div class="card"><div class="card-title"><i class="bi bi-share"></i> Traffic Channels</div><div class="chart-box"><canvas id="channelPie"></canvas></div></div>
                    <div class="card"><div class="card-title"><i class="bi bi-diagram-2"></i> Source Performance</div><div class="chart-box"><canvas id="sourceBar2"></canvas></div></div>
                </div>
                <div class="chart-grid chart-grid-2">
                    <div class="card"><div class="card-title"><i class="bi bi-filter-circle"></i> Medium Performance</div><div class="chart-box"><canvas id="mediumBar"></canvas></div></div>
                    <div class="card"><div class="card-title"><i class="bi bi-trophy"></i> Top Campaigns</div><div class="table-wrap"><table class="table" id="campaignTable"></table></div></div>
                </div>

                <div class="card">
                    <div class="card-title"><i class="bi bi-threads"></i> Social Media Analytics <span class="muted">Facebook · Instagram · YouTube · LinkedIn · WhatsApp · Email · QR Code</span></div>
                    <div class="social-grid" id="socialGrid"></div>
                </div>
            </section>

            <!-- ══════════ PAGES ══════════ -->
            <section class="tab-panel" id="tab-pages">
                <div class="chart-grid chart-grid-2">
                    <div class="card"><div class="card-title"><i class="bi bi-signpost-split"></i> Top Landing Pages</div><div class="chart-box"><canvas id="landingBar2"></canvas></div></div>
                    <div class="card"><div class="card-title"><i class="bi bi-sign-turn-right"></i> Exit Pages</div><div class="chart-box"><canvas id="exitBar"></canvas></div></div>
                </div>
                <div class="card">
                    <div class="card-title"><i class="bi bi-eye"></i> Most Viewed Pages</div>
                    <div class="chart-box"><canvas id="topPagesBar"></canvas></div>
                </div>
                <div class="card">
                    <div class="card-title"><i class="bi bi-table"></i> Page Performance</div>
                    <div class="table-wrap"><table class="table" id="pageTable"></table></div>
                </div>
            </section>

            <!-- ══════════ CONVERSIONS ══════════ -->
            <section class="tab-panel" id="tab-conversions">
                <div class="kpi-grid kpi-4" id="convKpi"></div>
                <div class="chart-grid chart-grid-2">
                    <div class="card"><div class="card-title"><i class="bi bi-graph-up-arrow"></i> Conversions Over Time</div><div class="chart-box"><canvas id="convTrend"></canvas></div></div>
                    <div class="card"><div class="card-title"><i class="bi bi-pie-chart"></i> Conversion by Type</div><div class="chart-box"><canvas id="convTypePie"></canvas></div></div>
                </div>
                <div class="card">
                    <div class="card-title"><i class="bi bi-list-check"></i> Conversion Breakdown</div>
                    <div class="table-wrap"><table class="table" id="convTable"></table></div>
                </div>
            </section>

            <!-- ══════════ LIVE ══════════ -->
            <section class="tab-panel" id="tab-live">
                <div class="kpi-grid kpi-4" id="liveKpi"></div>
                <div class="card">
                    <div class="card-title"><i class="bi bi-activity"></i> Active Visitors — Last 30 min <span class="live-badge" id="liveBadge"></span></div>
                    <div class="chart-box"><canvas id="liveTrend"></canvas></div>
                </div>
                <div class="card">
                    <div class="card-title"><i class="bi bi-radioactive"></i> Currently Active <span class="muted" id="liveActiveLabel"></span></div>
                    <div class="table-wrap"><table class="table" id="liveTable"></table></div>
                </div>
            </section>

            <!-- ══════════ REPORTS ══════════ -->
            <section class="tab-panel" id="tab-reports">
                <div class="card report-card">
                    <div class="card-title"><i class="bi bi-file-earmark-bar-graph"></i> Generate Reports</div>
                    <p class="muted report-desc">Export the complete analytics report for the selected date range (<span id="reportRange"></span>). Downloads open directly.</p>
                    <div class="report-actions">
                        <a class="btn btn-export big" id="repExcel" href="export.php?format=excel&from=<?= $from ?>&to=<?= $to ?>"><i class="bi bi-file-earmark-excel"></i><div><strong>Excel Report</strong><span>.xls · full data tables</span></div></a>
                        <a class="btn btn-export big" id="repPdf" href="export.php?format=pdf&from=<?= $from ?>&to=<?= $to ?>"><i class="bi bi-file-earmark-pdf"></i><div><strong>PDF Report</strong><span>.pdf · summary + charts data</span></div></a>
                        <a class="btn btn-export big" id="repCsv" href="export.php?format=csv&from=<?= $from ?>&to=<?= $to ?>"><i class="bi bi-filetype-csv"></i><div><strong>CSV Export</strong><span>.csv · raw session data</span></div></a>
                    </div>
                </div>
                <div class="card">
                    <div class="card-title"><i class="bi bi-clipboard-data"></i> Report Summary</div>
                    <div class="summary-grid" id="summaryGrid"></div>
                </div>
            </section>

        </div>
    </main>
</div>

<script src="admin.js?v=1"></script>
</body>
</html>
