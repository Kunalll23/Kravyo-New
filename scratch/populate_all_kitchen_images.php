<?php
$db = new PDO('mysql:host=127.0.0.1;dbname=kravyo_db', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $db->query("SELECT id FROM kitchens");
$kitchens = $stmt->fetchAll(PDO::FETCH_ASSOC);

$images = ['k1.png', 'k2.png', 'k3.png'];
$count = 0;

foreach ($kitchens as $index => $k) {
    $img = $images[$index % count($images)];
    $update = $db->prepare("UPDATE kitchens SET banner_image = :img WHERE id = :id");
    $update->execute(['img' => $img, 'id' => $k['id']]);
    $count++;
}

echo "Successfully populated images for all $count kitchens in less than 1 second!\n";
