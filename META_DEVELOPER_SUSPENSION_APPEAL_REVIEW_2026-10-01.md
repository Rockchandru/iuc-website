# Meta Developer Suspension Appeal Review — 1 October 2026

## Status

The Meta account login succeeded, but Meta Developer access is suspended.

The appeal page is open in the approved temporary browser profile. No appeal has been submitted.

WhatsApp production/API sending remains disabled. No replacement Meta account, app, WABA, number, or bypass integration was created.

## Exact Meta Notice Reviewed

- Status: `Developer account suspended`
- Policy reference: `Platform Term 7.e.i.3 — Disallowed use of apps`
- Meta's stated concern: the account may have created or maintained apps intended to circumvent an enforcement action or restriction applied to another app or developer account.
- Available action: appeal the decision.

The page does not identify a specific affected app, App ID, WABA, Phone Number ID, or action. Because the developer account is suspended, the existing app list and WhatsApp API Setup cannot currently be opened.

## Appeal Page Requirements

The appeal page asks the account owner to address:

1. Whether the owner understands the reported Platform Term violation.
2. Whether the necessary changes have been made.
3. Supporting documentation, where applicable.

The final submission is an account-owner attestation and has not been selected or submitted by the implementation agent.

## Local Project Inventory

Relevant implementation reviewed:

- `includes/whatsapp-enquiry.php`
- `includes/whatsapp-config.example.php`
- `includes/whatsapp-config.php`
- `index.php`
- `whatsapp-webhook.php`
- `whatsapp-worker.php`
- `WHATSAPP_SETUP.md`
- `tests/whatsapp-enquiry-test.php`
- `tests/whatsapp-provider-http-test.php`
- `tests/whatsapp-provider-stub.php`
- Relevant Git history
- `whatsapp_enquiry_messages` database schema and safe status aggregates

Verified local findings:

- There is one WhatsApp Cloud API implementation.
- The implementation sends through the official `graph.facebook.com` endpoint.
- A non-Meta production endpoint is explicitly rejected.
- A localhost endpoint is allowed only for isolated PHP development-server tests.
- No real Meta App ID is stored in the current project.
- No real Phone Number ID is stored in the current project.
- No real WABA ID is stored in the current project.
- No WhatsApp access token or Meta app secret is stored in the current project.
- No old or duplicate Meta app configuration was found.
- No alternate Meta account or replacement-app routing was found.
- Git history contains the same single WhatsApp integration introduction and no verified Meta access token.
- Token-like strings found in an old browser artifact were verified as Google click IDs, not Meta access tokens.

## Possible Cause Assessment

No project evidence was found that explains or supports a restriction-bypass allegation.

This does not prove that no account-side issue exists. The local repository cannot show:

- Historical Meta apps no longer represented in the project
- Actions performed directly in Meta Business or Developer dashboards
- Other developer accounts or business assets
- The specific app/action Meta used for its decision

Because Meta does not identify the affected asset on the available page, the exact cause cannot currently be determined. The appeal should request the specific app/action and a manual review.

## Corrective and Preventive Actions Actually Completed

- WhatsApp API sending remains disabled.
- A Git-ignored local configuration file exists with no credentials or IDs.
- No fake Phone Number ID, WABA ID, Graph version, or token was added.
- No replacement Meta app or account was created.
- The code's official Meta endpoint restriction was verified.
- Local course mapping, phone normalization, failure handling, and duplicate prevention were re-tested.
- No evidence was deleted or changed to influence the appeal.

No obsolete Meta configuration was removed because no obsolete App ID, WABA ID, Phone Number ID, or token was found.

## Local WhatsApp Verification

### Logic tests

`tests/whatsapp-enquiry-test.php` result:

```text
21 passed, 0 failed
```

Verified behavior includes:

- Dynamic course resolution
- Ten-digit Indian number normalization
- `+91` number normalization
- Invalid number rejection
- Nine template variables
- Enquiry preservation when WhatsApp fails
- Duplicate enqueue prevention
- Repeated-trigger prevention
- Consent handling
- Missing-course handling

### All-course payload audit

- Catalogue courses checked: `16`
- Successful course payloads: `16`
- Payload variables per course: `9`
- Mapping failures: `0`

### Database verification

- Database available: yes
- Duplicate outbox rows per enquiry: `0`
- Duplicate request keys: `0`
- Existing diagnostic rows demonstrate:
  - Safe `PROVIDER_DISABLED` failure
  - Safe `INVALID_PHONE` failure
  - Safe `COURSE_NOT_FOUND` failure
  - One previously accepted local test-double response with a provider message ID

The accepted diagnostic row is from the local Meta-shaped test double, not the real Meta API.

### Current HTTP test limitation

The current isolated HTTP mock rerun was intercepted by the execution environment's localhost layer and returned `HTTP 404` before reaching the PHP stub. The endpoint-restriction assertion passed, but two stub-response assertions were inconclusive in this rerun. No application code was changed to work around the test environment.

A real Meta API test is blocked by the Meta Developer suspension and the absence of approved credentials.

## Proposed Appeal Draft

```text
Hello Meta Developer Support,

I acknowledge the account-status notice referencing Platform Term 7.e.i.3 and understand that Meta is concerned about apps being created or maintained to circumvent an enforcement action or restriction.

I reviewed the local IUC website implementation associated with our intended WhatsApp course-enquiry workflow. The project contains one WhatsApp Cloud API integration that is restricted to the official graph.facebook.com endpoint. The local project currently contains no Meta App ID, Phone Number ID, WABA ID, access token, or app secret, and WhatsApp API sending remains disabled. I found no local code that creates Meta apps, switches developer accounts, routes through a replacement app, or attempts to bypass a Meta restriction.

I have not created a replacement account or app to avoid this suspension. I have kept the integration disabled and have not added any new Meta credentials while the review is pending.

The available account-status page does not identify the specific affected app, App ID, business asset, or action. Therefore, I could not determine the exact account-side cause from the information provided. Please manually review this suspension and identify the specific app or activity that triggered the decision so that any verified issue can be corrected accurately.

I confirm that the IUC integration will not be used to circumvent Meta restrictions or enforcement actions. Its intended purpose is only to send opted-in, course-specific WhatsApp enquiry confirmations through the official WhatsApp Business Platform after the appropriate app, business number, template, and permissions are approved.

Please review the account and restore Developer access if appropriate, or provide the specific remediation required.

Thank you.
```

## Account-Owner Confirmation Required

Before submitting, the owner must confirm that the draft is accurate in the context of all Meta accounts and apps they control.

For the question asking whether the violation is understood:

- The verified statement is that the policy concern is understood.
- The specific app/action is not known because Meta did not identify it.
- Select an answer only if it truthfully represents the owner's understanding.

For the question asking whether necessary changes were made:

- Local precautionary changes are complete: sending is disabled and no replacement integration exists.
- The underlying account-side cause has not been identified.
- Do not attest that all necessary changes were made unless the owner knows the relevant Meta account history and can confirm this truthfully.

Suggested supporting evidence, if Meta accepts it:

- This sanitized investigation report
- The Git-ignored/disabled configuration status without secret values
- The official-endpoint restriction and test summary
- A factual explanation of any historical Meta apps or enforcement actions known to the owner

Do not upload access tokens, passwords, OTPs, cookies, app secrets, or unredacted browser/session files.

## Files Modified

- `includes/whatsapp-config.php` — local-only, Git-ignored configuration created with API sending disabled and all credential/ID fields empty.
- `META_DEVELOPER_SUSPENSION_APPEAL_REVIEW_2026-10-01.md` — this report and proposed appeal draft.

No tracked application logic was modified during the suspension review.

## Blocked Until Meta Reinstatement

The following cannot be verified until Meta restores Developer access:

- Existing IUC Meta App identity/status
- Connected WhatsApp Business Account
- Actual Phone Number ID
- WABA ID
- Registration status of `+91 74180 48039`
- Current supported API version for the app
- Access-token type/status
- Approval status and WABA ownership of `iuc_course_enquiry_confirmation`
- Real Meta API acceptance and message ID
- Real WhatsApp delivery

## Production Status

Nothing was deployed to production.

## Temporary Browser Status

The temporary authenticated browser remains open on the appeal page for account-owner review. It has not yet been closed or deleted because the owner has not reviewed/submitted the appeal. It must be closed and its temporary profile deleted immediately after the appeal review is complete.
