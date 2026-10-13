<?php
/**
 * Kravyo Nearby Cloud Kitchens Feature - Automated Test Suite
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/constants.php';
require_once CORE_PATH . '/Database.php';
require_once CORE_PATH . '/Model.php';
require_once APP_PATH . '/models/Kitchen.php';

$kitchenModel = new Kitchen();
$db = Database::getInstance();

$passed = 0;
$failed = 0;

function assertTest(string $name, bool $condition, string $detail = '') {
    global $passed, $failed;
    if ($condition) {
        echo "✅ PASS: $name " . ($detail ? "($detail)" : "") . "\n";
        $passed++;
    } else {
        echo "❌ FAIL: $name " . ($detail ? "($detail)" : "") . "\n";
        $failed++;
    }
}

echo "========================================================\n";
echo "RUNNING KRAVYO NEARBY KITCHENS TEST SUITE\n";
echo "========================================================\n\n";

// ── TEST 1: Customer allows location (Pune) ──────────────────────────
$puneKitchens = $kitchenModel->findNearby(18.5204, 73.8567, 10.0);
assertTest("TEST 1 - Pune Location Query", count($puneKitchens) > 0, count($puneKitchens) . " kitchens found");
$mehul = array_filter($puneKitchens, fn($k) => strpos($k['kitchen_name'], 'Mehul') !== false);
assertTest("TEST 1b - Mehul's Kitchen in Pune Results", !empty($mehul), "Mehul's Kitchen is within 10km");

// ── TEST 2: Denial / browse without coords ───────────────────────────
$allCoords = $kitchenModel->findAllWithCoordinates([], 20);
assertTest("TEST 2 - Fallback browse without coordinates", count($allCoords) >= 20, count($allCoords) . " approved kitchens loaded");

// ── TEST 3: Radius expansion ─────────────────────────────────────────
$rad2 = $kitchenModel->findNearby(18.5204, 73.8567, 2.0);
$rad10 = $kitchenModel->findNearby(18.5204, 73.8567, 10.0);
$rad50 = $kitchenModel->findNearby(18.5204, 73.8567, 50.0);
assertTest("TEST 3 - Radius Expansion", count($rad50) >= count($rad10) && count($rad10) >= count($rad2), 
    "2km: " . count($rad2) . " <= 10km: " . count($rad10) . " <= 50km: " . count($rad50));

// ── TEST 4: No kitchens nearby (North Pole) ──────────────────────────
$emptyResults = $kitchenModel->findNearby(85.0, 0.0, 10.0);
assertTest("TEST 4 - No kitchens in remote area", count($emptyResults) === 0, "Returned 0 kitchens as expected");

// ── TEST 5 & 6: Unapproved or non-approved kitchens excluded ─────────
// Temporarily create or check pending kitchen
$pendingCount = $db->query("SELECT COUNT(*) FROM kitchens WHERE approval_status != 'approved'")->fetchColumn();
$nearbyKitchensSample = $kitchenModel->findNearby(18.5204, 73.8567, 50.0);
$anyUnapproved = false;
foreach ($nearbyKitchensSample as $k) {
    if (($k['approval_status'] ?? 'approved') !== 'approved') {
        $anyUnapproved = true;
    }
}
assertTest("TEST 5 & 6 - Only approved kitchens shown", !$anyUnapproved, "All results have approval_status = approved");

// ── TEST 7: Existing kitchen detail page link ────────────────────────
$sampleKitchen = $puneKitchens[0] ?? null;
assertTest("TEST 7 - Kitchen ID valid for detail route", !empty($sampleKitchen['id']) && $sampleKitchen['id'] > 0, "ID: " . ($sampleKitchen['id'] ?? 0));

// ── TEST 8: Haversine distance accuracy ──────────────────────────────
// Distance between Mehul's Kitchen (18.5314, 73.8446) and Pune City Center (18.5204, 73.8567)
// Great circle formula: ~1.75 km
$foundMehul = null;
foreach ($puneKitchens as $k) {
    if ($k['id'] == 43) { $foundMehul = $k; break; }
}
$dist = $foundMehul ? (float)$foundMehul['distance'] : 0;
assertTest("TEST 8 - Distance Calculation Accuracy", $dist >= 1.5 && $dist <= 2.0, "Calculated distance: {$dist} km (expected ~1.77 km)");

// ── TEST 9: HTTP API Endpoint JSON Verification ──────────────────────
$apiJson = file_get_contents('http://localhost/Kravyo/api/kitchens/nearby?lat=18.5204&lng=73.8567&radius=10');
$apiData = json_decode($apiJson, true);
assertTest("TEST 9a - API returns valid JSON", is_array($apiData) && ($apiData['success'] ?? false) === true);
assertTest("TEST 9b - API returns kitchens list", !empty($apiData['kitchens']) && is_array($apiData['kitchens']));
assertTest("TEST 9c - API exposes only safe fields", 
    isset($apiData['kitchens'][0]['name']) && 
    isset($apiData['kitchens'][0]['distance']) && 
    !isset($apiData['kitchens'][0]['password_hash']) && 
    !isset($apiData['kitchens'][0]['admin_notes'])
);

// ── TEST 10: Security Validation on Invalid Coordinates ──────────────
// Send invalid latitude (95 > 90)
$context = stream_context_create(['http' => ['ignore_errors' => true]]);
$badLatJson = file_get_contents('http://localhost/Kravyo/api/kitchens/nearby?lat=95.0&lng=73.8567', false, $context);
$badLatData = json_decode($badLatJson, true);
assertTest("TEST 10a - Rejects invalid latitude (> 90)", ($badLatData['success'] ?? true) === false, "Proper 400 error returned");

// SQL injection attempt in keyword
$sqlInjJson = file_get_contents('http://localhost/Kravyo/api/kitchens/nearby?lat=18.5204&lng=73.8567&q=' . urlencode("' OR 1=1 --"), false, $context);
$sqlInjData = json_decode($sqlInjJson, true);
assertTest("TEST 10b - SQL injection in search safely handled", ($sqlInjData['success'] ?? false) === true, "Query safely executed without error");

// ── TEST 11: Existing Kravyo Features Unbroken ───────────────────────
$pages = [
    '/'                 => 'Home Page',
    '/kitchens'         => 'Explore Kitchens',
    '/menu'             => 'Browse Dishes',
    '/login'            => 'Customer Login',
    '/register'         => 'Customer Register',
    '/zero-waste'       => 'Zero Waste Deals',
    '/subscriptions'    => 'Tiffin Plans',
];

foreach ($pages as $p => $lbl) {
    $res = file_get_contents("http://localhost/Kravyo" . $p);
    $ok = ($res !== false && strlen($res) > 500 && !preg_match('/(Fatal error|Parse error):/i', $res));
    assertTest("TEST 11 - Existing Page: $lbl ($p)", $ok, strlen($res) . " bytes");
}

echo "\n========================================================\n";
echo "TEST RESULTS: Passed: $passed | Failed: $failed\n";
echo "========================================================\n";
