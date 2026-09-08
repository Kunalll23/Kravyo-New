<?php
/**
 * Kravyo - Admin Controller (Platform Administration & Kitchen Verification)
 */

require_once APP_PATH . '/models/Kitchen.php';
require_once APP_PATH . '/models/User.php';
require_once APP_PATH . '/models/Category.php';

class AdminController extends Controller {

    private Kitchen $kitchenModel;
    private User $userModel;
    private Category $categoryModel;

    public function __construct() {
        Middleware::role(ROLE_ADMIN);
        $this->kitchenModel = new Kitchen();
        $this->userModel = new User();
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
        $orders       = $orderModel->findAllWithDetails($filterStatus, 150);
        $orderStats   = $orderModel->getPlatformOrderStats();
        $totalOrders  = array_sum($orderStats);

        $this->render('admin/orders', [
            'title'        => 'Platform Orders',
            'orders'       => $orders,
            'statusFilter' => $statusFilter,
            'orderStats'   => $orderStats,
            'totalOrders'  => $totalOrders,
        ]);
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
}
