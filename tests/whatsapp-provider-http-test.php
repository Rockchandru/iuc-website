<?php
ini_set('session.save_path', dirname(__DIR__) . '/.playwright-mcp');
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/whatsapp-enquiry.php';

$course = wa_resolve_course('java');
$data = $course ? wa_course_template_data('Local Student', $course) : null;
$config = array_merge(wa_config(), [
    'enabled' => true,
    'provider' => 'meta_cloud',
    'api_base_url' => 'http://127.0.0.1:8765',
    'allow_local_test_endpoint' => true,
    'access_token' => 'local-test-token',
    'phone_number_id' => '123456',
    'graph_version' => 'v99.0',
    'template_name' => 'iuc_course_enquiry_confirmation',
    'template_language' => 'en_US',
    'timeout' => 5,
]);

$failures = [];
$success = wa_send_meta_template('+919876543210', $data, $config);
if (empty($success['ok']) || ($success['provider_message_id'] ?? '') !== 'wamid.local-success-001') {
    $failures[] = 'Official-provider HTTP success response was not accepted.';
} else {
    echo "PASS: HTTP template request, authorization, nine variables and provider message ID\n";
}

$failure = wa_send_meta_template('+919999990000', $data, $config);
if (!empty($failure['ok']) || ($failure['error_code'] ?? '') !== '4' || empty($failure['retryable'])) {
    $failures[] = 'Retryable provider rejection was not classified correctly.';
} else {
    echo "PASS: Provider rejection is safely parsed and marked retryable\n";
}

$badEndpoint = $config;
$badEndpoint['api_base_url'] = 'http://example.test';
unset($badEndpoint['allow_local_test_endpoint']);
$blocked = wa_send_meta_template('+919876543210', $data, $badEndpoint);
if (($blocked['error_code'] ?? '') !== 'API_URL_INVALID') {
    $failures[] = 'Non-Meta endpoint was not blocked.';
} else {
    echo "PASS: Non-Meta production endpoint is blocked\n";
}

foreach ($failures as $failureMessage) echo 'FAIL: ' . $failureMessage . "\n";
echo 'RESULT: ' . (3 - count($failures)) . ' passed, ' . count($failures) . " failed\n";
exit($failures ? 1 : 0);
