

<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../app/models/DeliveryRun.php';

$runModel = new DeliveryRun();
$run = $runModel->findById(6);
print_r($run);
