# Admin Analytics Complete Investigation — 1 October 2026

## Investigation Status

Phase 1 investigation and Phase 2 root-cause analysis are complete.

No code was modified during this investigation. The existing campaign-session fix in `assets/js/tracker.js` and the `enquiry-thank-you-message` tracking class remain unchanged.

## Primary Conclusion

The IUC Admin Analytics dashboard measures activity that occurs on the IUC website:

- Website visitors
- Website sessions
- Website pageviews
- Website interactions
- Enquiries and conversions

It does not currently import:

- YouTube impressions or video views
- Facebook or Instagram impressions and reach
- Advertising-platform clicks
- Advertising spend

Therefore, figures such as 77 lakh YouTube views or 22K Meta impressions must not be expected to equal the website sessions shown in Admin Analytics.

Ad-platform impressions, video views, clicks, completed landing-page loads, website sessions, and enquiries are different metrics.

A typical funnel is:

```text
Ad impressions
    → Video views or engagements
    → Ad/link clicks
    → Completed landing-page loads
    → Tracked website sessions
    → Enquiries/conversions
```

The exact order and definitions depend on the platform and campaign format, but ad impressions and video views are not website visits.

## Current Analytics Architecture

```text
Campaign URL
    ↓
Browser URL, referrer and localStorage
    ↓
assets/js/tracker.js
    ↓
POST /track.php
    ↓
Analytics database tables
    ↓
admin/api.php
    ↓
admin/admin.js
    ↓
Admin Analytics dashboard
```

Successful enquiries additionally follow:

```text
Enquiry form
    ↓
index.php
    ↓
enquiries + analytics_events
    ↓
Admin Enquiries and Conversions
```

## Database Tables Reviewed

| Table | Purpose |
|---|---|
| `analytics_visitors` | Anonymous visitor identity and lifetime totals |
| `analytics_sessions` | Session attribution, entry/exit, duration, device and geography |
| `analytics_pageviews` | Individual pages visited and time on page |
| `analytics_campaigns` | One campaign-attribution row per campaign session |
| `analytics_events` | Website interactions and conversions |
| `analytics_live_activity` | Per-session/per-minute live presence |
| `analytics_campaign_meta` | Manually entered campaign cost and note |
| `analytics_geo_cache` | Cached IP geography |
| `analytics_migrations` | Applied analytics migrations |
| `enquiries` | Successful enquiries and their attribution |
| `whatsapp_enquiry_messages` | Enquiry WhatsApp delivery status |

## Attribution Support

| Source | Detection method | Status |
|---|---|---|
| Facebook | UTM, Facebook referrer, `fbclid` | Supported |
| Instagram | `utm_source=instagram` or Instagram referrer | Supported |
| YouTube | `utm_source=youtube` or YouTube referrer | Supported |
| Google Ads | Google UTM, `gclid`, `dclid`, `gbraid`, `wbraid` and Google markers | Supported |
| LinkedIn | UTM, referrer or `li_fat_id` | Supported |
| WhatsApp | UTM or detectable WhatsApp referrer | Supported |
| Organic search | Search-engine referrer | Supported |
| Referral | External referrer hostname | Supported |
| Direct | No campaign or referrer evidence | Supported |
| Other campaigns | Explicit UTM values | Supported |

Supported campaign parameters include:

- `utm_source`
- `utm_medium`
- `utm_campaign`
- `utm_content`
- `utm_term`
- `utm_id`
- `gad_campaignid`
- `fbclid`
- `gclid`
- `dclid`
- `gbraid`
- `wbraid`
- `msclkid`
- `ttclid`
- `twclid`
- `li_fat_id`

## Attribution Limitations

- `fbclid` alone cannot reliably distinguish Facebook from Instagram. Instagram links need `utm_source=instagram`.
- `gclid` alone cannot distinguish YouTube from Google Search or other Google inventory. YouTube links need `utm_source=youtube`.
- Mobile apps and privacy controls can remove referrers.
- Organic search terms are generally unavailable unless supplied through `utm_term` or aggregate Search Console data.
- JavaScript blockers and incomplete page loads can prevent website-side tracking.
- Clearing `localStorage`, incognito mode, or changing browsers/devices can create a new anonymous visitor ID.

## Actual Tracking Bugs Found

### 1. Campaign-session fix is not deployed live

The local `assets/js/tracker.js` contains the approved campaign-change session logic. The deployed live tracker does not yet contain that new logic.

The live asset reported a `Last-Modified` date of 24 September 2026.

Until the updated tracker is deployed, a later campaign opened during an existing 30-minute live session can retain the earlier attribution.

Affected sources include Facebook, Instagram, YouTube, Google and other UTM campaigns.

### 2. Legacy course and blog redirects remove campaign parameters

Live verification showed:

```text
/course.php?slug=python&utm_source=facebook&...
    → /course/python
```

The redirect destination contains no UTM values.

The same behavior applies to legacy blog URLs.

Responsible rules:

- `.htaccess` course redirect
- `.htaccess` blog redirect

The trailing `?` in these redirect substitutions removes the complete original query string, including UTM values and click IDs.

Clean campaign URLs work correctly:

```text
/course/python?utm_source=instagram&utm_medium=paid_social&utm_campaign=october_python
```

### 3. Recent Visitors ignores the selected date range

The Recent Visitors query returns the latest 60 sessions without applying `from` and `to` dates.

For the audited single-day range:

```text
Period sessions       = 7
Recent Visitors rows  = 9
```

Responsible query: `admin/api.php`, Recent Visits section.

### 4. Returning Visitors is historically inaccurate

The dashboard determines returning visitors from the visitor's current lifetime `is_returning` flag.

If someone first visited in an older selected period and returned later, the older first visit can be retroactively classified as returning.

Returning status should instead consider whether a visitor had a session before the selected session or period.

### 5. Conversion Rate counts actions instead of converting sessions

Current formula:

```text
Conversion actions / Sessions × 100
```

Conversion actions include successful forms, phone clicks, WhatsApp clicks, brochure downloads, admission actions and registrations.

One session can generate multiple actions, so this value can exceed 100%.

A standard session conversion rate should use:

```text
Distinct sessions with at least one selected conversion / Sessions × 100
```

Raw conversion-action count should remain available as a separate metric.

### 6. Social “views” label is ambiguous

The Facebook, Instagram and YouTube cards display `views`, but these are IUC website pageviews from `analytics_pageviews`.

They are not ad impressions, video views, platform engagements or platform reach.

The data is correct, but the label should be changed to “Website pageviews”.

### 7. Source and campaign filters are missing

The dashboard currently provides date selection and a daily/weekly/monthly/yearly chart control.

It does not provide source, medium, channel, campaign or landing-page filters.

## Interaction Tracking Status

### Existing events

| Interaction | Status |
|---|---|
| Pageview | Recorded |
| Heartbeat/session duration | Recorded |
| Phone click | Recorded |
| WhatsApp click | Recorded |
| Brochure download | Recorded |
| Marked admission CTA | Recorded |
| Newsletter submit | Recorded as registration |
| Enquiry submission attempt | Recorded |
| Successful enquiry | Recorded as `contact_form` |
| Marked generic CTA | Supported through `data-track-event` |
| Outbound link | Recorded |

### Missing events

| Interaction | Status |
|---|---|
| Scroll depth | Missing |
| General course-card click | Missing |
| Enquiry modal opened | Missing |
| Enquiry form started | Missing |
| Form field progression | Missing |
| Form validation failure | Missing |
| Thank You message shown | Missing as a separate browser event |
| CTA classification by placement | Incomplete |
| Engaged-session event | Missing |
| Ordered visitor journey | Stored data exists, but no dashboard view |

Successful enquiry conversions are recorded server-side even though a separate “Thank You shown” browser event does not currently exist.

## Visitor-Level Tracking Capability

| Data | Captured | Dashboard visibility |
|---|---:|---:|
| Anonymous visitor ID | Yes | Partial |
| Session ID | Yes | Partial |
| Landing page | Yes | Yes |
| Source and medium | Yes | Yes |
| Campaign and campaign ID | Yes | Yes |
| Creative/content | Yes | Campaign table |
| Search term | When available | SEO table |
| Click ID | Yes | Type shown; value usually hidden |
| Referrer | Yes | Enquiry view |
| Device/browser/OS | Yes | Yes |
| Country/state/city | When lookup succeeds | Yes |
| Entry time | Yes | Partial |
| Last activity | Yes | Yes |
| Session duration | Yes | Yes |
| Pageview count | Yes | Yes |
| Individual pages visited | Yes | No ordered journey view |
| Exit page | Yes | Yes |
| Bounce | Yes | Yes |
| Enquiry/conversion | Yes | Yes |
| Conversion page | Yes | Not clearly surfaced |
| Conversion timestamp | Yes | Aggregated; enquiry timestamp shown |

The system uses anonymous UUIDs in `localStorage`. It does not use invasive browser fingerprinting.

## Dashboard Metric Audit

| Metric | Calculation | Assessment |
|---|---|---|
| Total Visitors | All `analytics_visitors` rows | Correct; all-time |
| Today's Visitors | Visitors with `last_seen` today | Correct as active today |
| Active Users | Distinct visitors active in last 30 minutes | Correct; Live may use GA4 |
| Unique Visitors | Distinct period visitor IDs | Correct |
| Returning Visitors | Period visitors with lifetime returning flag | Historically incorrect |
| Total Pageviews | Period pageview rows | Correct |
| Sessions | Sessions started during period | Correct |
| Bounce Rate | Sessions with one or zero pageviews | Consistent, but not engagement-based |
| Average Duration | Average heartbeat duration | Correct within heartbeat limits |
| Total Conversions | Count of configured conversion actions | Correct as actions |
| Conversion Rate | Conversion actions divided by sessions | Misleading |
| Daily trend | Sessions, daily visitors and pageviews | Correct |
| Weekly/monthly/yearly trends | Sessions and unique visitors | Correct |
| Sources | Sessions and website pageviews by source | Correct |
| Channels | Sessions by channel | Correct |
| Campaigns | Sessions/pageviews/conversions by campaign dimensions | Correct |
| Social traffic | Sessions, website pageviews and conversions | Correct data; ambiguous label |
| Device/browser/OS | Sessions grouped by dimension | Correct |
| Country/city | Sessions grouped by available geography | Correct |
| Landing pages | Sessions grouped by landing page | Correct |
| Exit pages | Sessions grouped by exit page | Correct |
| Top pages | Pageviews and distinct visitors | Correct |
| Enquiries | Successful enquiries in selected period | Correct |
| All Enquiries | All-time enquiry count | Correct and labelled |
| Recent Visitors | Latest 60 sessions without period filter | Incorrect |
| Live Visitors | IUC activity or GA4 Realtime | Correct, but measurement source can differ |
| Search Console | Google Search Console API aggregates | Separate measurement system |

## Database vs. Dashboard Verification

Selected period: `2026-10-01`, timezone `Asia/Kolkata`.

| Metric | Independent database result | Admin API result | Status |
|---|---:|---:|---|
| Total visitors | 9 | 9 | Match |
| Unique visitors | 7 | 7 | Match |
| Sessions | 7 | 7 | Match |
| Pageviews | 9 | 9 | Match |
| Conversions | 2 | 2 | Match |
| Enquiries | 2 | 2 | Match |
| Recent Visitors | Expected 7 | 9 | Mismatch |

Channel totals for the same period:

| Channel | Sessions |
|---|---:|
| Direct | 2 |
| Facebook Ads | 2 |
| Instagram Ads | 1 |
| YouTube Ads | 1 |
| Google Ads | 1 |

Database integrity checks:

| Check | Result |
|---|---:|
| Duplicate campaign rows per session | 0 |
| Orphan pageviews | 0 |
| Orphan events | 0 |
| Session/pageview counter mismatches | 0 |
| Visitor/pageview counter mismatches | 0 |
| Visitor/session counter mismatches | 0 |
| Admin API query errors | 0 |

The inspected local database mainly contains identifiable Phase 3 verification data. It cannot be used to reconcile the real advertising-platform totals.

## Expected Difference vs. Actual Bugs

### Expected metric differences

- Ad impressions are not website visits.
- YouTube video views are not landing-page sessions.
- Repeated ad clicks may belong to one user/session.
- A click can occur without the website completing its load.
- JavaScript and analytics requests can be blocked.
- Platform invalid-traffic filtering and website bot filtering differ.
- Referrers can be removed by apps and privacy controls.
- Platform and website date/timezone definitions may differ.

These differences must not be corrected by fabricating or inflating website traffic.

### Actual bugs or product gaps

1. The campaign-session fix is not deployed live.
2. Legacy course/blog redirects remove campaign parameters.
3. Recent Visitors ignores the selected date range.
4. Returning Visitors can misclassify historical periods.
5. Conversion Rate counts actions rather than converting sessions.
6. Social `views` can be mistaken for platform views.
7. Important funnel events are missing.
8. Source/campaign filters and ordered journey reporting are absent.

## Files Reviewed

- `assets/js/tracker.js`
- `assets/js/main.js`
- `track.php`
- `index.php`
- `.htaccess`
- `db.php`
- `includes/analytics.php`
- `includes/header.php`
- `includes/footer.php`
- `includes/contact.php`
- `includes/application-modal.php`
- `includes/google-analytics.php`
- `includes/search-console.php`
- `admin/api.php`
- `admin/admin.js`
- `admin/dashboard.php`
- `admin/admin.css`
- `course.php`
- `blog.php`
- `landing.php`

## Proposed Implementation Scope

Confirmed fixes would require:

### `.htaccess`

- Preserve campaign parameters through legacy canonical redirects.

### `admin/api.php`

- Apply the selected date range to Recent Visitors.
- Correct returning-visitor calculation.
- Separate conversion-action count from converting-session rate.
- Add optional source/campaign filters and journey data.

### `admin/admin.js`

- Clarify website pageview labels.
- Display corrected metrics and user-journey data.
- Send selected acquisition filters to the API.

### `admin/dashboard.php`

- Add source/campaign filtering controls and clear metric descriptions.

### `assets/js/tracker.js`

- Add approved non-conversion interaction events while preserving the existing campaign-session fix.

### `track.php`

- Accept the selected new interaction event types.

No database schema change is currently required because `analytics_events` can store the missing interaction events.

The secondary campaign cost/note metadata issue remains separate and should not be included unless explicitly approved.

## External Analytics Required

The following metrics require advertising-platform APIs, connected accounts, or imported reports:

- Facebook/Instagram impressions
- Meta reach and frequency
- Meta link clicks and landing-page views
- YouTube impressions and video views
- Google Ads impressions, clicks and cost
- Platform-reported conversions and attribution windows
- Ad spend, CPC, CPM, CPV and ROAS

Website-side tracking alone cannot produce these platform metrics.

## Implementation Status

No implementation was performed as part of this investigation phase.
