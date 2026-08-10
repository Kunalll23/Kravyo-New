<?php
/**
 * Kravyo - Front Controller Entry Point
 */

// Load Configurations
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/constants.php';

// Autoload Core Framework Classes
require_once CORE_PATH . '/Database.php';
require_once CORE_PATH . '/Session.php';
require_once CORE_PATH . '/Helpers.php';
require_once CORE_PATH . '/View.php';
require_once CORE_PATH . '/Controller.php';
require_once CORE_PATH . '/Model.php';
require_once CORE_PATH . '/Middleware.php';
require_once CORE_PATH . '/Router.php';

// Initialize Security Session
Session::init();

// Dispatch Request through Router
$router = new Router();
$router->dispatch();
