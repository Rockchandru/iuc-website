<?php
/* ═══════════════════════════════════════════════════════════════
   IUC Edu — Database Connection
   ═══════════════════════════════════════════════════════════════ */

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'iuc_website';
$conn = null;

// Connect without database first, create if needed, then reconnect
$temp = @new mysqli($db_host, $db_user, $db_pass);
if ($temp && !$temp->connect_error) {
    $temp->query("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $temp->close();
    $conn = @new mysqli($db_host, $db_user, $db_pass, $db_name);
    if ($conn && !$conn->connect_error) {
        $conn->query("CREATE TABLE IF NOT EXISTS enquiries (
            id INT AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(100) NOT NULL,
            phone VARCHAR(20) NOT NULL,
            email VARCHAR(100) NOT NULL,
            course VARCHAR(200) NOT NULL,
            message TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
}
