<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Database.php';

$db = Database::getInstance();
try {
    $db->exec("ALTER TABLE delivery_partners ADD COLUMN location_updated_at TIMESTAMP NULL DEFAULT NULL AFTER current_longitude;");
    echo "Added location_updated_at to delivery_partners.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
