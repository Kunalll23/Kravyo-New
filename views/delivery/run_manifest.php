<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="<?= url('/delivery/runs') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Back to Runs
        </a>
        <span class="badge <?= $run['status'] === 'completed' ? 'bg-success' : ($run['status'] === 'in_progress' ? 'bg-primary' : 'bg-warning text-dark') ?> px-3 py-2 rounded-pill fw-bold text-uppercase">
            <?= sanitize($run['status']) ?>
        </span>
    </div>

    <!-- Header Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
        <div class="card-body p-4 bg-light border-bottom">
            <div class="row align-items-center g-3">
                <div class="col-md-7">
                    <span class="badge bg-secondary px-2 py-1 rounded-pill small mb-2">TIFFIN MANIFEST</span>
                    <h3 class="fw-bold mb-1">Run #<?= sanitize($run['run_code']) ?></h3>
                    <div class="text-muted small">
                        <i class="bi bi-calendar-event me-1"></i> <?= date('D, M d, Y', strtotime($run['delivery_date'])) ?> &bull;
                        <span class="text-uppercase fw-bold text-primary"><?= sanitize($run['meal_type']) ?></span> &bull;
                        <i class="bi bi-geo-alt me-1"></i> <?= sanitize($run['city']) ?>
                    </div>
                </div>
                <div class="col-md-5 text-md-end">
                    <div class="d-inline-block text-start text-md-end me-md-3">
                        <small class="text-muted d-block" style="font-size:0.75rem;">BATCH PAYOUT</small>
                        <span class="fs-3 fw-bold text-success">₹<?= number_format($run['total_payout'], 2) ?></span>
                    </div>
                    <?php if ($run['status'] === 'assigned'): ?>
                        <form method="POST" action="<?= url('/delivery/run/' . $run['id'] . '/start') ?>" class="d-inline-block">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-success fw-bold px-4 py-2 rounded-pill shadow">
                                <i class="bi bi-play-circle-fill me-1"></i> Start Run Now
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php
        $completedCount = 0;
        foreach ($stops as $s) {
            if ($s['status'] === 'completed') $completedCount++;
        }
        $totalStops = count($stops);
        $pct = $totalStops > 0 ? round(($completedCount / $totalStops) * 100) : 0;
        ?>

        <!-- Progress Overview -->
        <div class="card-body px-4 py-3 bg-white">
            <div class="d-flex justify-content-between small text-muted mb-1">
                <span>Completed Stops: <strong><?= $completedCount ?> / <?= $totalStops ?></strong></span>
                <span><?= $pct ?>% Complete</span>
            </div>
            <div class="progress" style="height: 6px;">
                <div class="progress-bar bg-success" style="width: <?= $pct ?>%;"></div>
            </div>
        </div>
    </div>

    <!-- Corridor Route Map Toggle & Section Header -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
            <i class="bi bi-signpost-split-fill text-warning"></i> Sequential Stop-by-Stop Route
        </h5>
        <button class="btn btn-outline-dark btn-sm rounded-pill px-3 shadow-sm fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#fullRunRouteCollapse" aria-expanded="false" id="toggleFullRouteBtn">
            <i class="bi bi-map-fill text-warning me-1"></i> <span id="toggleFullRouteText">Full Corridor Route Map</span>
        </button>
    </div>

    <!-- Collapsible Full Corridor Route Map -->
    <div class="collapse mb-4" id="fullRunRouteCollapse">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-dark text-white py-2 px-3 d-flex justify-content-between align-items-center">
                <span class="small fw-bold"><i class="bi bi-signpost-split-fill text-warning me-1"></i> Multi-Tiffin Milk-Run Corridor Map</span>
                <span class="badge bg-secondary"><?= count($stops) ?> Total Stops</span>
            </div>
            <div class="card-body p-0 position-relative">
                <div id="fullCorridorMapCanvas" style="height: 380px; width: 100%;"></div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-column gap-3 mb-5">
        <?php foreach ($stops as $idx => $stop): ?>
            <?php 
            $isPickup = ($stop['stop_type'] === 'pickup');
            $isCompleted = ($stop['status'] === 'completed');
            $cardBg = $isCompleted ? 'bg-white border-success' : ($isPickup ? 'bg-white border-primary' : 'bg-white border');
            ?>
            <div class="card border-2 shadow-sm rounded-4 <?= $cardBg ?> overflow-hidden" id="stop-card-<?= $stop['id'] ?>">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge <?= $isCompleted ? 'bg-success' : 'bg-dark' ?> rounded-circle p-2" style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;">
                                <?= $stop['stop_sequence'] ?>
                            </span>
                            <?php if ($isPickup): ?>
                                <span class="badge bg-primary text-white px-3 py-1 rounded-pill fw-bold">
                                    <i class="bi bi-shop me-1"></i> KITCHEN PICKUP
                                </span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark px-3 py-1 rounded-pill fw-bold">
                                    <i class="bi bi-person-fill me-1"></i> CUSTOMER DROP-OFF
                                </span>
                            <?php endif; ?>

                            <?php if ($isCompleted): ?>
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1 rounded-pill small">
                                    <i class="bi bi-check-circle-fill me-1"></i> Completed <?= !empty($stop['completed_at']) ? 'at ' . date('h:i A', strtotime($stop['completed_at'])) : '' ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($stop['items_count'])): ?>
                            <span class="badge bg-light text-dark border px-2 py-1 rounded-pill small fw-semibold">
                                <i class="bi bi-box-seam me-1"></i> <?= $stop['items_count'] ?> <?= $isPickup ? 'Boxes to Collect' : 'Tiffin Box' ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="row g-3 align-items-center">
                        <div class="col-md-7">
                            <h5 class="fw-bold mb-1 text-dark">
                                <?= sanitize($stop['target_name']) ?>
                            </h5>
                            <p class="text-muted small mb-2">
                                <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                                <?= sanitize($stop['target_address']) ?>
                            </p>
                            <?php if (!empty($stop['special_instructions'])): ?>
                                <div class="small text-secondary bg-light p-2 rounded-2 border">
                                    <i class="bi bi-info-circle me-1 text-primary"></i> <?= sanitize($stop['special_instructions']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-5 text-md-end">
                            <div class="d-flex gap-2 justify-content-md-end mb-2 align-items-center">
                                <?php if (!empty($stop['contact_phone'])): ?>
                                    <a href="tel:<?= sanitize($stop['contact_phone']) ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                                        <i class="bi bi-telephone-fill text-success me-1"></i> Call
                                    </a>
                                <?php endif; ?>

                                <!-- Inbuilt In-App Tracking Button (Replaces external map link) -->
                                <button type="button" 
                                        class="btn btn-primary btn-sm rounded-pill px-3 fw-semibold open-inbuilt-track-btn shadow-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#inbuiltTrackModal"
                                        data-stop-id="<?= $stop['id'] ?>"
                                        data-stop-sequence="<?= $stop['stop_sequence'] ?>"
                                        data-stop-type="<?= $stop['stop_type'] ?>"
                                        data-target-name="<?= htmlspecialchars($stop['target_name'], ENT_QUOTES) ?>"
                                        data-target-address="<?= htmlspecialchars($stop['target_address'], ENT_QUOTES) ?>"
                                        data-contact-phone="<?= htmlspecialchars($stop['contact_phone'] ?? '', ENT_QUOTES) ?>"
                                        data-lat="<?= (float)($stop['latitude'] ?? 0) ?>"
                                        data-lng="<?= (float)($stop['longitude'] ?? 0) ?>"
                                        data-items="<?= (int)($stop['items_count'] ?? 1) ?>"
                                        data-instructions="<?= htmlspecialchars($stop['special_instructions'] ?? '', ENT_QUOTES) ?>"
                                        data-is-completed="<?= $isCompleted ? '1' : '0' ?>"
                                        data-run-in-progress="<?= ($run['status'] === 'in_progress') ? '1' : '0' ?>"
                                        data-complete-url="<?= url('/delivery/run/' . $run['id'] . '/stop/' . $stop['id'] . '/complete') ?>">
                                    <i class="bi bi-geo-alt-fill me-1"></i> Track
                                </button>
                            </div>

                            <!-- Stop Actions -->
                            <?php if (!$isCompleted && $run['status'] === 'in_progress'): ?>
                                <div class="mt-3 pt-3 border-top text-start text-md-end">
                                    <form method="POST" action="<?= url('/delivery/run/' . $run['id'] . '/stop/' . $stop['id'] . '/complete') ?>" class="d-inline-block text-start">
                                        <?= csrf_field() ?>

                                        <?php if (!$isPickup): ?>
                                            <button type="submit" class="btn btn-success fw-bold px-4 rounded-pill shadow-sm">
                                                <i class="bi bi-check2-circle me-1"></i> Complete Drop-off (No OTP)
                                            </button>
                                        <?php else: ?>
                                            <!-- Pickup confirmation -->
                                            <button type="submit" class="btn btn-primary fw-bold px-4 rounded-pill shadow-sm">
                                                <i class="bi bi-box-arrow-in-down me-1"></i> Confirm Pickup (<?= $stop['items_count'] ?> Boxes)
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════════════ -->
<!-- INBUILT LIVE NAVIGATION MODAL (Interactive Turn-by-Turn Route Tracking)       -->
<!-- ═══════════════════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="inbuiltTrackModal" tabindex="-1" aria-labelledby="inbuiltTrackModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <!-- Modal Header -->
            <div class="modal-header bg-dark text-white p-3 border-0">
                <div class="d-flex align-items-center gap-2 flex-grow-1 overflow-hidden me-2">
                    <span id="modalStopBadge" class="badge bg-warning text-dark px-3 py-1 rounded-pill fw-bold text-nowrap">
                        Stop 1
                    </span>
                    <div class="text-truncate">
                        <h5 class="modal-title fw-bold text-truncate mb-0" id="modalTargetName">Stop Details</h5>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary bg-opacity-75 text-white px-3 py-1 rounded-pill small fw-semibold" id="modalEtaBadge">
                        <i class="bi bi-speedometer2 me-1"></i> <span id="modalEtaText">Calculating route...</span>
                    </span>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <!-- Modal Subheader info bar -->
            <div class="bg-light px-4 py-2 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="small text-muted flex-grow-1 text-truncate">
                    <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                    <span id="modalTargetAddress" class="fw-semibold text-dark"></span>
                </div>
                <div>
                    <a href="#" id="modalCallBtn" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1 d-none">
                        <i class="bi bi-telephone-fill me-1"></i> Call
                    </a>
                </div>
            </div>

            <!-- Map Container -->
            <div class="position-relative" style="background: #f1f5f9;">
                <div id="inbuiltNavMapCanvas" style="height: 420px; width: 100%;"></div>

                <!-- Floating Controls Overlay on Map -->
                <div class="position-absolute bottom-0 start-0 m-3 z-2 d-flex gap-2">
                    <button type="button" class="btn btn-light btn-sm shadow rounded-pill px-3 fw-semibold border" id="recenterRouteBtn" title="Fit both rider and stop destination">
                        <i class="bi bi-arrows-angle-contract text-primary me-1"></i> Fit Route
                    </button>
                    <button type="button" class="btn btn-light btn-sm shadow rounded-pill px-3 fw-semibold border" id="recenterDriverBtn" title="Center map on rider">
                        <i class="bi bi-crosshair text-danger me-1"></i> My Location
                    </button>
                </div>

                <!-- Loading Overlay -->
                <div id="mapLoadingOverlay" class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-white bg-opacity-75 z-3">
                    <div class="spinner-border text-primary mb-2" role="status"></div>
                    <span class="small fw-bold text-muted">Loading Inbuilt Route Navigation...</span>
                </div>
            </div>

            <!-- Special Instructions Note -->
            <div id="modalInstructionsBox" class="px-4 py-2 bg-warning bg-opacity-10 border-top border-bottom small text-dark d-none">
                <i class="bi bi-info-circle-fill text-warning me-1"></i>
                <span id="modalInstructionsText"></span>
            </div>

            <!-- Modal Footer Action -->
            <div class="modal-footer bg-light p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div id="modalStatusInfo" class="small text-muted">
                    <i class="bi bi-shield-check text-success me-1"></i> Doorstep contactless delivery &bull; No OTP required
                </div>
                
                <div id="modalActionContainer">
                    <!-- Dynamic complete button injected via JS -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Google Maps Loader scoped for delivery portal -->
<?php 
$portal = 'delivery';
include __DIR__ . '/../partials/google_maps_loader.php'; 
?>

<script>
(function() {
    'use strict';

    // Global Data
    const csrfToken = <?= json_encode(csrf_token()) ?>;
    const allStops = <?= json_encode($stops) ?>;
    const initialPartnerLat = <?= !empty($partner['current_latitude']) ? (float)$partner['current_latitude'] : 21.116415 ?>;
    const initialPartnerLng = <?= !empty($partner['current_longitude']) ? (float)$partner['current_longitude'] : 73.108070 ?>;

    let currentRiderPos = { lat: initialPartnerLat, lng: initialPartnerLng };

    // Try fetching actual browser GPS if available
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(pos) {
            if (pos && pos.coords) {
                currentRiderPos = { lat: pos.coords.latitude, lng: pos.coords.longitude };
            }
        }, function(err) {
            console.log('Using default partner location:', err ? err.message : '');
        }, { enableHighAccuracy: true, timeout: 5000 });
    }

    // Modal & Map state
    let inbuiltNavMap = null;
    let navDestinationMarker = null;
    let navRiderMarker = null;
    let navRouteLine = null;
    let activeStopData = null;

    // Full Corridor Map state
    let fullCorridorMap = null;
    let fullCorridorMarkers = [];

    // Helper: Clean text
    function cleanHtmlText(text) {
        if (!text) return '';
        var t = document.createElement('textarea');
        t.innerHTML = text;
        return t.value;
    }

    // Wire up Bootstrap Modal Events
    document.addEventListener('DOMContentLoaded', function() {
        const navModalEl = document.getElementById('inbuiltTrackModal');
        if (!navModalEl) return;

        // When modal begins opening, populate all text fields and action buttons
        navModalEl.addEventListener('show.bs.modal', function(event) {
            const btn = event.relatedTarget;
            if (!btn) return;
            populateModalFromButton(btn);
        });

        // When modal is fully visible and rendered, initialize or resize the Google Map
        navModalEl.addEventListener('shown.bs.modal', function() {
            if (!activeStopData) return;
            renderInbuiltNavMap(activeStopData);
        });

        // Direct click fallback in case data-bs-toggle is delayed
        document.querySelectorAll('.open-inbuilt-track-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                populateModalFromButton(btn);
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const m = bootstrap.Modal.getInstance(navModalEl) || new bootstrap.Modal(navModalEl);
                    m.show();
                }
            });
        });
    });

    function populateModalFromButton(btn) {
        const stopId = btn.dataset.stopId;
        const seq = btn.dataset.stopSequence;
        const type = btn.dataset.stopType;
        const name = cleanHtmlText(btn.dataset.targetName);
        const address = cleanHtmlText(btn.dataset.targetAddress);
        const phone = btn.dataset.contactPhone;
        const lat = parseFloat(btn.dataset.lat);
        const lng = parseFloat(btn.dataset.lng);
        const items = btn.dataset.items;
        const instructions = cleanHtmlText(btn.dataset.instructions);
        const isCompleted = btn.dataset.isCompleted === '1';
        const runInProgress = btn.dataset.runInProgress === '1';
        const completeUrl = btn.dataset.completeUrl;

        activeStopData = {
            id: stopId,
            sequence: seq,
            type: type,
            name: name,
            address: address,
            phone: phone,
            lat: lat,
            lng: lng,
            items: items,
            instructions: instructions,
            isCompleted: isCompleted,
            runInProgress: runInProgress,
            completeUrl: completeUrl
        };

        // 1. Badge & Titles
        const badge = document.getElementById('modalStopBadge');
        if (type === 'pickup') {
            badge.className = 'badge bg-primary text-white px-3 py-1 rounded-pill fw-bold text-nowrap';
            badge.innerHTML = `<i class="bi bi-shop me-1"></i> Stop ${seq}: Kitchen Pickup`;
        } else {
            badge.className = 'badge bg-warning text-dark px-3 py-1 rounded-pill fw-bold text-nowrap';
            badge.innerHTML = `<i class="bi bi-person-fill me-1"></i> Stop ${seq}: Customer Drop-off`;
        }

        document.getElementById('modalTargetName').textContent = name;
        document.getElementById('modalTargetAddress').textContent = address;

        // Call Button
        const callBtn = document.getElementById('modalCallBtn');
        if (phone) {
            callBtn.href = 'tel:' + phone;
            callBtn.classList.remove('d-none');
        } else {
            callBtn.classList.add('d-none');
        }

        // Special Instructions
        const instBox = document.getElementById('modalInstructionsBox');
        const instText = document.getElementById('modalInstructionsText');
        if (instructions) {
            instText.textContent = instructions;
            instBox.classList.remove('d-none');
        } else {
            instBox.classList.add('d-none');
        }

        // Action Button in Footer
        const actionContainer = document.getElementById('modalActionContainer');
        if (isCompleted) {
            actionContainer.innerHTML = `
                <span class="badge bg-success-subtle text-success border border-success px-3 py-2 rounded-pill fw-semibold">
                    <i class="bi bi-check-circle-fill me-1"></i> This Stop is Completed
                </span>
            `;
        } else if (runInProgress) {
            if (type === 'dropoff') {
                actionContainer.innerHTML = `
                    <form method="POST" action="${completeUrl}" class="d-inline">
                        <input type="hidden" name="_csrf" value="${csrfToken}">
                        <button type="submit" class="btn btn-success fw-bold px-4 rounded-pill shadow-sm">
                            <i class="bi bi-check2-circle me-1"></i> Complete Drop-off (No OTP)
                        </button>
                    </form>
                `;
            } else {
                actionContainer.innerHTML = `
                    <form method="POST" action="${completeUrl}" class="d-inline">
                        <input type="hidden" name="_csrf" value="${csrfToken}">
                        <button type="submit" class="btn btn-primary fw-bold px-4 rounded-pill shadow-sm">
                            <i class="bi bi-box-arrow-in-down me-1"></i> Confirm Pickup (${items} Boxes)
                        </button>
                    </form>
                `;
            }
        } else {
            actionContainer.innerHTML = `
                <span class="badge bg-secondary px-3 py-2 rounded-pill">Start Run to Complete</span>
            `;
        }
    }

    function renderInbuiltNavMap(stopData) {
        document.getElementById('mapLoadingOverlay').classList.remove('d-none');
        document.getElementById('modalEtaText').textContent = 'Calculating route...';

        const destPos = { lat: stopData.lat, lng: stopData.lng };

        if (typeof KravyoMaps === 'undefined') {
            document.getElementById('mapLoadingOverlay').classList.add('d-none');
            return;
        }

        KravyoMaps.init().then(function(libs) {
            document.getElementById('mapLoadingOverlay').classList.add('d-none');

            if (!inbuiltNavMap) {
                inbuiltNavMap = KravyoMaps.createMap('inbuiltNavMapCanvas', currentRiderPos.lat, currentRiderPos.lng, 14);
            }

            // Trigger Google Maps resize to ensure smooth, full rendering inside modal
            setTimeout(function() {
                if (window.google && google.maps && inbuiltNavMap) {
                    google.maps.event.trigger(inbuiltNavMap, 'resize');
                }
            }, 250);

            // Clean previous destination marker & route line
            if (navDestinationMarker && navDestinationMarker.setMap) {
                navDestinationMarker.setMap(null);
                navDestinationMarker = null;
            }
            if (navRouteLine && navRouteLine.setMap) {
                navRouteLine.setMap(null);
                navRouteLine = null;
            }

            // Create Destination Marker
            var markerType = (stopData.type === 'pickup') ? 'restaurant' : 'home';
            navDestinationMarker = KravyoMaps.addStaticMarker(inbuiltNavMap, destPos.lat, destPos.lng, stopData.name, markerType);

            // Update/Create Driver Marker
            KravyoMaps.updateDriverLocation(inbuiltNavMap, 'partner_me', currentRiderPos.lat, currentRiderPos.lng);

            // Fit map bounds to show Rider + Destination
            KravyoMaps.fitToBounds(inbuiltNavMap, [currentRiderPos, destPos], 70, 16);

            // Draw Route & calculate ETA
            var routeColor = (stopData.type === 'pickup') ? '#f59e0b' : '#2563eb';
            var haloColor = (stopData.type === 'pickup') ? '#78350f' : '#1e3a8a';

            KravyoMaps.drawRoute(inbuiltNavMap, currentRiderPos, destPos, null, routeColor, haloColor)
                .then(function(res) {
                    navRouteLine = res;
                    if (res && res.data && res.data.distance_m) {
                        var km = (res.data.distance_m / 1000).toFixed(1);
                        var mins = Math.max(1, Math.round(res.data.duration_s / 60));
                        document.getElementById('modalEtaText').textContent = `${km} km • ${mins} mins`;
                    } else {
                        var est = KravyoMaps.estimate(currentRiderPos, destPos);
                        document.getElementById('modalEtaText').textContent = `${KravyoMaps.formatDistance(est.distance_m)} • ${KravyoMaps.formatDuration(est.duration_s)}`;
                    }
                })
                .catch(function() {
                    var est = KravyoMaps.estimate(currentRiderPos, destPos);
                    document.getElementById('modalEtaText').textContent = `${KravyoMaps.formatDistance(est.distance_m)} • ${KravyoMaps.formatDuration(est.duration_s)}`;
                });
        }).catch(function(err) {
            document.getElementById('mapLoadingOverlay').classList.add('d-none');
            console.error('Inbuilt map load error:', err);
        });
    }

    // Modal Control Buttons
    const fitBtn = document.getElementById('recenterRouteBtn');
    if (fitBtn) {
        fitBtn.addEventListener('click', function() {
            if (inbuiltNavMap && activeStopData && typeof KravyoMaps !== 'undefined') {
                KravyoMaps.fitToBounds(inbuiltNavMap, [currentRiderPos, { lat: activeStopData.lat, lng: activeStopData.lng }], 70, 16);
            }
        });
    }

    const myLocBtn = document.getElementById('recenterDriverBtn');
    if (myLocBtn) {
        myLocBtn.addEventListener('click', function() {
            if (inbuiltNavMap) {
                inbuiltNavMap.setCenter(currentRiderPos);
                inbuiltNavMap.setZoom(16);
            }
        });
    }

    // ─── FULL CORRIDOR ROUTE MAP COLLAPSE ─────────────────────────────────────
    const fullRouteCollapse = document.getElementById('fullRunRouteCollapse');
    if (fullRouteCollapse) {
        fullRouteCollapse.addEventListener('shown.bs.collapse', function() {
            document.getElementById('toggleFullRouteText').textContent = 'Hide Route Corridor Map';
            renderFullCorridorMap();
        });
        fullRouteCollapse.addEventListener('hidden.bs.collapse', function() {
            document.getElementById('toggleFullRouteText').textContent = 'Full Corridor Route Map';
        });
    }

    function renderFullCorridorMap() {
        if (typeof KravyoMaps === 'undefined') return;

        KravyoMaps.init().then(function(libs) {
            if (!fullCorridorMap) {
                fullCorridorMap = KravyoMaps.createMap('fullCorridorMapCanvas', currentRiderPos.lat, currentRiderPos.lng, 13);
            }

            setTimeout(function() {
                if (window.google && google.maps && fullCorridorMap) {
                    google.maps.event.trigger(fullCorridorMap, 'resize');
                }
            }, 200);

            // Clean previous markers
            fullCorridorMarkers.forEach(function(m) { if (m.setMap) m.setMap(null); });
            fullCorridorMarkers = [];

            var waypoints = [];

            // Add Rider marker
            KravyoMaps.updateDriverLocation(fullCorridorMap, 'corridor_rider', currentRiderPos.lat, currentRiderPos.lng);
            waypoints.push(currentRiderPos);

            // Plot all stops
            allStops.forEach(function(stop) {
                if (!stop.latitude || !stop.longitude) return;
                var sPos = { lat: parseFloat(stop.latitude), lng: parseFloat(stop.longitude) };
                waypoints.push(sPos);

                var isPickup = (stop.stop_type === 'pickup');
                var mType = isPickup ? 'restaurant' : 'home';
                var label = `Stop ${stop.stop_sequence}: ${cleanHtmlText(stop.target_name)}`;
                var marker = KravyoMaps.addStaticMarker(fullCorridorMap, sPos.lat, sPos.lng, label, mType);
                if (marker) fullCorridorMarkers.push(marker);
            });

            // Fit bounds to all waypoints
            if (waypoints.length > 0) {
                KravyoMaps.fitToBounds(fullCorridorMap, waypoints, 50, 15);
            }
        });
    }
})();
</script>

