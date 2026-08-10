<?php
/**
 * Kravyo - System Global Constants
 */

// User Roles
define('ROLE_CUSTOMER', 'customer');
define('ROLE_CHEF', 'chef');
define('ROLE_ADMIN', 'admin');

// Kitchen Approval & Hygiene Badge Statuses
define('KITCHEN_STATUS_PENDING', 'pending');
define('KITCHEN_STATUS_APPROVED', 'approved');
define('KITCHEN_STATUS_REJECTED', 'rejected');
define('KITCHEN_STATUS_SUSPENDED', 'suspended');

define('HYGIENE_BADGE_NONE', 'none');
define('HYGIENE_BADGE_VERIFIED', 'verified');

// Order Statuses
define('ORDER_STATUS_PENDING', 'pending');
define('ORDER_STATUS_ACCEPTED', 'accepted');
define('ORDER_STATUS_PREPARING', 'preparing');
define('ORDER_STATUS_OUT_FOR_DELIVERY', 'out_for_delivery');
define('ORDER_STATUS_DELIVERED', 'delivered');
define('ORDER_STATUS_CANCELLED', 'cancelled');

// Payment Statuses & Methods
define('PAYMENT_STATUS_PENDING', 'pending');
define('PAYMENT_STATUS_COMPLETED', 'completed');
define('PAYMENT_STATUS_FAILED', 'failed');
define('PAYMENT_STATUS_REFUNDED', 'refunded');

define('PAYMENT_METHOD_COD', 'cod');
define('PAYMENT_METHOD_UPI', 'upi');
define('PAYMENT_METHOD_CARD', 'card');
define('PAYMENT_METHOD_NETBANKING', 'netbanking');

// Subscription Plans
define('SUBSCRIPTION_WEEKLY', 'weekly');
define('SUBSCRIPTION_MONTHLY', 'monthly');

// Subscription Statuses
define('SUBSCRIPTION_STATUS_ACTIVE', 'active');
define('SUBSCRIPTION_STATUS_PAUSED', 'paused');
define('SUBSCRIPTION_STATUS_CANCELLED', 'cancelled');
define('SUBSCRIPTION_STATUS_COMPLETED', 'completed');

// Zero Food Waste Listing Status
define('ZERO_WASTE_ACTIVE', 'active');
define('ZERO_WASTE_SOLD_OUT', 'sold_out');
define('ZERO_WASTE_EXPIRED', 'expired');
