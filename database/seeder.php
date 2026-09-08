<?php
/**
 * Kravyo — Database Seeder for Testing Phases 2-10
 * Run this script via command line: php database/seeder.php
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';

try {
    $db = Database::getInstance();
    echo "Connected to database.\n";

    // 1. Fetch existing data for relationships
    $usersQuery = $db->query("SELECT id, role FROM users");
    $users = $usersQuery->fetchAll(PDO::FETCH_ASSOC);
    
    $customers = array_filter($users, fn($u) => $u['role'] === 'customer');
    $customerIds = array_column($customers, 'id');
    
    $kitchensQuery = $db->query("SELECT id, user_id FROM kitchens");
    $kitchens = $kitchensQuery->fetchAll(PDO::FETCH_ASSOC);
    $kitchenIds = array_column($kitchens, 'id');

    if (empty($customerIds) || empty($kitchenIds)) {
        die("Error: Not enough users or kitchens to seed relational data. Please register some first.\n");
    }

    echo "Found " . count($customerIds) . " customers and " . count($kitchenIds) . " kitchens.\n";

    // 2. Seed Categories (Phase 4, 10)
    $categories = [
        ['category_name' => 'North Indian', 'description' => 'Authentic North Indian curries and breads.', 'image' => 'placeholder_north.jpg'],
        ['category_name' => 'South Indian', 'description' => 'Delicious dosas, idlis, and traditional meals.', 'image' => 'placeholder_south.jpg'],
        ['category_name' => 'Healthy Salads', 'description' => 'Fresh and organic salads for a healthy lifestyle.', 'image' => 'placeholder_salad.jpg'],
        ['category_name' => 'Desserts', 'description' => 'Homemade sweets and treats.', 'image' => 'placeholder_dessert.jpg'],
        ['category_name' => 'Snacks & Bites', 'description' => 'Evening snacks and quick bites.', 'image' => 'placeholder_snacks.jpg'],
        ['category_name' => 'Beverages', 'description' => 'Fresh juices, lassis, and buttermilk.', 'image' => 'placeholder_beverages.jpg'],
    ];

    $catIds = [];
    $stmtCat = $db->prepare("INSERT IGNORE INTO categories (category_name, description, image) VALUES (:category_name, :description, :image)");
    foreach ($categories as $cat) {
        $stmtCat->execute($cat);
        // We will fetch all category IDs later
    }
    
    $catQuery = $db->query("SELECT id FROM categories");
    $catIds = array_column($catQuery->fetchAll(PDO::FETCH_ASSOC), 'id');
    echo "Categories seeded.\n";

    // 3. Seed Addresses (Phase 6, 7)
    $stmtAddr = $db->prepare("INSERT INTO addresses (user_id, address_type, street_address, landmark, city, pincode) VALUES (?, ?, ?, ?, ?, ?)");
    $addressTypes = ['Home', 'Work', 'Other'];
    $cities = ['Mumbai', 'Delhi', 'Bangalore', 'Pune', 'Hyderabad'];
    
    $addressIds = [];
    for ($i = 0; $i < 20; $i++) {
        $cId = $customerIds[array_rand($customerIds)];
        $type = $addressTypes[array_rand($addressTypes)];
        $city = $cities[array_rand($cities)];
        $pin = mt_rand(111111, 999999);
        $stmtAddr->execute([$cId, $type, "Flat " . mt_rand(1, 100) . ", Building " . mt_rand(1, 20), "Near landmark " . mt_rand(1,5), $city, $pin]);
        $addressIds[] = $db->lastInsertId();
    }
    echo "Addresses seeded.\n";

    // 4. Seed Menu Items (Phase 4, 6, 8, 9, 10)
    $stmtMenu = $db->prepare("INSERT INTO menu_items (kitchen_id, category_id, item_name, description, price, image, is_veg, is_jain_available, is_diabetic_friendly, is_available) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $foodNames = ['Paneer Butter Masala', 'Masala Dosa', 'Quinoa Salad', 'Gulab Jamun', 'Samosa', 'Mango Lassi', 'Chicken Curry', 'Fish Fry', 'Dal Makhani', 'Vegetable Biryani', 'Roti', 'Idli Sambar'];
    $menuItemIds = [];

    for ($i = 0; $i < 40; $i++) {
        $kId = $kitchenIds[array_rand($kitchenIds)];
        $cId = $catIds[array_rand($catIds)];
        $name = $foodNames[array_rand($foodNames)] . " Variant " . mt_rand(1, 5);
        $price = mt_rand(50, 350);
        $isVeg = mt_rand(0, 10) > 2 ? 1 : 0; // 80% veg
        $isJain = $isVeg ? mt_rand(0, 1) : 0;
        $isDiabetic = mt_rand(0, 10) > 8 ? 1 : 0;
        
        $stmtMenu->execute([$kId, $cId, $name, "Delicious $name", $price, "item_$i.jpg", $isVeg, $isJain, $isDiabetic, 1]);
        $menuItemIds[] = $db->lastInsertId();
    }
    echo "Menu Items seeded.\n";

    // 5. Seed Tiffin Subscriptions (Phase 7)
    $stmtTiffin = $db->prepare("INSERT INTO tiffin_subscriptions (kitchen_id, plan_name, plan_type, price, description, meals_per_day, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $tiffinIds = [];
    
    for ($i = 0; $i < 10; $i++) {
        $kId = $kitchenIds[array_rand($kitchenIds)];
        $type = mt_rand(0, 1) ? 'weekly' : 'monthly';
        $meals = mt_rand(1, 2);
        $price = ($type === 'weekly') ? mt_rand(500, 1500) : mt_rand(2000, 5000);
        
        $stmtTiffin->execute([$kId, ucfirst($type) . " $meals-Meal Plan", $type, $price, "Enjoy home-cooked meals every day.", $meals, 1]);
        $tiffinIds[] = $db->lastInsertId();
    }
    echo "Tiffin Subscriptions seeded.\n";

    // 6. Seed Zero Waste Items (Phase 8)
    $stmtZero = $db->prepare("INSERT INTO zero_waste_items (menu_item_id, kitchen_id, original_price, discounted_price, quantity_available, expiry_time, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    for ($i = 0; $i < 10; $i++) {
        $mId = $menuItemIds[array_rand($menuItemIds)];
        
        // Find kitchen for this menu item
        $kmQuery = $db->query("SELECT kitchen_id, price FROM menu_items WHERE id = $mId");
        $miData = $kmQuery->fetch(PDO::FETCH_ASSOC);
        $kId = $miData['kitchen_id'];
        $origPrice = $miData['price'];
        
        $discPrice = round($origPrice * (mt_rand(40, 80) / 100), 2);
        $qty = mt_rand(0, 5);
        $status = $qty > 0 ? 'active' : 'sold_out';
        $expiry = date('Y-m-d H:i:s', strtotime('+' . mt_rand(1, 5) . ' hours'));
        
        $stmtZero->execute([$mId, $kId, $origPrice, $discPrice, $qty, $expiry, $status]);
    }
    echo "Zero Waste Items seeded.\n";

    // 7. Seed Orders and Order Items (Phase 6, 9, 10)
    $stmtOrder = $db->prepare("INSERT INTO orders (order_number, customer_id, kitchen_id, address_id, total_amount, payment_method, payment_status, order_status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmtOrderItem = $db->prepare("INSERT INTO order_items (order_id, menu_item_id, quantity, unit_price, subtotal, spice_level, oil_level, is_jain) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    $orderStatuses = ['pending', 'accepted', 'preparing', 'out_for_delivery', 'delivered', 'cancelled'];
    $payMethods = ['cod', 'upi', 'card'];
    
    $orderIds = [];
    $deliveredOrders = [];

    for ($i = 0; $i < 50; $i++) {
        $cId = $customerIds[array_rand($customerIds)];
        $kId = $kitchenIds[array_rand($kitchenIds)];
        
        // Find an address for this customer, or pick random if none exists (just for seeding)
        $addQuery = $db->query("SELECT id FROM addresses WHERE user_id = $cId LIMIT 1");
        $addIdRow = $addQuery->fetch(PDO::FETCH_ASSOC);
        $aId = $addIdRow ? $addIdRow['id'] : $addressIds[array_rand($addressIds)];
        
        $orderNum = "ORD" . date('Ymd') . mt_rand(1000, 9999) . $i;
        $status = $orderStatuses[array_rand($orderStatuses)];
        $payMethod = $payMethods[array_rand($payMethods)];
        
        // Random past date for analytics
        $daysAgo = mt_rand(0, 30);
        $createdAt = date('Y-m-d H:i:s', strtotime("-$daysAgo days"));

        $stmtOrder->execute([$orderNum, $cId, $kId, $aId, 0, $payMethod, 'completed', $status, $createdAt]);
        $orderId = $db->lastInsertId();
        $orderIds[] = $orderId;
        
        if ($status === 'delivered') {
            $deliveredOrders[] = ['order_id' => $orderId, 'customer_id' => $cId, 'kitchen_id' => $kId];
        }

        // Add 1 to 4 items per order
        $numItems = mt_rand(1, 4);
        $totalAmount = 0;
        
        // Get menu items for this kitchen
        $kMenuItemsQuery = $db->query("SELECT id, price FROM menu_items WHERE kitchen_id = $kId");
        $kMenuItems = $kMenuItemsQuery->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($kMenuItems)) continue; // Skip items if kitchen has no menu

        for ($j = 0; $j < $numItems; $j++) {
            $item = $kMenuItems[array_rand($kMenuItems)];
            $qty = mt_rand(1, 3);
            $subtotal = $item['price'] * $qty;
            $totalAmount += $subtotal;
            
            $spice = ['Low', 'Medium', 'High'][array_rand(['Low', 'Medium', 'High'])];
            $oil = ['Normal', 'Less Oil'][array_rand(['Normal', 'Less Oil'])];
            
            $stmtOrderItem->execute([$orderId, $item['id'], $qty, $item['price'], $subtotal, $spice, $oil, 0]);
        }
        
        // Update order total
        $db->query("UPDATE orders SET total_amount = $totalAmount WHERE id = $orderId");
    }
    echo "Orders & Order Items seeded.\n";

    // 8. Seed Reviews (Phase 9) - only for delivered orders
    $stmtReview = $db->prepare("INSERT INTO reviews (order_id, customer_id, kitchen_id, rating, review_text) VALUES (?, ?, ?, ?, ?)");
    
    $reviewTexts = [
        1 => ["Terrible food, arrived late.", "Not edible, too salty."],
        2 => ["Below average.", "Food was cold and portion was small."],
        3 => ["It was okay. Nothing special.", "Average taste, but good packaging."],
        4 => ["Very good food! Enjoyed it.", "Authentic taste, just slightly spicy."],
        5 => ["Absolutely amazing! Best home food.", "Tastes exactly like my mom's cooking. Highly recommended!"],
    ];

    foreach ($deliveredOrders as $do) {
        // 50% chance to leave a review
        if (mt_rand(0, 1) === 1) {
            $rating = mt_rand(1, 5);
            $texts = $reviewTexts[$rating];
            $text = $texts[array_rand($texts)];
            $stmtReview->execute([$do['order_id'], $do['customer_id'], $do['kitchen_id'], $rating, $text]);
        }
    }
    echo "Reviews seeded.\n";

    // 9. Seed Customer Subscriptions (Phase 7)
    $stmtCustSub = $db->prepare("INSERT INTO customer_subscriptions (customer_id, subscription_plan_id, kitchen_id, address_id, start_date, end_date, status, payment_method, payment_status, total_paid) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $subStatuses = ['active', 'paused', 'cancelled', 'completed'];

    for ($i = 0; $i < 15; $i++) {
        $cId = $customerIds[array_rand($customerIds)];
        $planId = $tiffinIds[array_rand($tiffinIds)];
        
        // Get plan info
        $pq = $db->query("SELECT kitchen_id, price, plan_type FROM tiffin_subscriptions WHERE id = $planId");
        $plan = $pq->fetch(PDO::FETCH_ASSOC);
        $kId = $plan['kitchen_id'];
        
        // Get address
        $addQuery = $db->query("SELECT id FROM addresses WHERE user_id = $cId LIMIT 1");
        $addIdRow = $addQuery->fetch(PDO::FETCH_ASSOC);
        $aId = $addIdRow ? $addIdRow['id'] : $addressIds[array_rand($addressIds)];

        $startDate = date('Y-m-d', strtotime('-' . mt_rand(0, 30) . ' days'));
        $days = $plan['plan_type'] === 'weekly' ? 7 : 30;
        $endDate = date('Y-m-d', strtotime($startDate . " + $days days"));
        
        $status = $subStatuses[array_rand($subStatuses)];

        $stmtCustSub->execute([$cId, $planId, $kId, $aId, $startDate, $endDate, $status, 'upi', 'completed', $plan['price']]);
    }
    echo "Customer Subscriptions seeded.\n";

    echo "\n=== Database Seeding Completed Successfully! ===\n";

} catch (Exception $e) {
    echo "Database Seeder Error: " . $e->getMessage() . "\n";
}
