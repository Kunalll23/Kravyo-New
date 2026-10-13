# Kravyo — Project Change Tracking

This document maintains structured tracking of new functional features implemented beyond the initial roadmap phases.

---

## Date: 2026-10-01
## Time: 14:35 IST
## Feature: Nearby Cloud Kitchens Map

### Summary
Implemented a production-style "Nearby Cloud Kitchens" interactive map feature for the Kravyo platform. The feature connects directly to the Kravyo MariaDB database and displays actual approved kitchens within a configurable radius (2 km, 5 km, 10 km default, 20 km, 50 km) from the customer's current GPS location or a manually selected location. The map uses Leaflet.js with OpenStreetMap tiles (no paid API keys required). It features a responsive side-by-side layout (scrollable cards on the left, interactive map on the right), custom markers for customers ("You are here" pulsing blue dot) and kitchens (branded Kravyo pins with open/closed indicators), interactive marker popups with live ratings and direct links to existing kitchen profiles, a radius selector, live keyword/city/open-only filtering, a graceful permission-denied fallback with city quick-jumping, and a chef profile location picker allowing home chefs to set their kitchen's exact coordinates.

---

### Files Changed
1. `app/models/Kitchen.php` — Added `findNearby()` (bounding box + Haversine formula), `findAllWithCoordinates()`, and `updateCoordinates()`.
2. `app/controllers/CustomerController.php` — Added `nearbyKitchens()` (page view renderer) and `apiNearbyKitchens()` (JSON API endpoint at `/api/kitchens/nearby`).
3. `app/controllers/ChefController.php` — Added coordinate validation (-90 to 90, -180 to 180) and persistence in `updateProfile()`.
4. `core/Helpers.php` — Added `/nearby-kitchens` session-safe prefix handling to `ctx_url()`.
5. `config/routes.php` — Added public routes (`/nearby-kitchens`, `/kitchens/nearby`, `/api/kitchens/nearby`) and context-aware routes (`/chef/nearby-kitchens`, `/admin/nearby-kitchens`).
6. `views/customer/nearby_kitchens.php` (New) — Complete customer-facing interactive map page with Leaflet.js, OpenStreetMap tiles, live distance badges, search & sort.
7. `views/chef/profile.php` — Added interactive Leaflet map picker with draggable marker and "Detect My Location" button.
8. `views/partials/header.php` — Added "Nearby Map" navigation link across customer, chef, and admin sessions.
9. `views/customer/kitchens.php` — Added "Nearby Kitchens Map" button in hero section.
10. `views/home/index.php` — Added "Nearby Map" action button in hero banner.
11. `views/admin/chefs.php` — Added kitchen map coordinates status badge for admin review.
12. `database/nearby_kitchens_migration.sql` (New) — MariaDB migration adding `latitude`, `longitude`, and composite spatial index `idx_kitchens_geo`.
13. `database/seed_kitchen_coordinates.php` (New) — Seed script populating realistic coordinates for 42 existing database kitchens.
14. `CHANGELOG.md` — Logged full entry dated 2026-10-01 | 14:35 IST.
15. `ROADMAP.md` — Updated with Phase 11 completion.

---

### Database Changes
- Modified table: `kitchens`
- Columns added:
  - `latitude` DECIMAL(10, 8) NULL DEFAULT NULL AFTER `pincode`
  - `longitude` DECIMAL(11, 8) NULL DEFAULT NULL AFTER `latitude`
- Indexes added:
  - `idx_kitchens_geo` (`approval_status`, `latitude`, `longitude`) for optimized spatial bounding-box pruning
- Migration file: `database/nearby_kitchens_migration.sql`
- Data population: Executed `database/seed_kitchen_coordinates.php` to populate realistic coordinates for all 42 approved kitchens across Surat, Pune, Mumbai, Ahmedabad, Bengaluru, Delhi, etc.

---

### Frontend Changes
- **Interactive Map:** Powered by Leaflet.js 1.9.4 and OpenStreetMap tiles (standard EPSG:3857, free & open source).
- **Custom Markers:**
  - Customer marker: Animated pulsing blue circle (`customer-marker-pulse`) with radar ripple effect.
  - Open Kitchen marker: Vibrant Kravyo orange pin (`#ff6b35`) with shop icon.
  - Closed Kitchen marker: Slate gray pin (`#64748b`) with shop icon.
- **Marker Popups:** Show kitchen name, chef name, star rating, review count, open/closed status badge, distance from customer (e.g., "1.8 km away"), street address, and "View Kitchen" button linking to existing profile.
- **Layout:** Responsive Bootstrap 5 grid:
  - Desktop: 5-column left panel (scrollable kitchen cards list) + 7-column right panel (interactive Leaflet map).
  - Mobile: Map displays at top (`380px`), kitchen cards stack underneath.
- **Radius Control:** Buttons for `2 km`, `5 km`, `10 km` (default active), `20 km`, `50 km`.
- **Search & Filters:** Search by kitchen name / area, city dropdown, open-now toggle, and sort dropdown (Nearest first, Highest rated, Open now).
- **Location Permission Handling:** Non-intrusive alert when geolocation is denied, complete with one-click quick-jump city buttons (Pune, Surat, Mumbai, Ahmedabad, Bengaluru, Delhi) and map-click manual pin placement.
- **Chef Location Management:** Integrated map picker in `views/chef/profile.php` with draggable marker and "Detect My Location" button.

---

### Backend Changes
- **Model Methods (`app/models/Kitchen.php`):**
  - `findNearby(float $lat, float $lng, float $radiusKm, array $filters, int $limit)`: Performs bounding-box pre-filtering (`latitude BETWEEN latMin AND latMax AND longitude BETWEEN lngMin AND lngMax`) utilizing the `idx_kitchens_geo` index, followed by the Haversine great-circle formula in SQL to compute exact distance in kilometers, filtering with `HAVING distance <= radiusKm`.
  - `findAllWithCoordinates(array $filters, int $limit)`: Fetches approved kitchens with coordinates for initial map load or city browsing.
  - `updateCoordinates(int $id, ?float $lat, ?float $lng)`: Updates kitchen coordinates.
- **Controllers:**
  - `CustomerController@nearbyKitchens`: Renders the map page with pre-fetched kitchen data so the page is never blank even before JavaScript runs.
  - `CustomerController@apiNearbyKitchens`: JSON API endpoint at `GET /api/kitchens/nearby` validating inputs and returning clean, sanitized, customer-safe kitchen JSON.
  - `ChefController@updateProfile`: Validates numeric coordinates bounded between -90/90 and -180/180.
- **Routes:** Registered `GET /nearby-kitchens`, `GET /kitchens/nearby`, `GET /api/kitchens/nearby`, `GET /chef/nearby-kitchens`, `GET /admin/nearby-kitchens`.

---

### Security
- **Server-Side Validation:** Latitude strictly bounded between -90 and 90; Longitude strictly bounded between -180 and 180; Radius clamped between 1 and 200 km.
- **SQL Injection Prevention:** All SQL queries use PDO prepared statements or cast float values in distance math.
- **XSS Prevention:** All user and kitchen output sanitized via `htmlspecialchars()` in PHP views and JSON responses.
- **Access Control:** Only approved kitchens (`approval_status = 'approved'`) are visible to customers. Unapproved, rejected, or suspended kitchens are excluded.
- **Data Privacy:** Only customer-safe public kitchen fields are exposed in the JSON API (no password hashes, email addresses, phone numbers, or admin notes).
- **No Background Tracking:** Geolocation is only requested on-demand when the customer clicks "Use My Location" or opens the map; no continuous GPS tracking or background tracking is implemented.
- **CSRF Protection:** Chef coordinate updates in profile require valid CSRF tokens.

---

### Testing
- **Automated Test Suite (`scratch/test_suite.php`):** 20/20 test assertions passed:
  1. *Customer allows location (Pune center):* Found 4 kitchens including Mehul's Kitchen.
  2. *Location denial / fallback browse:* Successfully loaded 20 approved kitchens.
  3. *Radius expansion:* Verified progressive result counts (2km: 1 <= 10km: 4 <= 50km: 4).
  4. *No kitchens nearby:* Remote coordinates (85.0, 0.0) correctly returned 0 kitchens with empty state suggestions.
  5. *Unapproved kitchen exclusion:* Verified only approved kitchens are returned.
  6. *Inactive/rejected kitchen exclusion:* Verified rejected kitchens do not appear.
  7. *Kitchen page linking:* Verified kitchen ID links to valid existing kitchen route.
  8. *Distance accuracy:* Calculated 1.77 km between Mehul's Kitchen (Shivaji Nagar) and Pune Center (expected ~1.75–1.77 km).
  9. *JSON API endpoint:* Verified valid JSON structure, safe fields, and correct types.
  10. *Security validation:* Verified HTTP 400 rejection for invalid latitude (95 > 90); verified SQL injection in keyword parameter safely handled.
  11. *Regression testing:* Verified existing pages (`/`, `/kitchens`, `/menu`, `/login`, `/register`, `/zero-waste`, `/subscriptions`) render with 200 OK and no errors.
- **Syntax Verification (`php -l`):** All 10 modified/created PHP files passed with 0 errors.

---

### Known Limitations
1. Map tiles require internet connectivity to fetch from OpenStreetMap tile servers (`https://*.tile.openstreetmap.org`).
2. Browser geolocation requires HTTPS or localhost; when hosted on HTTP without SSL in production, browsers block the Geolocation API (standard browser security restriction). The manual city selector and map click fallback address this scenario.
3. Does not include real-time rider GPS tracking or live delivery vehicle tracking (explicitly excluded as per requirements).
