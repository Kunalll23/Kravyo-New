<?php
$db = new PDO('mysql:host=127.0.0.1;dbname=kravyo_db', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$stmt = $db->query("SELECT id, kitchen_name, banner_image FROM kitchens WHERE banner_image IS NOT NULL AND banner_image != ''");
$kitchens = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($kitchens as $k) {
    echo "ID: {$k['id']}, Name: {$k['kitchen_name']}, Banner: {$k['banner_image']}\n";
}
