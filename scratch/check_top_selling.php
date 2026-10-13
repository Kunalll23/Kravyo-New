<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../app/models/MenuItem.php';

$menuItemModel = new MenuItem();
$topSelling = $menuItemModel->getTopSellingItems(4);
echo json_encode($topSelling, JSON_PRETTY_PRINT);
