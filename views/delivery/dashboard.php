<div class="container py-4">
    <?php View::partial('flash_messages'); ?>

    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h2 class="fw-bold mb-0">Delivery Dashboard</h2>
            <p class="text-muted mb-0">Welcome back, <?= sanitize($partner['full_name']) ?></p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0 d-flex flex-wrap justify-content-md-end align-items-center gap-2">
            <a href="<?= url('/delivery/runs') ?>" class="btn btn-warning text-dark rounded-pill px-3 py-2 shadow-sm fw-bold d-inline-flex align-items-center">
                <i class="bi bi-box-seam-fill me-1"></i> Tiffin Batch Runs
            </a>
            <button type="button" class="btn btn-outline-primary rounded-pill px-3 py-2 shadow-sm fw-semibold d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#dummyLocationModal" id="openDummyLocationBtn">
                <i class="bi bi-geo-alt-fill text-danger me-1"></i> Set Dummy Location
            </button>

            <div id="dummyActiveBadge" class="d-none align-items-center bg-warning bg-opacity-10 border border-warning text-dark px-3 py-1 rounded-pill small">
                <span class="me-2"><i class="bi bi-pin-map-fill text-warning me-1"></i><strong id="dummyDistDisplay">Demo GPS Active</strong></span>
                <button type="button" class="btn-close" style="font-size: 0.65rem;" onclick="resetRealGps()" title="Reset to Real Device GPS"></button>
            </div>

            <form action="<?= url('/delivery/toggle-status') ?>" method="POST" class="d-inline mb-0">
                <?= csrf_field() ?>
                <div class="d-inline-flex align-items-center bg-white border rounded-pill px-4 py-2 shadow-sm" style="transition: all 0.3s ease;">
                    <span class="fw-bold fs-6 me-3 <?= $partner['is_online'] ? 'text-muted opacity-50' : 'text-danger' ?>" style="user-select: none;">Offline</span>
                    
                    <div class="form-check form-switch mb-0 p-0 d-flex align-items-center">
                        <input class="form-check-input m-0 shadow-none <?= $partner['is_online'] ? 'bg-success border-success' : 'bg-secondary border-secondary' ?>" 
                               type="checkbox" 
                               role="switch" 
                               id="statusToggle" 
                               <?= $partner['is_online'] ? 'checked' : '' ?> 
                               onchange="this.form.submit()" 
                               style="width: 3.5rem; height: 1.75rem; cursor: pointer; transition: all 0.3s ease; --bs-form-switch-bg: url('data:image/svg+xml,%3csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%27-4 -4 8 8%27%3e%3ccircle r=%273%27 fill=%27%23fff%27/%3e%3c/svg%3e');">
                    </div>
                    
                    <span class="fw-bold fs-6 ms-3 <?= $partner['is_online'] ? 'text-success' : 'text-muted opacity-50' ?>" style="user-select: none;">Online</span>
                </div>
            </form>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card kravyo-card border-0 h-100 bg-primary text-white">
                <div class="card-body p-4 d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="bi bi-bicycle fs-4"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="text-white-50 mb-1">Active Deliveries</h6>
                        <h3 class="fw-bold mb-0"><?= count($activeOrders) ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card kravyo-card border-0 h-100 bg-success text-white">
                <div class="card-body p-4 d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-white text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="bi bi-check2-circle fs-4"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="text-white-50 mb-1">Completed Deliveries</h6>
                        <h3 class="fw-bold mb-0"><?= $completedCount ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card kravyo-card border-0 h-100 bg-warning text-dark">
                <div class="card-body p-4 d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-dark text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="bi bi-currency-rupee fs-4"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="text-dark-50 mb-1">Total Earnings</h6>
                        <h3 class="fw-bold mb-0">₹<?= number_format($totalEarnings, 2) ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (!$partner['is_online']): ?>
        <div class="alert alert-secondary border-0 shadow-sm d-flex align-items-center" role="alert">
            <i class="bi bi-info-circle-fill text-muted fs-4 me-3"></i>
            <div>
                <strong>You are currently offline.</strong><br>
                Go online to start receiving new delivery assignments from admins.
            </div>
        </div>
    <?php endif; ?>

    <!-- Broadcast Offers Container -->
    <div id="offersContainer" class="mb-4"></div>

    <!-- Active Orders -->
    <h4 class="fw-bold mb-3 mt-4"><i class="bi bi-box-seam me-2"></i> Current Deliveries</h4>
    
    <?php if (empty($activeOrders)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center p-5">
                <i class="bi bi-bicycle text-muted opacity-25" style="font-size: 4rem;"></i>
                <h5 class="mt-3 fw-bold text-muted">No active deliveries</h5>
                <p class="text-muted mb-0">You don't have any deliveries assigned to you right now.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($activeOrders as $order): ?>
                <?php
                // Determine styling based on status
                $statusColors = [
                    'assigned' => 'primary',
                    'accepted' => 'warning',
                    'picked_up' => 'info',
                    'out_for_delivery' => 'primary'
                ];
                $color = $statusColors[$order['delivery_status']] ?? 'secondary';
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm overflow-hidden" style="border-top: 4px solid var(--bs-<?= $color ?>) !important;">
                        <div class="card-header bg-white border-bottom-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                            <span class="badge bg-<?= $color ?> rounded-pill px-3 py-2">
                                <?= ucwords(str_replace('_', ' ', $order['delivery_status'])) ?>
                            </span>
                            <small class="text-muted fw-bold">#<?= sanitize($order['order_number']) ?></small>
                        </div>
                        <div class="card-body">
                            <!-- Kitchen Details -->
                            <div class="d-flex align-items-start mb-3 border-bottom pb-3">
                                <div class="bg-light text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <i class="bi bi-shop"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-bold text-truncate" style="max-width: 200px;">
                                        Pickup: <?= sanitize($order['kitchen_name']) ?>
                                    </h6>
                                    <p class="text-muted small mb-0 lh-sm">
                                        <?= sanitize($order['kitchen_address']) ?>, <?= sanitize($order['kitchen_city']) ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Customer Details -->
                            <div class="d-flex align-items-start mb-3">
                                <div class="bg-light text-success rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <i class="bi bi-geo-alt"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-bold text-truncate" style="max-width: 200px;">
                                        Drop: <?= sanitize($order['customer_name']) ?>
                                    </h6>
                                    <p class="text-muted small mb-0 lh-sm">
                                        <?= sanitize($order['street_address']) ?><br>
                                        <?= sanitize($order['delivery_city']) ?> - <?= sanitize($order['delivery_pincode']) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top p-3 text-center">
                            <a href="<?= url('/delivery/order/' . $order['id']) ?>" class="btn btn-<?= $color ?> w-100 fw-bold">
                                View Order Details <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Dummy Location Modal for Testing Distance-Based Cascades -->
    <div class="modal fade" id="dummyLocationModal" tabindex="-1" aria-labelledby="dummyLocationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header bg-light border-bottom py-3 px-4">
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-dark" id="dummyLocationModalLabel">
                            <i class="bi bi-geo-alt-fill text-danger me-2"></i>Set Dummy Testing Location
                        </h5>
                        <small class="text-muted">
                            City: <strong><?= sanitize($partner['city'] ?? 'Surat') ?></strong> &bull; Kitchens are pinned on the map
                        </small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Target Kitchen Selector & 1-Click Presets -->
                    <div class="card bg-light border-0 mb-3 shadow-none">
                        <div class="card-body p-3">
                            <div class="row g-2 align-items-center mb-2">
                                <div class="col-md-5">
                                    <label class="form-label small fw-bold text-muted mb-1">Target Kitchen Reference:</label>
                                    <select id="targetKitchenSelect" class="form-select form-select-sm fw-semibold" onchange="onTargetKitchenChange()">
                                        <?php if (!empty($cityKitchens)): ?>
                                            <?php foreach ($cityKitchens as $k): ?>
                                                <option value="<?= $k['id'] ?>" 
                                                        data-lat="<?= $k['latitude'] ?>" 
                                                        data-lng="<?= $k['longitude'] ?>"
                                                        data-name="<?= sanitize($k['kitchen_name']) ?>">
                                                    <?= sanitize($k['kitchen_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <option value="43" data-lat="21.1702" data-lng="72.8311" data-name="Mehul Chinchangare's Kitchen">
                                                Mehul Chinchangare's Kitchen (Surat)
                                            </option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <div class="col-md-7 text-md-end">
                                    <label class="form-label small fw-bold text-muted mb-1 d-block">Quick Distance Presets:</label>
                                    <div class="btn-group btn-group-sm w-100" role="group">
                                        <button type="button" class="btn btn-outline-success fw-bold" onclick="applyPresetDistance(1.0)">
                                            🟢 ~1.0 km (Round 1)
                                        </button>
                                        <button type="button" class="btn btn-outline-warning fw-bold text-dark" onclick="applyPresetDistance(2.5)">
                                            🟡 ~2.5 km (Round 2)
                                        </button>
                                        <button type="button" class="btn btn-outline-danger fw-bold" onclick="applyPresetDistance(4.5)">
                                            🔴 ~4.5 km (Round 3)
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="text-muted small">
                                <i class="bi bi-info-circle me-1"></i>Click anywhere on the map or drag the blue rider marker to custom-position this driver.
                            </div>
                        </div>
                    </div>

                    <!-- Google Map Container -->
                    <div id="dummyMapContainer" style="height: 380px; width: 100%; border-radius: 12px; z-index: 1;" class="border shadow-sm mb-3"></div>

                    <!-- Coordinate & Live Distance Info -->
                    <div class="row g-2 align-items-center bg-white p-3 rounded-3 border">
                        <div class="col-sm-7">
                            <div class="small text-muted mb-0">Rider Coordinates:</div>
                            <div class="fw-bold font-monospace text-primary" id="riderCoordDisplay">
                                <?= !empty($partner['current_latitude']) ? number_format((float)$partner['current_latitude'], 6) : '21.170200' ?>, 
                                <?= !empty($partner['current_longitude']) ? number_format((float)$partner['current_longitude'], 6) : '72.831100' ?>
                            </div>
                        </div>
                        <div class="col-sm-5 text-sm-end">
                            <div class="small text-muted mb-0">Distance to Kitchen:</div>
                            <span class="badge bg-primary fs-6 px-3 py-2 rounded-pill shadow-sm" id="liveDistanceBadge">
                                <i class="bi bi-arrows-expand me-1"></i><span id="liveDistanceText">0.0 km</span>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-3 px-4 d-flex justify-content-between bg-light">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3" onclick="resetRealGps()">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Reset to Device GPS
                    </button>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light border rounded-pill px-3" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-success fw-bold rounded-pill px-4 shadow-sm" id="saveDummyBtn" onclick="saveDummyLocation()">
                            <i class="bi bi-check-circle-fill me-1"></i>Save Location
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($partner['is_online']): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if geolocation is supported
    if ("geolocation" in navigator) {
        console.log("Kravyo GPS Tracker: Started. You are online.");
        
        // Function to send location to server
        function updateLocation(position) {
            // Skip device GPS overwrite if dummy testing location is active
            if (sessionStorage.getItem('kravyo_dummy_location_active') === '1') {
                console.log("Kravyo GPS: Dummy testing location active. Skipping automatic device GPS overwrite.");
                return;
            }
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            const csrfToken = '<?= csrf_token() ?>';
            
            const formData = new URLSearchParams();
            formData.append('_csrf', csrfToken);
            formData.append('latitude', lat);
            formData.append('longitude', lng);
            
            fetch('<?= url('/delivery/location/update') ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    console.log(`Live GPS Synced: ${lat}, ${lng}`);
                }
            })
            .catch(error => console.error('GPS Sync Error:', error));
        }

        function handleError(error) {
            console.warn(`ERROR(${error.code}): ${error.message}`);
        }

        // Send immediately, then every 30 seconds
        navigator.geolocation.getCurrentPosition(updateLocation, handleError, {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        });

        setInterval(function() {
            navigator.geolocation.getCurrentPosition(updateLocation, handleError, {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            });
        }, 30000); // 30 seconds
        
    } else {
        console.error("Geolocation is not supported by this browser.");
    }
});
</script>
<?php endif; ?>

<?php if ($partner['is_online']): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const offersContainer = document.getElementById('offersContainer');
    if (!offersContainer) return;
    
    let activeOffers = new Map();
    let audioPlayed = false;

    function fetchOffers() {
        fetch('<?= url('/delivery/offers') ?>', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.offers) {
                renderOffers(data.offers);
            }
        })
        .catch(err => console.error(err));
    }

    function renderOffers(offers) {
        if (offers.length > 0 && !audioPlayed && typeof kravyoPlayDing === 'function') {
            kravyoPlayDing();
            audioPlayed = true;
        } else if (offers.length === 0) {
            audioPlayed = false;
        }

        let newIds = new Set(offers.map(o => o.order_id));
        
        // Remove offers that are gone
        for (let [id, el] of activeOffers.entries()) {
            if (!newIds.has(id)) {
                el.remove();
                activeOffers.delete(id);
            }
        }

        // Add or update offers
        offers.forEach(offer => {
            if (!activeOffers.has(offer.order_id)) {
                const card = createOfferCard(offer);
                offersContainer.appendChild(card);
                activeOffers.set(offer.order_id, card);
            } else {
                // Update timer
                const card = activeOffers.get(offer.order_id);
                card.querySelector('.offer-timer').textContent = offer.expires_in;
            }
        });
    }

    function createOfferCard(offer) {
        const div = document.createElement('div');
        let cardBorder = 'border-primary';
        let badgeType = '<span class="badge bg-primary text-white"><i class="bi bi-lightning-charge-fill me-1"></i>DIRECT MATCH</span>';

        if (offer.offer_type === 'batch') {
            cardBorder = 'border-info shadow-lg';
            badgeType = '<span class="badge bg-info text-dark fw-bold"><i class="bi bi-layers-fill me-1"></i>BATCH MATCH (+Bonus)</span>';
        } else if (offer.offer_type === 'broadcast') {
            cardBorder = 'border-danger shadow';
            badgeType = '<span class="badge bg-danger text-white fw-bold"><i class="bi bi-broadcast me-1"></i>PRIORITY BROADCAST</span>';
        } else {
            cardBorder = 'border-primary shadow';
            badgeType = `<span class="badge bg-primary text-white"><i class="bi bi-lightning-charge-fill me-1"></i>DIRECT MATCH (Round ${offer.round || 1})</span>`;
        }

        div.className = `card ${cardBorder} mb-3`;
        div.dataset.orderId = offer.order_id;
        div.dataset.secondsLeft = offer.expires_in;
        div.innerHTML = `
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="d-flex align-items-center gap-2">
                        ${badgeType}
                    </div>
                    <span class="badge bg-danger rounded-pill"><span class="offer-timer">${offer.expires_in}</span>s left</span>
                </div>
                <h6 class="fw-bold mb-1"><i class="bi bi-shop me-1 text-primary"></i>${offer.kitchen_name}</h6>
                <p class="text-muted small mb-2"><i class="bi bi-geo-alt"></i> ${offer.kitchen_address}</p>
                <div class="alert alert-light border py-2 px-3 mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-house-door-fill text-success"></i>
                    <div>
                        <small class="d-block fw-bold text-muted" style="font-size:0.75rem;">DELIVER TO</small>
                        <span class="fw-semibold text-dark">${offer.customer_address}</span>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted d-block" style="font-size:0.75rem;">EARNING / TOTAL</small>
                        <div class="fs-4 fw-bold text-success">₹${parseFloat(offer.total_amount).toFixed(2)}</div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-secondary px-3 rounded-pill skip-btn">Skip</button>
                        <button class="btn btn-success px-4 rounded-pill fw-bold accept-btn shadow">ACCEPT</button>
                    </div>
                </div>
            </div>
        `;

        div.querySelector('.skip-btn').addEventListener('click', () => {
            const csrfToken = '<?= csrf_token() ?>';
            fetch('<?= url('/delivery/offers/decline') ?>', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: '_csrf=' + encodeURIComponent(csrfToken) + '&order_id=' + encodeURIComponent(offer.order_id)
            }).then(() => {
                div.remove();
                activeOffers.delete(offer.order_id);
            }).catch(err => console.error(err));
        });

        const acceptBtn = div.querySelector('.accept-btn');
        acceptBtn.addEventListener('click', () => {
            acceptBtn.disabled = true;
            acceptBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Accepting...';

            const csrfToken = '<?= csrf_token() ?>';
            fetch('<?= url('/delivery/offers/accept') ?>', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: '_csrf=' + encodeURIComponent(csrfToken) + '&order_id=' + encodeURIComponent(offer.order_id)
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    window.location.reload();
                } else {
                    div.innerHTML = `<div class="card-body text-center py-4"><h5 class="text-danger fw-bold"><i class="bi bi-emoji-frown"></i> ${data.message}</h5></div>`;
                    setTimeout(() => { div.remove(); activeOffers.delete(offer.order_id); }, 3000);
                }
            })
            .catch(err => {
                console.error(err);
                acceptBtn.disabled = false;
                acceptBtn.innerHTML = 'ACCEPT';
                alert('Could not accept offer. Please check connection and try again.');
            });
        });

        return div;
    }

    // Local 1-second countdown ticker for smooth real-time timer experience
    setInterval(function() {
        for (let [id, card] of activeOffers.entries()) {
            let left = parseInt(card.dataset.secondsLeft || '0', 10);
            if (left > 0) {
                left--;
                card.dataset.secondsLeft = left;
                const timerEl = card.querySelector('.offer-timer');
                if (timerEl) timerEl.textContent = left;
            } else {
                card.remove();
                activeOffers.delete(id);
            }
        }
    }, 1000);

    setInterval(fetchOffers, 4000);
    fetchOffers();
});
</script>
<?php endif; ?>

<!-- Google Maps Loader for Dummy Location Testing -->
<?php include __DIR__ . '/../partials/google_maps_loader.php'; ?>

<script>
let dummyMap = null;
let riderMarker = null;
let distanceLine = null;
let currentRiderLat = <?= !empty($partner['current_latitude']) ? (float)$partner['current_latitude'] : 21.1702 ?>;
let currentRiderLng = <?= !empty($partner['current_longitude']) ? (float)$partner['current_longitude'] : 72.8311 ?>;
let targetKitchenLat = 21.1702;
let targetKitchenLng = 72.8311;
let kitchenMarkers = [];

const cityKitchensData = <?= json_encode($cityKitchens ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

function getHaversineKm(lat1, lon1, lat2, lon2) {
    const R = 6371;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return parseFloat((R * c).toFixed(2));
}

function updateDistanceDisplay() {
    const dist = getHaversineKm(currentRiderLat, currentRiderLng, targetKitchenLat, targetKitchenLng);
    
    const coordEl = document.getElementById('riderCoordDisplay');
    if (coordEl) coordEl.textContent = `${currentRiderLat.toFixed(6)}, ${currentRiderLng.toFixed(6)}`;

    const distEl = document.getElementById('liveDistanceText');
    if (distEl) distEl.textContent = `${dist} km`;

    const badge = document.getElementById('liveDistanceBadge');
    if (badge) {
        badge.className = 'badge fs-6 px-3 py-2 rounded-pill shadow-sm ' + 
            (dist <= 1.5 ? 'bg-success' : (dist <= 3.0 ? 'bg-warning text-dark' : 'bg-danger'));
    }

    if (dummyMap && typeof KravyoMaps !== 'undefined') {
        KravyoMaps.init().then(function(libs) {
            if (distanceLine) {
                distanceLine.setMap(null);
            }
            distanceLine = new libs.Polyline({
                path: [
                    { lat: targetKitchenLat, lng: targetKitchenLng },
                    { lat: currentRiderLat, lng: currentRiderLng }
                ],
                geodesic: true,
                strokeColor: '#0d6efd',
                strokeOpacity: 0.85,
                strokeWeight: 3,
                map: dummyMap
            });
        }).catch(console.error);
    }
}

function onTargetKitchenChange() {
    const select = document.getElementById('targetKitchenSelect');
    if (!select) return;
    const opt = select.options[select.selectedIndex];
    if (opt) {
        targetKitchenLat = parseFloat(opt.dataset.lat || '21.1702');
        targetKitchenLng = parseFloat(opt.dataset.lng || '72.8311');
        updateDistanceDisplay();
    }
}

function applyPresetDistance(targetKm) {
    onTargetKitchenChange();
    
    // In Surat (lat ~21), 1 km is ~0.0090 degrees latitude
    const latOffset = (targetKm / 111.0);
    currentRiderLat = parseFloat((targetKitchenLat + latOffset).toFixed(6));
    currentRiderLng = parseFloat(targetKitchenLng.toFixed(6));

    if (riderMarker) {
        riderMarker.position = { lat: currentRiderLat, lng: currentRiderLng };
    }
    if (dummyMap) {
        dummyMap.panTo({ lat: currentRiderLat, lng: currentRiderLng });
    }
    updateDistanceDisplay();
}

function initDummyMap() {
    if (dummyMap) return;

    const select = document.getElementById('targetKitchenSelect');
    if (select && select.options.length > 0) {
        const opt = select.options[0];
        targetKitchenLat = parseFloat(opt.dataset.lat || '21.1702');
        targetKitchenLng = parseFloat(opt.dataset.lng || '72.8311');
    }

    if (typeof KravyoMaps === 'undefined') {
        console.warn('KravyoMaps is not loaded.');
        return;
    }

    KravyoMaps.init().then(function(libs) {
        dummyMap = KravyoMaps.createMap('dummyMapContainer', targetKitchenLat, targetKitchenLng, 13);
        if (!dummyMap) return;

        // Add kitchen markers
        if (cityKitchensData && cityKitchensData.length > 0) {
            cityKitchensData.forEach(function(k) {
                const kLat = parseFloat(k.latitude);
                const kLng = parseFloat(k.longitude);
                if (!isNaN(kLat) && !isNaN(kLng)) {
                    const km = KravyoMaps.addStaticMarker(dummyMap, kLat, kLng, k.kitchen_name, 'restaurant');
                    kitchenMarkers.push(km);
                }
            });
        } else {
            KravyoMaps.addStaticMarker(dummyMap, targetKitchenLat, targetKitchenLng, "Mehul's Kitchen", 'restaurant');
        }

        // Initial rider position
        const savedLat = sessionStorage.getItem('kravyo_dummy_lat');
        const savedLng = sessionStorage.getItem('kravyo_dummy_lng');
        if (savedLat && savedLng) {
            currentRiderLat = parseFloat(savedLat);
            currentRiderLng = parseFloat(savedLng);
        } else if (currentRiderLat === 0.0 || (currentRiderLat === 21.1702 && currentRiderLng === 72.8311)) {
            // Default offset ~1.2 km from kitchen
            currentRiderLat = targetKitchenLat + 0.0108;
            currentRiderLng = targetKitchenLng + 0.0020;
        }

        // Add draggable rider marker using KravyoMaps
        riderMarker = KravyoMaps.addDraggableMarker(dummyMap, currentRiderLat, currentRiderLng, function(lat, lng) {
            currentRiderLat = parseFloat(lat.toFixed(6));
            currentRiderLng = parseFloat(lng.toFixed(6));
            updateDistanceDisplay();
        });

        // Allow clicking anywhere on Google Map to place rider marker
        dummyMap.addListener('click', function(e) {
            if (!e.latLng) return;
            currentRiderLat = parseFloat(e.latLng.lat().toFixed(6));
            currentRiderLng = parseFloat(e.latLng.lng().toFixed(6));
            if (riderMarker) {
                riderMarker.position = { lat: currentRiderLat, lng: currentRiderLng };
            }
            updateDistanceDisplay();
        });

        updateDistanceDisplay();
    }).catch(function(err) {
        console.error('Google Maps init failed:', err);
    });
}

function saveDummyLocation() {
    const saveBtn = document.getElementById('saveDummyBtn');
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';

    const csrfToken = '<?= csrf_token() ?>';
    const distText = document.getElementById('liveDistanceText').textContent;

    fetch('<?= url('/delivery/set-dummy-location') ?>', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: '_csrf=' + encodeURIComponent(csrfToken) + 
              '&latitude=' + encodeURIComponent(currentRiderLat) + 
              '&longitude=' + encodeURIComponent(currentRiderLng)
    })
    .then(res => res.json())
    .then(data => {
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i>Save Location';
        if (data.success) {
            sessionStorage.setItem('kravyo_dummy_location_active', '1');
            sessionStorage.setItem('kravyo_dummy_lat', currentRiderLat);
            sessionStorage.setItem('kravyo_dummy_lng', currentRiderLng);
            sessionStorage.setItem('kravyo_dummy_dist', `Demo: ${distText} away`);
            
            updateBadgeDisplay();

            const modalEl = document.getElementById('dummyLocationModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
        } else {
            alert(data.message || 'Error saving location');
        }
    })
    .catch(err => {
        console.error(err);
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i>Save Location';
        alert('Could not save dummy location. Please try again.');
    });
}

function resetRealGps() {
    if (!confirm('Reset location back to your real device GPS?')) return;
    
    sessionStorage.removeItem('kravyo_dummy_location_active');
    sessionStorage.removeItem('kravyo_dummy_lat');
    sessionStorage.removeItem('kravyo_dummy_lng');
    sessionStorage.removeItem('kravyo_dummy_dist');

    const csrfToken = '<?= csrf_token() ?>';
    fetch('<?= url('/delivery/reset-location') ?>', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: '_csrf=' + encodeURIComponent(csrfToken)
    }).then(() => {
        updateBadgeDisplay();
        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(function(pos) {
                currentRiderLat = pos.coords.latitude;
                currentRiderLng = pos.coords.longitude;
                if (riderMarker) {
                    riderMarker.position = { lat: currentRiderLat, lng: currentRiderLng };
                }
                updateDistanceDisplay();
            });
        }
    });

    const modalEl = document.getElementById('dummyLocationModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) modal.hide();
}

function updateBadgeDisplay() {
    const badge = document.getElementById('dummyActiveBadge');
    if (!badge) return;
    if (sessionStorage.getItem('kravyo_dummy_location_active') === '1') {
        const dist = sessionStorage.getItem('kravyo_dummy_dist') || 'Demo Location Active';
        const distEl = document.getElementById('dummyDistDisplay');
        if (distEl) distEl.textContent = dist;
        badge.classList.remove('d-none');
        badge.classList.add('d-inline-flex');
    } else {
        badge.classList.add('d-none');
        badge.classList.remove('d-inline-flex');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    updateBadgeDisplay();

    const modalEl = document.getElementById('dummyLocationModal');
    if (modalEl) {
        modalEl.addEventListener('shown.bs.modal', function () {
            if (dummyMap) {
                if (window.google && google.maps && google.maps.event) {
                    google.maps.event.trigger(dummyMap, 'resize');
                }
                dummyMap.setCenter({ lat: currentRiderLat, lng: currentRiderLng });
            } else {
                initDummyMap();
            }
        });
    }
});
</script>
