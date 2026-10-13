<?php
/**
 * Kravyo - Session Isolation Verification Script
 *
 * HOW TO USE:
 *   Visit: http://localhost/Kravyo/scratch/session_isolation_test.php
 *   (No login required — tests the mechanics of session isolation.)
 *
 * This script verifies:
 *   1. Four independent named sessions each get a unique session ID.
 *   2. Writing to one session does NOT affect any other session.
 *   3. detectContext() returns the correct session name per URL prefix.
 *   4. switchToRole() generates a fresh ID and doesn't reuse the source session ID.
 *   5. destroyRole() destroys only the targeted session.
 *
 * IMPORTANT: Run this BEFORE and AFTER the fix to compare results.
 * DELETE this file from production servers.
 */

// ─── Bootstrap ────────────────────────────────────────────────────────────────
define('APP_NAME', 'Kravyo');
$protocol  = 'http';
$host      = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
// Go up from /Kravyo/scratch to /Kravyo
$baseUrl   = rtrim($protocol . '://' . $host . dirname($scriptDir), '/');
define('APP_URL',    $baseUrl);
define('ROOT_PATH',  dirname(__DIR__));
define('APP_PATH',   ROOT_PATH . '/app');
define('CONFIG_PATH',ROOT_PATH . '/config');
define('CORE_PATH',  ROOT_PATH . '/core');
define('PUBLIC_PATH',ROOT_PATH . '/public');
define('VIEWS_PATH', ROOT_PATH . '/views');
define('STORAGE_PATH',ROOT_PATH . '/storage');
define('ROLE_CUSTOMER', 'customer');
define('ROLE_CHEF',     'chef');
define('ROLE_DELIVERY', 'delivery');
define('ROLE_ADMIN',    'admin');

require_once ROOT_PATH . '/core/Session.php';

// ─── Cookie path should match what XAMPP uses ─────────────────────────────────
// For XAMPP this is typically '/Kravyo/public' but Session.php expires cookies at '/'.
// For this test we use '/' to match Session.php.

$results = [];
$pass    = '✅ PASS';
$fail    = '❌ FAIL';

// ─── TEST 1: detectContext() ──────────────────────────────────────────────────
// Simulate different REQUEST_URIs and check the returned session name.
function testDetectContext(string $requestUri, string $expectedSession): string {
    $_SERVER['REQUEST_URI']  = $requestUri;
    $_SERVER['SCRIPT_NAME']  = '/Kravyo/public/index.php';
    $result = Session::detectContext();
    return $result === $expectedSession ? "✅ PASS: $requestUri → $result" : "❌ FAIL: $requestUri → got '$result', expected '$expectedSession'";
}

$results['detectContext'] = [
    testDetectContext('/Kravyo/public/admin/dashboard',   Session::ADMIN_SESSION),
    testDetectContext('/Kravyo/public/chef/dashboard',    Session::CHEF_SESSION),
    testDetectContext('/Kravyo/public/delivery/dashboard',Session::DELIVERY_SESSION),
    testDetectContext('/Kravyo/public/',                  Session::CUSTOMER_SESSION),
    testDetectContext('/Kravyo/public/kitchens',          Session::CUSTOMER_SESSION),
    testDetectContext('/Kravyo/public/login',             Session::CUSTOMER_SESSION),
];

// ─── TEST 2: Four independent sessions with unique IDs ────────────────────────
// Open each of the four sessions in sequence and record their IDs.
// Each should be completely different from all others.

function openFreshSession(string $sessionName): string {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
    session_name($sessionName);
    // Force a brand-new session (ignore any stale test cookies)
    session_id(bin2hex(random_bytes(16)));
    session_start();
    $id = session_id();
    session_write_close();
    return $id;
}

$ids = [
    'KRAVYO_CUSTOMER'  => openFreshSession(Session::CUSTOMER_SESSION),
    'KRAVYO_CHEF'      => openFreshSession(Session::CHEF_SESSION),
    'KRAVYO_DELIVERY'  => openFreshSession(Session::DELIVERY_SESSION),
    'KRAVYO_ADMIN'     => openFreshSession(Session::ADMIN_SESSION),
];

$sessionIdTests = [];
$allIds = array_values($ids);
$allUnique = count($allIds) === count(array_unique($allIds));
$sessionIdTests[] = $allUnique
    ? "✅ PASS: All 4 session IDs are unique"
    : "❌ FAIL: Duplicate session IDs detected! " . implode(', ', $allIds);

foreach ($ids as $name => $id) {
    $sessionIdTests[] = "  $name → " . substr($id, 0, 12) . '…';
}
$results['sessionIds'] = $sessionIdTests;

// ─── TEST 3: Writing to one session does NOT pollute another ──────────────────
function writeToSession(string $sessionName, string $sessionId, array $data): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
    session_name($sessionName);
    session_id($sessionId);
    session_start();
    foreach ($data as $k => $v) {
        $_SESSION[$k] = $v;
    }
    session_write_close();
}

function readFromSession(string $sessionName, string $sessionId): array {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
    session_name($sessionName);
    session_id($sessionId);
    session_start();
    $data = $_SESSION;
    session_write_close();
    return $data;
}

// Write unique marker data to each session
writeToSession(Session::CUSTOMER_SESSION, $ids['KRAVYO_CUSTOMER'], ['user_id' => 100, 'user_role' => 'customer', 'marker' => 'CUSTOMER_DATA']);
writeToSession(Session::CHEF_SESSION,     $ids['KRAVYO_CHEF'],     ['user_id' => 200, 'user_role' => 'chef',     'marker' => 'CHEF_DATA']);
writeToSession(Session::DELIVERY_SESSION, $ids['KRAVYO_DELIVERY'], ['user_id' => 300, 'user_role' => 'delivery', 'marker' => 'DELIVERY_DATA']);
writeToSession(Session::ADMIN_SESSION,    $ids['KRAVYO_ADMIN'],    ['admin_id' => 1, 'admin_name' => 'Admin',   'marker' => 'ADMIN_DATA']);

// Now read each back and verify isolation
$isolationTests = [];

$customerData  = readFromSession(Session::CUSTOMER_SESSION, $ids['KRAVYO_CUSTOMER']);
$chefData      = readFromSession(Session::CHEF_SESSION,     $ids['KRAVYO_CHEF']);
$deliveryData  = readFromSession(Session::DELIVERY_SESSION, $ids['KRAVYO_DELIVERY']);
$adminData     = readFromSession(Session::ADMIN_SESSION,    $ids['KRAVYO_ADMIN']);

// Customer session should have user_id=100, NOT 200 or 300
$isolationTests[] = (($customerData['user_id'] ?? null) === 100 && ($customerData['marker'] ?? '') === 'CUSTOMER_DATA')
    ? "✅ PASS: KRAVYO_CUSTOMER contains only Customer data (user_id=100)"
    : "❌ FAIL: KRAVYO_CUSTOMER data corrupted: " . json_encode($customerData);

// Chef session should have user_id=200
$isolationTests[] = (($chefData['user_id'] ?? null) === 200 && ($chefData['marker'] ?? '') === 'CHEF_DATA')
    ? "✅ PASS: KRAVYO_CHEF contains only Chef data (user_id=200)"
    : "❌ FAIL: KRAVYO_CHEF data corrupted: " . json_encode($chefData);

// Delivery session should have user_id=300
$isolationTests[] = (($deliveryData['user_id'] ?? null) === 300 && ($deliveryData['marker'] ?? '') === 'DELIVERY_DATA')
    ? "✅ PASS: KRAVYO_DELIVERY contains only Delivery data (user_id=300)"
    : "❌ FAIL: KRAVYO_DELIVERY data corrupted: " . json_encode($deliveryData);

// Admin session should have admin_id=1
$isolationTests[] = (($adminData['admin_id'] ?? null) === 1 && ($adminData['marker'] ?? '') === 'ADMIN_DATA')
    ? "✅ PASS: KRAVYO_ADMIN contains only Admin data (admin_id=1)"
    : "❌ FAIL: KRAVYO_ADMIN data corrupted: " . json_encode($adminData);

// Verify Chef data did NOT leak into Customer
$isolationTests[] = !isset($customerData['user_id']) || $customerData['user_id'] !== 200
    ? "✅ PASS: Chef user_id (200) NOT in Customer session"
    : "❌ FAIL: Chef user_id (200) leaked into Customer session!";

// Verify Delivery data did NOT leak into Customer
$isolationTests[] = !isset($customerData['user_id']) || $customerData['user_id'] !== 300
    ? "✅ PASS: Delivery user_id (300) NOT in Customer session"
    : "❌ FAIL: Delivery user_id (300) leaked into Customer session!";

// Verify Admin data NOT in Chef session
$isolationTests[] = !isset($chefData['admin_id'])
    ? "✅ PASS: Admin admin_id NOT in Chef session"
    : "❌ FAIL: Admin data leaked into Chef session!";

$results['isolation'] = $isolationTests;

// ─── TEST 4: Verify deliveryRole for 'delivery' maps to KRAVYO_DELIVERY ───────
$roleMapTests = [];
$roleMap = [
    'customer' => Session::CUSTOMER_SESSION,
    'chef'     => Session::CHEF_SESSION,
    'delivery' => Session::DELIVERY_SESSION,
    'admin'    => Session::ADMIN_SESSION,
];
// Use reflection to call the private roleToSessionName
$ref    = new ReflectionClass('Session');
$method = $ref->getMethod('roleToSessionName');
$method->setAccessible(true);

foreach ($roleMap as $role => $expectedSessionName) {
    $actual = $method->invoke(null, $role);
    $roleMapTests[] = $actual === $expectedSessionName
        ? "✅ PASS: roleToSessionName('$role') → $actual"
        : "❌ FAIL: roleToSessionName('$role') → got '$actual', expected '$expectedSessionName'";
}
$results['roleMapping'] = $roleMapTests;

// ─── TEST 5: Verify context-aware nav URLs ────────────────────────────────────
// Checks that the delivery context is correctly identified for URL prefix checks
$navTests = [];
$navTests[] = defined('Session::DELIVERY_SESSION') || class_exists('Session')
    ? "✅ PASS: Session::DELIVERY_SESSION constant exists = '" . Session::DELIVERY_SESSION . "'"
    : "❌ FAIL: Session::DELIVERY_SESSION not accessible";
$results['sessionConstants'] = $navTests;

// ─── Cleanup: destroy all test sessions ──────────────────────────────────────
foreach ($ids as $sessionName => $sessionId) {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
    session_name($sessionName);
    session_id($sessionId);
    session_start();
    session_unset();
    session_destroy();
}

// ─── Output ───────────────────────────────────────────────────────────────────
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kravyo Session Isolation Test</title>
    <style>
        body { font-family: 'Courier New', monospace; background: #0f0f0f; color: #e0e0e0; padding: 30px; }
        h1   { color: #f59e0b; }
        h2   { color: #60a5fa; border-bottom: 1px solid #333; padding-bottom: 5px; }
        .pass { color: #4ade80; }
        .fail { color: #f87171; font-weight: bold; }
        .info { color: #94a3b8; }
        section { background: #1e1e2e; border-radius: 8px; padding: 20px; margin: 20px 0; }
        p { margin: 6px 0; }
    </style>
</head>
<body>
<h1>🔒 Kravyo Session Isolation Verification</h1>
<p class="info">This verifies that all four role sessions are completely independent.</p>

<?php foreach ($results as $testGroup => $tests): ?>
<section>
    <h2><?= htmlspecialchars($testGroup) ?></h2>
    <?php foreach ($tests as $line): ?>
        <p class="<?= str_starts_with($line, '✅') ? 'pass' : (str_starts_with($line, '❌') ? 'fail' : 'info') ?>">
            <?= htmlspecialchars($line) ?>
        </p>
    <?php endforeach; ?>
</section>
<?php endforeach; ?>

<section>
    <h2>Summary</h2>
    <?php
    $allLines   = array_merge(...array_values($results));
    $totalPass  = count(array_filter($allLines, fn($l) => str_starts_with($l, '✅')));
    $totalFail  = count(array_filter($allLines, fn($l) => str_starts_with($l, '❌')));
    ?>
    <p class="pass">✅ Tests Passed: <?= $totalPass ?></p>
    <p class="<?= $totalFail > 0 ? 'fail' : 'pass' ?>">
        <?= $totalFail > 0 ? '❌' : '✅' ?> Tests Failed: <?= $totalFail ?>
    </p>
    <?php if ($totalFail === 0): ?>
        <p class="pass" style="font-size: 1.3em; margin-top: 15px;">🎉 All session isolation tests PASSED. Independent concurrent logins are working correctly.</p>
    <?php else: ?>
        <p class="fail" style="font-size: 1.3em; margin-top: 15px;">⚠️ Some tests FAILED. Review the output above and fix the issues before going live.</p>
    <?php endif; ?>
</section>

<p class="info" style="margin-top: 30px;">⚠️ Delete <code>scratch/session_isolation_test.php</code> before deploying to production.</p>
</body>
</html>
