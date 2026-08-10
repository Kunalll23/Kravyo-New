<?php
$db = new PDO('mysql:host=127.0.0.1;dbname=kravyo_db', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $db->query("SELECT id, kitchen_name FROM kitchens ORDER BY id ASC");
$kitchens = $stmt->fetchAll(PDO::FETCH_ASSOC);

$images = [
    'img_1.png',  // Gujarati Thali
    'img_2.png',  // Dosa / Idli
    'img_3.png',  // North Indian Thali
    'img_4.png',  // Biryani
    'img_5.png',  // Chole Bhature
    'img_6.png',  // Aloo Paratha
    'img_7.png',  // Dal Baati
    'img_8.png',  // Misal Pav
    'img_9.png',  // Dhokla Fafda
    'img_10.png', // Hakka Noodles
    'img_11.png', // Paneer Butter Masala
    'img_12.png'  // Indian Sweets
];

$count = 0;
foreach ($kitchens as $index => $k) {
    // Pick an image based on index to ensure variety
    $img = $images[$index % count($images)];
    $update = $db->prepare("UPDATE kitchens SET banner_image = :img WHERE id = :id");
    $update->execute(['img' => $img, 'id' => $k['id']]);
    $count++;
}

echo "Successfully updated $count kitchens with 12 unique Indian cuisine cover photos!\n";
