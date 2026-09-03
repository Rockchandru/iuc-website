/* ═══════════════════════════════════════════════════════════════
   IUC Edu — Analytics Tracker
   Tracks every visitor, session, UTM campaign source and conversion
   events. Works without Facebook / Instagram / YouTube APIs.
   ═══════════════════════════════════════════════════════════════ */
(function (w, d) {
  'use strict';

  var BASE = (typeof w.BASE_URL !== 'undefined' && w.BASE_URL) ? w.BASE_URL : '';
  var TRACK_URL = BASE + '/track.php';
  var VK = 'iuc_visitor_id';
  var SK = 'iuc_session_id';
  var SA = 'iuc_session_activity';
  var AK = 'iuc_session_attribution';
  var TIMEOUT = 30 * 60 * 1000; // 30 min session timeout

  /* Do not self-track the analytics dashboard / endpoints. */
  try {
    var path = w.location.pathname || '';
    if (path.indexOf('/admin') !== -1 || path.indexOf('track.php') !== -1 ||
        path.indexOf('export.php') !== -1 || path.indexOf('api.php') !== -1) {
      return;
    }
  } catch (e) {}

  function uuid() {
    function gen() {
      return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
        var r = Math.random() * 16 | 0;
        return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
      });
    }
    try {
      if (w.crypto && w.crypto.getRandomValues) {
        var b = w.crypto.getRandomValues(new Uint8Array(16));
        b[6] = (b[6] & 0x0f) | 0x40;
        b[8] = (b[8] & 0x3f) | 0x80;
        var h = '';
        for (var i = 0; i < 16; i++) h += (i === 4 || i === 6 || i === 8 || i === 10 ? '-' : '') + ('0' + b[i].toString(16)).slice(-2);
        return h;
      }
    } catch (e) {}
    return gen();
  }

  function read(k) { try { return w.localStorage.getItem(k); } catch (e) { return null; } }
  function write(k, v) { try { w.localStorage.setItem(k, v); } catch (e) {} }

  /* ── Visitor + session identity ─────────────────────────── */
  var visitorId = read(VK);
  if (!visitorId) { visitorId = uuid(); write(VK, visitorId); }

  var sessionId = read(SK);
  var lastAct = parseInt(read(SA) || '0', 10) || 0;
  var isNewSession = false;
  if (!sessionId || (Date.now() - lastAct) > TIMEOUT) {
    sessionId = uuid();
    write(SK, sessionId);
    isNewSession = true;
  }
  function touch() { write(SA, String(Date.now())); }
  touch();

  /* ── UTM / campaign params (supports both utm_ and utm-) ── */
  var utm = {};
  try {
    var q = w.location.search || '';
    if (q.charAt(0) === '?') q = q.slice(1);
    q.split('&').forEach(function (pair) {
      if (!pair) return;
      var eq = pair.indexOf('=');
      if (eq === -1) return;
      var k = decodeURIComponent(pair.slice(0, eq).replace(/\+/g, ' ')).toLowerCase();
      var v = decodeURIComponent(pair.slice(eq + 1).replace(/\+/g, ' '));
      if (!v) return;
      var kk = k.replace('utm-', 'utm_');
      if (kk.indexOf('utm_') === 0 && kk.length > 4) utm[kk] = v;
      if (k === 'gclid') utm.gclid = v;
      if (k === 'ref') utm.ref = v;
    });
  } catch (e) {}

  /* Keep first-touch details for the whole session, including the enquiry form. */
  var attribution = {};
  try {
    if (!isNewSession) attribution = JSON.parse(read(AK) || '{}') || {};
  } catch (e) { attribution = {}; }
  if (isNewSession || !attribution.landing_page) {
    attribution = {
      landing_page: String(w.location.href || '').slice(0, 500),
      referrer: String(d.referrer || '').slice(0, 500)
    };
  }
  ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'gclid'].forEach(function (key) {
    if (utm[key] && !attribution[key]) attribution[key] = utm[key];
  });
  try { write(AK, JSON.stringify(attribution)); } catch (e) {}

  /* ── Device / browser / OS detection ────────────────────── */
  function detect() {
    var ua = w.navigator.userAgent || '';
    var device = 'desktop', browser = 'Unknown', os = 'Unknown';
    var oses = [
      [/Windows/i, 'Windows'], [/Android/i, 'Android'], [/iPhone|iPad|iPod/i, 'iOS'],
      [/Mac OS X/i, 'macOS'], [/Linux/i, 'Linux'], [/CrOS/i, 'Chrome OS']
    ];
    for (var i = 0; i < oses.length; i++) if (oses[i][0].test(ua)) { os = oses[i][1]; break; }
    if (/iPad/i.test(ua) || (/Android/i.test(ua) && !/Mobile/i.test(ua))) device = 'tablet';
    else if (/Mobi/i.test(ua)) device = 'mobile';

    var brows = [
      [/Edg\//i, 'Edge'], [/OPR\//i, 'Opera'], [/Chrome/i, 'Chrome'], [/Firefox/i, 'Firefox'],
      [/Safari/i, 'Safari'], [/MSIE/i, 'IE'], [/Trident/i, 'IE']
    ];
    for (var j = 0; j < brows.length; j++) if (brows[j][0].test(ua)) { browser = brows[j][1]; break; }
    if (browser === 'Safari' && /Chrome/i.test(ua)) browser = 'Chrome';
    if (browser === 'Chrome' && /Edg\//i.test(ua)) browser = 'Edge';
    return { device: device, browser: browser, os: os };
  }

  function screenSize() {
    try { return (w.screen && w.screen.width) ? w.screen.width + 'x' + w.screen.height : ''; }
    catch (e) { return ''; }
  }

  var pageStart = Date.now();
  var pvId = 0;
  var info = detect();

  function baseData(extra) {
    var d = {
      visitor_id: visitorId,
      session_id: sessionId,
      event_type: 'pageview',
      page_url: String(w.location.href).slice(0, 500),
      page_title: String(w.document.title || '').slice(0, 255),
      referrer: String(w.document.referrer || '').slice(0, 500),
      landing_page: String(attribution.landing_page || w.location.href).slice(0, 500),
      device: info.device,
      browser: info.browser,
      os: info.os,
      screen: screenSize(),
      language: String(w.navigator.language || '').slice(0, 16)
    };
    var keys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'gclid'];
    for (var i = 0; i < keys.length; i++) if (attribution[keys[i]]) d[keys[i]] = attribution[keys[i]];
    if (extra) for (var k in extra) if (extra[k] !== undefined) d[k] = extra[k];
    return d;
  }

  function send(data, wantResponse) {
    var body;
    try { body = new URLSearchParams(); } catch (e) { body = null; }
    if (body) {
      for (var k in data) if (data[k] !== null && data[k] !== undefined) body.append(k, data[k]);
    }
    try {
      if (wantResponse && w.fetch) {
        w.fetch(TRACK_URL, { method: 'POST', body: body, credentials: 'omit', keepalive: true })
          .then(function (r) { return r.json(); })
          .then(function (j) { if (j && j.pv_id) pvId = j.pv_id; })
          .catch(function () {});
      } else if (w.navigator && w.navigator.sendBeacon) {
        w.navigator.sendBeacon(TRACK_URL, body);
      } else {
        var img = new Image();
        var qs = '';
        for (var q in data) if (data[q] !== undefined) qs += '&' + encodeURIComponent(q) + '=' + encodeURIComponent(data[q]);
        img.src = TRACK_URL + '?' + qs.slice(1);
      }
    } catch (e) {}
  }

  /* ── Initial pageview ───────────────────────────────────── */
  send(baseData({ event_type: 'pageview' }), true);

  /* ── Heartbeat (time on page + live detection) ──────────── */
  function heartbeat() {
    touch();
    send(baseData({ event_type: 'heartbeat', pv_id: pvId, duration: Math.round((Date.now() - pageStart) / 1000) }), false);
  }
  setInterval(heartbeat, 30000);
  function finalBeat() { heartbeat(); }
  if (w.addEventListener) {
    w.addEventListener('pagehide', finalBeat);
    w.addEventListener('visibilitychange', function () {
      if (d.visibilityState === 'hidden') finalBeat();
    });
  }

  /* ── Public conversion API ──────────────────────────────── */
  function trackEvent(type, label) {
    send(baseData({ event_type: type, event_label: String(label || '').slice(0, 255) }), false);
  }
  w.__iucTrackEvent = trackEvent;

  function syncEnquiryAttribution(form) {
    if (!form) return;
    var values = {
      page_url: String(w.location.href || '').slice(0, 500),
      landing_page: String(attribution.landing_page || w.location.href || '').slice(0, 500),
      referrer: String(attribution.referrer || d.referrer || '').slice(0, 500),
      visitor_id: visitorId,
      session_id: sessionId,
      utm_source: attribution.utm_source || '',
      utm_medium: attribution.utm_medium || '',
      utm_campaign: attribution.utm_campaign || '',
      utm_content: attribution.utm_content || '',
      utm_term: attribution.utm_term || ''
    };
    Object.keys(values).forEach(function (name) {
      var input = form.querySelector('[name="' + name + '"]');
      if (input) input.value = values[name];
    });
  }

  var enquiryForm = d.querySelector('form [name="contact_submit"]');
  if (enquiryForm) syncEnquiryAttribution(enquiryForm.form);

  /* ── Automatic event tracking (event delegation) ────────── */
  function trackAttr(el, name) {
    var n = 'data-' + name;
    while (el && el.nodeType === 1 && el !== d) {
      if (el.hasAttribute && el.hasAttribute(n)) return el.getAttribute(n);
      el = el.parentNode;
    }
    return null;
  }

  if (d.addEventListener) {
    d.addEventListener('click', function (e) {
      var el = e.target;
      var t = trackAttr(el, 'track-event');
      if (t) { trackEvent(t, el.href || el.textContent || ''); return; }
      var a = el.closest ? el.closest('a') : null;
      if (!a) return;
      var href = a.getAttribute('href') || '';
      if (href.indexOf('tel:') === 0) trackEvent('call_click', href);
      else if (href.indexOf('wa.me') !== -1 || href.indexOf('api.whatsapp.com') !== -1) trackEvent('whatsapp_click', href);
      else if (href.indexOf('download-syllabus') !== -1 || /\.pdf(\?|#|$)/i.test(href)) trackEvent('brochure_download', href);
      else if (a.target === '_blank' && href.indexOf(location.host) === -1) trackEvent('outbound_click', href);
    }, true);

    d.addEventListener('submit', function (e) {
      var f = e.target;
      if (!f || !f.tagName || f.tagName.toLowerCase() !== 'form') return;
      if (f.querySelector('[name="contact_submit"]')) {
        syncEnquiryAttribution(f);
        trackEvent('contact_form_attempt', 'Contact Form Submission Attempt');
      }
      else if (f.className.indexOf('newsletter-form') !== -1) trackEvent('registration', 'Newsletter Signup');
    }, true);
  }
})(window, document);
