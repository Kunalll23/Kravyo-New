# Kravyo — Project Changelog

> **Purpose:** This file tracks every significant change made to the Kravyo project — what was built, what was modified, and why. Each entry includes the date, time (IST), a clear summary, and the files involved.
>
> **Format:** Entries are ordered from newest → oldest. Each entry starts with the date/time and a version/phase label.

---

## 📅 2026-10-01 | 14:05 IST

### 🎯 UX: Simplified Customer-Facing Order Tracking Timeline

- **Goal:** Reduce the customer-visible order progress from 5 steps to a cleaner 4-step cloud-kitchen style tracking flow.
- **Before:** Order Placed → Accepted → Preparing → Out for Delivery → Delivered (5 steps)
- **After:** Order Confirmed → Preparing → Out for Delivery → Delivered (4 steps)
- **Key Design Decision:** The backend `accepted` status is preserved for chef workflow, but the customer never sees "Accepted" as a separate step. Both `pending` and `accepted` backend statuses map to "Order Confirmed" on the customer timeline. The timeline only advances to "Preparing" when the chef explicitly starts preparing.
- **Changes:**
  - `app/controllers/OrderController.php` — Redesigned `trackOrder()` timeline data: 4 customer-facing steps with a `$backendToCustomerStep` mapping array. New `$customerStep` variable passed to the view.
  - `views/customer/order_track.php` — Updated timeline rendering to use the 4-step `$customerStep` progress. Status badge now shows customer-friendly labels (e.g. "Order Confirmed" instead of "Accepted").
  - `views/customer/order_history.php` — Updated status labels and icons to use customer-friendly terminology. `pending`/`accepted` both show as "Order Confirmed".
  - `app/controllers/ChefController.php` — Updated notification message for `ORDER_STATUS_ACCEPTED`: changed "accepted by the chef" to "confirmed" for consistency.
- **Backend preserved:** All 6 backend statuses (`pending`, `accepted`, `preparing`, `out_for_delivery`, `delivered`, `cancelled`) are untouched. Chef order management flow, status transitions, and database schema remain identical.
- **Backups:** `scratch/backup_order_tracking_20261001/`
- **Syntax verification:** All 3 modified view/controller files passed `php -l` checks.
- **Remaining limitations:** None — this is a display-only change.

---

## 📅 2026-09-30 | 23:15 IST

### 🐛 Critical Fix: Session ID Collision in `switchToRole()` — Chef Login Overwrites Customer Data

- **Problem:** After logging in as Customer (Tab 1), logging in as Chef (Tab 2) would overwrite the Customer's session data. Navigating back to Tab 1 and clicking any link (e.g. "Browse Dishes") would show the Chef identity instead of the Customer.
- **Root Cause:** In `Session::switchToRole()`, after `session_write_close()`, PHP's internal `session_id()` still holds the previous session's ID. When `session_start()` is called with a new session name (`KRAVYO_CHEF`), PHP reuses that cached ID if no cookie override is found — or if the existing cookie already has the same ID (from a previous buggy session). Both `KRAVYO_CUSTOMER` and `KRAVYO_CHEF` end up pointing to the **same session file** on disk. Writing Chef data overwrites the Customer data.
- **Proof:** Created a standalone PHP script that reproduces the bug — confirmed session IDs are identical and data corruption occurs.
- **Fix:** Captures the source session ID before `session_write_close()`. Before `session_start()` for the target role, checks whether the target cookie is missing OR its value matches the source session ID (collision). In either case, generates a fresh session ID via `bin2hex(random_bytes(16))`. This guarantees each role always gets its own independent session file, even if corrupted cookies remain in the browser.
- **Files modified:**
  - `core/Session.php` — robust session ID collision detection in `switchToRole()`
- **Backup:** `scratch/session_backup_20260930/Session_pre_switchfix.php`

---

## 📅 2026-09-30 | 21:48 IST

### 🔧 Fix: Three-Way Session Separation — Customer, Chef, Admin Concurrent Login

- **Problem:** Customer and Chef both used the same `KRAVYO_USER` session cookie, causing session conflict when both were logged in simultaneously in different browser tabs. Logging in as Chef in Tab 2 would overwrite the Customer session in Tab 1.

- **Root Cause:** `Session::detectContext()` only differentiated between `/admin/*` (KRAVYO_ADMIN) and everything else (KRAVYO_USER). The `/login` endpoint was shared by both Customer and Chef, so both wrote into the same cookie.
- **Solution:** Implemented a **three-cookie architecture**:
  - `KRAVYO_CUSTOMER` — Customer portal (all non-`/chef`, non-`/admin` routes)
  - `KRAVYO_CHEF` — Chef portal (`/chef/*` routes)
  - `KRAVYO_ADMIN` — Admin portal (`/admin/*` routes)
- **Key design decision:** Since `/login` is shared by Customer and Chef, a new `Session::switchToRole(string $role)` method was added. It is called in `AuthController::login()` and `AuthController::verifyEmail()` **after** credential validation but **before** writing `user_id`/`user_role`, ensuring data always lands in the correct cookie.
- **`switchToRole()` mechanism:**
  1. Saves temp session data (flash messages, pending email, reset ID)
  2. Calls `session_write_close()` to cleanly close the current session WITHOUT destroying its cookie
  3. Sets the new `session_name()` to the target cookie
  4. Calls `session_start()` to open the target session
  5. Restores saved temp data into the new session
- **Result:** All three roles fully independent in the same browser simultaneously
- **PHP syntax check:** `php -l` — PASS on all modified and dependent files
- **Files Created:**
  - `scratch/session_backup_20260930/Session.php.bak` — backup before change
  - `scratch/session_backup_20260930/AuthController.php.bak` — backup before change
- **Files Modified:**
  - `core/Session.php` — Added `CUSTOMER_SESSION`/`CHEF_SESSION` constants; updated `detectContext()` to detect `/chef/*`; added `switchToRole()` method; updated `destroy()` to expire the specific cookie by name
  - `app/controllers/AuthController.php` — Added `Session::switchToRole($user['role'])` call in `login()` before session writes; added same call in `verifyEmail()` before auto-login session writes
- **Files NOT Changed:**
  - `core/Middleware.php` — auth(), role(), adminAuth() all work correctly with no changes
  - `app/controllers/AdminAuthController.php` — already correct
  - `views/partials/header.php` — Session::has('admin_id') correctly returns false in user contexts
  - `views/partials/sidebar.php` — already fixed in previous session (uses Session::has('admin_id'))
  - All other controllers, models, views, routes — no changes needed
- **Limitations:**
  - If a Chef visits `/login` in a tab where only KRAVYO_CHEF is set, the page will NOT auto-redirect them to the chef dashboard (because `/login` checks the KRAVYO_CUSTOMER context). This is intentional — they can log in as a Customer in that tab.
  - Old `KRAVYO_USER` cookies from before this change remain in browsers until expiry. They are harmless but ignored.

---

## 📅 2026-09-30 | 19:21 IST

### 🔧 Fix: Multi-Tab Concurrent Login (Customer / Chef / Admin simultaneously)
- **What:** Initial two-way session separation. Created KRAVYO_USER + KRAVYO_ADMIN cookies. Customer+Chef still shared KRAVYO_USER (superseded by the entry above).
- **Files Modified:**
  - `core/Session.php`
  - `views/partials/sidebar.php`

---

## 📅 2026-09-30 | 19:13 IST

### 🆕 Created: `CHANGELOG.md` (this file)
- **What:** Created a project-level changelog to track all future and past changes made by the AI assistant to this codebase.
- **Why:** So that in any future session, the assistant can instantly understand the full history of what has been built, what was changed, and what the current state of the project is.
- **Files Created:**
  - `CHANGELOG.md` ← this file

---

## 📅 Earlier in Project — Phase 10 (Final Polish)

### ✅ Email Verification System (Full Implementation)
- **What:** Built a complete email verification flow — token generation, email dispatch, verification page UI, resend logic, and expiry handling.
- **Files Created/Modified:**
  - `app/models/EmailVerification.php` — Model handling token creation, lookup, verification, and resend throttle logic
  - `views/auth/verify_email.php` — Full-featured verification UI with animated status cards (success/expired/invalid/pending states)
  - `database/email_verification_migration.sql` — SQL migration to add `email_verified_at` column and `email_verifications` table
  - `database/email_verification_v2_migration.sql` — V2 migration with `resend_count` and `last_resend_at` columns for throttling
  - `app/controllers/AuthController.php` — Integrated verification token dispatch on registration, verify action, resend action

### ✅ Password Reset System
- **What:** Built a full forgot-password / reset-password flow using secure random tokens with expiry.
- **Files Created/Modified:**
  - `app/models/PasswordReset.php` — Token generation, validation, consumption, and cleanup logic
  - `views/auth/forgot_password.php` — Forgot password form UI
  - `views/auth/reset_password.php` — Reset password form with strength meter and confirmation
  - `database/password_reset_migration.sql` — SQL migration for `password_resets` table

### ✅ AI Recommendation Engine (Phase 10)
- **What:** Built a weighted-scoring PHP recommendation engine personalised for logged-in customers and "popular picks" fallback for guests.
- **Algorithm:** Scores dishes based on category affinity, dietary match, platform popularity, recency penalty.
- **Files Created/Modified:**
  - `app/models/Recommendation.php` — Core engine: `getPersonalizedRecommendations()`, `getPopularItems()`, scoring logic
  - `views/customer/recommendations.php` — Recommendations page UI with animated cards and score badges
  - `database/phase10_migration.sql` — `user_preferences` table migration for storing affinity data

### ✅ Performance Indexes
- **What:** Added MySQL indexes to key foreign-key and filter columns to speed up heavy queries.
- **Files Created:**
  - `database/performance_indexes.sql` — Indexes on `orders`, `menu_items`, `subscriptions`, `zero_waste_items`

### ✅ Security Hardening
- **What:** Audited all SQL queries (all confirmed as PDO prepared statements). Strengthened `sanitize()` with `ENT_SUBSTITUTE`. Added `sanitizeInput()` batch helper.
- **Files Modified:**
  - `core/` — Security utility functions updated
  - `app/controllers/AuthController.php` — Input sanitization applied consistently

---

## 📅 Earlier in Project — Phase 9 (Dashboards & Analytics)

### ✅ Admin Dashboard
- **What:** Built a full admin analytics dashboard showing platform revenue, total active users/chefs, order statistics, top chefs, and recent activity.
- **Files Modified:**
  - `app/controllers/AdminController.php` — Revenue aggregation, user/chef counts, order stats queries
  - `views/admin/` — Admin dashboard views

### ✅ Chef Dashboard (Earnings & Analytics)
- **What:** Built chef-specific analytics: daily/monthly earnings graph, top-selling dishes, total orders, customer count.
- **Files Modified:**
  - `app/controllers/ChefController.php` — Earnings queries, chart data preparation
  - `views/chef/` — Chef dashboard, earnings, analytics views

### ✅ Reviews & Ratings System
- **What:** Customers can rate and review completed orders. Chefs see their average rating on their profile.
- **Files Created/Modified:**
  - `app/models/Review.php` — Review CRUD, average rating calculation
  - Views for submitting and displaying reviews

---

## 📅 Earlier in Project — Phase 8 (Zero Food Waste)

### ✅ Zero Waste Module
- **What:** Chefs can list leftover cooked meals at discounted prices before closing. Auto-expiration when the deadline passes.
- **Files Created/Modified:**
  - `app/models/ZeroWasteItem.php` — Full CRUD, expiry logic, status management, customer-facing fetch with filters
  - `views/chef/` — Zero waste listing management UI
  - `views/home/` — Zero waste showcase section on homepage

---

## 📅 Earlier in Project — Phase 7 (Subscription/Tiffin System)

### ✅ Tiffin Subscription Module
- **What:** Chefs create Weekly/Monthly tiffin packages. Customers subscribe, see delivery schedule, manage active subscriptions.
- **Files Created/Modified:**
  - `app/models/Subscription.php` — Subscription plan CRUD
  - `app/models/CustomerSubscription.php` — Customer subscription management, delivery schedule
  - `app/models/SubscriptionDelivery.php` — Individual delivery tracking per subscription day
  - `app/controllers/SubscriptionController.php` — Full subscription checkout, view, cancel flow
  - `database/` — Subscription-related tables in `schema.sql`

---

## 📅 Earlier in Project — Phase 6 (Orders & Tracking)

### ✅ Order Placement & Live Tracking
- **What:** Full checkout flow (address + payment method selection). Chef order dashboard (Accept/Reject, update status). Customer live order timeline.
- **Files Created/Modified:**
  - `app/models/Order.php` — Order creation, status updates, history, cancellation
  - `app/models/OrderItem.php` — Line-item management per order
  - `app/controllers/OrderController.php` — Checkout, status update, cancellation, history endpoints
  - `views/customer/` — Order tracking page with live timeline
  - `views/chef/` — Incoming orders dashboard

### ✅ Notifications System
- **What:** In-app notification system for order status changes and other alerts.
- **Files Created:**
  - `app/models/Notification.php` — Notification creation, fetch, mark-as-read
  - `app/controllers/NotificationController.php` — API endpoints for notification fetch and read actions
  - `database/notifications_migration.sql` — `notifications` table migration

---

## 📅 Earlier in Project — Phase 5 (Customer Discovery & Cart)

### ✅ Food Discovery & Search
- **What:** Customers can search food by category, pincode/location, dietary filters (Veg/Non-Veg/Jain/Diabetic).
- **Files Modified:**
  - `app/controllers/CustomerController.php` — Search, filter, chef profile, food listing endpoints
  - `views/customer/` — Discovery, search, chef-profile views

### ✅ Interactive Cart System
- **What:** Session-based cart with real-time total, quantity changes, item removal.
- **Files Created/Modified:**
  - `app/controllers/CartController.php` — Add, update, remove, clear cart; session management

### ✅ Meal Customization
- **What:** Per-item customization: Oil level (Less/Normal), Spice level (Low/Medium/High), Jain preparation toggle.
- **Files Modified:**
  - `views/customer/` — Customization UI within cart/ordering flow

---

## 📅 Earlier in Project — Phase 4 (Menu Management)

### ✅ Chef Menu Builder
- **What:** Chefs can add/edit/delete their dishes with pricing, images, description, and dietary badges.
- **Files Modified:**
  - `app/models/MenuItem.php` — Menu item CRUD with category assignment and dietary flags
  - `app/models/Category.php` — Global category management
  - `app/controllers/ChefController.php` — Menu management endpoints
  - `views/chef/` — Menu builder UI

### ✅ Admin Category Management
- **What:** Admins can create/edit/delete global food categories (e.g., Gujarati, Punjabi, Baking).
- **Files Modified:**
  - `app/controllers/AdminController.php` — Category CRUD actions
  - `views/admin/` — Category management UI

---

## 📅 Earlier in Project — Phase 3 (Kitchen Verification)

### ✅ Chef Onboarding & Kitchen Verification
- **What:** Chefs register a kitchen profile (FSSAI license upload, kitchen name, address). Admin approves/rejects. Chefs toggle Open/Closed status.
- **Files Created/Modified:**
  - `app/models/Kitchen.php` — Kitchen profile CRUD, status toggle, admin verification queries
  - `app/controllers/ChefController.php` — Kitchen registration and profile endpoints
  - `app/controllers/AdminController.php` — Admin approval/rejection endpoints
  - `views/chef/` — Kitchen setup and management UI
  - `views/admin/` — Pending kitchen verification UI

---

## 📅 Earlier in Project — Phase 2 (Authentication)

### ✅ User Authentication & Role Management
- **What:** Full registration (Customer vs Chef role selection), login with bcrypt password hashing, session initialization, profile management, and route protection middleware.
- **Files Created/Modified:**
  - `app/models/User.php` — User lookup, creation, password verification
  - `app/models/Admin.php` — Admin-specific model
  - `app/controllers/AuthController.php` — Register, login, logout, profile update actions
  - `app/controllers/AdminAuthController.php` — Separate admin login flow
  - `app/middleware/` — `Middleware::role()` for route protection
  - `views/auth/login.php` — Login form UI
  - `views/auth/register.php` — Registration form with role selector UI

---

## 📅 Earlier in Project — Phase 1 (Foundation & Architecture)

### ✅ Project Scaffolding & Core Architecture
- **What:** Set up the full XAMPP-compatible MVC directory structure. Built core classes (Router, Controller, Model base), template engine (header/footer/view rendering), session & CSRF middleware, MySQL PDO wrapper, base CSS layout.
- **Files Created:**
  - `core/` — Router, App bootstrap, base Controller, base Model, Database wrapper
  - `config/` — DB config, app config
  - `public/index.php` — Front controller (all requests routed here)
  - `.htaccess` — URL rewriting rules
  - `views/layouts/` — Base HTML layout (header, footer partials)
  - `views/partials/` — Reusable UI partials (navbar, alerts, etc.)
  - `public/css/` — Base responsive stylesheet
  - `database/schema.sql` — Full initial database schema (all core tables)
  - `database/seeder.php` — Database seeder for test data
  - `README.md` — Project introduction and setup guide
  - `ROADMAP.md` — Phase-by-phase academic execution plan

### ✅ Admin Database Seeding
- **What:** Created seeder for Mehul dashboard test data and admin table setup.
- **Files Created:**
  - `database/admin_table_migration.sql` — Admin users table migration
  - `database/seed_mehul_dashboard.php` — Rich seed data for admin/chef/customer demo

### ✅ XAMPP MySQL Fix Script
- **What:** Batch script to fix common XAMPP MySQL startup issues (port conflicts, data dir issues).
- **Files Created:**
  - `fix_xampp_mysql.bat` — Windows batch fix script

---

## 📝 How to Use This File

- **Every time a change is made**, a new entry is added at the **top** of this file (newest first).
- **Entry format:**
  ```
  ## 📅 YYYY-MM-DD | HH:MM IST

  ### [Label]: Short Title
  - **What:** What was built or changed
  - **Why:** Reason for the change
  - **Files Created:** list of new files
  - **Files Modified:** list of changed files
  ```
- This file lives at the root of the project, right after `ROADMAP.md`.
