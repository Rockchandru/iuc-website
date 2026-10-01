<?php
/* ═══════════════════════════════════════════════════════════════
   IUC Edu — Admin Authentication
   Production can override both values with IUC_ADMIN_USER and
   IUC_ADMIN_PASS_HASH environment variables.
   ═══════════════════════════════════════════════════════════════ */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('X-Robots-Tag: noindex, nofollow', true);

$configuredAdminUser = getenv('IUC_ADMIN_USER');
$configuredAdminHash = getenv('IUC_ADMIN_PASS_HASH');
define('ADMIN_USER', $configuredAdminUser !== false && $configuredAdminUser !== '' ? $configuredAdminUser : 'IUCEducation');
define('ADMIN_PASS_HASH', $configuredAdminHash !== false && $configuredAdminHash !== ''
    ? $configuredAdminHash
    : '$2y$10$ptppeDriTt9bniInEtPcqeUXbr0Yig7vtii4jD3yxZiW9eAvn.WuS');

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
        && password_verify((string)$p, ADMIN_PASS_HASH);
}
