<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../core/Database.php';

try {
    $db = Database::getInstance();
    $sql = "ALTER TABLE admins ADD COLUMN last_login_at TIMESTAMP NULL DEFAULT NULL";
    $db->exec($sql);
    echo "Column last_login_at added to admins table successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
