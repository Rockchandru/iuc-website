<?php
/* Local, non-production verification for WhatsApp enquiry behavior. */
ini_set('session.save_path', dirname(__DIR__) . '/.playwright-mcp');
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/whatsapp-enquiry.php';

$failures = [];
$passes = [];
$assert = static function ($condition, $label) use (&$failures, &$passes) {
    if ($condition) $passes[] = $label;
    else $failures[] = $label;
};

$java = wa_resolve_course('Java & J2EE Programming');
$python = wa_resolve_course('Python');
$cloud = wa_resolve_course('cloud-computing');
$assert(($java['slug'] ?? '') === 'java' && ($java['price'] ?? '') === '₹46,000', 'Java course and fee resolve dynamically');
$assert(($python['slug'] ?? '') === 'python' && ($python['duration'] ?? '') === '3 Months', 'Python course and duration resolve dynamically');
$assert(($cloud['slug'] ?? '') === 'cloud-computing', 'Third course resolves dynamically');
$assert(wa_resolve_course('Unknown Course') === null, 'Unknown course is rejected');

$data = wa_course_template_data('Chandru', $java);
$assert(is_array($data) && $data['student_name'] === 'Chandru', 'Student name is included');
$assert(($data['contact_phone'] ?? '') === '+917418048039', 'IUC mobile comes from website configuration');
$assert(($data['contact_email'] ?? '') === 'info@iucedu.com', 'IUC email comes from website configuration');
$payload = wa_meta_template_payload('+919876543210', $data, array_merge(wa_config(), [
    'template_name' => 'iuc_course_enquiry_confirmation', 'template_language' => 'en_US',
]));
$assert(($payload['to'] ?? '') === '919876543210', 'Provider destination uses normalized student phone');
$assert(count($payload['template']['components'][0]['parameters'] ?? []) === 9, 'Template contains all nine dynamic parameters');

$assert(wa_normalize_phone('98765 43210') === '+919876543210', 'Ten-digit Indian phone is normalized');
$assert(wa_normalize_phone('+91-98765-43210') === '+919876543210', 'Country-code Indian phone is normalized');
$assert(wa_normalize_phone('123') === null, 'Invalid phone is rejected');
$missingFee = $java;
$missingFee['price'] = '';
$assert(wa_course_template_data('Student', $missingFee) === null, 'Missing course fee prevents message construction');

$disabled = wa_config();
$disabled['enabled'] = false;
$code = null; $message = null;
$assert(!wa_provider_ready($disabled, $code, $message) && $code === 'PROVIDER_DISABLED', 'Disabled provider fails safely');

/* Database idempotency checks use a transaction and roll back every test row. */
require_once dirname(__DIR__) . '/db.php';
if (!isset($conn) || !$conn) {
    // The checked-in connection uses the hosting account. For local-only tests,
    // fall back to an isolated XAMPP database instead of changing application config.
    $conn = @new mysqli('localhost', 'root', '', 'iuc_whatsapp_test');
}
if (isset($conn) && $conn && wa_ensure_schema($conn)) {
    $conn->begin_transaction();
    try {
        $insert = $conn->prepare("INSERT INTO enquiries (full_name, phone, email, course, message, created_at)
            VALUES (?, ?, ?, ?, 'WhatsApp local test', NOW())");
        $name = 'WhatsApp Test Student'; $phone = '9876543210'; $email = 'whatsapp-test@example.invalid';
        $courseName = 'Java & J2EE Programming';
        $insert->bind_param('ssss', $name, $phone, $email, $courseName);
        $insert->execute();
        $enquiryId = (int)$insert->insert_id;
        $insert->close();

        $requestKey = str_repeat('a', 32);
        $first = wa_enqueue_enquiry($conn, $enquiryId, $requestKey, true);
        $second = wa_enqueue_enquiry($conn, $enquiryId, $requestKey, true);
        $assert(!empty($first['queued']) && $first['message_id'] === $second['message_id'], 'Duplicate enqueue returns the same outbox row');
        $countResult = $conn->query("SELECT COUNT(*) AS c FROM whatsapp_enquiry_messages WHERE enquiry_id = " . $enquiryId)->fetch_assoc();
        $assert((int)$countResult['c'] === 1, 'Unique enquiry constraint prevents duplicate messages');

        if (!wa_config()['enabled']) {
            $failed = wa_process_message($conn, $first['message_id']);
            $assert(($failed['status'] ?? '') === 'FAILED' && ($failed['error_code'] ?? '') === 'PROVIDER_DISABLED', 'Provider failure is recorded without throwing');
            $again = wa_process_message($conn, $first['message_id']);
            $assert(!empty($again['duplicate_prevented']), 'Immediate repeated trigger does not send again');
        }

        $noConsent = wa_enqueue_enquiry($conn, $enquiryId + 999999, str_repeat('b', 32), false);
        $assert(($noConsent['status'] ?? '') === 'SKIPPED', 'No-consent request is not queued');

        $insert = $conn->prepare("INSERT INTO enquiries (full_name, phone, email, course, message, created_at)
            VALUES (?, '123', ?, ?, 'WhatsApp invalid phone test', NOW())");
        $insert->bind_param('sss', $name, $email, $courseName);
        $insert->execute(); $invalidId = (int)$insert->insert_id; $insert->close();
        $invalid = wa_enqueue_enquiry($conn, $invalidId, str_repeat('c', 32), true);
        $invalidRow = wa_get_message($conn, $invalid['message_id']);
        $assert(($invalidRow['error_code'] ?? '') === 'INVALID_PHONE' && (int)$invalidRow['is_retryable'] === 0, 'Invalid phone is permanently failed while enquiry remains');

        $unknown = 'Unknown Course';
        $insert = $conn->prepare("INSERT INTO enquiries (full_name, phone, email, course, message, created_at)
            VALUES (?, ?, ?, ?, 'WhatsApp unknown course test', NOW())");
        $insert->bind_param('ssss', $name, $phone, $email, $unknown);
        $insert->execute(); $unknownId = (int)$insert->insert_id; $insert->close();
        $unknownQueue = wa_enqueue_enquiry($conn, $unknownId, str_repeat('d', 32), true);
        $unknownRow = wa_get_message($conn, $unknownQueue['message_id']);
        $assert(($unknownRow['error_code'] ?? '') === 'COURSE_NOT_FOUND', 'Unknown course is safely recorded as failed');
    } finally {
        $conn->rollback();
    }
} else {
    $failures[] = 'Local database idempotency tests could not run';
}

foreach ($passes as $label) echo "PASS: $label\n";
foreach ($failures as $label) echo "FAIL: $label\n";
echo 'RESULT: ' . count($passes) . ' passed, ' . count($failures) . " failed\n";
exit($failures ? 1 : 0);
