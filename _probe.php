<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/db.php';
var_dump($conn instanceof mysqli);
echo "errno: " . ($conn ? $conn->connect_errno : 'null') . "\n";
echo "err: " . ($conn ? $conn->connect_error : 'no conn object') . "\n";
