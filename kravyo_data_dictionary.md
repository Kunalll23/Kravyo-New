# Kravyo Data Dictionary & Flow Diagram

This document outlines every table in the Kravyo database, how many exist, their purpose, which UI actions insert into them, and a full attribute breakdown.

## Database Overview
**Total Tables:** 19

## Table Definitions

### `addresses`
- **Purpose:** Saved addresses for customers
- **How Data is Inserted:** Checkout flow, Address book management

| Attribute | Type | Nullable | Default | Extra |
|---|---|---|---|---|
| `id` | `int(11)` | NO | `NULL` | auto_increment (PK) |
| `user_id` | `int(11)` | NO | `NULL` |  (FK/IDX) |
| `address_type` | `enum('Home','Work','Other')` | YES | `Home` |  |
| `street_address` | `text` | NO | `NULL` |  |
| `landmark` | `varchar(150)` | YES | `NULL` |  |
| `city` | `varchar(100)` | NO | `NULL` |  |
| `pincode` | `char(6)` | NO | `NULL` |  |
| `created_at` | `timestamp` | NO | `current_timestamp()` |  |

---

### `admins`
- **Purpose:** Administrative accounts
- **How Data is Inserted:** Admin creation (via Database or Admin portal)

| Attribute | Type | Nullable | Default | Extra |
|---|---|---|---|---|
| `id` | `int(11)` | NO | `NULL` | auto_increment (PK) |
| `full_name` | `varchar(50)` | NO | `NULL` |  |
| `email` | `varchar(50)` | NO | `NULL` |  (FK/IDX) |
| `phone` | `char(10)` | NO | `NULL` |  (FK/IDX) |
| `password_hash` | `varchar(60)` | NO | `NULL` |  |
| `status` | `enum('active','inactive','suspended','pending_verification')` | NO | `active` |  |
| `email_verified` | `tinyint(1)` | NO | `0` |  |
| `email_otp_hash` | `varchar(255)` | YES | `NULL` |  |
| `email_otp_expires_at` | `datetime` | YES | `NULL` |  |
| `email_otp_resent_at` | `datetime` | YES | `NULL` |  |
| `email_otp_attempts` | `tinyint(1)` | NO | `0` |  |
| `reset_otp_hash` | `varchar(60)` | YES | `NULL` |  |
| `reset_otp_expires_at` | `datetime` | YES | `NULL` |  |
| `reset_otp_resent_at` | `datetime` | YES | `NULL` |  |
| `reset_otp_attempts` | `tinyint(3) unsigned` | NO | `0` |  |
| `created_at` | `timestamp` | NO | `current_timestamp()` |  |
| `updated_at` | `timestamp` | NO | `current_timestamp()` | on update current_timestamp() |
| `last_login_at` | `timestamp` | YES | `NULL` |  |

---

### `categories`
- **Purpose:** Food categories (e.g., Gujarati, Punjabi)
- **How Data is Inserted:** Admin portal (`POST /admin/categories/add`)

| Attribute | Type | Nullable | Default | Extra |
|---|---|---|---|---|
| `id` | `int(11)` | NO | `NULL` | auto_increment (PK) |
| `category_name` | `varchar(50)` | NO | `NULL` |  (FK/IDX) |
| `description` | `varchar(255)` | YES | `NULL` |  |
| `image` | `varchar(255)` | YES | `NULL` |  |
| `created_at` | `timestamp` | NO | `current_timestamp()` |  |

---

### `chefs`
- **Purpose:** Core system table
- **How Data is Inserted:** System internal

| Attribute | Type | Nullable | Default | Extra |
|---|---|---|---|---|
| `id` | `int(11)` | NO | `NULL` | auto_increment (PK) |
| `full_name` | `varchar(50)` | NO | `NULL` |  |
| `email` | `varchar(50)` | NO | `NULL` |  (FK/IDX) |
| `phone` | `char(10)` | NO | `NULL` |  (FK/IDX) |
| `password_hash` | `varchar(60)` | NO | `NULL` |  |
| `role` | `enum('customer','chef','admin','delivery')` | NO | `customer` |  (FK/IDX) |
| `status` | `enum('active','inactive','suspended','pending_verification')` | NO | `active` |  |
| `email_verified` | `tinyint(1)` | NO | `0` |  |
| `email_otp_hash` | `varchar(255)` | YES | `NULL` |  |
| `email_otp_expires_at` | `datetime` | YES | `NULL` |  |
| `email_otp_resent_at` | `datetime` | YES | `NULL` |  |
| `email_otp_attempts` | `tinyint(1)` | NO | `0` |  |
| `reset_otp_hash` | `varchar(60)` | YES | `NULL` |  |
| `reset_otp_expires_at` | `datetime` | YES | `NULL` |  |
| `reset_otp_resent_at` | `datetime` | YES | `NULL` |  |
| `reset_otp_attempts` | `tinyint(3) unsigned` | NO | `0` |  |
| `created_at` | `timestamp` | NO | `current_timestamp()` |  |
| `updated_at` | `timestamp` | NO | `current_timestamp()` | on update current_timestamp() |

---

### `customer_subscriptions`
- **Purpose:** Customers who subscribed to a Tiffin Plan
- **How Data is Inserted:** Customer Portal (`POST /subscription/purchase`)

| Attribute | Type | Nullable | Default | Extra |
|---|---|---|---|---|
| `id` | `int(11)` | NO | `NULL` | auto_increment (PK) |
| `customer_id` | `int(11)` | NO | `NULL` |  (FK/IDX) |
| `subscription_plan_id` | `int(11)` | NO | `NULL` |  (FK/IDX) |
| `kitchen_id` | `int(11)` | NO | `NULL` |  (FK/IDX) |
| `address_id` | `int(11)` | NO | `NULL` |  (FK/IDX) |
| `start_date` | `date` | NO | `NULL` |  |
| `end_date` | `date` | NO | `NULL` |  |
| `status` | `enum('pending','active','paused','cancelled','completed')` | YES | `pending` |  (FK/IDX) |
| `payment_method` | `enum('cod','upi','card','netbanking')` | YES | `cod` |  |
| `payment_status` | `enum('pending','completed','failed','refunded')` | YES | `pending` |  |
| `total_paid` | `decimal(8,2)` | NO | `NULL` |  |
| `special_instructions` | `text` | YES | `NULL` |  |
| `created_at` | `timestamp` | NO | `current_timestamp()` |  |
| `updated_at` | `timestamp` | NO | `current_timestamp()` | on update current_timestamp() |

---

### `customers`
- **Purpose:** Core system table
- **How Data is Inserted:** System internal

| Attribute | Type | Nullable | Default | Extra |
|---|---|---|---|---|
| `id` | `int(11)` | NO | `NULL` | auto_increment (PK) |
| `full_name` | `varchar(50)` | NO | `NULL` |  |
| `email` | `varchar(50)` | NO | `NULL` |  (FK/IDX) |
| `phone` | `char(10)` | NO | `NULL` |  (FK/IDX) |
| `password_hash` | `varchar(60)` | NO | `NULL` |  |
| `role` | `enum('customer','chef','admin','delivery')` | NO | `customer` |  (FK/IDX) |
| `status` | `enum('active','inactive','suspended','pending_verification')` | NO | `active` |  |
| `email_verified` | `tinyint(1)` | NO | `0` |  |
| `email_otp_hash` | `varchar(255)` | YES | `NULL` |  |
| `email_otp_expires_at` | `datetime` | YES | `NULL` |  |
| `email_otp_resent_at` | `datetime` | YES | `NULL` |  |
| `email_otp_attempts` | `tinyint(1)` | NO | `0` |  |
| `reset_otp_hash` | `varchar(60)` | YES | `NULL` |  |
| `reset_otp_expires_at` | `datetime` | YES | `NULL` |  |
| `reset_otp_resent_at` | `datetime` | YES | `NULL` |  |
| `reset_otp_attempts` | `tinyint(3) unsigned` | NO | `0` |  |
| `created_at` | `timestamp` | NO | `current_timestamp()` |  |
| `updated_at` | `timestamp` | NO | `current_timestamp()` | on update current_timestamp() |

---

### `delivery_offers`
- **Purpose:** Live broadcast offers to riders
- **How Data is Inserted:** System-generated (`DispatchService::broadcast`)

| Attribute | Type | Nullable | Default | Extra |
|---|---|---|---|---|
| `id` | `int(11)` | NO | `NULL` | auto_increment (PK) |
| `order_id` | `int(11)` | NO | `NULL` |  (FK/IDX) |
| `delivery_partner_id` | `int(11)` | NO | `NULL` |  (FK/IDX) |
| `round` | `tinyint(4)` | NO | `1` |  |
| `status` | `enum('pending','accepted','declined','lost','expired','cancelled')` | NO | `pending` |  |
| `sent_at` | `datetime` | NO | `current_timestamp()` |  |
| `seen_at` | `datetime` | YES | `NULL` |  |
| `responded_at` | `datetime` | YES | `NULL` |  |

---

### `delivery_partners`
- **Purpose:** Rider profiles for delivery
- **How Data is Inserted:** Rider Onboarding

| Attribute | Type | Nullable | Default | Extra |
|---|---|---|---|---|
| `id` | `int(11)` | NO | `NULL` | auto_increment (PK) |
| `user_id` | `int(11)` | NO | `NULL` |  (FK/IDX) |
| `vehicle_type` | `enum('bicycle','scooter','motorcycle','car')` | NO | `scooter` |  |
| `vehicle_number` | `varchar(20)` | NO | `NULL` |  |
| `city` | `varchar(60)` | NO | `Surat` |  (FK/IDX) |
| `driving_license_number` | `varchar(30)` | NO | `NULL` |  |
| `verification_status` | `enum('pending','approved','rejected','suspended')` | NO | `pending` |  (FK/IDX) |
| `is_online` | `tinyint(1)` | NO | `0` |  (FK/IDX) |
| `is_busy` | `tinyint(1)` | NO | `0` |  |
| `last_seen_at` | `datetime` | YES | `NULL` |  |
| `current_latitude` | `decimal(10,8)` | YES | `NULL` |  |
| `current_longitude` | `decimal(11,8)` | YES | `NULL` |  |
| `location_updated_at` | `timestamp` | YES | `NULL` |  |
| `total_deliveries` | `int(11)` | NO | `0` |  |
| `total_earnings` | `decimal(10,2)` | NO | `0.00` |  |
| `admin_notes` | `text` | YES | `NULL` |  |
| `created_at` | `timestamp` | NO | `current_timestamp()` |  |
| `updated_at` | `timestamp` | NO | `current_timestamp()` | on update current_timestamp() |

---

### `delivery_users`
- **Purpose:** Core system table
- **How Data is Inserted:** System internal

| Attribute | Type | Nullable | Default | Extra |
|---|---|---|---|---|
| `id` | `int(11)` | NO | `NULL` | auto_increment (PK) |
| `full_name` | `varchar(50)` | NO | `NULL` |  |
| `email` | `varchar(50)` | NO | `NULL` |  (FK/IDX) |
| `phone` | `char(10)` | NO | `NULL` |  (FK/IDX) |
| `password_hash` | `varchar(60)` | NO | `NULL` |  |
| `role` | `enum('customer','chef','admin','delivery')` | NO | `customer` |  (FK/IDX) |
| `status` | `enum('active','inactive','suspended','pending_verification')` | NO | `active` |  |
| `email_verified` | `tinyint(1)` | NO | `0` |  |
| `email_otp_hash` | `varchar(255)` | YES | `NULL` |  |
| `email_otp_expires_at` | `datetime` | YES | `NULL` |  |
| `email_otp_resent_at` | `datetime` | YES | `NULL` |  |
| `email_otp_attempts` | `tinyint(1)` | NO | `0` |  |
| `reset_otp_hash` | `varchar(60)` | YES | `NULL` |  |
| `reset_otp_expires_at` | `datetime` | YES | `NULL` |  |
| `reset_otp_resent_at` | `datetime` | YES | `NULL` |  |
| `reset_otp_attempts` | `tinyint(3) unsigned` | NO | `0` |  |
| `created_at` | `timestamp` | NO | `current_timestamp()` |  |
| `updated_at` | `timestamp` | NO | `current_timestamp()` | on update current_timestamp() |

---

### `kitchens`
- **Purpose:** Chef/Kitchen profiles
- **How Data is Inserted:** Chef Registration (`POST /chef/register`)

| Attribute | Type | Nullable | Default | Extra |
|---|---|---|---|---|
| `id` | `int(11)` | NO | `NULL` | auto_increment (PK) |
| `user_id` | `int(11)` | NO | `NULL` |  (FK/IDX) |
| `kitchen_name` | `varchar(50)` | NO | `NULL` |  |
| `personal_story` | `text` | YES | `NULL` |  |
| `address` | `text` | NO | `NULL` |  |
| `city` | `varchar(100)` | NO | `NULL` |  |
| `pincode` | `char(6)` | NO | `NULL` |  |
| `latitude` | `decimal(10,8)` | YES | `NULL` |  |
| `longitude` | `decimal(11,8)` | YES | `NULL` |  |
| `fssai_license` | `varchar(20)` | YES | `NULL` |  |
| `hygiene_badge` | `enum('none','verified')` | YES | `none` |  |
| `hygiene_certificate_image` | `varchar(255)` | YES | `NULL` |  |
| `approval_status` | `enum('pending','approved','rejected','suspended')` | YES | `pending` |  (FK/IDX) |
| `admin_notes` | `text` | YES | `NULL` |  |
| `is_open` | `tinyint(1)` | YES | `1` |  |
| `banner_image` | `varchar(255)` | YES | `NULL` |  |
| `created_at` | `timestamp` | NO | `current_timestamp()` |  |
| `updated_at` | `timestamp` | NO | `current_timestamp()` | on update current_timestamp() |

---

### `menu_items`
- **Purpose:** Food items in a kitchen's menu
- **How Data is Inserted:** Chef portal (`POST /chef/menu/add`)

| Attribute | Type | Nullable | Default | Extra |
|---|---|---|---|---|
| `id` | `int(11)` | NO | `NULL` | auto_increment (PK) |
| `kitchen_id` | `int(11)` | NO | `NULL` |  (FK/IDX) |
| `category_id` | `int(11)` | NO | `NULL` |  (FK/IDX) |
| `item_name` | `varchar(50)` | NO | `NULL` |  |
| `description` | `text` | YES | `NULL` |  |
| `price` | `decimal(6,2)` | NO | `NULL` |  |
| `image` | `varchar(255)` | YES | `NULL` |  |
| `is_veg` | `tinyint(1)` | YES | `1` |  |
| `is_jain_available` | `tinyint(1)` | YES | `0` |  |
| `is_diabetic_friendly` | `tinyint(1)` | YES | `0` |  |
| `is_available` | `tinyint(1)` | YES | `1` |  |
| `created_at` | `timestamp` | NO | `current_timestamp()` |  |
| `updated_at` | `timestamp` | NO | `current_timestamp()` | on update current_timestamp() |

---

### `notifications`
- **Purpose:** System alerts and notifications
- **How Data is Inserted:** System-generated (Status changes, Broadcasts, Reminders)

| Attribute | Type | Nullable | Default | Extra |
|---|---|---|---|---|
| `id` | `int(11)` | NO | `NULL` | auto_increment (PK) |
| `user_id` | `int(11)` | YES | `NULL` |  (FK/IDX) |
| `user_type` | `enum('customer','chef','delivery','admin')` | NO | `NULL` |  |
| `title` | `varchar(100)` | NO | `NULL` |  |
| `message` | `varchar(500)` | NO | `NULL` |  |
| `type` | `enum('order_update','promotion','system_alert','kitchen_update')` | NO | `system_alert` |  |
| `is_read` | `tinyint(1)` | NO | `0` |  |
| `link` | `varchar(255)` | YES | `NULL` |  |
| `created_at` | `timestamp` | NO | `current_timestamp()` |  (FK/IDX) |

---

### `order_items`
- **Purpose:** Individual items mapped to an order
- **How Data is Inserted:** Customer Checkout (Items inside the Cart)

| Attribute | Type | Nullable | Default | Extra |
|---|---|---|---|---|
| `id` | `int(11)` | NO | `NULL` | auto_increment (PK) |
| `order_id` | `int(11)` | NO | `NULL` |  (FK/IDX) |
| `menu_item_id` | `int(11)` | NO | `NULL` |  (FK/IDX) |
| `quantity` | `smallint(5) unsigned` | NO | `NULL` |  |
| `unit_price` | `decimal(6,2)` | NO | `NULL` |  |
| `subtotal` | `decimal(8,2)` | NO | `NULL` |  |
| `spice_level` | `enum('Low','Medium','High')` | YES | `Medium` |  |
| `oil_level` | `enum('Normal','Less Oil')` | YES | `Normal` |  |
| `is_jain` | `tinyint(1)` | YES | `0` |  |
| `item_notes` | `text` | YES | `NULL` |  |
| `is_zero_waste` | `tinyint(1)` | NO | `0` |  |
| `zero_waste_id` | `int(10) unsigned` | YES | `NULL` |  |

---

### `orders`
- **Purpose:** Standard cart checkout orders & Live Tiffin orders
- **How Data is Inserted:** Customer Checkout (`POST /checkout/process`) or Auto-generated via Chef Tiffin Dispatch

| Attribute | Type | Nullable | Default | Extra |
|---|---|---|---|---|
| `id` | `int(11)` | NO | `NULL` | auto_increment (PK) |
| `order_number` | `varchar(30)` | NO | `NULL` |  (FK/IDX) |
| `order_type` | `enum('standard','tiffin')` | NO | `standard` |  |
| `subscription_delivery_id` | `int(11)` | YES | `NULL` |  |
| `customer_id` | `int(11)` | NO | `NULL` |  (FK/IDX) |
| `kitchen_id` | `int(11)` | NO | `NULL` |  (FK/IDX) |
| `address_id` | `int(11)` | NO | `NULL` |  (FK/IDX) |
| `delivery_latitude` | `decimal(10,8)` | YES | `NULL` |  |
| `delivery_longitude` | `decimal(11,8)` | YES | `NULL` |  |
| `total_amount` | `decimal(8,2)` | NO | `NULL` |  |
| `payment_method` | `enum('cod','upi','card','netbanking')` | YES | `cod` |  |
| `payment_status` | `enum('pending','completed','failed','refunded')` | YES | `pending` |  |
| `order_status` | `enum('pending','accepted','preparing','ready_for_pickup','out_for_delivery','delivered','cancelled','delivery_failed')` | YES | `pending` |  (FK/IDX) |
| `delivery_partner_id` | `int(11)` | YES | `NULL` |  (FK/IDX) |
| `offered_driver_id` | `int(11)` | YES | `NULL` |  |
| `offer_expires_at` | `datetime` | YES | `NULL` |  |
| `rejected_by_drivers` | `longtext` | YES | `NULL` |  |
| `delivery_otp` | `char(4)` | YES | `NULL` |  |
| `delivery_status` | `enum('unassigned','assigned','accepted','picked_up','out_for_delivery','delivered','failed')` | YES | `unassigned` |  (FK/IDX) |
| `delivery_accepted_at` | `datetime` | YES | `NULL` |  |
| `delivery_picked_up_at` | `datetime` | YES | `NULL` |  |
| `delivery_completed_at` | `datetime` | YES | `NULL` |  |
| `delivery_failed_reason` | `varchar(255)` | YES | `NULL` |  |
| `special_instructions` | `varchar(500)` | YES | `NULL` |  |
| `created_at` | `timestamp` | NO | `current_timestamp()` |  (FK/IDX) |
| `updated_at` | `timestamp` | NO | `current_timestamp()` | on update current_timestamp() |
| `dispatch_status` | `enum('none','broadcasting','assigned','no_rider','manual')` | NO | `none` |  |
| `dispatch_started_at` | `datetime` | YES | `NULL` |  |
| `dispatch_round` | `tinyint(4)` | NO | `0` |  |

---

### `pending_registrations`
- **Purpose:** Temporary holding for OTP verification
- **How Data is Inserted:** Registration flow before OTP is verified

| Attribute | Type | Nullable | Default | Extra |
|---|---|---|---|---|
| `email` | `varchar(50)` | NO | `NULL` |  (PK) |
| `full_name` | `varchar(50)` | NO | `NULL` |  |
| `phone` | `char(10)` | NO | `NULL` |  |
| `password_hash` | `varchar(60)` | NO | `NULL` |  |
| `role` | `enum('customer','chef','admin')` | NO | `customer` |  |
| `email_otp_hash` | `varchar(60)` | YES | `NULL` |  |
| `email_otp_expires_at` | `datetime` | YES | `NULL` |  |
| `email_otp_resent_at` | `datetime` | YES | `NULL` |  |
| `email_otp_attempts` | `tinyint(3) unsigned` | NO | `0` |  |
| `created_at` | `timestamp` | NO | `current_timestamp()` |  |

---

### `reviews`
- **Purpose:** Customer ratings for kitchens
- **How Data is Inserted:** Customer Order History (`POST /order/review`)

| Attribute | Type | Nullable | Default | Extra |
|---|---|---|---|---|
| `id` | `int(11)` | NO | `NULL` | auto_increment (PK) |
| `order_id` | `int(11)` | NO | `NULL` |  (FK/IDX) |
| `customer_id` | `int(11)` | NO | `NULL` |  (FK/IDX) |
| `kitchen_id` | `int(11)` | NO | `NULL` |  (FK/IDX) |
| `rating` | `tinyint(3) unsigned` | NO | `NULL` |  |
| `review_text` | `varchar(500)` | NO | `NULL` |  |
| `created_at` | `timestamp` | NO | `current_timestamp()` |  |

---

### `subscription_deliveries`
- **Purpose:** Daily delivery manifests for active subscriptions
- **How Data is Inserted:** Cron/Auto-generation on Kitchen Dashboard load

| Attribute | Type | Nullable | Default | Extra |
|---|---|---|---|---|
| `id` | `int(11)` | NO | `NULL` | auto_increment (PK) |
| `customer_subscription_id` | `int(11)` | NO | `NULL` |  (FK/IDX) |
| `kitchen_id` | `int(11)` | NO | `NULL` |  (FK/IDX) |
| `delivery_date` | `date` | NO | `NULL` |  |
| `meal_type` | `enum('breakfast','lunch','dinner')` | NO | `lunch` |  |
| `status` | `enum('pending','dispatched','delivered','cancelled')` | NO | `pending` |  |
| `created_at` | `timestamp` | NO | `current_timestamp()` |  |
| `updated_at` | `timestamp` | NO | `current_timestamp()` | on update current_timestamp() |

---

### `tiffin_subscriptions`
- **Purpose:** Subscription plans offered by Kitchens
- **How Data is Inserted:** Chef portal (`POST /chef/subscription-plans/add`)

| Attribute | Type | Nullable | Default | Extra |
|---|---|---|---|---|
| `id` | `int(11)` | NO | `NULL` | auto_increment (PK) |
| `kitchen_id` | `int(11)` | NO | `NULL` |  (FK/IDX) |
| `plan_name` | `varchar(50)` | NO | `NULL` |  |
| `plan_type` | `enum('weekly','monthly')` | NO | `NULL` |  |
| `price` | `decimal(8,2)` | NO | `NULL` |  |
| `description` | `text` | YES | `NULL` |  |
| `meals_per_day` | `tinyint(3) unsigned` | NO | `NULL` |  |
| `is_active` | `tinyint(1)` | YES | `1` |  |
| `created_at` | `timestamp` | NO | `current_timestamp()` |  |

---

### `zero_waste_items`
- **Purpose:** Discounted surplus food items
- **How Data is Inserted:** Chef Portal (`POST /chef/zero-waste/add`)

| Attribute | Type | Nullable | Default | Extra |
|---|---|---|---|---|
| `id` | `int(11)` | NO | `NULL` | auto_increment (PK) |
| `menu_item_id` | `int(11)` | NO | `NULL` |  (FK/IDX) |
| `kitchen_id` | `int(11)` | NO | `NULL` |  (FK/IDX) |
| `original_price` | `decimal(6,2)` | NO | `NULL` |  |
| `discounted_price` | `decimal(6,2)` | NO | `NULL` |  |
| `quantity_available` | `smallint(5) unsigned` | NO | `NULL` |  |
| `expiry_time` | `datetime` | NO | `NULL` |  |
| `status` | `enum('active','sold_out','expired')` | YES | `active` |  |
| `created_at` | `timestamp` | NO | `current_timestamp()` |  |

---

