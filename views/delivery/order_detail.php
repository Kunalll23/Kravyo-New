<div class="container py-4">
    <div class="mb-3">
        <a href="<?= url('/delivery/dashboard') ?>" class="text-decoration-none text-muted">
            <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>

    <?php View::partial('flash_messages'); ?>

    <div class="row g-4">
        <!-- Order Overview & Actions -->
        <div class="col-lg-8">
            <div class="card kravyo-card border-0 mb-4">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                    <h4 class="fw-bold mb-0">Order #<?= sanitize($order['order_number']) ?></h4>
                    <span class="badge bg-primary rounded-pill px-3 py-2 fs-6">
                        <?= ucwords(str_replace('_', ' ', $order['delivery_status'])) ?>
                    </span>
                </div>
                <div class="card-body p-4">
                    
                    <!-- Action Workflow -->
                    <div class="bg-light p-4 rounded-3 border mb-4 text-center">
                        <h5 class="fw-bold mb-3">Delivery Action Required</h5>
                        
                        <?php if ($order['delivery_status'] === 'assigned'): ?>
                            <p class="text-muted">You have been assigned this order. Please accept it to proceed.</p>
                            <form action="<?= url('/delivery/order/' . $order['id'] . '/accept') ?>" method="POST">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-success btn-lg px-5 fw-bold shadow-sm">
                                    <i class="bi bi-check-circle me-2"></i> Accept Order
                                </button>
                            </form>

                        <?php elseif ($order['delivery_status'] === 'accepted'): ?>
                            <p class="text-muted">Head to the kitchen to pick up the order.</p>
                            <form action="<?= url('/delivery/order/' . $order['id'] . '/pickup') ?>" method="POST">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-primary btn-lg px-5 fw-bold shadow-sm">
                                    <i class="bi bi-box-seam me-2"></i> Mark as Picked Up
                                </button>
                            </form>

                        <?php elseif ($order['delivery_status'] === 'picked_up'): ?>
                            <p class="text-muted">You have the food. Start your journey to the customer.</p>
                            <form action="<?= url('/delivery/order/' . $order['id'] . '/out-for-delivery') ?>" method="POST">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-warning btn-lg px-5 fw-bold shadow-sm">
                                    <i class="bi bi-bicycle me-2"></i> Start Delivery
                                </button>
                            </form>

                        <?php elseif ($order['delivery_status'] === 'out_for_delivery'): ?>
                            <p class="text-muted">You are at the customer location. Ask them for the 4-digit Delivery OTP.</p>
                            
                            <form action="<?= url('/delivery/order/' . $order['id'] . '/complete') ?>" method="POST" class="mx-auto" style="max-width: 300px;">
                                <?= csrf_field() ?>
                                <div class="mb-3">
                                    <input type="text" class="form-control form-control-lg text-center fw-bold fs-4" 
                                           name="delivery_otp" placeholder="Enter OTP" 
                                           maxlength="4" pattern="[0-9]{4}" required autocomplete="off">
                                </div>
                                <button type="submit" class="btn btn-success w-100 btn-lg fw-bold shadow-sm">
                                    <i class="bi bi-check2-circle me-2"></i> Verify & Complete
                                </button>
                            </form>

                            <hr class="my-4">
                            
                            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#failedModal">
                                <i class="bi bi-exclamation-triangle me-1"></i> Customer Unavailable / Report Issue
                            </button>

                        <?php else: ?>
                            <p class="text-muted mb-0">
                                This order is marked as <strong><?= ucwords(str_replace('_', ' ', $order['delivery_status'])) ?></strong>. No further actions required.
                            </p>
                        <?php endif; ?>
                    </div>

                    <?php if (in_array($order['delivery_status'], ['accepted', 'picked_up', 'out_for_delivery'])): ?>
                    <!-- Live GPS Tracking Widget -->
                    <div class="bg-light p-4 rounded-3 border mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0"><i class="bi bi-geo-alt-fill text-danger me-2"></i>Live Location</h5>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="gpsToggle" checked>
                                <label class="form-check-label fw-bold text-success" id="gpsStatusText">Sharing GPS</label>
                            </div>
                        </div>
                        <p class="small text-muted mb-2">Keep this page open to share your live location with the customer.</p>
                        <div id="gpsError" class="alert alert-danger d-none small py-2 mb-0"></div>
                        <div id="gpsSuccess" class="alert alert-success d-none small py-2 mb-0"><i class="bi bi-broadcast"></i> Location updating...</div>
                        
                        <!-- Google Maps integration -->
                        <?php 
                        $portal = 'delivery';
                        include __DIR__ . '/../partials/google_maps_loader.php'; 
                        ?>
                        
                        <div id="dpNavMap" class="mt-3 d-none" style="height: 380px; border-radius: 8px; z-index: 1;"></div>
                    </div>

                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        let watchId = null;
                        const gpsToggle = document.getElementById('gpsToggle');
                        const gpsStatusText = document.getElementById('gpsStatusText');
                        const gpsError = document.getElementById('gpsError');
                        const gpsSuccess = document.getElementById('gpsSuccess');
                        const dpNavMapContainer = document.getElementById('dpNavMap');
                        const updateUrl = '<?= url('/delivery/location/update') ?>';
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                        // Google Maps Init
                        let map = null;
                        let destinationLatLng = null;

                        // Phase Setup
                        const isPickedUp = <?= in_array($order['delivery_status'], ['picked_up', 'out_for_delivery']) ? 'true' : 'false' ?>;
                        const kitchenLat = <?= !empty($order['kitchen_lat']) ? (float)$order['kitchen_lat'] : (defined('GOOGLE_MAPS_DEFAULT_LAT') ? (float)GOOGLE_MAPS_DEFAULT_LAT : 21.1702) ?>;
                        const kitchenLng = <?= !empty($order['kitchen_lng']) ? (float)$order['kitchen_lng'] : (defined('GOOGLE_MAPS_DEFAULT_LNG') ? (float)GOOGLE_MAPS_DEFAULT_LNG : 72.8311) ?>;
                        
                        <?php if(!empty($order['delivery_latitude']) && !empty($order['delivery_longitude'])): ?>
                            destinationLatLng = { lat: <?= (float)$order['delivery_latitude'] ?>, lng: <?= (float)$order['delivery_longitude'] ?> };
                        <?php else: ?>
                            const fullAddress = "<?= sanitize(trim(($order['street_address'] ?? '') . ', ' . ($order['delivery_city'] ?? '') . ', ' . ($order['delivery_pincode'] ?? ''))) ?>";
                        <?php endif; ?>

                        function initMapAndRouting(startLat, startLng) {
                            dpNavMapContainer.classList.remove('d-none');
                            
                            if (typeof KravyoMaps === 'undefined') return;

                            KravyoMaps.init().then(() => {
                                updateMapRoute(startLat, startLng);

                                if (!destinationLatLng && typeof fullAddress !== 'undefined' && fullAddress) {
                                    KravyoMaps.geocode(fullAddress).then(res => {
                                        if (res && res.lat && res.lng) {
                                            destinationLatLng = { lat: res.lat, lng: res.lng };
                                            if (map && !window.customerMarker) {
                                                window.customerMarker = KravyoMaps.addStaticMarker(map, destinationLatLng.lat, destinationLatLng.lng, 'Customer', 'home');
                                            }
                                            updateMapRoute(startLat, startLng);
                                        }
                                    }).catch(err => {
                                        console.warn("Geocoding customer address failed:", err);
                                    });
                                }
                            });
                        }

                        function updateMapRoute(startLat, startLng) {
                            if (!map) {
                                map = KravyoMaps.createMap('dpNavMap', startLat, startLng, 14);

                                // Add Kitchen Marker
                                if (!window.kitchenMarker) {
                                    window.kitchenMarker = KravyoMaps.addStaticMarker(map, kitchenLat, kitchenLng, 'Pickup (Kitchen)', 'restaurant');
                                }
                            }

                            // Add Customer Marker whenever destination is resolved
                            if (destinationLatLng && !window.customerMarker && map) {
                                window.customerMarker = KravyoMaps.addStaticMarker(map, destinationLatLng.lat, destinationLatLng.lng, 'Customer', 'home');
                            }

                            // Update Partner Marker
                            KravyoMaps.updateDriverLocation(map, 'partner_me', startLat, startLng);

                            // Fit bounds to include all active markers
                            let pointsToFit = [{lat: startLat, lng: startLng}, {lat: kitchenLat, lng: kitchenLng}];
                            if (destinationLatLng) {
                                pointsToFit.push(destinationLatLng);
                            }
                            KravyoMaps.fitToBounds(map, pointsToFit, 50, 16);

                            // Calculate Routing
                            const newPoint = { lat: startLat, lng: startLng };
                            let shouldRoute = false;
                            
                            if (!window.lastRoutedPoint || window.lastRoutePhase !== isPickedUp || (destinationLatLng && !window.destinationRouted)) {
                                shouldRoute = true;
                            } else {
                                const prevPt = window.lastRoutedPoint;
                                const R = 6371e3;
                                const φ1 = prevPt.lat * Math.PI/180;
                                const φ2 = newPoint.lat * Math.PI/180;
                                const Δφ = (newPoint.lat - prevPt.lat) * Math.PI/180;
                                const Δλ = (newPoint.lng - prevPt.lng) * Math.PI/180;
                                const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) + Math.cos(φ1) * Math.cos(φ2) * Math.sin(Δλ/2) * Math.sin(Δλ/2);
                                const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                                const d = R * c;
                                
                                if (d > 40) { // 40m
                                    shouldRoute = true;
                                }
                            }

                            if (shouldRoute) {
                                let origin = newPoint;

                                if (!isPickedUp) {
                                    // Leg 1: Rider -> Kitchen (Red)
                                    KravyoMaps.drawRoute(map, origin, {lat: kitchenLat, lng: kitchenLng}, null, '#ef4444').then(r => {
                                        if (window.routeRenderer1 && window.routeRenderer1 !== r) window.routeRenderer1.setMap(null);
                                        window.routeRenderer1 = r;
                                    }).catch(e => console.error('Leg 1 route error:', e));

                                    // Leg 2 Preview: Kitchen -> Customer (Blue)
                                    if (destinationLatLng) {
                                        KravyoMaps.drawRoute(map, {lat: kitchenLat, lng: kitchenLng}, destinationLatLng, null, '#2563eb').then(r => {
                                            if (window.routeRenderer2 && window.routeRenderer2 !== r) window.routeRenderer2.setMap(null);
                                            window.routeRenderer2 = r;
                                            window.destinationRouted = true;
                                        }).catch(e => console.error('Leg 2 route error:', e));
                                    }
                                } else {
                                    // Food picked up: Direct route Rider -> Customer
                                    if (window.routeRenderer1) {
                                        window.routeRenderer1.setMap(null);
                                        window.routeRenderer1 = null;
                                    }
                                    if (destinationLatLng) {
                                        KravyoMaps.drawRoute(map, origin, destinationLatLng, null, '#2563eb').then(r => {
                                            if (window.routeRenderer2 && window.routeRenderer2 !== r) window.routeRenderer2.setMap(null);
                                            window.routeRenderer2 = r;
                                            window.destinationRouted = true;
                                        }).catch(e => console.error('Rider to customer route error:', e));
                                    }
                                }

                                window.lastRoutedPoint = newPoint;
                                window.lastRoutePhase = isPickedUp;
                            }
                        }

                        function startWatching() {
                            // Immediate render with recorded rider location or kitchen so map is instantly visible
                            const initialLat = <?= !empty($order['delivery_partner_lat']) ? (float)$order['delivery_partner_lat'] : (!empty($order['kitchen_lat']) ? (float)$order['kitchen_lat'] : 21.1702) ?>;
                            const initialLng = <?= !empty($order['delivery_partner_lng']) ? (float)$order['delivery_partner_lng'] : (!empty($order['kitchen_lng']) ? (float)$order['kitchen_lng'] : 72.8311) ?>;
                            initMapAndRouting(initialLat, initialLng);

                            if (!navigator.geolocation) {
                                showError('Geolocation is not supported by your browser.');
                                return;
                            }

                            gpsError.classList.add('d-none');
                            gpsSuccess.classList.remove('d-none');
                            gpsStatusText.textContent = 'Sharing GPS';
                            gpsStatusText.className = 'form-check-label fw-bold text-success';

                            watchId = navigator.geolocation.watchPosition(
                                (position) => {
                                    const lat = position.coords.latitude;
                                    const lng = position.coords.longitude;
                                    
                                    // Update Map
                                    initMapAndRouting(lat, lng);

                                    // Send to server
                                    const formData = new FormData();
                                    formData.append('latitude', lat);
                                    formData.append('longitude', lng);
                                    
                                    // Use form CSRF if meta not available
                                    const formCsrf = document.querySelector('input[name="csrf_token"]') || document.querySelector('input[name="_csrf"]');
                                    if (formCsrf) formData.append('_csrf', formCsrf.value);
                                    else formData.append('_csrf', csrfToken);

                                    fetch(updateUrl, {
                                        method: 'POST',
                                        body: formData
                                    }).catch(err => console.error('GPS Update Error:', err));
                                },
                                (error) => {
                                    let msg = 'Failed to get location.';
                                    if (error.code === error.PERMISSION_DENIED) msg = 'Location permission denied. Please enable GPS.';
                                    showError(msg);
                                    stopWatching();
                                    gpsToggle.checked = false;
                                },
                                { enableHighAccuracy: true, maximumAge: 10000, timeout: 10000 }
                            );
                        }

                        function stopWatching() {
                            if (watchId !== null) {
                                navigator.geolocation.clearWatch(watchId);
                                watchId = null;
                            }
                            gpsSuccess.classList.add('d-none');
                            gpsStatusText.textContent = 'GPS Stopped';
                            gpsStatusText.className = 'form-check-label fw-bold text-secondary';
                        }

                        function showError(msg) {
                            gpsError.textContent = msg;
                            gpsError.classList.remove('d-none');
                            gpsSuccess.classList.add('d-none');
                        }

                        gpsToggle.addEventListener('change', (e) => {
                            if (e.target.checked) startWatching();
                            else stopWatching();
                        });

                        // Start automatically if out for delivery
                        startWatching();
                    });
                    </script>
                    <?php endif; ?>

                    <!-- Items List -->
                    <h5 class="fw-bold mb-3">Order Items</h5>
                    <ul class="list-group list-group-flush mb-4">
                        <?php foreach ($orderItems as $item): ?>
                            <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-<?= $item['is_veg'] ? 'success' : 'danger' ?> rounded-circle p-1 me-2 border border-white" style="width: 12px; height: 12px; display: inline-block;"></span>
                                    <span class="fw-semibold"><?= sanitize($item['item_name']) ?></span>
                                    <span class="text-muted ms-1">x<?= $item['quantity'] ?></span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <div class="d-flex justify-content-between border-top pt-3 fw-bold">
                        <span>Payment Method</span>
                        <span class="text-uppercase text-<?= $order['payment_method'] === 'cod' ? 'danger' : 'success' ?>">
                            <?= sanitize($order['payment_method']) ?>
                        </span>
                    </div>
                    <?php if ($order['payment_method'] === 'cod' && $order['delivery_status'] !== 'delivered'): ?>
                        <div class="alert alert-danger mt-3 mb-0 text-center fw-bold py-2">
                            <i class="bi bi-cash-coin me-1"></i> Collect ₹<?= number_format($order['total_amount'], 2) ?> Cash!
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>

        <!-- Locations Sidebar -->
        <div class="col-lg-4">
            <!-- Pickup Card -->
            <div class="card kravyo-card border-0 mb-4">
                <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                    <h6 class="fw-bold text-muted mb-0 text-uppercase" style="letter-spacing: 1px;"><i class="bi bi-shop me-1 text-primary"></i> Pickup Location</h6>
                </div>
                <div class="card-body">
                    <h5 class="fw-bold mb-1"><?= sanitize($order['kitchen_name']) ?></h5>
                    <p class="text-muted small mb-3">
                        <?= sanitize($order['kitchen_address']) ?><br>
                        <?= sanitize($order['kitchen_city']) ?>
                    </p>
                    <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($order['kitchen_lat'] . ',' . $order['kitchen_lng']) ?>" 
                       target="_blank" class="btn btn-outline-primary btn-sm w-100 fw-bold">
                       <i class="bi bi-geo-alt me-1"></i> Open in Maps
                    </a>
                </div>
            </div>

            <!-- Dropoff Card -->
            <div class="card kravyo-card border-0">
                <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                    <h6 class="fw-bold text-muted mb-0 text-uppercase" style="letter-spacing: 1px;"><i class="bi bi-geo-alt-fill me-1 text-success"></i> Dropoff Location</h6>
                </div>
                <div class="card-body">
                    <h5 class="fw-bold mb-1"><?= sanitize($order['customer_name']) ?></h5>
                    <p class="mb-2"><a href="tel:<?= sanitize($order['customer_phone']) ?>" class="text-decoration-none fw-semibold"><i class="bi bi-telephone-fill me-1"></i> <?= sanitize($order['customer_phone']) ?></a></p>
                    <p class="text-muted small mb-3">
                        <?= sanitize($order['street_address']) ?><br>
                        <?php if ($order['landmark']): ?>
                            <span class="text-dark">Landmark: <?= sanitize($order['landmark']) ?></span><br>
                        <?php endif; ?>
                        <?= sanitize($order['delivery_city']) ?> - <?= sanitize($order['delivery_pincode']) ?>
                    </p>
                    <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($order['street_address'] . ', ' . $order['delivery_city']) ?>" 
                       target="_blank" class="btn btn-outline-success btn-sm w-100 fw-bold">
                       <i class="bi bi-geo-alt me-1"></i> Open in Maps
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Failed Delivery Modal -->
<div class="modal fade" id="failedModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?= url('/delivery/order/' . $order['id'] . '/failed') ?>" method="POST" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i> Report Issue</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">Only use this if you are at the customer location and cannot complete the delivery.</p>
                <div class="mb-3">
                    <label for="fail_reason" class="form-label fw-semibold">Reason</label>
                    <select name="fail_reason" id="fail_reason" class="form-select" required>
                        <option value="Customer unavailable/Not picking up phone">Customer unavailable / Not picking up phone</option>
                        <option value="Customer rejected order at door">Customer rejected order at door</option>
                        <option value="Incorrect address provided">Incorrect address provided</option>
                        <option value="Vehicle breakdown / Accident">Vehicle breakdown / Accident</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger fw-bold">Mark as Failed</button>
            </div>
        </form>
    </div>
</div>
