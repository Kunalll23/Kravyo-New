<?php
/**
 * Kravyo - Admin Controller (Platform Administration & Kitchen Verification)
 */

require_once APP_PATH . '/models/Kitchen.php';
require_once APP_PATH . '/models/Admin.php';
require_once APP_PATH . '/models/User.php';
require_once APP_PATH . '/models/Category.php';

class AdminController extends Controller {

    private Kitchen $kitchenModel;
    private Admin $adminModel;
    private User $userModel;
    private Category $categoryModel;

    public function __construct() {
        Middleware::adminAuth();
        $this->kitchenModel  = new Kitchen();
        $this->adminModel    = new Admin();
        $this->userModel     = new User();
        $this->categoryModel = new Category();
    }

    /**
     * Admin Dashboard — Platform overview with key stats
     */
    public function dashboard(): void {
        require_once APP_PATH . '/models/Order.php';
        $orderModel = new Order();

        $revenue = $orderModel->getPlatformRevenue();
        $orderStats = $orderModel->getPlatformOrderStats();

        $stats = [
            'total_users'       => $this->countUsers(),
            'total_customers'   => $this->countUsers('customer'),
            'total_chefs'       => $this->countUsers('chef'),
            'pending_kitchens'  => $this->kitchenModel->countByStatus(KITCHEN_STATUS_PENDING),
            'approved_kitchens' => $this->kitchenModel->countByStatus(KITCHEN_STATUS_APPROVED),
            'total_kitchens'    => $this->kitchenModel->countAll(),
            'total_orders'      => array_sum($orderStats),
            'delivered_orders'  => $orderStats['delivered'] ?? 0,
            'platform_revenue'  => $revenue['total'],
            'revenue_this_month'=> $revenue['this_month'],
        ];

        $pendingKitchens = $this->kitchenModel->findPendingKitchens();

        $this->render('admin/dashboard', [
            'title'          => 'Admin Dashboard',
            'stats'          => $stats,
            'pendingKitchens'=> $pendingKitchens
        ]);
    }

    /**
     * Chef / Kitchen Verification Queue — list all kitchens with filter
     */
    public function chefs(): void {
        $filter = sanitize($_GET['status'] ?? 'all');

        if ($filter === 'all') {
            $kitchens = $this->kitchenModel->findAllWithChef();
        } else {
            $kitchens = $this->kitchenModel->findAllWithChef($filter);
        }

        $counts = [
            'all'      => $this->kitchenModel->countAll(),
            'pending'  => $this->kitchenModel->countByStatus(KITCHEN_STATUS_PENDING),
            'approved' => $this->kitchenModel->countByStatus(KITCHEN_STATUS_APPROVED),
            'rejected' => $this->kitchenModel->countByStatus(KITCHEN_STATUS_REJECTED),
        ];

        $this->render('admin/chefs', [
            'title'    => 'Kitchen Verification Queue',
            'kitchens' => $kitchens,
            'counts'   => $counts,
            'filter'   => $filter
        ]);
    }

    /**
     * Process Kitchen Approval or Rejection
     */
    public function verifyChef(): void {
        Middleware::verifyCsrf();

        $kitchenId = (int) ($_POST['kitchen_id'] ?? 0);
        $action = sanitize($_POST['action'] ?? '');
        $notes = sanitize($_POST['admin_notes'] ?? '');

        if ($kitchenId <= 0 || !in_array($action, ['approve', 'reject'])) {
            Session::setFlash('danger', 'Invalid verification request.');
            $this->redirect('/admin/chefs');
        }

        $kitchen = $this->kitchenModel->find($kitchenId);
        if (!$kitchen) {
            Session::setFlash('danger', 'Kitchen not found.');
            $this->redirect('/admin/chefs');
        }

        if ($action === 'approve') {
            $this->kitchenModel->updateApprovalStatus($kitchenId, KITCHEN_STATUS_APPROVED, $notes);

            // If FSSAI license is provided, auto-grant hygiene badge
            if (!empty($kitchen['fssai_license'])) {
                $this->kitchenModel->updateHygieneBadge($kitchenId, HYGIENE_BADGE_VERIFIED);
            }

            Session::setFlash('success', 'Kitchen "' . sanitize($kitchen['kitchen_name']) . '" has been APPROVED!');
        } else {
            if (empty($notes)) {
                Session::setFlash('danger', 'Please provide a reason for rejection.');
                $this->redirect('/admin/chefs');
            }

            $this->kitchenModel->updateApprovalStatus($kitchenId, KITCHEN_STATUS_REJECTED, $notes);
            Session::setFlash('warning', 'Kitchen "' . sanitize($kitchen['kitchen_name']) . '" has been REJECTED.');
        }

        $this->redirect('/admin/chefs');
    }

    /**
     * User Management — List all platform users
     */
    public function users(): void {
        $users = $this->userModel->findAll([], 'created_at DESC');

        $this->render('admin/users', [
            'title' => 'User Management',
            'users' => $users
        ]);
    }

    /**
     * Toggle user active/inactive status
     */
    public function toggleUserStatus(): void {
        Middleware::verifyCsrf();

        $userId = (int) ($_POST['user_id'] ?? 0);
        $newStatus = sanitize($_POST['status'] ?? '');

        if ($userId <= 0 || !in_array($newStatus, ['active', 'inactive', 'suspended'])) {
            Session::setFlash('danger', 'Invalid request.');
            $this->redirect('/admin/users');
        }

        $this->userModel->update($userId, ['status' => $newStatus]);
        Session::setFlash('success', 'User status updated to ' . $newStatus . '.');
        $this->redirect('/admin/users');
    }

    /**
     * Count users by role (helper)
     */
    private function countUsers(?string $role = null): int {
        $sql = "SELECT COUNT(*) as total FROM users";
        $params = [];

        if ($role !== null) {
            $sql .= " WHERE role = :role";
            $params['role'] = $role;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return (int) ($result['total'] ?? 0);
    }

    // ---- Placeholder methods for future phases ----

    /**
     * All platform orders listing with status filter
     * GET /admin/orders
     */
    public function orders(): void {
        require_once APP_PATH . '/models/Order.php';
        $orderModel = new Order();

        $statusFilter = sanitize($_GET['status'] ?? 'all');
        $validStatuses = ['all', 'pending', 'accepted', 'preparing', 'out_for_delivery', 'delivered', 'cancelled'];
        if (!in_array($statusFilter, $validStatuses)) {
            $statusFilter = 'all';
        }

        $filterStatus = ($statusFilter === 'all') ? null : $statusFilter;
        $searchQuery  = isset($_GET['search']) ? sanitize($_GET['search']) : null;
        
        $orders       = $orderModel->findAllWithDetails($filterStatus, $searchQuery, 150);
        $orderStats   = $orderModel->getPlatformOrderStats();
        $totalOrders  = array_sum($orderStats);

        $this->render('admin/orders', [
            'title'        => 'Platform Orders',
            'orders'       => $orders,
            'statusFilter' => $statusFilter,
            'searchQuery'  => $searchQuery,
            'orderStats'   => $orderStats,
            'totalOrders'  => $totalOrders,
        ]);
    }

    /**
     * Tiffin Subscriptions Management
     */
    public function subscriptions(): void {
        require_once APP_PATH . '/models/CustomerSubscription.php';
        $customerSubModel = new CustomerSubscription();

        $statusFilter = $_GET['status'] ?? null;
        $subscriptions = $customerSubModel->findAllWithDetails($statusFilter);

        $this->render('admin/subscriptions', [
            'title'         => 'Tiffin Subscriptions Management',
            'subscriptions' => $subscriptions,
            'statusFilter'  => $statusFilter,
        ]);
    }

    public function approveSubscription(int $id): void {
        Middleware::verifyCsrf();
        require_once APP_PATH . '/models/CustomerSubscription.php';
        $customerSubModel = new CustomerSubscription();

        $sub = $customerSubModel->findById($id);
        if ($sub && $sub['status'] === SUBSCRIPTION_STATUS_PENDING) {
            $customerSubModel->updateStatus($id, SUBSCRIPTION_STATUS_ACTIVE);
            Session::setFlash('success', 'Subscription approved successfully.');
        } else {
            Session::setFlash('danger', 'Invalid subscription or already processed.');
        }
        $this->redirect('/admin/subscriptions');
    }

    public function rejectSubscription(int $id): void {
        Middleware::verifyCsrf();
        require_once APP_PATH . '/models/CustomerSubscription.php';
        $customerSubModel = new CustomerSubscription();

        $sub = $customerSubModel->findById($id);
        if ($sub && $sub['status'] === SUBSCRIPTION_STATUS_PENDING) {
            $customerSubModel->updateStatus($id, SUBSCRIPTION_STATUS_CANCELLED);
            Session::setFlash('success', 'Subscription rejected and cancelled.');
        } else {
            Session::setFlash('danger', 'Invalid subscription or already processed.');
        }
        $this->redirect('/admin/subscriptions');
    }

    /**
     * Category Management — List all food categories with add form
     */
    public function categories(): void {
        $categories = $this->categoryModel->getAllOrdered();

        // Attach item count to each category
        foreach ($categories as &$cat) {
            $cat['item_count'] = $this->categoryModel->countMenuItems($cat['id']);
        }

        $this->render('admin/categories', [
            'title'      => 'Food Category Management',
            'categories' => $categories
        ]);
    }

    /**
     * Add new food category
     */
    public function addCategory(): void {
        Middleware::verifyCsrf();

        $name = sanitize($_POST['category_name'] ?? '');
        $description = sanitize($_POST['description'] ?? '');

        if (empty($name)) {
            Session::setFlash('danger', 'Category name is required.');
            $this->redirect('/admin/categories');
        }

        $data = [
            'category_name' => $name,
            'description'   => $description,
        ];

        // Handle category image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = PUBLIC_PATH . '/uploads/categories/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $result = $this->handleImageUpload($_FILES['image'], $uploadDir, 'cat_');
            if ($result['success']) {
                $data['image'] = $result['filename'];
            } else {
                Session::setFlash('warning', $result['error']);
            }
        }

        try {
            $this->categoryModel->create($data);
            Session::setFlash('success', 'Category "' . $name . '" added successfully!');
        } catch (Exception $e) {
            Session::setFlash('danger', 'Failed to add category. It may already exist.');
        }

        $this->redirect('/admin/categories');
    }

    /**
     * Edit existing food category
     */
    public function editCategory(string $id): void {
        Middleware::verifyCsrf();

        $categoryId = (int) $id;
        $category = $this->categoryModel->find($categoryId);

        if (!$category) {
            Session::setFlash('danger', 'Category not found.');
            $this->redirect('/admin/categories');
        }

        $name = sanitize($_POST['category_name'] ?? '');
        $description = sanitize($_POST['description'] ?? '');

        if (empty($name)) {
            Session::setFlash('danger', 'Category name is required.');
            $this->redirect('/admin/categories');
        }

        $data = [
            'category_name' => $name,
            'description'   => $description,
        ];

        // Handle category image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = PUBLIC_PATH . '/uploads/categories/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $result = $this->handleImageUpload($_FILES['image'], $uploadDir, 'cat_');
            if ($result['success']) {
                // Delete old image
                if (!empty($category['image'])) {
                    $oldPath = $uploadDir . $category['image'];
                    if (file_exists($oldPath)) unlink($oldPath);
                }
                $data['image'] = $result['filename'];
            }
        }

        try {
            $this->categoryModel->update($categoryId, $data);
            Session::setFlash('success', 'Category updated successfully!');
        } catch (Exception $e) {
            Session::setFlash('danger', 'Failed to update category.');
        }

        $this->redirect('/admin/categories');
    }

    /**
     * Delete food category (only if no dishes are linked)
     */
    public function deleteCategory(string $id): void {
        Middleware::verifyCsrf();

        $categoryId = (int) $id;

        if ($this->categoryModel->hasMenuItems($categoryId)) {
            Session::setFlash('danger', 'Cannot delete this category — it has dishes linked to it. Remove the dishes first.');
            $this->redirect('/admin/categories');
        }

        $category = $this->categoryModel->find($categoryId);
        if ($category) {
            // Delete category image if exists
            if (!empty($category['image'])) {
                $imgPath = PUBLIC_PATH . '/uploads/categories/' . $category['image'];
                if (file_exists($imgPath)) unlink($imgPath);
            }
            $this->categoryModel->delete($categoryId);
            Session::setFlash('success', 'Category deleted successfully.');
        }

        $this->redirect('/admin/categories');
    }

    /**
     * Handle image file upload with validation
     */
    private function handleImageUpload(array $file, string $uploadDir, string $prefix = ''): array {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $maxSize = 5 * 1024 * 1024;

        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'error' => 'Only JPG, PNG, WebP, and GIF images are allowed.'];
        }
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'error' => 'Image must be under 5 MB.'];
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = $prefix . uniqid() . '_' . time() . '.' . $ext;

        if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
            return ['success' => true, 'filename' => $filename];
        }
        return ['success' => false, 'error' => 'Failed to upload image.'];
    }

    /**
     * Platform Analytics & Revenue Reports
     * GET /admin/reports
     */
    public function reports(): void {
        require_once APP_PATH . '/models/Order.php';
        $orderModel = new Order();

        $revenue      = $orderModel->getPlatformRevenue();
        $orderStats   = $orderModel->getPlatformOrderStats();
        $topKitchens  = $orderModel->getTopKitchensByRevenue(5);
        $monthlyTrend = $orderModel->getMonthlyOrderTrend(6);
        $userStats    = [
            'total'     => $this->countUsers(),
            'customers' => $this->countUsers('customer'),
            'chefs'     => $this->countUsers('chef'),
        ];

        $this->render('admin/reports', [
            'title'        => 'Analytics & Reports',
            'revenue'      => $revenue,
            'orderStats'   => $orderStats,
            'topKitchens'  => $topKitchens,
            'monthlyTrend' => $monthlyTrend,
            'userStats'    => $userStats,
        ]);
    }

    /**
     * Module 3.11 — Admin Broadcast Notification to all active users
     * POST /admin/broadcast
     */
    public function broadcast(): void {
        Middleware::verifyCsrf();

        $title   = trim($_POST['title']   ?? '');
        $message = trim($_POST['message'] ?? '');
        $type    = $_POST['type'] ?? 'promotion';

        $allowedTypes = ['promotion', 'system_alert', 'order_update', 'kitchen_update'];
        if (!in_array($type, $allowedTypes, true)) {
            $type = 'promotion';
        }

        if (empty($title) || empty($message)) {
            Session::setFlash('danger', 'Title and message are required.');
            $this->redirect('/admin/dashboard');
            return;
        }

        require_once APP_PATH . '/models/Notification.php';
        $notifModel = new Notification();
        $count = $notifModel->broadcast($title, $message, $type);
        Session::setFlash('success', 'Announcement broadcasted successfully to users.');
        $this->redirect('/admin/dashboard');
    }

    // ─── Admin Review Management ────────────────────────────────────────────────

    /**
     * View all reviews across the platform
     * GET /admin/reviews
     */
    public function reviews(): void {
        require_once APP_PATH . '/models/Review.php';
        $reviewModel = new Review();
        
        $reviews = $reviewModel->findAllWithDetails();

        $this->render('admin/reviews', [
            'title'   => 'Admin - Review Management',
            'reviews' => $reviews
        ]);
    }

    /**
     * Safely delete a review
     * POST /admin/review/delete/{id}
     */
    public function deleteReview(string $id): void {
        Middleware::verifyCsrf();
        
        require_once APP_PATH . '/models/Review.php';
        $reviewModel = new Review();
        
        $reviewId = (int)$id;
        $review = $reviewModel->find($reviewId);
        
        if (!$review) {
            Session::setFlash('danger', 'Review not found.');
            $this->redirect('/admin/reviews');
            return;
        }

        try {
            $reviewModel->delete($reviewId);
            Session::setFlash('success', 'Review deleted successfully.');
        } catch (Exception $e) {
            Session::setFlash('danger', 'Failed to delete review. Please ensure it is not locked by other records.');
        }

        $this->redirect('/admin/reviews');
    }

    // ─── Admin Zero Food Waste Management (Module 3.9) ─────────────────────────

    /**
     * Monitor all Zero Waste listings platform-wide + reporting stats
     * GET /admin/zero-waste
     */
    public function zeroWaste(): void {
        require_once APP_PATH . '/models/ZeroWasteItem.php';
        $zwModel = new ZeroWasteItem();

        // Optional status filter
        $statusFilter = sanitize($_GET['status'] ?? 'all');
        $validStatuses = ['all', 'active', 'sold_out', 'expired'];
        if (!in_array($statusFilter, $validStatuses, true)) {
            $statusFilter = 'all';
        }

        $filterParam = ($statusFilter === 'all') ? null : $statusFilter;

        $listings    = $zwModel->findAllWithAdminDetails($filterParam);
        $stats       = $zwModel->getPlatformStats();
        $byKitchen   = $zwModel->getStatsByKitchen();

        $this->render('admin/zero_waste', [
            'title'        => 'Zero Waste Management',
            'listings'     => $listings,
            'stats'        => $stats,
            'byKitchen'    => $byKitchen,
            'statusFilter' => $statusFilter,
        ]);
    }

    // ─── Admin AI Recommendation Management (Module 3.7) ──────────────────────

    /**
     * Main AI Recommendation dashboard
     * GET /admin/ai-recommendations
     */
    public function aiRecommendations(): void {
        require_once APP_PATH . '/models/Recommendation.php';
        $recModel = new Recommendation();

        $health    = $recModel->getAiHealth();
        $aiConfig  = $recModel->getAiConfig();   // null when offline
        $dataStats = $recModel->getAdminStats();

        // Default/fallback config values shown when Python is offline
        $defaultConfig = [
            'similarity_weight' => 0.80,
            'popularity_weight' => 0.20,
            'default_limit'     => 8,
        ];

        $this->render('admin/ai_recommendations', [
            'title'         => 'AI Recommendation Management',
            'health'        => $health,
            'aiConfig'      => $aiConfig ?? $defaultConfig,
            'aiOnline'      => $health['online'],
            'dataStats'     => $dataStats,
            'configIsFresh' => ($aiConfig !== null),
        ]);
    }

    /**
     * Update AI config parameters — writes via Python /config endpoint
     * POST /admin/ai-config/update
     */
    public function updateAiConfig(): void {
        Middleware::verifyCsrf();

        require_once APP_PATH . '/models/Recommendation.php';
        $recModel = new Recommendation();

        // Server-side validation — never trust client input
        $sw  = (float)($_POST['similarity_weight'] ?? -1);
        $pw  = (float)($_POST['popularity_weight']  ?? -1);
        $lim = (int)($_POST['default_limit']         ?? -1);

        if ($sw < 0 || $sw > 1 || $pw < 0 || $pw > 1) {
            Session::setFlash('danger', 'Weights must each be between 0 and 1.');
            $this->redirect('/admin/ai-recommendations');
            return;
        }
        if (abs($sw + $pw - 1.0) > 0.01) {
            Session::setFlash('danger', 'Similarity weight + Popularity weight must equal 1.0.');
            $this->redirect('/admin/ai-recommendations');
            return;
        }
        if ($lim < 1 || $lim > 20) {
            Session::setFlash('danger', 'Recommendation limit must be between 1 and 20.');
            $this->redirect('/admin/ai-recommendations');
            return;
        }

        $result = $recModel->updateAiConfig([
            'similarity_weight' => round($sw, 4),
            'popularity_weight' => round($pw, 4),
            'default_limit'     => $lim,
        ]);

        if ($result['success']) {
            Session::setFlash('success', 'AI configuration updated successfully! New weights will apply on the next recommendation request.');
        } else {
            Session::setFlash('danger', 'Failed to update AI config: ' . ($result['error'] ?? 'Unknown error'));
        }

        $this->redirect('/admin/ai-recommendations');
    }

    /**
     * Test recommendations for a given customer ID
     * POST /admin/ai-test
     */
    public function testAiRecommendation(): void {
        Middleware::verifyCsrf();

        require_once APP_PATH . '/models/Recommendation.php';
        $recModel = new Recommendation();

        $rawUserId = trim($_POST['test_user_id'] ?? '');

        // Validate: must be a positive integer
        if (!ctype_digit($rawUserId) || (int)$rawUserId <= 0) {
            Session::setFlash('danger', 'Invalid Customer ID. Please enter a positive integer.');
            $this->redirect('/admin/ai-recommendations');
            return;
        }

        $userId    = (int)$rawUserId;
        $limit     = max(1, min(20, (int)($_POST['test_limit'] ?? 8)));

        // Verify the user exists and is actually a customer
        $userModel = $this->userModel;
        $user      = $userModel->findById($userId);
        if (!$user || $user['role'] !== 'customer') {
            Session::setFlash('danger', 'Customer ID ' . $userId . ' not found or is not a customer account.');
            $this->redirect('/admin/ai-recommendations');
            return;
        }

        $result     = $recModel->testRecommendations($userId, $limit);
        $health     = $recModel->getAiHealth();
        $aiConfig   = $recModel->getAiConfig();
        $dataStats  = $recModel->getAdminStats();
        $defaultConfig = ['similarity_weight' => 0.80, 'popularity_weight' => 0.20, 'default_limit' => 8];

        $this->render('admin/ai_recommendations', [
            'title'         => 'AI Recommendation Management',
            'health'        => $health,
            'aiConfig'      => $aiConfig ?? $defaultConfig,
            'aiOnline'      => $health['online'],
            'dataStats'     => $dataStats,
            'configIsFresh' => ($aiConfig !== null),
            'testResult'    => $result,
            'testUserId'    => $userId,
            'testUserName'  => sanitize($user['full_name']),
            'testLimit'     => $limit,
        ]);
    }

    // ─── Admin Order Intervention (Force Status Change) ─────────────────────────

    /**
     * Force-update an order's status as admin intervention.
     * POST /admin/order/intervene
     */
    public function interveneOrder(): void {
        Middleware::verifyCsrf();

        require_once APP_PATH . '/models/Order.php';
        $orderModel = new Order();

        $orderId   = (int)($_POST['order_id']    ?? 0);
        $newStatus = sanitize(trim($_POST['new_status'] ?? ''));
        $reason    = sanitize(trim($_POST['reason']     ?? ''));

        $allowedStatuses = ['pending', 'accepted', 'preparing', 'out_for_delivery', 'delivered', 'cancelled'];

        // Validate inputs
        if ($orderId <= 0 || !in_array($newStatus, $allowedStatuses, true)) {
            Session::setFlash('danger', 'Invalid order or status selection.');
            $this->redirect('/admin/orders');
            return;
        }

        if (empty($reason)) {
            Session::setFlash('danger', 'A reason is required for admin intervention.');
            $this->redirect('/admin/orders');
            return;
        }

        $order = $orderModel->findByIdWithDetails($orderId);
        if (!$order) {
            Session::setFlash('danger', 'Order #' . $orderId . ' not found.');
            $this->redirect('/admin/orders');
            return;
        }

        // Prevent no-op update
        if ($order['order_status'] === $newStatus) {
            Session::setFlash('warning', 'Order is already set to "' . ucwords(str_replace('_', ' ', $newStatus)) . '".');
            $this->redirect('/admin/orders');
            return;
        }

        $success = $orderModel->adminUpdateStatus($orderId, $newStatus, $reason);

        if ($success) {
            $label = ucwords(str_replace('_', ' ', $newStatus));
            Session::setFlash('success',
                'Order <strong>' . sanitize($order['order_number']) . '</strong> has been updated to <strong>' . $label . '</strong>. Reason: ' . $reason
            );

            // Send a notification to the customer
            try {
                require_once APP_PATH . '/models/Notification.php';
                $notif = new Notification();
                $notif->create([
                    'user_id' => (int)$order['customer_id'],
                    'title'   => 'Your order has been updated',
                    'message' => 'Admin updated your order ' . sanitize($order['order_number']) . ' to "' . $label . '". Reason: ' . $reason,
                    'type'    => 'order_update',
                    'is_read' => 0,
                ]);
            } catch (Exception $e) {
                // Notification failure is non-fatal
            }
        } else {
            Session::setFlash('danger', 'Failed to update order status. Please try again.');
        }

        $this->redirect('/admin/orders');
    }
}
