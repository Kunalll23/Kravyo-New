<?php
require_once __DIR__ . '/../config/app.php';
$dbConfig = require __DIR__ . '/../config/database.php';
$pdo = new PDO("mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']}", $dbConfig['username'], $dbConfig['password']);
$stmt = $pdo->query('SELECT * FROM categories');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
