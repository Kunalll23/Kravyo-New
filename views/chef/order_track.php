<?php
// Phase 12: Chef Order Tracking (Rider map)
require_once APP_PATH . '/../config/pusher.php';
?>
<section class="py-4">
    <div class="container" id="live-order-track" data-live-refresh="true">
        <!-- Order Header -->
        <div class="order-track-header mb-4">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <nav aria-label="breadcrumb" class="mb-2">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="<?= url('/chef/orders') ?>" class="text-decoration-none">My Orders</a></li>
                            <li class="breadcrumb-item active">#<?= sanitize($order['order_number']) ?></li>
                        </ol>
                    </nav>
                    <h2 class="fw-800 mb-1">Track Rider for Order #<?= sanitize($order['order_number']) ?></h2>
                    <p class="text-muted mb-0">
                        <i class="bi bi-clock me-1"></i> Placed on <?= date('M d, Y \a\t h:i A', strtotime($order['created_at'])) ?>
                        <span class="mx-2">•</span>
                        <i class="bi bi-person me-1"></i> Customer: <?= sanitize($order['customer_name'] ?? 'Guest') ?>
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <span class="badge bg-primary px-3 py-2 fs-6 rounded-pill">
                        <?= ucwords(str_replace('_', ' ', $order['order_status'])) ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <?php if (!empty($order['delivery_partner_id']) && !in_array($order['order_status'], ['delivered', 'cancelled'])): ?>
                    <!-- Delivery Partner Info Box -->
                    <div class="checkout-section mb-4 p-3 bg-light rounded-3 d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-white d-flex align-items-center justify-content-center shadow-sm" style="width:50px; height:50px;">
                            <i class="bi bi-person-circle fs-3 text-secondary"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="fw-bold mb-0"><?= sanitize($order['delivery_partner_name'] ?? 'Delivery Partner') ?></h6>
                            <p class="small text-muted mb-0">
                                <?= sanitize($order['vehicle_type'] ?? 'Vehicle') ?> &bull; <?= sanitize($order['vehicle_number'] ?? '') ?>
                            </p>
                        </div>
                        <a href="tel:<?= sanitize($order['delivery_partner_phone'] ?? '') ?>" class="btn btn-sm btn-outline-success rounded-circle" style="width:40px; height:40px; display:flex; align-items:center; justify-content:center;">
                            <i class="bi bi-telephone-fill"></i>
                        </a>
                    </div>

                    <!-- Google Maps integration -->
                    <?php include __DIR__ . '/../partials/google_maps_loader.php'; ?>
                    
                    <!-- Pusher Real-Time WebSockets -->
                    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
                    
                    <div class="checkout-section mb-4">
                        <h5 class="checkout-section-title">
                            <i class="bi bi-geo-alt-fill me-2 text-danger"></i>Live Rider Tracking
                        </h5>
                        <div id="liveDeliveryMap" style="height: 400px; border-radius: 12px; z-index: 1;"></div>
                        <div class="text-center mt-2 small fw-bold text-muted" id="mapStatus">
                            <i class="bi bi-broadcast"></i> Waiting for delivery partner's location...
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle-fill fs-1 me-3 text-warning"></i>
                        <div>
                            <h5 class="fw-bold mb-1">Tracking Not Available</h5>
                            <p class="mb-0 small">No active rider is currently assigned to this order or the order has been completed/cancelled.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Delivery Address -->
                <div class="checkout-section mb-4">
                    <h6 class="fw-bold mb-2"><i class="bi bi-geo-alt me-2 text-danger"></i>Dropoff Address</h6>
                    <p class="small mb-0 fw-600"><?= sanitize($order['street_address']) ?></p>
                    <?php if (!empty($order['landmark'])): ?>
                        <p class="small mb-0 text-muted">Near: <?= sanitize($order['landmark']) ?></p>
                    <?php endif; ?>
                    <p class="small mb-0 text-muted">
                        <?= sanitize($order['delivery_city']) ?> — <?= sanitize($order['delivery_pincode']) ?>
                    </p>
                </div>

                <!-- Order Items -->
                <div class="checkout-section mb-4">
                    <h6 class="fw-bold mb-3"><i class="bi bi-bag me-2 text-warning"></i>Order Info</h6>
                    <div class="text-center text-muted mb-2">
                        <a href="<?= url('/chef/orders') ?>" class="btn btn-outline-primary btn-sm w-100">
                            <i class="bi bi-arrow-left me-1"></i> Back to Orders List
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($order['delivery_partner_id']) && !in_array($order['order_status'], ['delivered', 'cancelled'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof KravyoMaps === 'undefined') return;

    KravyoMaps.init().then(() => {
        const kitchenLat = <?= $order['kitchen_lat'] ?? 20.0 ?>;
        const kitchenLng = <?= $order['kitchen_lng'] ?? 78.0 ?>;
        
        let destinationLatLng = null;
        let map = null;
        let routeRenderer = null;

        <?php if(!empty($order['delivery_latitude']) && !empty($order['delivery_longitude'])): ?>
            destinationLatLng = { lat: <?= (float)$order['delivery_latitude'] ?>, lng: <?= (float)$order['delivery_longitude'] ?> };
            initializeTrackingMap();
        <?php else: ?>
            const fullAddress = "<?= sanitize($order['street_address'] . ', ' . $order['delivery_city'] . ', ' . $order['delivery_pincode']) ?>";
            
            KravyoMaps.geocode(fullAddress).then(result => {
                if(result && result.lat) {
                    destinationLatLng = { lat: result.lat, lng: result.lng };
                } else {
                    console.warn("Could not geocode customer address.");
                }
                initializeTrackingMap();
            }).catch(err => {
                console.error("Geocoding failed", err);
                initializeTrackingMap();
            });
        <?php endif; ?>

        function initializeTrackingMap() {
            map = KravyoMaps.createMap('liveDeliveryMap', kitchenLat, kitchenLng, 14);
            
            fetch('<?= url('/order/location/' . $order['id']) ?>')
                .then(res => res.json())
                .then(data => updateMap(data))
                .catch(err => console.error('Error fetching initial location:', err));
        }

        function updateMap(data) {
            if (!map) return;
            
            if (data.latitude && data.longitude) {
                const lat = parseFloat(data.latitude);
                const lng = parseFloat(data.longitude);
                const status = data.delivery_status || '<?= $order['order_status'] ?>';
                const isPhase2 = (status === 'out_for_delivery');
                
                KravyoMaps.updateDriverLocation(map, 'partner_marker_<?= $order['delivery_partner_id'] ?>', lat, lng);

                if (!window.kitchenMarker && kitchenLat !== 20.0) {
                    window.kitchenMarker = KravyoMaps.addStaticMarker(map, kitchenLat, kitchenLng, 'Kitchen', 'restaurant');
                }
                
                if (!window.customerMarker && destinationLatLng) {
                    window.customerMarker = KravyoMaps.addStaticMarker(map, destinationLatLng.lat, destinationLatLng.lng, 'Customer', 'home');
                }

                // Make sure the map auto-zooms to fit all elements
                let pointsToFit = [{lat, lng}];
                if (window.kitchenMarker) pointsToFit.push({lat: kitchenLat, lng: kitchenLng});
                if (window.customerMarker) pointsToFit.push(destinationLatLng);
                KravyoMaps.fitToBounds(map, pointsToFit, 60, 16);

                const newPoint = { lat, lng };
                let shouldRoute = false;
                
                if (!window.lastRoutedPoint || window.lastRoutePhase !== isPhase2) {
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
                    
                    if (d > 50) {
                        shouldRoute = true;
                    }
                }

                if (shouldRoute) {
                    let origin = { lat: lat, lng: lng };

                    if (destinationLatLng) {
                        // Leg 1: Rider -> Kitchen (Red)
                        KravyoMaps.drawRoute(map, origin, {lat: kitchenLat, lng: kitchenLng}, null, '#ef4444').then(r => {
                            if (window.routeRenderer1) window.routeRenderer1.setMap(null);
                            window.routeRenderer1 = r;
                        }).catch(e => console.error(e));
                        // Leg 2: Kitchen -> Customer (Blue)
                        KravyoMaps.drawRoute(map, {lat: kitchenLat, lng: kitchenLng}, destinationLatLng, null, '#2563eb').then(r => {
                            if (window.routeRenderer2) window.routeRenderer2.setMap(null);
                            window.routeRenderer2 = r;
                        }).catch(e => console.error(e));
                        
                        window.lastRoutedPoint = newPoint;
                    }
                }

                let phaseText = isPhase2 ? 'Heading to customer...' : 'Heading to kitchen...';
                document.getElementById('mapStatus').innerHTML = '<i class="bi bi-broadcast text-success"></i> Rider live location updated. ' + phaseText;
            }
        }

        // Pusher Real-Time WebSocket
        const pusher = new Pusher('<?= PUSHER_KEY ?>', { cluster: '<?= PUSHER_CLUSTER ?>' });
        const channel = pusher.subscribe('order-<?= $order['id'] ?>');
        channel.bind('location_update', function(data) {
            updateMap(data);
        });
        
        // 5-second polling fallback
        setInterval(() => {
            fetch('<?= url('/order/location/' . $order['id']) ?>')
                .then(res => res.json())
                .then(data => updateMap(data))
                .catch(err => console.error('Error in polling fallback:', err));
        }, 5000);
    });
});
</script>
<?php endif; ?>
