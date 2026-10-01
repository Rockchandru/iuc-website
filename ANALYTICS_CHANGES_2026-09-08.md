# IUC Analytics Changes — 2026-09-08

## Problems confirmed

- `gad_source=2` and `gad_campaignid` were being treated like source names. They are Google advertising parameters, not human-readable platform or campaign names.
- YouTube/Facebook referrers could be overridden by a generic Google click marker, producing the wrong platform.
- Old tracker requests could create repeated `analytics_campaigns` rows for one session.
- A successful contact form was recorded once by PHP and then a second time by browser JavaScript after redirect.
- The Live page counted sessions from five minutes while its chart said 30 minutes. Google Analytics Realtime reports distinct active users, so the figures were not comparable.
- Hidden tabs continued to send heartbeats. This inflated active counts and produced very large `time_on_page` values.
- The live chart existed only in dashboard memory and was lost on refresh.
- Landing pages were split into repeated rows by `gad_*`, click-ID and UTM query parameters.
- `analytics_campaign_meta` was empty because it is a manual cost/note table; it is not the automatically collected campaign visit table.

## Changes implemented

### Source and campaign attribution

- Store a canonical source such as `youtube`, `facebook`, `google` or `bing`.
- Display professional platform labels such as YouTube, Facebook, Google and Microsoft Bing.
- Prefer explicit UTM values, then a recognised external referrer, then a click-ID provider.
- Preserve `campaign_id`, `click_id_type` and `click_id` separately instead of showing them as a source.
- Identify paid YouTube traffic as `YouTube Ads / paid_video` when YouTube referrer evidence and a Google advertising marker are both present.
- Keep traffic as Direct/Unattributed when there is no UTM, recognised referrer or click ID. The system does not guess.
- Preserve the first external referrer for the whole session so later internal page navigation cannot replace the acquisition source.

### Duplicate campaign protection

- Enforce one `analytics_campaigns` row per `session_id`.
- Before existing duplicate rows are removed, they are copied to `analytics_campaigns_duplicate_archive` so the cleanup is recoverable.
- New campaign writes use an upsert and cannot create another repeated row for the same session.
- Existing blank source rows are backfilled only where stored referrer/channel evidence supports the result.
- Contact form conversions now use the single authoritative server-side event; the duplicate browser-side success event was removed.

### Live visitor measurement

- Main Live KPI: distinct IUC visitor IDs active in the last 30 minutes.
- Recently Active KPI/table: distinct IUC visitor IDs active in the last 5 minutes.
- Session count is shown separately and is not presented as a user count.
- `analytics_live_activity` stores one presence row per session/minute, allowing a persistent 30-minute graph after refresh.
- Heartbeats run only while the page is visible. Hidden tabs no longer remain active indefinitely.
- Returning to a tab after 30 minutes starts a new session instead of reviving an expired session.
- Time on page is visible engaged time and is capped server-side at 24 hours as a defensive limit.
- Admin/dashboard pages remain excluded from tracking.

### URLs and dashboard labels

- Tracking-only query parameters are removed from reporting URLs, so `/`, `/?gad_source=...` and `/?utm_source=...` are grouped as one page.
- Campaign, recent visitor and live visitor tables provide a clickable exact normalized page URL.
- Campaign rows display platform, traffic type, campaign name or exact campaign ID.

## Required campaign link format

Every managed ad or post must have a unique UTM link. Referrers are often removed by mobile apps and privacy controls, so an exact platform cannot always be recovered without UTM values.

YouTube paid video:

```text
https://www.iucedu.com/?utm_source=youtube&utm_medium=paid_video&utm_campaign=python_chennai_sep&utm_content=video_ad_01
```

YouTube unpaid post:

```text
https://www.iucedu.com/?utm_source=youtube&utm_medium=organic_social&utm_campaign=python_chennai_sep&utm_content=video_post_01
```

Facebook paid ad:

```text
https://www.iucedu.com/?utm_source=facebook&utm_medium=paid_social&utm_campaign=python_chennai_sep&utm_content=carousel_ad_01
```

Change `utm_content` for every video, creative or placement. Keep names lowercase and stable. `gad_source=2` alone cannot prove whether a visitor came from YouTube, Search, Display or another Google Ads inventory; use UTMs for that distinction.

## Database migration

The migration runs automatically through `an_ensure_tables()` on the first analytics/admin request after deployment. It adds:

- `campaign_id`, `click_id_type`, and `click_id` to `analytics_sessions` and `analytics_campaigns`;
- `analytics_live_activity` for minute-level live presence;
- `analytics_migrations` to make data backfills run once;
- unique index `analytics_campaigns.uk_campaign_session` after duplicate rows are archived.

No visitor, session, pageview, event or enquiry history is cleared. Only duplicate campaign copies are moved to the archive table before removal from the active campaign table.

## Why IUC and Google Analytics can still differ

The IUC dashboard and Google Analytics are separate measurement systems. GA can be blocked by consent settings, browser privacy tools or ad blockers; IUC uses a first-party local visitor ID. GA can also apply internal-traffic filters and processing rules that the IUC database does not know. The IUC Live page now uses the same type of comparison—distinct users over 5/30-minute windows—but identical counts are not guaranteed.

## Verification checklist

1. Deploy the changed PHP and JavaScript files together.
2. Open a tagged YouTube test link in a private browser window.
3. Confirm the Live page shows `YouTube` or `YouTube Ads`, not `gad_source=2`.
4. Confirm the exact page URL does not contain UTM or click-ID parameters.
5. Leave the tab visible and confirm the five-minute activity/card updates.
6. Hide/close the tab and confirm it leaves the five-minute list after five minutes.
7. Refresh the dashboard and confirm the 30-minute graph remains populated.
8. Run `SHOW INDEX FROM analytics_campaigns;` and confirm `uk_campaign_session` exists.
9. Check `analytics_campaigns_duplicate_archive` if an old duplicate row must be reviewed or restored.

## Files changed for this analytics fix

- `assets/js/tracker.js`
- `track.php`
- `includes/analytics.php`
- `admin/api.php`
- `admin/admin.js`
- `admin/dashboard.php`
- `includes/contact.php`
