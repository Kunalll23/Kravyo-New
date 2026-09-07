<?php
/**
 * Kravyo - Chef Controller (Kitchen Profile & Dashboard Management)
 */

require_once APP_PATH . '/models/Kitchen.php';
require_once APP_PATH . '/models/User.php';
require_once APP_PATH . '/models/MenuItem.php';
require_once APP_PATH . '/models/Category.php';

class ChefController extends Controller {

    private Kitchen $kitchenModel;
    private MenuItem $menuItemModel;
    private Category $categoryModel;

    public function __construct() {
        Middleware::role(ROLE_CHEF);
        $this->kitchenModel = new Kitchen();
        $this->menuItemModel = new MenuItem();
        $this->categoryModel = new Category();
    }

    /**
     * Chef Dashboard — Overview of kitchen status & quick stats
     */
    public function dashboard(): void {
        $userId = Session::get('user_id');
        $kitchen = $this->kitchenModel->findByUserId($userId);

        // Get real menu item count for the dashboard stat
        $menuItemCount = 0;
        if ($kitchen) {
            $menuItemCount = $this->menuItemModel->countByKitchenId($kitchen['id']);
        }

        $this->render('chef/dashboard', [
            'title' => 'Chef Dashboard',
            'kitchen' => $kitchen,
            'menuItemCount' => $menuItemCount
        ]);
    }

    /**
     * Show Kitchen Profile Edit Form
     */
    public function profile(): void {
        $userId = Session::get('user_id');
        $kitchen = $this->kitchenModel->findByUserId($userId);

        $this->render('chef/profile', [
            'title' => 'Edit Kitchen Profile',
            'kitchen' => $kitchen
        ]);
    }

    /**
     * Process Kitchen Profile Update (handles text data + file uploads)
     */
    public function updateProfile(): void {
        Middleware::verifyCsrf();

        $userId = Session::get('user_id');
        $kitchen = $this->kitchenModel->findByUserId($userId);

        if (!$kitchen) {
            Session::setFlash('danger', 'Kitchen profile not found.');
            $this->redirect('/chef/dashboard');
        }

        // Sanitize text inputs
        $data = [
            'kitchen_name'    => sanitize($_POST['kitchen_name'] ?? ''),
            'personal_story'  => sanitize($_POST['personal_story'] ?? ''),
            'address'         => sanitize($_POST['address'] ?? ''),
            'city'            => sanitize($_POST['city'] ?? ''),
            'pincode'         => sanitize($_POST['pincode'] ?? ''),
            'fssai_license'   => sanitize($_POST['fssai_license'] ?? ''),
        ];

        // Validate required fields
        if (empty($data['kitchen_name']) || empty($data['address']) || empty($data['city']) || empty($data['pincode'])) {
            Session::setFlash('danger', 'Kitchen name, address, city, and pincode are required.');
            $this->redirect('/chef/profile');
        }

        // Ensure upload directory exists
        $uploadDir = PUBLIC_PATH . '/uploads/kitchens/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Handle Banner Image Upload
        if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === UPLOAD_ERR_OK) {
            $bannerResult = $this->handleImageUpload($_FILES['banner_image'], $uploadDir, 'banner_');
            if ($bannerResult['success']) {
                // Delete old banner if exists
                if (!empty($kitchen['banner_image'])) {
                    $oldPath = PUBLIC_PATH . '/uploads/kitchens/' . $kitchen['banner_image'];
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }
                $data['banner_image'] = $bannerResult['filename'];
            } else {
                Session::setFlash('warning', $bannerResult['error']);
            }
        }

        // Handle Hygiene Certificate Upload
        if (isset($_FILES['hygiene_certificate_image']) && $_FILES['hygiene_certificate_image']['error'] === UPLOAD_ERR_OK) {
            $certResult = $this->handleImageUpload($_FILES['hygiene_certificate_image'], $uploadDir, 'cert_');
            if ($certResult['success']) {
                // Delete old certificate if exists
                if (!empty($kitchen['hygiene_certificate_image'])) {
                    $oldPath = PUBLIC_PATH . '/uploads/kitchens/' . $kitchen['hygiene_certificate_image'];
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }
                $data['hygiene_certificate_image'] = $certResult['filename'];
            } else {
                Session::setFlash('warning', $certResult['error']);
            }
        }

        // Update kitchen record
        try {
            $this->kitchenModel->update($kitchen['id'], $data);
            Session::setFlash('success', 'Kitchen profile updated successfully!');
        } catch (Exception $e) {
            Session::setFlash('danger', 'Failed to update profile. Please try again.');
        }

        $this->redirect('/chef/profile');
    }

    /**
     * Toggle Kitchen Availability (Open/Closed) — AJAX or POST
     */
    public function toggleAvailability(): void {
        Middleware::verifyCsrf();

        $userId = Session::get('user_id');
        $kitchen = $this->kitchenModel->findByUserId($userId);

        if (!$kitchen) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Kitchen not found.'], 404);
            }
            Session::setFlash('danger', 'Kitchen not found.');
            $this->redirect('/chef/dashboard');
        }

        // Toggle: if currently open (1), set to closed (0), and vice versa
        $newStatus = $kitchen['is_open'] ? 0 : 1;
        $this->kitchenModel->toggleAvailability($kitchen['id'], $newStatus);

        if ($this->isAjax()) {
            $this->json([
                'success' => true,
                'is_open' => $newStatus,
                'message' => $newStatus ? 'Kitchen is now OPEN for orders!' : 'Kitchen is now CLOSED.'
            ]);
        }

        Session::setFlash('success', $newStatus ? 'Kitchen is now OPEN for orders!' : 'Kitchen is now CLOSED.');
        $this->redirect('/chef/dashboard');
    }

    /**
     * Handle image file upload with validation
     */
    private function handleImageUpload(array $file, string $uploadDir, string $prefix = ''): array {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5 MB

        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'error' => 'Only JPG, PNG, WebP, and GIF images are allowed.'];
        }

        if ($file['size'] > $maxSize) {
            return ['success' => false, 'error' => 'Image file size must be under 5 MB.'];
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $prefix . uniqid() . '_' . time() . '.' . strtolower($extension);
        $destination = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return ['success' => true, 'filename' => $filename];
        }

        return ['success' => false, 'error' => 'Failed to upload image. Please try again.'];
    }

    /**
     * Check if current request is AJAX
     */
    private function isAjax(): bool {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) 
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Manage Menu — List all chef's dishes with Add Dish form
     */
    public function manageMenu(): void {
        $userId = Session::get('user_id');
        $kitchen = $this->kitchenModel->findByUserId($userId);

        if (!$kitchen) {
            Session::setFlash('danger', 'Kitchen profile not found.');
            $this->redirect('/chef/dashboard');
        }

        $dishes = $this->menuItemModel->findByKitchenId($kitchen['id']);
        $categories = $this->categoryModel->getAllOrdered();

        $this->render('chef/menu', [
            'title'      => 'Manage Menu',
            'kitchen'    => $kitchen,
            'dishes'     => $dishes,
            'categories' => $categories
        ]);
    }

    /**
     * Add new dish to chef's menu
     */
    public function addDish(): void {
        Middleware::verifyCsrf();

        $userId = Session::get('user_id');
        $kitchen = $this->kitchenModel->findByUserId($userId);

        if (!$kitchen) {
            Session::setFlash('danger', 'Kitchen not found.');
            $this->redirect('/chef/dashboard');
        }

        $data = [
            'kitchen_id'          => $kitchen['id'],
            'category_id'         => (int) ($_POST['category_id'] ?? 0),
            'item_name'           => sanitize($_POST['item_name'] ?? ''),
            'description'         => sanitize($_POST['description'] ?? ''),
            'price'               => (float) ($_POST['price'] ?? 0),
            'is_veg'              => isset($_POST['is_veg']) ? 1 : 0,
            'is_jain_available'   => isset($_POST['is_jain_available']) ? 1 : 0,
            'is_diabetic_friendly'=> isset($_POST['is_diabetic_friendly']) ? 1 : 0,
            'is_available'        => 1,
        ];

        // Validation
        if (empty($data['item_name']) || $data['category_id'] <= 0 || $data['price'] <= 0) {
            Session::setFlash('danger', 'Dish name, category, and price are required.');
            $this->redirect('/chef/menu');
        }

        // Handle dish image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = PUBLIC_PATH . '/uploads/dishes/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $result = $this->handleImageUpload($_FILES['image'], $uploadDir, 'dish_');
            if ($result['success']) {
                $data['image'] = $result['filename'];
            } else {
                Session::setFlash('warning', $result['error']);
            }
        }

        try {
            $this->menuItemModel->create($data);
            Session::setFlash('success', 'Dish "' . $data['item_name'] . '" added to your menu!');
        } catch (Exception $e) {
            Session::setFlash('danger', 'Failed to add dish. Please try again.');
        }

        $this->redirect('/chef/menu');
    }

    /**
     * Edit existing dish
     */
    public function editDish(string $id): void {
        Middleware::verifyCsrf();

        $dishId = (int) $id;
        $userId = Session::get('user_id');
        $kitchen = $this->kitchenModel->findByUserId($userId);

        if (!$kitchen || !$this->menuItemModel->belongsToKitchen($dishId, $kitchen['id'])) {
            Session::setFlash('danger', 'Dish not found or access denied.');
            $this->redirect('/chef/menu');
        }

        $existingDish = $this->menuItemModel->find($dishId);

        $data = [
            'category_id'         => (int) ($_POST['category_id'] ?? $existingDish['category_id']),
            'item_name'           => sanitize($_POST['item_name'] ?? ''),
            'description'         => sanitize($_POST['description'] ?? ''),
            'price'               => (float) ($_POST['price'] ?? 0),
            'is_veg'              => isset($_POST['is_veg']) ? 1 : 0,
            'is_jain_available'   => isset($_POST['is_jain_available']) ? 1 : 0,
            'is_diabetic_friendly'=> isset($_POST['is_diabetic_friendly']) ? 1 : 0,
            'is_available'        => isset($_POST['is_available']) ? 1 : 0,
        ];

        if (empty($data['item_name']) || $data['price'] <= 0) {
            Session::setFlash('danger', 'Dish name and price are required.');
            $this->redirect('/chef/menu');
        }

        // Handle dish image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = PUBLIC_PATH . '/uploads/dishes/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $result = $this->handleImageUpload($_FILES['image'], $uploadDir, 'dish_');
            if ($result['success']) {
                // Delete old image
                if (!empty($existingDish['image'])) {
                    $oldPath = $uploadDir . $existingDish['image'];
                    if (file_exists($oldPath)) unlink($oldPath);
                }
                $data['image'] = $result['filename'];
            }
        }

        try {
            $this->menuItemModel->update($dishId, $data);
            Session::setFlash('success', 'Dish updated successfully!');
        } catch (Exception $e) {
            Session::setFlash('danger', 'Failed to update dish.');
        }

        $this->redirect('/chef/menu');
    }

    /**
     * Delete dish from menu
     */
    public function deleteDish(string $id): void {
        Middleware::verifyCsrf();

        $dishId = (int) $id;
        $userId = Session::get('user_id');
        $kitchen = $this->kitchenModel->findByUserId($userId);

        if (!$kitchen || !$this->menuItemModel->belongsToKitchen($dishId, $kitchen['id'])) {
            Session::setFlash('danger', 'Dish not found or access denied.');
            $this->redirect('/chef/menu');
        }

        $dish = $this->menuItemModel->find($dishId);

        // Delete dish image if exists
        if ($dish && !empty($dish['image'])) {
            $imgPath = PUBLIC_PATH . '/uploads/dishes/' . $dish['image'];
            if (file_exists($imgPath)) unlink($imgPath);
        }

        $this->menuItemModel->delete($dishId);
        Session::setFlash('success', 'Dish removed from your menu.');
        $this->redirect('/chef/menu');
    }

    /**
     * Chef Incoming Orders Dashboard
     * GET /chef/orders
     */
    public function orders(): void {
        require_once APP_PATH . '/models/Order.php';
        require_once APP_PATH . '/models/OrderItem.php';

        $orderModel = new Order();
        $orderItemModel = new OrderItem();

        $userId = Session::get('user_id');
        $kitchen = $this->kitchenModel->findByUserId($userId);

        if (!$kitchen) {
            Session::setFlash('danger', 'Kitchen profile not found.');
            $this->redirect('/chef/dashboard');
            return;
        }

        $kitchenId = (int) $kitchen['id'];

        // Get filter from query string
        $statusFilter = $_GET['status'] ?? 'all';
        $validStatuses = ['all', 'pending', 'accepted', 'preparing', 'out_for_delivery', 'delivered', 'cancelled'];
        if (!in_array($statusFilter, $validStatuses)) {
            $statusFilter = 'all';
        }

        // Fetch orders based on filter
        $filterStatus = ($statusFilter === 'all') ? null : $statusFilter;
        $orders = $orderModel->findByKitchenId($kitchenId, $filterStatus);

        // Attach order items to each order
        foreach ($orders as &$order) {
            $order['items'] = $orderItemModel->findByOrderId((int) $order['id']);
        }
        unset($order);

        // Count orders by status for tab badges
        $statusCounts = [
            'all'              => $orderModel->countByKitchenId($kitchenId),
            'pending'          => $orderModel->countByKitchenId($kitchenId, ORDER_STATUS_PENDING),
            'accepted'         => $orderModel->countByKitchenId($kitchenId, ORDER_STATUS_ACCEPTED),
            'preparing'        => $orderModel->countByKitchenId($kitchenId, ORDER_STATUS_PREPARING),
            'out_for_delivery' => $orderModel->countByKitchenId($kitchenId, ORDER_STATUS_OUT_FOR_DELIVERY),
            'delivered'        => $orderModel->countByKitchenId($kitchenId, ORDER_STATUS_DELIVERED),
            'cancelled'        => $orderModel->countByKitchenId($kitchenId, ORDER_STATUS_CANCELLED),
        ];

        $this->render('chef/orders', [
            'title'        => 'Manage Orders',
            'kitchen'      => $kitchen,
            'orders'       => $orders,
            'statusFilter' => $statusFilter,
            'statusCounts' => $statusCounts,
        ]);
    }

    /**
     * Update order status (chef action: accept, prepare, dispatch, deliver, reject)
     * POST /chef/order/status
     */
    public function updateOrderStatus(): void {
        Middleware::verifyCsrf();

        require_once APP_PATH . '/models/Order.php';
        $orderModel = new Order();

        $userId = Session::get('user_id');
        $kitchen = $this->kitchenModel->findByUserId($userId);

        if (!$kitchen) {
            Session::setFlash('danger', 'Kitchen not found.');
            $this->redirect('/chef/dashboard');
            return;
        }

        $orderId = (int) ($_POST['order_id'] ?? 0);
        $newStatus = $_POST['new_status'] ?? '';

        // Security: ensure order belongs to this kitchen
        if (!$orderModel->belongsToKitchen($orderId, (int) $kitchen['id'])) {
            Session::setFlash('danger', 'Order not found or access denied.');
            $this->redirect('/chef/orders');
            return;
        }

        $order = $orderModel->find($orderId);
        if (!$order) {
            Session::setFlash('danger', 'Order not found.');
            $this->redirect('/chef/orders');
            return;
        }

        // Define valid status transitions
        $validTransitions = [
            ORDER_STATUS_PENDING          => [ORDER_STATUS_ACCEPTED, ORDER_STATUS_CANCELLED],
            ORDER_STATUS_ACCEPTED         => [ORDER_STATUS_PREPARING],
            ORDER_STATUS_PREPARING        => [ORDER_STATUS_OUT_FOR_DELIVERY],
            ORDER_STATUS_OUT_FOR_DELIVERY => [ORDER_STATUS_DELIVERED],
        ];

        $currentStatus = $order['order_status'];

        if (!isset($validTransitions[$currentStatus]) || !in_array($newStatus, $validTransitions[$currentStatus])) {
            Session::setFlash('danger', 'Invalid status transition.');
            $this->redirect('/chef/orders');
            return;
        }

        $orderModel->updateStatus($orderId, $newStatus);

        // If delivered, mark payment as completed for COD
        if ($newStatus === ORDER_STATUS_DELIVERED && $order['payment_method'] === PAYMENT_METHOD_COD) {
            $orderModel->updatePaymentStatus($orderId, PAYMENT_STATUS_COMPLETED);
        }

        $statusLabels = [
            ORDER_STATUS_ACCEPTED         => 'accepted',
            ORDER_STATUS_PREPARING        => 'marked as preparing',
            ORDER_STATUS_OUT_FOR_DELIVERY => 'dispatched for delivery',
            ORDER_STATUS_DELIVERED        => 'marked as delivered',
            ORDER_STATUS_CANCELLED        => 'rejected',
        ];

        $label = $statusLabels[$newStatus] ?? 'updated';
        Session::setFlash('success', 'Order #' . $order['order_number'] . ' has been ' . $label . '.');
        $this->redirect('/chef/orders');
    }

    /**
     * Manage Tiffin Subscription Plans + View Subscribers
     * GET /chef/subscriptions
     */
    public function subscriptions(): void {
        require_once APP_PATH . '/models/Subscription.php';
        require_once APP_PATH . '/models/CustomerSubscription.php';

        $subscriptionModel = new Subscription();
        $customerSubModel = new CustomerSubscription();

        $userId = Session::get('user_id');
        $kitchen = $this->kitchenModel->findByUserId($userId);

        if (!$kitchen) {
            Session::setFlash('danger', 'Kitchen profile not found.');
            $this->redirect('/chef/dashboard');
            return;
        }

        $kitchenId = (int) $kitchen['id'];
        $plans = $subscriptionModel->findByKitchenId($kitchenId);
        $activeSubscribers = $customerSubModel->findByKitchenId($kitchenId, SUBSCRIPTION_STATUS_ACTIVE);
        $subscriberCount = $customerSubModel->countActiveByKitchenId($kitchenId);

        $this->render('chef/subscriptions', [
            'title'             => 'Tiffin Subscription Plans',
            'kitchen'           => $kitchen,
            'plans'             => $plans,
            'activeSubscribers' => $activeSubscribers,
            'subscriberCount'   => $subscriberCount,
        ]);
    }

    /**
     * Create a new tiffin subscription plan
     * POST /chef/subscription/add
     */
    public function addSubscriptionPlan(): void {
        Middleware::verifyCsrf();
        require_once APP_PATH . '/models/Subscription.php';

        $subscriptionModel = new Subscription();
        $userId = Session::get('user_id');
        $kitchen = $this->kitchenModel->findByUserId($userId);

        if (!$kitchen) {
            Session::setFlash('danger', 'Kitchen not found.');
            $this->redirect('/chef/dashboard');
            return;
        }

        $data = [
            'kitchen_id'    => (int) $kitchen['id'],
            'plan_name'     => sanitize($_POST['plan_name'] ?? ''),
            'plan_type'     => $_POST['plan_type'] ?? 'weekly',
            'price'         => (float) ($_POST['price'] ?? 0),
            'description'   => sanitize($_POST['description'] ?? ''),
            'meals_per_day' => (int) ($_POST['meals_per_day'] ?? 1),
            'is_active'     => 1,
        ];

        // Validation
        if (empty($data['plan_name']) || $data['price'] <= 0) {
            Session::setFlash('danger', 'Plan name and price are required.');
            $this->redirect('/chef/subscriptions');
            return;
        }

        if (!in_array($data['plan_type'], ['weekly', 'monthly'])) {
            $data['plan_type'] = 'weekly';
        }

        if ($data['meals_per_day'] < 1 || $data['meals_per_day'] > 3) {
            $data['meals_per_day'] = 1;
        }

        try {
            $subscriptionModel->create($data);
            Session::setFlash('success', 'Tiffin plan "' . $data['plan_name'] . '" created successfully!');
        } catch (Exception $e) {
            Session::setFlash('danger', 'Failed to create subscription plan. Please try again.');
        }

        $this->redirect('/chef/subscriptions');
    }

    /**
     * Edit an existing tiffin subscription plan
     * POST /chef/subscription/edit/{id}
     */
    public function editSubscriptionPlan(string $id): void {
        Middleware::verifyCsrf();
        require_once APP_PATH . '/models/Subscription.php';

        $planId = (int) $id;
        $subscriptionModel = new Subscription();
        $userId = Session::get('user_id');
        $kitchen = $this->kitchenModel->findByUserId($userId);

        if (!$kitchen || !$subscriptionModel->belongsToKitchen($planId, (int) $kitchen['id'])) {
            Session::setFlash('danger', 'Plan not found or access denied.');
            $this->redirect('/chef/subscriptions');
            return;
        }

        $data = [
            'plan_name'     => sanitize($_POST['plan_name'] ?? ''),
            'plan_type'     => $_POST['plan_type'] ?? 'weekly',
            'price'         => (float) ($_POST['price'] ?? 0),
            'description'   => sanitize($_POST['description'] ?? ''),
            'meals_per_day' => (int) ($_POST['meals_per_day'] ?? 1),
        ];

        if (empty($data['plan_name']) || $data['price'] <= 0) {
            Session::setFlash('danger', 'Plan name and price are required.');
            $this->redirect('/chef/subscriptions');
            return;
        }

        if (!in_array($data['plan_type'], ['weekly', 'monthly'])) {
            $data['plan_type'] = 'weekly';
        }

        if ($data['meals_per_day'] < 1 || $data['meals_per_day'] > 3) {
            $data['meals_per_day'] = 1;
        }

        try {
            $subscriptionModel->update($planId, $data);
            Session::setFlash('success', 'Plan updated successfully!');
        } catch (Exception $e) {
            Session::setFlash('danger', 'Failed to update plan.');
        }

        $this->redirect('/chef/subscriptions');
    }

    /**
     * Delete a tiffin subscription plan
     * POST /chef/subscription/delete/{id}
     */
    public function deleteSubscriptionPlan(string $id): void {
        Middleware::verifyCsrf();
        require_once APP_PATH . '/models/Subscription.php';

        $planId = (int) $id;
        $subscriptionModel = new Subscription();
        $userId = Session::get('user_id');
        $kitchen = $this->kitchenModel->findByUserId($userId);

        if (!$kitchen || !$subscriptionModel->belongsToKitchen($planId, (int) $kitchen['id'])) {
            Session::setFlash('danger', 'Plan not found or access denied.');
            $this->redirect('/chef/subscriptions');
            return;
        }

        try {
            $subscriptionModel->delete($planId);
            Session::setFlash('success', 'Subscription plan deleted.');
        } catch (Exception $e) {
            Session::setFlash('danger', 'Cannot delete this plan. It may have active subscribers.');
        }

        $this->redirect('/chef/subscriptions');
    }

    /**
     * Toggle a subscription plan active/inactive
     * POST /chef/subscription/toggle/{id}
     */
    public function toggleSubscriptionPlan(string $id): void {
        Middleware::verifyCsrf();
        require_once APP_PATH . '/models/Subscription.php';

        $planId = (int) $id;
        $subscriptionModel = new Subscription();
        $userId = Session::get('user_id');
        $kitchen = $this->kitchenModel->findByUserId($userId);

        if (!$kitchen || !$subscriptionModel->belongsToKitchen($planId, (int) $kitchen['id'])) {
            Session::setFlash('danger', 'Plan not found or access denied.');
            $this->redirect('/chef/subscriptions');
            return;
        }

        $plan = $subscriptionModel->find($planId);
        if (!$plan) {
            Session::setFlash('danger', 'Plan not found.');
            $this->redirect('/chef/subscriptions');
            return;
        }

        $newStatus = $plan['is_active'] ? 0 : 1;
        $subscriptionModel->toggleActive($planId, $newStatus);

        $label = $newStatus ? 'activated' : 'deactivated';
        Session::setFlash('success', 'Plan "' . sanitize($plan['plan_name']) . '" has been ' . $label . '.');
        $this->redirect('/chef/subscriptions');
    }

    /**
     * Zero Waste Management Panel — list chef's own listings + add form
     * GET /chef/zero-waste
     */
    public function zeroWaste(): void {
        require_once APP_PATH . '/models/ZeroWasteItem.php';

        $userId  = Session::get('user_id');
        $kitchen = $this->kitchenModel->findByUserId($userId);

        if (!$kitchen) {
            Session::setFlash('danger', 'Kitchen profile not found.');
            $this->redirect('/chef/dashboard');
            return;
        }

        $zeroWasteModel = new ZeroWasteItem();

        // Auto-expire stale listings before display
        $zeroWasteModel->markExpired();

        $listings = $zeroWasteModel->findByKitchenId((int) $kitchen['id']);
        $stats    = $zeroWasteModel->getStatsByKitchenId((int) $kitchen['id']);

        // Get chef's own available menu items for the "Add Listing" form
        $menuItems = $this->menuItemModel->findByKitchenId((int) $kitchen['id']);
        $availableMenuItems = array_filter($menuItems, fn($item) => $item['is_available']);

        $this->render('chef/zero_waste', [
            'title'              => 'Zero Food Waste Listings',
            'kitchen'            => $kitchen,
            'listings'           => $listings,
            'stats'              => $stats,
            'availableMenuItems' => array_values($availableMenuItems),
        ]);
    }

    /**
     * Create a new zero waste listing
     * POST /chef/zero-waste/add
     */
    public function addZeroWasteItem(): void {
        Middleware::verifyCsrf();
        require_once APP_PATH . '/models/ZeroWasteItem.php';

        $userId  = Session::get('user_id');
        $kitchen = $this->kitchenModel->findByUserId($userId);

        if (!$kitchen) {
            Session::setFlash('danger', 'Kitchen not found.');
            $this->redirect('/chef/dashboard');
            return;
        }

        $menuItemId      = (int) ($_POST['menu_item_id'] ?? 0);
        $discountedPrice = (float) ($_POST['discounted_price'] ?? 0);
        $quantity        = (int) ($_POST['quantity_available'] ?? 1);
        $expiryTime      = sanitize($_POST['expiry_time'] ?? '');

        // Validate menu item belongs to this kitchen
        if ($menuItemId <= 0 || !$this->menuItemModel->belongsToKitchen($menuItemId, (int) $kitchen['id'])) {
            Session::setFlash('danger', 'Invalid menu item selected.');
            $this->redirect('/chef/zero-waste');
            return;
        }

        $menuItem = $this->menuItemModel->find($menuItemId);

        if (!$menuItem) {
            Session::setFlash('danger', 'Menu item not found.');
            $this->redirect('/chef/zero-waste');
            return;
        }

        // Validate price
        if ($discountedPrice <= 0 || $discountedPrice >= (float) $menuItem['price']) {
            Session::setFlash('danger', 'Discounted price must be greater than ₹0 and less than the original price (₹' . number_format($menuItem['price'], 2) . ').');
            $this->redirect('/chef/zero-waste');
            return;
        }

        // Validate quantity
        if ($quantity < 1 || $quantity > 50) {
            Session::setFlash('danger', 'Quantity must be between 1 and 50.');
            $this->redirect('/chef/zero-waste');
            return;
        }

        // Validate expiry time (must be in the future)
        if (empty($expiryTime) || strtotime($expiryTime) <= time()) {
            Session::setFlash('danger', 'Expiry time must be a future date and time.');
            $this->redirect('/chef/zero-waste');
            return;
        }

        $data = [
            'menu_item_id'       => $menuItemId,
            'kitchen_id'         => (int) $kitchen['id'],
            'original_price'     => (float) $menuItem['price'],
            'discounted_price'   => $discountedPrice,
            'quantity_available' => $quantity,
            'expiry_time'        => date('Y-m-d H:i:s', strtotime($expiryTime)),
            'status'             => ZERO_WASTE_ACTIVE,
        ];

        try {
            (new ZeroWasteItem())->createListing($data);
            Session::setFlash('success', 'Zero waste listing for "' . sanitize($menuItem['item_name']) . '" is now live!');
        } catch (Exception $e) {
            Session::setFlash('danger', 'Failed to create listing. Please try again.');
        }

        $this->redirect('/chef/zero-waste');
    }

    /**
     * Delete a zero waste listing
     * POST /chef/zero-waste/delete/{id}
     */
    public function deleteZeroWasteItem(string $id): void {
        Middleware::verifyCsrf();
        require_once APP_PATH . '/models/ZeroWasteItem.php';

        $listingId      = (int) $id;
        $userId         = Session::get('user_id');
        $kitchen        = $this->kitchenModel->findByUserId($userId);
        $zeroWasteModel = new ZeroWasteItem();

        if (!$kitchen || !$zeroWasteModel->belongsToKitchen($listingId, (int) $kitchen['id'])) {
            Session::setFlash('danger', 'Listing not found or access denied.');
            $this->redirect('/chef/zero-waste');
            return;
        }

        $zeroWasteModel->delete($listingId);
        Session::setFlash('success', 'Zero waste listing removed.');
        $this->redirect('/chef/zero-waste');
    }

    /**
     * Toggle a zero waste listing active/sold_out
     * POST /chef/zero-waste/toggle/{id}
     */
    public function toggleZeroWasteStatus(string $id): void {
        Middleware::verifyCsrf();
        require_once APP_PATH . '/models/ZeroWasteItem.php';

        $listingId      = (int) $id;
        $userId         = Session::get('user_id');
        $kitchen        = $this->kitchenModel->findByUserId($userId);
        $zeroWasteModel = new ZeroWasteItem();

        if (!$kitchen || !$zeroWasteModel->belongsToKitchen($listingId, (int) $kitchen['id'])) {
            Session::setFlash('danger', 'Listing not found or access denied.');
            $this->redirect('/chef/zero-waste');
            return;
        }

        $listing   = $zeroWasteModel->find($listingId);
        if (!$listing) {
            Session::setFlash('danger', 'Listing not found.');
            $this->redirect('/chef/zero-waste');
            return;
        }

        $newStatus = ($listing['status'] === ZERO_WASTE_ACTIVE) ? ZERO_WASTE_SOLD_OUT : ZERO_WASTE_ACTIVE;
        $zeroWasteModel->toggleStatus($listingId, $newStatus);

        $label = ($newStatus === ZERO_WASTE_ACTIVE) ? 'reactivated' : 'marked as sold out';
        Session::setFlash('success', 'Listing has been ' . $label . '.');
        $this->redirect('/chef/zero-waste');
    }
}
