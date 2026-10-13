<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';

$db = Database::getInstance();

$stmt = $db->query("SELECT id, full_name, email, city FROM customers ORDER BY id DESC LIMIT 5");
$customers = $stmt->fetchAll();

foreach ($customers as $c) {
    echo "ID: {$c['id']}, Name: {$c['full_name']}, City: '" . ($c['city'] ?? 'NULL') . "'\n";
}
