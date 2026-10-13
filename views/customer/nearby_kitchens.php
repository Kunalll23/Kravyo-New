<!-- Phase 11: Nearby Cloud Kitchens Interactive Map Feature -->
<?php include __DIR__ . '/../partials/google_maps_loader.php'; ?>

<style>
/* ── Nearby Kitchens Map Styles ────────────────────────────────────────── */
.nearby-hero {
    background: linear-gradient(135deg, #1a1a24 0%, #2b2b3a 100%);
    color: white;
    padding: 2.2rem 0;
    border-radius: 0 0 24px 24px;
    margin-bottom: 1.5rem;
}
.nearby-control-bar {
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    padding: 1.25rem;
    margin-bottom: 1.5rem;
    border: 1px solid rgba(0,0,0,0.06);
}
#nearbyMap {
    height: 640px;
    width: 100%;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    z-index: 1;
}
.kitchen-list-scroll {
    max-height: 640px;
    overflow-y: auto;
    padding-right: 6px;
}
.kitchen-list-scroll::-webkit-scrollbar {
    width: 6px;
}
.kitchen-list-scroll::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 10px;
}
.kitchen-list-scroll::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}
.kitchen-list-scroll::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
.nearby-kitchen-card {
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    background: #ffffff;
    transition: all 0.2s ease;
    cursor: pointer;
    overflow: hidden;
    margin-bottom: 0.85rem;
}
.nearby-kitchen-card:hover,
.nearby-kitchen-card.active-card {
    border-color: #ff6b35;
    box-shadow: 0 8px 24px rgba(255, 107, 53, 0.18);
    transform: translateY(-2px);
}
.nearby-kitchen-card.active-card {
    background: #fffaf5;
}
.nearby-card-thumb {
    width: 90px;
    height: 90px;
    object-fit: cover;
    border-radius: 10px;
    flex-shrink: 0;
}
.nearby-card-thumb-placeholder {
    width: 90px;
    height: 90px;
    background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ff6b35;
    font-size: 2rem;
    flex-shrink: 0;
}
.distance-badge {
    background: #e0f2fe;
    color: #0369a1;
    font-weight: 700;
    font-size: 0.78rem;
    padding: 0.25rem 0.6rem;
    border-radius: 50px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.radius-btn {
    font-size: 0.85rem;
    font-weight: 600;
    padding: 0.35rem 0.85rem;
    border-radius: 50px;
    transition: all 0.2s;
}
.radius-btn.active {
    background-color: #ff6b35 !important;
    border-color: #ff6b35 !important;
    color: #ffffff !important;
    box-shadow: 0 2px 8px rgba(255,107,53,0.3);
}

/* ── Custom Leaflet Markers ────────────────────────────────────────── */
.customer-marker-pulse {
    position: relative;
    width: 24px;
    height: 24px;
}
.customer-marker-pulse .dot {
    position: absolute;
    width: 16px;
    height: 16px;
    top: 4px;
    left: 4px;
    background: #2563eb;
    border: 3px solid #ffffff;
    border-radius: 50%;
    box-shadow: 0 2px 8px rgba(37,99,235,0.6);
}
.customer-marker-pulse .ring {
    position: absolute;
    width: 32px;
    height: 32px;
    top: -4px;
    left: -4px;
    border: 3px solid rgba(37,99,235,0.5);
    border-radius: 50%;
    animation: pulseRing 1.8s ease-out infinite;
}
@keyframes pulseRing {
    0% { transform: scale(0.5); opacity: 1; }
    100% { transform: scale(1.6); opacity: 0; }
}

.custom-kitchen-marker {
    background: #ff6b35;
    color: #ffffff;
    border-radius: 50%;
    width: 38px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.25);
    border: 2px solid #ffffff;
    transition: transform 0.2s;
}
.custom-kitchen-marker:hover,
.custom-kitchen-marker.active-marker {
    transform: scale(1.22);
    background: #e85a26;
    box-shadow: 0 6px 16px rgba(232,90,38,0.45);
}
.custom-kitchen-marker.marker-closed {
    background: #64748b;
}

/* ── Map Popup Styling ─────────────────────────────────────────────── */
.leaflet-popup-content-wrapper {
    border-radius: 14px;
    padding: 2px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.18);
}
.leaflet-popup-content {
    margin: 12px;
    line-height: 1.4;
    font-family: inherit;
}
.popup-kitchen-title {
    font-size: 1rem;
    font-weight: 700;
    color: #1a1a24;
    margin-bottom: 2px;
}
.popup-kitchen-chef {
    font-size: 0.8rem;
    color: #64748b;
    margin-bottom: 6px;
}
.popup-badge {
    font-size: 0.72rem;
    font-weight: 600;
    padding: 2px 7px;
    border-radius: 12px;
}

/* Floating map overlay control buttons */
.map-floating-controls {
    position: absolute;
    top: 14px;
    right: 14px;
    z-index: 1000;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.map-floating-btn {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    color: #1e293b;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 0.82rem;
    font-weight: 600;
    box-shadow: 0 3px 10px rgba(0,0,0,0.12);
    display: flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    transition: all 0.2s;
}
.map-floating-btn:hover {
    background: #f8fafc;
    color: #ff6b35;
    border-color: #ff6b35;
}
.map-legend-box {
    position: absolute;
    bottom: 16px;
    left: 16px;
    z-index: 1000;
    background: rgba(255,255,255,0.95);
    backdrop-filter: blur(4px);
    border: 1px solid #e2e8f0;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 0.75rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 14px;
}

/* Mobile responsive adjustments */
@media (max-width: 991.98px) {
    #nearbyMap {
        height: 380px;
        margin-bottom: 1.5rem;
    }
    .kitchen-list-scroll {
        max-height: 480px;
    }
}
</style>

<!-- Hero Section -->
<section class="nearby-hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <span class="badge bg-warning text-dark px-3 py-1 rounded-pill fw-bold mb-2">
                    <i class="bi bi-geo-alt-fill text-danger me-1"></i> Live Geo-Discovery
                </span>
                <h1 class="fw-800 text-white mb-2">
                    Nearby Cloud Kitchens
                </h1>
                <p class="text-white-50 mb-0">
                    Locate authentic Kravyo home kitchens near your current location with live distance & availability.
                </p>
            </div>
            <div class="col-lg-5 text-lg-end mt-3 mt-lg-0">
                <a href="<?= ctx_url('/kitchens') ?>" class="btn btn-outline-light rounded-pill px-4 py-2 fw-semibold">
                    <i class="bi bi-grid-fill me-1"></i> Switch to Grid View
                </a>
            </div>
        </div>
    </div>
</section>

<div class="container pb-5">
    <!-- Location Permission Denied Alert (Hidden by default) -->
    <div id="locAlertDenied" class="alert alert-warning alert-dismissible fade d-none mb-3 shadow-sm rounded-3" role="alert">
        <div class="d-flex align-items-start gap-2">
            <i class="bi bi-geo-alt-fill fs-5 text-warning flex-shrink-0 mt-1"></i>
            <div class="flex-grow-1">
                <strong>Location access was not allowed.</strong>
                <p class="mb-2 small">You can select a city manually below, or click anywhere on the map to find nearby kitchens.</p>
                <div class="d-flex flex-wrap gap-1" id="deniedCityPills">
                    <span class="small fw-semibold me-1 align-self-center">Jump to:</span>
                    <?php 
                    $popularCities = ['Pune', 'Surat', 'Mumbai', 'Ahmedabad', 'Bengaluru', 'Delhi'];
                    foreach ($popularCities as $pCity): 
                    ?>
                        <button type="button" class="btn btn-sm btn-outline-dark py-0 px-2 rounded-pill small btn-quick-city" data-city="<?= $pCity ?>">
                            <?= $pCity ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>

    <!-- Filter & Control Bar -->
    <div class="nearby-control-bar">
        <div class="row g-3 align-items-center">
            <!-- Location Action -->
            <div class="col-lg-3 col-md-5">
                <button type="button" class="btn btn-kravyo-primary w-100 fw-bold py-2 shadow-sm d-flex align-items-center justify-content-center gap-2" id="btnUseLocation">
                    <i class="bi bi-crosshair fs-5"></i>
                    <span id="btnLocationText">Use My Location</span>
                </button>
            </div>

            <!-- Radius Selector Pills -->
            <div class="col-lg-4 col-md-7">
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted small fw-semibold text-nowrap"><i class="bi bi-bullseye me-1"></i>Radius:</span>
                    <div class="btn-group w-100" role="group" id="radiusGroup">
                        <button type="button" class="btn btn-outline-secondary radius-btn" data-radius="2">2 km</button>
                        <button type="button" class="btn btn-outline-secondary radius-btn" data-radius="5">5 km</button>
                        <button type="button" class="btn btn-outline-secondary radius-btn active" data-radius="10">10 km</button>
                        <button type="button" class="btn btn-outline-secondary radius-btn" data-radius="20">20 km</button>
                        <button type="button" class="btn btn-outline-secondary radius-btn" data-radius="50">50 km</button>
                    </div>
                </div>
            </div>

            <!-- City Dropdown -->
            <div class="col-lg-2 col-md-4">
                <select class="form-select" id="selectCityFilter" aria-label="Filter by City">
                    <option value="">All Cities</option>
                    <?php foreach ($cities as $c): ?>
                        <option value="<?= sanitize($c) ?>" <?= ($selectedCity === $c) ? 'selected' : '' ?>>
                            <?= sanitize($c) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Search Input -->
            <div class="col-lg-3 col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control border-start-0" id="inputKitchenSearch" placeholder="Search kitchen or area..." value="">
                </div>
            </div>
        </div>

        <!-- Secondary controls: Sort & Open Only toggle -->
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pt-3 mt-3 border-top">
            <div class="d-flex align-items-center gap-3">
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" id="chkOpenOnly">
                    <label class="form-check-label small fw-semibold" for="chkOpenOnly">
                        <i class="bi bi-clock-fill text-success me-1"></i> Open Now Only
                    </label>
                </div>
                <span class="text-muted small d-none d-md-inline">•</span>
                <span class="text-muted small" id="activeLocationLabel">
                    <i class="bi bi-info-circle me-1"></i> <span id="locationStatusText">Click "Use My Location" or select a city</span>
                </span>
            </div>

            <div class="d-flex align-items-center gap-2">
                <label for="selectSortBy" class="small text-muted fw-semibold text-nowrap">Sort:</label>
                <select class="form-select form-select-sm" id="selectSortBy" style="width: 140px;">
                    <option value="distance" selected>Nearest First</option>
                    <option value="rating">Highest Rated</option>
                    <option value="open">Open Kitchens</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Main Side-by-Side Area -->
    <div class="row g-4">
        <!-- Left: Kitchen Cards List -->
        <div class="col-lg-5 order-2 order-lg-1">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-shop me-2 text-warning"></i>Kitchens Found
                    <span class="badge bg-secondary ms-1 fs-6" id="kitchenCountBadge"><?= count($initialKitchens) ?></span>
                </h5>
                <span class="text-muted small" id="radiusIndicatorLabel">Within 10 km</span>
            </div>

            <!-- Loading Spinner State -->
            <div id="kitchensLoading" class="text-center py-5 d-none">
                <div class="spinner-border text-warning" role="status">
                    <span class="visually-hidden">Loading kitchens...</span>
                </div>
                <p class="text-muted small mt-2 mb-0">Finding nearby cloud kitchens...</p>
            </div>

            <!-- Empty State -->
            <div id="emptyKitchensState" class="card border-0 shadow-sm text-center p-4 d-none">
                <div class="py-4">
                    <div class="mb-3 text-warning opacity-75 display-4">
                        <i class="bi bi-geo-alt"></i>
                    </div>
                    <h5 class="fw-bold text-dark">No nearby kitchens found</h5>
                    <p class="text-muted small mb-3">
                        We couldn't find any approved kitchens within <span id="emptyRadiusText">10 km</span> of your selected location.
                    </p>
                    <div class="d-flex flex-wrap justify-content-center gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill btn-expand-radius" data-radius="20">
                            Search within 20 km
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill btn-expand-radius" data-radius="50">
                            Search within 50 km
                        </button>
                        <button type="button" class="btn btn-kravyo-primary btn-sm rounded-pill" id="btnShowAllKitchens">
                            Show All Kitchens
                        </button>
                    </div>
                </div>
            </div>

            <!-- Scrollable Kitchen Cards Container -->
            <div class="kitchen-list-scroll" id="kitchenListContainer">
                <?php if (!empty($initialKitchens)): ?>
                    <?php foreach ($initialKitchens as $idx => $k): ?>
                        <div class="nearby-kitchen-card p-3" data-id="<?= (int)$k['id'] ?>" data-lat="<?= (float)$k['latitude'] ?>" data-lng="<?= (float)$k['longitude'] ?>">
                            <div class="d-flex gap-3">
                                <?php if (!empty($k['banner_image'])): ?>
                                    <img src="<?= UPLOAD_URL . '/kitchens/' . $k['banner_image'] ?>" alt="<?= sanitize($k['kitchen_name']) ?>" class="nearby-card-thumb">
                                <?php else: ?>
                                    <div class="nearby-card-thumb-placeholder">
                                        <i class="bi bi-shop"></i>
                                    </div>
                                <?php endif; ?>

                                <div class="flex-grow-1 min-w-0">
                                    <div class="d-flex justify-content-between align-items-start gap-1">
                                        <h6 class="fw-bold mb-1 text-truncate text-dark">
                                            <?= sanitize($k['kitchen_name']) ?>
                                        </h6>
                                        <?php if ((int)$k['is_open'] === 1): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small py-1 px-2" style="font-size:0.72rem;">
                                                <i class="bi bi-circle-fill me-1" style="font-size:0.5rem;"></i>Open
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill small py-1 px-2" style="font-size:0.72rem;">
                                                <i class="bi bi-circle-fill me-1" style="font-size:0.5rem;"></i>Closed
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="text-muted small mb-2 d-flex align-items-center gap-1">
                                        <i class="bi bi-person text-secondary"></i>
                                        <span class="text-truncate"><?= sanitize($k['chef_name']) ?></span>
                                        <?php if (($k['hygiene_badge'] ?? '') === 'verified'): ?>
                                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill ms-1" style="font-size:0.68rem;" title="Hygiene Verified">
                                                <i class="bi bi-shield-check"></i> Verified
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                        <?php if (isset($k['distance'])): ?>
                                            <span class="distance-badge">
                                                <i class="bi bi-pin-map-fill"></i> <?= (float)$k['distance'] < 1 ? round((float)$k['distance'] * 1000) . ' m away' : (float)$k['distance'] . ' km away' ?>
                                            </span>
                                        <?php endif; ?>

                                        <!-- Rating -->
                                        <span class="small fw-semibold text-warning d-inline-flex align-items-center gap-1">
                                            <i class="bi bi-star-fill"></i>
                                            <?= !empty($k['avg_rating']) ? (float)$k['avg_rating'] : 'New' ?>
                                            <?php if (!empty($k['review_count'])): ?>
                                                <span class="text-muted fw-normal">(<?= (int)$k['review_count'] ?>)</span>
                                            <?php endif; ?>
                                        </span>

                                        <span class="badge bg-light text-dark border small" style="font-size:0.72rem;">
                                            <i class="bi bi-egg-fried me-1 text-warning"></i><?= (int)($k['dish_count'] ?? 0) ?> dishes
                                        </span>
                                    </div>

                                    <p class="small text-muted mb-2 text-truncate">
                                        <i class="bi bi-geo-alt me-1 text-danger"></i><?= sanitize($k['address']) ?>, <?= sanitize($k['city']) ?>
                                    </p>

                                    <div class="d-flex justify-content-between align-items-center pt-1">
                                        <small class="text-primary fw-semibold"><i class="bi bi-cursor-fill me-1"></i>Click to view on map</small>
                                        <a href="<?= ctx_url('/kitchen/' . $k['id']) ?>" class="btn btn-sm btn-outline-warning text-dark fw-bold px-3 rounded-pill" onclick="event.stopPropagation();">
                                            View Kitchen <i class="bi bi-arrow-right ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right: Interactive Leaflet Map -->
        <div class="col-lg-7 order-1 order-lg-2">
            <div class="position-relative">
                <!-- Floating Map Buttons -->
                <div class="map-floating-controls">
                    <button type="button" class="map-floating-btn" id="btnRecenterMe" title="Center map on your current location">
                        <i class="bi bi-crosshair2 text-primary"></i> Recenter to Me
                    </button>
                    <button type="button" class="map-floating-btn" id="btnFitKitchens" title="Zoom to fit all nearby kitchens">
                        <i class="bi bi-arrows-angle-expand text-secondary"></i> Fit All
                    </button>
                </div>

                <!-- Map Legend -->
                <div class="map-legend-box d-none d-sm-flex">
                    <span class="d-flex align-items-center gap-1">
                        <span style="display:inline-block;width:10px;height:10px;background:#2563eb;border-radius:50%;"></span> You
                    </span>
                    <span class="d-flex align-items-center gap-1">
                        <span style="display:inline-block;width:10px;height:10px;background:#ff6b35;border-radius:50%;"></span> Open Kitchen
                    </span>
                    <span class="d-flex align-items-center gap-1">
                        <span style="display:inline-block;width:10px;height:10px;background:#64748b;border-radius:50%;"></span> Closed Kitchen
                    </span>
                </div>

                <!-- Leaflet Container -->
                <div id="nearbyMap"></div>
            </div>
        </div>
    </div>
</div>

<!-- Google Maps is loaded via the partial above -->

<script>
/**
 * Kravyo Nearby Cloud Kitchens Map Controller
 */
(function() {
    'use strict';

    // State
    var state = {
        userLat: <?= $userLat !== null ? (float)$userLat : 'null' ?>,
        userLng: <?= $userLng !== null ? (float)$userLng : 'null' ?>,
        radius: <?= (float)$radius ?>,
        city: <?= json_encode($selectedCity) ?>,
        keyword: '',
        openOnly: 0,
        sort: 'distance',
        kitchens: <?= json_encode(array_values($initialKitchens), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>,
        activeKitchenId: null
    };

    var map = null;
    var userMarker = null;
    var userAccuracyCircle = null;
    var markersMap = {}; // id => google.maps.Marker
    var infoWindow = null;

    var defaultCenter = { lat: 18.5204, lng: 73.8567 }; // Pune
    var defaultZoom = 12;

    if (state.kitchens && state.kitchens.length > 0) {
        var firstValid = state.kitchens.find(function(k) { return k.latitude && k.longitude; });
        if (firstValid) {
            defaultCenter = { lat: parseFloat(firstValid.latitude), lng: parseFloat(firstValid.longitude) };
        }
    }

    // ── 1. Map Initialization ─────────────────────────────────────────────
    function initMap() {
        if (typeof KravyoMaps === 'undefined') return;

        KravyoMaps.init().then(() => {
            var startLat = (state.userLat && state.userLng) ? state.userLat : defaultCenter.lat;
            var startLng = (state.userLat && state.userLng) ? state.userLng : defaultCenter.lng;

            map = KravyoMaps.createMap('nearbyMap', startLat, startLng, defaultZoom);
            infoWindow = new google.maps.InfoWindow();

            map.addListener('click', function(e) {
                var lat = e.latLng.lat();
                var lng = e.latLng.lng();
                setUserLocation(lat, lng, 'Selected Location on Map');
                fetchNearbyKitchens();
            });

            renderKitchenMarkers(state.kitchens);

            if (state.userLat && state.userLng) {
                renderUserMarker(state.userLat, state.userLng, 'Your Location');
            } else if (state.city && state.city !== '') {
                var cKey = state.city.toLowerCase().trim();
                var cityCenters = {
                    'pune': [18.5204, 73.8567],
                    'surat': [21.1702, 72.8311],
                    'mumbai': [19.0760, 72.8777],
                    'ahmedabad': [23.0225, 72.5714],
                    'vadodara': [22.3072, 73.1812],
                    'rajkot': [22.3039, 70.8022],
                    'delhi': [28.6139, 77.2090],
                    'bengaluru': [12.9716, 77.5946],
                    'bangalore': [12.9716, 77.5946],
                    'banglore': [12.9716, 77.5946],
                    'hyderabad': [17.3850, 78.4867],
                    'chennai': [13.0827, 80.2707],
                    'jaipur': [26.9124, 75.7873],
                    'lucknow': [26.8467, 80.9462],
                    'kolkata': [22.5726, 88.3639],
                    'nashik': [19.9975, 73.7898]
                };
                if (cityCenters[cKey]) {
                    setUserLocation(cityCenters[cKey][0], cityCenters[cKey][1], state.city);
                    map.setCenter({lat: cityCenters[cKey][0], lng: cityCenters[cKey][1]});
                    map.setZoom(12);
                }
            } else {
                requestUserLocation(false);
            }

            // Handle route_to query parameter
            var urlParams = new URLSearchParams(window.location.search);
            var routeToId = urlParams.get('route_to');
            if (routeToId) {
                setTimeout(function() {
                    drawRouteTo(routeToId);
                }, 800);
            }
        });
    }

    window._currentRouteLine = null;
    function drawRouteTo(kitchenId) {
        if (!state.userLat || !state.userLng) return;
        var k = state.kitchens.find(function(kitchen) { return kitchen.id == kitchenId; });
        if (k && k.latitude && k.longitude && typeof KravyoMaps.fetchRoute === 'function') {
            var origin = { lat: parseFloat(state.userLat), lng: parseFloat(state.userLng) };
            var destination = { lat: parseFloat(k.latitude), lng: parseFloat(k.longitude) };
            
            KravyoMaps.fetchRoute(origin, destination).then(function(routeData) {
                if (window._currentRouteLine) window._currentRouteLine.remove();
                window._currentRouteLine = new KravyoMaps.RouteLine(map, routeData);
                
                // Fit bounds to route
                var bounds = new google.maps.LatLngBounds();
                bounds.extend(origin);
                bounds.extend(destination);
                
                // Attempt to decode path if available
                if (routeData.polyline && google.maps.geometry && google.maps.geometry.encoding) {
                    var path = google.maps.geometry.encoding.decodePath(routeData.polyline);
                    path.forEach(function(pt) { bounds.extend(pt); });
                }
                map.fitBounds(bounds);
                
                // Highlight card and trigger marker
                highlightCard(kitchenId);
                if (markersMap[kitchenId]) {
                    google.maps.event.trigger(markersMap[kitchenId], 'click');
                }
            }).catch(function(err) {
                console.error('Failed to draw route:', err);
                // Fallback: just center on it
                map.setCenter(destination);
                map.setZoom(15);
                highlightCard(kitchenId);
                if (markersMap[kitchenId]) {
                    google.maps.event.trigger(markersMap[kitchenId], 'click');
                }
            });
        }
    }

    // ── 2. User Location Handling ─────────────────────────────────────────
    function renderUserMarker(lat, lng, label) {
        if (!map) return;

        if (userMarker) {
            userMarker.setMap(null);
        }
        if (userAccuracyCircle) {
            userAccuracyCircle.setMap(null);
        }

        userMarker = KravyoMaps.addStaticMarker(map, lat, lng, label || 'Current Location', 'home');

        userAccuracyCircle = new google.maps.Circle({
            strokeColor: '#2563eb',
            strokeOpacity: 0.8,
            strokeWeight: 1,
            fillColor: '#3b82f6',
            fillOpacity: 0.05,
            map: map,
            center: { lat: lat, lng: lng },
            radius: state.radius * 1000
        });
    }

    function setUserLocation(lat, lng, label) {
        state.userLat = lat;
        state.userLng = lng;
        renderUserMarker(lat, lng, label);

        var statusEl = document.getElementById('locationStatusText');
        if (statusEl) {
            statusEl.innerHTML = '<strong class="text-success"><i class="bi bi-geo-alt-fill"></i> ' + (label || 'Location Active') + '</strong>';
        }
        var btnText = document.getElementById('btnLocationText');
        if (btnText) {
            btnText.textContent = 'Location Active';
        }
        var alertDenied = document.getElementById('locAlertDenied');
        if (alertDenied) {
            alertDenied.classList.add('d-none');
            alertDenied.classList.remove('show');
        }
    }

    function requestUserLocation(isUserClick) {
        if (!navigator.geolocation) {
            if (isUserClick) {
                showLocationError('Geolocation is not supported by your browser. Please select a city or click on the map.');
            }
            return;
        }

        var btn = document.getElementById('btnUseLocation');
        var btnText = document.getElementById('btnLocationText');
        if (btn && isUserClick) {
            btn.disabled = true;
            btnText.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Locating...';
        }

        navigator.geolocation.getCurrentPosition(
            function(position) {
                if (btn) {
                    btn.disabled = false;
                    btnText.textContent = 'Use My Location';
                }
                var lat = position.coords.latitude;
                var lng = position.coords.longitude;

                setUserLocation(lat, lng, 'Current GPS Location');
                if (map) map.setCenter({lat: lat, lng: lng});
                if (map) map.setZoom(13);
                fetchNearbyKitchens();
            },
            function(error) {
                if (btn) {
                    btn.disabled = false;
                    btnText.textContent = 'Use My Location';
                }
                console.warn('Geolocation error:', error.message);
                var alertDenied = document.getElementById('locAlertDenied');
                if (alertDenied) {
                    alertDenied.classList.remove('d-none');
                    alertDenied.classList.add('show');
                }
                var statusEl = document.getElementById('locationStatusText');
                if (statusEl) {
                    statusEl.innerHTML = '<span class="text-muted">Browsing all kitchens. Click map to set pin.</span>';
                }
            },
            { enableHighAccuracy: true, timeout: 8000, maximumAge: 60000 }
        );
    }

    function showLocationError(msg) {
        var alertDenied = document.getElementById('locAlertDenied');
        if (alertDenied) {
            alertDenied.classList.remove('d-none');
            alertDenied.classList.add('show');
        }
    }

    // ── 3. Kitchen Markers & Popups ───────────────────────────────────────
    function renderKitchenMarkers(kitchens) {
        Object.values(markersMap).forEach(m => m.setMap(null));
        markersMap = {};

        if (!kitchens || kitchens.length === 0) return;

        var bounds = new google.maps.LatLngBounds();

        kitchens.forEach(function(k) {
            var lat = parseFloat(k.latitude);
            var lng = parseFloat(k.longitude);
            if (isNaN(lat) || isNaN(lng)) return;

            var pos = { lat: lat, lng: lng };
            bounds.extend(pos);

            var isOpen = (parseInt(k.is_open, 10) === 1 || k.is_open === true);
            var title = escapeHtml(k.name || k.kitchen_name);

            var marker = KravyoMaps.addStaticMarker(map, lat, lng, title, 'restaurant');
            
            // Popup HTML
            var kitchenName = escapeHtml(k.name || k.kitchen_name);
            var chefName = escapeHtml(k.chef_name || '');
            var address = escapeHtml((k.address || '') + (k.city ? ', ' + k.city : ''));
            var rating = (k.avg_rating && k.avg_rating > 0) ? (k.avg_rating + ' ★') : 'New';
            var reviewCount = k.review_count ? '(' + k.review_count + ')' : '';
            var statusPill = isOpen 
                ? '<span class="badge bg-success-subtle text-success border border-success-subtle popup-badge">Open Now</span>'
                : '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle popup-badge">Closed</span>';

            var distanceHtml = '';
            if (k.distance !== null && k.distance !== undefined) {
                var dKm = parseFloat(k.distance);
                var dText = dKm < 1 ? Math.round(dKm * 1000) + ' m away' : dKm + ' km away';
                distanceHtml = '<div class="distance-badge my-1"><i class="bi bi-pin-map-fill"></i> ' + dText + '</div>';
            }

            var kitchenUrl = k.kitchen_url || ('<?= ctx_url('/kitchen/') ?>' + k.id);

            var popupContent = '' +
                '<div style="min-width: 210px; padding: 10px;">' +
                    '<div class="d-flex justify-content-between align-items-center mb-1">' +
                        statusPill +
                        '<span class="small fw-bold text-warning"><i class="bi bi-star-fill"></i> ' + rating + ' <span class="text-muted fw-normal">' + reviewCount + '</span></span>' +
                    '</div>' +
                    '<div class="popup-kitchen-title fs-6 fw-bold">' + kitchenName + '</div>' +
                    '<div class="popup-kitchen-chef small"><i class="bi bi-person me-1"></i>Chef ' + chefName + '</div>' +
                    distanceHtml +
                    '<div class="small text-muted mb-2 text-truncate" style="max-width: 220px;"><i class="bi bi-geo-alt me-1"></i>' + address + '</div>' +
                    '<a href="' + kitchenUrl + '" class="btn btn-sm btn-primary w-100 fw-bold rounded-pill text-white py-1">' +
                        'View Kitchen <i class="bi bi-arrow-right ms-1"></i>' +
                    '</a>' +
                '</div>';

            marker.addListener('click', function() {
                highlightCard(k.id);
                if (infoWindow) {
                    infoWindow.setContent(popupContent);
                    infoWindow.open(map, marker);
                }
            });

            markersMap[k.id] = marker;
        });

        // Include user location in bounds if exists
        if (state.userLat && state.userLng) {
            bounds.extend({ lat: state.userLat, lng: state.userLng });
        }

        // Fit map bounds to show all kitchens and user
        if (Object.keys(markersMap).length > 0 || (state.userLat && state.radius)) {
            // If no kitchens found but we have user location, fake bounds based on radius
            if (Object.keys(markersMap).length === 0 && state.userLat) {
                var c = new google.maps.LatLng(state.userLat, state.userLng);
                var r = state.radius * 1000;
                var circle = new google.maps.Circle({ center: c, radius: r });
                bounds = circle.getBounds();
            }
            
            map.fitBounds(bounds);
            
            // Limit max zoom so it doesn't zoom in too close when there's only 1 item
            var listener = google.maps.event.addListener(map, "idle", function() { 
                if (map.getZoom() > 15) map.setZoom(15); 
                google.maps.event.removeListener(listener); 
            });
        }
    }

    // ── 4. Render Kitchen Cards List in Left Column ───────────────────────
    function renderKitchenCards(kitchens) {
        var container = document.getElementById('kitchenListContainer');
        var emptyState = document.getElementById('emptyKitchensState');
        var countBadge = document.getElementById('kitchenCountBadge');
        var radiusIndicator = document.getElementById('radiusIndicatorLabel');

        if (!container) return;

        if (countBadge) {
            countBadge.textContent = kitchens.length;
        }
        if (radiusIndicator) {
            radiusIndicator.textContent = 'Within ' + state.radius + ' km';
        }

        if (!kitchens || kitchens.length === 0) {
            container.innerHTML = '';
            if (emptyState) {
                var emptyRad = document.getElementById('emptyRadiusText');
                if (emptyRad) emptyRad.textContent = state.radius + ' km';
                emptyState.classList.remove('d-none');
            }
            return;
        }

        if (emptyState) {
            emptyState.classList.add('d-none');
        }

        var html = '';
        kitchens.forEach(function(k) {
            var isOpen = (parseInt(k.is_open, 10) === 1 || k.is_open === true);
            var kitchenName = escapeHtml(k.name || k.kitchen_name);
            var chefName = escapeHtml(k.chef_name || '');
            var address = escapeHtml((k.address || '') + (k.city ? ', ' + k.city : ''));
            var rating = (k.avg_rating && k.avg_rating > 0) ? (k.avg_rating) : 'New';
            var reviewCount = k.review_count ? '(' + k.review_count + ')' : '';
            var dishCount = k.dish_count || 0;
            var isVerified = (k.hygiene_badge === 'verified');

            var bannerHtml = k.banner_url 
                ? '<img src="' + escapeHtml(k.banner_url) + '" alt="' + kitchenName + '" class="nearby-card-thumb">'
                : '<div class="nearby-card-thumb-placeholder"><i class="bi bi-shop"></i></div>';

            var statusBadge = isOpen
                ? '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small py-1 px-2" style="font-size:0.72rem;"><i class="bi bi-circle-fill me-1" style="font-size:0.5rem;"></i>Open</span>'
                : '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill small py-1 px-2" style="font-size:0.72rem;"><i class="bi bi-circle-fill me-1" style="font-size:0.5rem;"></i>Closed</span>';

            var distanceBadge = '';
            if (k.distance !== null && k.distance !== undefined) {
                var dKm = parseFloat(k.distance);
                var dText = dKm < 1 ? Math.round(dKm * 1000) + ' m away' : dKm + ' km away';
                distanceBadge = '<span class="distance-badge"><i class="bi bi-pin-map-fill"></i> ' + dText + '</span>';
            }

            var verifiedBadge = isVerified
                ? '<span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill ms-1" style="font-size:0.68rem;" title="Hygiene Verified"><i class="bi bi-shield-check"></i> Verified</span>'
                : '';

            var kitchenUrl = k.kitchen_url || ('<?= ctx_url('/kitchen/') ?>' + k.id);

            html += '' +
                '<div class="nearby-kitchen-card p-3" data-id="' + k.id + '" data-lat="' + k.latitude + '" data-lng="' + k.longitude + '">' +
                    '<div class="d-flex gap-3">' +
                        bannerHtml +
                        '<div class="flex-grow-1 min-w-0">' +
                            '<div class="d-flex justify-content-between align-items-start gap-1">' +
                                '<h6 class="fw-bold mb-1 text-truncate text-dark">' + kitchenName + '</h6>' +
                                statusBadge +
                            '</div>' +
                            '<div class="text-muted small mb-2 d-flex align-items-center gap-1">' +
                                '<i class="bi bi-person text-secondary"></i>' +
                                '<span class="text-truncate">' + chefName + '</span>' +
                                verifiedBadge +
                            '</div>' +
                            '<div class="d-flex flex-wrap align-items-center gap-2 mb-2">' +
                                distanceBadge +
                                '<span class="small fw-semibold text-warning d-inline-flex align-items-center gap-1">' +
                                    '<i class="bi bi-star-fill"></i> ' + rating + ' ' +
                                    '<span class="text-muted fw-normal">' + reviewCount + '</span>' +
                                '</span>' +
                                '<span class="badge bg-light text-dark border small" style="font-size:0.72rem;">' +
                                    '<i class="bi bi-egg-fried me-1 text-warning"></i>' + dishCount + ' dishes' +
                                '</span>' +
                            '</div>' +
                            '<p class="small text-muted mb-2 text-truncate">' +
                                '<i class="bi bi-geo-alt me-1 text-danger"></i>' + address +
                            '</p>' +
                            '<div class="d-flex justify-content-between align-items-center pt-1">' +
                                '<small class="text-primary fw-semibold"><i class="bi bi-cursor-fill me-1"></i>Click to view on map</small>' +
                                '<a href="' + kitchenUrl + '" class="btn btn-sm btn-outline-warning text-dark fw-bold px-3 rounded-pill" onclick="event.stopPropagation();">' +
                                    'View Kitchen <i class="bi bi-arrow-right ms-1"></i>' +
                                '</a>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>';
        });

        container.innerHTML = html;

        // Attach click listeners to cards
        attachCardClickEvents();
    }

    function attachCardClickEvents() {
        var cards = document.querySelectorAll('.nearby-kitchen-card');
        cards.forEach(function(card) {
            card.addEventListener('click', function() {
                var kid = parseInt(this.getAttribute('data-id'), 10);
                var lat = parseFloat(this.getAttribute('data-lat'));
                var lng = parseFloat(this.getAttribute('data-lng'));

                highlightCard(kid);

                if (!isNaN(lat) && !isNaN(lng) && map) {
                    map.panTo({lat: lat, lng: lng});
                    map.setZoom(15);
                    if (markersMap[kid] && infoWindow) {
                        google.maps.event.trigger(markersMap[kid], 'click');
                    }
                }
            });
        });
    }

    function highlightCard(kitchenId) {
        state.activeKitchenId = kitchenId;
        var cards = document.querySelectorAll('.nearby-kitchen-card');
        cards.forEach(function(c) {
            if (parseInt(c.getAttribute('data-id'), 10) === kitchenId) {
                c.classList.add('active-card');
                c.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } else {
                c.classList.remove('active-card');
            }
        });
    }

    // ── 5. AJAX API Fetcher ───────────────────────────────────────────────
    function fetchNearbyKitchens() {
        var loadingEl = document.getElementById('kitchensLoading');
        var container = document.getElementById('kitchenListContainer');
        var emptyState = document.getElementById('emptyKitchensState');

        if (loadingEl) loadingEl.classList.remove('d-none');
        if (emptyState) emptyState.classList.add('d-none');

        // Build API query params
        var params = new URLSearchParams();
        if (state.userLat !== null && state.userLng !== null) {
            params.append('lat', state.userLat);
            params.append('lng', state.userLng);
            params.append('radius', state.radius);
        }
        if (state.city) {
            params.append('city', state.city);
        }
        if (state.keyword) {
            params.append('q', state.keyword);
        }
        if (state.openOnly) {
            params.append('open_only', 1);
        }
        if (state.sort) {
            params.append('sort', state.sort);
        }

        var apiUrl = '<?= url('/api/kitchens/nearby') ?>?' + params.toString();

        fetch(apiUrl)
            .then(function(res) {
                if (!res.ok) throw new Error('Network error ' + res.status);
                return res.json();
            })
            .then(function(data) {
                if (loadingEl) loadingEl.classList.add('d-none');
                if (data.success && Array.isArray(data.kitchens)) {
                    state.kitchens = data.kitchens;
                    renderKitchenCards(state.kitchens);
                    renderKitchenMarkers(state.kitchens);

                    // Update accuracy circle radius if exists
                    if (userAccuracyCircle && state.userLat && state.userLng) {
                        userAccuracyCircle.setRadius(state.radius * 1000);
                    }
                } else {
                    renderKitchenCards([]);
                    renderKitchenMarkers([]);
                }
            })
            .catch(function(err) {
                if (loadingEl) loadingEl.classList.add('d-none');
                console.error('Failed to fetch nearby kitchens:', err);
                renderKitchenCards([]);
            });
    }

    // ── 6. UI Controls & Event Listeners ──────────────────────────────────
    function setupControls() {
        // Use location button
        var btnUseLoc = document.getElementById('btnUseLocation');
        if (btnUseLoc) {
            btnUseLoc.addEventListener('click', function() {
                requestUserLocation(true);
            });
        }

        // Radius buttons
        var radiusButtons = document.querySelectorAll('.radius-btn');
        radiusButtons.forEach(function(btn) {
            btn.addEventListener('click', function() {
                radiusButtons.forEach(function(b) { b.classList.remove('active'); });
                this.classList.add('active');
                state.radius = parseFloat(this.getAttribute('data-radius'));
                fetchNearbyKitchens();
            });
        });

        // Expand radius buttons from empty state
        document.querySelectorAll('.btn-expand-radius').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var rad = parseFloat(this.getAttribute('data-radius'));
                state.radius = rad;
                radiusButtons.forEach(function(b) {
                    if (parseFloat(b.getAttribute('data-radius')) === rad) {
                        b.classList.add('active');
                    } else {
                        b.classList.remove('active');
                    }
                });
                fetchNearbyKitchens();
            });
        });

        // Show all kitchens button
        var btnShowAll = document.getElementById('btnShowAllKitchens');
        if (btnShowAll) {
            btnShowAll.addEventListener('click', function() {
                state.city = '';
                var citySelect = document.getElementById('selectCityFilter');
                if (citySelect) citySelect.value = '';
                state.radius = 50;
                radiusButtons.forEach(function(b) {
                    if (parseFloat(b.getAttribute('data-radius')) === 50) {
                        b.classList.add('active');
                    } else {
                        b.classList.remove('active');
                    }
                });
                fetchNearbyKitchens();
            });
        }

        // City selector
        var citySelect = document.getElementById('selectCityFilter');
        if (citySelect) {
            citySelect.addEventListener('change', function() {
                state.city = this.value;
                var cKey = this.value.toLowerCase().trim();
                
                if (cKey === '') {
                    // "All Cities" selected - clear user location to show everything
                    state.userLat = null;
                    state.userLng = null;
                    if (userMarker) userMarker.setMap(null);
                    if (userAccuracyCircle) userAccuracyCircle.setMap(null);
                    
                    var statusEl = document.getElementById('locationStatusText');
                    if (statusEl) statusEl.innerHTML = 'Showing All Cities';
                    var btnText = document.getElementById('btnLocationText');
                    if (btnText) btnText.textContent = 'Use My Location';
                    
                    fetchNearbyKitchens();
                    setTimeout(function() {
                        var btnFit = document.getElementById('btnFitKitchens');
                        if (btnFit) btnFit.click();
                    }, 800);
                } else {
                    // Move map center towards the city center if we know it
                    var cityCenters = {
                        'pune': [18.5204, 73.8567],
                        'surat': [21.1702, 72.8311],
                        'mumbai': [19.0760, 72.8777],
                        'ahmedabad': [23.0225, 72.5714],
                        'vadodara': [22.3072, 73.1812],
                        'rajkot': [22.3039, 70.8022],
                        'delhi': [28.6139, 77.2090],
                        'bengaluru': [12.9716, 77.5946],
                        'bangalore': [12.9716, 77.5946],
                        'banglore': [12.9716, 77.5946],
                        'hyderabad': [17.3850, 78.4867],
                        'chennai': [13.0827, 80.2707],
                        'jaipur': [26.9124, 75.7873],
                        'lucknow': [26.8467, 80.9462],
                        'kolkata': [22.5726, 88.3639],
                        'nashik': [19.9975, 73.7898]
                    };
                    if (cityCenters[cKey]) {
                        setUserLocation(cityCenters[cKey][0], cityCenters[cKey][1], this.value);
                        if (map) map.setCenter({lat: cityCenters[cKey][0], lng: cityCenters[cKey][1]});
                        if (map) map.setZoom(12);
                    }
                    fetchNearbyKitchens();
                }
            });
        }

        // Quick city buttons in permission denied alert
        document.querySelectorAll('.btn-quick-city').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var c = this.getAttribute('data-city');
                if (citySelect) citySelect.value = c;
                state.city = c;
                var cityCenters = {
                    'Pune': [18.5204, 73.8567],
                    'Surat': [21.1702, 72.8311],
                    'Mumbai': [19.0760, 72.8777],
                    'Ahmedabad': [23.0225, 72.5714],
                    'Bengaluru': [12.9716, 77.5946],
                    'Delhi': [28.6139, 77.2090]
                };
                if (cityCenters[c]) {
                    setUserLocation(cityCenters[c][0], cityCenters[c][1], c);
                    if (map) map.setCenter({lat: cityCenters[c][0], lng: cityCenters[c][1]});
                    if (map) map.setZoom(12);
                }
                fetchNearbyKitchens();
            });
        });

        // Search input with Places Autocomplete
        var searchInput = document.getElementById('inputKitchenSearch');
        if (searchInput) {
            KravyoMaps.initAutocomplete('inputKitchenSearch', map, null, function(pos, place) {
                // When user selects a place, center map and search nearby
                state.userLat = pos.lat;
                state.userLng = pos.lng;
                
                // Drop a marker for the selected area
                if (userMarker) {
                    userMarker.position = pos;
                } else {
                    userMarker = KravyoMaps.addDraggableMarker(map, pos.lat, pos.lng, function(nlat, nlng) {
                        state.userLat = nlat;
                        state.userLng = nlng;
                        fetchNearbyKitchens();
                    });
                }
                
                if (userAccuracyCircle) {
                    userAccuracyCircle.setCenter(pos);
                }
                
                if (map) {
                    map.setCenter(pos);
                    map.setZoom(14);
                }
                
                var lText = document.getElementById('locationStatusText');
                if (lText) lText.innerHTML = 'Showing near: <strong>' + (place.name || 'Selected Location') + '</strong>';
                
                fetchNearbyKitchens();
            });
            
            // Allow normal text search fallback
            var searchDebounceTimer = null;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchDebounceTimer);
                var val = this.value.trim();
                searchDebounceTimer = setTimeout(function() {
                    state.keyword = val;
                    fetchNearbyKitchens();
                }, 350);
            });
        }

        // Open only toggle
        var openCheck = document.getElementById('chkOpenOnly');
        if (openCheck) {
            openCheck.addEventListener('change', function() {
                state.openOnly = this.checked ? 1 : 0;
                fetchNearbyKitchens();
            });
        }

        // Sort dropdown
        var sortSelect = document.getElementById('selectSortBy');
        if (sortSelect) {
            sortSelect.addEventListener('change', function() {
                state.sort = this.value;
                fetchNearbyKitchens();
            });
        }

        // Recenter to me button
        var btnRecenter = document.getElementById('btnRecenterMe');
        if (btnRecenter) {
            btnRecenter.addEventListener('click', function() {
                if (state.userLat && state.userLng && map) {
                    map.panTo({lat: state.userLat, lng: state.userLng});
                    map.setZoom(14);
                } else {
                    requestUserLocation(true);
                }
            });
        }

        // Fit all kitchens button
        var btnFit = document.getElementById('btnFitKitchens');
        if (btnFit) {
            btnFit.addEventListener('click', function() {
                var bounds = [];
                if (state.kitchens) {
                    state.kitchens.forEach(function(k) {
                        var lat = parseFloat(k.latitude);
                        var lng = parseFloat(k.longitude);
                        if (!isNaN(lat) && !isNaN(lng)) bounds.push([lat, lng]);
                    });
                }
                if (state.userLat && state.userLng) {
                    bounds.push([state.userLat, state.userLng]);
                }
                if (bounds.length > 0 && map) {
                    var gb = new google.maps.LatLngBounds();
                    bounds.forEach(function(b) { gb.extend({lat: b[0], lng: b[1]}); });
                    map.fitBounds(gb);
                }
            });
        }
    }

    function escapeHtml(text) {
        if (!text) return '';
        var mapObj = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, function(m) { return mapObj[m]; });
    }

    // ── 7. DOM Ready ──────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function() {
        initMap();
        setupControls();
        attachCardClickEvents();
    });

})();
</script>
