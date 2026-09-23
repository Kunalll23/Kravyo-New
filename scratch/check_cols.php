<?php
require 'config/app.php';
require 'config/database.php';
require 'core/Database.php';
$db = Database::getInstance();
$cols = $db->query('SHOW COLUMNS FROM menu_items')->fetchAll(PDO::FETCH_ASSOC);
foreach($cols as $c) { echo $c['Field'] . "\n"; }
