# Kravyo — Strict Documentation Standards & Ground Truth Rule

## Purpose
This rule strictly forbids generating or updating documentation based on outdated markdown text, historical changelogs, assumptions, or stale prototype comments. It enforces a **Code-First Ground Truth Hierarchy** across all project documentation, viva reports, and technical manuals.

---

## 1. The Ground Truth Hierarchy (Mandatory Order)

Whenever you are asked to write, update, or explain Kravyo documentation:
1. **Level 1 (Ultimate Source of Truth): Working Source Code & Config**
   - Active PHP backend (`app/controllers/`, `app/services/`, `app/models/`, `core/`, `config/`)
   - Active Frontend logic (`public/assets/js/`, `views/partials/`, `views/`)
   - Machine Learning service (`python_ai/app.py`, `python_ai/recommender.py`)
2. **Level 2: Live Database (`kravyo_db`)**
   - Run a query or inspect `information_schema` / `SHOW FULL TABLES` to get exact table counts and schemas.
3. **Level 3: Automated Verification Script**
   - Run `php scripts/system_audit.php` to instantly verify active controllers, services, models, tables, and 3rd-party services.
4. **STRICTLY PROHIBITED:**
   - **NEVER** use old text in `ROADMAP.md`, `README.md`, or historical entries in `CHANGELOG.md` as evidence of how a feature currently works. Features evolve, and old docs often describe early prototypes.

---

## 2. Permanent Architectural Facts for Kravyo

Any agent working on Kravyo must remember these verified facts:
- **Maps & Geolocation:** **Google Maps Platform** ONLY.
  - Maps JavaScript API with Advanced Markers (`google_maps_loader.php` + `kravyo-maps.js`)
  - Google Routes API v2 & Geocoding API (`core/GoogleMaps.php` + `MapsController.php`)
  - Server-side disk caching in `storage/cache/maps/`
  - Real-Time sync via **Pusher WebSockets** (`pusher.js`)
  - *Leaflet was an early prototype and is completely superseded.*
- **Payments & Refunds:** **Razorpay** (`app/services/RazorpayService.php`) for both online checkout and automated refunds via REST API.
- **Dispatch Engine:** **4-Stage Smart Cascade Hybrid Dispatch** (`app/services/DispatchService.php`).
- **Machine Learning:** Standalone **Python Flask Microservice** on port 5000 (`python_ai/`) using Scikit-Learn TF-IDF + Cosine Similarity + Popularity Blending with zero-downtime PHP heuristic fallback (`app/models/Recommendation.php`).
- **Database:** MariaDB / MySQL 8+ (`kravyo_db`) with **20 tables**.
- **Role Isolation:** 4 distinct session cookies (`KRAVYO_CUSTOMER`, `KRAVYO_CHEF`, `KRAVYO_DELIVERY`, `KRAVYO_ADMIN`).

---

## 3. Pre-Documentation Checklist

Before saving any changes to `README.md`, `ROADMAP.md`, `kravyo_data_dictionary.md`, or any project report:
- [ ] Run `php scripts/system_audit.php` or inspect the relevant active PHP/JS files.
- [ ] Ensure no deprecated library names (e.g. Leaflet) are attributed to active features.
- [ ] Ensure database table counts and column names match live DB schema.
- [ ] Automatically log all code/documentation updates to `CHANGELOG.md` following the 12-hour format `## 📅 DD-MM-YY | hh:mm AM/PM IST`.
