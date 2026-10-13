<?php
/**
 * Kravyo - Strict Role Context & 4-Way Tab Isolation Verification Suite
 */

ob_start();
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('session.use_cookies', '0');

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('CORE_PATH', ROOT_PATH . '/core');
define('APP_URL', 'http://localhost/Kravyo');

require_once ROOT_PATH . '/config/constants.php';
require_once ROOT_PATH . '/core/Session.php';
require_once ROOT_PATH . '/core/Helpers.php';
require_once ROOT_PATH . '/core/Middleware.php';
require_once ROOT_PATH . '/core/Router.php';

$totalTests = 0;
$passedTests = 0;
$failedTests = 0;

function assertTest(bool $condition, string $description, string $details = ''): void {
    global $totalTests, $passedTests, $failedTests;
    $totalTests++;
    if ($condition) {
        $passedTests++;
        echo "  [PASS] {$description}\n";
    } else {
        $failedTests++;
        echo "  [FAIL] {$description}\n";
        if ($details) {
            echo "         Details: {$details}\n";
        }
    }
}

echo "======================================================\n";
echo "  KRAVYO STRICT ROLE CONTEXT RULE TEST SUITE\n";
echo "======================================================\n\n";

// --- 1. TEST ROUTE MAP COVERAGE ---
echo "1. Checking Route Map Registrations...\n";
$routes = require CONFIG_PATH . '/routes.php';

assertTest(isset($routes['GET /chef/login']), "GET /chef/login route is registered");
assertTest(($routes['GET /chef/login'] ?? '') === 'AuthController@showChefLoginForm', "GET /chef/login maps to AuthController@showChefLoginForm");

assertTest(isset($routes['POST /chef/login']), "POST /chef/login route is registered");
assertTest(($routes['POST /chef/login'] ?? '') === 'AuthController@chefLogin', "POST /chef/login maps to AuthController@chefLogin");

assertTest(isset($routes['POST /chef/logout']), "POST /chef/logout route is registered");
assertTest(isset($routes['GET /chef']), "GET /chef root alias is registered");
assertTest(isset($routes['GET /admin']), "GET /admin root alias is registered");
assertTest(isset($routes['GET /delivery']), "GET /delivery root alias is registered");

assertTest(isset($routes['GET /login']) && isset($routes['POST /login']) && isset($routes['POST /logout']), "Customer auth routes registered");
assertTest(isset($routes['GET /admin/login']) && isset($routes['POST /admin/login']) && isset($routes['POST /admin/logout']), "Admin auth routes registered");
assertTest(isset($routes['GET /delivery/login']) && isset($routes['POST /delivery/login']) && isset($routes['POST /delivery/logout']), "Delivery auth routes registered");

// --- 2. TEST detectContext() ACCURACY ---
echo "\n2. Testing detectContext() Under All Role Paths...\n";

function checkContext(string $uri, string $expected): bool {
    $_SERVER['REQUEST_URI'] = $uri;
    $_SERVER['SCRIPT_NAME'] = '/Kravyo/public/index.php';
    return Session::detectContext() === $expected;
}

assertTest(checkContext('/chef/orders', Session::CHEF_SESSION), "/chef/orders -> KRAVYO_CHEF");
assertTest(checkContext('/chef/dashboard', Session::CHEF_SESSION), "/chef/dashboard -> KRAVYO_CHEF");
assertTest(checkContext('/chef/login', Session::CHEF_SESSION), "/chef/login -> KRAVYO_CHEF");
assertTest(checkContext('/chef/menu-browse', Session::CHEF_SESSION), "/chef/menu-browse -> KRAVYO_CHEF");

assertTest(checkContext('/delivery/dashboard', Session::DELIVERY_SESSION), "/delivery/dashboard -> KRAVYO_DELIVERY");
assertTest(checkContext('/delivery/login', Session::DELIVERY_SESSION), "/delivery/login -> KRAVYO_DELIVERY");
assertTest(checkContext('/delivery/history', Session::DELIVERY_SESSION), "/delivery/history -> KRAVYO_DELIVERY");

assertTest(checkContext('/admin/dashboard', Session::ADMIN_SESSION), "/admin/dashboard -> KRAVYO_ADMIN");
assertTest(checkContext('/admin/login', Session::ADMIN_SESSION), "/admin/login -> KRAVYO_ADMIN");
assertTest(checkContext('/admin/chefs', Session::ADMIN_SESSION), "/admin/chefs -> KRAVYO_ADMIN");

assertTest(checkContext('/', Session::CUSTOMER_SESSION), "/ -> KRAVYO_CUSTOMER");
assertTest(checkContext('/menu', Session::CUSTOMER_SESSION), "/menu -> KRAVYO_CUSTOMER");
assertTest(checkContext('/login', Session::CUSTOMER_SESSION), "/login -> KRAVYO_CUSTOMER");
assertTest(checkContext('/kitchens', Session::CUSTOMER_SESSION), "/kitchens -> KRAVYO_CUSTOMER");

// --- 3. TEST ctx_url() CONTEXT AWARENESS ---
echo "\n3. Testing ctx_url() Role-Preservation...\n";

// Helper to simulate session_name() without starting session
function simulateSessionContext(string $ctxName): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
    session_name($ctxName);
}

// Chef Context URL Rewriting
simulateSessionContext(Session::CHEF_SESSION);
assertTest(ctx_url('/') === APP_URL . '/chef/dashboard', "Chef: ctx_url('/') stays in Chef (-> /chef/dashboard)");
assertTest(ctx_url('/login') === APP_URL . '/chef/login', "Chef: ctx_url('/login') stays in Chef (-> /chef/login)");
assertTest(ctx_url('/menu') === APP_URL . '/chef/menu-browse', "Chef: ctx_url('/menu') -> /chef/menu-browse");
assertTest(ctx_url('/kitchens') === APP_URL . '/chef/kitchens', "Chef: ctx_url('/kitchens') -> /chef/kitchens");
assertTest(ctx_url('/dish/42') === APP_URL . '/chef/dish/42', "Chef: ctx_url('/dish/42') -> /chef/dish/42");
assertTest(ctx_url('/zero-waste') === APP_URL . '/chef/zero-waste-deals', "Chef: ctx_url('/zero-waste') -> /chef/zero-waste-deals");

// Delivery Context URL Rewriting
simulateSessionContext(Session::DELIVERY_SESSION);
assertTest(ctx_url('/') === APP_URL . '/delivery/dashboard', "Delivery: ctx_url('/') stays in Delivery (-> /delivery/dashboard)");
assertTest(ctx_url('/login') === APP_URL . '/delivery/login', "Delivery: ctx_url('/login') stays in Delivery (-> /delivery/login)");
assertTest(ctx_url('/menu') === APP_URL . '/delivery/menu-browse', "Delivery: ctx_url('/menu') -> /delivery/menu-browse");
assertTest(ctx_url('/kitchens') === APP_URL . '/delivery/kitchens', "Delivery: ctx_url('/kitchens') -> /delivery/kitchens");

// Admin Context URL Rewriting
simulateSessionContext(Session::ADMIN_SESSION);
assertTest(ctx_url('/') === APP_URL . '/admin/dashboard', "Admin: ctx_url('/') stays in Admin (-> /admin/dashboard)");
assertTest(ctx_url('/login') === APP_URL . '/admin/login', "Admin: ctx_url('/login') stays in Admin (-> /admin/login)");
assertTest(ctx_url('/menu') === APP_URL . '/admin/menu-browse', "Admin: ctx_url('/menu') -> /admin/menu-browse");

// Customer Context (No Rewriting)
simulateSessionContext(Session::CUSTOMER_SESSION);
assertTest(ctx_url('/') === APP_URL . '/', "Customer: ctx_url('/') -> /");
assertTest(ctx_url('/login') === APP_URL . '/login', "Customer: ctx_url('/login') -> /login");
assertTest(ctx_url('/menu') === APP_URL . '/menu', "Customer: ctx_url('/menu') -> /menu");
assertTest(ctx_url('/dish/42') === APP_URL . '/dish/42', "Customer: ctx_url('/dish/42') -> /dish/42");

// --- 4. TEST Middleware Context Login & Home URL Guards ---
echo "\n4. Testing Middleware Role-Isolation Guards...\n";

simulateSessionContext(Session::CHEF_SESSION);
assertTest(Middleware::getContextLoginUrl() === '/chef/login', "Chef Middleware Login URL is /chef/login (NEVER /login)");
assertTest(Middleware::getContextHomeUrl() === '/chef/dashboard', "Chef Middleware Home URL is /chef/dashboard (NEVER /)");

simulateSessionContext(Session::DELIVERY_SESSION);
assertTest(Middleware::getContextLoginUrl() === '/delivery/login', "Delivery Middleware Login URL is /delivery/login (NEVER /login)");
assertTest(Middleware::getContextHomeUrl() === '/delivery/dashboard', "Delivery Middleware Home URL is /delivery/dashboard (NEVER /)");

simulateSessionContext(Session::ADMIN_SESSION);
assertTest(Middleware::getContextLoginUrl() === '/admin/login', "Admin Middleware Login URL is /admin/login (NEVER /login)");
assertTest(Middleware::getContextHomeUrl() === '/admin/dashboard', "Admin Middleware Home URL is /admin/dashboard (NEVER /)");

simulateSessionContext(Session::CUSTOMER_SESSION);
assertTest(Middleware::getContextLoginUrl() === '/login', "Customer Middleware Login URL is /login");
assertTest(Middleware::getContextHomeUrl() === '/', "Customer Middleware Home URL is /");

// --- 5. TEST 4-WAY CONCURRENT LOGIN INDEPENDENCE ---
echo "\n5. Testing 4-Way Tab Session Isolation & Data Integrity...\n";

session_cache_limiter('');

$tmpDir = sys_get_temp_dir() . '/kravyo_test_sessions_' . time();
mkdir($tmpDir);
session_save_path($tmpDir);

$sessIds = [
    Session::CUSTOMER_SESSION => bin2hex(random_bytes(16)),
    Session::CHEF_SESSION     => bin2hex(random_bytes(16)),
    Session::DELIVERY_SESSION => bin2hex(random_bytes(16)),
    Session::ADMIN_SESSION    => bin2hex(random_bytes(16)),
];

// Tab 1: Customer Kunal logs in
session_name(Session::CUSTOMER_SESSION);
session_id($sessIds[Session::CUSTOMER_SESSION]);
session_start();
$_SESSION['user_id'] = 101;
$_SESSION['user_name'] = 'Customer Kunal';
$_SESSION['user_role'] = ROLE_CUSTOMER;
session_write_close();

// Tab 2: Chef Gordon logs in
session_name(Session::CHEF_SESSION);
session_id($sessIds[Session::CHEF_SESSION]);
session_start();
$_SESSION['user_id'] = 202;
$_SESSION['user_name'] = 'Chef Gordon';
$_SESSION['user_role'] = ROLE_CHEF;
session_write_close();

// Tab 3: Admin Root logs in
session_name(Session::ADMIN_SESSION);
session_id($sessIds[Session::ADMIN_SESSION]);
session_start();
$_SESSION['admin_id'] = 1;
$_SESSION['admin_name'] = 'Admin Root';
session_write_close();

// Tab 4: Delivery Partner Swift logs in
session_name(Session::DELIVERY_SESSION);
session_id($sessIds[Session::DELIVERY_SESSION]);
session_start();
$_SESSION['user_id'] = 303;
$_SESSION['user_name'] = 'Rider Swift';
$_SESSION['user_role'] = ROLE_DELIVERY;
session_write_close();

// Now simulate Tab 1 accessing Customer page (verify Kunal)
session_name(Session::CUSTOMER_SESSION);
session_id($sessIds[Session::CUSTOMER_SESSION]);
session_start();
assertTest($_SESSION['user_id'] === 101 && $_SESSION['user_name'] === 'Customer Kunal', "Tab 1 retains Customer Kunal");
session_write_close();

// Simulate Tab 2 accessing Chef orders (verify Chef Gordon)
session_name(Session::CHEF_SESSION);
session_id($sessIds[Session::CHEF_SESSION]);
session_start();
assertTest($_SESSION['user_id'] === 202 && $_SESSION['user_name'] === 'Chef Gordon', "Tab 2 retains Chef Gordon");
session_write_close();

// Simulate Tab 3 accessing Admin panel (verify Admin Root)
session_name(Session::ADMIN_SESSION);
session_id($sessIds[Session::ADMIN_SESSION]);
session_start();
assertTest($_SESSION['admin_id'] === 1 && $_SESSION['admin_name'] === 'Admin Root', "Tab 3 retains Admin Root");
session_write_close();

// Simulate Tab 4 accessing Delivery dashboard (verify Rider Swift)
session_name(Session::DELIVERY_SESSION);
session_id($sessIds[Session::DELIVERY_SESSION]);
session_start();
assertTest($_SESSION['user_id'] === 303 && $_SESSION['user_name'] === 'Rider Swift', "Tab 4 retains Rider Swift");
session_write_close();

// Simulate Chef logout: destroy Chef role only
$_COOKIE[Session::CHEF_SESSION] = $sessIds[Session::CHEF_SESSION];
Session::destroyRole('chef');

// Verify Tab 2 is logged out
session_name(Session::CHEF_SESSION);
session_id($sessIds[Session::CHEF_SESSION]);
session_start();
assertTest(empty($_SESSION['user_id']), "Chef session is destroyed after chef logout");
session_write_close();

// Verify Tab 1 (Customer) is STILL logged in
session_name(Session::CUSTOMER_SESSION);
session_id($sessIds[Session::CUSTOMER_SESSION]);
session_start();
assertTest(!empty($_SESSION['user_id']) && $_SESSION['user_name'] === 'Customer Kunal', "Customer Kunal remains logged in after Chef logout");
session_write_close();

// Verify Tab 3 (Admin) is STILL logged in
session_name(Session::ADMIN_SESSION);
session_id($sessIds[Session::ADMIN_SESSION]);
session_start();
assertTest(!empty($_SESSION['admin_id']) && $_SESSION['admin_name'] === 'Admin Root', "Admin Root remains logged in after Chef logout");
session_write_close();

// Verify Tab 4 (Delivery) is STILL logged in
session_name(Session::DELIVERY_SESSION);
session_id($sessIds[Session::DELIVERY_SESSION]);
session_start();
assertTest(!empty($_SESSION['user_id']) && $_SESSION['user_name'] === 'Rider Swift', "Rider Swift remains logged in after Chef logout");
session_write_close();

// Cleanup temp test session files
array_map('unlink', glob("$tmpDir/*"));
rmdir($tmpDir);

echo "\n======================================================\n";
echo "  TEST SUMMARY: {$passedTests} passed, {$failedTests} failed (Total: {$totalTests})\n";
echo "======================================================\n";

ob_end_flush();

if ($failedTests === 0) {
    echo ">>> ALL STRICT ROLE CONTEXT & 4-TAB TESTS PASSED SUCCESSFULLY! <<<\n";
    exit(0);
} else {
    echo ">>> SOME TESTS FAILED. PLEASE REVIEW DETAILS ABOVE. <<<\n";
    exit(1);
}
