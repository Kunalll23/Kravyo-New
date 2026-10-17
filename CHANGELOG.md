# Kravyo — Project Changelog

> **Purpose:** This file tracks every significant change made to the Kravyo project — what was built, what was modified, and why. Each entry includes the date, time (IST), a clear summary, and the files involved.
>
> **Format:** Entries are ordered from newest → oldest. Each entry starts with the date/time and a version/phase label.

## 📅 17-10-26 | 05:43 PM IST

### 🛠️ Feature: Upgraded Admin Zero Food Waste Dashboard with Search, Moderation Actions & Reporting

- **Goal & Summary:** Transformed `/admin/zero-waste` from a static display into a fully interactive moderation and reporting control center with multi-criteria filtering, direct listing moderation actions, real waste rescue metrics, and automated CSV report generation.
- **Key Changes:**
  - **Admin Actions & Moderation:** Added an `Actions` column with:
    - **Force-Expire (`POST /admin/zero-waste/expire/{id}`):** Allows admins to immediately deactivate active surplus deals.
    - **Permanent Delete (`POST /admin/zero-waste/delete/{id}`):** CSRF-protected deletion with confirmation prompt to eliminate invalid/spoiled listings.
    - **Sweep Past-Expiry Deals (`POST /admin/zero-waste/sweep-expired`):** One-click batch action to update stale listings whose expiry time has passed.
  - **Comprehensive Multi-Filter Toolbar:** Added keyword search (by food item name, kitchen name, city, or `#ID`), kitchen select, city select, status dropdown, and time range presets (`Today`, `Last 7 Days`, `Last 30 Days`).
  - **Visual Dish Thumbnails & Dietary Icons:** Displayed food item thumbnails with fallback images and pure vegetarian (🟢) / non-vegetarian (🔺) dietary indicator badges.
  - **Real Waste Rescue & Impact KPIs:** Added operational metrics for actual meals rescued in customer orders, realized rescued revenue, potential savings, and portion availability.
  - **CSV Report Generator (`GET /admin/zero-waste/export`):** Allows admins to download filtered Zero Waste reports with complete item details, pricing, discount percentages, and timestamps.
- **Files Modified:**
  - `views/admin/zero_waste.php`
  - `app/controllers/AdminController.php`
  - `app/models/ZeroWasteItem.php`
  - `config/routes.php`
  - `CHANGELOG.md`
- **Database Changes:** None.

---

## 📅 17-10-26 | 05:15 PM IST

### 🛠️ Feature & Bugfix: Admin Review Search Live Typeahead Auto-Suggestions & Parse Error Fix

- **Goal & Summary:** Fixed syntax parse error in `Review.php` and completed live typeahead search auto-suggestions for Customer names, Kitchens, and Orders on the Admin Reviews Management dashboard (`/admin/reviews`).
- **Key Changes:**
  - **Fixed Parse Error:** Restored the clean closing brace and full `Review::getReviewSearchSuggestions()` definition in `app/models/Review.php`.
  - **Live Auto-Suggestions for Admin Search:** Added typeahead dropdown for the search input with instant in-memory suggestions on focus/click (0ms latency) and AJAX live queries (`/admin/reviews/suggest`) on typing with 120ms debounce.
  - **Category-Grouped Results:** Organized suggestions with badges and icons for **Customers** (👤), **Kitchens** (🍽️), **Orders** (🧾), and **Dishes** (🍲), plus a quick "Search all reviews" action.
  - **Zero-Interference UI & Stacking:** Replaced Bootstrap `.dropdown-menu` with isolated `.kravyo-admin-suggestions` class and `position: relative; z-index: 1050;` on the toolbar card to prevent any Bootstrap dropdown collision or table stacking overlap.
  - **Keyboard Navigation & Instant Filter:** Added full <kbd>↓</kbd>, <kbd>↑</kbd>, <kbd>Enter</kbd>, and <kbd>Esc</kbd> support. Selecting any suggestion instantly updates the input and triggers the filtered search without manual typing.
- **Files Modified:**
  - `app/models/Review.php`
  - `views/admin/reviews.php`
  - `app/controllers/AdminController.php`
  - `config/routes.php`
  - `CHANGELOG.md`
- **Database Changes:** None.

---

## 📅 17-10-26 | 04:36 PM IST

### 🛠️ Feature: Redesigned Admin Review Management & Moderation Dashboard

- **Goal & Summary:** Upgraded `/admin/reviews` with platform KPI analytics, interactive filters (kitchen, city, rating, time range, search), order deep-linking, ordered dish tags, and a full statement detail modal.
- **Key Changes:**
  - **Platform Review KPI Summary:** Added top metric cards for Platform Rating Score (`avg / 5.0`), Satisfaction Rate (`positive %`), Total Reviews, and Critical Alerts (`1–2★` feedback).
  - **Comprehensive Admin Filtering:** Implemented `Review::findFilteredForAdmin()` and `Review::getPlatformReviewMetrics()` supporting filtering by kitchen, city, rating score, time presets (`today`, `7days`, `30days`, `this_month`, `custom` dates), sorting, and multi-field keyword search (customer, kitchen, review text, order number).
  - **Order Deep-Linking & Dish Context:** Linked each review's `#ORD...` badge directly to `/admin/orders?search={order_number}` and displayed pill tags for dishes included in that order.
  - **Zero-Flicker Review Details Modal:** Added a root-level Bootstrap modal to inspect untruncated customer statements, customer phone/email, kitchen details, and a direct button to inspect the order.
  - **Safe Permanent Deletion:** Preserved admin moderation power with CSRF-protected deletion forms and confirmation prompts.
- **Files Modified:**
  - `views/admin/reviews.php`
  - `app/controllers/AdminController.php`
  - `app/models/Review.php`
  - `CHANGELOG.md`
- **Database Changes:** None.

---

## 📅 17-10-26 | 04:28 PM IST

### 🛠️ Improvement: Direct Order Linking & Search from Reviews to Chef Incoming Orders

- **Goal & Summary:** Clicking an order number in the chef reviews feed now directly opens and highlights that exact order on the Chef Incoming Orders dashboard (`/chef/orders?search=...#order-ID`) instead of showing the entire un-filtered orders list.
- **Key Changes:**
  - **Direct Order Navigation in Reviews:** Updated the `Order #...` badge in [views/chef/reviews.php](file:///c:/xampp/htdocs/Kravyo/views/chef/reviews.php) to link to `/chef/orders?search={order_number}#order-{order_id}`.
  - **Order Search in Chef Orders:** Added search parameter support to `Order::findByKitchenId()` and `ChefController::orders()`. Searching an order automatically removes status restrictions so delivered/past orders are immediately returned.
  - **Order Card Search Bar & Match Highlighting:** Added an order search form on [views/chef/orders.php](file:///c:/xampp/htdocs/Kravyo/views/chef/orders.php), anchor `id="order-{id}"`, and glowing highlight styling with a **"Target Order"** badge when an order is matched via search.
- **Files Modified:**
  - `views/chef/reviews.php`
  - `views/chef/orders.php`
  - `app/controllers/ChefController.php`
  - `app/models/Order.php`
  - `CHANGELOG.md`
- **Database Changes:** None.

---

## 📅 17-10-26 | 04:14 PM IST

### 🛠️ Feature: Comprehensive Chef Customer Reviews & Food Quality Insights Dashboard

- **Goal & Summary:** Built a dedicated, analytics-rich reviews portal for home chefs (`/chef/reviews`) allowing them to monitor customer ratings, filter reviews by specific dishes, time ranges, and sentiment scores.
- **Key Changes:**
  - **New Chef Reviews Route & Controller:** Added `GET /chef/reviews` in `config/routes.php` and implemented `ChefController::reviews()` to fetch filtered reviews, sentiment metrics, and dish aggregates.
  - **Advanced Filtering & Search:** Supported reactive filtering by individual dish (`dish_id`), rating score (1 to 5 stars, or Positive vs. Critical), time presets (`all`, `today`, `7days`, `30days`, `this_month`, `custom` date range), sort order (`recent`, `oldest`, `highest`, `lowest`), and keyword search.
  - **Dish-Level Feedback Association:** Joined `order_items` and `menu_items` with reviews to visually tag exactly which dishes were in each reviewed order.
  - **Chef Review Analytics:** Created `Review::findFilteredForChef()`, `Review::getDishRatingsSummary()`, and `Review::getKitchenReviewMetrics()` for star distribution bars, satisfaction rates, and per-dish rating badges.
  - **New View:** Created `views/chef/reviews.php` with summary score cards, clickable star distribution filters, performance-by-dish chips, filter toolbar, and full customer feedback cards with dish badges.
  - **Navigation Integration:** Added "Customer Reviews" to the chef navbar dropdown in `views/partials/header.php` and "View All" / "See All Customer Reviews" links to the "Recent Reviews" card on `views/chef/dashboard.php`.
  - **PDO Parameter Binding Fix:** Used distinct named placeholders (`:search1`, `:search2`, `:search3`) for `LIKE` clauses to ensure compatibility with strict non-emulated PDO prepared statements.
- **Files Modified:**
  - `views/chef/reviews.php` (created)
  - `app/models/Review.php`
  - `app/controllers/ChefController.php`
  - `config/routes.php`
  - `views/chef/dashboard.php`
  - `views/partials/header.php`
  - `CHANGELOG.md`
- **Database Changes:** None (leveraged existing `reviews`, `orders`, `order_items`, and `menu_items` tables).

---

## 📅 17-10-26 | 03:45 PM IST

### 🛠️ Feature: Automated Live Heartbeat & Online Status Staleness Cleanup

- **Goal & Summary:** Fixed false "Online" statuses across delivery partners by implementing an automatic 5-minute heartbeat check, background cleanup, and one-click database offline reset.
- **Key Changes:**
  - **Live Heartbeat Verification:** Delivery partners now only display as `🟢 Online` if `is_online = 1` AND their `last_seen_at` heartbeat timestamp was recorded within the last 5 minutes.
  - **Human-Readable Last Seen:** If offline, the UI accurately shows relative activity (e.g. `⚪ Offline`, `Last seen 15m ago`, `Last seen 2h ago`).
  - **Automated Stale Cleanup:** Added `DeliveryPartner::cleanupStaleOnlinePartners(5)` which runs on dashboard load to automatically set `is_online = 0` for any rider whose heartbeat has lapsed.
  - **Touch Heartbeat Method:** Implemented `DeliveryPartner::touchHeartbeat()` method to update `last_seen_at` on rider dashboard activity.
- **Files Modified:**
  - `views/admin/delivery_partners.php`
  - `app/controllers/AdminController.php`
  - `app/models/DeliveryPartner.php`
  - `CHANGELOG.md`
- **Database Changes:** Stale `is_online` values reset to `0` for inactive partners.

## 📅 17-10-26 | 03:39 PM IST

### 🛠️ Feature: Interactive City Filter for Delivery Partners Management

- **Goal & Summary:** Removed the force online/offline simulation buttons and integrated instant reactive city filtering for the Delivery Partners management dashboard.
- **Key Changes:**
  - **Removed Simulation Actions:** Removed the "Force Online" and "Force Offline" buttons and form action from the header.
  - **Instant City Filter:** Converted the city dropdown into an instant reactive GET filter (`onchange="this.form.submit()"`), with a dedicated reset button when active.
  - **State Preservation in Tabs:** Updated status tabs (`All Partners`, `Pending`, `Approved`) with helper `dpTabUrl()` to maintain the active city filter across tab switches.
  - **Backend Filtering:** Updated `AdminController::deliveryPartners()` and `DeliveryPartner::findAllWithUser()` to filter delivery partners by `city` using case-insensitive matching.
  - **Expanded Cities List:** Updated `DeliveryPartner::getAvailableCities()` to aggregate distinct cities across delivery partners, kitchens, and addresses.
  - **Visual Indicator:** Added a city badge in the "Partner Info" column and improved the empty state when no partners exist in a chosen city.
- **Files Modified:**
  - `views/admin/delivery_partners.php`
  - `app/controllers/AdminController.php`
  - `app/models/DeliveryPartner.php`
  - `CHANGELOG.md`
- **Database Changes:** None.

## 📅 17-10-26 | 03:30 PM IST

### 🐛 Bug Fix: Eliminate Modal Dialog Blinking & Stacking Context Conflict

- **Goal & Summary:** Fixed the continuous blinking and flickering issue on the "Manage Partner" dialog modal in the Delivery Partners Admin section.
- **Root Cause:**
  1. The modal markup was being rendered repeatedly inside table cells (`<td>...</td>`) wrapped in `<div class="card kravyo-card">`.
  2. `.kravyo-card:hover` applies `transform: translateY(-5px)`, which creates a CSS containing block and breaks `position: fixed` modals.
  3. The Bootstrap backdrop on `<body>` repeatedly triggered hover enter/leave oscillation against the table row and card, causing high-frequency rendering jitter.
- **Key Changes:**
  - Removed `kravyo-card` hover transform from the data table container in `views/admin/delivery_partners.php`.
  - Extracted the modal out of the `<table>` and `<div class="card">` entirely to root DOM level.
  - Converted the repeated per-row modal loop into a single reusable, high-performance `#managePartnerModal` dynamically populated on open via JavaScript.
- **Files Modified:**
  - `views/admin/delivery_partners.php`
  - `CHANGELOG.md`
- **Database Changes:** None.

## 📅 17-10-26 | 11:36 AM IST

### 🎨 UI & UX Implementation: Edge-to-Edge Full-Width Production Landing Hub

- **Goal:** Make the approved ultra-premium UI mockup span 100% full-width edge-to-edge across the viewport without letterboxing, side gaps, or top spacing.
- **Key Changes:**
  - **Edge-to-Edge Full-Width Hero:** Replaced boxed wrapper with a true `width: 100%` dark slate hero spanning from screen edge to screen edge, touching the top boundary with zero unwanted margin gaps.
  - **Layout Spacing Reset:** Updated `views/layouts/public_portal.php` to remove the default `mt-3` container so page content starts flush at the top.
  - **Fluid Responsive Structure:** Wrapped hero content and portal cards in `container-xl` to ensure optimal readability on widescreen monitors while background colors extend 100% across the viewport.
  - **Floating Badges & Proportions:** Refined positioning of the glassmorphic `45+ Verified Home Kitchens` and `Live GPS Tracking` floating badges on the food hero platter.
  - **Appetizing Food Showcase & Glassmorphism:** Integrated high-resolution Indian home-cooked meal banquet asset (`kravyo_hero_thali.jpg`) accompanied by floating glassmorphic badges (`45+ Verified Home Kitchens` with green checkmark and `Live GPS Tracking` with coral map pin).
  - **4 Distinct Role Gateway Cards:** Built color-coded cards matching the mockup:
    - 🟠 **Customer (Coral):** Header banner, *Order Fresh Food*, full-width *Order Food* button, and dual *Login* / *Sign Up* buttons.
    - 🟡 **Home Chef (Amber):** Header banner, *Partner Kitchen*, *Chef Portal* button, and dual *Login* / *Register* buttons.
    - 🟢 **Delivery Partner (Emerald):** Header banner, *Delivery Fleet*, *Driver Login* button, and dual *Login* / *Join Fleet* buttons.
    - 🟣 **Administrator (Deep Indigo):** Header banner, *Admin Console*, and direct secure *Admin Console* login.
  - **Live Stats Strip:** Added bottom dark charcoal strip showcasing 4 live platform metrics (45+ Home Kitchens, 1,250+ Active Foodies, 99.4% On-Time Deliveries, and Google Maps Live GPS Engine).
- **Files Modified / Added:**
  - `views/home/index.php`
  - `public/assets/images/kravyo_hero_thali.jpg` (New asset)
  - `CHANGELOG.md`

## 📅 16-10-26 | 11:35 PM IST

### 📚 Documentation: Platform-Wide Synchronization, Google Maps Audit & Architecture Verification System

- **Goal:** Update and synchronize all primary Kravyo architectural, database, and setup documentation files, verify the Google Maps Platform integration, and install a permanent automated verification mechanism to prevent documentation drift in future.
- **Key Changes:**
  - **`README.md` ([README.md](file:///c:/xampp/htdocs/Kravyo/README.md)):** Overhauled the platform architecture guide to document all 4 isolated portals (`/`, `/chef`, `/delivery`, `/admin`) and session cookies (`KRAVYO_CUSTOMER`, `KRAVYO_CHEF`, `KRAVYO_DELIVERY`, `KRAVYO_ADMIN`). Fully documented the **Google Maps Platform** implementation: Maps JavaScript API with Advanced Markers, Google Routes API v2, Geocoding API, Distance Matrix API, server-side caching proxy ([GoogleMaps.php](file:///c:/xampp/htdocs/Kravyo/core/GoogleMaps.php) & [MapsController.php](file:///c:/xampp/htdocs/Kravyo/app/controllers/MapsController.php)), and Pusher WebSockets for real-time delivery tracking. Documented the full 12-controller and 2-service MVC architecture, 4-stage smart cascade dispatch, automated Razorpay refunds, tiffin subscriptions, zero waste rescue, and Python AI microservice.
  - **`ROADMAP.md` ([ROADMAP.md](file:///c:/xampp/htdocs/Kravyo/ROADMAP.md)):** Expanded the milestone roadmap to track all 14 completed development phases. Updated Phase 11 and Phase 12 to detail the production Google Maps Platform integration, Google Routes API v2 polyline rendering, and Pusher WebSockets real-time driver tracking.
  - **`kravyo_data_dictionary.md` ([kravyo_data_dictionary.md](file:///c:/xampp/htdocs/Kravyo/kravyo_data_dictionary.md)):** Verified complete coverage for all 20 database tables in `kravyo_db`, detailing schema attributes, constraints, default values, UI ingestion triggers, and Mermaid ER diagram.
  - **`python_ai/README.md` ([python_ai/README.md](file:///c:/xampp/htdocs/Kravyo/python_ai/README.md)):** Added dynamic hyperparameter configuration documentation (`ai_config.json`, `GET /config`, `POST /config`), updated full REST API references, and documented the zero-downtime PHP fallback engine.
  - **System Audit Tool ([scripts/system_audit.php](file:///c:/xampp/htdocs/Kravyo/scripts/system_audit.php)):** Created a standalone CLI introspection tool (`php scripts/system_audit.php`) that programmatically verifies the live database table count, controllers, services, models, 3rd-party integrations, scans for stale library traces, and audits all 21 active notification trigger emission points.
  - **Strict Documentation & Anti-Assumption Rules ([.agents/rules/documentation_standards.md](file:///c:/xampp/htdocs/Kravyo/.agents/rules/documentation_standards.md) & [.agents/rules/strict_verification_protocol.md](file:///c:/xampp/htdocs/Kravyo/.agents/rules/strict_verification_protocol.md)):** Established persistent workspace rules enforcing a Proof-First Protocol — requiring mandatory code search and exact file/line citation before making any factual statement about Kravyo's behavior.
- **Files Modified / Added:**
  - `README.md`
  - `ROADMAP.md`
  - `python_ai/README.md`
  - `scripts/system_audit.php` (New)
  - `.agents/rules/documentation_standards.md` (New)
  - `.agents/rules/strict_verification_protocol.md` (New)
  - `CHANGELOG.md`

## 📅 16-10-26 | 11:00 PM IST

### 🛠️ Feature & Bug Fixes: Address Soft-Delete, Notification Navigation, Kitchen Updates & Doc Cleanup

- **Goal:** Fix address deletion constraint errors, repair notification dropdown click navigation, improve Bardoli kitchen catalog, and clean up outdated documentation.
- **Key Changes:**
  - **Address Soft-Deletion:** Added `is_deleted` column to `addresses` table. Updated `Address` model methods (`findByUserId`, `findByIdAndUserId`, `countByUserId`) to filter out soft-deleted addresses. Updated `CustomerController::deleteAddress()` to catch foreign key constraints on past orders and soft-delete gracefully while preserving order history. Added automatic fallback to re-sync active session city.
  - **Notification Dropdown Click & Navigation:** Replaced brittle inline click loops in `views/partials/header.php` with event delegation on `#notifDropdownList` using `e.target.closest('a')`. Added automatic reload for same-page notification clicks (e.g. order tracking updates). Made all notification cards on the full inbox page (`views/customer/notifications.php`) fully clickable with interactive hover styling.
  - **Kitchen Renaming & Catalog Expansion:** Renamed dummy kitchens in Bardoli: Kitchen 44 &rarr; *Meera's Cloud Kitchen* (`img_1.png`) and Kitchen 45 &rarr; *Ramanbhai ni Rasoi* (`img_2.png`). Added 2 fresh homemade dishes with descriptions, realistic pricing, and images across all 45 kitchens in the platform (90 dishes total).
  - **Documentation Cleanup:** Removed redundant and outdated documentation files (`CHANGE_TRACKING.md`, `docs/API_REFERENCE.md`, `tiffin_dispatch_flow.md`, `project_change_log.md`).
- **Files Modified:**
  - `app/models/Address.php`
  - `app/controllers/CustomerController.php`
  - `app/controllers/SubscriptionController.php`
  - `views/partials/header.php`
  - `views/customer/notifications.php`
  - `.agents/rules/update_changelog.md` (New automatic changelog tracking rule)
- **Database Changes:**
  - Added `is_deleted TINYINT(1) NOT NULL DEFAULT 0` column to `addresses` table.
  - Updated `kitchen_name` and `banner_image` for Kitchen IDs 44 and 45.
  - Inserted 90 new dishes into `menu_items`.

## 📅 2026-10-02 | 01:20 IST

### 📍 Feature: Live GPS Delivery Tracking

- **Goal:** Added live GPS tracking capability for active deliveries without replacing the core OTP verification workflow or status timeline.
- **Key Capabilities:**
  - **Delivery Partner Application:** Integrated browser Geolocation API (`watchPosition`) to continuously push coordinates to the server via AJAX during an active delivery. Added a clear on/off toggle.
  - **Customer Application:** Integrated a Leaflet map into the Order Track view that polls the server for the delivery partner's live location and updates smoothly on the map alongside a freshness timestamp.
  - **Dynamic Map Plotting & Routing:** Added a Red marker for the Starting Point (Kitchen) and real-time **OSM Nominatim Geocoding** to convert the customer's text address into a precise Green marker (Destination).
  - **Rapido-style Route Highlighting:** Integrated `leaflet-routing-machine` for BOTH the Customer and the Delivery Partner. The map now constantly draws the best highlighted path from the Delivery Partner's live location directly to the Customer's destination.
  - **Security & Scope:** Map only renders when the order is "Out for delivery". GPS updates strictly require delivery partner authentication and CSRF tokens. Tracking strictly stops when the order is completed (OTP verified).
  - **Independence:** Kept the 4-role independent session architecture fully intact.
- **Files Modified:**
  - `config/routes.php` (Added `POST /delivery/location/update` and `GET /order/location/{id}`)
  - `app/controllers/DeliveryController.php` (Added `updateLocation()`)
  - `app/controllers/OrderController.php` (Added `getDeliveryLocation()`)
  - `app/models/Order.php` (Updated `findWithDetails` to fetch kitchen coords)
  - `views/delivery/order_detail.php` (Added Live GPS Tracking widget and JS)
  - `views/customer/order_track.php` (Added Leaflet map and AJAX polling)
- **Database Changes:**
  - Added `location_updated_at` (TIMESTAMP) column to `delivery_partners` table.

## 📅 2026-10-01 | 14:35 IST

### 🗺️ Feature: Nearby Cloud Kitchens Interactive Map System (Production-Ready)

- **Goal:** Implement a complete, production-grade "Nearby Cloud Kitchens" discovery feature for Kravyo using Leaflet.js, OpenStreetMap tiles, MariaDB geospatial indexing, and PHP 8.4 backend.
- **Key Capabilities:**
  - Customer interactive map with real-time browser Geolocation API (`navigator.geolocation.getCurrentPosition`).
  - Distinct custom map markers: animated pulsing blue pin for customer ("You are here") and Kravyo orange pins for kitchens with open/closed indicators.
  - Side-by-side desktop layout with responsive scrollable kitchen card list + interactive map (collapses cleanly on mobile with map on top).
  - Radius selector (2 km, 5 km, 10 km default, 20 km, 50 km) with instant live filtering.
  - Graceful location denial fallback with friendly banner and quick-jump city selector (Pune, Surat, Mumbai, Ahmedabad, Bengaluru, Delhi).
  - Interactive card-to-map synchronization: clicking a card centers map, zooms to kitchen, and opens its detail popup; clicking a marker highlights the card.
  - Real database integration: queries actual approved kitchens from `kitchens` table with live ratings from `reviews` and dish counts from `menu_items`.
  - Direct linking: marker popups and cards feature "View Kitchen" buttons linking to existing kitchen profile routes (`ctx_url('/kitchen/{id}')`).
  - Chef location manager in `views/chef/profile.php`: interactive Leaflet map picker allowing chefs to click/drag a pin or click "Detect My Location" to save exact coordinates.
  - Admin panel indicator in `views/admin/chefs.php` showing kitchen map coordinates status.
- **Database Changes:**
  - Migration: `database/nearby_kitchens_migration.sql`
  - Added `latitude` DECIMAL(10, 8) NULL and `longitude` DECIMAL(11, 8) NULL to `kitchens` table.
  - Added composite index `idx_kitchens_geo` (`approval_status`, `latitude`, `longitude`) for fast spatial filtering.
  - Seed script: `database/seed_kitchen_coordinates.php` populated 42 existing database kitchens across major cities with realistic coordinates.
- **Backend & API Changes:**
  - `app/models/Kitchen.php`: Added `findNearby()` (bounding-box pre-filtering + Haversine distance formula), `findAllWithCoordinates()`, and `updateCoordinates()`.
  - `app/controllers/CustomerController.php`: Added `nearbyKitchens()` (view renderer) and `apiNearbyKitchens()` (secure JSON API endpoint at `GET /api/kitchens/nearby`).
  - `app/controllers/ChefController.php`: Added latitude/longitude input sanitization, bounds validation (-90 to 90, -180 to 180), and database persistence in `updateProfile()`.
  - `config/routes.php`: Added routes for `GET /nearby-kitchens`, `GET /kitchens/nearby`, `GET /api/kitchens/nearby`, `GET /chef/nearby-kitchens`, `GET /admin/nearby-kitchens`.
  - `core/Helpers.php`: Updated `ctx_url()` to support session-safe `/nearby-kitchens` routing across Customer, Chef, and Admin contexts.
- **Frontend & Views:**
  - Created `views/customer/nearby_kitchens.php` — Complete production-style interactive map with Leaflet.js, OpenStreetMap tiles, live distance badges, search & sort.
  - Updated `views/chef/profile.php` — Integrated Leaflet map picker for chefs with draggable pin & GPS auto-detection.
  - Updated `views/partials/header.php` & `public/assets/css/style.css` — Added "Nearby Map" navigation link with context-aware session handling and perfected single-line horizontal alignment with `white-space: nowrap`, fluid container, and balanced spacing.
  - Updated `views/customer/kitchens.php` — Added direct "Nearby Kitchens Map" button in hero.
  - Updated `views/home/index.php` — Added "Nearby Map" action button in hero.
  - Updated `views/admin/chefs.php` — Added kitchen map coordinates status badge.
- **Security & Privacy:**
  - Strict server-side validation: coordinates bounded (-90 <= lat <= 90, -180 <= lng <= 180); radius clamped (1 to 200 km).
  - All database queries use PDO prepared statements or cast float values in distance math, preventing SQL injection.
  - Output sanitized via `htmlspecialchars` in both PHP views and API JSON responses.
  - Only approved kitchens displayed; unapproved, rejected, or suspended kitchens strictly excluded.
  - API exposes only safe public fields (no passwords, tokens, admin notes).
  - No background tracking or continuous customer GPS tracking.
- **Testing & Verification:**
  - Automated test suite (`scratch/test_suite.php`): 20/20 test assertions passed covering all 11 test scenarios specified in requirements.
  - Distance calculation verified against known coordinates (~1.77 km between Mehul's Kitchen in Shivaji Nagar and Pune center).
  - PHP linting (`php -l`): Passed on all 10 modified/created PHP files with 0 syntax errors.
  - HTTP API testing: Verified JSON output and 400 response on invalid coordinates.
- **Backups:** `scratch/backup_nearby_kitchens_20261001/`
- **Known Limitations:** Map relies on browser geolocation permission or user selecting a city/pin; client-side tiles require internet access to OpenStreetMap CDN.

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
