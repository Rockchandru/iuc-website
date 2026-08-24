<?php
/* ═══════════════════════════════════════════════════════════════
   IUC Edu — Admin Authentication (fixed credentials)
   Username : IUCEducation
   Password : Iuc@12345   (stored as SHA-256, never plaintext)
   ═══════════════════════════════════════════════════════════════ */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

define('ADMIN_USER', 'IUCEducation');
define('ADMIN_PASS_HASH', '2844fb5980a2287d9a8f14011e70c759070d54a0010633e7cd1a9d24cadbe59f');

function admin_logged_in() {
    return !empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function admin_require() {
    if (!admin_logged_in()) {
        $self = dirname($_SERVER['SCRIPT_NAME']);
        $base = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\');
        header('Location: ' . $base . '/admin/index.php');
        exit;
    }
}

function admin_verify($u, $p) {
    return hash_equals(ADMIN_USER, (string)$u)
        && hash_equals(ADMIN_PASS_HASH, hash('sha256', (string)$p));
}
