<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/_auth.php';
$_SESSION = [];
session_destroy();
header('Location: ' . BASE_URL . '/');
exit;
