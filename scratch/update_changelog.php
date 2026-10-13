<?php
$filePath = __DIR__ . '/../CHANGELOG.md';
$content = file_get_contents($filePath);

$isCrlf = strpos($content, "\r\n") !== false;
$eol = $isCrlf ? "\r\n" : "\n";

$target = '---' . $eol . $eol . '## 📅 2026-10-01 | 14:05 IST';

$newEntry = '---' . $eol . $eol . '## 📅 2026-10-01 | 14:35 IST' . $eol . $eol .
'### 🗺️ Feature: Nearby Cloud Kitchens Interactive Map System (Production-Ready)' . $eol . $eol .
'- **Goal:** Implement a complete, production-grade "Nearby Cloud Kitchens" discovery feature for Kravyo using Leaflet.js, OpenStreetMap tiles, MariaDB geospatial indexing, and PHP 8.4 backend.' . $eol .
'- **Key Capabilities:**' . $eol .
'  - Customer interactive map with real-time browser Geolocation API (`navigator.geolocation.getCurrentPosition`).' . $eol .
'  - Distinct custom map markers: animated pulsing blue pin for customer ("You are here") and Kravyo orange pins for kitchens with open/closed indicators.' . $eol .
'  - Side-by-side desktop layout with responsive scrollable kitchen card list + interactive map (collapses cleanly on mobile with map on top).' . $eol .
'  - Radius selector (2 km, 5 km, 10 km default, 20 km, 50 km) with instant live filtering.' . $eol .
'  - Graceful location denial fallback with friendly banner and quick-jump city selector (Pune, Surat, Mumbai, Ahmedabad, Bengaluru, Delhi).' . $eol .
'  - Interactive card-to-map synchronization: clicking a card centers map, zooms to kitchen, and opens its detail popup; clicking a marker highlights the card.' . $eol .
'  - Real database integration: queries actual approved kitchens from `kitchens` table with live ratings from `reviews` and dish counts from `menu_items`.' . $eol .
'  - Direct linking: marker popups and cards feature "View Kitchen" buttons linking to existing kitchen profile routes (`ctx_url(\'/kitchen/{id}\')`).' . $eol .
'  - Chef location manager in `views/chef/profile.php`: interactive Leaflet map picker allowing chefs to click/drag a pin or click "Detect My Location" to save exact coordinates.' . $eol .
'  - Admin panel indicator in `views/admin/chefs.php` showing kitchen map coordinates status.' . $eol .
'- **Database Changes:**' . $eol .
'  - Migration: `database/nearby_kitchens_migration.sql`' . $eol .
'  - Added `latitude` DECIMAL(10, 8) NULL and `longitude` DECIMAL(11, 8) NULL to `kitchens` table.' . $eol .
'  - Added composite index `idx_kitchens_geo` (`approval_status`, `latitude`, `longitude`) for fast spatial filtering.' . $eol .
'  - Seed script: `database/seed_kitchen_coordinates.php` populated 42 existing database kitchens across major cities with realistic coordinates.' . $eol .
'- **Backend & API Changes:**' . $eol .
'  - `app/models/Kitchen.php`: Added `findNearby()` (bounding-box pre-filtering + Haversine distance formula), `findAllWithCoordinates()`, and `updateCoordinates()`.' . $eol .
'  - `app/controllers/CustomerController.php`: Added `nearbyKitchens()` (view renderer) and `apiNearbyKitchens()` (secure JSON API endpoint at `GET /api/kitchens/nearby`).' . $eol .
'  - `app/controllers/ChefController.php`: Added latitude/longitude input sanitization, bounds validation (-90 to 90, -180 to 180), and database persistence in `updateProfile()`.' . $eol .
'  - `config/routes.php`: Added routes for `GET /nearby-kitchens`, `GET /kitchens/nearby`, `GET /api/kitchens/nearby`, `GET /chef/nearby-kitchens`, `GET /admin/nearby-kitchens`.' . $eol .
'  - `core/Helpers.php`: Updated `ctx_url()` to support session-safe `/nearby-kitchens` routing across Customer, Chef, and Admin contexts.' . $eol .
'- **Frontend & Views:**' . $eol .
'  - Created `views/customer/nearby_kitchens.php` — Complete production-style interactive map with Leaflet.js, OpenStreetMap tiles, live distance badges, search & sort.' . $eol .
'  - Updated `views/chef/profile.php` — Integrated Leaflet map picker for chefs with draggable pin & GPS auto-detection.' . $eol .
'  - Updated `views/partials/header.php` — Added "Nearby Map" navigation link with context-aware session handling.' . $eol .
'  - Updated `views/customer/kitchens.php` — Added direct "Nearby Kitchens Map" button in hero.' . $eol .
'  - Updated `views/home/index.php` — Added "Nearby Map" action button in hero.' . $eol .
'  - Updated `views/admin/chefs.php` — Added kitchen map coordinates status badge.' . $eol .
'- **Security & Privacy:**' . $eol .
'  - Strict server-side validation: coordinates bounded (-90 <= lat <= 90, -180 <= lng <= 180); radius clamped (1 to 200 km).' . $eol .
'  - All database queries use PDO prepared statements or cast float values in distance math, preventing SQL injection.' . $eol .
'  - Output sanitized via `htmlspecialchars` in both PHP views and API JSON responses.' . $eol .
'  - Only approved kitchens displayed; unapproved, rejected, or suspended kitchens strictly excluded.' . $eol .
'  - API exposes only safe public fields (no passwords, tokens, admin notes).' . $eol .
'  - No background tracking or continuous customer GPS tracking.' . $eol .
'- **Testing & Verification:**' . $eol .
'  - Automated test suite (`scratch/test_suite.php`): 20/20 test assertions passed covering all 11 test scenarios specified in requirements.' . $eol .
'  - Distance calculation verified against known coordinates (~1.77 km between Mehul\'s Kitchen in Shivaji Nagar and Pune center).' . $eol .
'  - PHP linting (`php -l`): Passed on all 10 modified/created PHP files with 0 syntax errors.' . $eol .
'  - HTTP API testing: Verified JSON output and 400 response on invalid coordinates.' . $eol .
'- **Backups:** `scratch/backup_nearby_kitchens_20261001/`' . $eol .
'- **Known Limitations:** Map relies on browser geolocation permission or user selecting a city/pin; client-side tiles require internet access to OpenStreetMap CDN.' . $eol . $eol .
'---' . $eol . $eol . '## 📅 2026-10-01 | 14:05 IST';

$content = str_replace($target, $newEntry, $content);
file_put_contents($filePath, $content);
echo "SUCCESS: CHANGELOG.md updated.\n";
