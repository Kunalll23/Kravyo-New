<!-- Phase 6 + Phase 9: Order Tracking Timeline Page -->
<?php
// Phase 9: Check if customer already reviewed this order
require_once APP_PATH . '/models/Review.php';
$_reviewModel   = new Review();
$_existingReview = $_reviewModel->findByOrderId((int) $order['id']);
?>
<section class="py-4">
    <div class="container" id="live-order-track" data-live-refresh="true">
        <!-- Order Header -->
        <div class="order-track-header mb-4">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <nav aria-label="breadcrumb" class="mb-2">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="<?= url('/orders/history') ?>" class="text-decoration-none">My Orders</a></li>
                            <li class="breadcrumb-item active">#<?= sanitize($order['order_number']) ?></li>
                        </ol>
                    </nav>
                    <h2 class="fw-800 mb-1">Order #<?= sanitize($order['order_number']) ?></h2>
                    <p class="text-muted mb-0">
                        <i class="bi bi-clock me-1"></i> Placed on <?= date('M d, Y \a\t h:i A', strtotime($order['created_at'])) ?>
                        <span class="mx-2">•</span>
                        <i class="bi bi-shop me-1"></i> <?= sanitize($order['kitchen_name']) ?>
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <?php
                    // Customer-friendly status labels and colors
                    // Backend 'accepted' is shown as 'Confirmed' to the customer
                    $customerStatusLabels = [
                        'pending'          => 'Awaiting Confirmation',
                        'accepted'         => 'Order Confirmed',
                        'preparing'        => 'Preparing',
                        'ready_for_pickup' => 'Ready for Pickup',
                        'out_for_delivery' => 'Out for Delivery',
                        'delivered'        => 'Delivered',
                        'cancelled'        => 'Cancelled',
                        'delivery_failed'  => 'Delivery Failed',
                    ];
                    $statusColors = [
                        'pending' => 'warning', 'accepted' => 'info', 'preparing' => 'primary',
                        'ready_for_pickup' => 'info',
                        'out_for_delivery' => 'success', 'delivered' => 'success', 'cancelled' => 'danger', 'delivery_failed' => 'danger'
                    ];
                    $statusColor = $statusColors[$order['order_status']] ?? 'secondary';
                    $statusLabel = $customerStatusLabels[$order['order_status']] ?? ucwords(str_replace('_', ' ', $order['order_status']));
                    ?>
                    <span class="badge bg-<?= $statusColor ?> px-3 py-2 fs-6 rounded-pill">
                        <?= $statusLabel ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Timeline -->
            <div class="col-lg-8">
                <?php if ($order['order_status'] === 'cancelled'): ?>
                    <!-- Cancelled State -->
                    <div class="order-cancelled-card mb-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="cancelled-icon">
                                <i class="bi bi-x-circle-fill"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1 text-danger">Order Cancelled</h5>
                                <p class="mb-0 text-muted">This order has been cancelled and will not be processed.</p>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Simplified 4-Step Status Timeline -->
                    <div class="order-timeline mb-4">
                        <?php
                        // Customer-facing timeline order (5 steps)
                        $timelineOrder = ['placed', 'confirmed', 'preparing', 'out_for_delivery', 'delivered'];
                        $currentIndex = array_search($customerStep, $timelineOrder);
                        if ($currentIndex === false) $currentIndex = 0;
                        ?>

                        <?php foreach ($statusTimeline as $stepKey => $step): ?>
                            <?php
                            $stepIndex = array_search($stepKey, $timelineOrder);
                            $isCompleted = $stepIndex < $currentIndex;
                            $isActive = $stepIndex === $currentIndex;
                            $isPending = $stepIndex > $currentIndex;
                            $stepClass = $isCompleted ? 'completed' : ($isActive ? 'active' : 'pending');
                            ?>
                            <div class="timeline-step <?= $stepClass ?>">
                                <div class="timeline-node">
                                    <?php if ($isCompleted): ?>
                                        <i class="bi bi-check-lg"></i>
                                    <?php else: ?>
                                        <i class="bi <?= $step['icon'] ?>"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="timeline-content">
                                    <h6 class="fw-bold mb-0"><?= $step['label'] ?></h6>
                                    <p class="small text-muted mb-0"><?= $step['desc'] ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($order['order_status'] === 'out_for_delivery' && !empty($order['delivery_otp'])): ?>
                    <div class="alert alert-info border-0 shadow-sm mb-4 d-flex align-items-center">
                        <i class="bi bi-shield-lock-fill fs-1 me-3 text-info"></i>
                        <div>
                            <h5 class="fw-bold mb-1">Delivery OTP: <span class="text-primary fs-3"><?= sanitize($order['delivery_otp']) ?></span></h5>
                            <p class="mb-0 small text-muted">Share this OTP with your delivery partner to receive your order.</p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($order['delivery_partner_id']) && $order['order_status'] === ORDER_STATUS_OUT_FOR_DELIVERY): ?>
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
                            <i class="bi bi-geo-alt-fill me-2 text-danger"></i>Live Delivery Tracking
                        </h5>
                        <div id="liveDeliveryMap" style="height: 300px; border-radius: 12px; z-index: 1;"></div>
                        <div class="text-center mt-2 small fw-bold text-muted" id="mapStatus">
                            <i class="bi bi-broadcast"></i> Waiting for delivery partner's location...
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($order['order_status'] === 'delivery_failed'): ?>
                    <div class="alert alert-danger border-0 shadow-sm mb-4 d-flex align-items-center">
                        <i class="bi bi-exclamation-octagon-fill fs-1 me-3 text-danger"></i>
                        <div>
                            <h5 class="fw-bold mb-1">Delivery Failed</h5>
                            <p class="mb-0 small">The delivery partner reported an issue. Reason: <strong><?= sanitize($order['delivery_failed_reason'] ?? 'Customer unavailable') ?></strong></p>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#reportModal" data-kitchen-id="<?= $order['kitchen_id'] ?>" data-order-id="<?= $order['id'] ?>" data-target-type="delivery" data-target-id="<?= $order['id'] ?>"><i class="bi bi-flag me-1"></i> Report Delivery Issue</button>
                </div>

                <!-- Order Items -->
                <div class="checkout-section">
                    <h5 class="checkout-section-title">
                        <i class="bi bi-bag me-2 text-warning"></i>Order Items
                    </h5>

                    <?php foreach ($orderItems as $item): ?>
                        <div class="order-track-item d-flex align-items-center gap-3 mb-3">
                            <div class="order-track-item-img">
                                <?php if (!empty($item['image'])): ?>
                                    <img src="<?= UPLOAD_URL . '/dishes/' . $item['image'] ?>"
                                         alt="<?= sanitize($item['item_name']) ?>">
                                <?php else: ?>
                                    <div class="order-track-item-placeholder">
                                        <i class="bi bi-egg-fried"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1">
                                    <?php if ($item['is_veg']): ?>
                                        <i class="bi bi-circle-fill text-success me-1" style="font-size:0.5rem;"></i>
                                    <?php else: ?>
                                        <i class="bi bi-circle-fill text-danger me-1" style="font-size:0.5rem;"></i>
                                    <?php endif; ?>
                                    <?= sanitize($item['item_name']) ?>
                                </h6>
                                <?php if (!empty($item['is_zero_waste'])): ?>
                                    <span class="badge rounded-pill fw-semibold small px-2 py-1" style="background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;">
                                        <i class="bi bi-recycle me-1"></i>Zero Waste Deal
                                    </span>
                                <?php else: ?>
                                    <div class="d-flex gap-1">
                                        <span class="cart-custom-tag">🌶️ <?= sanitize($item['spice_level']) ?></span>
                                        <span class="cart-custom-tag">🫒 <?= sanitize($item['oil_level']) ?></span>
                                        <?php if ($item['is_jain']): ?>
                                            <span class="cart-custom-tag cart-custom-jain">🌿 Jain</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="text-end">
                                <div class="small text-muted"><?= format_currency($item['unit_price']) ?> × <?= $item['quantity'] ?></div>
                                <div class="fw-bold <?= !empty($item['is_zero_waste']) ? 'text-success' : '' ?>"><?= format_currency($item['subtotal']) ?></div>
                                <button type="button" class="btn btn-sm btn-link text-danger p-0 mt-1 text-decoration-none" style="font-size: 0.75rem;" data-bs-toggle="modal" data-bs-target="#reportModal" data-kitchen-id="<?= $order['kitchen_id'] ?>" data-order-id="<?= $order['id'] ?>" data-target-type="dish" data-target-id="<?= $item['menu_item_id'] ?>"><i class="bi bi-flag"></i> Report</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Phase 9: Rate & Review (only when delivered) -->
                <?php if ($order['order_status'] === 'delivered'): ?>
                    <div class="checkout-section mt-4" id="reviewSection">
                        <h5 class="checkout-section-title">
                            <i class="bi bi-star me-2 text-warning"></i>Rate Your Experience
                        </h5>

                        <?php if ($_existingReview): ?>
                            <!-- Already reviewed -->
                            <div class="d-flex align-items-start gap-3 p-3 bg-light rounded-3">
                                <div class="text-warning fs-4">
                                    <?php for ($s = 1; $s <= 5; $s++): ?>
                                        <i class="bi bi-star<?= $s <= (int)$_existingReview['rating'] ? '-fill' : '' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <div>
                                    <p class="fw-semibold mb-1">Your review has been submitted — thank you! ✅</p>
                                    <?php if (!empty($_existingReview['review_text'])): ?>
                                        <p class="text-muted small mb-0">"<?= sanitize($_existingReview['review_text']) ?>"</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <form action="<?= url('/review/submit') ?>" method="POST" id="reviewForm">
                                <?= csrf_field() ?>
                                <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">

                                <!-- Star Rating -->
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">How was your meal? <span class="text-danger">*</span></label>
                                    <div class="star-rating-input d-flex gap-1" id="starRatingInput">
                                        <?php for ($s = 5; $s >= 1; $s--): ?>
                                            <input type="radio" id="star<?= $s ?>" name="rating" value="<?= $s ?>" class="visually-hidden" required>
                                            <label for="star<?= $s ?>" class="star-label fs-2 text-muted" title="<?= $s ?> star<?= $s > 1 ? 's' : '' ?>">
                                                <i class="bi bi-star-fill" style="cursor:pointer;"></i>
                                            </label>
                                        <?php endfor; ?>
                                    </div>
                                    <div class="form-text" id="ratingText">Click to rate</div>
                                </div>

                                <!-- Review Text -->
                                <div class="mb-3">
                                    <label for="review_text" class="form-label fw-semibold">Share your experience (optional)</label>
                                    <textarea class="form-control" id="review_text" name="review_text"
                                              rows="3" maxlength="500"
                                              placeholder="The food was amazing! Fresh, tasty and delivered on time..."></textarea>
                                </div>

                                <button type="submit" class="btn btn-kravyo-primary">
                                    <i class="bi bi-send me-2"></i>Submit Review
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Order Details Sidebar -->
            <div class="col-lg-4">
                <div class="cart-summary-card mb-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-receipt me-2"></i>Payment Details</h5>

                    <div class="d-flex justify-content-between small mb-2">
                        <span class="text-muted">Subtotal</span>
                        <span class="fw-600"><?= format_currency($order['total_amount']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between small mb-2">
                        <span class="text-muted">Delivery</span>
                        <span class="fw-600 text-success">Free</span>
                    </div>

                    <hr class="my-2">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-bold">Total</span>
                        <span class="fw-800 fs-5"><?= format_currency($order['total_amount']) ?></span>
                    </div>

                    <div class="d-flex justify-content-between small mb-2">
                        <span class="text-muted">Payment Method</span>
                        <span class="fw-600"><?= strtoupper($order['payment_method']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between small">
                        <span class="text-muted">Payment Status</span>
                        <span class="badge bg-<?= $order['payment_status'] === 'completed' ? 'success' : 'warning' ?> text-capitalize">
                            <?= $order['payment_status'] ?>
                        </span>
                    </div>
                </div>

                <!-- Delivery Address -->
                <div class="checkout-section mb-4">
                    <h6 class="fw-bold mb-2"><i class="bi bi-geo-alt me-2 text-danger"></i>Delivery Address</h6>
                    <p class="small mb-0 fw-600"><?= sanitize($order['street_address']) ?></p>
                    <?php if (!empty($order['landmark'])): ?>
                        <p class="small mb-0 text-muted">Near: <?= sanitize($order['landmark']) ?></p>
                    <?php endif; ?>
                    <p class="small mb-0 text-muted">
                        <?= sanitize($order['delivery_city']) ?> — <?= sanitize($order['delivery_pincode']) ?>
                    </p>
                </div>

                <?php if (!empty($order['special_instructions'])): ?>
                    <div class="checkout-section mb-4">
                        <h6 class="fw-bold mb-2"><i class="bi bi-chat-left-text me-2 text-primary"></i>Special Instructions</h6>
                        <p class="small mb-0 text-muted"><?= nl2br(sanitize($order['special_instructions'])) ?></p>
                    </div>
                <?php endif; ?>

                <!-- Cancel Button (only if pending) -->
                <?php if ($order['order_status'] === ORDER_STATUS_PENDING): ?>
                    <form method="POST" action="<?= url('/order/cancel') ?>" id="cancelOrderForm">
                        <?= csrf_field() ?>
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                        <button type="submit" class="btn btn-outline-danger w-100"
                                onclick="return confirm('Are you sure you want to cancel this order?');">
                            <i class="bi bi-x-circle me-2"></i> Cancel Order
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<style>
/* Phase 9: Star rating input */
.star-rating-input {
    flex-direction: row-reverse;
    justify-content: flex-end;
}
.star-rating-input input:checked ~ label .bi,
.star-rating-input label:hover ~ label .bi,
.star-rating-input label:hover .bi {
    color: #f59e0b !important;
}
.star-label { transition: color 0.15s ease; }
</style>

<script>
// Star rating label feedback
const ratingLabels = {1:'Poor',2:'Fair',3:'Good',4:'Great',5:'Excellent!'};
document.querySelectorAll('#starRatingInput input').forEach(radio => {
    radio.addEventListener('change', () => {
        document.getElementById('ratingText').textContent =
            ratingLabels[radio.value] + ' (' + radio.value + ' star' + (radio.value > 1 ? 's' : '') + ')';
        document.getElementById('ratingText').className = 'form-text text-warning fw-semibold';
    });
});
</script>

<?php if (!empty($order['delivery_partner_id']) && $order['order_status'] === ORDER_STATUS_OUT_FOR_DELIVERY): ?>
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
            // Fallback for old orders before Phase 11
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
            
            // Initial fetch
            fetch('<?= url('/order/location/' . $order['id']) ?>')
                .then(res => res.json())
                .then(data => updateMap(data))
                .catch(err => console.error('Error fetching initial location:', err));
        }

        // 2. Real-Time WebSocket Updates via Pusher
        function updateMap(data) {
            if (!map) return;
            
            if (data.latitude && data.longitude) {
                const lat = parseFloat(data.latitude);
                const lng = parseFloat(data.longitude);
                const status = data.delivery_status || '<?= $order['order_status'] ?>';
                const isPhase2 = (status === 'out_for_delivery');
                
                // Update driver location (animates if enabled)
                KravyoMaps.updateDriverLocation(map, 'partner_marker_<?= $order['delivery_partner_id'] ?>', lat, lng);

                if (!window.kitchenMarker && kitchenLat !== 20.0) {
                    window.kitchenMarker = KravyoMaps.addStaticMarker(map, kitchenLat, kitchenLng, 'Kitchen: <?= sanitize($order['kitchen_name']) ?>', 'restaurant');
                }
                
                if (!window.customerMarker && destinationLatLng) {
                    window.customerMarker = KravyoMaps.addStaticMarker(map, destinationLatLng.lat, destinationLatLng.lng, 'Destination (Customer)', 'home');
                }

                // Make sure the map auto-zooms to fit all elements
                let pointsToFit = [{lat, lng}];
                if (window.kitchenMarker) pointsToFit.push({lat: kitchenLat, lng: kitchenLng});
                if (window.customerMarker) pointsToFit.push(destinationLatLng);
                KravyoMaps.fitToBounds(map, pointsToFit, 60, 16);

                // Routing - Calculate distance roughly
                const newPoint = { lat, lng };
                let shouldRoute = false;
                
                if (!window.lastRoutedPoint || window.lastRoutePhase !== isPhase2) {
                    shouldRoute = true;
                } else {
                    const prevPt = window.lastRoutedPoint;
                    const R = 6371e3; // metres
                    const φ1 = prevPt.lat * Math.PI/180;
                    const φ2 = newPoint.lat * Math.PI/180;
                    const Δφ = (newPoint.lat - prevPt.lat) * Math.PI/180;
                    const Δλ = (newPoint.lng - prevPt.lng) * Math.PI/180;
                    const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) + Math.cos(φ1) * Math.cos(φ2) * Math.sin(Δλ/2) * Math.sin(Δλ/2);
                    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                    const d = R * c;
                    
                    if (d > 50) { // 50 meters
                        shouldRoute = true;
                    }
                }

                if (shouldRoute) {
                    let origin = { lat: lat, lng: lng };

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
                    window.lastRoutePhase = isPhase2;
                }
                
                let dateStr = new Date().toLocaleTimeString();
                document.getElementById('mapStatus').innerHTML = '<span class="text-success"><i class="bi bi-geo-fill"></i> Live (Updated ' + dateStr + ')</span>';
            }
        }

        const pusherAppId = '24a1d781abe1f8122479'; // The user will set this up
        if (pusherAppId !== 'YOUR_KEY_HERE') {
            const pusher = new Pusher(pusherAppId, { cluster: 'ap2' });
            const channel = pusher.subscribe('delivery-partner-<?= $order['delivery_partner_id'] ?>');
            channel.bind('location_update', function(data) {
                console.log('Real-Time GPS Update Received via Pusher!');
                updateMap(data);
            });
        } else {
            document.getElementById('mapStatus').innerHTML = '<span class="text-danger"><i class="bi bi-exclamation-triangle"></i> Pusher API not configured yet. Map static.</span>';
        }

        // Fallback: Poll for location every 5 seconds if Pusher is unreliable/unconfigured
        setInterval(function() {
            fetch('<?= url('/order/location/' . $order['id']) ?>')
                .then(res => res.json())
                .then(data => {
                    if (data && data.latitude) {
                        updateMap(data);
                    }
                })
                .catch(err => console.error('Error polling location:', err));
        }, 5000);
    });
});
</script>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const currentDispatchStatus = <?= json_encode($order['dispatch_status'] ?? '') ?>;
    const currentOrderStatus = <?= json_encode($order['order_status'] ?? '') ?>;
    const currentDpId = <?= json_encode($order['delivery_partner_id'] ?? null) ?>;
    const isOrderFinal = <?= json_encode(in_array($order['order_status'], ['delivered', 'cancelled', 'delivery_failed'])) ?>;

    if (!isOrderFinal) {
        setInterval(function() {
            fetch('<?= url('/order/status/' . $order['id']) ?>', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                if (data && !data.error) {
                    if (data.dispatch_status !== currentDispatchStatus || 
                        data.order_status !== currentOrderStatus || 
                        data.delivery_partner_id !== currentDpId) {
                        window.location.reload();
                    }
                }
            })
            .catch(err => console.error(err));
        }, 3000);
    }
});
</script>

<?php View::partial('report_modal'); ?>


