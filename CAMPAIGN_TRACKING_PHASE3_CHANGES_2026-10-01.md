# Campaign Tracking Phase 3 Changes — 1 October 2026

## Status

Phase 3 implementation is complete.

The campaign attribution fix was implemented and verified locally through the tracker, `track.php`, database, enquiry conversion flow, and Admin Analytics API.

## Root Cause Fixed

The browser tracker previously reused the current 30-minute analytics session even when an existing visitor opened a new or different campaign URL.

Although `track.php` detected the source in the incoming request, an existing database session retained its original attribution. As a result, later Facebook, Instagram, YouTube, Google, or UTM campaign visits could remain displayed as Direct or as an earlier campaign in Admin Analytics.

## File Modified in Phase 3

- `assets/js/tracker.js`

No change to `track.php` was required.

The enquiry form, Thank You message, database schema, Admin dashboard UI, and unrelated APIs were not modified during Phase 3.

## Exact Logic Added

The tracker now compares incoming acquisition information with the attribution stored for the active session.

Compared values include:

- `utm_source`
- `utm_medium`
- `utm_campaign`
- `utm_content`
- `utm_term`
- Campaign ID
- Click-ID type
- Click-ID value
- Recognized external acquisition referrer

Supported referrer-source detection includes:

- Facebook
- Instagram
- YouTube
- LinkedIn
- WhatsApp
- X / Twitter
- TikTok
- Google
- Bing
- Yahoo
- DuckDuckGo
- Other external referral hosts

When the incoming attribution is different, the tracker:

1. Generates a new analytics session ID.
2. Stores the new session ID in `localStorage`.
3. Clears the previous session attribution in memory.
4. Creates new attribution using the current campaign URL and referrer.
5. Sends the pageview through the existing tracking flow.

## Session Rules After the Fix

| Scenario | Result |
|---|---|
| Fresh Direct visit | New Direct session |
| Fresh campaign visit | New attributed campaign session |
| Direct → Facebook | New Facebook session |
| Facebook → Instagram | New Instagram session |
| Facebook campaign A → campaign B | New campaign B session |
| Same campaign reload | Existing session reused |
| Campaign → internal page | Existing campaign session reused |
| Direct navigation during an active campaign | Existing session reused |
| Session inactive for more than 30 minutes | New session through existing timeout logic |

This prevents duplicate campaign sessions from normal reloads while allowing genuinely different acquisition visits to appear correctly.

## Before vs. After

### Before

- An active Direct session could remain Direct after opening a Facebook campaign URL.
- An active Facebook campaign could remain attributed to Facebook after opening an Instagram campaign.
- A second campaign within 30 minutes could be lost.
- The Admin dashboard displayed the original session attribution.

### After

- A changed campaign or acquisition source starts a new session.
- The new session stores the correct source, medium, campaign, content, term, and click ID.
- Reloads and internal navigation remain in the same session.
- Admin Analytics receives the corrected session attribution.

## Tracker Test Results

All isolated tracker tests passed:

- Fresh Direct visit
- Fresh Facebook campaign
- Fresh Instagram campaign
- Fresh YouTube campaign
- Fresh Google campaign
- Direct → Facebook within 30 minutes
- Facebook → Instagram within 30 minutes
- Facebook campaign A → Facebook campaign B
- Same Facebook campaign reload
- Campaign page → normal internal navigation
- External Facebook referrer detection
- Repeated Facebook referrer detection
- Campaign attribution synchronized to the enquiry form
- Existing 30-minute timeout behavior

JavaScript syntax validation passed for `assets/js/tracker.js`.

## Database Verification

Local `track.php` and MySQL verification produced the following results:

| Campaign | Stored channel | Sessions | Pageviews | Campaign rows |
|---|---|---:|---:|---:|
| Fresh Direct | Direct | 1 | 1 | 0 |
| `phase3_facebook` | Facebook Ads | 1 | 1 | 1 |
| `phase3_instagram` | Instagram Ads | 1 | 1 | 1 |
| `phase3_youtube` | YouTube Ads | 1 | 1 | 1 |
| `phase3_google` | Google Ads | 1 | 1 | 1 |
| `phase3_reload` | Facebook Ads | 1 | 3 | 1 |

The `phase3_reload` test included the first pageview, a reload, and internal navigation. It remained one session and one campaign row while correctly counting all three pageviews.

## Enquiry and Conversion Verification

A local campaign-attributed enquiry was submitted successfully.

- Submission response: `303 See Other`
- Source: `facebook`
- Medium: `paid_social`
- Campaign: `phase3_facebook`
- Content: `feed`
- Term: `course`
- Session ID: correctly linked
- Successful `contact_form` conversion: recorded

The enquiry's stored campaign attribution matched its analytics session.

## Thank You Tracking Class Verification

The existing Digital Marketing tracking class remains unchanged:

```text
enquiry-thank-you-message
```

The class was rendered successfully after the local enquiry submission.

## Admin Analytics Verification

The actual local Admin Analytics API aggregation returned:

| Campaign | Source | Sessions | Views | Conversions |
|---|---|---:|---:|---:|
| `phase3_facebook` | Facebook | 1 | 1 | 2 |
| `phase3_instagram` | Instagram | 1 | 1 | 0 |
| `phase3_youtube` | YouTube | 1 | 1 | 0 |
| `phase3_google` | Google | 1 | 1 | 0 |
| `phase3_reload` | Facebook | 1 | 3 | 0 |

Admin Analytics API query errors: `0`.

## Production Deployment Status

The corrected tracker has been verified locally but has not been deployed to the live website from this workspace.

Live production verification must be performed after deploying the updated `assets/js/tracker.js`. The existing file-version query generated with `filemtime()` should change after deployment and request the updated tracker asset.

## Remaining Limitations

- Local verification created identifiable `phase3_*` analytics records and two test enquiries in the local database.
- The secondary campaign notes/cost metadata issue remains pending and was intentionally not changed in this task.
- No production result should be claimed until the updated tracker is deployed and the same scenarios are repeated against the live website.
