# Campaign Tracking Investigation Report — 1 October 2026

## Status

Phase 1 investigation and Phase 2 root-cause analysis are complete.

No tracking, analytics, database, enquiry, or Admin dashboard code was modified during this investigation.

## Confirmed Root Cause

Campaign visits are lost when a visitor already has an active IUC tracking session.

The browser tracker keeps one session in `localStorage` for 30 minutes. When that visitor subsequently opens a Facebook, Instagram, YouTube, Google, or other UTM-tagged URL:

1. The tracker reuses the existing `session_id`.
2. It sends the new campaign parameters to `track.php`.
3. `track.php` correctly detects the incoming source.
4. Because the database session already exists, `track.php` updates only:
   - `last_activity`
   - `ended_at`
   - `page_views`
   - `is_bounce`
   - `exit_page`
5. It does not update the existing session's source, medium, campaign, content, term, click ID, referrer, or landing page.
6. It only inserts an `analytics_campaigns` record when `$isNewSession` is true.
7. The Admin dashboard reads campaign attribution primarily from `analytics_sessions`, so it continues displaying the session's original attribution, which is often Direct.

This creates a mismatch where the live collector response can report `Facebook Ads`, while the stored session and Admin dashboard remain `Direct`.

## Live Network Evidence

The live website was checked directly at:

- <https://www.iucedu.com/>
- <https://www.iucedu.com/assets/js/tracker.js>
- <https://www.iucedu.com/track.php>

The production website loads the first-party tracker and Google Analytics 4. The deployed tracker contains the same 30-minute `localStorage` session and first-touch attribution logic found in the local code.

Fresh live production sessions returned the following results:

| Test | Live collector result |
|---|---|
| Direct | `channel: Direct`, `pv_id: 13001` |
| Google | `channel: Google Ads`, `pv_id: 13003` |
| Facebook | `channel: Facebook Ads`, `pv_id: 13004` |
| Instagram | `channel: Instagram Ads`, `pv_id: 13005` |
| YouTube | `channel: YouTube Ads`, `pv_id: 13006` |

The unique `pv_id` values confirm that the live collector accepted and inserted the pageviews.

The failure was reproduced using the same visitor and session:

| Request | Result |
|---|---|
| Initial Direct pageview | `pv_id: 13008`, `channel: Direct` |
| Later Facebook campaign pageview with the same session | `pv_id: 13009`, response `channel: Facebook Ads` |

Despite the second response, the existing database session remains Direct because the existing-session update in `track.php` does not update attribution fields. A campaign row is also not created because campaign insertion requires `$isNewSession`.

## Affected Sources

The issue affects every new campaign entered during an existing 30-minute session:

- Facebook
- Instagram
- YouTube
- Google
- Any UTM-tagged campaign
- Referrer-based acquisition changes
- `fbclid`, `gclid`, and other supported click IDs

Fresh visitors and visitors whose previous session has expired are tracked correctly. This explains why some campaign records appear while others do not.

## Campaign Values Being Lost

Existing attribution is not overwritten. Instead, first-touch values are locked for the entire active session.

The following later-arriving values can be ignored or fail to reach the stored session:

- `utm_source`
- `utm_medium`
- `utm_campaign`
- `utm_content`
- `utm_term`
- Referrer
- Landing page
- Campaign ID
- Click-ID type and value

A different campaign opened within the same session can also be discarded by `assets/js/tracker.js` because it fills attribution fields only when their stored values are empty.

## Why Admin Analytics Does Not Update

The Admin dashboard source and campaign queries read from `analytics_sessions`:

- Sources: `admin/api.php`, source query near line 373
- Channels: `admin/api.php`, channel query near line 383
- Campaigns: `admin/api.php`, campaign query near line 386
- Social media: `admin/api.php`, social query near line 413

Because an existing session retains its earlier attribution, the dashboard reports the stale database values rather than the campaign detected in the later tracking request.

The Admin frontend mapping works correctly with the data returned by the API. Date filtering is also passed to the API and applied using inclusive `00:00:00` through `23:59:59` boundaries.

## Enquiry and Conversion Tracking

The enquiry forms synchronize stored attribution immediately before submission. The backend saves those hidden values and records a successful `contact_form` conversion.

If a campaign arrived during an existing session, however, the form receives stale first-touch attribution. The enquiry can therefore save successfully while being attributed to Direct or an earlier campaign.

The recently added `enquiry-thank-you-message` class is unrelated to this issue and remains intact.

## Secondary Admin Limitation

Campaign notes and costs are keyed only by `utm_campaign`.

If the same campaign name is reused across Facebook, Instagram, YouTube, or multiple creatives, the dashboard displays separate campaign rows but maps all saved metadata using only the campaign name. Different notes and costs therefore cannot be maintained reliably for each source/content combination.

Responsible areas:

- `includes/analytics.php` — `analytics_campaign_meta` schema
- `admin/api.php` — `save_cost` handler and metadata mapping
- `admin/admin.js` — campaign-row save mapping

This is separate from the primary campaign acquisition failure.

## Files Reviewed

- `assets/js/tracker.js`
- `track.php`
- `includes/analytics.php`
- `includes/header.php`
- `includes/footer.php`
- `index.php`
- `includes/contact.php`
- `includes/application-modal.php`
- `admin/api.php`
- `admin/admin.js`
- `admin/dashboard.php`
- `db.php`
- `.htaccess`
- `ANALYTICS_CHANGES_2026-09-08.md`

## Proposed Minimum Fix Scope

The primary fix should modify:

- `assets/js/tracker.js`

The tracker should start a new analytics session when a tagged acquisition URL contains a new or different campaign/source from the currently stored attribution. Repeated page loads for the same campaign must retain the existing session to prevent duplicate analytics records.

A small defensive adjustment may also be appropriate in:

- `track.php`

No enquiry files, Thank You markup, unrelated APIs, or database structures are required for the primary fix.

## Current Implementation Status

The proposed fix has not been implemented. Phase 3 should begin only after approval of this investigation report and fix scope.
