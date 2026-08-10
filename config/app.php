<?php
/**
 * Kravyo - Application Configuration
 */

// Application Info
define('APP_NAME', 'Kravyo');
define('APP_TAGLINE', 'Cloud Kitchen Platform for Homemakers & Small Food Businesses');
define('APP_VERSION', '1.0.0-dev');
define('APP_ENV', 'development'); // 'development' or 'production'

// Base URL configuration (automatic detection for XAMPP / custom domains / PHP dev server)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));

if ($scriptDir === '/' || $scriptDir === '\\') {
    // When served directly with public folder as document root (e.g. php -S localhost:8000 -t public)
    $baseUrl = $protocol . '://' . $host;
    $assetUrl = $baseUrl . '/assets';
    $uploadUrl = $baseUrl . '/uploads';
} else {
    // When served from root/subfolder directory (e.g. XAMPP http://localhost/Kravyo/)
    $baseUrl = rtrim($protocol . '://' . $host . $scriptDir, '/public');
    $assetUrl = $baseUrl . '/public/assets';
    $uploadUrl = $baseUrl . '/public/uploads';
}

define('APP_URL', $baseUrl);
define('ASSET_URL', $assetUrl);
define('UPLOAD_URL', $uploadUrl);

// Path Constants
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('CORE_PATH', ROOT_PATH . '/core');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('VIEWS_PATH', ROOT_PATH . '/views');
define('STORAGE_PATH', ROOT_PATH . '/storage');

// Error Reporting Config based on Environment
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// Timezone
date_default_timezone_set('Asia/Kolkata');
