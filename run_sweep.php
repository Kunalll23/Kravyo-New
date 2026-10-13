<?php
define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
require 'config/database.php';
require 'core/Database.php';
require 'core/Model.php';
require 'app/models/Order.php';
require 'app/models/DeliveryOffer.php';
require 'app/models/DeliveryPartner.php';
require 'app/services/DispatchService.php';
$svc = new DispatchService();
$svc->sweepExpired();
echo "Swept\n";
