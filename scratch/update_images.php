<?php
$db = new PDO('mysql:host=127.0.0.1;dbname=kravyo_db', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $db->query("SELECT id FROM kitchens LIMIT 3");
$kitchens = $stmt->fetchAll(PDO::FETCH_ASSOC);

$images = ['kitchens/k1.png', 'kitchens/k2.png', 'kitchens/k3.png'];

foreach ($kitchens as $index => $k) {
    if (isset($images[$index])) {
        $updateStmt = $db->prepare("UPDATE kitchens SET banner_image = :img WHERE id = :id");
        $updateStmt->execute(['img' => $images[$index], 'id' => $k['id']]);
        echo "Updated kitchen {$k['id']} with {$images[$index]}\n";
    }
}
echo "Done.\n";
