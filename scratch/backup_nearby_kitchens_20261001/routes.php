<?php
/**
 * Kravyo - Application Route Map
 * Format: 'METHOD /uri' => 'ControllerName@methodName'
 */

return [
    // --- Public / General Routes ---
    'GET /'                     => 'HomeController@index',
    'GET /about'                => 'HomeController@about',
    'GET /contact'              => 'HomeController@contact',

    // --- Customer Authentication Routes ---
    'GET /login'                => 'AuthController@showLoginForm',
    'POST /login'               => 'AuthController@login',
    'GET /register'             => 'AuthController@showRegisterForm',
    'POST /register'            => 'AuthController@register',
    'POST /logout'              => 'AuthController@logout',
    // Chef logout gets its own route so the POST lands in KRAVYO_CHEF context
    // and CSRF is verified against the Chef session (not KRAVYO_CUSTOMER).
    'POST /chef/logout'         => 'AuthController@logout',
    'GET /forgot-password'      => 'AuthController@showForgotPasswordForm',
    'POST /forgot-password'     => 'AuthController@forgotPassword',
    'GET /reset-password'       => 'AuthController@showResetPasswordForm',
    'POST /reset-password'      => 'AuthController@resetPassword',
    'POST /resend-reset-otp'    => 'AuthController@resendResetOtp',
    // --- Email OTP Verification Routes ---
    'GET /verify-email'         => 'AuthController@showVerifyEmail',
    'POST /verify-email'        => 'AuthController@verifyEmail',
    'POST /resend-email-otp'    => 'AuthController@resendOtp',

    // --- Food Discovery Routes ---
    'GET /kitchens'             => 'CustomerController@browseKitchens',
    'GET /kitchen/{id}'         => 'CustomerController@viewKitchen',
    'GET /menu'                 => 'CustomerController@browseMenu',
    'GET /dish/{id}'            => 'CustomerController@viewDish',
    'GET /zero-waste'           => 'CustomerController@zeroWasteDeals',
    'GET /recommendations'      => 'CustomerController@recommendations',

    // --- Customer Profile Routes ---
    'GET /profile'                    => 'CustomerController@profile',
    'POST /profile/update'            => 'CustomerController@updateProfile',
    'POST /profile/address/add'       => 'CustomerController@addAddress',
    'POST /profile/address/edit/{id}' => 'CustomerController@editAddress',
    'POST /profile/address/delete/{id}' => 'CustomerController@deleteAddress',

    // --- Cart & Checkout Routes ---
    'GET /cart'                 => 'CartController@index',
    'POST /cart/add'            => 'CartController@add',
    'POST /cart/update'         => 'CartController@update',
    'POST /cart/remove'         => 'CartController@remove',
    'GET /checkout'             => 'OrderController@checkout',
    'POST /order/place'         => 'OrderController@placeOrder',
    'GET /order/track/{id}'     => 'OrderController@trackOrder',
    'POST /order/cancel'        => 'OrderController@cancelOrder',
    'GET /orders/history'       => 'OrderController@history',
    'POST /review/submit'       => 'OrderController@submitReview',

    // --- Chef-Context Browse Routes ──────────────────────────────────────────
    // Mirrors of public Customer browse pages, but under /chef/ prefix so the
    // session stays in KRAVYO_CHEF context.  Without these, clicking "Browse
    // Dishes" from the Chef tab would switch to KRAVYO_CUSTOMER and show the
    // Customer's session instead of the Chef's.
    'GET /chef/kitchens'           => 'CustomerController@browseKitchens',
    'GET /chef/kitchen/{id}'       => 'CustomerController@viewKitchen',
    'GET /chef/menu-browse'        => 'CustomerController@browseMenu',
    'GET /chef/dish/{id}'          => 'CustomerController@viewDish',
    'GET /chef/zero-waste-deals'   => 'CustomerController@zeroWasteDeals',
    'GET /chef/subscriptions-browse' => 'SubscriptionController@browsePlans',
    'GET /chef/subscription/{id}'  => 'SubscriptionController@viewPlan',

    // --- Home Chef / Seller Routes ---
    'GET /chef/dashboard'       => 'ChefController@dashboard',
    'GET /chef/profile'         => 'ChefController@profile',
    'POST /chef/profile/update' => 'ChefController@updateProfile',
    'GET /chef/menu'            => 'ChefController@manageMenu',
    'POST /chef/menu/add'       => 'ChefController@addDish',
    'POST /chef/menu/edit/{id}' => 'ChefController@editDish',
    'POST /chef/menu/delete/{id}' => 'ChefController@deleteDish',
    'GET /chef/orders'          => 'ChefController@orders',
    'POST /chef/order/status'   => 'ChefController@updateOrderStatus',
    'GET /chef/subscriptions'   => 'ChefController@subscriptions',
    'POST /chef/subscription/add' => 'ChefController@addSubscriptionPlan',
    'POST /chef/subscription/edit/{id}' => 'ChefController@editSubscriptionPlan',
    'POST /chef/subscription/delete/{id}' => 'ChefController@deleteSubscriptionPlan',
    'POST /chef/subscription/toggle/{id}' => 'ChefController@toggleSubscriptionPlan',
    'POST /chef/subscription/approve/{id}'=> 'ChefController@approveSubscription',
    'POST /chef/subscription/reject/{id}' => 'ChefController@rejectSubscription',
    'POST /chef/subscription/delivery/status' => 'ChefController@updateDeliveryStatus',
    'GET /chef/zero-waste'               => 'ChefController@zeroWaste',
    'POST /chef/zero-waste/add'          => 'ChefController@addZeroWasteItem',
    'POST /chef/zero-waste/delete/{id}'  => 'ChefController@deleteZeroWasteItem',
    'POST /chef/zero-waste/toggle/{id}'  => 'ChefController@toggleZeroWasteStatus',
    'POST /chef/toggle-availability' => 'ChefController@toggleAvailability',

    // --- Tiffin Subscription Routes (Customer) ---
    'GET /subscriptions'            => 'SubscriptionController@browsePlans',
    'GET /subscription/{id}'        => 'SubscriptionController@viewPlan',
    'POST /subscription/subscribe'  => 'SubscriptionController@subscribe',
    'GET /my-subscriptions'         => 'SubscriptionController@mySubscriptions',
    'POST /subscription/cancel'     => 'SubscriptionController@cancelSubscription',

    // --- Notification Routes (Modules 1.13 & 2.8) ---
    'GET /notifications'                => 'NotificationController@index',
    'POST /notifications/mark-read'     => 'NotificationController@markRead',
    'POST /notifications/mark-all'      => 'NotificationController@markAllRead',
    'GET /notifications/count'          => 'NotificationController@count',
    'GET /notifications/latest'         => 'NotificationController@latest',

    // --- Admin Authentication Routes ---
    'GET /admin/login'          => 'AdminAuthController@showLoginForm',
    'POST /admin/login'         => 'AdminAuthController@login',
    'POST /admin/logout'        => 'AdminAuthController@logout',

    // --- Admin-Context Browse Routes ─────────────────────────────────────────
    // Same concept as Chef browse routes: keeps the session in KRAVYO_ADMIN.
    'GET /admin/kitchens-browse'      => 'CustomerController@browseKitchens',
    'GET /admin/kitchen/{id}'         => 'CustomerController@viewKitchen',
    'GET /admin/menu-browse'          => 'CustomerController@browseMenu',
    'GET /admin/dish/{id}'            => 'CustomerController@viewDish',
    'GET /admin/zero-waste-browse'    => 'CustomerController@zeroWasteDeals',

    // --- Admin Routes ---
    'GET /admin/dashboard'      => 'AdminController@dashboard',
    'GET /admin/users'          => 'AdminController@users',
    'POST /admin/user/toggle'   => 'AdminController@toggleUserStatus',
    'GET /admin/chefs'          => 'AdminController@chefs',
    'POST /admin/chef/verify'   => 'AdminController@verifyChef',
    'GET /admin/orders'         => 'AdminController@orders',
    'POST /admin/order/intervene' => 'AdminController@interveneOrder',
    'GET /admin/subscriptions'  => 'AdminController@subscriptions',
    'POST /admin/subscription/approve/{id}' => 'AdminController@approveSubscription',
    'POST /admin/subscription/reject/{id}'  => 'AdminController@rejectSubscription',
    'GET /admin/categories'     => 'AdminController@categories',
    'POST /admin/category/add'  => 'AdminController@addCategory',
    'POST /admin/category/edit/{id}' => 'AdminController@editCategory',
    'POST /admin/category/delete/{id}' => 'AdminController@deleteCategory',
    'GET /admin/reports'        => 'AdminController@reports',
    'GET /admin/reviews'        => 'AdminController@reviews',
    'POST /admin/review/delete/{id}' => 'AdminController@deleteReview',
    'GET /admin/zero-waste'     => 'AdminController@zeroWaste',
    'GET /admin/ai-recommendations'   => 'AdminController@aiRecommendations',
    'POST /admin/ai-config/update'    => 'AdminController@updateAiConfig',
    'POST /admin/ai-test'             => 'AdminController@testAiRecommendation',
    'POST /admin/broadcast'     => 'AdminController@broadcast',  // Module 3.11
];
