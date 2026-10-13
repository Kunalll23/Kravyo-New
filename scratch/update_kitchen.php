<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../core/Database.php';

$db = Database::getInstance();
$sql = "UPDATE kitchens SET city = 'Surat', latitude = 21.1702, longitude = 72.8311 WHERE id = 43";
$db->query($sql);

echo "Updated Mehul's Kitchen to Surat!";
