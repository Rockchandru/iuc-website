<?php
/* cPanel cron entry point. This file cannot be invoked over HTTP. */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/whatsapp-enquiry.php';
if (!isset($conn) || !$conn) {
    fwrite(STDERR, "Database unavailable.\n");
    exit(1);
}
$summary = wa_process_pending($conn, 25);
echo json_encode($summary, JSON_UNESCAPED_SLASHES) . PHP_EOL;
exit(!empty($summary['error']) ? 1 : 0);
