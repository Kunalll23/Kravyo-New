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
                            
                            <?php if ($order['payment_method'] === 'cod' && $order['payment_status'] !== 'completed'): ?>
                                <div class="card border-warning bg-warning bg-opacity-10 mb-3 mx-auto text-start shadow-sm" style="max-width: 360px;">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="badge bg-warning text-dark fw-bold"><i class="bi bi-cash-stack me-1"></i> Cash Pending</span>
                                            <span class="fw-bold text-dark fs-5"><?= format_currency($order['total_amount']) ?></span>
                                        </div>
                                        <p class="small text-muted mb-2">Customer wants to pay full or split via UPI QR?</p>
                                        <button type="button" class="btn btn-dark btn-sm w-100 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#doorstepUpiModal">
                                            <i class="bi bi-qr-code-scan me-1 text-warning"></i> Show Customer UPI QR (Full or Split)
                                        </button>
                                    </div>
                                </div>
                            <?php elseif ($order['payment_method'] === 'split' && $order['payment_status'] === 'completed'): ?>
                                <div class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill mb-3">
                                    <i class="bi bi-pie-chart-fill me-1"></i> Split Payment Settled (Cash: <?= format_currency($order['paid_cash_amount']) ?> + UPI: <?= format_currency($order['paid_online_amount']) ?>)
                                </div>
                            <?php elseif ($order['payment_method'] === 'upi' || $order['payment_status'] === 'completed'): ?>
                                <div class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill mb-3">
                                    <i class="bi bi-check-circle-fill me-1"></i> Payment Completed (<?= strtoupper($order['payment_method']) ?>) — No Cash Needed
                                </div>
                            <?php endif; ?>

                            <form action="<?= url('/delivery/order/' . $order['id'] . '/complete') ?>" method="POST" class="mx-auto" style="max-width: 300px;" data-once>
                                <?= csrf_field() ?>
                                <div class="mb-3">
                                    <input type="text" class="form-control form-control-lg text-center fw-bold fs-4" 
                                           name="delivery_otp" placeholder="Enter OTP" 
                                           maxlength="4" pattern="[0-9]{4}" required autocomplete="off">
                                </div>
                                <button type="submit" class="btn btn-success w-100 btn-lg fw-bold shadow-sm" data-loading-text="Verifying OTP...">
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
                        
                        <!-- Route Summary & Legend Bar -->
                        <div class="row g-2 mt-2 mb-2" id="routeLegendBar">
                            <div class="col-sm-6">
                                <div class="p-2 rounded-3 border bg-white d-flex align-items-center justify-content-between shadow-sm">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge rounded-circle p-2" style="background:#f59e0b; width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center;">
                                            <i class="bi bi-shop text-white" style="font-size:0.85rem;"></i>
                                        </span>
                                        <div>
                                            <div class="fw-bold small text-dark">Leg 1: Rider ➔ Kitchen</div>
                                            <small class="text-muted" id="leg1Details">Pickup at <?= sanitize($order['kitchen_name']) ?></small>
                                        </div>
                                    </div>
                                    <span class="badge bg-warning text-dark px-2 py-1" id="leg1Status">
                                        <?= in_array($order['delivery_status'], ['picked_up', 'out_for_delivery']) ? '✅ Picked Up' : 'To Kitchen' ?>
                                    </span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-2 rounded-3 border bg-white d-flex align-items-center justify-content-between shadow-sm">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge rounded-circle p-2" style="background:#2563eb; width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center;">
                                            <i class="bi bi-house-door-fill text-white" style="font-size:0.85rem;"></i>
                                        </span>
                                        <div>
                                            <div class="fw-bold small text-dark">Leg 2: Kitchen ➔ Customer</div>
                                            <small class="text-muted" id="leg2Details">Deliver to <?= sanitize($order['customer_name'] ?? 'Customer') ?></small>
                                        </div>
                                    </div>
                                    <span class="badge bg-primary text-white px-2 py-1" id="leg2Status">
                                        <?= in_array($order['delivery_status'], ['picked_up', 'out_for_delivery']) ? '🚚 En Route' : 'Delivery Leg' ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Google Maps integration -->
                        <?php 
                        $portal = 'delivery';
                        include __DIR__ . '/../partials/google_maps_loader.php'; 
                        ?>
                        
                        <div id="dpNavMap" class="mt-2 d-none" style="height: 420px; border-radius: 8px; z-index: 1;"></div>
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

                            // Fit bounds to include all 3 key points: Rider, Kitchen, and Customer
                            let pointsToFit = [{lat: startLat, lng: startLng}, {lat: kitchenLat, lng: kitchenLng}];
                            if (destinationLatLng) {
                                pointsToFit.push(destinationLatLng);
                            }
                            KravyoMaps.fitToBounds(map, pointsToFit, 50, 15);

                            // ── 1. ALWAYS DRAW LEG 2: Kitchen -> Customer (Blue #2563eb) ──
                            if (destinationLatLng && !window.routeRendererKitchenCustomer) {
                                KravyoMaps.drawRoute(map, {lat: kitchenLat, lng: kitchenLng}, destinationLatLng, null, '#2563eb', '#1e3a8a')
                                    .then(res => {
                                        window.routeRendererKitchenCustomer = res;
                                        if (res && res.data && res.data.distance_m) {
                                            const km = (res.data.distance_m / 1000).toFixed(1);
                                            const mins = Math.round(res.data.duration_s / 60);
                                            const el = document.getElementById('leg2Details');
                                            if (el) el.textContent = `${km} km (${mins} mins) from kitchen`;
                                        }
                                    })
                                    .catch(e => console.error('Kitchen to Customer route error:', e));
                            }

                            // ── 2. DRAW ACTIVE LEG ──
                            if (!isPickedUp) {
                                // Stage 1: Rider is navigating to the kitchen (Amber/Orange #f59e0b)
                                KravyoMaps.drawRoute(map, {lat: startLat, lng: startLng}, {lat: kitchenLat, lng: kitchenLng}, null, '#f59e0b', '#78350f')
                                    .then(res => {
                                        if (window.routeRendererActive && window.routeRendererActive !== res) {
                                            window.routeRendererActive.setMap(null);
                                        }
                                        window.routeRendererActive = res;
                                        if (res && res.data && res.data.distance_m) {
                                            const km = (res.data.distance_m / 1000).toFixed(1);
                                            const mins = Math.round(res.data.duration_s / 60);
                                            const el = document.getElementById('leg1Details');
                                            if (el) el.textContent = `${km} km (${mins} mins) to kitchen`;
                                        }
                                    })
                                    .catch(e => console.error('Rider to Kitchen route error:', e));
                            } else {
                                // Stage 2: Food picked up! Rider is navigating to the customer (Emerald Green #10b981)
                                if (destinationLatLng) {
                                    KravyoMaps.drawRoute(map, {lat: startLat, lng: startLng}, destinationLatLng, null, '#10b981', '#064e3b')
                                        .then(res => {
                                            if (window.routeRendererActive && window.routeRendererActive !== res) {
                                                window.routeRendererActive.setMap(null);
                                            }
                                            window.routeRendererActive = res;
                                            if (res && res.data && res.data.distance_m) {
                                                const km = (res.data.distance_m / 1000).toFixed(1);
                                                const mins = Math.round(res.data.duration_s / 60);
                                                const el = document.getElementById('leg2Details');
                                                if (el) el.textContent = `${km} km (${mins} mins) remaining to customer`;
                                            }
                                        })
                                        .catch(e => console.error('Rider to Customer route error:', e));
                                }
                            }
                        }

                        function startWatching() {
                            // Immediate render with recorded rider location or kitchen so map is instantly visible
                            const initialLat = <?= !empty($order['delivery_partner_lat']) ? (float)$order['delivery_partner_lat'] : (!empty($order['kitchen_lat']) ? (float)$order['kitchen_lat'] : 21.1702) ?>;
                            const initialLng = <?= !empty($order['delivery_partner_lng']) ? (float)$order['delivery_partner_lng'] : (!empty($order['kitchen_lng']) ? (float)$order['kitchen_lng'] : 72.8311) ?>;
                            initMapAndRouting(initialLat, initialLng);

                            // If testing with dummy location, do not let browser/PC GPS overwrite it!
                            if (sessionStorage.getItem('kravyo_dummy_location_active') === '1') {
                                console.log("Kravyo GPS: Dummy testing location active. Skipping watchPosition.");
                                gpsSuccess.classList.remove('d-none');
                                gpsSuccess.innerHTML = '<i class="bi bi-geo-alt-fill text-warning"></i> Dummy test location active (' + initialLat.toFixed(4) + ', ' + initialLng.toFixed(4) + '). PC GPS paused.';
                                gpsStatusText.textContent = 'Dummy Mode';
                                gpsStatusText.className = 'form-check-label fw-bold text-warning';
                                return;
                            }

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
                                    // Double check dummy flag
                                    if (sessionStorage.getItem('kravyo_dummy_location_active') === '1') return;

                                    const lat = position.coords.latitude;
                                    const lng = position.coords.longitude;
                                    
                                    // Update Map
                                    initMapAndRouting(lat, lng);

                                    // Send to server
                                    const formData = new FormData();
                                    formData.append('latitude', lat);
                                    formData.append('longitude', lng);
                                    
                                    // Use form CSRF if meta not available
                                    const csrfVal = '<?= csrf_token() ?>';
                                    formData.append('_csrf', csrfVal);

                                    fetch(updateUrl, {
                                        method: 'POST',
                                        headers: {
                                            'X-Requested-With': 'XMLHttpRequest',
                                            'X-CSRF-TOKEN': csrfVal
                                        },
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
                    <?php if ($order['payment_method'] === 'cod' && $order['payment_status'] !== 'completed' && $order['delivery_status'] !== 'delivered'): ?>
                        <div class="alert alert-danger mt-3 mb-0 text-center fw-bold py-2">
                            <i class="bi bi-cash-coin me-1"></i> Collect <?= format_currency($order['total_amount']) ?> Cash or Doorstep QR!
                        </div>
                    <?php elseif ($order['payment_method'] === 'split' && $order['payment_status'] === 'completed'): ?>
                        <div class="alert alert-success mt-3 mb-0 text-start small py-2">
                            <div class="fw-bold mb-1"><i class="bi bi-check-circle-fill me-1"></i> Split Payment Settled:</div>
                            <div class="d-flex justify-content-between">
                                <span>Cash Collected:</span>
                                <strong><?= format_currency($order['paid_cash_amount']) ?></strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>UPI Received:</span>
                                <strong><?= format_currency($order['paid_online_amount']) ?></strong>
                            </div>
                            <div class="text-muted text-end mt-1" style="font-size:0.75rem;">Ref: <?= sanitize($order['razorpay_payment_id'] ?? 'Verified') ?></div>
                        </div>
                    <?php elseif ($order['payment_status'] === 'completed'): ?>
                        <div class="alert alert-success mt-3 mb-0 text-center fw-bold py-2">
                            <i class="bi bi-check-circle-fill me-1"></i> Paid Online / UPI (<?= sanitize($order['razorpay_payment_id'] ?? 'Completed') ?>)
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

<?php
$doorstepUpiString = sprintf(
    'upi://pay?pa=%s&pn=%s&am=%s&cu=INR&tn=%s',
    'kravyo.foods@upi',
    urlencode('Kravyo Foods'),
    round($order['total_amount'], 2),
    urlencode('Doorstep Order #' . $order['order_number'])
);
$doorstepQrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&margin=4&data=' . urlencode($doorstepUpiString);
?>
<!-- Doorstep UPI QR Modal (Test Mode - Full or Split Pay) -->
<div class="modal fade" id="doorstepUpiModal" tabindex="-1" aria-labelledby="doorstepUpiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-dark text-white border-0 px-4 py-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-1" style="font-size:0.75rem;">
                        <i class="bi bi-phone me-1"></i>Doorstep QR Pay
                    </span>
                    <h5 class="modal-title fw-bold mb-0" id="doorstepUpiModalLabel">Collect Payment via UPI QR</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4 text-center">
                <!-- Sandbox Test Notice -->
                <div class="alert alert-warning border-0 rounded-3 text-start small mb-3 py-2 px-3 d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle-fill text-warning fs-5 flex-shrink-0"></i>
                    <div>
                        <strong class="d-block">Test Mode Active (₹0 Deducted)</strong>
                        <span>Customer scans QR with any UPI app (GPay, PhonePe, Paytm, BHIM). Safe sandbox demo mode.</span>
                    </div>
                </div>

                <!-- Navigation Tabs: Full UPI vs Split Pay -->
                <ul class="nav nav-pills nav-fill mb-3 p-1 bg-light rounded-3 border" id="doorstepPayTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold py-1.5" id="tab-full-upi" data-bs-toggle="pill" data-bs-target="#pane-full-upi" type="button" role="tab">
                            <i class="bi bi-qr-code-scan me-1"></i> 100% UPI QR
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold py-1.5" id="tab-split-pay" data-bs-toggle="pill" data-bs-target="#pane-split-pay" type="button" role="tab">
                            <i class="bi bi-pie-chart-fill me-1"></i> Split Pay (Cash + UPI)
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="doorstepPayTabsContent">
                    <!-- Tab 1: 100% Full UPI -->
                    <div class="tab-pane fade show active" id="pane-full-upi" role="tabpanel">
                        <div class="bg-light rounded-3 p-3 mb-3 border">
                            <div class="text-muted small">Total Payable Amount</div>
                            <div class="fw-800 fs-3 text-dark"><?= format_currency($order['total_amount']) ?></div>
                            <div class="small text-muted mt-1">
                                Order #<span class="fw-bold"><?= sanitize($order['order_number']) ?></span> • <?= sanitize($order['customer_name'] ?? 'Customer') ?>
                            </div>
                        </div>

                        <div class="p-3 rounded-4 bg-white border shadow-sm mb-3 d-inline-block">
                            <img src="<?= $doorstepQrUrl ?>" alt="Full UPI QR Code" width="220" height="220" class="img-fluid rounded-3" style="display:block; min-width:220px; min-height:220px; object-fit:contain;">
                        </div>

                        <div class="small text-muted mb-3">
                            UPI ID: <strong class="text-dark">kravyo.foods@upi</strong>
                        </div>

                        <form action="<?= url('/delivery/order/' . $order['id'] . '/doorstep-upi') ?>" method="POST" data-once>
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-success btn-lg w-100 fw-bold shadow-sm" data-loading-text="Confirming Payment...">
                                <i class="bi bi-check2-circle me-1"></i> Confirm Full UPI Received (Test Mode)
                            </button>
                        </form>
                    </div>

                    <!-- Tab 2: Split Pay (Cash + UPI) -->
                    <div class="tab-pane fade" id="pane-split-pay" role="tabpanel">
                        <div class="bg-light rounded-3 p-3 mb-3 border text-start">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small">Order Total:</span>
                                <span class="fw-bold fs-6 text-dark"><?= format_currency($order['total_amount']) ?></span>
                            </div>

                            <label for="splitCashInput" class="form-label small fw-bold text-dark mb-1">
                                <i class="bi bi-cash-stack text-success me-1"></i> Cash Amount Collected (₹):
                            </label>
                            <div class="input-group mb-2">
                                <span class="input-group-text bg-white fw-bold">₹</span>
                                <input type="number" class="form-control form-control-lg fw-bold" id="splitCashInput" 
                                       step="1" min="1" max="<?= ((float)$order['total_amount'] - 1) ?>" 
                                       value="<?= floor((float)$order['total_amount'] / 2) ?>" placeholder="Cash given by customer">
                            </div>
                            <div class="d-flex justify-content-between small pt-1 border-top">
                                <span>💵 Cash: <strong id="splitCashSummary" class="text-dark">₹0.00</strong></span>
                                <span>⚡ Remaining UPI: <strong id="splitUpiSummary" class="text-success fw-bold">₹0.00</strong></span>
                            </div>
                        </div>

                        <!-- Dynamic Split QR Box -->
                        <div class="p-3 rounded-4 bg-white border shadow-sm mb-3 d-inline-block">
                            <img id="splitQrImage" src="" alt="Split UPI QR Code" width="220" height="220" class="img-fluid rounded-3" style="display:block; min-width:220px; min-height:220px; object-fit:contain;">
                        </div>

                        <div class="small text-muted mb-3">
                            Scan with UPI app to pay balance: <strong id="splitQrAmountDisplay" class="text-dark">₹0.00</strong>
                        </div>

                        <form action="<?= url('/delivery/order/' . $order['id'] . '/doorstep-split') ?>" method="POST" id="doorstepSplitForm" data-once>
                            <?= csrf_field() ?>
                            <input type="hidden" name="cash_amount" id="formSplitCash" value="">
                            <input type="hidden" name="online_amount" id="formSplitOnline" value="">
                            <button type="submit" class="btn btn-success btn-lg w-100 fw-bold shadow-sm" id="btnConfirmSplit" data-loading-text="Recording Split Payment...">
                                <i class="bi bi-check2-circle me-1"></i> Confirm Split Payment Received (Test Mode)
                            </button>
                        </form>
                    </div>
                </div>

                <button type="button" class="btn btn-outline-secondary btn-sm w-100 mt-2" data-bs-dismiss="modal">
                    Cancel & Return
                </button>
            </div>
            <div class="modal-footer bg-light border-0 py-2 justify-content-center">
                <small class="text-muted"><i class="bi bi-shield-check me-1"></i> Instant Order Update • Safe Test Mode</small>
            </div>
        </div>
    </div>
</div>

<script>
/**
 * Double-submit prevention for forms marked with [data-once]
 */
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('form[data-once]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (form.dataset.submitted === '1') {
                e.preventDefault();
                return false;
            }
            form.dataset.submitted = '1';
            var btn = form.querySelector('[type="submit"]');
            if (btn) {
                btn.style.pointerEvents = 'none';
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>'
                              + (btn.dataset.loadingText || 'Processing...');
                setTimeout(function () {
                    btn.disabled = true;
                }, 0);
            }
        });
    });

    // ── Doorstep Split Pay Logic ─────────────────────────────────────────
    const totalAmount = <?= json_encode((float)$order['total_amount']) ?>;
    const orderNumber = <?= json_encode($order['order_number']) ?>;
    const cashInput   = document.getElementById('splitCashInput');
    const cashSummary = document.getElementById('splitCashSummary');
    const upiSummary  = document.getElementById('splitUpiSummary');
    const qrDisplay   = document.getElementById('splitQrAmountDisplay');
    const splitQrImg  = document.getElementById('splitQrImage');
    const formCash    = document.getElementById('formSplitCash');
    const formOnline  = document.getElementById('formSplitOnline');
    const btnConfirm  = document.getElementById('btnConfirmSplit');

    function updateSplitCalculations() {
        if (!cashInput) return;
        let cash = parseFloat(cashInput.value);
        if (isNaN(cash) || cash < 1) cash = 1;
        if (cash >= totalAmount) cash = totalAmount - 1;

        cash = Math.round(cash * 100) / 100;
        const online = Math.round((totalAmount - cash) * 100) / 100;

        if (cashSummary) cashSummary.textContent = '₹' + cash.toFixed(2);
        if (upiSummary) upiSummary.textContent = '₹' + online.toFixed(2);
        if (qrDisplay) qrDisplay.textContent = '₹' + online.toFixed(2);

        if (formCash) formCash.value = cash.toFixed(2);
        if (formOnline) formOnline.value = online.toFixed(2);

        if (btnConfirm) {
            btnConfirm.disabled = (cash <= 0 || online <= 0);
        }

        const splitUpiString = 'upi://pay?pa=kravyo.foods@upi&pn=' + encodeURIComponent('Kravyo Foods') + 
            '&am=' + online.toFixed(2) + 
            '&cu=INR&tn=' + encodeURIComponent('Split Order #' + orderNumber + ' (UPI Balance)');
        const qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&margin=4&data=' + encodeURIComponent(splitUpiString);

        if (splitQrImg) splitQrImg.src = qrUrl;
    }

    if (cashInput) {
        cashInput.addEventListener('input', updateSplitCalculations);
        cashInput.addEventListener('change', updateSplitCalculations);
        updateSplitCalculations();
    }

    const tabSplit = document.getElementById('tab-split-pay');
    if (tabSplit) {
        tabSplit.addEventListener('shown.bs.tab', updateSplitCalculations);
    }
});
</script>
