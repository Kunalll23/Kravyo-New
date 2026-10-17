# Kravyo — Multi-Tenant Cloud Kitchen & Tiffin Subscription Platform

**Kravyo** is a production-ready, full-stack cloud kitchen and meal subscription platform designed to empower homemakers, independent home chefs, and micro food entrepreneurs. It enables home chefs to operate authentic, hygienic virtual kitchens without physical restaurant overheads, connected to nearby customers through a smart hybrid delivery dispatch engine.

---

## 🛠️ Technology Stack & Environment

- **Core Backend:** PHP 8.2+ (Custom High-Performance MVC Framework)
- **AI / Machine Learning Service:** Python 3.10+, Flask, Pandas, NumPy, Scikit-learn (TF-IDF & Cosine Similarity)
- **Database Engine:** MySQL 8+ / MariaDB (InnoDB, 20 Tables, Spatial Indexing & Geospatial Haversine Queries)
- **Frontend & UI:** HTML5, CSS3, Vanilla CSS Design System, Bootstrap 5.3, JavaScript (ES6+), FontAwesome 6
- **Maps, Geolocation & Routing:** Google Maps Platform (Maps JavaScript API with Advanced Markers, Google Routes API v2, Geocoding API, Distance Matrix API, server-side disk caching proxy via `core/GoogleMaps.php`, and `kravyo-maps.js`)
- **Real-Time Live Tracking:** Pusher WebSockets (`pusher.js`) & HTML5 Geolocation API (`navigator.geolocation.watchPosition`)
- **Payment Processing & Automated Refunds:** Razorpay Payment Gateway (JS Checkout SDK + Server-Side REST API for instant automated refunds)
- **Audio & Real-time Alerts:** HTML5 Web Audio API & HTML5 Audio Notifications (`beep.wav`)
- **Server Environment:** Apache (XAMPP Server environment with URL rewriting enabled)

---

## 🚪 Multi-Portal Architecture & Isolated Roles

Kravyo features **4 strictly isolated application portals**, each secured with its own independent session cookie to allow simultaneous testing and operation across multiple roles without session collisions:

| Portal | URL Path | Session Cookie | Target User / Business Purpose |
|---|---|---|---|
| **Customer Portal** | `/` | `KRAVYO_CUSTOMER` | Food discovery, meal customization, cart, checkout, live order tracking, tiffin subscriptions, zero-waste food rescue, reviews |
| **Chef Portal** | `/chef` | `KRAVYO_CHEF` | Kitchen registration, FSSAI verification, menu management, live incoming orders with audio alerts, tiffin plans, sales analytics |
| **Delivery Portal** | `/delivery` | `KRAVYO_DELIVERY` | Driver onboarding, document verification, live duty toggle (Online/Offline), smart cascade offer acceptance, turn-by-turn routing, OTP delivery confirmation |
| **Admin Portal** | `/admin` | `KRAVYO_ADMIN` | Platform oversight, chef FSSAI approval, delivery partner verification, order tracking, disputes, category management, financial metrics |

---

## ⚙️ Core Platform Engines & Capabilities

### 1. ⚡ 4-Stage Smart Cascade Hybrid Dispatch Engine
Located in `app/services/DispatchService.php`, this algorithm assigns orders to delivery partners with minimal transit delay and zero broadcast chaos:
1. **Stage 1 — Corridor Batching:** Checks if an active driver already has an accepted order along the same transit corridor and batches the delivery if capacity permits.
2. **Stage 2 — Targeted Ping (25s Timer):** Identifies the single best-matched, idle, online partner within a 5 km radius based on proximity to the kitchen and pings only them with an exclusive 25-second acceptance window.
3. **Stage 3 — Instant Cascade:** If the first partner rejects or the 25-second timer expires, the offer immediately cascades to the 2nd nearest driver without platform-wide broadcast noise.
4. **Stage 4 — Radius Broadcast Fallback:** If sequential targeted pings are exhausted, the offer broadcasts to all remaining available partners within an expanded radius.

### 2. 💳 Seamless Razorpay Payments & Automated Refunds
Integrated via `app/services/RazorpayService.php`:
- **Online Checkout:** Customers pay securely via UPI, Cards, NetBanking, or Wallets using the Razorpay JavaScript Modal.
- **Automated Instant Refunds:** If a home chef rejects an online-paid order, or if a customer cancels before preparation begins, the backend immediately calls Razorpay's Refund API to return funds to the original payment source.

### 3. 🗺️ Google Maps Platform & Real-Time Driver Tracking
- **Google Maps JavaScript API & Advanced Markers:** Interactive maps across all 4 portals (Customer, Chef, Delivery, Admin) featuring custom SVGs and animated pins (`views/partials/google_maps_loader.php` and `kravyo-maps.js`).
- **Google Routes API v2 & Geocoding:** Live turn-by-turn routing, distance calculations, and address geocoding proxied through `MapsController.php` and `core/GoogleMaps.php`. The Google Server API Key is kept strictly confidential on the backend.
- **Cost-Optimized Disk Caching:** Geocoding results are cached for 30 days and routes for 2 minutes in `storage/cache/maps/` to keep usage within the Google Maps Platform monthly free tier ($200 credit).
- **Swiggy-Style Live Delivery Tracking:** When an order is "Out for delivery", the delivery driver's device streams live GPS coordinates (`watchPosition`) via Pusher WebSockets. The customer map renders an animated bike marker with heading/bearing rotation, halo route line, and dynamic ETA badge.

### 4. 📅 Weekly & Monthly Tiffin Subscriptions
Customers can subscribe to recurring breakfast, lunch, or dinner meal plans created by verified home chefs. Kitchens receive daily meal preparation forecasts, and scheduled deliveries are tracked via `customer_subscriptions` and `subscription_deliveries`.

### 5. ♻️ Zero Food Waste Rescue Module
Chefs can list freshly cooked, surplus meals at discounted rates towards the end of their operating hours (`zero_waste_items`). Listed meals auto-expire once the countdown timer finishes, preventing food waste while offering affordable meals to customers.

### 6. 🧠 Standalone Python AI / ML Recommendation Microservice
Located in `python_ai/`, this microservice provides personalized meal recommendations:
- **Algorithm:** Scikit-learn TF-IDF vectorization and Cosine Similarity over dish names, descriptions, and dietary flags, combined with platform-wide order popularity scores.
- **Fail-Safe Fallback:** If the Python Flask service is offline, `Recommendation.php` automatically falls back to an internal PHP scoring engine, ensuring 100% platform uptime.

### 7. 🛡️ Address Soft-Deletion & Data Integrity
Customer addresses utilize soft-deletion (`is_deleted = 1`). Customers can delete old or outdated addresses from their profile without causing foreign key constraint errors or corrupting historical order receipts.

---

## 📁 Complete Project Architecture & Directory Layout

```
Kravyo/
├── .htaccess                       # Apache root URL rewrite rules
├── CHANGELOG.md                    # Chronological version & change history
├── ROADMAP.md                      # 14-Phase development milestone tracker
├── kravyo_data_dictionary.md       # Comprehensive 20-table database schema
├── README.md                       # Main platform technical documentation
│
├── config/                         # System Configurations
│   ├── app.php                     # App URL, base paths & environment settings
│   ├── database.php                # Database PDO connection credentials
│   ├── constants.php               # System status codes, roles & badges
│   ├── google_maps.example.php     # Google Maps Platform API keys template
│   └── routes.php                  # Centralized URI-to-Controller routing map
│
├── core/                           # Lightweight MVC Core Engine
│   ├── Database.php                # Singleton PDO database connector
│   ├── GoogleMaps.php              # Server-side Google Maps client (Routes v2, Geocoding & disk caching)
│   ├── Router.php                  # Regex front-controller URI dispatcher
│   ├── Controller.php              # Base controller with view & layout helpers
│   ├── Model.php                   # Base model with prepared CRUD utilities
│   ├── View.php                    # Layout & sub-view rendering engine
│   ├── Session.php                 # Role-isolated multi-tenant session manager
│   ├── Helpers.php                 # Global sanitization & utility functions
│   └── Middleware.php              # Authentication & role guard middleware
│
├── app/                            # Application Business Logic
│   ├── controllers/                # Request Controllers (12 Controllers)
│   │   ├── AdminAuthController.php # Admin authentication & session security
│   │   ├── AdminController.php     # Admin dashboard, verification & metrics
│   │   ├── AuthController.php      # Customer & Chef registration/login
│   │   ├── CartController.php      # Shopping cart & meal customization
│   │   ├── ChefController.php      # Chef kitchen dashboard, menu CRUD & orders
│   │   ├── CustomerController.php  # Customer profile, addresses & favorites
│   │   ├── DeliveryController.php  # Delivery portal, duty status & GPS tracking
│   │   ├── HomeController.php      # Homepage discovery & landing views
│   │   ├── MapsController.php      # Google Routes & Geocoding proxy controller (safe server key)
│   │   ├── NotificationController.php # Customer & chef notification updates
│   │   ├── OrderController.php     # Checkout, Razorpay webhook & order tracking
│   │   └── SubscriptionController.php # Tiffin subscriptions & daily meal schedules
│   │
│   ├── services/                   # Advanced Domain Services
│   │   ├── DispatchService.php     # 4-stage smart cascade delivery dispatch engine
│   │   └── RazorpayService.php     # Razorpay API client & automated refund handler
│   │
│   └── models/                     # Eloquent-style PDO Data Models (20+ Models)
│       ├── Address.php             # Customer address model with soft-delete
│       ├── Admin.php               # Administrator credentials & permissions
│       ├── Category.php            # Cuisine categories (Gujarati, Punjabi, etc.)
│       ├── Chef.php                # Chef user accounts & bank details
│       ├── Customer.php            # Customer user accounts & preferences
│       ├── CustomerReport.php      # Customer support complaints & reports
│       ├── CustomerSubscription.php# Active recurring customer subscriptions
│       ├── DeliveryOffer.php       # Live dispatch offers with expiration timers
│       ├── DeliveryPartner.php     # Delivery driver records & live GPS coordinates
│       ├── DeliveryUser.php        # Delivery partner authentication logins
│       ├── Kitchen.php             # Cloud kitchen profiles, FSSAI & coordinates
│       ├── MenuItem.php            # Food menu dishes, pricing & dietary flags
│       ├── Notification.php        # Real-time user alert records
│       ├── Order.php               # Master orders, delivery OTP & payment states
│       ├── OrderItem.php           # Line items with spice/oil customization
│       ├── Recommendation.php      # AI engine connector with PHP fallback
│       ├── Review.php              # Verified customer dish & kitchen reviews
│       ├── Subscription.php        # Tiffin subscription tier plans
│       ├── SubscriptionDelivery.php# Daily recurring subscription deliveries
│       └── ZeroWasteItem.php       # Discounted surplus meals & countdowns
│
├── views/                          # Dynamic PHP Views & Layouts
│   ├── layouts/
│   │   └── main.php                # Master HTML5 template
│   ├── partials/
│   │   ├── header.php              # Dynamic navigation bar with notifications
│   │   ├── footer.php              # Footer with responsive design
│   │   ├── alerts.php              # Flash message notification banners
│   │   └── google_maps_loader.php  # Google Maps JS API async loader & configuration
│   ├── home/                       # Landing page & category browsing
│   ├── customer/                   # Order tracking, profile, cart & checkout views
│   ├── chef/                       # Kitchen dashboard, menu builder & live orders
│   ├── delivery/                   # Partner dashboard, map routing & order details
│   ├── admin/                      # Administration panels & verification tables
│   └── errors/                     # Custom 404 and 500 error pages
│
├── python_ai/                      # Machine Learning Recommendation Microservice
│   ├── app.py                      # Flask REST API server (`/recommend`, `/health`)
│   ├── recommender.py              # Scikit-learn TF-IDF & Cosine Similarity engine
│   ├── requirements.txt            # Python dependencies
│   └── README.md                   # AI microservice architecture & run guide
│
├── database/                       # Database Schemas & Migrations
│   ├── schema.sql                  # Complete 20-table database DDL
│   └── seed.sql                    # Production demo seed data
│
├── public/                         # Public Web Root
│   ├── index.php                   # Front controller entry point
│   ├── .htaccess                   # Rewrite engine routing requests to index.php
│   ├── assets/                     # Frontend static assets (CSS, JS, Icons)
│   └── uploads/                    # Uploaded kitchen banners, food dishes & FSSAI docs
│
└── storage/                        # Application Storage
    └── logs/                       # Application runtime error logs
```

---

## ⚡ Quickstart: Local Setup & Installation

### Prerequisites
1. **XAMPP Server** (Apache + MySQL/MariaDB with PHP 8.2+)
2. **Python 3.10+** (Optional, for the AI Recommendation Microservice)
3. **Web Browser** (Chrome / Edge / Firefox)

### Step 1: Copy Project to XAMPP
Place the project repository in your XAMPP web root:
```bash
C:\xampp\htdocs\Kravyo
```

### Step 2: Start Apache & MySQL
Launch **XAMPP Control Panel** and start both **Apache** and **MySQL**.

### Step 3: Import Database Schema
1. Open phpMyAdmin at `http://localhost/phpmyadmin/`.
2. Create a database named `kravyo_db`.
3. Select `kravyo_db`, click the **Import** tab, and import:
   - `database/schema.sql` (Initializes all 20 tables, indexes, and constraints).
   - *(Optional)* `database/seed.sql` to load demo home kitchens, dishes, and delivery partners.

### Step 4: Configure Environment (Optional)
Database credentials are pre-configured for standard XAMPP in `config/database.php`:
- **Host:** `localhost`
- **Database:** `kravyo_db`
- **Username:** `root`
- **Password:** *(empty)*

### Step 5: (Optional) Launch Python AI Service
In a separate terminal window:
```powershell
cd c:\xampp\htdocs\Kravyo\python_ai
python -m venv .venv
.\.venv\Scripts\activate
pip install -r requirements.txt
python app.py
```
*The AI engine will listen on `http://127.0.0.1:5000/`. If you skip this step, Kravyo automatically uses its internal PHP heuristic recommendation engine.*

### Step 6: Launch Kravyo
Open your browser and navigate to:
```
http://localhost/Kravyo/
```

---

## 🔑 Default Portal Access URLs

| Portal | URL | Demo Login / Role |
|---|---|---|
| **Customer App** | `http://localhost/Kravyo/` | Customer Sign-up or Login |
| **Chef Portal** | `http://localhost/Kravyo/chef` | Chef Login / Registration |
| **Delivery Portal** | `http://localhost/Kravyo/delivery` | Delivery Partner Login / Onboarding |
| **Admin Panel** | `http://localhost/Kravyo/admin` | System Administrator Dashboard |

---

## 📄 Documentation Index

- [README.md](file:///c:/xampp/htdocs/Kravyo/README.md) — Main platform setup, architecture & technical guide
- [kravyo_data_dictionary.md](file:///c:/xampp/htdocs/Kravyo/kravyo_data_dictionary.md) — Complete 20-table database schema with attribute descriptions & constraints
- [ROADMAP.md](file:///c:/xampp/htdocs/Kravyo/ROADMAP.md) — 14-Phase completed milestone tracker and feature execution log
- [CHANGELOG.md](file:///c:/xampp/htdocs/Kravyo/CHANGELOG.md) — Chronological release history and technical change records
- [python_ai/README.md](file:///c:/xampp/htdocs/Kravyo/python_ai/README.md) — Python Flask ML recommendation microservice architecture & API reference
