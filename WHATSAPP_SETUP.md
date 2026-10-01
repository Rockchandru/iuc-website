# IUC WhatsApp Course Enquiry Setup

The website code uses the official Meta WhatsApp Cloud API. Enquiries continue to save when WhatsApp is unavailable.

## Approved template

Create and approve a template named `iuc_course_enquiry_confirmation` in English (US), or change the configured name and language.

Recommended body:

```text
Hello {{1}} 👋

Welcome to IUC. We have received your enquiry for the {{2}} course.

About the course:
{{3}}

Duration: {{4}}
Highlights: {{5}}
Course fee: {{6}}

Complete course details: {{7}}

For questions about admission, fees, batches or syllabus, contact us at {{8}} or {{9}}.

Thank you for choosing IUC.
```

Parameter order:

1. Student name
2. Course name
3. Short course description
4. Duration
5. Highlights
6. Fee
7. Course URL
8. IUC mobile number from `SITE_PHONE`
9. IUC email from `SITE_EMAIL`

Meta decides the final template category during review.

## Server configuration

Copy `includes/whatsapp-config.example.php` to `includes/whatsapp-config.php` or set equivalent environment variables:

```text
WHATSAPP_ENABLED=true
WHATSAPP_PROVIDER=meta_cloud
WHATSAPP_API_URL=https://graph.facebook.com
WHATSAPP_ACCESS_TOKEN=
WHATSAPP_PHONE_NUMBER_ID=
WHATSAPP_BUSINESS_ACCOUNT_ID=
WHATSAPP_APP_SECRET=
WHATSAPP_VERIFY_TOKEN=
WHATSAPP_GRAPH_VERSION=
WHATSAPP_TEMPLATE_NAME=iuc_course_enquiry_confirmation
WHATSAPP_TEMPLATE_LANGUAGE=en_US
WHATSAPP_MAX_ATTEMPTS=5
WHATSAPP_HTTP_TIMEOUT=8
```

`includes/whatsapp-config.php` is ignored by Git. Never place tokens in JavaScript or committed PHP files.

## Webhook

Configure Meta to use:

```text
https://www.iucedu.com/whatsapp-webhook.php
```

Use the same verify token configured on the server. Subscribe to message status updates. POST requests are accepted only when the `X-Hub-Signature-256` signature matches the configured Meta app secret.

## Retry worker

Run the worker from cPanel cron every five minutes:

```text
php /home4/iucteoxs/public_html/iucedu.com/whatsapp-worker.php
```

The worker is CLI-only and cannot be executed over HTTP.
