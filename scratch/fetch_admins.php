<?php
require_once 'C:\xampp\htdocs\Kravyo\config\app.php';
require_once 'C:\xampp\htdocs\Kravyo\config\constants.php';
require_once 'C:\xampp\htdocs\Kravyo\core\Database.php';

$db = Database::getInstance();
$stmt = $db->query('SELECT full_name, email FROM admins');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
