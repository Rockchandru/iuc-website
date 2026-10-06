# WhatsApp Course Enquiry Investigation — 1 October 2026

## Status

The local WhatsApp course-enquiry workflow has been fully investigated and tested.

No application-code defect was found in the current queue, course mapping, phone normalization, payload generation, database logging, or failure handling.

The actual local failure is missing Meta WhatsApp configuration. No code file was modified because credentials cannot be invented or committed safely.

Production deployment and live Meta delivery are not claimed.

## Exact Failure Point

The current flow reaches:

```text
Course page
    → enquiry modal/form
    → index.php
    → enquiry saved
    → WhatsApp outbox row created
    → wa_process_message()
    → wa_send_meta_template()
    → wa_provider_ready()
    → stopped before Meta HTTP request
```

The exact local error is:

```text
PROVIDER_DISABLED — WhatsApp delivery is not enabled.
```

No real Meta API response exists because the provider-readiness check correctly prevents a request when configuration is incomplete.

## Root Cause

The local environment has no active WhatsApp Cloud API configuration:

| Configuration | Local status |
|---|---|
| `includes/whatsapp-config.php` | Missing |
| `WHATSAPP_ENABLED` | Disabled/unset |
| Access token | Missing |
| Phone Number ID | Missing |
| WhatsApp Business Account ID | Missing |
| Graph API version | Missing |
| App secret | Missing |
| Webhook verify token | Missing |
| Template name | Defaults to `iuc_course_enquiry_confirmation` |
| Template language | Defaults to `en_US` |
| PHP cURL | Available |

Classification: environment and Meta account configuration.

It is not a frontend, course mapping, phone normalization, payload, database schema, or queue-processing bug.

## Existing Architecture Found

### Frontend

- Course `Apply Now` links use `.apply-now-trigger` and carry the selected title in `data-course`.
- `assets/js/main.js` opens the existing enquiry modal and copies the course into the course select.
- The modal sends the existing form to `index.php` using AJAX.
- The standard contact form posts to the same handler.
- WhatsApp opt-in is explicitly required by both forms.

### Backend

`index.php` performs these operations in order:

1. Validates CSRF, required fields, email, and CAPTCHA.
2. Saves the enquiry.
3. Records the existing analytics conversion.
4. Calls `wa_enqueue_enquiry()`.
5. Calls `wa_process_message()` for a pending outbox row.
6. Preserves enquiry success even when WhatsApp fails.

### WhatsApp service

`includes/whatsapp-enquiry.php` provides:

- Configuration loading from environment variables or an ignored local configuration file
- Consent checking
- Course resolution from the existing `$courses` catalogue
- Indian/international phone normalization
- Nine-variable course-specific template construction
- Meta Cloud API request construction
- Safe provider-response parsing
- Idempotent outbox insertion
- Retry scheduling
- Permanent failure handling
- Provider message-ID storage

### Database and operations

- `whatsapp_enquiry_messages` stores queue, attempt, error, provider ID, sent time, and delivery status.
- `whatsapp-worker.php` processes retryable rows from CLI/cron.
- `whatsapp-webhook.php` verifies Meta signatures and updates sent/delivered/read/failed status.
- Admin Analytics displays the stored WhatsApp status/error with each enquiry.

## Files Reviewed

- `course.php`
- `includes/courses.php`
- `includes/application-modal.php`
- `includes/contact.php`
- `assets/js/main.js`
- `index.php`
- `includes/functions.php`
- `includes/whatsapp-enquiry.php`
- `includes/whatsapp-config.example.php`
- `db.php`
- `whatsapp-worker.php`
- `whatsapp-webhook.php`
- `admin/api.php`
- `tests/whatsapp-enquiry-test.php`
- `tests/whatsapp-provider-http-test.php`
- `tests/whatsapp-provider-stub.php`
- `WHATSAPP_SETUP.md`
- `.gitignore`

## Files Modified

No application files were modified.

This investigation report is the only new file.

## Actual Form and Database Evidence

### Python enquiry using a ten-digit number

An actual local HTTP form submission was made through the existing form handler.

| Check | Result |
|---|---|
| Course page/form loaded | Pass |
| Python option available | Pass |
| CSRF/request key/CAPTCHA present | Pass |
| Form response | Success |
| Enquiry saved | Pass |
| Course stored | `Python Programming` |
| Course slug resolved | `python` |
| `9876543210` normalized | `+919876543210` |
| WhatsApp row created | Pass |
| Send attempted | Pass, attempt count `1` |
| Result | `FAILED` |
| Error | `PROVIDER_DISABLED` |
| Provider message ID | Not created |

This proves the current break occurs after form submission, enquiry storage, mapping, normalization, and outbox insertion.

### Java enquiry using `+91`

A temporary local Meta-compatible provider stub was used with non-secret test configuration.

| Check | Result |
|---|---|
| Java course page loaded | Pass |
| Course carried into the form | Pass |
| AJAX-style form POST | Pass |
| Enquiry saved | Pass |
| Course slug resolved | `java` |
| `+91 98765 43210` normalized | `+919876543210` |
| Nine template variables sent | Pass |
| Authorization header/request path | Pass |
| Provider response contained message ID | Pass |
| Database WhatsApp status | `SENT` |
| Database delivery status | `accepted` |
| Attempt count | `1` |
| Retryable after success | `0` |
| Sent timestamp stored | Pass |

This confirms the existing application succeeds end-to-end when valid provider configuration is supplied.

The local stub message ID is test-only and is not a real WhatsApp delivery.

## Course Mapping Verification

All 16 configured course catalogue entries were tested.

- Every title resolved to its correct slug.
- Every course produced all nine template parameter values.
- Python, Java, and Cloud Computing were additionally checked individually.
- An unknown/`Other` course is rejected safely with `COURSE_NOT_FOUND`.
- Missing required fee data prevents message construction safely.
- No single course is hard-coded for all enquiries.

Template parameter order:

1. Student name
2. Course name
3. Course summary
4. Duration
5. Highlights
6. Course fee
7. Course URL
8. IUC contact number
9. IUC email

## Phone Normalization Verification

| Input | Result |
|---|---|
| `98765 43210` | `+919876543210` |
| `+91-98765-43210` | `+919876543210` |
| `123` | Rejected |

Invalid phone verification through the actual handler:

- Enquiry still saved successfully.
- WhatsApp row recorded `FAILED`.
- Error code: `INVALID_PHONE`.
- Retryable: `0`.
- No provider request was attempted.

## Missing Course Mapping Verification

An `Other` course submission was tested through the actual handler.

- Enquiry still saved successfully.
- WhatsApp row recorded `FAILED`.
- Error code: `COURSE_NOT_FOUND`.
- Retryable: `0`.
- No provider request was attempted.

## Provider Request Verification

The generated provider request uses:

```text
POST {api_base_url}/{graph_version}/{phone_number_id}/messages
Authorization: Bearer [redacted]
Content-Type: application/json
```

Payload type:

```text
messaging_product = whatsapp
recipient_type = individual
type = template
```

Local stub results:

- Valid template request returned a message ID and was accepted.
- Simulated HTTP 429/provider code `4` was parsed safely as retryable.
- A non-Meta production endpoint was blocked by the endpoint allowlist.
- No token value was logged or included in this report.

## Automated Test Result

Existing WhatsApp suite:

```text
21 passed, 0 failed
```

Covered behavior includes:

- Multiple course mappings
- Dynamic fee/duration data
- Nine-variable template payload
- Ten-digit and `+91` phone inputs
- Invalid phone
- Missing fee
- Disabled provider
- Duplicate enqueue prevention
- Unique enquiry constraint
- Safe provider failure
- No-consent skip
- Missing course mapping

## Failed-Case Handling

The current behavior meets the safety requirement:

- Enquiry is saved before WhatsApp processing.
- WhatsApp failure does not change the successful form response.
- Failure status and safe diagnostic code/message are stored.
- Sensitive provider credentials are not shown to visitors.
- Duplicate enqueue/send attempts are prevented.
- Retryable failures receive exponential retry scheduling.
- Invalid phone/course failures are permanent and not retried.

## Exact Configuration Required

Supply these as server environment variables or in the Git-ignored `includes/whatsapp-config.php` file:

```text
WHATSAPP_ENABLED=true
WHATSAPP_PROVIDER=meta_cloud
WHATSAPP_API_URL=https://graph.facebook.com
WHATSAPP_ACCESS_TOKEN=[real secret]
WHATSAPP_PHONE_NUMBER_ID=[real ID]
WHATSAPP_BUSINESS_ACCOUNT_ID=[real ID]
WHATSAPP_APP_SECRET=[real secret]
WHATSAPP_VERIFY_TOKEN=[private verification value]
WHATSAPP_GRAPH_VERSION=[supported configured version]
WHATSAPP_TEMPLATE_NAME=iuc_course_enquiry_confirmation
WHATSAPP_TEMPLATE_LANGUAGE=en_US
```

Meta-side requirements:

- WhatsApp Business Account is active.
- Sending phone number is registered and usable.
- Access token has the required WhatsApp permissions and is not expired.
- Template `iuc_course_enquiry_confirmation` is approved in `en_US`.
- The approved template body contains exactly the expected nine body variables in the same order.
- Webhook points to `https://www.iucedu.com/whatsapp-webhook.php` with the configured verify token.
- The cron worker is scheduled as documented in `WHATSAPP_SETUP.md`.

## Why No Credential File Was Created

Creating an enabled file with blank, fake, or guessed credentials would not fix delivery and could create misleading production behavior.

Real credentials were not provided and must not be committed. The code already supports a safe ignored configuration file and environment variables.

## Test Data Note

The actual HTTP workflow tests created clearly named local diagnostic enquiry/outbox records for:

- Provider-disabled Python flow
- Successful Java flow against the local provider stub
- Invalid phone flow
- Missing course mapping flow

The automated unit/database tests used transactions and rolled their rows back.

## Remaining Blocker

Valid Meta credentials and an approved template are required before a real WhatsApp message can be sent.

Once supplied locally, repeat one test using a Meta-approved recipient and confirm:

```text
Form success
→ enquiry row saved
→ WhatsApp row SENT/accepted
→ real Meta message ID stored
→ webhook updates delivered/read
→ recipient receives the course-specific message
```

## Production Deployment Status

No application deployment was performed.

No real Meta/WhatsApp message was sent during this local investigation.
