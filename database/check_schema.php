<?php
require 'c:/xampp/htdocs/Kravyo/bootstrap.php';
$db = Database::getInstance();
$stmt = $db->query('DESCRIBE kitchens');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
