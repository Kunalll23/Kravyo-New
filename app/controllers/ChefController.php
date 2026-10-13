<?php
/**
 * Kravyo - Chef Controller (Kitchen Profile & Dashboard Management)
 */

require_once APP_PATH . '/models/Kitchen.php';
require_once APP_PATH . '/models/Chef.php';
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
     * Chef Dashboard — Overview with real analytics, date filters & financial insights
     */
    public function dashboard(): void {
        require_once APP_PATH . '/models/Order.php';
        require_once APP_PATH . '/models/Review.php';

        $orderModel  = new Order();
        $reviewModel = new Review();

        $userId  = Session::get('user_id');
        $kitchen = $this->kitchenModel->findByUserId($userId);

        $kitchenId     = $kitchen ? (int) $kitchen['id'] : 0;
        $menuItemCount = $kitchen ? $this->menuItemModel->countByKitchenId($kitchenId) : 0;

        // Lifetime stats
        $totalOrders     = $kitchen ? $orderModel->countByKitchenId($kitchenId) : 0;
        $totalEarnings   = $kitchen ? $orderModel->totalEarnings($kitchenId) : 0.0;
        $avgRating       = $kitchen ? $reviewModel->getAverageRating($kitchenId) : 0.0;
        $reviewCount     = $kitchen ? $reviewModel->countByKitchenId($kitchenId) : 0;
        $recentReviews   = $kitchen ? $reviewModel->findRecentByKitchenId($kitchenId, 3) : [];
        $monthlyEarnings = $kitchen ? $orderModel->getMonthlyEarningsComparison($kitchenId) : ['this_month' => 0, 'last_month' => 0];

        // Month-over-month trend %
        $monthTrend = 0;
        if ($monthlyEarnings['last_month'] > 0) {
            $monthTrend = round((($monthlyEarnings['this_month'] - $monthlyEarnings['last_month']) / $monthlyEarnings['last_month']) * 100, 1);
        } elseif ($monthlyEarnings['this_month'] > 0) {
            $monthTrend = 100;
        }

        // Global Date & Period Filter Resolution
        $range = trim($_GET['range'] ?? '7days');
        $today = date('Y-m-d');
        $startDate = $today;
        $endDate   = $today;

        switch ($range) {
            case 'today':
                $startDate  = $today;
                $endDate    = $today;
                $rangeLabel = 'Today (' . date('d M Y') . ')';
                break;
            case 'yesterday':
                $startDate  = date('Y-m-d', strtotime('-1 day'));
                $endDate    = $startDate;
                $rangeLabel = 'Yesterday (' . date('d M Y', strtotime('-1 day')) . ')';
                break;
            case 'this_month':
                $startDate  = date('Y-m-01');
                $endDate    = date('Y-m-t');
                $rangeLabel = 'This Month (' . date('F Y') . ')';
                break;
            case 'last_month':
                $startDate  = date('Y-m-d', strtotime('first day of last month'));
                $endDate    = date('Y-m-d', strtotime('last day of last month'));
                $rangeLabel = 'Last Month (' . date('F Y', strtotime('last month')) . ')';
                break;
            case 'overall':
            case 'all':
                $earliestOrder  = $kitchen ? $orderModel->getEarliestOrderDate($kitchenId) : null;
                $kitchenCreated = !empty($kitchen['created_at']) ? date('Y-m-d', strtotime($kitchen['created_at'])) : null;
                $earliest       = $earliestOrder ?: $kitchenCreated ?: date('Y-m-d', strtotime('-30 days'));
                $startDate      = min(date('Y-m-d', strtotime($earliest)), $today);
                $endDate        = $today;
                $rangeLabel     = ($startDate === $endDate)
                    ? 'Overall Period (' . date('d M Y', strtotime($startDate)) . ')'
                    : 'Overall Period (' . date('d M Y', strtotime($startDate)) . ' – ' . date('d M Y', strtotime($endDate)) . ')';
                break;
            case 'custom':
                $customStart = trim($_GET['start_date'] ?? '');
                $customEnd   = trim($_GET['end_date'] ?? '');
                if (!empty($customStart) && !empty($customEnd) && strtotime($customStart) && strtotime($customEnd)) {
                    $startDate = date('Y-m-d', min(strtotime($customStart), strtotime($customEnd)));
                    $endDate   = date('Y-m-d', max(strtotime($customStart), strtotime($customEnd)));
                } else {
                    $startDate = date('Y-m-d', strtotime('-6 days'));
                    $endDate   = $today;
                }
                $rangeLabel = date('d M Y', strtotime($startDate)) . ' – ' . date('d M Y', strtotime($endDate));
                break;
            case '7days':
            default:
                $range      = '7days';
                $startDate  = date('Y-m-d', strtotime('-6 days'));
                $endDate    = $today;
                $rangeLabel = 'Last 7 Days (' . date('d M', strtotime($startDate)) . ' – ' . date('d M Y', strtotime($endDate)) . ')';
                break;
        }

        // Period-specific dynamic metrics
        $filteredStats = $kitchen ? $orderModel->getFilteredAnalytics($kitchenId, $startDate, $endDate) : [
            'total_placed_orders' => 0,
            'delivered_orders'    => 0,
            'total_earnings'      => 0.0,
            'avg_order_value'     => 0.0,
            'online_settled'      => 0.0,
            'cod_pending'         => 0.0,
            'platform_fee'        => 0.0,
            'net_payable'         => 0.0,
            'cancelled_orders'    => 0,
        ];

        // Period-specific chart data
        $chartData = $kitchen ? $orderModel->getChartDataByRange($kitchenId, $startDate, $endDate) : [
            'labels'       => [],
            'full_dates'   => [],
            'revenues'     => [],
            'orders'       => [],
            'total_rev'    => 0.0,
            'total_orders' => 0,
            'max_rev'      => 0.0,
            'max_orders'   => 0,
        ];

        // Top selling dishes for selected period
        $topDishes = $kitchen ? $orderModel->getTopSellingItemsByRange($kitchenId, $startDate, $endDate, 5) : [];
        if (empty($topDishes) && $kitchen) {
            $topDishes = $orderModel->getTopSellingItems($kitchenId, 5);
        }

        $this->render('chef/dashboard', [
            'title'           => 'Chef Dashboard',
            'kitchen'         => $kitchen,
            'menuItemCount'   => $menuItemCount,
            'totalOrders'     => $totalOrders,
            'totalEarnings'   => $totalEarnings,
            'avgRating'       => $avgRating,
            'reviewCount'     => $reviewCount,
            'topDishes'       => $topDishes,
            'monthlyEarnings' => $monthlyEarnings,
            'monthTrend'      => $monthTrend,
            'recentReviews'   => $recentReviews,
            // Date Filter Data
            'activeRange'     => $range,
            'startDate'       => $startDate,
            'endDate'         => $endDate,
            'rangeLabel'      => $rangeLabel,
            'filteredStats'   => $filteredStats,
            'chartData'       => $chartData,
        ]);
    }

    /**
     * Export kitchen orders report to CSV
     */
    public function exportReport(): void {
        require_once APP_PATH . '/models/Order.php';
        $orderModel = new Order();

        $userId  = Session::get('user_id');
        $kitchen = $this->kitchenModel->findByUserId($userId);
        if (!$kitchen) {
            $this->redirect('/chef/dashboard');
            return;
        }

        $kitchenId = (int)$kitchen['id'];
        $range     = trim($_GET['range'] ?? '7days');
        $today     = date('Y-m-d');
        $startDate = $today;
        $endDate   = $today;

        switch ($range) {
            case 'today':
                $startDate = $today;
                $endDate   = $today;
                break;
            case 'yesterday':
                $startDate = date('Y-m-d', strtotime('-1 day'));
                $endDate   = $startDate;
                break;
            case 'this_month':
                $startDate = date('Y-m-01');
                $endDate   = date('Y-m-t');
                break;
            case 'last_month':
                $startDate = date('Y-m-d', strtotime('first day of last month'));
                $endDate   = date('Y-m-d', strtotime('last day of last month'));
                break;
            case 'overall':
            case 'all':
                $earliestOrder  = $orderModel->getEarliestOrderDate($kitchenId);
                $kitchenCreated = !empty($kitchen['created_at']) ? date('Y-m-d', strtotime($kitchen['created_at'])) : null;
                $earliest       = $earliestOrder ?: $kitchenCreated ?: date('Y-m-d', strtotime('-30 days'));
                $startDate      = min(date('Y-m-d', strtotime($earliest)), $today);
                $endDate        = $today;
                break;
            case 'custom':
                $customStart = trim($_GET['start_date'] ?? '');
                $customEnd   = trim($_GET['end_date'] ?? '');
                if (!empty($customStart) && !empty($customEnd) && strtotime($customStart) && strtotime($customEnd)) {
                    $startDate = date('Y-m-d', min(strtotime($customStart), strtotime($customEnd)));
                    $endDate   = date('Y-m-d', max(strtotime($customStart), strtotime($customEnd)));
                } else {
                    $startDate = date('Y-m-d', strtotime('-6 days'));
                    $endDate   = $today;
                }
                break;
            case '7days':
            default:
                $startDate = date('Y-m-d', strtotime('-6 days'));
                $endDate   = $today;
                break;
        }

        $orders = $orderModel->getOrdersForExport($kitchenId, $startDate, $endDate);

        $kitchenSlug = preg_replace('/[^a-zA-Z0-9_-]/', '_', $kitchen['kitchen_name']);
        $filename = "Kravyo_Report_{$kitchenSlug}_{$startDate}_to_{$endDate}.csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        // CSV Header row
        fputcsv($out, [
            'Order Number',
            'Date & Time',
            'Order Type',
            'Customer Name',
            'Total Amount (INR)',
            'Payment Method',
            'Payment Status',
            'Order Status'
        ]);

        foreach ($orders as $row) {
            fputcsv($out, [
                $row['order_number'],
                $row['created_at'],
                strtoupper($row['order_type'] ?: 'standard'),
                $row['customer_name'],
                number_format((float)$row['total_amount'], 2, '.', ''),
                strtoupper($row['payment_method'] ?: 'N/A'),
                ucfirst($row['payment_status']),
                ucwords(str_replace('_', ' ', $row['order_status']))
            ]);
        }
        fclose($out);
        exit;
    }

    /**
     * AJAX endpoint for interactive Chart scale switcher (7D, 15D, 30D, month, year)
     */
    public function chartDataApi(): void {
        header('Content-Type: application/json');
        require_once APP_PATH . '/models/Order.php';
        $orderModel = new Order();

        $userId  = Session::get('user_id');
        $kitchen = $this->kitchenModel->findByUserId($userId);
        if (!$kitchen) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }

        $kitchenId = (int)$kitchen['id'];
        $scale     = trim($_GET['scale'] ?? '7D');
        $today     = date('Y-m-d');

        switch ($scale) {
            case '15D':
                $startDate = date('Y-m-d', strtotime('-14 days'));
                $endDate   = $today;
                break;
            case '30D':
                $startDate = date('Y-m-d', strtotime('-29 days'));
                $endDate   = $today;
                break;
            case 'month':
                $startDate = date('Y-m-01');
                $endDate   = date('Y-m-t');
                break;
            case 'year':
                $startDate = date('Y-01-01');
                $endDate   = date('Y-12-31');
                break;
            case 'all':
            case 'overall':
                $scale          = 'Overall';
                $earliestOrder  = $orderModel->getEarliestOrderDate($kitchenId);
                $kitchenCreated = !empty($kitchen['created_at']) ? date('Y-m-d', strtotime($kitchen['created_at'])) : null;
                $earliest       = $earliestOrder ?: $kitchenCreated ?: date('Y-m-d', strtotime('-30 days'));
                $startDate      = min(date('Y-m-d', strtotime($earliest)), $today);
                $endDate        = $today;
                break;
            case '7D':
            default:
                $scale     = '7D';
                $startDate = date('Y-m-d', strtotime('-6 days'));
                $endDate   = $today;
                break;
        }

        $chartData = $orderModel->getChartDataByRange($kitchenId, $startDate, $endDate);
        echo json_encode([
            'success'   => true,
            'scale'     => $scale,
            'startDate' => $startDate,
            'endDate'   => $endDate,
            'data'      => $chartData
        ]);
        exit;
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

        // Latitude & Longitude location coordinates
        $rawLat = trim($_POST['latitude'] ?? '');
        $rawLng = trim($_POST['longitude'] ?? '');

        if ($rawLat !== '' && $rawLng !== '') {
            if (!is_numeric($rawLat) || !is_numeric($rawLng)) {
                Session::setFlash('danger', 'Latitude and Longitude must be valid numbers.');
                $this->redirect('/chef/profile');
                return;
            }
            $latVal = (float)$rawLat;
            $lngVal = (float)$rawLng;
            if ($latVal < -90.0 || $latVal > 90.0 || $lngVal < -180.0 || $lngVal > 180.0) {
                Session::setFlash('danger', 'Latitude must be between -90 and 90, and Longitude between -180 and 180.');
                $this->redirect('/chef/profile');
                return;
            }
            $data['latitude']  = $latVal;
            $data['longitude'] = $lngVal;
        } else {
            // Automatic Backend Geocoding via Nominatim
            $searchQuery = urlencode($data['address'] . ', ' . $data['city'] . ', ' . $data['pincode'] . ', India');
            $url = "https://nominatim.openstreetmap.org/search?format=json&q={$searchQuery}&limit=1";
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'KravyoFoodDeliveryApp/1.0');
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $response = curl_exec($ch);
            curl_close($ch);

            $geoData = json_decode($response, true);
            
            if (!empty($geoData) && isset($geoData[0]['lat']) && isset($geoData[0]['lon'])) {
                $data['latitude']  = (float)$geoData[0]['lat'];
                $data['longitude'] = (float)$geoData[0]['lon'];
            } else {
                Session::setFlash('danger', 'We could not locate this address automatically on the map. Please click on the map manually to pin your exact kitchen location.');
                Session::set('_old_post', $_POST);
                $this->redirect('/chef/profile');
                return;
            }
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
     * Toggle dish availability status
     * POST /chef/dish/toggle-availability/{id}
     */
    public function toggleDishAvailability(string $id): void {
        Middleware::verifyCsrf();

        $dishId = (int) $id;
        $userId = Session::get('user_id');
        $kitchen = $this->kitchenModel->findByUserId($userId);

        if (!$kitchen || !$this->menuItemModel->belongsToKitchen($dishId, $kitchen['id'])) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Dish not found.'], 404);
            }
            Session::setFlash('danger', 'Dish not found or access denied.');
            $this->redirect('/chef/menu');
        }

        $dish = $this->menuItemModel->find($dishId);
        $newStatus = $dish['is_available'] ? 0 : 1;
        
        $this->menuItemModel->update($dishId, ['is_available' => $newStatus]);

        if ($this->isAjax()) {
            $this->json([
                'success' => true,
                'is_available' => $newStatus,
                'message' => $newStatus ? 'Dish is now In Stock.' : 'Dish is now Out of Stock.'
            ]);
        }

        Session::setFlash('success', 'Dish availability updated.');
        $this->redirect('/chef/menu');
    }

    /**
     * Chef Incoming Orders Dashboard
     * GET /chef/orders
     */
    public function orders(): void {
        require_once APP_PATH . '/models/Order.php';
        require_once APP_PATH . '/models/OrderItem.php';
        require_once APP_PATH . '/models/DeliveryOffer.php';

        $orderModel = new Order();
        $orderItemModel = new OrderItem();
        $deliveryOfferModel = new DeliveryOffer();

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

        // Attach order items and timeline to each order
        foreach ($orders as &$order) {
            $order['items'] = $orderItemModel->findByOrderId((int) $order['id']);
            if (($order['dispatch_status'] ?? '') === 'broadcasting') {
                $order['timeline'] = $deliveryOfferModel->getTimelineForOrder((int) $order['id']);
            }
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

        // --- NEW PHASE: Early Dispatch Request ---
        if ($newStatus === 'request_rider') {
            if ($order['order_status'] === ORDER_STATUS_PREPARING && $order['dispatch_status'] === 'none') {
                require_once APP_PATH . '/services/DispatchService.php';
                require_once APP_PATH . '/models/DeliveryOffer.php';
                require_once APP_PATH . '/models/DeliveryPartner.php';
                
                $dispatchService = new DispatchService();
                $dispatchService->broadcast($orderId);
                
                Session::setFlash('success', 'Looking for a rider! Keep preparing the food.');
            } else {
                Session::setFlash('danger', 'Cannot request rider at this time.');
            }
            $this->redirect('/chef/orders');
            return;
        }

        $validTransitions = [
            ORDER_STATUS_PENDING          => [ORDER_STATUS_ACCEPTED, ORDER_STATUS_CANCELLED],
            ORDER_STATUS_ACCEPTED         => [ORDER_STATUS_PREPARING],
            ORDER_STATUS_PREPARING        => [ORDER_STATUS_READY_FOR_PICKUP], // Changed: Chef marks ready for pickup
        ];

        $currentStatus = $order['order_status'];

        if (!isset($validTransitions[$currentStatus]) || !in_array($newStatus, $validTransitions[$currentStatus])) {
            Session::setFlash('danger', 'Invalid status transition.');
            $this->redirect('/chef/orders');
            return;
        }

        $orderModel->updateStatus($orderId, $newStatus);

        // --- PHASE 12: BROADCAST DISPATCH ---
        if ($newStatus === ORDER_STATUS_READY_FOR_PICKUP) {
            if ($order['dispatch_status'] === 'none') {
                require_once APP_PATH . '/services/DispatchService.php';
                require_once APP_PATH . '/models/DeliveryOffer.php';
                require_once APP_PATH . '/models/DeliveryPartner.php';
                
                $dispatchService = new DispatchService();
                $dispatchService->broadcast($orderId);
            }
            
            // If already assigned, notify the rider that the food is now ready
            if ($order['delivery_partner_id']) {
                require_once APP_PATH . '/models/Notification.php';
                $nModel = new Notification();
                
                // IMPORTANT: In Kravyo, the notification system uses 'delivery' (not 'delivery_partner') for the userType.
                // We need to fetch the actual User ID of the delivery partner, not their profile ID!
                require_once APP_PATH . '/models/DeliveryPartner.php';
                $dpModel = new DeliveryPartner();
                $dpProfile = $dpModel->find((int)$order['delivery_partner_id']);
                
                if ($dpProfile) {
                    $nModel->createForUser(
                        (int) $dpProfile['user_id'], // Must be the user_id, not delivery_partner_id!
                        'delivery',
                        'Food is Ready!',
                        "The kitchen has marked Order #{$order['order_number']} as packed and ready.",
                        'order_update',
                        '/delivery/order/' . $orderId
                    );
                }
            }
        }

        // If delivered, mark payment as completed for COD
        if ($newStatus === ORDER_STATUS_DELIVERED && $order['payment_method'] === PAYMENT_METHOD_COD) {
            $orderModel->updatePaymentStatus($orderId, PAYMENT_STATUS_COMPLETED);
        }

        $statusLabels = [
            ORDER_STATUS_ACCEPTED         => 'accepted',
            ORDER_STATUS_PREPARING        => 'marked as preparing',
            ORDER_STATUS_READY_FOR_PICKUP => 'marked as ready for pickup',
            ORDER_STATUS_CANCELLED        => 'rejected',
        ];

        $label = $statusLabels[$newStatus] ?? 'updated';

        // Module 1.13 — Notify customer of their order status change
        try {
            require_once APP_PATH . '/models/Notification.php';
            $notifModel = new Notification();

            $orderNo     = $order['order_number'];
            $kitchenName = $kitchen['kitchen_name'] ?? 'Your home chef';

            $statusNotifs = [
                ORDER_STATUS_ACCEPTED => [
                    'Order Confirmed! #' . $orderNo,
                    $kitchenName . ' has accepted your order #' . $orderNo . ' and will start preparing it shortly.',
                ],
                ORDER_STATUS_CANCELLED => [
                    'Order Rejected #' . $orderNo,
                    'Sorry, ' . $kitchenName . ' could not accept your order #' . $orderNo . '. Any online payment will be refunded.',
                ],
            ];

            if (isset($statusNotifs[$newStatus])) {
                [$nTitle, $nMessage] = $statusNotifs[$newStatus];
                $notifModel->createForUser(
                    (int) $order['customer_id'],
                    'customer',
                    $nTitle,
                    $nMessage,
                    'order_update',
                    '/order/track/' . $orderId
                );
            }
        } catch (Throwable $e) {
            error_log('[Kravyo] Customer status notification failed for order ' . $orderId . ': ' . $e->getMessage());
        }

        Session::setFlash('success', 'Order #' . $order['order_number'] . ' has been ' . $label . '.');
        $this->redirect('/chef/orders');
    }

    /**
     * Track Rider Map (Chef View)
     * GET /chef/order/track/{id}
     */
    public function trackOrder(string $id): void {
        Middleware::role(ROLE_CHEF);

        require_once APP_PATH . '/models/Order.php';
        $orderModel = new Order();

        $userId = Session::get('user_id');
        $kitchen = $this->kitchenModel->findByUserId($userId);
        
        $orderId = (int) $id;

        if (!$kitchen || !$orderModel->belongsToKitchen($orderId, (int) $kitchen['id'])) {
            Session::setFlash('danger', 'Order not found.');
            $this->redirect('/chef/orders');
            return;
        }

        $order = $orderModel->findWithDetails($orderId);
        if (!$order || !$order['delivery_partner_id']) {
            $this->redirect('/chef/orders');
            return;
        }

        $this->render('chef/order_track', [
            'title' => 'Track Rider - Order #' . $order['order_number'],
            'order' => $order
        ]);
    }

    /**
     * Manage Tiffin Subscription Plans + View Subscribers
     * GET /chef/subscriptions
     */
    public function subscriptions(): void {
        require_once APP_PATH . '/models/Subscription.php';
        require_once APP_PATH . '/models/CustomerSubscription.php';
        require_once APP_PATH . '/models/SubscriptionDelivery.php';

        $subscriptionModel = new Subscription();
        $customerSubModel = new CustomerSubscription();
        $deliveryModel = new SubscriptionDelivery();

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
        $pendingSubscribers = $customerSubModel->findByKitchenId($kitchenId, SUBSCRIPTION_STATUS_PENDING);
        $todaysDeliveries = $deliveryModel->getTodaysDeliveries($kitchenId);
        $subscriberCount = count($activeSubscribers);

        $this->render('chef/subscriptions', [
            'title'              => 'Tiffin Subscription Plans',
            'kitchen'            => $kitchen,
            'plans'              => $plans,
            'activeSubscribers'  => $activeSubscribers,
            'pendingSubscribers' => $pendingSubscribers,
            'todaysDeliveries'   => $todaysDeliveries,
            'subscriberCount'    => $subscriberCount,
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

        $mealsPerDay = (int) ($_POST['meals_per_day'] ?? 1);
        if ($mealsPerDay < 1 || $mealsPerDay > 3) {
            $mealsPerDay = 1;
        }

        $lunchTime = sanitize(trim($_POST['lunch_time'] ?? '12:30 PM - 01:30 PM'));
        $dinnerTime = $mealsPerDay >= 2 ? sanitize(trim($_POST['dinner_time'] ?? '07:30 PM - 08:30 PM')) : null;
        $breakfastTime = $mealsPerDay >= 3 ? sanitize(trim($_POST['breakfast_time'] ?? '08:00 AM - 09:00 AM')) : null;

        $data = [
            'kitchen_id'     => (int) $kitchen['id'],
            'plan_name'      => sanitize($_POST['plan_name'] ?? ''),
            'plan_type'      => $_POST['plan_type'] ?? 'weekly',
            'price'          => (float) ($_POST['price'] ?? 0),
            'description'    => sanitize($_POST['description'] ?? ''),
            'meals_per_day'  => $mealsPerDay,
            'lunch_time'     => !empty($lunchTime) ? $lunchTime : '12:30 PM - 01:30 PM',
            'dinner_time'    => $dinnerTime,
            'breakfast_time' => $breakfastTime,
            'is_active'      => 1,
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

        $mealsPerDay = (int) ($_POST['meals_per_day'] ?? 1);
        if ($mealsPerDay < 1 || $mealsPerDay > 3) {
            $mealsPerDay = 1;
        }

        $lunchTime = sanitize(trim($_POST['lunch_time'] ?? '12:30 PM - 01:30 PM'));
        $dinnerTime = $mealsPerDay >= 2 ? sanitize(trim($_POST['dinner_time'] ?? '07:30 PM - 08:30 PM')) : null;
        $breakfastTime = $mealsPerDay >= 3 ? sanitize(trim($_POST['breakfast_time'] ?? '08:00 AM - 09:00 AM')) : null;

        $data = [
            'plan_name'      => sanitize($_POST['plan_name'] ?? ''),
            'plan_type'      => $_POST['plan_type'] ?? 'weekly',
            'price'          => (float) ($_POST['price'] ?? 0),
            'description'    => sanitize($_POST['description'] ?? ''),
            'meals_per_day'  => $mealsPerDay,
            'lunch_time'     => !empty($lunchTime) ? $lunchTime : '12:30 PM - 01:30 PM',
            'dinner_time'    => $dinnerTime,
            'breakfast_time' => $breakfastTime,
        ];

        if (empty($data['plan_name']) || $data['price'] <= 0) {
            Session::setFlash('danger', 'Plan name and price are required.');
            $this->redirect('/chef/subscriptions');
            return;
        }

        if (!in_array($data['plan_type'], ['weekly', 'monthly'])) {
            $data['plan_type'] = 'weekly';
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

        $msg = $newStatus ? 'Plan activated successfully.' : 'Plan deactivated successfully.';
        Session::setFlash('success', $msg);
        $this->redirect('/chef/subscriptions');
    }

    /**
     * Approve a customer subscription
     * POST /chef/subscription/approve/{id}
     */
    public function approveSubscription(string $id): void {
        Middleware::verifyCsrf();
        require_once APP_PATH . '/models/CustomerSubscription.php';
        
        $customerSubModel = new CustomerSubscription();
        $userId = Session::get('user_id');
        $kitchen = $this->kitchenModel->findByUserId($userId);

        if (!$kitchen) {
            Session::setFlash('danger', 'Kitchen not found.');
            $this->redirect('/chef/subscriptions');
            return;
        }

        $kitchenId = (int) $kitchen['id'];
        $subId = (int) $id;

        $sub = $customerSubModel->findWithDetails($subId);
        if (!$sub || (int) $sub['kitchen_id'] !== $kitchenId || $sub['status'] !== SUBSCRIPTION_STATUS_PENDING) {
            Session::setFlash('danger', 'Invalid subscription request.');
            $this->redirect('/chef/subscriptions');
            return;
        }

        $customerSubModel->updateStatus($subId, SUBSCRIPTION_STATUS_ACTIVE);

        // Notify customer
        try {
            require_once APP_PATH . '/models/Notification.php';
            $startDateFormatted = !empty($sub['start_date']) ? date('d M Y', strtotime($sub['start_date'])) : 'tomorrow';
            (new Notification())->createForUser(
                (int) $sub['customer_id'],
                'customer',
                'Subscription Approved! 🎉',
                'Your subscription for "' . ($sub['plan_name'] ?? 'Tiffin Plan') . '" has been approved by ' . ($kitchen['kitchen_name'] ?? 'the chef') . '! Deliveries start ' . $startDateFormatted . '.',
                'order_update',
                '/my-subscriptions'
            );
        } catch (Throwable $ne) {
            error_log('[Kravyo] Subscription approval notification failed: ' . $ne->getMessage());
        }

        Session::setFlash('success', 'Subscription approved successfully!');
        $this->redirect('/chef/subscriptions');
    }

    /**
     * Reject a customer subscription
     * POST /chef/subscription/reject/{id}
     */
    public function rejectSubscription(string $id): void {
        Middleware::verifyCsrf();
        require_once APP_PATH . '/models/CustomerSubscription.php';
        
        $customerSubModel = new CustomerSubscription();
        $userId = Session::get('user_id');
        $kitchen = $this->kitchenModel->findByUserId($userId);

        if (!$kitchen) {
            Session::setFlash('danger', 'Kitchen not found.');
            $this->redirect('/chef/subscriptions');
            return;
        }

        $kitchenId = (int) $kitchen['id'];
        $subId = (int) $id;

        $sub = $customerSubModel->findWithDetails($subId);
        if (!$sub || (int) $sub['kitchen_id'] !== $kitchenId || $sub['status'] !== SUBSCRIPTION_STATUS_PENDING) {
            Session::setFlash('danger', 'Invalid subscription request.');
            $this->redirect('/chef/subscriptions');
            return;
        }

        $customerSubModel->updateStatus($subId, SUBSCRIPTION_STATUS_CANCELLED);

        // Notify customer
        try {
            require_once APP_PATH . '/models/Notification.php';
            (new Notification())->createForUser(
                (int) $sub['customer_id'],
                'customer',
                'Subscription Request Rejected',
                'Your subscription request for "' . ($sub['plan_name'] ?? 'Tiffin Plan') . '" could not be accepted by ' . ($kitchen['kitchen_name'] ?? 'the chef') . '.',
                'order_update',
                '/my-subscriptions'
            );
        } catch (Throwable $ne) {
            error_log('[Kravyo] Subscription rejection notification failed: ' . $ne->getMessage());
        }

        Session::setFlash('success', 'Subscription request rejected.');
        $this->redirect('/chef/subscriptions');
    }

    /**
     * Update today's delivery status
     * POST /chef/subscription/delivery/status
     */
    public function updateDeliveryStatus(): void {
        Middleware::verifyCsrf();
        require_once APP_PATH . '/models/SubscriptionDelivery.php';
        require_once APP_PATH . '/models/Order.php';
        require_once APP_PATH . '/models/CustomerSubscription.php';
        require_once APP_PATH . '/services/DispatchService.php';
        
        $deliveryModel = new SubscriptionDelivery();
        $orderModel = new Order();
        $subModel = new CustomerSubscription();
        $dispatchService = new DispatchService();

        $userId = Session::get('user_id');
        $kitchen = $this->kitchenModel->findByUserId($userId);

        if (!$kitchen) {
            Session::setFlash('danger', 'Kitchen not found.');
            $this->redirect('/chef/subscriptions');
            return;
        }

        $kitchenId = (int) $kitchen['id'];
        $deliveryId = (int) ($_POST['delivery_id'] ?? 0);
        $newStatus = $_POST['status'] ?? 'pending';

        if (!in_array($newStatus, ['pending', 'dispatched', 'delivered'])) {
            Session::setFlash('danger', 'Invalid status.');
            $this->redirect('/chef/subscriptions');
            return;
        }

        if ($deliveryModel->updateStatus($deliveryId, $newStatus, $kitchenId)) {
            // Ultra Processing: If dispatched, create a real order and broadcast to riders!
            if ($newStatus === 'dispatched') {
                $db = Database::getInstance();

                // Check if order was already created for this delivery
                $checkOrderStmt = $db->prepare("SELECT id FROM orders WHERE subscription_delivery_id = ? LIMIT 1");
                $checkOrderStmt->execute([$deliveryId]);
                $existingOrder = $checkOrderStmt->fetch();

                if (!$existingOrder) {
                    // Get the delivery details
                    $sql = "SELECT sd.*, cs.customer_id, cs.address_id, cs.special_instructions 
                            FROM subscription_deliveries sd 
                            JOIN customer_subscriptions cs ON sd.customer_subscription_id = cs.id
                            WHERE sd.id = ?";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([$deliveryId]);
                    $deliveryInfo = $stmt->fetch();

                    if ($deliveryInfo) {
                        // Create the order
                        $orderNumber = 'TFN-' . date('Ymd') . '-' . strtoupper(substr(md5((string)$deliveryId), 0, 5));
                        
                        $deliveryLat = null;
                        $deliveryLng = null;
                        if (!empty($deliveryInfo['address_id'])) {
                            $addrStmt = $db->prepare("SELECT * FROM addresses WHERE id = ?");
                            $addrStmt->execute([$deliveryInfo['address_id']]);
                            $addrRecord = $addrStmt->fetch();
                            if ($addrRecord) {
                                if (!empty($addrRecord['latitude']) && !empty($addrRecord['longitude'])) {
                                    $deliveryLat = (float) $addrRecord['latitude'];
                                    $deliveryLng = (float) $addrRecord['longitude'];
                                } else {
                                    // Attempt geocode
                                    $fullAddr = trim(($addrRecord['street_address'] ?? '') . ', ' . ($addrRecord['city'] ?? '') . ', ' . ($addrRecord['pincode'] ?? ''));
                                    if ($fullAddr !== '' && class_exists('GoogleMaps')) {
                                        $geo = GoogleMaps::geocode($fullAddr);
                                        if ($geo && !empty($geo['lat']) && !empty($geo['lng'])) {
                                            $deliveryLat = (float) $geo['lat'];
                                            $deliveryLng = (float) $geo['lng'];
                                            $db->prepare("UPDATE addresses SET latitude = ?, longitude = ? WHERE id = ?")
                                               ->execute([$deliveryLat, $deliveryLng, $deliveryInfo['address_id']]);
                                        }
                                    }
                                }
                            }
                        }

                        $timeWindow = !empty($deliveryInfo['delivery_time']) ? ' (' . $deliveryInfo['delivery_time'] . ')' : '';

                        $orderData = [
                            'order_number' => $orderNumber,
                            'customer_id'  => $deliveryInfo['customer_id'],
                            'kitchen_id'   => $kitchenId,
                            'address_id'   => $deliveryInfo['address_id'],
                            'delivery_latitude'  => $deliveryLat,
                            'delivery_longitude' => $deliveryLng,
                            'total_amount' => 0.00, // Prepaid via subscription
                            'payment_method' => 'cod', // Placeholder
                            'payment_status' => 'completed',
                            'order_status'   => 'preparing', // So dispatch picks it up
                            'special_instructions' => 'TIFFIN ' . strtoupper($deliveryInfo['meal_type']) . $timeWindow . ' - ' . ($deliveryInfo['special_instructions'] ?? ''),
                            'order_type' => 'tiffin',
                            'subscription_delivery_id' => $deliveryId
                        ];

                        $newOrderId = (int) $orderModel->create($orderData);
                        
                        if ($newOrderId > 0) {
                            // Trigger Broadcast to riders
                            $dispatchService->broadcast($newOrderId, 1);
                        }
                    }
                }
            }

            Session::setFlash('success', 'Delivery status updated and dispatched!');
        } else {
            Session::setFlash('danger', 'Failed to update status.');
        }

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

    /**
     * View dish reports for the chef's kitchen
     * GET /chef/reports
     */
    public function dishReports(): void {
        $userId  = Session::get('user_id');
        $kitchen = $this->kitchenModel->findByUserId($userId);

        if (!$kitchen) {
            Session::setFlash('danger', 'You must create a kitchen profile first.');
            $this->redirect('/chef/profile');
            return;
        }

        require_once APP_PATH . '/models/CustomerReport.php';
        $reportModel = new CustomerReport();
        $reports = $reportModel->findByKitchenIdAndType((int)$kitchen['id'], 'dish');

        $this->render('chef/dish_reports', [
            'title'   => 'Dish Reports & Feedback',
            'reports' => $reports
        ]);
    }
}

