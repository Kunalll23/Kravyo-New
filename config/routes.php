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
    'GET /forgot-password'      => 'AuthController@showForgotPasswordForm',

    // --- Food Discovery Routes ---
    'GET /kitchens'             => 'CustomerController@browseKitchens',
    'GET /kitchen/{id}'         => 'CustomerController@viewKitchen',
    'GET /menu'                 => 'CustomerController@browseMenu',
    'GET /dish/{id}'            => 'CustomerController@viewDish',
    'GET /zero-waste'           => 'CustomerController@zeroWasteDeals',

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

    // --- Admin Routes ---
    'GET /admin/dashboard'      => 'AdminController@dashboard',
    'GET /admin/users'          => 'AdminController@users',
    'POST /admin/user/toggle'   => 'AdminController@toggleUserStatus',
    'GET /admin/chefs'          => 'AdminController@chefs',
    'POST /admin/chef/verify'   => 'AdminController@verifyChef',
    'GET /admin/orders'         => 'AdminController@orders',
    'GET /admin/categories'     => 'AdminController@categories',
    'POST /admin/category/add'  => 'AdminController@addCategory',
    'POST /admin/category/edit/{id}' => 'AdminController@editCategory',
    'POST /admin/category/delete/{id}' => 'AdminController@deleteCategory',
    'GET /admin/reports'        => 'AdminController@reports',
];
