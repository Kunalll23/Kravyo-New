<?php
require_once 'C:\xampp\htdocs\Kravyo\config\app.php';
require_once 'C:\xampp\htdocs\Kravyo\config\constants.php';
require_once 'C:\xampp\htdocs\Kravyo\core\Database.php';

$db = Database::getInstance();
$stmt = $db->query('SELECT password_hash FROM admins WHERE email="admin@kravyo.com"');
$hash = $stmt->fetchColumn();

$passwords = ['Admin@123', 'admin123', 'password', 'kravyo123', '123456', 'admin'];

foreach ($passwords as $p) {
    if (password_verify($p, $hash)) {
        echo "MATCH FOUND: $p\n";
        exit;
    }
}
echo "No match found.\n";
