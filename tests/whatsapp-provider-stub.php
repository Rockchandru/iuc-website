<?php
/* Local Meta Cloud API test double. Never deploy this file. */
header('Content-Type: application/json; charset=UTF-8');

$authorization = (string)($_SERVER['HTTP_AUTHORIZATION'] ?? '');
$payload = json_decode((string)file_get_contents('php://input'), true);
$validPath = preg_match('~^/v99\.0/123456/messages$~', (string)parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH));
$validPayload = is_array($payload)
    && ($payload['messaging_product'] ?? '') === 'whatsapp'
    && ($payload['type'] ?? '') === 'template'
    && count($payload['template']['components'][0]['parameters'] ?? []) === 9;

if ($authorization !== 'Bearer local-test-token' || !$validPath || !$validPayload) {
    http_response_code(400);
    echo json_encode(['error' => ['code' => 100, 'message' => 'Invalid local test request.']]);
    exit;
}

if (($payload['to'] ?? '') === '919999990000') {
    http_response_code(429);
    echo json_encode(['error' => ['code' => 4, 'message' => 'Local rate-limit simulation.']]);
    exit;
}

echo json_encode(['messaging_product' => 'whatsapp', 'messages' => [['id' => 'wamid.local-success-001']]]);
