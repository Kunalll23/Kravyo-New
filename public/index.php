<?php
/**
 * Kravyo - Front Controller Entry Point
 */

// Load Configurations
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/constants.php';

if (file_exists(__DIR__ . '/../config/smtp.php')) {
    require_once __DIR__ . '/../config/smtp.php';
} elseif (file_exists(__DIR__ . '/../config/smtp.example.php')) {
    require_once __DIR__ . '/../config/smtp.example.php';
}

if (file_exists(__DIR__ . '/../config/google_maps.php')) {
    require_once __DIR__ . '/../config/google_maps.php';
} elseif (file_exists(__DIR__ . '/../config/google_maps.example.php')) {
    require_once __DIR__ . '/../config/google_maps.example.php';
}

// Autoload Core Framework Classes
require_once CORE_PATH . '/Database.php';
require_once CORE_PATH . '/Session.php';
require_once CORE_PATH . '/Helpers.php';
require_once CORE_PATH . '/View.php';
require_once CORE_PATH . '/Controller.php';
require_once CORE_PATH . '/Model.php';
require_once CORE_PATH . '/Middleware.php';
require_once CORE_PATH . '/Router.php';
require_once CORE_PATH . '/GoogleMaps.php';

// Initialize Security Session
Session::init();

// Dispatch Request through Router
$router = new Router();
$router->dispatch();
