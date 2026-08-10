# Kravyo — Development Roadmap & Academic Execution Plan

This roadmap outlines the complete feature implementation strategy structured across 10 distinct phases, strictly aligned with the BCA 5th Semester project definition document.

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
Phase 8: Zero Food Waste Module (End-of-Day Discounts)
Phase 9: Seller & Administrator Analytics Dashboards
Phase 10: AI Recommendation Engine Integration & Final Polish
```

---

## 📋 Detailed Phase Breakdown

### Phase 1: Scaffolding & Core Architecture (Completed ✅)
- [x] XAMPP-compatible directory structure setup
- [x] Core classes creation (Router, Controller, Model)
- [x] Template engine (header, footer, view rendering)
- [x] Session & CSRF Security Middleware
- [x] MySQL Database connection wrapper (`Database.php`)
- [x] Base layout and responsive CSS setup

---

### Phase 2: User Authentication & Role Management (Completed ✅)
- [x] **Registration:** Role selection (Customer vs Home Chef)
- [x] **Login:** Secure password hashing (Bcrypt) and session initialization
- [x] **Profile Management:** View and update basic profile details
- [x] **Security:** Route protection middleware (`Middleware::role()`)

---

### Phase 3: Kitchen Verification & Chef Onboarding (Completed ✅)
- [x] **Kitchen Profile Creation:** Upload FSSAI License, set kitchen name and address
- [x] **Admin Verification:** Admin panel to approve/reject pending kitchen applications
- [x] **Kitchen Status Toggle:** Chef can toggle Open/Closed status

---

### Phase 4: Menu & Category Management (Completed ✅)
- [x] **Admin Categories:** Global food categories management (Gujarati, Punjabi, Baking, etc.)
- [x] **Chef Menu Builder:** CRUD operations for dishes (Price, Image, Description)
- [x] **Dietary Badges:** Veg, Non-Veg, Jain-friendly, Diabetic-friendly tags

---

### Phase 5: Customer Food Discovery & Shopping Cart (Completed ✅)
- [x] **Search & Filter:** Search food by category, location/pincode, and dietary filters
- [x] **Chef Profile View:** View home chef's personal story and customer reviews
- [x] **Interactive Cart:** Session-based cart with real-time total calculation
- [x] **Meal Customization Selector:** Less Oil / Normal Oil, Spice Level (Low/Med/High), Jain preparation toggle

---

### Phase 6: Order Management & Tracking (Completed ✅)
- [x] **Checkout Flow:** Address selection & payment method selection (COD/UPI)
- [x] **Chef Order Management:** Real-time incoming order dashboard (Accept/Reject, Preparation status)
- [x] **Customer Order Tracking:** Live order timeline (Pending → Accepted → Preparing → Out for Delivery → Delivered)
- [x] **Order Cancellation:** Allow customer cancellation before kitchen preparation begins

---

### Phase 7: Tiffin Subscription Module (Completed ✅)
- [x] **Subscription Plan Creation:** Chefs create Weekly and Monthly tiffin packages
- [x] **Customer Subscription Checkout:** Subscribe to daily meal deliveries
- [x] **Subscription Management:** View active subscriptions and delivery schedules

---

### Phase 8: Zero Food Waste Module
- [ ] **End-of-Day Listing:** Chefs list unsold cooked meals at discounted prices before closing
- [ ] **Zero Waste Customer Showcase:** Dedicated section on homepage for discounted meals
- [ ] **Auto-Expiration:** Automated status change when meal expiry time passes

---

### Phase 9: Dashboards & Analytics
- [ ] **Chef Dashboard:** Daily/Monthly earnings report, top-selling dishes, customer statistics
- [ ] **Admin Dashboard:** Platform revenue reports, total active users/chefs, order statistics
- [ ] **Reviews & Ratings:** Customer rating/review system for completed orders

---

### Phase 10: AI Recommendations & Final Testing
- [ ] **Meal Recommendation Module:** Suggest dishes based on user preference history & weather
- [ ] **Security Hardening:** SQL Injection & XSS audit
- [ ] **Documentation & Presentation:** Final academic project report preparation
