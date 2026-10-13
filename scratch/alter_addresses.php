<?php
$config = require __DIR__ . '/../config/database.php';
$dsn = "{$config['driver']}:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset={$config['charset']}";
$db = new PDO($dsn, $config['username'], $config['password'], $config['options']);

try {
    $db->exec("ALTER TABLE addresses ADD COLUMN latitude DECIMAL(10, 8) NULL;");
    echo "Added latitude\n";
} catch (Exception $e) { echo "Lat error: " . $e->getMessage() . "\n"; }

try {
    $db->exec("ALTER TABLE addresses ADD COLUMN longitude DECIMAL(11, 8) NULL;");
    echo "Added longitude\n";
} catch (Exception $e) { echo "Lng error: " . $e->getMessage() . "\n"; }
echo "Done.";
