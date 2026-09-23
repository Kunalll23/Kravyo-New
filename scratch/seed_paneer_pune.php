<?php
define('CONFIG_PATH', 'c:/xampp/htdocs/Kravyo/config');
define('APP_ENV', 'development');
require 'c:/xampp/htdocs/Kravyo/core/Database.php';

$db = Database::getInstance();

try {
    $db->beginTransaction();

    // 1. Ensure category "Main Course" exists
    $stmt = $db->query("SELECT id FROM categories WHERE category_name = 'Main Course'");
    $category = $stmt->fetch();
    if (!$category) {
        $stmt = $db->prepare("INSERT INTO categories (category_name) VALUES ('Main Course')");
        $stmt->execute();
        $categoryId = $db->lastInsertId();
    } else {
        $categoryId = $category['id'];
    }

    // 2. Ensure a user and kitchen in "Pune" exist
    $stmt = $db->query("SELECT id FROM kitchens WHERE city = 'Pune' AND approval_status = 'approved' LIMIT 1");
    $kitchen = $stmt->fetch();
    if (!$kitchen) {
        // Create user
        $stmt = $db->prepare("INSERT INTO users (full_name, email, phone, password_hash, role) VALUES ('Pune Chef', 'punechef@example.com', '9999999999', 'hash', 'chef')");
        $stmt->execute();
        $userId = $db->lastInsertId();

        // Create kitchen
        $stmt = $db->prepare("INSERT INTO kitchens (user_id, kitchen_name, address, city, pincode, approval_status, is_open) VALUES (?, 'Pune Delights', '123 Pune St', 'Pune', '411001', 'approved', 1)");
        $stmt->execute([$userId]);
        $kitchenId = $db->lastInsertId();
    } else {
        $kitchenId = $kitchen['id'];
    }

    // 3. Insert 4 Paneer dishes
    $dishes = [
        ['item_name' => 'Paneer Butter Masala', 'desc' => 'Rich and creamy paneer curry.', 'price' => 250],
        ['item_name' => 'Kadai Paneer', 'desc' => 'Spicy and flavorful paneer with bell peppers.', 'price' => 220],
        ['item_name' => 'Palak Paneer', 'desc' => 'Healthy spinach gravy with soft paneer cubes.', 'price' => 200],
        ['item_name' => 'Shahi Paneer', 'desc' => 'Royal paneer preparation in a sweet and spicy gravy.', 'price' => 260]
    ];

    $stmt = $db->prepare("INSERT INTO menu_items (kitchen_id, category_id, item_name, description, price, is_veg, is_available) VALUES (?, ?, ?, ?, ?, 1, 1)");

    foreach ($dishes as $dish) {
        // Check if dish exists to prevent duplicates on multiple runs
        $check = $db->prepare("SELECT id FROM menu_items WHERE kitchen_id = ? AND item_name = ?");
        $check->execute([$kitchenId, $dish['item_name']]);
        if (!$check->fetch()) {
            $stmt->execute([
                $kitchenId,
                $categoryId,
                $dish['item_name'],
                $dish['desc'],
                $dish['price']
            ]);
        }
    }

    $db->commit();
    echo "Successfully seeded 4 Paneer dishes in Pune under Main Course!\n";

} catch (Exception $e) {
    $db->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
