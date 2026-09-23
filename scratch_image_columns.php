<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=kravyo_db', 'root', '');
$stmt = $pdo->query("SELECT TABLE_NAME, COLUMN_NAME FROM information_schema.columns WHERE table_schema='kravyo_db' AND (COLUMN_NAME LIKE '%image%' OR COLUMN_NAME LIKE '%photo%' OR COLUMN_NAME LIKE '%avatar%' OR COLUMN_NAME LIKE '%banner%')");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
