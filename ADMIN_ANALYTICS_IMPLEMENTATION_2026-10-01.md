# Admin Analytics Implementation — 1 October 2026

## Status

The confirmed Admin Analytics issues from the approved investigation have been implemented locally.

The changes have not been deployed to the production website. No live-production success is claimed in this report.

## Root Causes Fixed

1. Legacy course and blog redirects discarded the complete query string, including UTM values and click IDs.
2. Recent Visitors queried the latest sessions without using the selected date range.
3. Returning Visitors used the visitor's current lifetime flag and could retroactively change historical reports.
4. Conversion Rate divided conversion actions by sessions, so multiple actions from one session could inflate the percentage.
5. Dashboard acquisition views did not share one reusable source/medium/channel/campaign/landing-page filter scope.
6. Social `Views` could be misunderstood as advertising-platform views rather than website pageviews.
7. Important non-sensitive funnel interactions were not recorded.
8. The dashboard had no ordered anonymous session-journey view.

## Files Reviewed

- `.htaccess`
- `assets/js/tracker.js`
- `assets/js/main.js`
- `track.php`
- `index.php`
- `includes/analytics.php`
- `includes/contact.php`
- `includes/application-modal.php`
- `admin/api.php`
- `admin/admin.js`
- `admin/dashboard.php`
- `admin/admin.css`

## Files Modified

- `.htaccess`
- `assets/js/tracker.js`
- `track.php`
- `admin/api.php`
- `admin/admin.js`
- `admin/dashboard.php`
- `admin/admin.css`

No database schema was added or changed.

The enquiry form, Thank You text/design, WhatsApp flow, and `enquiry-thank-you-message` class were not modified.

## Exact Changes

### Campaign session handling

The existing campaign-change session logic in `assets/js/tracker.js` remains intact:

- Direct to Facebook starts a new attributed session.
- Facebook to Instagram starts a new session.
- Campaign A to Campaign B starts a new session.
- Same-campaign reloads reuse the session.
- Internal navigation reuses the session.
- The 30-minute timeout remains unchanged.

Only new interaction listeners were appended to the tracker.

### Legacy redirects

Course and blog redirect rules now remove only the routing `slug` parameter and preserve all other query parameters when `slug` appears first, middle, or last.

Examples of values preserved:

- `utm_source`
- `utm_medium`
- `utm_campaign`
- `utm_content`
- `utm_term`
- `utm_id`
- `fbclid`
- `gclid`
- `dclid`
- `gbraid`
- `wbraid`
- `msclkid`
- `ttclid`
- `twclid`
- `li_fat_id`

Apache configuration validation result: `Syntax OK`.

### Shared dashboard filter scope

The API creates one date-bounded filtered session scope per request. The following optional exact-match filters are supported together:

- Source
- Medium
- Channel
- Campaign
- Landing page

Date-bounded pageview and conversion-count scopes are derived from the same filtered sessions. Period KPIs, trends, acquisition reports, campaign/social reports, page reports, conversion reports, Recent Visitors, SEO attribution, filtered enquiries, and journeys use this common dataset.

All-time/current operational cards such as Total Visitors, Today's Visitors, Active Users, and collector health remain explicitly global/current rather than being presented as filtered-period metrics.

### Recent Visitors

Recent Visitors now joins the common filtered session scope. It therefore respects both the selected date range and the five acquisition filters.

### Returning Visitors

A visitor is now counted as returning only when an earlier analytics session exists before the selected period start. The current lifetime `is_returning` flag is no longer used for historical period reporting.

### Conversion Rate

Before:

```text
Conversion actions / sessions × 100
```

After:

```text
Distinct converting sessions / sessions × 100
```

Raw conversion-action totals remain visible separately.

### Social labels

Social cards now say `website pageviews`, and the dashboard explains that these figures are not platform impressions, reach, engagements, or video views.

### Interaction events

The following event types were added using the existing `analytics_events` table:

- `scroll_depth` at 25%, 50%, 75%, and 100%
- `course_card_click`
- `enquiry_modal_open`
- `enquiry_form_start`
- `form_validation_failure`
- `thank_you_shown`
- `cta_click`
- `engaged_session`

Safeguards:

- No keystrokes or form-field values are recorded.
- Labels contain only interaction type/placement information.
- Form-start and validation-failure events are de-duplicated per form view.
- Thank You and engaged-session events are de-duplicated per analytics session.
- Scroll events use four milestones to avoid event spam.
- These interaction events are not added to the conversion-type list and therefore do not inflate conversion totals.

### User Journeys

A dedicated User Journeys dashboard tab now shows, where available:

- Anonymous visitor ID prefix
- Session ID prefix
- Entry time
- Source, channel, medium, and campaign
- Landing page
- Ordered pageviews
- Ordered important interaction/conversion events
- Exit page
- Duration
- Enquiry/conversion outcome and timestamp

Personal enquiry field values are not included in the journey view.

## Local Verification

### Syntax and configuration

| Check | Result |
|---|---|
| `admin/api.php` PHP syntax | Pass |
| `admin/dashboard.php` PHP syntax | Pass |
| `track.php` PHP syntax | Pass |
| `assets/js/tracker.js` JavaScript syntax | Pass |
| `admin/admin.js` JavaScript syntax | Pass |
| Apache configuration | `Syntax OK` |
| Admin API query errors | `0` |

### Database vs API vs dashboard mapping

Selected local period: `2026-10-01`.

| Metric | Database Result | Admin API Result | Dashboard Result | Status |
|---|---:|---:|---:|---|
| Unique visitors | 7 | 7 | 7 | Match |
| Sessions | 8 | 8 | 8 | Match |
| Pageviews | 10 | 10 | 10 | Match |
| Conversion actions | 2 | 2 | 2 | Match |
| Converting sessions | 1 | 1 | 1 | Match |
| Conversion rate | 12.50% | 12.50% | 12.50% | Match |
| Recent Visitors | 8 | 8 | 8 | Match |
| User journeys | 8 | 8 | 8 | Match |

Dashboard Result means the value mapped by `admin/admin.js` from the verified API response. An automated graphical browser was not available in this workspace.

### Filter verification

| Filter | Sessions DB/API | Pageviews DB/API | Conversion actions DB/API | Converting sessions DB/API | Status |
|---|---:|---:|---:|---:|---|
| Facebook | 2 / 2 | 4 / 4 | 2 / 2 | 1 / 1 | Match |
| Instagram | 1 / 1 | 1 / 1 | 0 / 0 | 0 / 0 | Match |
| YouTube | 1 / 1 | 1 / 1 | 0 / 0 | 0 / 0 | Match |
| Google | 1 / 1 | 1 / 1 | 0 / 0 | 0 / 0 | Match |
| Direct channel | 3 / 3 | 3 / 3 | 0 / 0 | 0 / 0 | Match |
| `phase3_reload` campaign | 1 / 1 | 3 / 3 | 0 / 0 | 0 / 0 | Match |
| `paid_social` medium | 3 / 3 | 5 / 5 | 2 / 2 | 1 / 1 | Match |
| `/iuc-website` landing page | 2 / 2 | 2 / 2 | 0 / 0 | 0 / 0 | Match |

The Facebook-filtered case demonstrates the corrected formula: two conversion actions occurred in one converting session across two total sessions, so the rate is 50%, not 100%.

### Interaction receiver verification

All eight new interaction event types returned a successful tracking response and produced one matching `analytics_events` row during transaction-based tests.

The verification transactions were rolled back. No interaction-test analytics rows were retained.

### Enquiry and Thank You safeguards

- `index.php`, enquiry persistence logic, and WhatsApp processing were not changed.
- Server-side successful `contact_form` conversion recording remains unchanged.
- The `enquiry-thank-you-message` class remains unchanged.
- The tracker observes that existing class to send the separate `thank_you_shown` event.
- No new real enquiry was submitted during this implementation test, avoiding a test WhatsApp/customer notification.

## UTM Redirect Verification

The course/blog rules cover `slug` first, middle, last, and slug-only cases, and Apache accepted the configuration.

An actual HTTPS redirect response could not be completed from the local CLI because the local XAMPP TLS endpoint did not provide usable credentials to the CLI client. The redirect must therefore be rechecked over HTTP network tools after deployment.

## Campaign and Source Verification

Local stored data and Admin API aggregation matched for Facebook, Instagram, YouTube, Google, Direct, a named campaign, a named medium, and landing-page filters.

The already-tested campaign-session behavior was preserved. No production campaign result is claimed because the updated files are not deployed from this workspace.

## Remaining Limitations

- Production deployment and live browser/network verification remain pending.
- A graphical end-to-end dashboard render was not automated because this workspace has no browser automation runtime installed.
- True Meta/YouTube/Google Ads impressions, reach, video views, clicks, spend, CPC, CPM, CPV, and ROAS still require separate advertising-platform API/report integrations.
- The separate campaign cost/notes metadata issue was not changed.
- Source/campaign filters apply to IUC website analytics only, not Search Console's separate aggregate API dataset or GA4 Realtime.

## Production Deployment Status

Not deployed.

After deployment, repeat the requested browser flow for Direct, Facebook, Instagram, YouTube, Google Ads, generic UTM, both legacy redirects, campaign switching, reload/internal navigation, enquiry modal/start/validation/success/Thank You, WhatsApp, CTA, and scroll milestones while checking:

```text
Browser URL → tracker.js → track.php → database → Admin API → dashboard
```
