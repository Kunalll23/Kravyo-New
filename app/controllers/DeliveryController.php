<?php
/**
 * Kravyo — Delivery Partner Controller
 * Handles delivery partner dashboard, order management, and delivery workflow.
 */

require_once APP_PATH . '/models/DeliveryPartner.php';
require_once APP_PATH . '/models/Order.php';
require_once APP_PATH . '/models/OrderItem.php';

class DeliveryController extends Controller {

    private DeliveryPartner $partnerModel;
    private Order $orderModel;

    public function __construct() {
        $this->partnerModel = new DeliveryPartner();
        $this->orderModel   = new Order();
    }

    // ─── Authentication ──────────────────────────────────────────────────────

    /**
     * Show delivery partner login form
     * GET /delivery/login
     */
    public function showLoginForm(): void {
        $this->render('delivery/login', ['title' => 'Delivery Partner Login'], 'public_portal');
    }

    /**
     * Process delivery partner login
     * POST /delivery/login
     */
    public function login(): void {
        Middleware::verifyCsrf();

        require_once APP_PATH . '/models/DeliveryUser.php';
        $userModel = new DeliveryUser();

        $email    = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            Session::setFlash('danger', 'Please enter both email and password.');
            $this->redirect('/delivery/login');
            return;
        }

        $user = $userModel->findByEmail($email);

        if (!$user || !$userModel->verifyPassword($password, $user['password_hash'])) {
            Session::setFlash('danger', 'Invalid email or password.');
            $this->redirect('/delivery/login');
            return;
        }

        if ($user['role'] !== ROLE_DELIVERY) {
            Session::setFlash('danger', 'This account is not a delivery partner account.');
            $this->redirect('/delivery/login');
            return;
        }

        if ($user['status'] !== 'active') {
            Session::setFlash('danger', 'Your account is currently <strong>' . sanitize($user['status']) . '</strong>. Please contact support.');
            $this->redirect('/delivery/login');
            return;
        }

        // Check verification status
        $partner = $this->partnerModel->findByUserId((int)$user['id']);
        if (!$partner || $partner['verification_status'] !== DELIVERY_VERIFICATION_APPROVED) {
            $statusMsg = $partner ? $partner['verification_status'] : 'not found';
            Session::setFlash('warning', 'Your delivery partner account is <strong>' . sanitize($statusMsg) . '</strong>. Please wait for admin approval.');
            $this->redirect('/delivery/login');
            return;
        }

        // Switch to delivery session context
        Session::switchToRole(ROLE_DELIVERY);

        Session::set('user_id',   (int)$user['id']);
        Session::set('user_name', $user['full_name']);
        Session::set('user_role', ROLE_DELIVERY);
        Session::set('delivery_partner_id', (int)$partner['id']);

        // Force offline upon login so they have to manually toggle it
        $this->partnerModel->toggleOnlineStatus((int)$partner['id'], 0);

        Session::setFlash('success', 'Welcome back, ' . sanitize($user['full_name']) . '!');
        $this->redirect('/delivery/dashboard');
    }

    /**
     * Show delivery partner registration form
     * GET /delivery/register
     */
    public function showRegisterForm(): void {
        $this->render('delivery/register', ['title' => 'Register as Delivery Partner'], 'public_portal');
    }

    /**
     * Process delivery partner registration
     * POST /delivery/register
     */
    public function register(): void {
        Middleware::verifyCsrf();

        require_once APP_PATH . '/models/DeliveryUser.php';
        $userModel = new DeliveryUser();

        $fullName  = sanitize(trim($_POST['full_name'] ?? ''));
        $email     = strtolower(trim($_POST['email'] ?? ''));
        $phone     = sanitize(trim($_POST['phone'] ?? ''));
        $password  = $_POST['password'] ?? '';
        $vehicleType    = sanitize(trim($_POST['vehicle_type'] ?? 'scooter'));
        $vehicleNumber  = sanitize(trim($_POST['vehicle_number'] ?? ''));
        $licenseNumber  = sanitize(trim($_POST['driving_license_number'] ?? ''));

        // Validation
        if (empty($fullName) || empty($email) || empty($phone) || empty($password) || empty($vehicleNumber) || empty($licenseNumber)) {
            Session::setFlash('danger', 'All fields are required.');
            $this->redirect('/delivery/register');
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::setFlash('danger', 'Please enter a valid email address.');
            $this->redirect('/delivery/register');
            return;
        }

        if (strlen($password) < 6) {
            Session::setFlash('danger', 'Password must be at least 6 characters.');
            $this->redirect('/delivery/register');
            return;
        }

        if (!preg_match('/^[6-9][0-9]{9}$/', $phone)) {
            Session::setFlash('danger', 'Please enter a valid 10-digit Indian mobile number.');
            $this->redirect('/delivery/register');
            return;
        }

        if ($userModel->findByEmail($email)) {
            Session::setFlash('danger', 'This email address is already registered.');
            $this->redirect('/delivery/register');
            return;
        }

        if ($userModel->findByPhone($phone)) {
            Session::setFlash('danger', 'This phone number is already registered.');
            $this->redirect('/delivery/register');
            return;
        }

        $validVehicleTypes = ['bicycle', 'scooter', 'motorcycle', 'car'];
        if (!in_array($vehicleType, $validVehicleTypes)) {
            $vehicleType = 'scooter';
        }

        try {
            $db = Database::getInstance();
            $db->beginTransaction();

            // Create user record
            $sql = "INSERT INTO delivery_users (full_name, email, phone, password_hash, role, status)
                    VALUES (:full_name, :email, :phone, :password_hash, 'delivery', 'active')";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'full_name'     => $fullName,
                'email'         => $email,
                'phone'         => $phone,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ]);
            $userId = (int) $db->lastInsertId();

            // Create delivery partner profile
            $this->partnerModel->create([
                'user_id'                => $userId,
                'vehicle_type'           => $vehicleType,
                'vehicle_number'         => $vehicleNumber,
                'driving_license_number' => $licenseNumber,
                'verification_status'    => DELIVERY_VERIFICATION_PENDING,
                'is_online'              => 0,
            ]);

            $db->commit();

            Session::setFlash('success', '✅ Registration successful! Your account is <strong>pending verification</strong>. An admin will review and approve your profile.');
            $this->redirect('/delivery/login');

        } catch (\Exception $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            error_log('[Kravyo Delivery] Registration error: ' . $e->getMessage());
            Session::setFlash('danger', 'An error occurred during registration. Please try again.');
            $this->redirect('/delivery/register');
        }
    }

    /**
     * Logout delivery partner
     * POST /delivery/logout
     */
    public function logout(): void {
        Middleware::verifyCsrf();
        Session::destroyRole(ROLE_DELIVERY);
        redirect('/delivery/login');
    }

    // ─── Dashboard ───────────────────────────────────────────────────────────

    /**
     * Delivery Partner Dashboard
     * GET /delivery/dashboard
     */
    public function dashboard(): void {
        Middleware::deliveryAuth();

        $partnerId = (int) Session::get('delivery_partner_id');
        $partner   = $this->partnerModel->findWithUser($partnerId);

        if ($partner && (int)$partner['is_online'] === 1) {
            $this->partnerModel->touchHeartbeat($partnerId);
        }

        $activeOrders     = $this->orderModel->findActiveDeliveries($partnerId);
        $completedCount   = $this->orderModel->countCompletedDeliveries($partnerId);
        $totalEarnings    = $this->orderModel->getDeliveryEarnings($partnerId);

        $this->render('delivery/dashboard', [
            'title'          => 'Delivery Dashboard',
            'partner'        => $partner,
            'activeOrders'   => $activeOrders,
            'completedCount' => $completedCount,
            'totalEarnings'  => $totalEarnings,
        ]);
    }

    /**
     * Toggle online/offline status
     * POST /delivery/toggle-status
     */
    public function toggleStatus(): void {
        Middleware::deliveryAuth();
        Middleware::verifyCsrf();

        $partnerId = (int) Session::get('delivery_partner_id');
        $partner   = $this->partnerModel->find($partnerId);

        if ($partner) {
            $newStatus = $partner['is_online'] ? 0 : 1;
            $this->partnerModel->toggleOnlineStatus($partnerId, $newStatus);
            if ($newStatus === 1) {
                $this->partnerModel->touchHeartbeat($partnerId);
                
                // Instantly ping for any active broadcasts in their city
                require_once APP_PATH . '/services/DispatchService.php';
                $dispatchService = new DispatchService();
                $dispatchService->pingNewRiderForActiveBroadcasts($partnerId, $partner['city'] ?? 'Surat');
            }
            $label = $newStatus ? 'Online' : 'Offline';
            Session::setFlash('success', 'You are now <strong>' . $label . '</strong>.');
        }

        $this->redirect('/delivery/dashboard');
    }

    /**
     * Update delivery partner live GPS location via AJAX
     * POST /delivery/location/update
     */
    public function updateLocation(): void {
        Middleware::deliveryAuth();
        Middleware::verifyCsrf();

        header('Content-Type: application/json');

        $partnerId = (int) Session::get('delivery_partner_id');
        $lat = filter_input(INPUT_POST, 'latitude', FILTER_VALIDATE_FLOAT);
        $lng = filter_input(INPUT_POST, 'longitude', FILTER_VALIDATE_FLOAT);

        if ($lat === false || $lng === false || $lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            echo json_encode(['success' => false, 'message' => 'Invalid coordinates.']);
            return;
        }

        // Update database
        $db = Database::getInstance();
        $sql = "UPDATE delivery_partners 
                SET current_latitude = :lat, current_longitude = :lng, location_updated_at = NOW() 
                WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute(['lat' => $lat, 'lng' => $lng, 'id' => $partnerId]);

        // Phase 9: Real-Time WebSockets Push
        require_once APP_PATH . '/../core/PusherService.php';
        $pusher = new PusherService();
        $pusher->trigger("delivery-partner-{$partnerId}", 'location_update', [
            'latitude' => $lat,
            'longitude' => $lng
        ]);

        echo json_encode(['success' => true, 'message' => 'Location updated.']);
    }

    // ─── Broadcast Dispatch Methods (Phase 12) ───────────────────────────────

    public function getOffers(): void {
        Middleware::deliveryAuth();
        $partnerId = (int) Session::get('delivery_partner_id');
        
        require_once APP_PATH . '/services/DispatchService.php';
        require_once APP_PATH . '/models/DeliveryOffer.php';
        $offerModel = new DeliveryOffer();
        
        // Touch heartbeat
        $this->partnerModel->touchHeartbeat($partnerId);
        
        // Sweep expired before getting current
        $dispatchService = new DispatchService();
        $dispatchService->sweepExpired();
        
        $offers = $offerModel->getPendingForPartner($partnerId);
        
        header('Content-Type: application/json');
        
        $results = [];
        foreach ($offers as $offer) {
            $offerModel->touchSeen($offer['order_id'], $partnerId);
            $results[] = [
                'order_id'         => (int) $offer['order_id'],
                'order_number'     => $offer['order_number'],
                'kitchen_name'     => $offer['kitchen_name'],
                'kitchen_address'  => $offer['kitchen_address'],
                'customer_address' => $offer['delivery_address'] . ', ' . $offer['delivery_city'],
                'total_amount'     => $offer['total_amount'],
                'offer_type'       => $offer['offer_type'] ?? 'cascade',
                'dispatch_status'  => $offer['dispatch_status'] ?? 'cascading',
                'round'            => (int) ($offer['round'] ?? 1),
                'expires_in'       => (int) ($offer['seconds_left'] ?? 25)
            ];
        }
        
        echo json_encode(['offers' => $results]);
    }

    public function acceptOffer(): void {
        Middleware::deliveryAuth();
        Middleware::verifyCsrf();
        $partnerId = (int) Session::get('delivery_partner_id');
        $orderId = (int) ($_POST['order_id'] ?? 0);
        
        require_once APP_PATH . '/services/DispatchService.php';
        require_once APP_PATH . '/models/DeliveryOffer.php';
        $dispatchService = new DispatchService();
        
        $claimed = $dispatchService->claim($orderId, $partnerId);
        
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => $claimed, 'message' => $claimed ? 'Accepted!' : 'Taken by another rider']);
            exit;
        }

        if ($claimed) {
            Session::setFlash('success', 'Order accepted successfully!');
        } else {
            Session::setFlash('danger', 'Taken by another rider.');
        }
        $this->redirect('/delivery/dashboard');
    }

    public function declineOffer(): void {
        Middleware::deliveryAuth();
        Middleware::verifyCsrf();
        $partnerId = (int) Session::get('delivery_partner_id');
        $orderId = (int) ($_POST['order_id'] ?? 0);
        
        require_once APP_PATH . '/services/DispatchService.php';
        require_once APP_PATH . '/models/DeliveryOffer.php';
        $dispatchService = new DispatchService();
        
        $dispatchService->decline($orderId, $partnerId);
        
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }
        $this->redirect('/delivery/dashboard');
    }

    // ─── Order Management ────────────────────────────────────────────────────

    /**
     * View a specific assigned order
     * GET /delivery/order/{id}
     */
    public function viewOrder(string $id): void {
        Middleware::deliveryAuth();

        $orderId   = (int) $id;
        $partnerId = (int) Session::get('delivery_partner_id');

        $order = $this->orderModel->findWithDeliveryDetails($orderId);

        if (!$order || (int)$order['delivery_partner_id'] !== $partnerId) {
            Session::setFlash('danger', 'Order not found or not assigned to you.');
            $this->redirect('/delivery/dashboard');
            return;
        }

        require_once APP_PATH . '/models/OrderItem.php';
        $orderItemModel = new OrderItem();
        $orderItems = $orderItemModel->findByOrderId($orderId);

        $this->render('delivery/order_detail', [
            'title'      => 'Delivery Order #' . $order['order_number'],
            'order'      => $order,
            'orderItems' => $orderItems,
        ]);
    }

    /**
     * Accept an assigned order
     * POST /delivery/order/{id}/accept
     */
    public function acceptOrder(string $id): void {
        Middleware::deliveryAuth();
        Middleware::verifyCsrf();

        $orderId   = (int) $id;
        $partnerId = (int) Session::get('delivery_partner_id');

        $order = $this->orderModel->find($orderId);
        if (!$order || (int)($order['delivery_partner_id'] ?? 0) !== $partnerId) {
            Session::setFlash('danger', 'Order not found or not assigned to you.');
            $this->redirect('/delivery/dashboard');
            return;
        }

        if ($order['delivery_status'] !== DELIVERY_STATUS_ASSIGNED) {
            Session::setFlash('danger', 'Invalid status transition. Order must be in "Assigned" status.');
            $this->redirect('/delivery/order/' . $orderId);
            return;
        }

        $this->orderModel->updateDeliveryStatus($orderId, DELIVERY_STATUS_ACCEPTED);
        $this->notifyOrderParties($order, [
            'chef'  => ['Rider Heading to Kitchen', 'Rider confirmed order #' . $order['order_number'] . ' and is on the way to your kitchen.'],
            'admin' => ['Rider Confirmed', 'Rider confirmed order #' . $order['order_number'] . ' and is heading to the kitchen.'],
        ]);
        Session::setFlash('success', 'Order #' . $order['order_number'] . ' accepted! Head to the kitchen for pickup.');
        $this->redirect('/delivery/order/' . $orderId);
    }

    /**
     * Mark order as picked up from kitchen
     * POST /delivery/order/{id}/pickup
     */
    public function pickupOrder(string $id): void {
        Middleware::deliveryAuth();
        Middleware::verifyCsrf();

        $orderId   = (int) $id;
        $partnerId = (int) Session::get('delivery_partner_id');

        $order = $this->orderModel->find($orderId);
        if (!$order || (int)($order['delivery_partner_id'] ?? 0) !== $partnerId) {
            Session::setFlash('danger', 'Order not found or not assigned to you.');
            $this->redirect('/delivery/dashboard');
            return;
        }

        if ($order['delivery_status'] !== DELIVERY_STATUS_ACCEPTED) {
            Session::setFlash('danger', 'You must accept the order before marking it as picked up.');
            $this->redirect('/delivery/order/' . $orderId);
            return;
        }

        $this->orderModel->updateDeliveryStatus($orderId, DELIVERY_STATUS_PICKED_UP);
        $this->notifyOrderParties($order, [
            'admin'    => ['Order Picked Up', 'Order #' . $order['order_number'] . ' picked up by rider.'],
        ]);
        Session::setFlash('success', 'Order picked up! Mark as "Out for Delivery" when you start.');
        $this->redirect('/delivery/order/' . $orderId);
    }

    /**
     * Mark order as out for delivery
     * POST /delivery/order/{id}/out-for-delivery
     */
    public function outForDelivery(string $id): void {
        Middleware::deliveryAuth();
        Middleware::verifyCsrf();

        $orderId   = (int) $id;
        $partnerId = (int) Session::get('delivery_partner_id');

        $order = $this->orderModel->find($orderId);
        if (!$order || (int)($order['delivery_partner_id'] ?? 0) !== $partnerId) {
            Session::setFlash('danger', 'Order not found or not assigned to you.');
            $this->redirect('/delivery/dashboard');
            return;
        }

        if ($order['delivery_status'] !== DELIVERY_STATUS_PICKED_UP) {
            Session::setFlash('danger', 'You must pick up the order before starting delivery.');
            $this->redirect('/delivery/order/' . $orderId);
            return;
        }

        $this->orderModel->updateDeliveryStatus($orderId, DELIVERY_STATUS_OUT_FOR_DELIVERY, ORDER_STATUS_OUT_FOR_DELIVERY);

        // Notify customer
        try {
            require_once APP_PATH . '/models/Notification.php';
            $notif = new Notification();
            $notif->createForUser(
                (int) $order['customer_id'],
                'customer',
                'Your order is on its way!',
                'Order #' . $order['order_number'] . ' is out for delivery. Get ready!',
                'order_update',
                '/order/track/' . $orderId
            );
        } catch (Throwable $e) {
            error_log('[Kravyo] Out-for-delivery notification failed for order ' . $orderId . ': ' . $e->getMessage());
        }

        $this->notifyOrderParties($order, [
            'admin' => ['Out for Delivery', 'Order #' . $order['order_number'] . ' is out for delivery.'],
        ]);

        Session::setFlash('success', 'Order is now out for delivery! Head to the customer.');
        $this->redirect('/delivery/order/' . $orderId);
    }

    /**
     * Complete delivery with OTP verification
     * POST /delivery/order/{id}/complete
     */
    public function completeDelivery(string $id): void {
        Middleware::deliveryAuth();
        Middleware::verifyCsrf();

        $orderId   = (int) $id;
        $partnerId = (int) Session::get('delivery_partner_id');

        $order = $this->orderModel->find($orderId);
        if (!$order || (int)($order['delivery_partner_id'] ?? 0) !== $partnerId) {
            Session::setFlash('danger', 'Order not found or not assigned to you.');
            $this->redirect('/delivery/dashboard');
            return;
        }

        if ($order['delivery_status'] !== DELIVERY_STATUS_OUT_FOR_DELIVERY) {
            Session::setFlash('danger', 'Order must be "Out for Delivery" before completing.');
            $this->redirect('/delivery/order/' . $orderId);
            return;
        }

        // Verify OTP
        $submittedOtp = trim($_POST['delivery_otp'] ?? '');
        if (empty($submittedOtp) || !$this->orderModel->verifyDeliveryOtp($orderId, $submittedOtp)) {
            Session::setFlash('danger', 'Invalid delivery OTP. Please ask the customer for the correct code.');
            $this->redirect('/delivery/order/' . $orderId);
            return;
        }

        // Mark as delivered
        $this->orderModel->updateDeliveryStatus($orderId, DELIVERY_STATUS_DELIVERED, ORDER_STATUS_DELIVERED);

        // Update payment status for COD
        if ($order['payment_method'] === PAYMENT_METHOD_COD) {
            $this->orderModel->updatePaymentStatus($orderId, PAYMENT_STATUS_COMPLETED);
        }

        // Update delivery partner stats
        $this->partnerModel->incrementDeliveryStats($partnerId, (float)$order['total_amount']);
        $this->partnerModel->setIsBusy($partnerId, 0);

        // Ultra Processing: Complete Tiffin Delivery 
        if (($order['order_type'] ?? 'standard') === 'tiffin' && !empty($order['subscription_delivery_id'])) {
            require_once APP_PATH . '/models/SubscriptionDelivery.php';
            $sdModel = new SubscriptionDelivery();
            $sdModel->updateStatus((int)$order['subscription_delivery_id'], 'delivered', (int)$order['kitchen_id']);
        }

        // Notify customer
        try {
            require_once APP_PATH . '/models/Notification.php';
            $notif = new Notification();
            $notif->createForUser(
                (int) $order['customer_id'],
                'customer',
                'Order Delivered! 🎉',
                'Your order #' . $order['order_number'] . ' has been delivered. Enjoy your meal!',
                'order_update',
                '/order/track/' . $orderId
            );
        } catch (Throwable $e) {
            error_log('[Kravyo] Delivered notification failed for order ' . $orderId . ': ' . $e->getMessage());
        }

        $this->notifyOrderParties($order, [
            'admin' => ['Order Delivered', 'Order #' . $order['order_number'] . ' delivered (' . format_currency((float) $order['total_amount']) . ').'],
        ]);

        Session::setFlash('success', '✅ Order #' . $order['order_number'] . ' delivered successfully!');
        $this->redirect('/delivery/dashboard');
    }

    /**
     * Mark delivery as failed (customer unavailable)
     * POST /delivery/order/{id}/failed
     */
    public function failedDelivery(string $id): void {
        Middleware::deliveryAuth();
        Middleware::verifyCsrf();

        $orderId   = (int) $id;
        $partnerId = (int) Session::get('delivery_partner_id');

        $order = $this->orderModel->find($orderId);
        if (!$order || (int)($order['delivery_partner_id'] ?? 0) !== $partnerId) {
            Session::setFlash('danger', 'Order not found or not assigned to you.');
            $this->redirect('/delivery/dashboard');
            return;
        }

        $allowedStatuses = [DELIVERY_STATUS_ACCEPTED, DELIVERY_STATUS_PICKED_UP, DELIVERY_STATUS_OUT_FOR_DELIVERY];
        if (!in_array($order['delivery_status'], $allowedStatuses)) {
            Session::setFlash('danger', 'Cannot mark this order as failed from current status.');
            $this->redirect('/delivery/order/' . $orderId);
            return;
        }

        $reason = sanitize(trim($_POST['fail_reason'] ?? 'Customer unavailable'));

        $this->orderModel->updateDeliveryStatus($orderId, DELIVERY_STATUS_FAILED, ORDER_STATUS_DELIVERY_FAILED, $reason);
        $this->partnerModel->setIsBusy($partnerId, 0);

        // Notify customer
        try {
            require_once APP_PATH . '/models/Notification.php';
            $notif = new Notification();
            $notif->createForUser(
                (int) $order['customer_id'],
                'customer',
                'Delivery Issue',
                'We could not deliver your order #' . $order['order_number'] . '. Reason: ' . $reason . '. Our admin will resolve this.',
                'order_update',
                '/order/track/' . $orderId
            );
        } catch (Throwable $e) {
            error_log('[Kravyo] Delivery-failed notification failed for order ' . $orderId . ': ' . $e->getMessage());
        }

        $this->notifyOrderParties($order, [
            'chef'  => ['Delivery Failed', 'Delivery of order #' . $order['order_number'] . ' failed. Reason: ' . $reason],
            'admin' => ['Delivery Failed — Action Needed', 'Order #' . $order['order_number'] . ' could not be delivered. Reason: ' . $reason],
        ], 'system_alert');

        Session::setFlash('warning', 'Order #' . $order['order_number'] . ' marked as delivery failed. Admin has been notified.');
        $this->redirect('/delivery/dashboard');
    }

    /**
     * Send toast notifications to the parties of an order.
     * @param array $order    Order row (needs id, customer_id, kitchen_id)
     * @param array $messages ['customer'|'chef'|'admin' => [title, message]]
     */
    private function notifyOrderParties(array $order, array $messages, string $type = 'order_update'): void {
        try {
            require_once APP_PATH . '/models/Notification.php';
            $notif   = new Notification();
            $orderId = (int) $order['id'];

            if (isset($messages['customer']) && !empty($order['customer_id'])) {
                [$t, $m] = $messages['customer'];
                $notif->createForUser((int) $order['customer_id'], 'customer', $t, $m, $type, '/order/track/' . $orderId);
            }
            if (isset($messages['chef']) && !empty($order['kitchen_id'])) {
                [$t, $m] = $messages['chef'];
                $notif->notifyKitchenOwner((int) $order['kitchen_id'], $t, $m, $type, '/chef/orders');
            }
        } catch (Throwable $e) {
            error_log('[Kravyo] notifyOrderParties failed for order ' . ($order['id'] ?? '?') . ': ' . $e->getMessage());
        }
    }

    // ─── History ─────────────────────────────────────────────────────────────

    /**
     * Delivery history
     * GET /delivery/history
     */
    public function history(): void {
        Middleware::deliveryAuth();

        $partnerId = (int) Session::get('delivery_partner_id');
        $deliveries = $this->orderModel->findDeliveryHistory($partnerId);

        $this->render('delivery/history', [
            'title'      => 'Delivery History',
            'deliveries' => $deliveries,
        ]);
    }
}
