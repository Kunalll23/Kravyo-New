# Kravyo — Development Roadmap & Architectural Milestone Tracker

This roadmap outlines the complete feature implementation strategy structured across **14 distinct phases**, strictly tracking all completed milestones in the Kravyo platform architecture.

---

## 📌 Phase Overview

```
Phase 1: Project Foundation & Architecture (COMPLETED ✅)
Phase 2: Authentication & User Management (COMPLETED ✅)
Phase 3: Home Chef Registration & Kitchen Verification (COMPLETED ✅)
Phase 4: Menu Management & Food Category Catalog (COMPLETED ✅)
Phase 5: Customer Discovery, Meal Customization & Cart System (COMPLETED ✅)
Phase 6: Order Placement & Live Status Tracking (COMPLETED ✅)
Phase 7: Weekly & Monthly Tiffin Subscription System (COMPLETED ✅)
Phase 8: Zero Food Waste Module (COMPLETED ✅)
Phase 9: Seller & Administrator Analytics Dashboards (COMPLETED ✅)
Phase 10: AI Recommendations & Security Hardening (COMPLETED ✅)
Phase 11: Nearby Cloud Kitchens Interactive Map System (COMPLETED ✅)
Phase 12: Delivery Partner Portal & Live GPS Routing (COMPLETED ✅)
Phase 13: Smart Cascade Dispatch Engine & Razorpay Refund Automation (COMPLETED ✅)
Phase 14: Python ML Recommendation Microservice & Data Protection (COMPLETED ✅)
```

---

## 📋 Detailed Phase Breakdown

### Phase 1: Scaffolding & Core Architecture (Completed ✅)
- [x] XAMPP-compatible directory structure setup
- [x] Core classes creation (`Router`, `Controller`, `Model`)
- [x] Template engine (header, footer, view rendering)
- [x] Session & CSRF Security Middleware
- [x] MySQL Database connection wrapper (`Database.php`)
- [x] Base layout and responsive CSS design system

---

### Phase 2: User Authentication & Role Management (Completed ✅)
- [x] **Registration:** Role selection (Customer vs Home Chef)
- [x] **Login:** Secure password hashing (Bcrypt) and session initialization
- [x] **Profile Management:** View and update personal profile details
- [x] **Security:** Route protection middleware (`Middleware::role()`) and CSRF guards

---

### Phase 3: Kitchen Verification & Chef Onboarding (Completed ✅)
- [x] **Kitchen Profile Creation:** Upload FSSAI License, set kitchen name, address, and banner
- [x] **Admin Verification:** Admin panel workflow to approve/reject pending kitchen applications
- [x] **Kitchen Status Toggle:** Chef can dynamically toggle Open/Closed status

---

### Phase 4: Menu & Category Management (Completed ✅)
- [x] **Admin Categories:** Global food categories management (Gujarati, Punjabi, South Indian, Baking, etc.)
- [x] **Chef Menu Builder:** Full CRUD operations for dishes (Price, Image, Description, Prep Time)
- [x] **Dietary Badges:** Veg, Non-Veg, Jain-friendly, and Diabetic-friendly tags

---

### Phase 5: Customer Food Discovery & Shopping Cart (Completed ✅)
- [x] **Search & Filter:** Search food by category, location/pincode, and dietary filters
- [x] **Chef Profile View:** View home chef's personal story, kitchen ratings, and customer reviews
- [x] **Interactive Cart:** Session-based cart with real-time total calculation and delivery fees
- [x] **Meal Customization Selector:** Less Oil / Normal Oil, Spice Level (Low/Med/High), Jain preparation toggle

---

### Phase 6: Order Management & Tracking (Completed ✅)
- [x] **Checkout Flow:** Address selection & payment method selection (COD & Online)
- [x] **Chef Order Management:** Real-time incoming order dashboard (Accept/Reject, Preparation status updates)
- [x] **Customer Order Tracking:** Live order timeline (Pending → Accepted → Preparing → Out for Delivery → Delivered)
- [x] **Order Cancellation:** Customer cancellation workflow before kitchen preparation begins

---

### Phase 7: Tiffin Subscription Module (Completed ✅)
- [x] **Subscription Plan Creation:** Chefs create Weekly and Monthly tiffin packages with custom meal allocations
- [x] **Customer Subscription Checkout:** Subscribe to recurring daily breakfast, lunch, or dinner deliveries
- [x] **Subscription Management:** View active subscriptions, pause/resume schedules, and track daily deliveries

---

### Phase 8: Zero Food Waste Module (Completed ✅)
- [x] **End-of-Day Listing:** Chefs list unsold cooked meals at discounted prices before closing
- [x] **Zero Waste Customer Showcase:** Dedicated section on homepage for discounted surplus meals
- [x] **Auto-Expiration:** Automated countdown timer and status change when meal expiry time passes

---

### Phase 9: Dashboards & Analytics (Completed ✅)
- [x] **Chef Dashboard:** Daily/Monthly earnings report, top-selling dishes, customer statistics
- [x] **Admin Dashboard:** Platform revenue reports, total active users/chefs, order statistics
- [x] **Reviews & Ratings:** Customer rating and review system for completed orders

---

### Phase 10: AI Recommendations & Security Hardening (Completed ✅)
- [x] **Meal Recommendation Module:** Weighted-scoring PHP engine — category affinity, dietary match, platform popularity, recency penalty; personalized for logged-in customers, popular picks for guests
- [x] **Security Hardening:** SQL Injection & XSS audit — `sanitize()` strengthened with `ENT_SUBSTITUTE`; new `sanitizeInput()` batch helper; all DB queries confirmed PDO prepared statements
- [x] **Documentation & Presentation:** Architectural documentation and database migration validation

---

### Phase 11: Nearby Cloud Kitchens Interactive Map System & Google Maps Engine (Completed ✅)
- [x] **Geospatial Schema & Indexing:** Added `latitude` and `longitude` fields to `kitchens` table with `idx_kitchens_geo` composite index
- [x] **Haversine Distance Engine:** Backend SQL bounding-box optimization + Haversine distance formula in `Kitchen::findNearby()`
- [x] **Google Maps Platform Integration:** Integrated Google Maps JavaScript API with AdvancedMarkerElement (`views/partials/google_maps_loader.php` and `kravyo-maps.js`)
- [x] **Server-Side Google Proxy & Disk Cache:** Implemented `core/GoogleMaps.php` and `MapsController.php` wrapping Geocoding API and Routes API v2 with disk caching in `storage/cache/maps/` (30-day geocode / 2-min route TTL)
- [x] **Dynamic Radius & City Filtering:** Real-time radius selector (2km, 5km, 10km, 20km, 50km), city quick-jump, and open-now toggles
- [x] **Side-by-Side Responsive UI:** Synchronized scrollable kitchen card list and interactive map on desktop; stacked layout on mobile
- [x] **Chef Profile Location Picker:** Interactive draggable pin map picker and "Detect My Location" GPS button in Chef Profile
- [x] **Location Privacy & Graceful Fallback:** Permission-denied fallback banner with manual city selection; zero continuous background tracking

---

### Phase 12: Delivery Partner Portal & Google Routes Real-Time Tracking (Completed ✅)
- [x] **Isolated Delivery Session:** Created dedicated `KRAVYO_DELIVERY` session cookie and auth guard, ensuring zero state contamination across customer/chef/admin portals
- [x] **Driver Onboarding & Verification:** Complete registration portal with vehicle details and driving license document submission for admin review
- [x] **Dynamic Duty Status:** Real-time Online / Offline duty status toggle controlling dispatch offer availability
- [x] **Google Routes API v2 Route Highlighting:** High-precision turn-by-turn route path rendering using Google Routes API v2 with polyline decoding and Swiggy-style visual halo
- [x] **Live GPS Delivery Tracking & Pusher WebSockets:** Integrated HTML5 Geolocation API (`watchPosition`) paired with Pusher WebSockets (`pusher.js`) to stream live coordinates to `/delivery/location/update` and customer tracking map
- [x] **Animated Driver Marker:** Smooth bike marker glide and heading/bearing rotation as driver travels towards customer
- [x] **Secure OTP Verification:** One-Time Password verification workflow required by the delivery partner to mark the order as "Delivered"

---

### Phase 13: Smart Cascade Dispatch Engine & Razorpay Refund Automation (Completed ✅)
- [x] **4-Stage Hybrid Dispatch Engine (`DispatchService.php`):**
  - *Stage 1:* Corridor Batching for drivers already on active delivery along the same route
  - *Stage 2:* Targeted Ping to the nearest idle driver with an exclusive 25-second countdown timer
  - *Stage 3:* Zero-delay Instant Cascade to the next nearest partner upon rejection or timeout
  - *Stage 4:* Fallback Radius Broadcast to all available nearby drivers if targeted cascade is exhausted
- [x] **Dispatch Audio & Visual Alerts:** Incoming delivery popups with dynamic countdown timers and audio chime (`beep.wav`)
- [x] **Razorpay Online Payments:** Razorpay JavaScript Checkout modal integration for UPI, Cards, NetBanking, and Wallets
- [x] **Automated Instant Refunds (`RazorpayService.php`):** Instant API refund triggers when a home chef rejects an order or a customer cancels before food preparation begins

---

### Phase 14: Python ML Recommendation Microservice & Data Protection (Completed ✅)
- [x] **Standalone Python Microservice (`python_ai/`):** Flask REST API running Scikit-learn TF-IDF vectorizer and Cosine Similarity over dish features (ingredients, cuisine, dietary tags)
- [x] **Hybrid Popularity Blending:** Final recommendation score calculated as `0.80 × similarity_score + 0.20 × platform_popularity_score`
- [x] **Seamless Fail-Safe Fallback:** PHP cURL client with zero-crash fallback to internal PHP scoring engine when microservice is offline
- [x] **Address Soft-Deletion Architecture:** Added `is_deleted` flag to `addresses` table, preventing foreign key constraint errors on past orders while removing addresses from active customer profile views
- [x] **Notification Experience Refinement:** Event-delegated notification dropdown clicks and interactive inbox card navigation
