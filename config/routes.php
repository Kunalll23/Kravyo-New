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

    // --- Chef Authentication Routes ---
    'GET /chef/login'           => 'AuthController@showChefLoginForm',
    'POST /chef/login'          => 'AuthController@chefLogin',
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
    'GET /home'                 => 'CustomerController@home',
    'GET /kitchens'             => 'CustomerController@browseKitchens',
    'GET /kitchen/{id}'         => 'CustomerController@viewKitchen',
    'GET /nearby-kitchens'      => 'CustomerController@nearbyKitchens',
    'GET /kitchens/nearby'      => 'CustomerController@nearbyKitchens',
    'GET /api/kitchens/nearby'  => 'CustomerController@apiNearbyKitchens',
    'POST /api/set-city'        => 'CustomerController@setCity',
    'GET /api/dishes/suggest'   => 'CustomerController@apiDishSuggestions',
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
    'POST /order/verify-payment'=> 'OrderController@verifyPayment',
    'GET /order/track/{id}'     => 'OrderController@trackOrder',
    'GET /order/status/{id}'    => 'OrderController@getOrderStatus',
    'GET /order/location/{id}'  => 'OrderController@getDeliveryLocation',
    'POST /order/cancel'        => 'OrderController@cancelOrder',
    'GET /orders/history'       => 'OrderController@history',
    'POST /review/submit'       => 'OrderController@submitReview',
    'POST /customer/report/submit' => 'CustomerController@submitReport',

    // --- Customer Notification Routes ---
    'GET /notifications'                => 'NotificationController@index',
    'POST /notifications/mark-read'     => 'NotificationController@markRead',
    'POST /notifications/mark-all'      => 'NotificationController@markAllRead',
    'GET /notifications/count'          => 'NotificationController@count',
    'GET /notifications/latest'         => 'NotificationController@latest',
    'GET /api/notifications/unread'     => 'NotificationController@apiUnread',

    // --- Chef-Context Browse Routes ──────────────────────────────────────────
    // Mirrors of public Customer browse pages, but under /chef/ prefix so the
    // session stays in KRAVYO_CHEF context.  Without these, clicking "Browse
    // Dishes" from the Chef tab would switch to KRAVYO_CUSTOMER and show the
    // Customer's session instead of the Chef's.
    'GET /chef/kitchens'           => 'CustomerController@browseKitchens',
    'GET /chef/kitchen/{id}'       => 'CustomerController@viewKitchen',
    'GET /chef/nearby-kitchens'    => 'CustomerController@nearbyKitchens',
    'GET /chef/menu-browse'        => 'CustomerController@browseMenu',
    'GET /chef/dish/{id}'          => 'CustomerController@viewDish',
    'GET /chef/zero-waste-deals'   => 'CustomerController@zeroWasteDeals',
    'GET /chef/subscriptions-browse' => 'SubscriptionController@browsePlans',
    'GET /chef/subscription/{id}'  => 'SubscriptionController@viewPlan',

    // --- Home Chef / Seller Routes ---
    'GET /chef'                  => 'ChefController@dashboard',
    'GET /chef/dashboard'        => 'ChefController@dashboard',
    'GET /chef/dashboard/export' => 'ChefController@exportReport',
    'GET /chef/api/dashboard-chart' => 'ChefController@chartDataApi',
    'GET /chef/profile'          => 'ChefController@profile',
    'POST /chef/profile/update' => 'ChefController@updateProfile',
    'GET /chef/menu'            => 'ChefController@manageMenu',
    'POST /chef/menu/add'       => 'ChefController@addDish',
    'POST /chef/menu/edit/{id}' => 'ChefController@editDish',
    'POST /chef/menu/delete/{id}' => 'ChefController@deleteDish',
    'POST /chef/dish/toggle-availability/{id}' => 'ChefController@toggleDishAvailability',
    'GET /chef/orders'          => 'ChefController@orders',
    'POST /chef/order/status'   => 'ChefController@updateOrderStatus',
    'GET /chef/order/track/{id}' => 'ChefController@trackOrder',
    'GET /chef/subscriptions'   => 'ChefController@subscriptions',
    'POST /chef/subscription/add' => 'ChefController@addSubscriptionPlan',
    'POST /chef/subscription/edit/{id}' => 'ChefController@editSubscriptionPlan',
    'POST /chef/subscription/delete/{id}' => 'ChefController@deleteSubscriptionPlan',
    'POST /chef/subscription/toggle/{id}' => 'ChefController@toggleSubscriptionPlan',
    'POST /chef/subscription/approve/{id}'=> 'ChefController@approveSubscription',
    'POST /chef/subscription/reject/{id}' => 'ChefController@rejectSubscription',
    'GET /chef/reports'         => 'ChefController@dishReports',

    // --- Chef Notification Routes ---
    'GET /chef/notifications'                => 'NotificationController@index',
    'POST /chef/notifications/mark-read'     => 'NotificationController@markRead',
    'POST /chef/notifications/mark-all'      => 'NotificationController@markAllRead',
    'GET /chef/notifications/count'          => 'NotificationController@count',
    'GET /chef/notifications/latest'         => 'NotificationController@latest',
    'GET /chef/api/notifications/unread'     => 'NotificationController@apiUnread',
    'POST /chef/subscription/delivery/status' => 'ChefController@updateDeliveryStatus',
    'GET /chef/zero-waste'               => 'ChefController@zeroWaste',
    'POST /chef/zero-waste/add'          => 'ChefController@addZeroWasteItem',
    'POST /chef/zero-waste/delete/{id}'  => 'ChefController@deleteZeroWasteItem',
    'POST /chef/zero-waste/toggle/{id}'  => 'ChefController@toggleZeroWasteStatus',
    'POST /chef/toggle-availability' => 'ChefController@toggleAvailability',

    // --- Chef Notification Routes ---
    'GET /chef/notifications'                => 'NotificationController@index',
    'POST /chef/notifications/mark-read'     => 'NotificationController@markRead',
    'POST /chef/notifications/mark-all'      => 'NotificationController@markAllRead',
    'GET /chef/notifications/count'          => 'NotificationController@count',
    'GET /chef/notifications/latest'         => 'NotificationController@latest',
    'GET /chef/api/notifications/unread'     => 'NotificationController@apiUnread',

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
    'GET /api/notifications/unread'     => 'NotificationController@apiUnread',

    // --- Admin Authentication Routes ---
    'GET /admin/login'          => 'AdminAuthController@showLoginForm',
    'POST /admin/login'         => 'AdminAuthController@login',
    'POST /admin/logout'        => 'AdminAuthController@logout',

    // --- Admin-Context Browse Routes ─────────────────────────────────────────
    // Same concept as Chef browse routes: keeps the session in KRAVYO_ADMIN.
    'GET /admin/kitchens-browse'      => 'CustomerController@browseKitchens',
    'GET /admin/kitchen/{id}'         => 'CustomerController@viewKitchen',
    'GET /admin/nearby-kitchens'      => 'CustomerController@nearbyKitchens',
    'GET /admin/menu-browse'          => 'CustomerController@browseMenu',
    'GET /admin/dish/{id}'            => 'CustomerController@viewDish',
    'GET /admin/zero-waste-browse'    => 'CustomerController@zeroWasteDeals',
    'GET /admin/subscriptions-browse' => 'SubscriptionController@browsePlans',
    'GET /admin/subscription/{id}'    => 'SubscriptionController@viewPlan',

    // --- Admin Routes ---
    'GET /admin'                => 'AdminController@dashboard',
    'GET /admin/dashboard'      => 'AdminController@dashboard',
    
    // --- Admin Notification Routes ---
    'GET /admin/notifications'                => 'NotificationController@index',
    'POST /admin/notifications/mark-read'     => 'NotificationController@markRead',
    'POST /admin/notifications/mark-all'      => 'NotificationController@markAllRead',
    'GET /admin/notifications/count'          => 'NotificationController@count',
    'GET /admin/notifications/latest'         => 'NotificationController@latest',
    'GET /admin/api/notifications/unread'     => 'NotificationController@apiUnread',
    'GET /admin/users'          => 'AdminController@users',
    'POST /admin/user/toggle'   => 'AdminController@toggleUserStatus',
    'GET /admin/chefs'          => 'AdminController@chefs',
    'POST /admin/chef/verify'   => 'AdminController@verifyChef',
    'GET /admin/orders'         => 'AdminController@orders',
    'POST /admin/order/intervene' => 'AdminController@interveneOrder',
    'GET /admin/dispatch-monitor' => 'AdminController@liveDispatchMonitor',
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
    'GET /admin/reports-center' => 'AdminController@reportsCenter',
    'POST /admin/reports-center/update' => 'AdminController@updateReportStatus',
    'GET /admin/zero-waste'     => 'AdminController@zeroWaste',
    'GET /admin/ai-recommendations'   => 'AdminController@aiRecommendations',
    'POST /admin/ai-config/update'    => 'AdminController@updateAiConfig',
    'POST /admin/ai-test'             => 'AdminController@testAiRecommendation',
    'POST /admin/broadcast'           => 'AdminController@broadcast',  // Module 3.11

    // --- Delivery Routes ---
    'POST /delivery/toggle-status'             => 'DeliveryController@toggleStatus',

    // --- Admin Delivery Routes ---
    'GET /admin/delivery-partners'             => 'AdminController@deliveryPartners',
    'POST /admin/delivery-partners/update'     => 'AdminController@updateDeliveryPartnerStatus',
    'POST /admin/delivery-partners/force-status' => 'AdminController@forceDeliveryStatus',

    // --- Delivery-Context Browse Routes ─────────────────────────────────────
    // Mirrors of public Customer browse pages under /delivery/ prefix so the
    // KRAVYO_DELIVERY session cookie stays active when a Delivery Partner
    // navigates to shared catalogue pages like "Explore Kitchens" or "Browse
    // Dishes".  Without these, clicking such a link would open KRAVYO_CUSTOMER
    // and the Delivery Partner would appear to be logged out.
    'GET /delivery/kitchens'                   => 'CustomerController@browseKitchens',
    'GET /delivery/kitchen/{id}'               => 'CustomerController@viewKitchen',
    'GET /delivery/nearby-kitchens'            => 'CustomerController@nearbyKitchens',
    'GET /delivery/menu-browse'                => 'CustomerController@browseMenu',
    'GET /delivery/dish/{id}'                  => 'CustomerController@viewDish',
    'GET /delivery/zero-waste-browse'          => 'CustomerController@zeroWasteDeals',
    'GET /delivery/subscriptions-browse'       => 'SubscriptionController@browsePlans',
    'GET /delivery/subscription/{id}'          => 'SubscriptionController@viewPlan',

    // --- Delivery Partner Routes ---
    'GET /delivery'                            => 'DeliveryController@dashboard',
    'GET /delivery/login'                      => 'DeliveryController@showLoginForm',
    'POST /delivery/login'                     => 'DeliveryController@login',
    'GET /delivery/register'                   => 'DeliveryController@showRegisterForm',
    'POST /delivery/register'                  => 'DeliveryController@register',
    'POST /delivery/logout'                    => 'DeliveryController@logout',
    'GET /delivery/dashboard'                  => 'DeliveryController@dashboard',
    'POST /delivery/toggle-status'             => 'DeliveryController@toggleStatus',
    'GET /delivery/offers'                     => 'DeliveryController@getOffers',
    'POST /delivery/offers/accept'             => 'DeliveryController@acceptOffer',
    'POST /delivery/offers/decline'            => 'DeliveryController@declineOffer',
    'GET /delivery/order/{id}'                 => 'DeliveryController@viewOrder',
    'POST /delivery/order/{id}/accept'         => 'DeliveryController@acceptOrder',
    'POST /delivery/order/{id}/pickup'         => 'DeliveryController@pickupOrder',
    'POST /delivery/order/{id}/out-for-delivery' => 'DeliveryController@outForDelivery',
    'POST /delivery/order/{id}/complete'       => 'DeliveryController@completeDelivery',
    'POST /delivery/order/{id}/failed'         => 'DeliveryController@failedDelivery',
    'POST /delivery/location/update'           => 'DeliveryController@updateLocation',
    'GET /delivery/history'                    => 'DeliveryController@history',

    // --- Delivery Notification Routes ---
    'GET /delivery/notifications'                => 'NotificationController@index',
    'POST /delivery/notifications/mark-read'     => 'NotificationController@markRead',
    'POST /delivery/notifications/mark-all'      => 'NotificationController@markAllRead',
    'GET /delivery/notifications/count'          => 'NotificationController@count',
    'GET /delivery/notifications/latest'         => 'NotificationController@latest',
    'GET /delivery/api/notifications/unread'     => 'NotificationController@apiUnread',

    // --- Google Maps Proxy (one mount per portal so the right session cookie is used) ---
    'GET /api/maps/route'                      => 'MapsController@route',
    'GET /api/maps/geocode'                    => 'MapsController@geocode',
    'GET /delivery/api/maps/route'             => 'MapsController@route',
    'GET /delivery/api/maps/geocode'           => 'MapsController@geocode',
    'GET /chef/api/maps/route'                 => 'MapsController@route',
    'GET /chef/api/maps/geocode'               => 'MapsController@geocode',
];
