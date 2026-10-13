<?php
require_once __DIR__ . '/../config/app.php';
$dbConfig = require __DIR__ . '/../config/database.php';
$pdo = new PDO("mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']}", $dbConfig['username'], $dbConfig['password']);
$stmt = $pdo->query('SELECT id, item_name, is_veg, image FROM menu_items');
$dishes = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($dishes, JSON_PRETTY_PRINT);
