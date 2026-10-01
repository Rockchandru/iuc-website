<?php
/* ═══════════════════════════════════════════════════════════════
   IUC Edu — Database Connection
   ═══════════════════════════════════════════════════════════════ */

date_default_timezone_set('Asia/Kolkata');

$db_host = 'localhost';
$db_user = 'iucteoxs_admin';
$db_pass = 'iuctech@123';
$db_name = 'iucteoxs_contact';
$conn = null;
$db_status = ['connected' => false, 'errors' => [], 'migrations' => []];

function db_column_exists($connection, $table, $column) {
    $stmt = $connection->prepare("SELECT COUNT(*) AS c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
    if (!$stmt) return false;
    $stmt->bind_param('ss', $table, $column);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return !empty($row['c']);
}

function db_index_exists($connection, $table, $index) {
    $stmt = $connection->prepare("SELECT COUNT(*) AS c FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?");
    if (!$stmt) return false;
    $stmt->bind_param('ss', $table, $index);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return !empty($row['c']);
}

function db_ensure_enquiry_schema($connection, &$status) {
    $columns = [
        ['page_url', "ALTER TABLE enquiries ADD COLUMN page_url VARCHAR(512) DEFAULT NULL AFTER message"],
        ['landing_page', "ALTER TABLE enquiries ADD COLUMN landing_page VARCHAR(512) DEFAULT NULL AFTER page_url"],
        ['referrer', "ALTER TABLE enquiries ADD COLUMN referrer VARCHAR(512) DEFAULT NULL AFTER landing_page"],
        ['visitor_id', "ALTER TABLE enquiries ADD COLUMN visitor_id CHAR(36) DEFAULT NULL AFTER referrer"],
        ['session_id', "ALTER TABLE enquiries ADD COLUMN session_id CHAR(36) DEFAULT NULL AFTER visitor_id"],
        ['utm_source', "ALTER TABLE enquiries ADD COLUMN utm_source VARCHAR(100) DEFAULT NULL AFTER session_id"],
        ['utm_medium', "ALTER TABLE enquiries ADD COLUMN utm_medium VARCHAR(100) DEFAULT NULL AFTER utm_source"],
        ['utm_campaign', "ALTER TABLE enquiries ADD COLUMN utm_campaign VARCHAR(100) DEFAULT NULL AFTER utm_medium"],
        ['utm_content', "ALTER TABLE enquiries ADD COLUMN utm_content VARCHAR(100) DEFAULT NULL AFTER utm_campaign"],
        ['utm_term', "ALTER TABLE enquiries ADD COLUMN utm_term VARCHAR(255) DEFAULT NULL AFTER utm_content"],
        ['status', "ALTER TABLE enquiries ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'new' AFTER utm_term"],
        ['admin_note', "ALTER TABLE enquiries ADD COLUMN admin_note TEXT AFTER status"],
        ['updated_at', "ALTER TABLE enquiries ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at"],
    ];
    foreach ($columns as $migration) {
        [$column, $sql] = $migration;
        if (!db_column_exists($connection, 'enquiries', $column)) {
            if ($connection->query($sql)) $status['migrations'][] = 'enquiries.' . $column;
            else $status['errors'][] = 'enquiries.' . $column . ': ' . $connection->error;
        }
    }
    $indexes = [
        ['idx_enquiries_created', "ALTER TABLE enquiries ADD KEY idx_enquiries_created (created_at)"],
        ['idx_enquiries_status', "ALTER TABLE enquiries ADD KEY idx_enquiries_status (status)"],
        ['idx_enquiries_session', "ALTER TABLE enquiries ADD KEY idx_enquiries_session (session_id)"],
    ];
    foreach ($indexes as $migration) {
        [$index, $sql] = $migration;
        if (!db_index_exists($connection, 'enquiries', $index)) {
            if ($connection->query($sql)) $status['migrations'][] = 'enquiries.' . $index;
            else $status['errors'][] = 'enquiries.' . $index . ': ' . $connection->error;
        }
    }
}

// Keep the public website available if MySQL is temporarily offline.
try {
    // Connect without database first, create if needed, then reconnect.
    $temp = @new mysqli($db_host, $db_user, $db_pass);
    if ($temp && !$temp->connect_error) {
        $temp->query("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $temp->close();
        $conn = @new mysqli($db_host, $db_user, $db_pass, $db_name);
        if ($conn && !$conn->connect_error) {
            $conn->set_charset('utf8mb4');
            $conn->query("SET time_zone = '+05:30'");
            $created = $conn->query("CREATE TABLE IF NOT EXISTS enquiries (
                id INT AUTO_INCREMENT PRIMARY KEY,
                full_name VARCHAR(100) NOT NULL,
                phone VARCHAR(20) NOT NULL,
                email VARCHAR(100) NOT NULL,
                course VARCHAR(200) NOT NULL,
                message TEXT,
                page_url VARCHAR(512) DEFAULT NULL,
                landing_page VARCHAR(512) DEFAULT NULL,
                referrer VARCHAR(512) DEFAULT NULL,
                visitor_id CHAR(36) DEFAULT NULL,
                session_id CHAR(36) DEFAULT NULL,
                utm_source VARCHAR(100) DEFAULT NULL,
                utm_medium VARCHAR(100) DEFAULT NULL,
                utm_campaign VARCHAR(100) DEFAULT NULL,
                utm_content VARCHAR(100) DEFAULT NULL,
                utm_term VARCHAR(255) DEFAULT NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'new',
                admin_note TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                KEY idx_enquiries_created (created_at),
                KEY idx_enquiries_status (status),
                KEY idx_enquiries_session (session_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            if (!$created) $db_status['errors'][] = 'enquiries: ' . $conn->error;
            else db_ensure_enquiry_schema($conn, $db_status);
            $db_status['connected'] = true;
        } else {
            $db_status['errors'][] = 'Unable to connect to database ' . $db_name . '.';
        }
    } else {
        $db_status['errors'][] = 'Unable to connect to MySQL server.';
    }
} catch (Throwable $e) {
    $conn = null;
    $db_status['errors'][] = $e->getMessage();
}

foreach ($db_status['errors'] as $db_error) error_log('Database error: ' . $db_error);
