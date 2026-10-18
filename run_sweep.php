<?php
/**
 * Kravyo - Dispatch Sweep Runner
 * Called by cron or manually to sweep expired delivery offers.
 * Protected by a MySQL advisory lock so overlapping executions are skipped safely.
 */

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

// Advisory lock prevents two overlapping sweep executions from processing the
// same expired orders and double-cascading offers to the same driver.
// (sweepExpired() also acquires the same lock internally — this outer check
// provides an early-exit for cron overlap before any model is instantiated.)
if (!Database::acquireLock('kravyo_file_sweep', 0)) {
    echo "Sweep skipped — already running.\n";
    exit(0);
}

try {
    $svc = new DispatchService();
    $svc->sweepExpired();
    echo "Swept\n";
} finally {
    Database::releaseLock('kravyo_file_sweep');
}
