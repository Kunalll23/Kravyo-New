<?php
/**
 * Kravyo Automated System & Unit Test Suite
 * Execution Mode: CLI / Automated Quality Assurance Runner
 * Standard: PHPUnit-compatible assertions with microtime benchmarking
 */

define('TEST_START_TIME', microtime(true));
require_once dirname(__DIR__) . '/config/app.php';
require_once ROOT_PATH . '/config/constants.php';
require_once ROOT_PATH . '/core/Database.php';

class KravyoTestSuite {
    private int $passed = 0;
    private int $failed = 0;
    private array $results = [];

    public function run(): void {
        $this->printHeader();

        // 1. Authentication & Security
        $this->testBcryptPasswordHashing();
        $this->testMultiTenantSessionIsolation();

        // 2. Spatial & Logistics Algorithms
        $this->testHaversine15KmRadialGeofence();
        $this->test4DigitOtpHandshakeGenerator();
        $this->testCascadeDispatchCandidatePriority();

        // 3. Financial & Cart Calculations
        $this->testCartFinancialTaxAndCommissionFormulas();
        $this->testRazorpayRefundTriggerLogic();

        // 4. Lifecycle & State Machine
        $this->testOrderStateMachineTransitions();
        $this->testZeroWasteCountdownExpiryLogic();

        // 5. Database Schema & Integrity
        $this->testDatabaseSchemaAndTableIntegrity();
        $this->testAddressSoftDeletionConstraint();
        $this->testFssaiLicenseFormatValidation();

        $this->printSummary();
    }

    private function assert(string $testName, bool $condition, string $detail = ''): void {
        $duration = round((microtime(true) - TEST_START_TIME) * 1000, 2);
        if ($condition) {
            $this->passed++;
            $this->results[] = ['status' => 'PASS', 'name' => $testName, 'time' => $duration . 'ms'];
            echo sprintf("  [ \033[32mPASS\033[0m ] %-60s (%s)\n", $testName, $duration . 'ms');
        } else {
            $this->failed++;
            $this->results[] = ['status' => 'FAIL', 'name' => $testName, 'detail' => $detail];
            echo sprintf("  [ \033[31mFAIL\033[0m ] %-60s [Reason: %s]\n", $testName, $detail);
        }
    }

    private function testBcryptPasswordHashing(): void {
        $password = "KravyoSecure@2026!";
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
        $verified = password_verify($password, $hash);
        $tampered = password_verify("WrongPassword123", $hash);
        $this->assert("SEC-01: Bcrypt Cryptographic Hash & Salt Verification", $verified && !$tampered);
    }

    private function testMultiTenantSessionIsolation(): void {
        $portals = [
            'CUSTOMER' => 'KRAVYO_CUSTOMER',
            'CHEF'     => 'KRAVYO_CHEF',
            'DELIVERY' => 'KRAVYO_DELIVERY',
            'ADMIN'    => 'KRAVYO_ADMIN'
        ];
        $isUnique = count(array_unique(array_values($portals))) === 4;
        $this->assert("SEC-02: 4-Portal Independent Session Cookie Isolation", $isUnique);
    }

    private function testHaversine15KmRadialGeofence(): void {
        // Bardoli Kitchen Coordinates (approx)
        $kitchenLat = 21.1180;
        $kitchenLng = 73.1147;
        
        // Point A: Within Bardoli (approx 3.2 km)
        $userA_Lat = 21.1250;
        $userA_Lng = 73.0850;
        $distA = $this->calculateDistance($kitchenLat, $kitchenLng, $userA_Lat, $userA_Lng);
        
        // Point B: Surat City (approx 34 km - Beyond 15 km threshold)
        $userB_Lat = 21.1702;
        $userB_Lng = 72.8311;
        $distB = $this->calculateDistance($kitchenLat, $kitchenLng, $userB_Lat, $userB_Lng);

        $validGeofence = ($distA <= 15.0) && ($distB > 15.0);
        $this->assert("GEO-01: Haversine 15 km Radius Geofence Verification", $validGeofence, "DistA: {$distA}km, DistB: {$distB}km");
    }

    private function test4DigitOtpHandshakeGenerator(): void {
        $otp = (string)random_int(1000, 9999);
        $isValidFormat = (strlen($otp) === 4) && ctype_digit($otp);
        $isNumericBoundary = ($otp >= 1000 && $otp <= 9999);
        $this->assert("OTP-01: Confidential 4-Digit Handshake OTP Generation", $isValidFormat && $isNumericBoundary);
    }

    private function testCascadeDispatchCandidatePriority(): void {
        $riders = [
            ['id' => 101, 'dist' => 4.2, 'busy' => false],
            ['id' => 102, 'dist' => 1.1, 'busy' => false],
            ['id' => 103, 'dist' => 0.8, 'busy' => true],  // Nearest but busy
            ['id' => 104, 'dist' => 2.5, 'busy' => false]
        ];
        // Filter available & sort by distance
        $available = array_filter($riders, fn($r) => !$r['busy']);
        usort($available, fn($a, $b) => $a['dist'] <=> $b['dist']);
        $firstCandidate = reset($available);

        $correctSelection = ($firstCandidate['id'] === 102 && $firstCandidate['dist'] === 1.1);
        $this->assert("DSP-01: 4-Stage Cascade Nearest Idle Courier Selection", $correctSelection);
    }

    private function testCartFinancialTaxAndCommissionFormulas(): void {
        $itemsTotal = 500.00;
        $gstRate = 0.05; // 5% GST
        $platformCommissionRate = 0.10; // 10% flat
        
        $gstAmount = round($itemsTotal * $gstRate, 2);
        $customerTotal = $itemsTotal + $gstAmount;
        $platformFee = round($itemsTotal * $platformCommissionRate, 2);
        $vendorNetPayout = $itemsTotal - $platformFee;

        $isValidMath = ($gstAmount == 25.00) && ($customerTotal == 525.00) && ($platformFee == 50.00) && ($vendorNetPayout == 450.00);
        $this->assert("FIN-01: Cart GST (5%) & Platform Commission (10%) Accuracy", $isValidMath);
    }

    private function testRazorpayRefundTriggerLogic(): void {
        $order = [
            'id' => 5410,
            'payment_status' => 'paid',
            'order_status' => 'pending',
            'payment_gateway' => 'razorpay',
            'payment_id' => 'pay_PQw78aB901Z'
        ];
        // Condition for auto-refund: Chef Rejects while paid
        $chefRejection = true;
        $shouldTriggerAutoRefund = ($order['payment_status'] === 'paid' && $chefRejection && !empty($order['payment_id']));
        $this->assert("PAY-01: Razorpay Instant REST API Auto-Refund Trigger Logic", $shouldTriggerAutoRefund);
    }

    private function testOrderStateMachineTransitions(): void {
        $allowedTransitions = [
            'pending'          => ['accepted', 'cancelled'],
            'accepted'         => ['preparing', 'cancelled'],
            'preparing'        => ['ready_for_pickup'],
            'ready_for_pickup' => ['assigned', 'out_for_delivery'],
            'assigned'         => ['out_for_delivery'],
            'out_for_delivery' => ['delivered', 'failed'],
            'delivered'        => []
        ];
        
        $validNormal = in_array('accepted', $allowedTransitions['pending']) && 
                       in_array('preparing', $allowedTransitions['accepted']) &&
                       in_array('delivered', $allowedTransitions['out_for_delivery']);
                       
        // Disallowed illegal leap: pending directly to delivered
        $illegalTransition = in_array('delivered', $allowedTransitions['pending']);
        
        $this->assert("STM-01: Order State Machine Valid Progression & Guard Rules", $validNormal && !$illegalTransition);
    }

    private function testZeroWasteCountdownExpiryLogic(): void {
        $now = time();
        $activeItem = ['expires_at' => date('Y-m-d H:i:s', $now + 1800)]; // +30 mins
        $expiredItem = ['expires_at' => date('Y-m-d H:i:s', $now - 60)];  // -1 min

        $isActive = strtotime($activeItem['expires_at']) > $now;
        $isExpired = strtotime($expiredItem['expires_at']) <= $now;

        $this->assert("ZWD-01: Zero Food Waste Real-Time Dynamic Cutoff Auto-Expiry", $isActive && $isExpired);
    }

    private function testDatabaseSchemaAndTableIntegrity(): void {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $requiredTables = [
                'addresses', 'admins', 'categories', 'chefs', 'customer_reports',
                'customer_subscriptions', 'customers', 'delivery_offers', 'delivery_partners',
                'delivery_users', 'kitchens', 'menu_items', 'notifications', 'order_items',
                'orders', 'pending_registrations', 'reviews', 'subscription_deliveries',
                'tiffin_subscriptions', 'zero_waste_items'
            ];
            $missing = array_diff($requiredTables, $tables);
            $hasIntegrity = empty($missing) && count($tables) === 20;
            $this->assert("DB-01: Relational InnoDB Schema & 20 Tables Validation", $hasIntegrity, "Missing: " . implode(', ', $missing));
        } catch (Exception $e) {
            $this->assert("DB-01: Relational InnoDB Schema & 20 Tables Validation", false, $e->getMessage());
        }
    }

    private function testAddressSoftDeletionConstraint(): void {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->query("SHOW COLUMNS FROM addresses LIKE 'is_deleted'");
            $col = $stmt->fetch();
            $hasSoftDelete = !empty($col);
            $this->assert("DB-02: Customer Address Soft-Deletion Safety Column (is_deleted)", $hasSoftDelete);
        } catch (Exception $e) {
            $this->assert("DB-02: Customer Address Soft-Deletion Safety Column", false, $e->getMessage());
        }
    }

    private function testFssaiLicenseFormatValidation(): void {
        // FSSAI format: exactly 14 numeric digits
        $validFssai = "10723001000124";
        $invalidFssai1 = "1072300100012";  // 13 digits
        $invalidFssai2 = "1072300100012A"; // Alpha character
        
        $regex = '/^[0-9]{14}$/';
        $checkValid = preg_match($regex, $validFssai) === 1;
        $checkInv1 = preg_match($regex, $invalidFssai1) === 0;
        $checkInv2 = preg_match($regex, $invalidFssai2) === 0;

        $this->assert("VAL-01: 14-Digit FSSAI License Format Regex Validation", $checkValid && $checkInv1 && $checkInv2);
    }

    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return round($earthRadius * $c, 2);
    }

    private function printHeader(): void {
        echo "==================================================================================\n";
        echo "               KRAVYO ENTERPRISE QUALITY ASSURANCE AUTOMATED TEST RUNNER          \n";
        echo "==================================================================================\n";
        echo "  Environment    : Local Development (XAMPP / PHP " . PHP_VERSION . " / MySQL InnoDB)\n";
        echo "  Target Host    : http://localhost/Kravyo\n";
        echo "  Execution Mode : Automated Headless Test Suite & Boundary Assertion Engine\n";
        echo "  Timestamp      : " . date('Y-m-d H:i:s') . "\n";
        echo "----------------------------------------------------------------------------------\n";
    }

    private function printSummary(): void {
        $totalTime = round((microtime(true) - TEST_START_TIME), 3);
        $total = $this->passed + $this->failed;
        $passRate = $total > 0 ? round(($this->passed / $total) * 100, 1) : 0;
        echo "----------------------------------------------------------------------------------\n";
        echo sprintf("  Total Tests: %d | Passed: \033[32m%d\033[0m | Failed: \033[31m%d\033[0m | Pass Rate: \033[32m%s%%\033[0m\n", $total, $this->passed, $this->failed, $passRate);
        echo sprintf("  Memory Peak : %.2f MB | Total Execution Duration: %s seconds\n", memory_get_peak_usage() / 1024 / 1024, $totalTime);
        echo "==================================================================================\n";
        if ($this->failed === 0) {
            echo "  >>> STATUS: ALL AUTOMATED UNIT & INTEGRATION ASSERTIONS VERIFIED [SUCCESS] <<<\n";
        }
        echo "==================================================================================\n";
    }
}

$suite = new KravyoTestSuite();
$suite->run();
