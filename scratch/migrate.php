<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../core/Database.php';
$db = Database::getInstance();
$sql = file_get_contents(__DIR__ . '/../database/phase11_logistics.sql');
$db->exec($sql);
echo "Migration executed!";
