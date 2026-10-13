<?php
/**
 * Kravyo - Order Controller (Checkout, Order Placement & Tracking)
 * Phase 6: Complete order lifecycle from checkout to delivery tracking
 */

class OrderController extends Controller {

    /**
     * Display checkout page with address selection & payment method
     * GET /checkout
     */
    public function checkout(): void {
        Middleware::role(ROLE_CUSTOMER);

        // Verify cart is not empty
        $cart = Session::get('cart', []);
        if (empty($cart['items'])) {
            Session::setFlash('warning', 'Your cart is empty. Add some dishes before checking out.');
            $this->redirect('/menu');
            return;
        }

        require_once CONFIG_PATH . '/razorpay.php';
        require_once APP_PATH . '/models/Address.php';
        $addressModel = new Address();

        $userId = (int) Session::get('user_id');
        $addresses = $addressModel->findByUserId($userId);

        // Calculate cart total
        $cartTotal = 0;
        foreach ($cart['items'] as $item) {
            $cartTotal += $item['price'] * $item['quantity'];
        }

        $this->render('customer/checkout', [
            'title'     => 'Checkout — Place Your Order',
            'cart'      => $cart,
            'cartTotal' => round($cartTotal, 2),
            'addresses' => $addresses,
        ]);
    }

    /**
     * Place order from cart — creates order + order items, clears cart
     * POST /order/place
     */
    public function placeOrder(): void {
        Middleware::role(ROLE_CUSTOMER);
        Middleware::verifyCsrf();

        // Verify cart
        $cart = Session::get('cart', []);
        if (empty($cart['items'])) {
            Session::setFlash('danger', 'Your cart is empty.');
            $this->redirect('/menu');
            return;
        }

        require_once APP_PATH . '/models/Order.php';
        require_once APP_PATH . '/models/OrderItem.php';
        require_once APP_PATH . '/models/Address.php';

        $orderModel = new Order();
        $orderItemModel = new OrderItem();
        $addressModel = new Address();

        $userId = (int) Session::get('user_id');
        $addressId = (int) ($_POST['address_id'] ?? 0);
        $paymentMethod = in_array($_POST['payment_method'] ?? '', ['cod', 'online']) ? $_POST['payment_method'] : 'cod';
        $specialInstructions = sanitize($_POST['special_instructions'] ?? '');

        // Handle new address creation
        if ($addressId === 0 && !empty($_POST['new_street_address'])) {
            $apartment = sanitize(trim($_POST['new_apartment'] ?? ''));
            $street    = sanitize(trim($_POST['new_street_address'] ?? ''));
            $fullStreetAddress = !empty($apartment) ? ($apartment . ', ' . $street) : $street;

            $newAddressData = [
                'user_id'        => $userId,
                'address_type'   => in_array($_POST['new_address_type'] ?? '', ['Home', 'Work', 'Other']) ? $_POST['new_address_type'] : 'Home',
                'apartment'      => $apartment ?: null,
                'street_address' => $fullStreetAddress,
                'landmark'       => sanitize($_POST['new_landmark'] ?? ''),
                'city'           => sanitize($_POST['new_city'] ?? ''),
                'pincode'        => sanitize($_POST['new_pincode'] ?? ''),
                'latitude'       => isset($_POST['delivery_lat']) && is_numeric($_POST['delivery_lat']) ? (float)$_POST['delivery_lat'] : null,
                'longitude'      => isset($_POST['delivery_lng']) && is_numeric($_POST['delivery_lng']) ? (float)$_POST['delivery_lng'] : null,
            ];

            if (empty($newAddressData['street_address']) || empty($newAddressData['city']) || empty($newAddressData['pincode'])) {
                Session::setFlash('danger', 'Please provide a complete delivery address.');
                $this->redirect('/checkout');
                return;
            }

            $addressId = (int) $addressModel->create($newAddressData);
        }

        // Validate address belongs to user
        $address = $addressModel->findByIdAndUserId($addressId, $userId);
        if (!$address) {
            Session::setFlash('danger', 'Invalid delivery address selected.');
            $this->redirect('/checkout');
            return;
        }

        // Verify stock availability
        require_once APP_PATH . '/models/MenuItem.php';
        $menuItemModel = new MenuItem();
        foreach ($cart['items'] as $item) {
            if (empty($item['is_zero_waste'])) {
                $dish = $menuItemModel->find((int)$item['menu_item_id']);
                if (!$dish || !$dish['is_available']) {
                    if (!empty($_POST['is_ajax'])) {
                        $this->json(['success' => false, 'message' => "Sorry, '" . sanitize($item['item_name']) . "' is currently out of stock."]);
                        return;
                    }
                    Session::setFlash('danger', "Sorry, '" . sanitize($item['item_name']) . "' is currently out of stock. Please remove it from your cart.");
                    $this->redirect('/cart');
                    return;
                }
            }
        }

        // Calculate total
        $totalAmount = 0;
        foreach ($cart['items'] as $item) {
            $totalAmount += $item['price'] * $item['quantity'];
        }
        $totalAmount = round($totalAmount, 2);

        // Create the order
        $orderData = [
            'order_number'        => $orderModel->generateOrderNumber(),
            'customer_id'         => $userId,
            'kitchen_id'          => (int) $cart['kitchen_id'],
            'address_id'          => $addressId,
            'delivery_latitude'   => isset($address['latitude']) ? (float)$address['latitude'] : null,
            'delivery_longitude'  => isset($address['longitude']) ? (float)$address['longitude'] : null,
            'total_amount'        => $totalAmount,
            'payment_method'      => $paymentMethod,
            'payment_status'      => $paymentMethod === 'cod' ? PAYMENT_STATUS_PENDING : PAYMENT_STATUS_PENDING,
            'order_status'        => ORDER_STATUS_PENDING,
            'special_instructions'=> $specialInstructions,
        ];

        try {
            $orderId = (int) $orderModel->create($orderData);

            // Create order items from cart
            $orderItemModel->createFromCart($orderId, $cart['items']);

            // If Razorpay Online Payment
            if ($paymentMethod === 'online' && !empty($_POST['is_ajax'])) {
                require_once CONFIG_PATH . '/razorpay.php';
                $ch = curl_init('https://api.razorpay.com/v1/orders');
                curl_setopt($ch, CURLOPT_USERPWD, RAZORPAY_KEY_ID . ':' . RAZORPAY_KEY_SECRET);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                    'amount'   => (int) ($totalAmount * 100), // convert to paise
                    'currency' => 'INR',
                    'receipt'  => $orderData['order_number']
                ]));
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                $rzpResponse = curl_exec($ch);
                curl_close($ch);

                $rzpData = json_decode($rzpResponse, true);
                
                if (isset($rzpData['id'])) {
                    $this->json([
                        'success'           => true,
                        'kravyo_order_id'   => $orderId,
                        'order_number'      => $orderData['order_number'],
                        'razorpay_order_id' => $rzpData['id'],
                        'razorpay_amount'   => (int) ($totalAmount * 100)
                    ]);
                    return;
                } else {
                    $this->json(['success' => false, 'message' => 'Failed to create payment on Razorpay.']);
                    return;
                }
            }

            // --- Regular COD Checkout Flow ---

            // Decrement Zero Waste listing stock for any deal items in this order
            try {
                require_once APP_PATH . '/models/ZeroWasteItem.php';
                $zwModel = new ZeroWasteItem();
                foreach ($cart['items'] as $item) {
                    if (!empty($item['is_zero_waste']) && !empty($item['zero_waste_id'])) {
                        $zwModel->decrementQuantity((int) $item['zero_waste_id'], (int) $item['quantity']);
                    }
                }
            } catch (Exception $ignored) {}

            // Clear the cart
            Session::remove('cart');

            // Phase 10: Refresh AI recommendation preference profile
            try {
                require_once APP_PATH . '/models/Recommendation.php';
                $recModel = new Recommendation();
                $recModel->refreshPreferences($userId);
            } catch (Exception $ignored) {
                // Non-critical; don't block the order success flow
            }

            // Module 1.13 & 2.8 — Fire notifications
            try {
                require_once APP_PATH . '/models/Notification.php';
                $notifModel = new Notification();

                // 1.13: Notify the customer that their order was placed
                $notifModel->createForUser(
                    $userId,
                    'customer',
                    'Order Placed Successfully!',
                    'Your order #' . $orderData['order_number'] . ' has been placed. Your home chef will confirm it shortly.',
                    'order_update',
                    '/order/track/' . $orderId
                );

                // 2.8: Notify the chef about the new incoming order
                $kitchenId = (int) $cart['kitchen_id'];
                require_once APP_PATH . '/models/Kitchen.php';
                $kitchenModel = new Kitchen();
                $kitchen = $kitchenModel->find($kitchenId);
                if ($kitchen) {
                    $chefUserId = (int) $kitchen['user_id'];
                    $notifModel->createForUser(
                        $chefUserId,
                        'chef',
                        'New Order Received!',
                        'You have a new order #' . $orderData['order_number'] . ' worth ' . format_currency($totalAmount) . '. Please accept it promptly.',
                        'order_update',
                        '/chef/orders'
                    );
                }

            } catch (Throwable $ignored) {
                // Non-critical; don't block the order flow
            }

            Session::setFlash('success', 'Order placed successfully! Your order number is ' . $orderData['order_number']);
            $this->redirect('/order/track/' . $orderId);

        } catch (Exception $e) {
            if (!empty($_POST['is_ajax'])) {
                $this->json(['success' => false, 'message' => 'System error placing order.']);
                return;
            }
            Session::setFlash('danger', 'Failed to place order. Please try again.');
            $this->redirect('/checkout');
        }
    }

    /**
     * Verify Razorpay Payment Signature
     * POST /order/verify-payment
     */
    public function verifyPayment(): void {
        Middleware::verifyCsrf();
        
        $paymentId = $_POST['razorpay_payment_id'] ?? '';
        $orderId   = $_POST['razorpay_order_id'] ?? '';
        $signature = $_POST['razorpay_signature'] ?? '';
        $kravyoId  = (int)($_POST['kravyo_order_id'] ?? 0);

        require_once CONFIG_PATH . '/razorpay.php';
        
        $generatedSignature = hash_hmac('sha256', $orderId . "|" . $paymentId, RAZORPAY_KEY_SECRET);

        if (hash_equals($generatedSignature, $signature)) {
            // Payment Success
            require_once APP_PATH . '/models/Order.php';
            $orderModel = new Order();
            
            // Mark as paid
            $orderModel->updatePaymentStatus($kravyoId, PAYMENT_STATUS_COMPLETED);
            
            // Do the post-checkout tasks we skipped during AJAX creation
            $cart = Session::get('cart', ['items' => []]);
            
            // Decrement ZW items
            try {
                require_once APP_PATH . '/models/ZeroWasteItem.php';
                $zwModel = new ZeroWasteItem();
                foreach ($cart['items'] as $item) {
                    if (!empty($item['is_zero_waste']) && !empty($item['zero_waste_id'])) {
                        $zwModel->decrementQuantity((int) $item['zero_waste_id'], (int) $item['quantity']);
                    }
                }
            } catch (Exception $ignored) {}

            Session::remove('cart');
            
            // Fire Notifications
            try {
                $order = $orderModel->findById($kravyoId);
                require_once APP_PATH . '/models/Notification.php';
                $notifModel = new Notification();
                $notifModel->createForUser((int)$order['customer_id'], 'customer', 'Payment Successful!', 'Your online payment for order #' . $order['order_number'] . ' was successful.', 'order_update', '/order/track/' . $kravyoId);
                
                require_once APP_PATH . '/models/Kitchen.php';
                $kitchenModel = new Kitchen();
                $kitchen = $kitchenModel->find((int)$order['kitchen_id']);
                if ($kitchen) {
                    $notifModel->createForUser((int)$kitchen['user_id'], 'chef', 'New Paid Order!', 'You have a new PAID order #' . $order['order_number'] . ' worth ' . format_currency($order['total_amount']) . '.', 'order_update', '/chef/orders');
                }
            } catch (Exception $ignored) {}

            Session::setFlash('success', 'Payment successful! Order confirmed.');
            $this->json(['success' => true]);
        } else {
            $this->json(['success' => false, 'message' => 'Payment verification failed.']);
        }
    }

    /**
     * Track a specific order with timeline visualization
     * GET /order/track/{id}
     */
    public function trackOrder(string $id): void {
        Middleware::role(ROLE_CUSTOMER);

        require_once APP_PATH . '/models/Order.php';
        require_once APP_PATH . '/models/OrderItem.php';

        $orderModel = new Order();
        $orderItemModel = new OrderItem();

        $orderId = (int) $id;
        $userId = (int) Session::get('user_id');

        // Security: ensure order belongs to this customer
        if (!$orderModel->belongsToCustomer($orderId, $userId)) {
            Session::setFlash('danger', 'Order not found.');
            $this->redirect('/orders/history');
            return;
        }

        $order = $orderModel->findWithDetails($orderId);
        if (!$order) {
            http_response_code(404);
            $this->render('errors/404', ['title' => 'Order Not Found']);
            return;
        }

        $orderItems = $orderItemModel->findByOrderId($orderId);

        // ── Simplified Customer-Facing Timeline ──────────────────────────────────
        // The backend maintains granular statuses (pending, accepted, preparing,
        // out_for_delivery, delivered, cancelled) for chef workflow.  The customer
        // sees a cleaner 5-step progress:
        //   1. Awaiting Confirmation (backend: pending)
        //   2. Order Confirmed       (backend: accepted)
        //   3. Preparing             (backend: preparing, ready_for_pickup)
        //   4. Out for Delivery      (backend: out_for_delivery)
        //   5. Delivered             (backend: delivered)

        $statusTimeline = [
            'placed'           => ['label' => 'Awaiting Confirmation', 'icon' => 'bi-clock-history',   'desc' => 'Waiting for the chef to accept your order.'],
            'confirmed'        => ['label' => 'Order Confirmed',       'icon' => 'bi-bag-check-fill',  'desc' => 'Your order has been accepted.'],
            'preparing'        => ['label' => 'Preparing',             'icon' => 'bi-fire',            'desc' => 'Your meal is being prepared with care.'],
            'out_for_delivery' => ['label' => 'Out for Delivery',      'icon' => 'bi-bicycle',         'desc' => 'Your food is on its way to you!'],
            'delivered'        => ['label' => 'Delivered',             'icon' => 'bi-house-check-fill','desc' => 'Your order has been delivered. Enjoy your meal!'],
        ];

        // Map the backend order_status to the customer-facing timeline key
        $backendToCustomerStep = [
            'pending'          => 'placed',
            'accepted'         => 'confirmed',
            'preparing'        => 'preparing',
            'ready_for_pickup' => 'preparing',
            'out_for_delivery' => 'out_for_delivery',
            'delivered'        => 'delivered',
        ];

        $customerStep = $backendToCustomerStep[$order['order_status']] ?? 'placed';

        $this->render('customer/order_track', [
            'title'          => 'Track Order #' . $order['order_number'],
            'order'          => $order,
            'orderItems'     => $orderItems,
            'statusTimeline' => $statusTimeline,
            'customerStep'   => $customerStep,
        ]);
    }

    /**
     * Get Delivery Partner's Live Location
     * GET /order/location/{id}
     */
    public function getDeliveryLocation(string $id): void {
        Middleware::role(ROLE_CUSTOMER);
        header('Content-Type: application/json');

        require_once APP_PATH . '/models/Order.php';
        require_once APP_PATH . '/models/DeliveryPartner.php';
        
        $orderModel = new Order();
        $orderId = (int) $id;
        $userId = (int) Session::get('user_id');

        if (!$orderModel->belongsToCustomer($orderId, $userId)) {
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $order = $orderModel->findWithDetails($orderId);
        if (!$order || empty($order['delivery_partner_id']) || in_array($order['order_status'], ['delivered', 'cancelled'])) {
            echo json_encode(['error' => 'Location tracking not available for this state']);
            return;
        }

        $dpModel = new DeliveryPartner();
        $partner = $dpModel->find($order['delivery_partner_id']);

        if ($partner && $partner['current_latitude'] && $partner['current_longitude']) {
            echo json_encode([
                'latitude' => $partner['current_latitude'],
                'longitude' => $partner['current_longitude'],
                'updated_at' => $partner['location_updated_at'],
                'delivery_status' => $order['delivery_status'] ?? ''
            ]);
        } else {
            echo json_encode(['error' => 'Location not available']);
        }
    }

    /**
     * Get Real-time order & dispatch status for customer tracking polling
     * GET /order/status/{id}
     */
    public function getOrderStatus(string $id): void {
        Middleware::role(ROLE_CUSTOMER);
        header('Content-Type: application/json');

        require_once APP_PATH . '/models/Order.php';
        $orderModel = new Order();
        $orderId = (int) $id;
        $userId = (int) Session::get('user_id');

        if (!$orderModel->belongsToCustomer($orderId, $userId)) {
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $order = $orderModel->findWithDetails($orderId);
        if (!$order) {
            echo json_encode(['error' => 'Order not found']);
            return;
        }

        echo json_encode([
            'order_id' => (int) $order['id'],
            'order_status' => $order['order_status'],
            'dispatch_status' => $order['dispatch_status'],
            'delivery_partner_id' => $order['delivery_partner_id'] ? (int) $order['delivery_partner_id'] : null
        ]);
    }

    public function history(): void {
        Middleware::role(ROLE_CUSTOMER);

        require_once APP_PATH . '/models/Order.php';
        require_once APP_PATH . '/models/OrderItem.php';

        $orderModel = new Order();
        $orderItemModel = new OrderItem();

        $userId = (int) Session::get('user_id');
        $dateFilter = !empty($_GET['date']) ? trim($_GET['date']) : null;
        
        $orders = $orderModel->findByCustomerId($userId, null, $dateFilter);

        // Attach item count to each order
        foreach ($orders as &$order) {
            $order['item_count'] = $orderItemModel->countByOrderId((int) $order['id']);
        }
        unset($order);

        $this->render('customer/order_history', [
            'title'      => 'My Orders',
            'orders'     => $orders,
            'dateFilter' => $dateFilter
        ]);
    }

    /**
     * Cancel an order (only if status is still 'pending')
     * POST /order/cancel
     */
    public function cancelOrder(): void {
        Middleware::role(ROLE_CUSTOMER);
        Middleware::verifyCsrf();

        require_once APP_PATH . '/models/Order.php';
        $orderModel = new Order();

        $orderId = (int) ($_POST['order_id'] ?? 0);
        $userId = (int) Session::get('user_id');

        // Security check
        if (!$orderModel->belongsToCustomer($orderId, $userId)) {
            Session::setFlash('danger', 'Order not found.');
            $this->redirect('/orders/history');
            return;
        }

        $order = $orderModel->find($orderId);
        if (!$order) {
            Session::setFlash('danger', 'Order not found.');
            $this->redirect('/orders/history');
            return;
        }

        // Can only cancel if status is 'pending'
        if ($order['order_status'] !== ORDER_STATUS_PENDING) {
            Session::setFlash('warning', 'This order can no longer be cancelled. The kitchen has already started processing it.');
            $this->redirect('/order/track/' . $orderId);
            return;
        }

        $orderModel->updateStatus($orderId, ORDER_STATUS_CANCELLED);

        // Notify chef + admin that the customer cancelled
        try {
            require_once APP_PATH . '/models/Notification.php';
            $notifModel = new Notification();
            $notifModel->notifyKitchenOwner(
                (int) $order['kitchen_id'],
                'Order Cancelled by Customer',
                'Order #' . $order['order_number'] . ' was cancelled by the customer.'
            );
        } catch (Throwable $e) {
            error_log('[Kravyo] Cancel notification failed for order ' . $orderId . ': ' . $e->getMessage());
        }

        Session::setFlash('success', 'Order #' . $order['order_number'] . ' has been cancelled.');
        $this->redirect('/orders/history');
    }

    /**
     * Submit a customer review for a delivered order
     * POST /review/submit
     */
    public function submitReview(): void {
        Middleware::role(ROLE_CUSTOMER);
        Middleware::verifyCsrf();

        require_once APP_PATH . '/models/Order.php';
        require_once APP_PATH . '/models/Review.php';

        $orderModel  = new Order();
        $reviewModel = new Review();

        $orderId  = (int) ($_POST['order_id'] ?? 0);
        $rating   = (int) ($_POST['rating']   ?? 0);
        $text     = sanitize($_POST['review_text'] ?? '');
        $userId   = (int) Session::get('user_id');

        // Validate rating range
        if ($rating < 1 || $rating > 5) {
            Session::setFlash('danger', 'Please select a star rating between 1 and 5.');
            $this->redirect('/order/track/' . $orderId);
            return;
        }

        // Security: order must belong to this customer
        if (!$orderModel->belongsToCustomer($orderId, $userId)) {
            Session::setFlash('danger', 'Order not found.');
            $this->redirect('/orders/history');
            return;
        }

        $order = $orderModel->findWithDetails($orderId);

        // Must be delivered
        if (!$order || $order['order_status'] !== ORDER_STATUS_DELIVERED) {
            Session::setFlash('warning', 'You can only review a delivered order.');
            $this->redirect('/order/track/' . $orderId);
            return;
        }

        // Prevent duplicate review
        if ($reviewModel->hasReviewedOrder($orderId)) {
            Session::setFlash('info', 'You have already reviewed this order.');
            $this->redirect('/order/track/' . $orderId);
            return;
        }

        try {
            $reviewModel->submitReview([
                'order_id'    => $orderId,
                'customer_id' => $userId,
                'kitchen_id'  => (int) $order['kitchen_id'],
                'rating'      => $rating,
                'review_text' => $text,
            ]);
            Session::setFlash('success', '⭐ Thank you for your review! Your feedback helps the chef improve.');
        } catch (Exception $e) {
            Session::setFlash('danger', 'Failed to submit review. Please try again.');
        }

        $this->redirect('/order/track/' . $orderId);
    }
}

