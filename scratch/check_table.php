<?php
require 'c:/xampp/htdocs/Kravyo/config/app.php';
require 'c:/xampp/htdocs/Kravyo/core/Database.php';

$pdo = Database::getInstance();
$plans = $pdo->query("SELECT id, plan_name, meals_per_day, plan_type, price FROM tiffin_subscriptions")->fetchAll(PDO::FETCH_ASSOC);
print_r($plans);
