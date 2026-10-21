<?php
/**
 * Kravyo — Targeted Dashboard Seeder for Mehul Chinchangare's Kitchen
 * Fills: Menu Items, Orders, Order Items, Reviews, Subscriptions, Zero Waste Items
 * Run: php database/seed_mehul_dashboard.php
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';

try {
    $db = Database::getInstance();
    echo "✅ Connected to database: kravyo_db\n\n";

    // ─── STEP 1: Find Mehul's user & kitchen ─────────────────────────────────
    $userRow = $db->query("SELECT id, full_name FROM users WHERE email = 'mehul@example.com' OR full_name LIKE '%Mehul%' LIMIT 1")->fetch(PDO::FETCH_ASSOC);

    if (!$userRow) {
        // Try by name pattern
        $userRow = $db->query("SELECT id, full_name FROM users WHERE role = 'chef' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    }

    if (!$userRow) {
        die("❌ No chef user found. Please register Mehul first.\n");
    }

    $chefUserId = (int) $userRow['id'];
    $chefName   = $userRow['full_name'];
    echo "👤 Chef User: [$chefUserId] $chefName\n";

    // Find kitchen
    $kitchen = $db->query("SELECT * FROM kitchens WHERE user_id = $chefUserId")->fetch(PDO::FETCH_ASSOC);

    if (!$kitchen) {
        die("❌ No kitchen found for user ID $chefUserId. Please complete chef profile first.\n");
    }

    $kitchenId = (int) $kitchen['id'];
    echo "🍳 Kitchen: [$kitchenId] {$kitchen['kitchen_name']}\n\n";

    // ─── STEP 2: Approve the kitchen so dashboard shows APPROVED status ──────
    $db->query("UPDATE kitchens SET 
        approval_status = 'approved',
        is_open = 1,
        address = '12, Shivaji Nagar, Near Market',
        city = 'Pune',
        pincode = '411005',
        fssai_license = 'FSSAI-2024-MH-12345',
        personal_story = 'I am passionate about cooking authentic home-style meals. Started this kitchen to bring the taste of home to everyone in the city.',
        hygiene_badge = 'verified'
        WHERE id = $kitchenId");
    echo "✅ Kitchen approved & profile filled.\n";

    // ─── STEP 3: Ensure Categories exist ─────────────────────────────────────
    $catMap = [];
    $catNames = ['North Indian', 'South Indian', 'Healthy Salads', 'Desserts', 'Snacks & Bites', 'Beverages'];
    foreach ($catNames as $cn) {
        $existing = $db->query("SELECT id FROM categories WHERE category_name = " . $db->quote($cn))->fetch(PDO::FETCH_ASSOC);
        if ($existing) {
            $catMap[$cn] = (int) $existing['id'];
        } else {
            $db->query("INSERT INTO categories (category_name, description) VALUES (" . $db->quote($cn) . ", 'Delicious $cn dishes')");
            $catMap[$cn] = (int) $db->lastInsertId();
        }
    }
    echo "✅ Categories ready: " . implode(', ', array_keys($catMap)) . "\n";

    // ─── STEP 4: Clear old data for this kitchen ──────────────────────────────
    // Get all order IDs for this kitchen
    $orderIdsRaw = $db->query("SELECT id FROM orders WHERE kitchen_id = $kitchenId")->fetchAll(PDO::FETCH_COLUMN);
    if (!empty($orderIdsRaw)) {
        $orderIdList = implode(',', $orderIdsRaw);
        $db->query("DELETE FROM order_items WHERE order_id IN ($orderIdList)");
        $db->query("DELETE FROM reviews WHERE kitchen_id = $kitchenId");
        $db->query("DELETE FROM orders WHERE kitchen_id = $kitchenId");
    }
    $db->query("DELETE FROM zero_waste_items WHERE kitchen_id = $kitchenId");
    $db->query("DELETE FROM menu_items WHERE kitchen_id = $kitchenId");
    $db->query("DELETE FROM tiffin_subscriptions WHERE kitchen_id = $kitchenId");
    $db->query("DELETE FROM customer_subscriptions WHERE kitchen_id = $kitchenId");
    echo "🗑️  Old data cleared for kitchen.\n";

    // ─── STEP 5: Seed Menu Items ──────────────────────────────────────────────
    $menuItems = [
        // [name, description, price, is_veg, is_jain, is_diabetic, category]
        ['Paneer Butter Masala',     'Rich creamy tomato gravy with soft paneer cubes',          220, 1, 1, 0, 'North Indian'],
        ['Dal Makhani',              'Slow-cooked black lentils with butter and cream',           180, 1, 0, 1, 'North Indian'],
        ['Aloo Paratha',             'Stuffed whole wheat flatbread with spiced potato filling', 120, 1, 1, 0, 'North Indian'],
        ['Rajma Chawal',             'Kidney bean curry served with steamed basmati rice',       160, 1, 0, 1, 'North Indian'],
        ['Chole Bhature',            'Spiced chickpea curry with fluffy deep-fried bread',       150, 1, 0, 0, 'North Indian'],
        ['Masala Dosa',              'Crispy rice crepe filled with spiced potato masala',       130, 1, 1, 0, 'South Indian'],
        ['Idli Sambar',              'Soft steamed rice cakes with piping hot sambar',           100, 1, 1, 1, 'South Indian'],
        ['Medu Vada',                'Crispy lentil donuts served with coconut chutney',         110, 1, 1, 0, 'South Indian'],
        ['Quinoa Buddha Bowl',       'Nutritious quinoa with roasted veggies and tahini',        250, 1, 0, 1, 'Healthy Salads'],
        ['Greek Salad',              'Fresh cucumber, tomato, olives with feta cheese',          200, 1, 1, 1, 'Healthy Salads'],
        ['Gulab Jamun',              'Soft milk solid balls soaked in rose-flavored sugar syrup',  90, 1, 0, 0, 'Desserts'],
        ['Kheer',                    'Creamy rice pudding with saffron and cardamom',            110, 1, 1, 0, 'Desserts'],
        ['Samosa (2 pcs)',           'Crispy pastry filled with spiced potatoes and peas',        60, 1, 0, 0, 'Snacks & Bites'],
        ['Dhokla',                   'Soft and spongy fermented chickpea flour snack',            80, 1, 1, 1, 'Snacks & Bites'],
        ['Mango Lassi',              'Chilled yogurt drink blended with fresh Alphonso mango',    90, 1, 1, 0, 'Beverages'],
        ['Masala Chaas',             'Spiced buttermilk with coriander and roasted cumin',        60, 1, 1, 1, 'Beverages'],
    ];

    $menuItemIds = [];
    $stmtMenu = $db->prepare("INSERT INTO menu_items 
        (kitchen_id, category_id, item_name, description, price, is_veg, is_jain_available, is_diabetic_friendly, is_available) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)");

    foreach ($menuItems as $mi) {
        $catId = $catMap[$mi[6]] ?? array_values($catMap)[0];
        $stmtMenu->execute([$kitchenId, $catId, $mi[0], $mi[1], $mi[2], $mi[3], $mi[4], $mi[5]]);
        $menuItemIds[$mi[0]] = (int) $db->lastInsertId();
    }
    echo "✅ " . count($menuItemIds) . " Menu Items seeded.\n";

    // ─── STEP 6: Find or create customer users for orders ────────────────────
    $customers = $db->query("SELECT id FROM users WHERE role = 'customer' LIMIT 10")->fetchAll(PDO::FETCH_COLUMN);

    if (empty($customers)) {
        // Create dummy customers
        $dummyCustomers = [
            ['Priya Sharma',    'priya.sharma@gmail.com',    '9876543210'],
            ['Rahul Verma',     'rahul.verma@gmail.com',     '9876543211'],
            ['Sneha Patil',     'sneha.patil@gmail.com',     '9876543212'],
            ['Amit Desai',      'amit.desai@gmail.com',      '9876543213'],
            ['Pooja Mehta',     'pooja.mehta@gmail.com',     '9876543214'],
        ];
        $passHash = password_hash('password123', PASSWORD_DEFAULT);
        $stmtUser = $db->prepare("INSERT INTO users (full_name, email, phone, password_hash, role, status) VALUES (?, ?, ?, ?, 'customer', 'active')");
        foreach ($dummyCustomers as $dc) {
            $existing = $db->query("SELECT id FROM users WHERE email = " . $db->quote($dc[1]))->fetch(PDO::FETCH_ASSOC);
            if ($existing) {
                $customers[] = (int) $existing['id'];
            } else {
                $stmtUser->execute([$dc[0], $dc[1], $dc[2], $passHash]);
                $customers[] = (int) $db->lastInsertId();
            }
        }
        echo "✅ " . count($customers) . " Customer users created.\n";
    } else {
        echo "✅ Found " . count($customers) . " existing customers.\n";
    }

    // ─── STEP 7: Create or get addresses for customers ───────────────────────
    $addressIds = [];
    $stmtAddr = $db->prepare("INSERT INTO addresses (user_id, address_type, street_address, landmark, city, pincode) VALUES (?, ?, ?, ?, ?, ?)");
    $streets = ['12 MG Road', '45 FC Road', '7 Koregaon Park', '23 Baner', '88 Viman Nagar', '3 Aundh', '56 Kothrud'];
    $landmarks = ['Near Bus Stand', 'Opp. Market', 'Behind Mall', 'Near Park', 'Next to School'];

    foreach ($customers as $cId) {
        $existing = $db->query("SELECT id FROM addresses WHERE user_id = $cId LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        if ($existing) {
            $addressIds[$cId] = (int) $existing['id'];
        } else {
            $stmtAddr->execute([$cId, 'Home', $streets[array_rand($streets)], $landmarks[array_rand($landmarks)], 'Pune', '411001']);
            $addressIds[$cId] = (int) $db->lastInsertId();
        }
    }
    echo "✅ Addresses ready.\n";

    // ─── STEP 8: Seed Orders with realistic spread over last 30 days ─────────
    $menuItemList = array_values($menuItemIds);
    $orderStatuses = [
        'delivered'        => 20, // 20 delivered orders
        'accepted'         =>  5,
        'preparing'        =>  4,
        'out_for_delivery' =>  3,
        'pending'          =>  3,
        'cancelled'        =>  2,
    ];
    $payMethods = ['cod', 'upi'];

    $stmtOrder = $db->prepare("INSERT INTO orders 
        (order_number, customer_id, kitchen_id, address_id, total_amount, payment_method, payment_status, order_status, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmtItem = $db->prepare("INSERT INTO order_items 
        (order_id, menu_item_id, quantity, unit_price, subtotal, spice_level, oil_level, is_jain) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 0)");

    $deliveredOrderData = [];
    $orderCounter = 0;
    $spiceLevels = ['Low', 'Medium', 'High'];
    $oilLevels   = ['Normal', 'Less Oil'];

    foreach ($orderStatuses as $status => $count) {
        for ($i = 0; $i < $count; $i++) {
            $cId    = $customers[array_rand($customers)];
            $aId    = $addressIds[$cId] ?? $addressIds[array_values($customers)[0]];
            $pay    = $payMethods[array_rand($payMethods)];
            $payStatus = ($status === 'delivered') ? 'completed' : (($pay === 'cod') ? 'pending' : 'completed');

            // Spread across last 30 days; recent statuses closer to today
            $daysAgo = match($status) {
                'delivered'        => mt_rand(1, 30),
                'out_for_delivery' => mt_rand(0, 1),
                'preparing'        => mt_rand(0, 1),
                'accepted'         => mt_rand(0, 2),
                'pending'          => 0,
                'cancelled'        => mt_rand(5, 30),
            };
            $hoursAgo = mt_rand(0, 23);
            $createdAt = date('Y-m-d H:i:s', strtotime("-$daysAgo days -$hoursAgo hours"));

            $orderNum = 'ORD' . date('Ymd', strtotime($createdAt)) . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // Pick 1-4 menu items
            $numItems = mt_rand(1, 4);
            shuffle($menuItemList);
            $selectedItems = array_slice($menuItemList, 0, $numItems);

            // Calculate total
            $totalAmount = 0;
            $lineItems   = [];
            foreach ($selectedItems as $miId) {
                $priceRow = $db->query("SELECT price FROM menu_items WHERE id = $miId")->fetch(PDO::FETCH_ASSOC);
                $qty = mt_rand(1, 3);
                $price = (float) $priceRow['price'];
                $subtotal = $price * $qty;
                $totalAmount += $subtotal;
                $lineItems[] = [$miId, $qty, $price, $subtotal];
            }

            $stmtOrder->execute([$orderNum, $cId, $kitchenId, $aId, round($totalAmount, 2), $pay, $payStatus, $status, $createdAt]);
            $orderId = (int) $db->lastInsertId();
            $orderCounter++;

            foreach ($lineItems as $li) {
                $stmtItem->execute([$orderId, $li[0], $li[1], $li[2], $li[3], $spiceLevels[array_rand($spiceLevels)], $oilLevels[array_rand($oilLevels)]]);
            }

            if ($status === 'delivered') {
                $deliveredOrderData[] = ['order_id' => $orderId, 'customer_id' => $cId, 'kitchen_id' => $kitchenId];
            }
        }
    }
    echo "✅ $orderCounter Orders seeded (20 delivered, rest in various statuses).\n";

    // ─── STEP 9: Seed Reviews for delivered orders ────────────────────────────
    $reviewTexts = [
        5 => [
            "Absolutely loved the food! Paneer Butter Masala was divine. Will order again!",
            "Best home-cooked food I've had outside my own home. Highly recommend!",
            "Fresh, hygienic, and tastes exactly like my mom's cooking. 5 stars easily!",
            "Superb quality and generous portions. Delivery was also very prompt.",
        ],
        4 => [
            "Really good food. Dal Makhani was rich and flavorful. Slightly late but worth it.",
            "Very tasty and authentic. The dosa was perfectly crispy. Will order again!",
            "Good food and good quantity. Nice packaging too.",
        ],
        3 => [
            "Food was okay. Average taste but good hygiene. Could improve on spices.",
            "Decent meal. Nothing extraordinary but served its purpose.",
        ],
        2 => [
            "Food was cold by the time it arrived. Taste was okay though.",
        ],
        1 => [
            "Too oily for my taste. Not what I expected.",
        ],
    ];

    $stmtReview = $db->prepare("INSERT INTO reviews (order_id, customer_id, kitchen_id, rating, review_text) VALUES (?, ?, ?, ?, ?)");
    $reviewCount = 0;

    foreach ($deliveredOrderData as $do) {
        // 80% chance of leaving a review
        if (mt_rand(1, 10) <= 8) {
            // Weight ratings towards 4-5 stars
            $ratingWeights = [1 => 2, 2 => 3, 3 => 10, 4 => 30, 5 => 55];
            $rand = mt_rand(1, 100);
            $cumulative = 0;
            $rating = 5;
            foreach ($ratingWeights as $r => $w) {
                $cumulative += $w;
                if ($rand <= $cumulative) {
                    $rating = $r;
                    break;
                }
            }
            $texts = $reviewTexts[$rating];
            $text  = $texts[array_rand($texts)];
            $stmtReview->execute([$do['order_id'], $do['customer_id'], $do['kitchen_id'], $rating, $text]);
            $reviewCount++;
        }
    }
    echo "✅ $reviewCount Reviews seeded.\n";

    // ─── STEP 10: Seed Tiffin Subscriptions ──────────────────────────────────
    $tiffinPlans = [
        ['Weekly Veg Tiffin',    'weekly',  799,  'Healthy home-cooked veg meals delivered daily. Includes roti, sabji, dal & rice.', 2],
        ['Monthly Veg Tiffin',  'monthly', 2999,  'Monthly subscription with full balanced veg meals every day.', 2],
        ['Premium Tiffin Plan', 'monthly', 4499,  'Premium 3-meal daily plan with dessert and beverage included.', 3],
    ];

    $stmtTiffin = $db->prepare("INSERT INTO tiffin_subscriptions (kitchen_id, plan_name, plan_type, price, description, meals_per_day, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)");
    $tiffinIds  = [];
    foreach ($tiffinPlans as $tp) {
        $stmtTiffin->execute([$kitchenId, $tp[0], $tp[1], $tp[2], $tp[3], $tp[4]]);
        $tiffinIds[] = (int) $db->lastInsertId();
    }
    echo "✅ " . count($tiffinIds) . " Tiffin Subscription Plans seeded.\n";

    // ─── STEP 11: Seed Customer Subscriptions ────────────────────────────────
    $stmtCustSub = $db->prepare("INSERT INTO customer_subscriptions 
        (customer_id, subscription_plan_id, kitchen_id, address_id, start_date, end_date, status, payment_method, payment_status, total_paid) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $subStatuses = ['active', 'active', 'active', 'cancelled', 'completed'];
    $subCount = 0;
    foreach ($customers as $idx => $cId) {
        if ($idx >= count($tiffinIds)) break;
        $planId  = $tiffinIds[$idx % count($tiffinIds)];
        $aId     = $addressIds[$cId] ?? $addressIds[array_values($customers)[0]];
        $planRow = $db->query("SELECT plan_type, price FROM tiffin_subscriptions WHERE id = $planId")->fetch(PDO::FETCH_ASSOC);
        $days    = $planRow['plan_type'] === 'weekly' ? 7 : 30;
        $start   = date('Y-m-d', strtotime('-' . mt_rand(0, 10) . ' days'));
        $end     = date('Y-m-d', strtotime("$start +$days days"));
        $status  = $subStatuses[array_rand($subStatuses)];
        $stmtCustSub->execute([$cId, $planId, $kitchenId, $aId, $start, $end, $status, 'upi', 'completed', $planRow['price']]);
        $subCount++;
    }
    echo "✅ $subCount Customer Subscriptions seeded.\n";

    // ─── STEP 12: Seed Zero Waste Items ──────────────────────────────────────
    $zeroWasteItems = [
        'Paneer Butter Masala',
        'Dal Makhani',
        'Masala Dosa',
        'Gulab Jamun',
        'Samosa (2 pcs)',
    ];

    $stmtZero = $db->prepare("INSERT INTO zero_waste_items 
        (menu_item_id, kitchen_id, original_price, discounted_price, quantity_available, expiry_time, status) 
        VALUES (?, ?, ?, ?, ?, ?, 'active')");

    $zeroCount = 0;
    foreach ($zeroWasteItems as $itemName) {
        if (!isset($menuItemIds[$itemName])) continue;
        $miId    = $menuItemIds[$itemName];
        $origPrc = (float) $db->query("SELECT price FROM menu_items WHERE id = $miId")->fetchColumn();
        $disc    = round($origPrc * 0.55, 2); // 45% off
        $qty     = mt_rand(2, 8);
        $expiry  = date('Y-m-d H:i:s', strtotime('+4 hours'));
        $stmtZero->execute([$miId, $kitchenId, $origPrc, $disc, $qty, $expiry]);
        $zeroCount++;
    }
    echo "✅ $zeroCount Zero Waste Items seeded.\n";

    // ─── STEP 13: Refresh AI Recommendations ─────────────────────────────────
    // Update preference profile for all customers involved
    $prefTable = $db->query("SHOW TABLES LIKE 'customer_preferences'")->fetchColumn();
    if ($prefTable) {
        foreach ($customers as $cId) {
            $db->query("INSERT INTO customer_preferences (customer_id, updated_at) VALUES ($cId, NOW())
                ON DUPLICATE KEY UPDATE updated_at = NOW()");
        }
    }

    // ─── Final Summary ────────────────────────────────────────────────────────
    echo "\n";
    echo "╔═══════════════════════════════════════════════════════╗\n";
    echo "║        🎉 DASHBOARD SEEDING COMPLETE!                 ║\n";
    echo "╚═══════════════════════════════════════════════════════╝\n\n";

    // Show what values will now show on dashboard
    $totalOrders   = $db->query("SELECT COUNT(*) FROM orders WHERE kitchen_id = $kitchenId")->fetchColumn();
    $totalEarnings = $db->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE kitchen_id = $kitchenId AND order_status = 'delivered'")->fetchColumn();
    $menuCount     = $db->query("SELECT COUNT(*) FROM menu_items WHERE kitchen_id = $kitchenId")->fetchColumn();
    $avgRating     = $db->query("SELECT ROUND(AVG(rating), 1) FROM reviews WHERE kitchen_id = $kitchenId")->fetchColumn();
    $reviewCnt     = $db->query("SELECT COUNT(*) FROM reviews WHERE kitchen_id = $kitchenId")->fetchColumn();
    $thisMonth     = $db->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE kitchen_id = $kitchenId AND order_status='delivered' AND MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())")->fetchColumn();

    echo "  📦 Total Orders    : $totalOrders\n";
    echo "  💰 Total Earnings  : ₹" . number_format((float)$totalEarnings, 2) . "\n";
    echo "  🍽️  Menu Items      : $menuCount\n";
    echo "  ⭐ Avg Rating      : $avgRating / 5 ($reviewCnt reviews)\n";
    echo "  📅 This Month Rev  : ₹" . number_format((float)$thisMonth, 2) . "\n";
    echo "  🟢 Kitchen Status  : APPROVED & OPEN\n";
    echo "\n  ✅ Refresh the Chef Dashboard — all values should now be filled!\n\n";

} catch (Exception $e) {
    echo "\n❌ Seeder Error: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
