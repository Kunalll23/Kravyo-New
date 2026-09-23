<?php
require_once 'C:\xampp\htdocs\Kravyo\config\app.php';
require_once 'C:\xampp\htdocs\Kravyo\config\constants.php';
require_once 'C:\xampp\htdocs\Kravyo\core\Database.php';

$db = Database::getInstance();
$hash = password_hash('Admin@123', PASSWORD_DEFAULT);
$stmt = $db->prepare('UPDATE admins SET password_hash = :hash WHERE email="admin@kravyo.com"');
$stmt->execute(['hash' => $hash]);
echo "Password reset to Admin@123 for admin@kravyo.com\n";
