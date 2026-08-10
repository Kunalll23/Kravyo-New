<?php
$db = new PDO('mysql:host=127.0.0.1;dbname=kravyo_db', 'root', '');
$db->exec("UPDATE kitchens SET banner_image = REPLACE(banner_image, 'kitchens/', '') WHERE banner_image LIKE 'kitchens/%'");
echo "DB fixed.\n";
