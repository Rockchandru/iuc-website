<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/whatsapp-enquiry.php';

$config = wa_config();
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $mode = (string)($_GET['hub_mode'] ?? '');
    $token = (string)($_GET['hub_verify_token'] ?? '');
    $challenge = (string)($_GET['hub_challenge'] ?? '');
    if ($mode === 'subscribe' && $config['verify_token'] !== '' && hash_equals($config['verify_token'], $token)) {
        header('Content-Type: text/plain; charset=UTF-8');
        echo $challenge;
        exit;
    }
    http_response_code(403);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}
$raw = (string)file_get_contents('php://input');
$signature = (string)($_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '');
if ($config['app_secret'] === '' || $signature === '') {
    http_response_code(403);
    exit;
}
$expected = 'sha256=' . hash_hmac('sha256', $raw, $config['app_secret']);
if (!hash_equals($expected, $signature)) {
    http_response_code(403);
    exit;
}
$payload = json_decode($raw, true);
if (!is_array($payload) || !isset($conn) || !$conn || !wa_ensure_schema($conn)) {
    http_response_code(200);
    exit;
}
foreach (($payload['entry'] ?? []) as $entry) {
    foreach (($entry['changes'] ?? []) as $change) {
        foreach (($change['value']['statuses'] ?? []) as $statusRow) {
            $providerId = wa_safe_text($statusRow['id'] ?? '', 191);
            $delivery = strtolower(wa_safe_text($statusRow['status'] ?? '', 20));
            if ($providerId === '' || !in_array($delivery, ['sent', 'delivered', 'read', 'failed'], true)) continue;
            $errorCode = null; $errorMessage = null; $status = 'SENT'; $retryable = 0;
            if ($delivery === 'failed') {
                $status = 'FAILED';
                $errorCode = wa_safe_text($statusRow['errors'][0]['code'] ?? 'DELIVERY_FAILED', 80);
                $errorMessage = wa_safe_text($statusRow['errors'][0]['title'] ?? $statusRow['errors'][0]['message'] ?? 'WhatsApp delivery failed.', 500);
            }
            $stmt = $conn->prepare("UPDATE whatsapp_enquiry_messages SET status = ?, delivery_status = ?,
                is_retryable = ?, error_code = ?, error_message = ? WHERE provider_message_id = ?");
            if ($stmt) {
                $stmt->bind_param('ssisss', $status, $delivery, $retryable, $errorCode, $errorMessage, $providerId);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
}
http_response_code(200);
header('Content-Type: application/json; charset=UTF-8');
echo '{"ok":true}';
