<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';

$db = Database::getInstance();

$tables = ['customers', 'chefs', 'delivery_users', 'pending_registrations'];
foreach ($tables as $table) {
    try {
        $db->exec("ALTER TABLE $table ADD COLUMN city VARCHAR(100) NULL AFTER phone");
        echo "Added city to $table\n";
    } catch (Exception $e) {
        echo "Error on $table (maybe already exists?): " . $e->getMessage() . "\n";
    }
}
