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
  var EK = 'iuc_engaged_session';
  var TK = 'iuc_thank_you_shown';
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

  /* The live host's ModSecurity rules reject a full URL in a field named
     landing_page. A same-site path carries the same reporting information and
     keeps tracker and enquiry requests from being rejected with HTTP 406. */
  function sameSitePath(value) {
    var raw = String(value || '');
    if (!raw) return '/';
    try {
      var url = new URL(raw, w.location.href);
      if (url.origin === w.location.origin) return (url.pathname || '/') + (url.search || '');
    } catch (e) {}
    return raw;
  }

  /* ── UTM / campaign params (supports both utm_ and utm-) ── */
  var utm = {};
  var clickRank = { gad_source: 1, gad_campaignid: 1, fbclid: 2, ttclid: 2, twclid: 2, li_fat_id: 2, msclkid: 3, gbraid: 4, wbraid: 4, dclid: 5, gclid: 6 };
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
      if (kk === 'utm_id') utm.campaign_id = v;
      /* Preserve the real click-ID type. A gad_source value is evidence of a
         Google advertising click, but it is not a platform or campaign name. */
      if (clickRank[k] && (!utm.click_id_type || clickRank[k] > clickRank[utm.click_id_type])) {
        utm.click_id_type = k;
        utm.click_id = v;
      }
      if (k === 'gad_campaignid') utm.campaign_id = v;
      if (k === 'ref') utm.ref = v;
    });
  } catch (e) {}

  function referrerSource(value) {
    var host = '';
    try { host = new URL(String(value || ''), w.location.href).hostname.toLowerCase().replace(/^www\./, ''); }
    catch (e) { return ''; }
    if (!host || host === w.location.hostname.toLowerCase().replace(/^www\./, '')) return '';
    var sources = [
      ['facebook', ['facebook.', 'fb.com', 'fb.me']],
      ['instagram', ['instagram.']],
      ['youtube', ['youtube.', 'youtu.be']],
      ['linkedin', ['linkedin.']],
      ['whatsapp', ['whatsapp.', 'wa.me']],
      ['twitter', ['twitter.', 'x.com', 't.co']],
      ['tiktok', ['tiktok.']],
      ['google', ['google.']],
      ['bing', ['bing.']],
      ['yahoo', ['search.yahoo.']],
      ['duckduckgo', ['duckduckgo.']]
    ];
    for (var i = 0; i < sources.length; i++) {
      for (var j = 0; j < sources[i][1].length; j++) {
        if (host.indexOf(sources[i][1][j]) !== -1) return sources[i][0];
      }
    }
    return host;
  }

  /* Keep first-touch details for the whole session, including the enquiry form. */
  var attribution = {};
  try {
    if (!isNewSession) attribution = JSON.parse(read(AK) || '{}') || {};
  } catch (e) { attribution = {}; }
  /* Upgrade attribution saved by the previous tracker version, which stored
     every advertising marker in a field named gclid. */
  if (attribution.gclid && !attribution.click_id_type) {
    var legacyClick = String(attribution.gclid);
    var legacyParts = legacyClick.split(':');
    if (legacyParts.length > 1 && clickRank[legacyParts[0]]) {
      attribution.click_id_type = legacyParts.shift();
      attribution.click_id = legacyParts.join(':');
    } else {
      attribution.click_id_type = 'gclid';
      attribution.click_id = legacyClick;
    }
    delete attribution.gclid;
  }
  /* A tagged or externally referred acquisition starts a new analytics
     session only when it differs from the active session's attribution.
     This keeps reloads and internal navigation in the current session while
     allowing a new campaign click inside the 30-minute window to be stored. */
  if (!isNewSession) {
    var acquisitionKeys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'campaign_id', 'click_id_type', 'click_id'];
    var hasTaggedAcquisition = false;
    var acquisitionChanged = false;
    for (var ai = 0; ai < acquisitionKeys.length; ai++) {
      var acquisitionKey = acquisitionKeys[ai];
      if (!utm[acquisitionKey]) continue;
      hasTaggedAcquisition = true;
      if (String(attribution[acquisitionKey] || '') !== String(utm[acquisitionKey])) acquisitionChanged = true;
    }
    if (!hasTaggedAcquisition) {
      var incomingReferrerSource = referrerSource(d.referrer);
      var storedReferrerSource = referrerSource(attribution.referrer);
      acquisitionChanged = !!incomingReferrerSource && incomingReferrerSource !== storedReferrerSource;
    }
    if (acquisitionChanged) {
      sessionId = uuid();
      write(SK, sessionId);
      isNewSession = true;
      attribution = {};
    }
  }
  if (isNewSession || !attribution.landing_page) {
    attribution = {
      landing_page: sameSitePath(w.location.href).slice(0, 500),
      referrer: String(d.referrer || '').slice(0, 500)
    };
  }
  ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'campaign_id', 'click_id_type', 'click_id'].forEach(function (key) {
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

  var engagedMs = 0;
  var visibleSince = d.visibilityState === 'hidden' ? 0 : Date.now();
  var lastHeartbeatAt = 0;
  var pvId = 0;
  var info = detect();

  function baseData(extra) {
    var d = {
      visitor_id: visitorId,
      session_id: sessionId,
      event_type: 'pageview',
      page_url: String(w.location.href).slice(0, 500),
      page_title: String(w.document.title || '').slice(0, 255),
      referrer: String(Object.prototype.hasOwnProperty.call(attribution, 'referrer') ? attribution.referrer : (w.document.referrer || '')).slice(0, 500),
      landing_page: sameSitePath(attribution.landing_page || w.location.href).slice(0, 500),
      device: info.device,
      browser: info.browser,
      os: info.os,
      screen: screenSize(),
      language: String(w.navigator.language || '').slice(0, 16)
    };
    var keys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'campaign_id', 'click_id_type', 'click_id'];
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
  function engagedSeconds(commit) {
    var now = Date.now();
    var total = engagedMs;
    if (visibleSince) {
      total += now - visibleSince;
      if (commit) { engagedMs = total; visibleSince = 0; }
    }
    return Math.max(0, Math.round(total / 1000));
  }

  function heartbeat(force) {
    if (!force && d.visibilityState === 'hidden') return;
    var now = Date.now();
    if (force && now - lastHeartbeatAt < 1000) return;
    lastHeartbeatAt = now;
    touch();
    send(baseData({ event_type: 'heartbeat', pv_id: pvId, duration: engagedSeconds(false) }), false);
  }

  function renewExpiredSession() {
    var storedActivity = parseInt(read(SA) || '0', 10) || 0;
    if (!storedActivity || Date.now() - storedActivity <= TIMEOUT) return false;
    sessionId = uuid();
    write(SK, sessionId);
    attribution = {
      landing_page: sameSitePath(w.location.href).slice(0, 500),
      referrer: ''
    };
    ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'campaign_id', 'click_id_type', 'click_id'].forEach(function (key) {
      if (utm[key]) attribution[key] = utm[key];
    });
    write(AK, JSON.stringify(attribution));
    engagedMs = 0;
    visibleSince = Date.now();
    pvId = 0;
    touch();
    send(baseData({ event_type: 'pageview' }), true);
    return true;
  }
  setInterval(function () { heartbeat(false); }, 30000);
  function finalBeat() { heartbeat(true); }
  if (w.addEventListener) {
    w.addEventListener('pagehide', function () {
      engagedSeconds(true);
      finalBeat();
    });
    w.addEventListener('visibilitychange', function () {
      if (d.visibilityState === 'hidden') {
        engagedSeconds(true);
        finalBeat();
      } else {
        visibleSince = Date.now();
        if (!renewExpiredSession()) heartbeat(false);
      }
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
      landing_page: sameSitePath(attribution.landing_page || w.location.href).slice(0, 500),
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

  /* Non-invasive funnel events. Labels describe UI placement/type only and
     never contain form values or other personal data. */
  var startedForms = [];
  var invalidForms = [];
  var shownThankYou = [];
  var scrollMilestones = [25, 50, 75, 100];
  var sentScroll = {};

  function isEnquiryForm(form) {
    return !!(form && form.querySelector && form.querySelector('[name="contact_submit"]'));
  }
  function rememberOnce(list, item) {
    if (list.indexOf(item) !== -1) return false;
    list.push(item);
    return true;
  }
  function elementLabel(el, fallback) {
    if (!el) return fallback || '';
    return String(el.getAttribute('aria-label') || el.getAttribute('data-course') ||
      el.getAttribute('data-track-label') || el.textContent || fallback || '').replace(/\s+/g, ' ').trim().slice(0, 180);
  }
  function recordThankYou(root) {
    var nodes = [];
    if (root && root.matches && root.matches('.enquiry-thank-you-message')) nodes.push(root);
    if (root && root.querySelectorAll) {
      var found = root.querySelectorAll('.enquiry-thank-you-message');
      for (var i = 0; i < found.length; i++) nodes.push(found[i]);
    }
    for (var j = 0; j < nodes.length; j++) {
      if (!nodes[j].hidden && read(TK) !== sessionId && rememberOnce(shownThankYou, nodes[j])) {
        write(TK, sessionId);
        trackEvent('thank_you_shown', 'Enquiry Thank You Message');
      }
    }
  }

  recordThankYou(d);
  if (w.MutationObserver && d.documentElement) {
    new MutationObserver(function (mutations) {
      for (var i = 0; i < mutations.length; i++) {
        recordThankYou(mutations[i].target);
        for (var j = 0; j < mutations[i].addedNodes.length; j++) recordThankYou(mutations[i].addedNodes[j]);
      }
    }).observe(d.documentElement, { childList: true, subtree: true, attributes: true, attributeFilter: ['class', 'hidden'] });
  }

  function checkScrollDepth() {
    var doc = d.documentElement;
    var body = d.body;
    var height = Math.max(doc ? doc.scrollHeight : 0, body ? body.scrollHeight : 0);
    var viewport = w.innerHeight || (doc ? doc.clientHeight : 0) || 0;
    var available = Math.max(1, height - viewport);
    var percent = Math.min(100, Math.round(((w.pageYOffset || (doc && doc.scrollTop) || 0) / available) * 100));
    for (var i = 0; i < scrollMilestones.length; i++) {
      var milestone = scrollMilestones[i];
      if (percent >= milestone && !sentScroll[milestone]) {
        sentScroll[milestone] = true;
        trackEvent('scroll_depth', milestone + '%');
      }
    }
  }
  if (w.addEventListener) w.addEventListener('scroll', checkScrollDepth, { passive: true });

  /* One engaged-session event per analytics session after ten visible seconds. */
  setTimeout(function () {
    if (d.visibilityState === 'hidden' || read(EK) === sessionId) return;
    write(EK, sessionId);
    trackEvent('engaged_session', '10 seconds visible');
  }, 10000);

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
      var modalTrigger = el.closest ? el.closest('.apply-now-trigger') : null;
      if (modalTrigger) trackEvent('enquiry_modal_open', elementLabel(modalTrigger, 'Apply now'));
      var courseCard = el.closest ? el.closest('.course-card, .seo-landing-course-card') : null;
      if (courseCard) trackEvent('course_card_click', elementLabel(courseCard, 'Course card'));
      var a = el.closest ? el.closest('a') : null;
      var button = el.closest ? el.closest('a, button') : null;
      if (button && !modalTrigger && !courseCard) {
        var placement = button.getAttribute('data-track-placement') || (button.closest('header') ? 'header' : (button.closest('footer') ? 'footer' : 'page'));
        var buttonClass = String(button.className || '');
        if (buttonClass.indexOf('btn') !== -1 || buttonClass.indexOf('cta') !== -1) {
          trackEvent('cta_click', placement + ': ' + elementLabel(button, 'CTA'));
        }
      }
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

    d.addEventListener('input', function (e) {
      var form = e.target && e.target.form;
      if (isEnquiryForm(form) && rememberOnce(startedForms, form)) {
        syncEnquiryAttribution(form);
        trackEvent('enquiry_form_start', form.querySelector('[name="modal_submit"]') ? 'Enquiry Modal' : 'Contact Form');
      }
    }, true);

    d.addEventListener('invalid', function (e) {
      var form = e.target && e.target.form;
      if (isEnquiryForm(form) && rememberOnce(invalidForms, form)) {
        trackEvent('form_validation_failure', form.querySelector('[name="modal_submit"]') ? 'Enquiry Modal' : 'Contact Form');
      }
    }, true);
  }
})(window, document);
