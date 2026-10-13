<?php
require_once 'config/app.php';
require_once 'core/Database.php';
require_once 'core/Model.php';
require_once 'app/models/Notification.php';

try {
    $notifModel = new Notification();
    $notifModel->createForUser(1, 'chef', 'Test Title', 'Test Message', 'order_update', '/test');
    echo "Success";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
