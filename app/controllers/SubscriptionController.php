<?php
/**
 * Kravyo - Subscription Controller (Customer-Facing Tiffin Subscriptions)
 * Phase 7: Browse plans, subscribe, manage subscriptions
 */

class SubscriptionController extends Controller {

    /**
     * Browse all available tiffin subscription plans
     * GET /subscriptions
     */
    public function browsePlans(): void {
        require_once APP_PATH . '/models/Subscription.php';
        require_once APP_PATH . '/models/Kitchen.php';

        $subscriptionModel = new Subscription();
        $kitchenModel = new Kitchen();

        // Gather filter parameters
        $filters = [
            'keyword'   => trim($_GET['q'] ?? ''),
            'plan_type' => trim($_GET['plan_type'] ?? ''),
            'city'      => trim($_GET['city'] ?? ''),
            'sort'      => $_GET['sort'] ?? 'newest',
        ];

        $plans = $subscriptionModel->findAllActivePlans($filters);
        $cities = $kitchenModel->getDistinctCities();

        $this->render('customer/subscriptions', [
            'title'   => 'Tiffin Subscription Plans',
            'plans'   => $plans,
            'cities'  => $cities,
            'filters' => $filters,
        ]);
    }

    /**
     * View a single subscription plan with details + subscribe form
     * GET /subscription/{id}
     */
    public function viewPlan(string $id): void {
        require_once APP_PATH . '/models/Subscription.php';
        require_once APP_PATH . '/models/Address.php';
        require_once APP_PATH . '/models/Review.php';

        $subscriptionModel = new Subscription();
        $reviewModel = new Review();

        $plan = $subscriptionModel->findWithKitchenDetails((int) $id);

        if (!$plan || !$plan['is_active']) {
            http_response_code(404);
            $this->render('errors/404', ['title' => 'Plan Not Found']);
            return;
        }

        // Get kitchen rating
        $avgRating = $reviewModel->getAverageRating((int) $plan['kitchen_id']);
        $reviewCount = $reviewModel->countByKitchenId((int) $plan['kitchen_id']);

        // Get customer addresses if logged in
        $addresses = [];
        if (Session::has('user_id')) {
            $addressModel = new Address();
            $addresses = $addressModel->findAll(['user_id' => Session::get('user_id')]);
        }

        $this->render('customer/subscription_detail', [
            'title'       => sanitize($plan['plan_name']) . ' — Tiffin Plan',
            'plan'        => $plan,
            'avgRating'   => $avgRating,
            'reviewCount' => $reviewCount,
            'addresses'   => $addresses,
        ]);
    }

    /**
     * Subscribe to a tiffin plan
     * POST /subscription/subscribe
     */
    public function subscribe(): void {
        Middleware::role(ROLE_CUSTOMER);
        Middleware::verifyCsrf();

        require_once APP_PATH . '/models/Subscription.php';
        require_once APP_PATH . '/models/CustomerSubscription.php';
        require_once APP_PATH . '/models/Address.php';

        $subscriptionModel = new Subscription();
        $customerSubModel = new CustomerSubscription();

        $customerId = (int) Session::get('user_id');
        $planId = (int) ($_POST['plan_id'] ?? 0);
        $addressId = (int) ($_POST['address_id'] ?? 0);
        $paymentMethod = $_POST['payment_method'] ?? 'cod';
        $specialInstructions = sanitize($_POST['special_instructions'] ?? '');

        // Validate plan exists and is active
        $plan = $subscriptionModel->findWithKitchenDetails($planId);
        if (!$plan || !$plan['is_active']) {
            Session::setFlash('danger', 'This subscription plan is no longer available.');
            $this->redirect('/subscriptions');
            return;
        }

        // Validate address
        $addressModel = new Address();
        $address = $addressModel->find($addressId);
        if (!$address || (int) $address['user_id'] !== $customerId) {
            Session::setFlash('danger', 'Please select a valid delivery address.');
            $this->redirect('/subscription/' . $planId);
            return;
        }

        // Check if customer already has an active subscription to this kitchen
        $existing = $customerSubModel->findActiveByCustomerAndKitchen($customerId, (int) $plan['kitchen_id']);
        if ($existing) {
            Session::setFlash('warning', 'You already have an active subscription with this kitchen (' . sanitize($existing['plan_name']) . '). Cancel it first to subscribe to a new plan.');
            $this->redirect('/my-subscriptions');
            return;
        }

        // Validate payment method
        $validPayments = ['cod', 'upi', 'card', 'netbanking'];
        if (!in_array($paymentMethod, $validPayments)) {
            $paymentMethod = 'cod';
        }

        // Calculate start and end dates
        $startDate = date('Y-m-d', strtotime('+1 day')); // starts tomorrow
        if ($plan['plan_type'] === 'weekly') {
            $endDate = date('Y-m-d', strtotime($startDate . ' +7 days'));
        } else {
            $endDate = date('Y-m-d', strtotime($startDate . ' +30 days'));
        }

        $data = [
            'customer_id'          => $customerId,
            'subscription_plan_id' => $planId,
            'kitchen_id'           => (int) $plan['kitchen_id'],
            'address_id'           => $addressId,
            'start_date'           => $startDate,
            'end_date'             => $endDate,
            'status'               => SUBSCRIPTION_STATUS_PENDING,
            'payment_method'       => $paymentMethod,
            'payment_status'       => ($paymentMethod === 'cod') ? PAYMENT_STATUS_PENDING : PAYMENT_STATUS_COMPLETED,
            'total_paid'           => (float) $plan['price'],
            'special_instructions' => $specialInstructions,
        ];

        try {
            $customerSubModel->create($data);
            Session::setFlash('success', 'You have successfully requested to subscribe to "' . sanitize($plan['plan_name']) . '"! It is currently pending Admin approval.');
            $this->redirect('/my-subscriptions');
        } catch (Exception $e) {
            Session::setFlash('danger', 'Failed to subscribe. Please try again.');
            $this->redirect('/subscription/' . $planId);
        }
    }

    /**
     * View customer's active and past subscriptions
     * GET /my-subscriptions
     */
    public function mySubscriptions(): void {
        Middleware::role(ROLE_CUSTOMER);

        require_once APP_PATH . '/models/CustomerSubscription.php';
        require_once APP_PATH . '/models/SubscriptionDelivery.php';
        
        $customerSubModel = new CustomerSubscription();
        $deliveryModel = new SubscriptionDelivery();

        $customerId = (int) Session::get('user_id');
        $allSubscriptions = $customerSubModel->findByCustomerId($customerId);

        $activeSubscriptions = [];
        $pastSubscriptions = [];
        
        foreach ($allSubscriptions as $sub) {
            if (in_array($sub['status'], [SUBSCRIPTION_STATUS_ACTIVE, SUBSCRIPTION_STATUS_PENDING])) {
                if ($sub['status'] === SUBSCRIPTION_STATUS_ACTIVE) {
                    $sub['today_status'] = $deliveryModel->getTodaysStatusForSubscription((int) $sub['id']);
                }
                $activeSubscriptions[] = $sub;
            } else {
                $pastSubscriptions[] = $sub;
            }
        }

        $this->render('customer/my_subscriptions', [
            'title'               => 'My Tiffin Subscriptions',
            'activeSubscriptions' => array_values($activeSubscriptions),
            'pastSubscriptions'   => array_values($pastSubscriptions),
        ]);
    }

    /**
     * Cancel an active subscription
     * POST /subscription/cancel
     */
    public function cancelSubscription(): void {
        Middleware::role(ROLE_CUSTOMER);
        Middleware::verifyCsrf();

        require_once APP_PATH . '/models/CustomerSubscription.php';
        $customerSubModel = new CustomerSubscription();

        $customerId = (int) Session::get('user_id');
        $subscriptionId = (int) ($_POST['subscription_id'] ?? 0);

        // Security: ensure subscription belongs to this customer
        if (!$customerSubModel->belongsToCustomer($subscriptionId, $customerId)) {
            Session::setFlash('danger', 'Subscription not found or access denied.');
            $this->redirect('/my-subscriptions');
            return;
        }

        $sub = $customerSubModel->find($subscriptionId);
        if (!$sub || $sub['status'] !== SUBSCRIPTION_STATUS_ACTIVE) {
            Session::setFlash('warning', 'This subscription is not active.');
            $this->redirect('/my-subscriptions');
            return;
        }

        $customerSubModel->updateStatus($subscriptionId, SUBSCRIPTION_STATUS_CANCELLED);
        Session::setFlash('success', 'Your subscription has been cancelled successfully.');
        $this->redirect('/my-subscriptions');
    }
}
