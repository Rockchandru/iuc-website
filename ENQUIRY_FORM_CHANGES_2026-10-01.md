# Enquiry Form Changes — 1 October 2026

## Summary

The enquiry forms were updated to add a Digital Marketing tracking class to successful Thank You messages and to display the generated four-digit security code inside the Enter Code field area.

No enquiry messages, backend validation rules, APIs, database logic, or submission behavior were changed.

## Files Reviewed

- `index.php`
- `db.php`
- `includes/functions.php`
- `includes/contact.php`
- `includes/application-modal.php`
- `includes/whatsapp-enquiry.php`
- `assets/js/main.js`
- `assets/css/style.css`

## Files Modified

- `includes/contact.php`
- `includes/application-modal.php`
- `assets/js/main.js`
- `assets/css/style.css`

## Digital Marketing Tracking Class

The following class was added to successful enquiry Thank You messages:

```text
enquiry-thank-you-message
```

The class is present on the main contact-form success message. For the application modal, JavaScript adds it only after a successful submission and removes it when the modal is reset or an error occurs.

The existing Thank You message text and visual design remain unchanged.

## Security Code UI

- The generated four-digit security code now appears on the right side inside the Enter Code input area.
- The input reserves sufficient right-side space so typed text does not overlap the displayed code.
- The implementation applies to both the main contact form and application modal.
- Numeric keyboard support is enabled on mobile devices through `inputmode="numeric"`.
- Existing four-character limits, server-side validation, CAPTCHA generation, and form submission flow remain unchanged.

## Test Results

- PHP syntax validation passed for `index.php`, `includes/contact.php`, and `includes/application-modal.php`.
- JavaScript syntax validation passed for `assets/js/main.js`.
- Both enquiry forms rendered a four-digit security code inside their input wrapper.
- The existing modal CAPTCHA refresh logic still targets and updates the displayed code.
- The tracking class is conditionally applied to successful modal responses and removed for reset/error states.
- No unrelated tracked files were modified.

## Testing Limitation

A complete successful database-backed submission could not be executed in the local environment because Apache and MySQL were not running, and the configured PHP session directory denied writes. Therefore, live database insertion and the final successful response were not tested end to end locally.

No unrelated application issue was changed.
