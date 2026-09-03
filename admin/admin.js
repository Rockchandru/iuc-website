/* ═══════════════════════════════════════════════════════════════
   IUC Edu — Admin Analytics JS
   ═══════════════════════════════════════════════════════════════ */
(function () {
  'use strict';

  var API = (typeof ADMIN_BASE !== 'undefined' ? ADMIN_BASE : '/admin') + '/api.php';
  var charts = {};
  var state = {
    from: '', to: '', data: null, period: 'daily', liveSeries: [], liveInterval: null,
    activeTab: 'overview', gscData: null, gscLoaded: false, gscLoading: false
  };

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
      renderSeo(d);
      renderEnquiries(d);
      renderHealth(d);
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
    var h = '<thead><tr><th>Campaign</th><th>Source / Medium</th><th>Landing Page</th><th>First / Last Visit</th><th>Sessions</th><th>Views</th><th>Conversions</th><th>Cost (₹)</th><th>Note</th><th>Save</th></tr></thead><tbody>';
    if (!rows.length) {
      h += '<tr><td colspan="10" style="text-align:center;color:#94a3b8;padding:1.5rem">No UTM campaigns recorded yet. Add ?utm_source=facebook&amp;utm_medium=social&amp;utm_campaign=course_name to campaign links.</td></tr>';
    }
    rows.forEach(function (r) {
      h += '<tr>' +
        '<td style="font-weight:700">' + esc(r.campaign) + '</td>' +
        '<td>' + chip(r.source || 'Unknown') + '<div class="cell-sub">' + esc(r.medium || '-') + '</div></td>' +
        '<td class="url-cell" title="' + esc(r.landing_page || '') + '">' + esc(shortUrl(r.landing_page || '/')) + '</td>' +
        '<td><span>' + esc(r.first_seen || '-') + '</span><div class="cell-sub">' + esc(r.last_seen || '-') + '</div></td>' +
        '<td>' + fmtInt(r.sessions) + '</td>' +
        '<td>' + fmtInt(r.views) + '</td>' +
        '<td>' + fmtInt(r.conversions) + '</td>' +
        '<td><input type="number" class="cost-input" data-campaign="' + esc(r.campaign) + '" value="' + (r.cost || 0) + '" min="0" step="100" /></td>' +
        '<td><input type="text" class="note-input" value="' + esc(r.note || '') + '" maxlength="255" placeholder="Campaign note" /></td>' +
        '<td><button type="button" class="btn btn-primary campaign-save">Save</button><div class="save-state">' + (r.meta_updated_at ? esc(r.meta_updated_at) : '') + '</div></td>' +
        '</tr>';
    });
    h += '</tbody>';
    el('campaignTable').innerHTML = h;

    el('campaignTable').querySelectorAll('.campaign-save').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var row = btn.closest('tr');
        var inp = row.querySelector('.cost-input');
        var note = row.querySelector('.note-input');
        var campaignRow = rows.filter(function (x) { return x.campaign === inp.dataset.campaign; })[0] || {};
        var body = new FormData();
        body.append('campaign', inp.dataset.campaign);
        body.append('cost', inp.value || 0);
        body.append('landing_page', campaignRow.landing_page || '');
        body.append('note', note.value || '');
        body.append('csrf_token', typeof ADMIN_CSRF !== 'undefined' ? ADMIN_CSRF : '');
        btn.disabled = true;
        btn.textContent = 'Saving…';
        fetch(API + '?action=save_cost', { method: 'POST', body: body, credentials: 'same-origin' })
          .then(function (r) { return r.json(); })
          .then(function (j) {
            if (!j.ok) throw new Error(j.error || 'Save failed');
            btn.textContent = 'Saved';
            setTimeout(refresh, 500);
          })
          .catch(function (e) {
            btn.disabled = false;
            btn.textContent = 'Retry';
            row.querySelector('.save-state').textContent = e.message;
          });
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

  function renderSeo(d) {
    var s = d.seo || { keywords: [], engines: [], landing_pages: [] };
    el('seoKpi').innerHTML =
      kpiCard('bi-search', '#1d4ed8', fmtInt(s.organic_sessions), 'Organic Search', 'Sessions', '#dbeafe') +
      kpiCard('bi-badge-ad', '#7e22ce', fmtInt(s.paid_search_sessions), 'Paid Search', 'Google Ads sessions', '#f3e8ff') +
      kpiCard('bi-key', '#166534', fmtInt(s.known_keyword_sessions), 'Known Keywords', 'UTM/referrer sessions', '#dcfce7') +
      kpiCard('bi-eye-slash', '#b45309', fmtInt(s.not_provided_sessions), 'Not Provided', 'Hidden by search engines', '#fef3c7');
    makeChart('seoKeywordBar', {
      type: 'bar',
      data: { labels: s.keywords.slice(0, 12).map(function (x) { return x.keyword; }), datasets: [{ label: 'Sessions', data: s.keywords.slice(0, 12).map(function (x) { return x.sessions; }), backgroundColor: PALETTE[4], borderRadius: 4 }] },
      options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { precision: 0 } }, y: { grid: { display: false } } } }
    });
    makeChart('searchEnginePie', {
      type: 'doughnut',
      data: { labels: s.engines.map(function (x) { return x.engine; }), datasets: [{ data: s.engines.map(function (x) { return x.sessions; }), backgroundColor: PALETTE }] },
      options: { plugins: { legend: { position: 'bottom' } } }
    });

    var kh = '<thead><tr><th>Keyword</th><th>Engine / Source</th><th>Landing Page</th><th>Sessions</th><th>Visitors</th><th>Views</th><th>Conversions</th><th>Last Seen</th></tr></thead><tbody>';
    if (!s.keywords.length) kh += '<tr><td colspan="8" class="empty-cell">No visible keyword data yet. Use utm_term on managed campaign links.</td></tr>';
    s.keywords.forEach(function (r) {
      kh += '<tr><td style="font-weight:700">' + esc(r.keyword) + '</td><td>' + esc(r.engine || '-') + '</td>' +
        '<td class="url-cell" title="' + esc(r.landing_page || '') + '">' + esc(shortUrl(r.landing_page || '/')) + '</td>' +
        '<td>' + fmtInt(r.sessions) + '</td><td>' + fmtInt(r.visitors) + '</td><td>' + fmtInt(r.views) + '</td>' +
        '<td>' + fmtInt(r.conversions) + '</td><td>' + esc(r.last_seen || '-') + '</td></tr>';
    });
    kh += '</tbody>';
    el('seoKeywordTable').innerHTML = kh;

    var lh = '<thead><tr><th>Search Landing Page</th><th>Sessions</th><th>Visitors</th><th>Sessions with Known Keyword</th></tr></thead><tbody>';
    if (!s.landing_pages.length) lh += '<tr><td colspan="4" class="empty-cell">No search landing-page traffic recorded in this period.</td></tr>';
    s.landing_pages.forEach(function (r) {
      lh += '<tr><td class="url-cell" title="' + esc(r.page || '') + '">' + esc(shortUrl(r.page || '/')) + '</td><td>' + fmtInt(r.sessions) + '</td><td>' + fmtInt(r.visitors) + '</td><td>' + fmtInt(r.known_keywords) + '</td></tr>';
    });
    lh += '</tbody>';
    el('seoLandingTable').innerHTML = lh;
  }

  /* ── Google Search Console performance ───────────────────── */
  function gscDelta(change, mode) {
    if (!change) return '<span class="metric-delta flat">No comparison</span>';
    var value = Number(change.absolute || 0);
    var cls = value > 0 ? 'up' : (value < 0 ? 'down' : 'flat');
    var icon = value > 0 ? 'bi-arrow-up-right' : (value < 0 ? 'bi-arrow-down-right' : 'bi-dash');
    var label;
    if (mode === 'ctr') label = (value * 100).toFixed(2) + ' pp';
    else if (mode === 'position') label = Math.abs(value).toFixed(1) + ' positions';
    else if (change.percent !== null && change.percent !== undefined) label = Math.abs(Number(change.percent)).toFixed(1) + '%';
    else label = Math.abs(value).toFixed(0);
    return '<span class="metric-delta ' + cls + '"><i class="bi ' + icon + '"></i> ' + label + '</span>';
  }

  function gscMetricRowCells(row) {
    return '<td>' + fmtInt(row.clicks) + '</td>' +
      '<td>' + fmtInt(row.impressions) + '</td>' +
      '<td>' + fmtPct(Number(row.ctr || 0) * 100, 2) + '</td>' +
      '<td>' + Number(row.position || 0).toFixed(1) + '</td>';
  }

  function setGscStatus(type, title, detail) {
    var box = el('gscStatus');
    box.className = 'gsc-status ' + type;
    var icon = type === 'is-ready' ? 'bi-cloud-check' : (type === 'is-error' ? 'bi-exclamation-triangle' : 'bi-cloud-arrow-down');
    box.innerHTML = '<i class="bi ' + icon + '"></i><div><strong>' + esc(title) + '</strong><span>' + detail + '</span></div>';
  }

  function showGscConnectionControls(showConnect, sessionActive, property) {
    var card = el('gscConnectCard');
    var sessionActions = el('gscSessionActions');
    if (card) card.hidden = !showConnect;
    if (sessionActions) sessionActions.hidden = !sessionActive;
    if (showConnect && property && el('gscProperty')) el('gscProperty').value = property;
  }

  function loadGsc(force) {
    if (state.gscLoading) return;
    if (state.gscLoaded && !force && state.gscData) {
      renderGsc(state.gscData);
      return;
    }
    state.gscLoading = true;
    var refreshButton = el('gscRefresh');
    if (refreshButton) { refreshButton.disabled = true; refreshButton.classList.add('is-loading'); }
    setGscStatus('is-loading', 'Loading Search Console', 'Requesting the selected date range and previous-period comparison…');
    var url = API + '?action=gsc_performance&from=' + encodeURIComponent(state.from) + '&to=' + encodeURIComponent(state.to)
      + (force ? '&refresh=1' : '');
    fetch(url, { credentials: 'same-origin' })
      .then(function (response) { return response.json(); })
      .then(function (data) {
        state.gscLoading = false;
        if (refreshButton) { refreshButton.disabled = false; refreshButton.classList.remove('is-loading'); }
        if (!data || !data.ok) {
          renderGscUnavailable(data || { error: 'Search Console returned no data.' });
          return;
        }
        state.gscData = data;
        state.gscLoaded = true;
        renderGsc(data);
      })
      .catch(function (error) {
        state.gscLoading = false;
        if (refreshButton) { refreshButton.disabled = false; refreshButton.classList.remove('is-loading'); }
        renderGscUnavailable({ configured: true, error: error.message || 'Search Console request failed.' });
      });
  }

  function renderGscUnavailable(data) {
    state.gscLoaded = false;
    state.gscData = null;
    el('gscKpi').innerHTML = '';
    ['gscQueryTable', 'gscOpportunityTable', 'gscPageTable', 'gscDeviceTable', 'gscCountryTable', 'gscAppearanceTable'].forEach(function (id) {
      var table = el(id);
      if (table) table.innerHTML = '';
    });
    ['gscDailyTrend', 'gscBrandPie'].forEach(function (id) {
      if (charts[id]) { charts[id].destroy(); delete charts[id]; }
    });
    var source = data && data.credential_source ? data.credential_source : 'none';
    showGscConnectionControls(source === 'none' || source === 'session', source === 'session', data && data.property);
    if (data && data.configured === false) {
      var steps = (data.setup || []).map(function (step) { return '<li>' + esc(step) + '</li>'; }).join('');
      setGscStatus('is-error', 'Search Console connection required', esc(data.error || '') + (steps ? '<ol class="gsc-setup-list">' + steps + '</ol>' : ''));
    } else {
      var failed = data && data.failed_dataset ? ' Dataset: ' + esc(data.failed_dataset) + '.' : '';
      setGscStatus('is-error', 'Search Console unavailable', esc((data && data.error) || 'Unable to load keyword data.') + failed);
    }
  }

  function renderGsc(data) {
    showGscConnectionControls(false, data.credential_source === 'session', data.property);
    var cacheText = data.cache && data.cache.hit ? 'cached' : 'fresh';
    var warningText = (data.warnings || []).length ? ' · ' + esc(data.warnings.join(' ')) : '';
    setGscStatus(
      'is-ready',
      'Connected: ' + data.property,
      esc(data.range.from + ' to ' + data.range.to + ' · compared with ' + data.comparison_range.from + ' to ' + data.comparison_range.to + ' · ' + cacheText) + warningText
    );
    var s = data.summary || {};
    var c = data.changes || {};
    el('gscKpi').innerHTML =
      kpiCard('bi-cursor', '#1d4ed8', fmtInt(s.clicks), 'Google Clicks', gscDelta(c.clicks), '#dbeafe') +
      kpiCard('bi-eye', '#7e22ce', fmtInt(s.impressions), 'Impressions', gscDelta(c.impressions), '#f3e8ff') +
      kpiCard('bi-percent', '#166534', fmtPct(Number(s.ctr || 0) * 100, 2), 'Average CTR', gscDelta(c.ctr, 'ctr'), '#dcfce7') +
      kpiCard('bi-trophy', '#b45309', Number(s.position || 0).toFixed(1), 'Average Position', gscDelta(c.position, 'position'), '#fef3c7');

    makeChart('gscDailyTrend', {
      type: 'line',
      data: {
        labels: (data.daily || []).map(function (row) { return (row.date || '').slice(5); }),
        datasets: [
          { label: 'Clicks', data: (data.daily || []).map(function (row) { return row.clicks; }), borderColor: '#2563eb', backgroundColor: 'rgba(37,99,235,.12)', fill: true, tension: .3, yAxisID: 'y' },
          { label: 'Impressions', data: (data.daily || []).map(function (row) { return row.impressions; }), borderColor: '#8b5cf6', backgroundColor: 'rgba(139,92,246,.08)', tension: .3, yAxisID: 'y1' }
        ]
      },
      options: {
        plugins: { legend: { position: 'bottom' } },
        scales: {
          x: { grid: { display: false } },
          y: { beginAtZero: true, position: 'left', ticks: { precision: 0 } },
          y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, ticks: { precision: 0 } }
        }
      }
    });

    var brand = data.brand || { brand: {}, non_brand: {} };
    var brandValues = [Number(brand.brand.clicks || 0), Number(brand.non_brand.clicks || 0)];
    var brandLabel = 'Clicks';
    if (brandValues[0] + brandValues[1] === 0) {
      brandValues = [Number(brand.brand.impressions || 0), Number(brand.non_brand.impressions || 0)];
      brandLabel = 'Impressions';
    }
    makeChart('gscBrandPie', {
      type: 'doughnut',
      data: { labels: ['Brand', 'Non-brand'], datasets: [{ label: brandLabel, data: brandValues, backgroundColor: ['#2563eb', '#10b981'] }] },
      options: { plugins: { legend: { position: 'bottom' } } }
    });

    renderGscQueries(data.queries || []);
    renderGscOpportunities(data.opportunities || []);
    renderGscDimensionTable('gscPageTable', data.pages || [], 'page', 'Landing Page', 100);
    renderGscDimensionTable('gscDeviceTable', data.devices || [], 'device', 'Device', 20);
    renderGscDimensionTable('gscCountryTable', data.countries || [], 'country', 'Country', 50);
    renderGscDimensionTable('gscAppearanceTable', data.appearances || [], 'searchAppearance', 'Appearance', 50);
  }

  function renderGscQueries(rows) {
    var search = (el('gscQuerySearch').value || '').trim().toLowerCase();
    var filtered = rows.filter(function (row) {
      return !search || String(row.query || '').toLowerCase().indexOf(search) !== -1 || String(row.page || '').toLowerCase().indexOf(search) !== -1;
    });
    el('gscQueryCount').textContent = filtered.length + ' of ' + rows.length;
    var html = '<thead><tr><th>Query</th><th>Type</th><th>Top Page</th><th>Clicks</th><th>Δ Clicks</th><th>Impressions</th><th>Δ Impr.</th><th>CTR</th><th>Position</th><th>Δ Position</th></tr></thead><tbody>';
    if (!filtered.length) html += '<tr><td colspan="10" class="empty-cell">No matching Search Console queries.</td></tr>';
    filtered.slice(0, 500).forEach(function (row) {
      html += '<tr><td class="keyword-cell">' + esc(row.query) + '</td>' +
        '<td><span class="query-type ' + (row.is_brand ? 'brand' : 'non-brand') + '">' + (row.is_brand ? 'Brand' : 'Non-brand') + '</span></td>' +
        '<td class="url-cell" title="' + esc(row.page || '') + '">' + esc(shortUrl(row.page || '/')) + '</td>' +
        '<td>' + fmtInt(row.clicks) + '</td><td>' + gscDelta(row.changes && row.changes.clicks) + '</td>' +
        '<td>' + fmtInt(row.impressions) + '</td><td>' + gscDelta(row.changes && row.changes.impressions) + '</td>' +
        '<td>' + fmtPct(Number(row.ctr || 0) * 100, 2) + '</td><td>' + Number(row.position || 0).toFixed(1) + '</td>' +
        '<td>' + gscDelta(row.changes && row.changes.position, 'position') + '</td></tr>';
    });
    html += '</tbody>';
    el('gscQueryTable').innerHTML = html;
  }

  function renderGscOpportunities(rows) {
    var html = '<thead><tr><th>Query</th><th>Top Page</th><th>Impressions</th><th>Clicks</th><th>CTR</th><th>Position</th><th>Recommended Focus</th></tr></thead><tbody>';
    if (!rows.length) html += '<tr><td colspan="7" class="empty-cell">No opportunity queries match the current thresholds.</td></tr>';
    rows.forEach(function (row) {
      var focus = row.position <= 10 ? 'Improve title/meta CTR' : 'Strengthen page content and internal links';
      html += '<tr><td class="keyword-cell">' + esc(row.query) + '</td><td class="url-cell" title="' + esc(row.page || '') + '">' + esc(shortUrl(row.page || '/')) + '</td>' +
        '<td>' + fmtInt(row.impressions) + '</td><td>' + fmtInt(row.clicks) + '</td><td>' + fmtPct(Number(row.ctr || 0) * 100, 2) + '</td>' +
        '<td>' + Number(row.position || 0).toFixed(1) + '</td><td>' + esc(focus) + '</td></tr>';
    });
    html += '</tbody>';
    el('gscOpportunityTable').innerHTML = html;
  }

  function renderGscDimensionTable(id, rows, key, heading, limit) {
    var html = '<thead><tr><th>' + esc(heading) + '</th><th>Clicks</th><th>Impressions</th><th>CTR</th><th>Position</th></tr></thead><tbody>';
    if (!rows.length) html += '<tr><td colspan="5" class="empty-cell">No ' + esc(heading.toLowerCase()) + ' data for this period.</td></tr>';
    rows.slice(0, limit).forEach(function (row) {
      var label = row[key] || '-';
      if (key === 'page') label = shortUrl(label);
      if (key === 'country') label = String(label).toUpperCase();
      html += '<tr><td class="' + (key === 'page' ? 'url-cell' : '') + '" title="' + esc(row[key] || '') + '">' + esc(label) + '</td>' + gscMetricRowCells(row) + '</tr>';
    });
    html += '</tbody>';
    el(id).innerHTML = html;
  }

  function connectGsc(event) {
    event.preventDefault();
    var form = el('gscConnectForm');
    var button = el('gscConnectButton');
    var message = el('gscConnectMessage');
    var fileInput = el('gscCredentials');
    if (!fileInput.files || !fileInput.files.length) {
      message.className = 'is-error';
      message.textContent = 'Choose the Google service-account JSON file.';
      return;
    }
    var body = new FormData(form);
    body.append('csrf_token', typeof ADMIN_CSRF !== 'undefined' ? ADMIN_CSRF : '');
    button.disabled = true;
    button.classList.add('is-loading');
    message.className = '';
    message.textContent = 'Validating credential…';
    fetch(API + '?action=gsc_connect', { method: 'POST', body: body, credentials: 'same-origin' })
      .then(function (response) { return response.json(); })
      .then(function (data) {
        if (!data || !data.ok) throw new Error((data && data.error) || 'Connection failed.');
        message.className = 'is-success';
        message.textContent = 'Connected as ' + data.account + '. Loading real data…';
        state.gscLoaded = false;
        state.gscData = null;
        loadGsc(true);
      })
      .catch(function (error) {
        message.className = 'is-error';
        message.textContent = error.message || 'Connection failed.';
      })
      .finally(function () {
        button.disabled = false;
        button.classList.remove('is-loading');
      });
  }

  function disconnectGsc() {
    var button = el('gscDisconnect');
    var body = new FormData();
    body.append('csrf_token', typeof ADMIN_CSRF !== 'undefined' ? ADMIN_CSRF : '');
    button.disabled = true;
    fetch(API + '?action=gsc_disconnect', { method: 'POST', body: body, credentials: 'same-origin' })
      .then(function (response) { return response.json(); })
      .then(function (data) {
        if (!data || !data.ok) throw new Error((data && data.error) || 'Unable to remove credential.');
        state.gscLoaded = false;
        state.gscData = null;
        loadGsc(true);
      })
      .catch(function (error) {
        setGscStatus('is-error', 'Unable to disconnect', esc(error.message || 'Request failed.'));
      })
      .finally(function () { button.disabled = false; });
  }

  function enquiryStatusOptions(current) {
    return ['new', 'contacted', 'enrolled', 'closed', 'spam'].map(function (status) {
      return '<option value="' + status + '"' + (status === current ? ' selected' : '') + '>' + status.charAt(0).toUpperCase() + status.slice(1) + '</option>';
    }).join('');
  }

  function renderEnquiries(d) {
    var e = d.enquiries || { rows: [], by_status: [], total: 0, latest: null };
    var statusMap = {};
    e.by_status.forEach(function (r) { statusMap[r.status] = r.c; });
    el('enquiryKpi').innerHTML =
      kpiCard('bi-inbox', '#1d4ed8', fmtInt(e.total), 'All Enquiries', 'Database total', '#dbeafe') +
      kpiCard('bi-envelope-plus', '#b45309', fmtInt(statusMap.new), 'New', 'Selected period', '#fef3c7') +
      kpiCard('bi-telephone-outbound', '#7e22ce', fmtInt(statusMap.contacted), 'Contacted', 'Selected period', '#f3e8ff') +
      kpiCard('bi-mortarboard', '#166534', fmtInt(statusMap.enrolled), 'Enrolled', e.latest ? 'Latest: ' + e.latest : 'No saved rows', '#dcfce7');

    var h = '<thead><tr><th>ID / Time</th><th>Contact</th><th>Course / Message</th><th>Submitted Page</th><th>Landing / Referrer</th><th>UTM Attribution</th><th>Status / Admin Note</th><th>Save</th></tr></thead><tbody>';
    if (!e.rows.length) h += '<tr><td colspan="8" class="empty-cell">No enquiries saved in this date range. Check Data Collection Health for the table and latest insert time.</td></tr>';
    e.rows.forEach(function (r) {
      var utm = [r.utm_source, r.utm_medium, r.utm_campaign, r.utm_term].filter(function (v) { return v; }).join(' / ') || 'Direct / unavailable';
      h += '<tr data-enquiry-id="' + r.id + '">' +
        '<td><strong>#' + r.id + '</strong><div class="cell-sub">' + esc(r.created_at || '-') + '</div></td>' +
        '<td><strong>' + esc(r.full_name) + '</strong><div class="cell-sub">' + esc(r.phone) + '</div><div class="cell-sub">' + esc(r.email) + '</div></td>' +
        '<td><strong>' + esc(r.course) + '</strong><div class="cell-sub message-cell" title="' + esc(r.message || '') + '">' + esc(r.message || '-') + '</div></td>' +
        '<td class="url-cell" title="' + esc(r.page_url || '') + '">' + esc(shortUrl(r.page_url || '/')) + '</td>' +
        '<td><div class="url-cell" title="' + esc(r.landing_page || '') + '">' + esc(shortUrl(r.landing_page || '/')) + '</div><div class="cell-sub url-cell" title="' + esc(r.referrer || '') + '">Ref: ' + esc(shortUrl(r.referrer || '-')) + '</div></td>' +
        '<td>' + esc(utm) + '<div class="cell-sub">Session: ' + esc((r.session_id || '-').slice(0, 8)) + '</div></td>' +
        '<td><select class="enquiry-status">' + enquiryStatusOptions(r.status || 'new') + '</select><textarea class="enquiry-note" maxlength="2000" placeholder="Follow-up note">' + esc(r.admin_note || '') + '</textarea></td>' +
        '<td><button type="button" class="btn btn-primary enquiry-save">Save</button><div class="save-state">' + esc(r.updated_at || '') + '</div></td></tr>';
    });
    h += '</tbody>';
    el('enquiryTable').innerHTML = h;
    el('enquiryTable').querySelectorAll('.enquiry-save').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var row = btn.closest('tr');
        var body = new FormData();
        body.append('enquiry_id', row.dataset.enquiryId);
        body.append('status', row.querySelector('.enquiry-status').value);
        body.append('admin_note', row.querySelector('.enquiry-note').value || '');
        body.append('csrf_token', typeof ADMIN_CSRF !== 'undefined' ? ADMIN_CSRF : '');
        btn.disabled = true;
        btn.textContent = 'Saving…';
        fetch(API + '?action=save_enquiry', { method: 'POST', body: body, credentials: 'same-origin' })
          .then(function (r) { return r.json(); })
          .then(function (j) { if (!j.ok) throw new Error(j.error || 'Save failed'); btn.textContent = 'Saved'; setTimeout(refresh, 500); })
          .catch(function (error) { btn.disabled = false; btn.textContent = 'Retry'; row.querySelector('.save-state').textContent = error.message; });
      });
    });
  }

  function renderHealth(d) {
    var h = d.health || { tables: [], schema_errors: [], query_errors: [] };
    el('healthChecked').textContent = h.checked_at ? 'Checked ' + h.checked_at : '';
    el('healthGrid').innerHTML = h.tables.map(function (r) {
      var ok = h.database && h.schema_ready;
      return '<div class="health-item"><span class="health-dot ' + (ok ? 'ok' : 'bad') + '"></span><div><strong>' + esc(r.table) + '</strong><span>' + fmtInt(r.rows) + ' rows · latest: ' + esc(r.latest || 'no data yet') + '</span></div></div>';
    }).join('');
    var errors = (h.schema_errors || []).concat(h.query_errors || []);
    var msg = el('healthMessage');
    if (errors.length) {
      msg.className = 'health-message error';
      msg.textContent = 'Action required: ' + errors.join(' | ');
    } else {
      msg.className = 'health-message success';
      msg.textContent = 'Database connection and required analytics/enquiry columns are ready. “No data yet” means the table works but has not received a matching event.';
    }
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
    state.activeTab = name;
    document.querySelectorAll('.nav-item').forEach(function (b) { b.classList.toggle('active', b.dataset.tab === name); });
    document.querySelectorAll('.tab-panel').forEach(function (p) { p.classList.toggle('active', p.id === 'tab-' + name); });
    var titles = {
      overview: 'Overview', visitors: 'Visitors', campaigns: 'Campaigns', pages: 'Pages',
      seo: 'SEO Monitor', enquiries: 'Enquiries', conversions: 'Conversions', live: 'Live Visitors', reports: 'Reports'
    };
    el('pageTitle').textContent = titles[name] || name;
    Object.keys(charts).forEach(function (k) { if (charts[k]) charts[k].resize(); });
    if (name === 'seo') loadGsc(false);
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
      state.gscLoaded = false;
      state.gscData = null;
      refresh();
      if (state.activeTab === 'seo') loadGsc(false);
    });

    el('periodSeg').addEventListener('click', function (e) {
      var btn = e.target.closest('button');
      if (!btn) return;
      state.period = btn.dataset.period;
      document.querySelectorAll('#periodSeg button').forEach(function (b) { b.classList.toggle('active', b === btn); });
      if (state.data) renderPeriodBar(state.data);
    });

    var gscRefresh = el('gscRefresh');
    if (gscRefresh) gscRefresh.addEventListener('click', function () { loadGsc(true); });
    var gscQuerySearch = el('gscQuerySearch');
    if (gscQuerySearch) gscQuerySearch.addEventListener('input', function () {
      if (state.gscData) renderGscQueries(state.gscData.queries || []);
    });
    var gscConnectForm = el('gscConnectForm');
    if (gscConnectForm) gscConnectForm.addEventListener('submit', connectGsc);
    var gscDisconnect = el('gscDisconnect');
    if (gscDisconnect) gscDisconnect.addEventListener('click', disconnectGsc);

    refresh();
  });
})();
