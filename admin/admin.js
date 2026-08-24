/* ═══════════════════════════════════════════════════════════════
   IUC Edu — Admin Analytics JS
   ═══════════════════════════════════════════════════════════════ */
(function () {
  'use strict';

  var API = (typeof ADMIN_BASE !== 'undefined' ? ADMIN_BASE : '/admin') + '/api.php';
  var charts = {};
  var state = { from: '', to: '', data: null, period: 'daily', liveSeries: [], liveInterval: null };

  var PALETTE = ['#2563eb', '#06b6d4', '#8b5cf6', '#f59e0b', '#10b981', '#ef4444', '#ec4899', '#14b8a6', '#6366f1', '#f97316', '#84cc16', '#0ea5e9', '#a855f7', '#e11d48', '#64748b'];
  var CHIP = {
    'Facebook': 'chip-s', 'Instagram': 'chip-m', 'YouTube': 'chip-r', 'LinkedIn': 'chip-s',
    'WhatsApp': 'chip-l', 'Email': 'chip-g', 'QR Code': 'chip-m', 'Twitter': 'chip-b',
    'Google Search': 'chip-s', 'Google Ads': 'chip-l', 'Direct': 'chip-b', 'Referral': 'chip-b',
    'Bing Search': 'chip-s', 'Yahoo Search': 'chip-s', 'DuckDuckGo': 'chip-s', 'Unknown': 'chip-b'
  };
  var CONV_LABELS = {
    contact_form: 'Contact Forms', call_click: 'Call Clicks', whatsapp_click: 'WhatsApp Clicks',
    brochure_download: 'Brochure Downloads', admission: 'Admissions', registration: 'Registrations',
    outbound_click: 'Outbound Clicks'
  };
  var SOCIAL_META = {
    Facebook: ['bi-facebook', '#1877f2'], Instagram: ['bi-instagram', '#e1306c'],
    YouTube: ['bi-youtube', '#ff0000'], LinkedIn: ['bi-linkedin', '#0a66c2'],
    WhatsApp: ['bi-whatsapp', '#25d366'], Email: ['bi-envelope-fill', '#ea4335'],
    'QR Code': ['bi-qr-code-scan', '#8b5cf6'], Twitter: ['bi-twitter-x', '#0f1419']
  };

  /* ── helpers ───────────────────────────────────────────────── */
  function el(id) { return document.getElementById(id); }
  function esc(s) {
    return String(s === null || s === undefined ? '' : s).replace(/[&<>"']/g, function (m) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m];
    });
  }
  function shortUrl(u) {
    try {
      var a = document.createElement('a'); a.href = u;
      return (a.pathname || '/') + (a.search ? a.search.slice(0, 40) : '');
    } catch (e) { return u; }
  }
  function fmtInt(n) { return Number(n || 0).toLocaleString('en-IN'); }
  function fmtDur(sec) {
    sec = Number(sec || 0);
    if (sec < 60) return sec + 's';
    var m = Math.floor(sec / 60), s = sec % 60;
    return m + 'm ' + s + 's';
  }
  function fmtPct(n, d) { return (Number(n || 0).toFixed(d === undefined ? 1 : d)) + '%'; }
  function chip(channel) { return '<span class="chip ' + (CHIP[channel] || 'chip-b') + '">' + esc(channel || 'Unknown') + '</span>'; }
  function badge(v, yes) { return v ? '<span class="pill ' + (yes ? 'pill-green' : 'pill-red') + '">Yes</span>' : '<span class="pill ' + (yes ? 'pill-red' : 'pill-amber') + '">No</span>'; }

  function makeChart(id, cfg) {
    if (charts[id]) { charts[id].destroy(); }
    var ctx = el(id);
    if (!ctx) return null;
    var defaults = {
      responsive: true, maintainAspectRatio: false,
      plugins: {
        legend: { labels: { boxWidth: 12, boxHeight: 12, font: { size: 11 } } },
        tooltip: { callbacks: {} }
      },
      animation: { duration: 400 }
    };
    var merged = Object.assign({}, defaults, cfg);
    merged.options = Object.assign({}, defaults.plugins && {}, cfg.options || {});
    charts[id] = new Chart(ctx, merged);
    return charts[id];
  }

  function lineOpts(tooltipLabel, fill) {
    return {
      plugins: { legend: { display: true, position: 'top' } },
      scales: {
        x: { grid: { display: false }, ticks: { maxRotation: 45, autoSkip: true, maxTicksLimit: 12, font: { size: 10 } } },
        y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { precision: 0, font: { size: 10 } } }
      }
    };
  }

  /* ── data fetch ────────────────────────────────────────────── */
  function loadDashboard() {
    var url = API + '?action=dashboard&from=' + encodeURIComponent(state.from) + '&to=' + encodeURIComponent(state.to);
    return fetch(url, { credentials: 'same-origin' }).then(function (r) { return r.json(); });
  }

  function refresh() {
    loadDashboard().then(function (d) {
      if (!d || d.error) { renderError(d); return; }
      state.data = d;
      el('periodLabel').textContent = 'Report period: ' + d.from + '  →  ' + d.to;
      el('reportRange').textContent = d.from + ' to ' + d.to;
      renderKpis(d);
      renderCharts(d);
      renderRecent(d);
      renderCampaigns(d);
      renderSocial(d);
      renderPages(d);
      renderConversions(d);
      renderSummary(d);
      renderLiveKpi(d);
    }).catch(function (e) { renderError(e); });
  }

  function renderError(e) {
    console.error(e);
    var g = el('kpiGrid');
    if (g) g.innerHTML = '<div class="card" style="grid-column:1/-1;color:#b91c1c">Failed to load analytics data. Check database connection.</div>';
  }

  /* ── KPIs ──────────────────────────────────────────────────── */
  function kpiCard(icon, color, value, label, sub, bg) {
    return '<div class="kpi"><div class="kpi-icon" style="background:' + bg + ';color:' + color + '"><i class="bi ' + icon + '"></i></div>' +
      '<div class="kpi-value">' + value + '</div>' +
      '<div class="kpi-label">' + label + '</div>' +
      (sub ? '<div class="kpi-sub">' + sub + '</div>' : '') + '</div>';
  }
  function renderKpis(d) {
    var k = d.kpis;
    el('kpiGrid').innerHTML =
      kpiCard('bi-people', '#1d4ed8', fmtInt(k.total_visitors), 'Total Visitors', 'All time', '#dbeafe') +
      kpiCard('bi-calendar-day', '#166534', fmtInt(k.today_visitors), "Today's Visitors", dateLabel(), '#dcfce7') +
      kpiCard('bi-broadcast', '#b91c1c', fmtInt(k.active_now), 'Active Now', 'Last 5 minutes', '#fee2e2') +
      kpiCard('bi-person-badge', '#7e22ce', fmtInt(k.unique_visitors), 'Unique Visitors', 'Period', '#f3e8ff') +
      kpiCard('bi-person-check', '#b45309', fmtInt(k.returning_visitors), 'Returning Visitors', 'Period', '#fef3c7') +
      kpiCard('bi-eye', '#0f766e', fmtInt(k.total_views), 'Total Page Views', 'Period', '#ccfbf1') +
      kpiCard('bi-arrow-90deg-left', '#be123c', fmtPct(k.bounce_rate), 'Bounce Rate', 'Single-page sessions', '#ffe4e6') +
      kpiCard('bi-clock-history', '#4338ca', fmtDur(k.avg_duration), 'Avg Session Duration', 'Per session', '#e0e7ff') +
      kpiCard('bi-percent', '#15803d', fmtPct(k.conversion_rate, 2), 'Conversion Rate', fmtInt(k.conversions) + ' conversions', '#dcfce7');
  }
  function dateLabel() {
    try { return new Date().toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' }); }
    catch (e) { return ''; }
  }

  /* ── charts ────────────────────────────────────────────────── */
  function renderCharts(d) {
    /* Daily visitors trend (line) */
    var labels = d.daily.map(function (x) { return x.date.slice(5); });
    makeChart('dailyTrend', {
      type: 'line',
      data: {
        labels: labels,
        datasets: [
          { label: 'Visitors', data: d.daily.map(function (x) { return x.visitors; }), borderColor: PALETTE[0], backgroundColor: 'rgba(37,99,235,.12)', fill: true, tension: .35, pointRadius: 2 },
          { label: 'Page Views', data: d.daily.map(function (x) { return x.views; }), borderColor: PALETTE[1], backgroundColor: 'rgba(6,182,212,.1)', fill: true, tension: .35, pointRadius: 2 }
        ]
      },
      options: lineOpts()
    });

    /* Device pie */
    makeChart('devicePie', {
      type: 'pie',
      data: { labels: d.devices.map(function (x) { return x.label; }), datasets: [{ data: d.devices.map(function (x) { return x.value; }), backgroundColor: PALETTE }] },
      options: { plugins: { legend: { position: 'bottom' } } }
    });

    /* Traffic by source (bar) */
    makeChart('sourceBar', {
      type: 'bar',
      data: { labels: d.sources.map(function (x) { return x.label; }), datasets: [
        { label: 'Sessions', data: d.sources.map(function (x) { return x.sessions; }), backgroundColor: PALETTE[0], borderRadius: 4 },
        { label: 'Views', data: d.sources.map(function (x) { return x.views; }), backgroundColor: PALETTE[1], borderRadius: 4 }
      ] },
      options: { plugins: { legend: { position: 'top' } }, scales: { x: { grid: { display: false }, ticks: { font: { size: 10 } } }, y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { precision: 0 } } } }
    });

    /* Monthly growth */
    makeChart('monthlyGrowth', {
      type: 'line',
      data: { labels: d.monthly.map(function (x) { return x.m; }), datasets: [
        { label: 'Visitors', data: d.monthly.map(function (x) { return x.visitors; }), borderColor: '#8b5cf6', backgroundColor: 'rgba(139,92,246,.12)', fill: true, tension: .35, pointRadius: 3 },
        { label: 'Sessions', data: d.monthly.map(function (x) { return x.sessions; }), borderColor: '#f59e0b', fill: false, tension: .35, pointRadius: 3 }
      ] },
      options: lineOpts()
    });

    /* Browser pie */
    makeChart('browserPie', {
      type: 'pie',
      data: { labels: d.browsers.map(function (x) { return x.label; }), datasets: [{ data: d.browsers.map(function (x) { return x.value; }), backgroundColor: PALETTE }] },
      options: { plugins: { legend: { position: 'bottom' } } }
    });

    /* Top landing pages */
    makeChart('landingBar', {
      type: 'bar',
      data: { labels: d.landing_pages.map(function (x) { return shortUrl(x.page); }), datasets: [
        { label: 'Sessions', data: d.landing_pages.map(function (x) { return x.sessions; }), backgroundColor: PALETTE[2], borderRadius: 4 }
      ] },
      options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { precision: 0 } }, y: { grid: { display: false }, ticks: { font: { size: 10 } } } } }
    });

    renderPeriodBar(d);
    renderVisitorsCharts(d);
    renderCampaignCharts(d);
    renderPageCharts(d);
    renderConvCharts(d);
  }

  /* Visitors tab */
  function renderPeriodBar(d) {
    var p = state.period;
    var labels, visitors, sessions, title;
    if (p === 'daily') { labels = d.daily.map(function (x) { return x.date.slice(5); }); visitors = d.daily.map(function (x) { return x.visitors; }); sessions = d.daily.map(function (x) { return x.sessions; }); }
    else if (p === 'weekly') { labels = d.weekly.map(function (x) { return x.d ? x.d.slice(5) : x.yw; }); visitors = d.weekly.map(function (x) { return x.visitors; }); sessions = d.weekly.map(function (x) { return x.sessions; }); }
    else if (p === 'monthly') { labels = d.monthly.map(function (x) { return x.m; }); visitors = d.monthly.map(function (x) { return x.visitors; }); sessions = d.monthly.map(function (x) { return x.sessions; }); }
    else { labels = d.yearly.map(function (x) { return String(x.y); }); visitors = d.yearly.map(function (x) { return x.visitors; }); sessions = d.yearly.map(function (x) { return x.sessions; }); }
    makeChart('periodBar', {
      type: 'bar',
      data: { labels: labels, datasets: [
        { label: 'Visitors', data: visitors, backgroundColor: PALETTE[0], borderRadius: 4 },
        { label: 'Sessions', data: sessions, backgroundColor: PALETTE[3], borderRadius: 4 }
      ] },
      options: { plugins: { legend: { position: 'top' } }, scales: { x: { grid: { display: false }, ticks: { maxRotation: 45, font: { size: 10 } } }, y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { precision: 0 } } } }
    });
  }

  function renderVisitorsCharts(d) {
    makeChart('newReturningPie', {
      type: 'doughnut',
      data: { labels: ['New Visitors', 'Returning Visitors'], datasets: [{ data: [d.kpis.new_visitors, d.kpis.returning_visitors], backgroundColor: [PALETTE[0], PALETTE[4]] }] },
      options: { plugins: { legend: { position: 'bottom' } } }
    });
    makeChart('osBar', {
      type: 'bar',
      data: { labels: d.os.map(function (x) { return x.label; }), datasets: [{ label: 'Sessions', data: d.os.map(function (x) { return x.value; }), backgroundColor: PALETTE[5], borderRadius: 4 }] },
      options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, grid: { color: '#f1f5f9' } }, y: { grid: { display: false }, ticks: { font: { size: 10 } } } } }
    });
    makeChart('geoCountryPie', {
      type: 'pie',
      data: { labels: d.countries.map(function (x) { return x.label; }), datasets: [{ data: d.countries.map(function (x) { return x.value; }), backgroundColor: PALETTE }] },
      options: { plugins: { legend: { position: 'bottom' } } }
    });
    makeChart('cityBar', {
      type: 'bar',
      data: { labels: d.cities.map(function (x) { return x.label; }), datasets: [{ label: 'Sessions', data: d.cities.map(function (x) { return x.value; }), backgroundColor: PALETTE[6], borderRadius: 4 }] },
      options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, grid: { color: '#f1f5f9' } }, y: { grid: { display: false }, ticks: { font: { size: 10 } } } } }
    });
  }

  /* Campaigns tab */
  function renderCampaignCharts(d) {
    makeChart('channelPie', {
      type: 'pie',
      data: { labels: d.channels.map(function (x) { return x.label; }), datasets: [{ data: d.channels.map(function (x) { return x.value; }), backgroundColor: PALETTE }] },
      options: { plugins: { legend: { position: 'bottom' } } }
    });
    makeChart('sourceBar2', {
      type: 'bar',
      data: { labels: d.sources.map(function (x) { return x.label; }), datasets: [{ label: 'Sessions', data: d.sources.map(function (x) { return x.sessions; }), backgroundColor: PALETTE[0], borderRadius: 4 }] },
      options: { plugins: { legend: { display: false } }, scales: { x: { grid: { display: false }, ticks: { font: { size: 10 } } }, y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { precision: 0 } } } }
    });
    makeChart('mediumBar', {
      type: 'bar',
      data: { labels: d.mediums.map(function (x) { return x.label; }), datasets: [{ label: 'Sessions', data: d.mediums.map(function (x) { return x.sessions; }), backgroundColor: PALETTE[3], borderRadius: 4 }] },
      options: { plugins: { legend: { display: false } }, scales: { x: { grid: { display: false }, ticks: { font: { size: 10 } } }, y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { precision: 0 } } } }
    });
  }

  /* Pages tab */
  function renderPageCharts(d) {
    makeChart('landingBar2', {
      type: 'bar',
      data: { labels: d.landing_pages.map(function (x) { return shortUrl(x.page); }), datasets: [{ label: 'Sessions', data: d.landing_pages.map(function (x) { return x.sessions; }), backgroundColor: PALETTE[2], borderRadius: 4 }] },
      options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, grid: { color: '#f1f5f9' } }, y: { grid: { display: false }, ticks: { font: { size: 10 } } } } }
    });
    makeChart('exitBar', {
      type: 'bar',
      data: { labels: d.exit_pages.map(function (x) { return shortUrl(x.page); }), datasets: [{ label: 'Exits', data: d.exit_pages.map(function (x) { return x.sessions; }), backgroundColor: PALETTE[7], borderRadius: 4 }] },
      options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, grid: { color: '#f1f5f9' } }, y: { grid: { display: false }, ticks: { font: { size: 10 } } } } }
    });
    makeChart('topPagesBar', {
      type: 'bar',
      data: { labels: d.top_pages.map(function (x) { return shortUrl(x.page); }), datasets: [
        { label: 'Views', data: d.top_pages.map(function (x) { return x.views; }), backgroundColor: PALETTE[0], borderRadius: 4 },
        { label: 'Visitors', data: d.top_pages.map(function (x) { return x.visitors; }), backgroundColor: PALETTE[4], borderRadius: 4 }
      ] },
      options: { plugins: { legend: { position: 'top' } }, scales: { x: { grid: { display: false }, ticks: { font: { size: 10 } } }, y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { precision: 0 } } } }
    });
  }

  /* Conversions tab */
  function renderConvCharts(d) {
    makeChart('convTrend', {
      type: 'line',
      data: { labels: d.conversions.over_time.map(function (x) { return x.date.slice(5); }), datasets: [{ label: 'Conversions', data: d.conversions.over_time.map(function (x) { return x.count; }), borderColor: PALETTE[4], backgroundColor: 'rgba(16,185,129,.14)', fill: true, tension: .35, pointRadius: 3 }] },
      options: lineOpts()
    });
    makeChart('convTypePie', {
      type: 'doughnut',
      data: { labels: d.conversions.by_type.map(function (x) { return CONV_LABELS[x.event_type] || x.event_type; }), datasets: [{ data: d.conversions.by_type.map(function (x) { return x.c; }), backgroundColor: PALETTE }] },
      options: { plugins: { legend: { position: 'bottom' } } }
    });
  }

  /* ── Tables ────────────────────────────────────────────────── */
  function renderRecent(d) {
    var rows = d.recent;
    el('recentCount').textContent = '(' + rows.length + ' sessions)';
    var h = '<thead><tr><th>Channel</th><th>Page</th><th>Device / Browser / OS</th><th>Country / City</th><th>Views</th><th>Duration</th><th>Bounce</th><th>Last Activity</th></tr></thead><tbody>';
    if (!rows.length) {
      h += '<tr><td colspan="8" style="text-align:center;color:#94a3b8;padding:1.5rem">No sessions recorded yet in this period.</td></tr>';
    }
    rows.forEach(function (r) {
      h += '<tr>' +
        '<td>' + chip(r.channel || 'Unknown') + '</td>' +
        '<td style="max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">' + esc(shortUrl(r.exit_page || r.landing_page || '/')) + '</td>' +
        '<td>' + esc(r.device || '-') + ' · ' + esc(r.browser || '-') + ' · ' + esc(r.os || '-') + '</td>' +
        '<td>' + esc(r.country || '-') + (r.city ? ' / ' + esc(r.city) : '') + '</td>' +
        '<td>' + fmtInt(r.page_views) + '</td>' +
        '<td>' + fmtDur(r.duration_sec) + '</td>' +
        '<td>' + (Number(r.is_bounce) ? '<span class="pill pill-red">Bounced</span>' : '<span class="pill pill-green">Engaged</span>') + '</td>' +
        '<td>' + esc(r.last_activity || '-') + '</td>' +
        '</tr>';
    });
    h += '</tbody>';
    el('recentTable').innerHTML = h;
  }

  function renderCampaigns(d) {
    var rows = d.campaigns;
    var h = '<thead><tr><th>Campaign</th><th>Source</th><th>Medium</th><th>Sessions</th><th>Views</th><th>Conversions</th><th>Cost (₹)</th><th>ROI %</th></tr></thead><tbody>';
    if (!rows.length) {
      h += '<tr><td colspan="8" style="text-align:center;color:#94a3b8;padding:1.5rem">No UTM campaigns recorded yet. Add ?utm_source=facebook&utm_campaign=xxx to your links.</td></tr>';
    }
    rows.forEach(function (r) {
      h += '<tr>' +
        '<td style="font-weight:700">' + esc(r.campaign) + '</td>' +
        '<td>' + chip(r.source || r.medium || '—') + '</td>' +
        '<td>' + esc(r.medium || '-') + '</td>' +
        '<td>' + fmtInt(r.sessions) + '</td>' +
        '<td>' + fmtInt(r.views) + '</td>' +
        '<td>' + fmtInt(r.conversions) + '</td>' +
        '<td><input type="number" class="cost-input" data-campaign="' + esc(r.campaign) + '" value="' + (r.cost || 0) + '" min="0" step="100" /></td>' +
        '<td>' + (r.roi === null ? '<span class="muted">—</span>' : '<strong>' + fmtInt(r.roi) + '%</strong>') + '</td>' +
        '</tr>';
    });
    h += '</tbody>';
    el('campaignTable').innerHTML = h;

    el('campaignTable').querySelectorAll('.cost-input').forEach(function (inp) {
      inp.addEventListener('change', function () {
        var body = new FormData();
        body.append('campaign', inp.dataset.campaign);
        body.append('cost', inp.value || 0);
        fetch(API + '?action=save_cost', { method: 'POST', body: body, credentials: 'same-origin' })
          .then(function (r) { return r.json(); })
          .then(function (j) { if (j.ok) refresh(); });
      });
    });
  }

  function renderSocial(d) {
    var order = ['Facebook', 'Instagram', 'YouTube', 'LinkedIn', 'WhatsApp', 'Email', 'QR Code', 'Twitter'];
    var map = {};
    d.social.forEach(function (s) { map[s.channel] = s; });
    var h = '';
    order.forEach(function (ch) {
      var meta = SOCIAL_META[ch];
      if (!meta) return;
      var s = map[ch];
      if (!s) return;
      h += '<div class="social-card">' +
        '<div class="sc-icon" style="background:' + meta[1] + '"><i class="bi ' + meta[0] + '"></i></div>' +
        '<div><div class="sc-name">' + ch + '</div>' +
        '<div class="sc-stats"><span><b>' + fmtInt(s.sessions) + '</b> sessions</span><span><b>' + fmtInt(s.views) + '</b> views</span><span><b>' + fmtInt(s.conversions) + '</b> conv.</span></div></div>' +
        '</div>';
    });
    if (!h) h = '<div class="muted" style="padding:.5rem 0">No social traffic recorded yet in this period.</div>';
    el('socialGrid').innerHTML = h;
  }

  function renderPages(d) {
    var all = {};
    d.top_pages.forEach(function (p) { all[p.page] = { views: p.views, visitors: p.visitors, landings: 0, exits: 0 }; });
    d.landing_pages.forEach(function (p) { if (!all[p.page]) all[p.page] = { views: 0, visitors: 0, landings: 0, exits: 0 }; all[p.page].landings = p.sessions; });
    d.exit_pages.forEach(function (p) { if (!all[p.page]) all[p.page] = { views: 0, visitors: 0, landings: 0, exits: 0 }; all[p.page].exits = p.sessions; });
    var sorted = Object.keys(all).sort(function (a, b) { return all[b].views - all[a].views; });
    var h = '<thead><tr><th>Page</th><th>Views</th><th>Visitors</th><th>Landings</th><th>Exits</th></tr></thead><tbody>';
    if (!sorted.length) h += '<tr><td colspan="5" style="text-align:center;color:#94a3b8;padding:1.5rem">No page data yet.</td></tr>';
    sorted.slice(0, 12).forEach(function (p) {
      var row = all[p];
      h += '<tr><td style="max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-weight:600">' + esc(shortUrl(p)) + '</td>' +
        '<td>' + fmtInt(row.views) + '</td><td>' + fmtInt(row.visitors) + '</td>' +
        '<td>' + fmtInt(row.landings) + '</td><td>' + fmtInt(row.exits) + '</td></tr>';
    });
    h += '</tbody>';
    el('pageTable').innerHTML = h;
  }

  function renderConversions(d) {
    var c = d.conversions;
    el('convKpi').innerHTML =
      kpiCard('bi-check-circle', '#166534', fmtInt(c.total), 'Total Conversions', 'Period', '#dcfce7') +
      kpiCard('bi-percent', '#1d4ed8', fmtPct(c.rate, 2), 'Conversion Rate', 'vs sessions', '#dbeafe') +
      kpiCard('bi-chat-square-text', '#0f766e', fmtInt(countType(c, 'contact_form')), 'Contact Forms', '', '#ccfbf1') +
      kpiCard('bi-whatsapp', '#15803d', fmtInt(countType(c, 'whatsapp_click')), 'WhatsApp Clicks', '', '#dcfce7');
    var h = '<thead><tr><th>Conversion Type</th><th>Count</th><th>Share</th></tr></thead><tbody>';
    c.by_type.forEach(function (r) {
      var pct = c.total ? (r.c / c.total * 100).toFixed(1) : 0;
      h += '<tr><td style="font-weight:600">' + esc(CONV_LABELS[r.event_type] || r.event_type) + '</td><td>' + fmtInt(r.c) + '</td><td>' + pct + '%</td></tr>';
    });
    if (!c.by_type.length) h += '<tr><td colspan="3" style="text-align:center;color:#94a3b8;padding:1.5rem">No conversions yet in this period.</td></tr>';
    h += '</tbody>';
    el('convTable').innerHTML = h;
  }
  function countType(c, t) {
    var f = c.by_type.filter(function (r) { return r.event_type === t; });
    return f.length ? f[0].c : 0;
  }

  function renderSummary(d) {
    var items = [
      ['Total Sessions', fmtInt(d.kpis.sessions)],
      ['Unique Visitors', fmtInt(d.kpis.unique_visitors)],
      ['Returning Visitors', fmtInt(d.kpis.returning_visitors)],
      ['Page Views', fmtInt(d.kpis.total_views)],
      ['Bounce Rate', fmtPct(d.kpis.bounce_rate)],
      ['Avg Duration', fmtDur(d.kpis.avg_duration)],
      ['Conversions', fmtInt(d.conversions.total)],
      ['Conversion Rate', fmtPct(d.conversions.rate, 2)],
      ['Active Now', fmtInt(d.kpis.active_now)]
    ];
    el('summaryGrid').innerHTML = items.map(function (it) {
      return '<div class="summary-item"><span class="si-label">' + it[0] + '</span><span class="si-value">' + it[1] + '</span></div>';
    }).join('');
  }

  /* ── Live ──────────────────────────────────────────────────── */
  function renderLiveKpi(d) {
    el('liveKpi').innerHTML =
      kpiCard('bi-broadcast', '#b91c1c', fmtInt(d.kpis.active_now), 'Active Now', 'Last 5 minutes', '#fee2e2') +
      kpiCard('bi-people', '#1d4ed8', fmtInt(d.kpis.today_visitors), "Today's Visitors", '', '#dbeafe') +
      kpiCard('bi-person-badge', '#7e22ce', fmtInt(d.kpis.unique_visitors), 'Unique Visitors', 'Period', '#f3e8ff') +
      kpiCard('bi-check-circle', '#166534', fmtInt(d.conversions.total), 'Conversions', 'Period', '#dcfce7');
  }

  function pollLive() {
    fetch(API + '?action=live', { credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (d) {
      if (!d || d.error) return;
      var dot = el('liveDot');
      if (dot) { dot.classList.toggle('on', d.active_count > 0); }

      var kpi = el('liveKpi');
      if (kpi) kpi.innerHTML =
        kpiCard('bi-broadcast', '#b91c1c', fmtInt(d.active_count), 'Active Now', 'Last 5 minutes', '#fee2e2') +
        kpiCard('bi-people', '#1d4ed8', fmtInt(d.total_visitors), 'Total Visitors', 'All time', '#dbeafe') +
        kpiCard('bi-alarm', '#b45309', fmtInt(state.data ? state.data.kpis.today_visitors : 0), "Today's Visitors", '', '#fef3c7') +
        kpiCard('bi-check-circle', '#166534', fmtInt(state.data ? state.data.conversions.total : 0), 'Conversions', 'Period', '#dcfce7');

      /* rolling series */
      state.liveSeries.push({ t: new Date().toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit', second: '2-digit' }), v: d.active_count });
      if (state.liveSeries.length > 30) state.liveSeries.shift();
      makeChart('liveTrend', {
        type: 'line',
        data: { labels: state.liveSeries.map(function (x) { return x.t; }), datasets: [{ label: 'Active Visitors', data: state.liveSeries.map(function (x) { return x.v; }), borderColor: '#ef4444', backgroundColor: 'rgba(239,68,68,.14)', fill: true, tension: .3, pointRadius: 2 }] },
        options: lineOpts()
      });

      var badge = el('liveBadge');
      if (badge) badge.textContent = d.active_count + ' active now';
      var lbl = el('liveActiveLabel');
      if (lbl) lbl.textContent = '(' + d.active.length + ' sessions in last 5 min)';

      var h = '<thead><tr><th>Channel</th><th>Campaign</th><th>Page</th><th>Device</th><th>City</th><th>Views</th><th>Last Activity</th></tr></thead><tbody>';
      if (!d.active.length) h += '<tr><td colspan="7" style="text-align:center;color:#94a3b8;padding:1.5rem">No active visitors right now.</td></tr>';
      d.active.forEach(function (r) {
        h += '<tr>' +
          '<td>' + chip(r.channel || 'Unknown') + '</td>' +
          '<td>' + esc(r.campaign || '—') + '</td>' +
          '<td style="max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">' + esc(shortUrl(r.exit_page || r.landing_page || '/')) + '</td>' +
          '<td>' + esc(r.device || '-') + '</td>' +
          '<td>' + esc(r.city || r.country || '-') + '</td>' +
          '<td>' + fmtInt(r.page_views) + '</td>' +
          '<td>' + esc((r.last_activity || '').slice(11)) + '</td>' +
          '</tr>';
      });
      h += '</tbody>';
      var t = el('liveTable');
      if (t) t.innerHTML = h;
    }).catch(function () {});
  }

  /* ── Tabs ──────────────────────────────────────────────────── */
  function switchTab(name) {
    document.querySelectorAll('.nav-item').forEach(function (b) { b.classList.toggle('active', b.dataset.tab === name); });
    document.querySelectorAll('.tab-panel').forEach(function (p) { p.classList.toggle('active', p.id === 'tab-' + name); });
    var titles = {
      overview: 'Overview', visitors: 'Visitors', campaigns: 'Campaigns', pages: 'Pages',
      conversions: 'Conversions', live: 'Live Visitors', reports: 'Reports'
    };
    el('pageTitle').textContent = titles[name] || name;
    Object.keys(charts).forEach(function (k) { if (charts[k]) charts[k].resize(); });
    if (name === 'live') startLive();
    else stopLive();
  }

  function startLive() {
    pollLive();
    if (!state.liveInterval) state.liveInterval = setInterval(pollLive, 5000);
  }
  function stopLive() {
    if (state.liveInterval) { clearInterval(state.liveInterval); state.liveInterval = null; }
  }

  /* ── init ──────────────────────────────────────────────────── */
  document.addEventListener('DOMContentLoaded', function () {
    state.from = el('fromDate').value;
    state.to = el('toDate').value;

    document.querySelectorAll('.nav-item[data-tab]').forEach(function (b) {
      b.addEventListener('click', function () { switchTab(b.dataset.tab); });
    });

    el('applyRange').addEventListener('click', function () {
      state.from = el('fromDate').value;
      state.to = el('toDate').value;
      function href(fmt) { return 'export.php?format=' + fmt + '&from=' + state.from + '&to=' + state.to; }
      el('btnExcel').href = href('excel');
      el('btnPdf').href = href('pdf');
      el('btnCsv').href = href('csv');
      el('repExcel').href = href('excel');
      el('repPdf').href = href('pdf');
      el('repCsv').href = href('csv');
      refresh();
    });

    el('periodSeg').addEventListener('click', function (e) {
      var btn = e.target.closest('button');
      if (!btn) return;
      state.period = btn.dataset.period;
      document.querySelectorAll('#periodSeg button').forEach(function (b) { b.classList.toggle('active', b === btn); });
      if (state.data) renderPeriodBar(state.data);
    });

    refresh();
  });
})();
