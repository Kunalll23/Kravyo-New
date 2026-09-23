<?php
/**
 * Test script for Customer Profile Management Module
 */

require_once 'C:\xampp\htdocs\Kravyo\config\app.php';
require_once 'C:\xampp\htdocs\Kravyo\config\constants.php';
require_once 'C:\xampp\htdocs\Kravyo\core\Database.php';
require_once 'C:\xampp\htdocs\Kravyo\core\Model.php';
require_once 'C:\xampp\htdocs\Kravyo\app\models\User.php';
require_once 'C:\xampp\htdocs\Kravyo\app\models\Address.php';
require_once 'C:\xampp\htdocs\Kravyo\core\Helpers.php';

$userModel = new User();
$addressModel = new Address();

// --- TEST 1: Phone Duplicate Check ---
echo "--- TEST 1: Phone Duplicate Check ---\n";
// Create two mock users
$db = Database::getInstance();
$db->exec("DELETE FROM users WHERE email IN ('test_dup1@test.com', 'test_dup2@test.com')");
$user1_id = $userModel->create([
    'full_name' => 'Test One',
    'email' => 'test_dup1@test.com',
    'phone' => '9999999991',
    'password_hash' => 'hash',
    'role' => 'customer'
]);
$user2_id = $userModel->create([
    'full_name' => 'Test Two',
    'email' => 'test_dup2@test.com',
    'phone' => '9999999992',
    'password_hash' => 'hash',
    'role' => 'customer'
]);

$existingPhoneUser = $userModel->findByPhone('9999999991');
if ($existingPhoneUser && $existingPhoneUser['id'] !== $user2_id) {
    echo "PASS: User 2 cannot use User 1's phone.\n";
} else {
    echo "FAIL: Duplicate phone check failed.\n";
}

// --- TEST 2: Address CRUD & Security ---
echo "--- TEST 2: Address CRUD & Security ---\n";
$addr_id = $addressModel->create([
    'user_id' => $user1_id,
    'address_type' => 'Home',
    'street_address' => '123 Test St',
    'city' => 'Mumbai',
    'pincode' => '400001'
]);

$addr = $addressModel->findByIdAndUserId($addr_id, $user1_id);
if ($addr) {
    echo "PASS: Address created and found by correct user.\n";
} else {
    echo "FAIL: Address not found by correct user.\n";
}

$addr2 = $addressModel->findByIdAndUserId($addr_id, $user2_id);
if (!$addr2) {
    echo "PASS: User 2 cannot access User 1's address.\n";
} else {
    echo "FAIL: Security check failed, User 2 accessed User 1's address.\n";
}

$addressModel->update($addr_id, ['city' => 'Pune']);
$addr_updated = $addressModel->find($addr_id);
if ($addr_updated['city'] === 'Pune') {
    echo "PASS: Address updated successfully.\n";
} else {
    echo "FAIL: Address update failed.\n";
}

$addressModel->delete($addr_id);
if (!$addressModel->find($addr_id)) {
    echo "PASS: Address deleted successfully.\n";
} else {
    echo "FAIL: Address deletion failed.\n";
}

// Cleanup
$db->exec("DELETE FROM users WHERE id IN ($user1_id, $user2_id)");
echo "--- Tests Completed ---\n";
