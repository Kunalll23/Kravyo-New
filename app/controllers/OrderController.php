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
        $paymentMethod = in_array($_POST['payment_method'] ?? '', ['cod', 'upi']) ? $_POST['payment_method'] : 'cod';
        $specialInstructions = sanitize($_POST['special_instructions'] ?? '');

        // Handle new address creation
        if ($addressId === 0 && !empty($_POST['new_street_address'])) {
            $newAddressData = [
                'user_id'        => $userId,
                'address_type'   => in_array($_POST['new_address_type'] ?? '', ['Home', 'Work', 'Other']) ? $_POST['new_address_type'] : 'Home',
                'street_address' => sanitize($_POST['new_street_address'] ?? ''),
                'landmark'       => sanitize($_POST['new_landmark'] ?? ''),
                'city'           => sanitize($_POST['new_city'] ?? ''),
                'pincode'        => sanitize($_POST['new_pincode'] ?? ''),
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

            // Clear the cart
            Session::remove('cart');

            Session::setFlash('success', 'Order placed successfully! Your order number is ' . $orderData['order_number']);
            $this->redirect('/order/track/' . $orderId);
        } catch (Exception $e) {
            Session::setFlash('danger', 'Failed to place order. Please try again.');
            $this->redirect('/checkout');
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

        // Define status timeline steps
        $statusTimeline = [
            'pending'          => ['label' => 'Order Placed',       'icon' => 'bi-bag-check',       'desc' => 'Your order has been placed and is waiting for the chef to accept.'],
            'accepted'         => ['label' => 'Accepted',           'icon' => 'bi-check2-circle',   'desc' => 'The chef has accepted your order.'],
            'preparing'        => ['label' => 'Preparing',          'icon' => 'bi-fire',            'desc' => 'Your meal is being prepared with care.'],
            'out_for_delivery' => ['label' => 'Out for Delivery',   'icon' => 'bi-bicycle',         'desc' => 'Your food is on its way to you!'],
            'delivered'        => ['label' => 'Delivered',          'icon' => 'bi-house-check',     'desc' => 'Your order has been delivered. Enjoy your meal!'],
        ];

        $this->render('customer/order_track', [
            'title'          => 'Track Order #' . $order['order_number'],
            'order'          => $order,
            'orderItems'     => $orderItems,
            'statusTimeline' => $statusTimeline,
        ]);
    }

    /**
     * Display customer order history
     * GET /orders/history
     */
    public function history(): void {
        Middleware::role(ROLE_CUSTOMER);

        require_once APP_PATH . '/models/Order.php';
        require_once APP_PATH . '/models/OrderItem.php';

        $orderModel = new Order();
        $orderItemModel = new OrderItem();

        $userId = (int) Session::get('user_id');
        $orders = $orderModel->findByCustomerId($userId);

        // Attach item count to each order
        foreach ($orders as &$order) {
            $order['item_count'] = $orderItemModel->countByOrderId((int) $order['id']);
        }
        unset($order);

        $this->render('customer/order_history', [
            'title'  => 'My Orders',
            'orders' => $orders,
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
        Session::setFlash('success', 'Order #' . $order['order_number'] . ' has been cancelled.');
        $this->redirect('/orders/history');
    }
}
