<!-- Phase 6: Checkout Page — Address Selection & Payment Method -->
<?php
    // Kitchen GPS injected for frontend 15 km delivery range check
    $checkoutKitchenLat = isset($kitchenLat) ? (float)$kitchenLat : null;
    $checkoutKitchenLng = isset($kitchenLng) ? (float)$kitchenLng : null;
    $maxDeliveryKm = 15;
?>
<section class="py-4">
    <div class="container">
        <h2 class="fw-800 mb-4">
            <i class="bi bi-bag-check me-2 text-warning"></i>Checkout
        </h2>

        <form method="POST" action="<?= url('/order/place') ?>" id="checkoutForm">
            <?= csrf_field() ?>

            <!-- Delivery Range Warning Banner -->
            <div id="deliveryRangeAlert" class="alert alert-danger d-none mb-4 fw-semibold" role="alert" style="border-left: 4px solid #dc3545;">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <span id="deliveryRangeMsg">Your delivery address is too far from the kitchen.</span>
            </div>
            <div id="deliveryRangeOk" class="alert alert-success d-none mb-3 py-2 small" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <span id="deliveryRangeOkMsg">Delivery available!</span>
            </div>

            <div class="row g-4">
                <!-- Left: Address & Payment -->
                <div class="col-lg-7">
                    <!-- Delivery Address Section -->
                    <div class="checkout-section mb-4">
                        <h5 class="checkout-section-title">
                            <i class="bi bi-geo-alt-fill text-danger me-2"></i>Delivery Address
                        </h5>

                        <?php if (!empty($addresses)): ?>
                            <!-- Saved Addresses -->
                            <div class="saved-addresses mb-3">
                                <?php foreach ($addresses as $i => $addr): ?>
                                    <label class="address-card <?= $i === 0 ? 'selected' : '' ?>">
                                        <input type="radio" name="address_id" value="<?= $addr['id'] ?>"
                                               class="address-radio" <?= $i === 0 ? 'checked' : '' ?>>
                                        <div class="address-card-inner">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <span class="badge bg-light text-dark">
                                                    <i class="bi bi-<?= $addr['address_type'] === 'Home' ? 'house' : ($addr['address_type'] === 'Work' ? 'building' : 'pin-map') ?> me-1"></i>
                                                    <?= sanitize($addr['address_type']) ?>
                                                </span>
                                            </div>
                                            <p class="mb-0 small fw-600"><?= sanitize($addr['street_address']) ?></p>
                                            <?php if (!empty($addr['landmark'])): ?>
                                                <p class="mb-0 small text-muted">Near: <?= sanitize($addr['landmark']) ?></p>
                                            <?php endif; ?>
                                            <p class="mb-0 small text-muted">
                                                <?= sanitize($addr['city']) ?> — <?= sanitize($addr['pincode']) ?>
                                            </p>
                                        </div>
                                        <div class="address-check"><i class="bi bi-check-circle-fill"></i></div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Add New Address Toggle -->
                        <button type="button" class="btn btn-outline-secondary btn-sm mb-3" id="toggleNewAddress">
                            <i class="bi bi-plus-circle me-1"></i> Add New Address
                        </button>

                        <!-- New Address Form (hidden by default) -->
                        <div class="new-address-form d-none" id="newAddressForm">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="filter-label">Address Type</label>
                                    <select name="new_address_type" class="form-select filter-input">
                                        <option value="Home">🏠 Home</option>
                                        <option value="Work">🏢 Work</option>
                                        <option value="Other">📍 Other</option>
                                    </select>
                                </div>
                                <div class="col-md-8">
                                    <label class="filter-label">Apartment No / Room No *</label>
                                    <input type="text" name="new_apartment" id="new_apartment" class="form-control filter-input"
                                           placeholder="e.g. Flat 402, Block B / Room 12">
                                </div>
                                <div class="col-12">
                                    <label class="filter-label">Street Address *</label>
                                    <input type="text" name="new_street_address" id="new_street_address" class="form-control filter-input"
                                           placeholder="Search society, building name, street, area...">
                                </div>
                                <div class="col-md-4">
                                    <label class="filter-label">Landmark</label>
                                    <input type="text" name="new_landmark" class="form-control filter-input"
                                           placeholder="Near...">
                                </div>
                                <div class="col-md-4">
                                    <label class="filter-label">City *</label>
                                    <input type="text" name="new_city" class="form-control filter-input"
                                           placeholder="e.g. Surat">
                                </div>
                                <div class="col-md-4">
                                    <label class="filter-label">Pincode *</label>
                                    <input type="text" name="new_pincode" class="form-control filter-input"
                                           placeholder="e.g. 395007" maxlength="6">
                                </div>
                            </div>
                            
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="filter-label mb-0"><i class="bi bi-pin-map-fill text-danger me-1"></i>Pin your exact location on the map *</label>
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="btnCustomerDetectLoc">
                                            <i class="bi bi-crosshair me-1"></i> Detect My Location
                                        </button>
                                    </div>
                                    <p class="small text-muted mb-2">Drag the marker to your exact building to help our delivery partners find you.</p>
                                    
                                    <!-- Google Maps integration -->
                                    <?php include __DIR__ . '/../partials/google_maps_loader.php'; ?>
                                    
                                    <div id="checkoutMap" style="height: 250px; border-radius: 8px; border: 2px solid #dee2e6; z-index: 1;"></div>
                                    <input type="hidden" name="delivery_lat" id="delivery_lat">
                                    <input type="hidden" name="delivery_lng" id="delivery_lng">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method Section -->
                    <div class="checkout-section mb-4">
                        <h5 class="checkout-section-title">
                            <i class="bi bi-credit-card-fill text-success me-2"></i>Payment Method
                        </h5>

                        <div class="payment-methods">
                            <label class="payment-method-card selected">
                                <input type="radio" name="payment_method" value="cod" class="payment-radio" checked>
                                <div class="payment-card-inner">
                                    <div class="payment-icon">
                                        <i class="bi bi-cash-stack"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-0">Cash on Delivery</h6>
                                        <small class="text-muted">Pay when your food arrives</small>
                                    </div>
                                </div>
                                <div class="payment-check"><i class="bi bi-check-circle-fill"></i></div>
                            </label>

                            <label class="payment-method-card" id="upiQrLabel">
                                <input type="radio" name="payment_method" value="upi" class="payment-radio" id="upiQrRadio">
                                <div class="payment-card-inner">
                                    <div class="payment-icon upi-icon">
                                        <i class="bi bi-qr-code-scan"></i>
                                    </div>
                                    <div>
                                        <div class="d-flex align-items-center gap-2">
                                            <h6 class="fw-bold mb-0">Scan & Pay via UPI QR</h6>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0" style="font-size:0.68rem;">Test Mode (₹0)</span>
                                        </div>
                                        <small class="text-muted">GPay, PhonePe, Paytm, BHIM</small>
                                    </div>
                                </div>
                                <div class="payment-check"><i class="bi bi-check-circle-fill"></i></div>
                            </label>

                            <label class="payment-method-card" id="razorpayLabel">
                                <input type="radio" name="payment_method" value="online" class="payment-radio" id="razorpayRadio">
                                <div class="payment-card-inner">
                                    <div class="payment-icon text-primary">
                                        <i class="bi bi-credit-card"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-0">Pay Online (Razorpay)</h6>
                                        <small class="text-muted">Cards, NetBanking, Gateway</small>
                                    </div>
                                </div>
                                <div class="payment-check"><i class="bi bi-check-circle-fill"></i></div>
                            </label>
                        </div>
                    </div>


                </div>

                <!-- Right: Order Summary -->
                <div class="col-lg-5">
                    <div class="cart-summary-card checkout-summary">
                        <h5 class="fw-bold mb-3"><i class="bi bi-receipt me-2"></i>Order Summary</h5>

                        <!-- Kitchen Info -->
                        <div class="checkout-kitchen-badge mb-3">
                            <i class="bi bi-shop text-warning me-1"></i>
                            <span class="fw-600"><?= sanitize($cart['kitchen_name'] ?? 'Kitchen') ?></span>
                        </div>

                        <!-- Order Items -->
                        <div class="cart-summary-rows mb-3">
                            <?php
                                $checkoutSavings = 0;
                                foreach ($cart['items'] as $item) {
                                    if (!empty($item['is_zero_waste']) && !empty($item['original_price'])) {
                                        $checkoutSavings += ($item['original_price'] - $item['price']) * $item['quantity'];
                                    }
                                }
                            ?>
                            <?php foreach ($cart['items'] as $item): ?>
                                <div class="checkout-item d-flex justify-content-between align-items-start mb-2">
                                    <div class="flex-grow-1">
                                        <div class="small fw-600">
                                            <?php if ($item['is_veg']): ?>
                                                <i class="bi bi-circle-fill text-success me-1" style="font-size:0.5rem;"></i>
                                            <?php else: ?>
                                                <i class="bi bi-circle-fill text-danger me-1" style="font-size:0.5rem;"></i>
                                            <?php endif; ?>
                                            <?php if (!empty($item['is_zero_waste'])): ?>
                                                <i class="bi bi-recycle text-success me-1" title="Zero Waste Deal"></i>
                                            <?php endif; ?>
                                            <?= sanitize($item['item_name']) ?> × <?= $item['quantity'] ?>
                                        </div>
                                        <?php if (!empty($item['is_zero_waste'])): ?>
                                            <div class="d-flex gap-1 mt-1">
                                                <span class="badge bg-success bg-opacity-10 text-success" style="font-size:0.65rem;">
                                                    <i class="bi bi-recycle me-1"></i>Zero Waste Deal
                                                </span>
                                            </div>
                                        <?php else: ?>
                                            <div class="d-flex gap-1 mt-1">
                                                <span class="cart-custom-tag">🌶️ <?= sanitize($item['spice_level']) ?></span>
                                                <span class="cart-custom-tag">🫒 <?= sanitize($item['oil_level']) ?></span>
                                                <?php if ($item['is_jain']): ?>
                                                    <span class="cart-custom-tag cart-custom-jain">🌿 Jain</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($item['item_notes'])): ?>
                                            <div class="mt-1 small text-muted fst-italic" style="font-size: 0.75rem;">
                                                <i class="bi bi-chat-text text-primary"></i> "<?= sanitize($item['item_notes']) ?>"
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <span class="fw-600 small"><?= format_currency($item['price'] * $item['quantity']) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <hr class="my-3">

                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted small">Subtotal</span>
                            <span class="fw-600"><?= format_currency($cartTotal) ?></span>
                        </div>
                        <?php if ($checkoutSavings > 0): ?>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-success small fw-semibold">
                                    <i class="bi bi-recycle me-1"></i>Zero Waste Savings
                                </span>
                                <span class="fw-600 text-success">−<?= format_currency($checkoutSavings) ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted small">Delivery</span>
                            <span class="fw-600 text-success">Free</span>
                        </div>

                        <hr class="my-2">

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <span class="fw-bold fs-6">Total to Pay</span>
                            <span class="fw-800 fs-5 text-dark"><?= format_currency($cartTotal) ?></span>
                        </div>

                        <button type="submit" class="btn btn-kravyo-primary btn-lg w-100" id="placeOrderBtn">
                            <i class="bi bi-check2-circle me-2"></i> Place Order — <?= format_currency($cartTotal) ?>
                        </button>

                        <div class="text-center mt-3">
                            <a href="<?= url('/cart') ?>" class="small text-muted text-decoration-none">
                                <i class="bi bi-arrow-left me-1"></i> Back to Cart
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<!-- Kravyo UPI QR Code Payment Modal (Test Sandbox Mode) -->
<div class="modal fade" id="kravyoUpiModal" tabindex="-1" aria-labelledby="kravyoUpiModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-dark text-white border-0 px-4 py-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1" style="font-size:0.75rem;">
                        <i class="bi bi-shield-check me-1"></i>NPCI Sandbox
                    </span>
                    <h5 class="modal-title fw-bold mb-0" id="kravyoUpiModalLabel">Scan UPI QR to Pay</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" id="closeUpiModalBtn" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4 text-center">
                <!-- Sandbox Test Notice -->
                <div class="alert alert-warning border-0 rounded-3 text-start small mb-3 py-2 px-3 d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle-fill text-warning fs-5 flex-shrink-0"></i>
                    <div>
                        <strong class="d-block">Test Mode Active (₹0 Deducted)</strong>
                        <span>Scan with any UPI app to inspect the payload, or click simulate to complete the test order safely.</span>
                    </div>
                </div>

                <!-- Amount & Order Number -->
                <div class="bg-light rounded-3 p-3 mb-3 border">
                    <div class="text-muted small">Payable Amount</div>
                    <div class="fw-800 fs-3 text-dark" id="modalUpiAmount">₹0.00</div>
                    <div class="small text-muted mt-1">
                        Order #<span id="modalUpiOrderNumber" class="fw-bold">-</span> • <span class="badge bg-primary bg-opacity-10 text-primary">Kravyo Foods</span>
                    </div>
                </div>

                <!-- QR Code Box -->
                <div class="position-relative d-inline-block p-3 rounded-4 bg-white border shadow-sm mb-3">
                    <img id="modalUpiQrImage" src="" alt="UPI QR Code" width="220" height="220" class="img-fluid rounded-3" style="display:block; min-width:220px; min-height:220px; object-fit:contain;">
                    <div id="modalUpiQrSpinner" class="position-absolute top-50 start-50 translate-middle">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading QR...</span>
                        </div>
                    </div>
                </div>

                <!-- UPI VPA & Timer -->
                <div class="mb-3">
                    <div class="small text-muted mb-1">UPI ID: <strong id="modalUpiVpa" class="text-dark">kravyo.foods@upi</strong></div>
                    <div class="small text-muted">
                        <i class="bi bi-clock-history me-1 text-danger"></i> QR Expires in: <span id="modalUpiTimer" class="fw-bold text-danger">05:00</span>
                    </div>
                </div>

                <!-- Supported Apps Icons / Badges -->
                <div class="d-flex justify-content-center align-items-center gap-2 mb-3 text-muted small">
                    <span class="badge bg-light text-dark border">Google Pay</span>
                    <span class="badge bg-light text-dark border">PhonePe</span>
                    <span class="badge bg-light text-dark border">Paytm</span>
                    <span class="badge bg-light text-dark border">BHIM UPI</span>
                </div>

                <!-- Simulation Action Button -->
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-success btn-lg fw-bold shadow-sm" id="btnSimulateUpiSuccess">
                        <i class="bi bi-check2-circle me-1"></i> Simulate Payment Success
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                        Cancel & Return
                    </button>
                </div>
            </div>
            
            <div class="modal-footer bg-light border-0 py-2 justify-content-center">
                <small class="text-muted"><i class="bi bi-shield-lock-fill me-1"></i> 100% Safe Demo • No Real Money Debited</small>
            </div>
        </div>
    </div>
</div>

<!-- Razorpay Integration Script -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
// ── Kitchen GPS & 15 km delivery limit ───────────────────────────────────────
const KITCHEN_LAT = <?= json_encode($checkoutKitchenLat) ?>;
const KITCHEN_LNG = <?= json_encode($checkoutKitchenLng) ?>;
const MAX_DELIVERY_KM = <?= (int)$maxDeliveryKm ?>;

/** Haversine formula — returns distance in km between two lat/lng points */
function haversineKm(lat1, lng1, lat2, lng2) {
    const R = 6371;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLng = (lng2 - lng1) * Math.PI / 180;
    const a = Math.sin(dLat/2)**2 +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLng/2)**2;
    return R * 2 * Math.asin(Math.sqrt(a));
}

/**
 * Checks distance from kitchen to deliveryLat/deliveryLng.
 * Shows warning banner and disables/enables the place order button.
 * @param {number} deliveryLat
 * @param {number} deliveryLng
 * @param {string} [addressLabel] - human-readable label for message
 */
function checkDeliveryRange(deliveryLat, deliveryLng, addressLabel) {
    const alertEl   = document.getElementById('deliveryRangeAlert');
    const alertMsg  = document.getElementById('deliveryRangeMsg');
    const okEl      = document.getElementById('deliveryRangeOk');
    const okMsg     = document.getElementById('deliveryRangeOkMsg');
    const placeBtn  = document.getElementById('placeOrderBtn');

    if (!KITCHEN_LAT || !KITCHEN_LNG || !deliveryLat || !deliveryLng) {
        if (alertEl) alertEl.classList.add('d-none');
        if (okEl) okEl.classList.add('d-none');
        return;
    }

    const distKm = haversineKm(KITCHEN_LAT, KITCHEN_LNG, parseFloat(deliveryLat), parseFloat(deliveryLng));
    const distStr = distKm.toFixed(1) + ' km';

    if (distKm > MAX_DELIVERY_KM) {
        // Too far — show error, disable button
        if (alertEl) {
            alertEl.classList.remove('d-none');
            alertMsg.textContent = '🚫 Delivery not available — your address is ' + distStr +
                ' away from the kitchen. We only deliver within ' + MAX_DELIVERY_KM + ' km.';
        }
        if (okEl) okEl.classList.add('d-none');
        if (placeBtn) {
            placeBtn.disabled = true;
            placeBtn.classList.add('btn-secondary');
            placeBtn.classList.remove('btn-kravyo-primary');
        }
    } else {
        // Within range — clear error, enable button
        if (alertEl) alertEl.classList.add('d-none');
        if (okEl) {
            okEl.classList.remove('d-none');
            okMsg.textContent = '✅ Delivery available! Kitchen is ' + distStr + ' away.';
        }
        if (placeBtn) {
            placeBtn.disabled = false;
            placeBtn.classList.remove('btn-secondary');
            placeBtn.classList.add('btn-kravyo-primary');
        }
    }
}

// ── Address radio selection listener ─────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    const addressRadios = document.querySelectorAll('input[name="address_id"]');
    const savedAddressData = <?= json_encode(array_map(function($a) {
        return [
            'id'  => $a['id'],
            'lat' => isset($a['latitude'])  ? (float)$a['latitude']  : null,
            'lng' => isset($a['longitude']) ? (float)$a['longitude'] : null,
            'label' => ($a['address_type'] ?? 'Address') . ' — ' . ($a['city'] ?? '')
        ];
    }, $addresses ?? [])) ?>;

    function getAddressById(id) {
        return savedAddressData.find(a => String(a.id) === String(id)) || null;
    }

    // Check currently selected address on page load
    const checkedRadio = document.querySelector('input[name="address_id"]:checked');
    if (checkedRadio) {
        const addrData = getAddressById(checkedRadio.value);
        if (addrData && addrData.lat && addrData.lng) {
            checkDeliveryRange(addrData.lat, addrData.lng, addrData.label);
        }
    }

    // Re-check when address selection changes
    addressRadios.forEach(function(radio) {
        radio.addEventListener('change', function() {
            const addrData = getAddressById(this.value);
            if (addrData && addrData.lat && addrData.lng) {
                checkDeliveryRange(addrData.lat, addrData.lng, addrData.label);
            } else {
                // Address has no GPS — hide banners
                const alertEl = document.getElementById('deliveryRangeAlert');
                const okEl    = document.getElementById('deliveryRangeOk');
                if (alertEl) alertEl.classList.add('d-none');
                if (okEl) okEl.classList.add('d-none');
            }
        });
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkoutForm = document.getElementById('checkoutForm');
    
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            // Prevent double-submit
            if (checkoutForm.dataset.submitted === '1') {
                e.preventDefault();
                return false;
            }

            // Block submit if order button is disabled (distance check failed)
            const btn = document.getElementById('placeOrderBtn');
            if (btn && btn.disabled) {
                e.preventDefault();
                const alertEl = document.getElementById('deliveryRangeAlert');
                if (alertEl) alertEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }

            const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
            
            if (paymentMethod === 'upi') {
                e.preventDefault(); // Stop normal form submission

                // Validate address before proceeding
                const addressId = document.querySelector('input[name="address_id"]:checked');
                const newStreet = document.querySelector('input[name="new_street_address"]');
                if (!addressId && (!newStreet || newStreet.value.trim() === '')) {
                    alert('Please select or add a delivery address first.');
                    return;
                }

                checkoutForm.dataset.submitted = '1';
                const originalBtnHtml = btn.innerHTML;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Generating UPI QR...';
                btn.disabled = true;

                const formData = new FormData(checkoutForm);
                formData.append('is_ajax', '1');

                fetch("<?= url('/order/place') ?>", {
                    method: "POST",
                    body: formData,
                    headers: {
                        "X-Requested-With": "XMLHttpRequest"
                    }
                })
                .then(async res => {
                    let data;
                    try {
                        data = await res.json();
                    } catch (e) {
                        data = { success: false, message: 'Server returned an invalid response.' };
                    }
                    btn.innerHTML = originalBtnHtml;
                    btn.disabled = false;
                    checkoutForm.dataset.submitted = '0';

                    if (data && data.success && data.kravyo_order_id) {
                        openUpiQrModal(data);
                    } else {
                        alert((data && data.message) ? data.message : 'Failed to initiate UPI payment.');
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert("Network error: " + (err.message || 'Error connecting to server.'));
                    btn.innerHTML = originalBtnHtml;
                    btn.disabled = false;
                    checkoutForm.dataset.submitted = '0';
                });
            } else if (paymentMethod === 'online') {
                e.preventDefault(); // Stop normal form submission
                
                // Validate address before proceeding
                const addressId = document.querySelector('input[name="address_id"]:checked');
                const newStreet = document.querySelector('input[name="new_street_address"]');
                if (!addressId && (!newStreet || newStreet.value.trim() === '')) {
                    alert('Please select or add a delivery address first.');
                    return;
                }

                checkoutForm.dataset.submitted = '1';
                // Show loading state on button
                const originalBtnHtml = btn.innerHTML;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Initiating Payment...';
                btn.disabled = true;

                const formData = new FormData(checkoutForm);
                formData.append('is_ajax', '1'); // Tell backend this is an AJAX call

                fetch("<?= url('/order/place') ?>", {
                    method: "POST",
                    body: formData,
                    headers: {
                        "X-Requested-With": "XMLHttpRequest"
                    }
                })
                .then(res => res.json())
                .then(data => {
                    btn.innerHTML = originalBtnHtml;
                    btn.disabled = false;
                    checkoutForm.dataset.submitted = '0';

                    if (data.success && data.razorpay_order_id) {
                        // Open Razorpay Popup
                        var options = {
                            "key": "<?= RAZORPAY_KEY_ID ?>", 
                            "amount": data.razorpay_amount, 
                            "currency": "INR",
                            "name": "Kravyo Foods",
                            "description": "Order #" + data.order_number,
                            "image": "https://cdn-icons-png.flaticon.com/512/3170/3170733.png",
                            "order_id": data.razorpay_order_id, 
                            "handler": function (response) {
                                // On Success: Verify on backend
                                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Verifying Payment...';
                                btn.disabled = true;

                                const csrfVal = document.querySelector('input[name="_csrf"]')?.value || "<?= csrf_token() ?>";
                                const verifyForm = new FormData();
                                verifyForm.append('razorpay_payment_id', response.razorpay_payment_id);
                                verifyForm.append('razorpay_order_id', response.razorpay_order_id);
                                verifyForm.append('razorpay_signature', response.razorpay_signature);
                                verifyForm.append('kravyo_order_id', data.kravyo_order_id);
                                verifyForm.append('_csrf', csrfVal);
                                verifyForm.append('csrf_token', csrfVal);
                                
                                fetch("<?= url('/order/verify-payment') ?>", {
                                    method: "POST",
                                    headers: {
                                        'X-Requested-With': 'XMLHttpRequest',
                                        'X-CSRF-TOKEN': csrfVal
                                    },
                                    body: verifyForm
                                })
                                .then(async res => {
                                    let vData;
                                    try {
                                        vData = await res.json();
                                    } catch(e) {
                                        vData = { success: false, message: 'Server returned non-JSON response.' };
                                    }
                                    if(vData && vData.success) {
                                        window.location.href = "<?= url('/order/track') ?>/" + data.kravyo_order_id;
                                    } else {
                                        alert((vData && vData.message) ? vData.message : 'Payment verification failed.');
                                        btn.innerHTML = originalBtnHtml;
                                        btn.disabled = false;
                                    }
                                }).catch(err => {
                                    console.error('Verify error:', err);
                                    alert("Server error during verification.");
                                    btn.innerHTML = originalBtnHtml;
                                    btn.disabled = false;
                                });
                            },
                            "prefill": {
                                "name": "<?= sanitize(Session::get('user_name') ?? '') ?>",
                                "email": "<?= sanitize(Session::get('user_email') ?? 'customer@example.com') ?>",
                                "contact": ""
                            },
                            "theme": {
                                "color": "#ff6b6b"
                            }
                        };
                        var rzp1 = new Razorpay(options);
                        rzp1.on('payment.failed', function (response){
                            alert(response.error.description);
                        });
                        rzp1.open();
                    } else {
                        alert(data.message || 'Failed to initiate payment.');
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert("Error connecting to server.");
                    btn.innerHTML = originalBtnHtml;
                    btn.disabled = false;
                    checkoutForm.dataset.submitted = '0';
                });
            } else {
                // COD Flow: Lock button and form to prevent duplicate orders
                checkoutForm.dataset.submitted = '1';
                btn.style.pointerEvents = 'none';
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Placing Order...';
                setTimeout(function() {
                    btn.disabled = true;
                }, 0);
            }
        });
    }

    // ── UPI QR Modal Logic & Simulation ───────────────────────────────────
    let upiTimerInterval = null;
    let currentUpiOrderId = null;
    let upiModalInstance = null;

    function openUpiQrModal(data) {
        currentUpiOrderId = data.kravyo_order_id;
        document.getElementById('modalUpiAmount').textContent = data.formatted_amount || ('₹' + parseFloat(data.amount).toFixed(2));
        document.getElementById('modalUpiOrderNumber').textContent = data.order_number;
        document.getElementById('modalUpiVpa').textContent = data.upi_vpa || 'kravyo.foods@upi';

        const qrImg = document.getElementById('modalUpiQrImage');
        const qrSpinner = document.getElementById('modalUpiQrSpinner');
        if (qrSpinner) qrSpinner.style.display = 'block';

        const qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&margin=4&data=' + encodeURIComponent(data.upi_string);
        qrImg.onload = function() {
            if (qrSpinner) qrSpinner.style.display = 'none';
        };
        qrImg.src = qrUrl;

        // Reset & start 5 min countdown
        let timeLeft = 300;
        const timerEl = document.getElementById('modalUpiTimer');
        if (upiTimerInterval) clearInterval(upiTimerInterval);

        function updateTimer() {
            const m = Math.floor(timeLeft / 60);
            const s = timeLeft % 60;
            timerEl.textContent = (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
            if (timeLeft <= 0) {
                clearInterval(upiTimerInterval);
                timerEl.textContent = 'Expired';
                const simBtn = document.getElementById('btnSimulateUpiSuccess');
                if (simBtn) simBtn.disabled = true;
            }
            timeLeft--;
        }
        updateTimer();
        upiTimerInterval = setInterval(updateTimer, 1000);

        const simBtn = document.getElementById('btnSimulateUpiSuccess');
        if (simBtn) {
            simBtn.disabled = false;
            simBtn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Simulate Payment Success';
        }

        const modalEl = document.getElementById('kravyoUpiModal');
        upiModalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        upiModalInstance.show();
    }

    // Handle Simulate Payment Success Button Click
    const simSuccessBtn = document.getElementById('btnSimulateUpiSuccess');
    if (simSuccessBtn) {
        simSuccessBtn.addEventListener('click', function() {
            if (!currentUpiOrderId) return;
            const simBtn = this;
            const originalText = simBtn.innerHTML;
            simBtn.disabled = true;
            simBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Confirming ₹0 Sandbox Payment...';

            const csrfVal = document.querySelector('input[name="_csrf"]')?.value || "<?= csrf_token() ?>";
            const postData = new FormData();
            postData.append('kravyo_order_id', currentUpiOrderId);
            postData.append('_csrf', csrfVal);
            postData.append('csrf_token', csrfVal);

            fetch("<?= url('/order/verify-upi') ?>", {
                method: "POST",
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfVal
                },
                body: postData
            })
            .then(async res => {
                let vData;
                try {
                    vData = await res.json();
                } catch(e) {
                    vData = { success: false, message: 'Server returned non-JSON response.' };
                }
                if (vData && vData.success) {
                    window.location.href = vData.redirect || ("<?= url('/order/track') ?>/" + currentUpiOrderId);
                } else {
                    alert((vData && vData.message) ? vData.message : 'UPI verification failed.');
                    simBtn.innerHTML = originalText;
                    simBtn.disabled = false;
                }
            })
            .catch(err => {
                console.error('Verify UPI error:', err);
                alert('Server error during UPI verification.');
                simBtn.innerHTML = originalText;
                simBtn.disabled = false;
            });
        });
    }

    // Clear timer when modal is closed
    const modalEl = document.getElementById('kravyoUpiModal');
    if (modalEl) {
        modalEl.addEventListener('hidden.bs.modal', function () {
            if (upiTimerInterval) {
                clearInterval(upiTimerInterval);
                upiTimerInterval = null;
            }
        });
    }
});

document.addEventListener('DOMContentLoaded', function() {
    // Map Initialization
    const mapContainer = document.getElementById('checkoutMap');
    if (mapContainer && typeof KravyoMaps !== 'undefined') {
        KravyoMaps.init().then(() => {
            const latInput = document.getElementById('delivery_lat');
            const lngInput = document.getElementById('delivery_lng');
            
            // Default Surat
            const initialLat = 21.1702;
            const initialLng = 72.8311;
            
            latInput.value = initialLat;
            lngInput.value = initialLng;
            
            const map = KravyoMaps.createMap('checkoutMap', initialLat, initialLng, 14);
            const marker = KravyoMaps.addDraggableMarker(map, initialLat, initialLng, function(lat, lng) {
                latInput.value = lat.toFixed(8);
                lngInput.value = lng.toFixed(8);
                // Re-check distance when user drags the marker
                checkDeliveryRange(lat, lng, 'Pinned location');
            });
            
            // Attach Autocomplete
            KravyoMaps.initAutocomplete('new_street_address', map, marker, function(pos, place) {
                latInput.value = pos.lat.toFixed(8);
                lngInput.value = pos.lng.toFixed(8);
                checkDeliveryRange(pos.lat, pos.lng, place.name || 'Selected address');
                
                // Auto-fill form fields based on selected place
                if (place.address_components) {
                    let city = '';
                    let pincode = '';
                    
                    place.address_components.forEach(component => {
                        const types = component.types;
                        if (types.includes('locality')) {
                            city = component.long_name;
                        }
                        if (types.includes('postal_code')) {
                            pincode = component.long_name;
                        }
                    });
                    
                    const cityInput = document.querySelector('input[name="new_city"]');
                    const zipInput = document.querySelector('input[name="new_pincode"]');
                    const landmarkInput = document.querySelector('input[name="new_landmark"]');
                    
                    if (city && cityInput) cityInput.value = city;
                    if (pincode && zipInput) zipInput.value = pincode;
                    
                    if (place.name && landmarkInput) {
                        // Don't use the name if it's just the city name
                        if (place.name !== city) {
                            landmarkInput.value = place.name;
                        }
                    }
                }
            });
            
            // Allow clicking on map to move marker
            map.addListener('click', function(e) {
                const lat = e.latLng.lat();
                const lng = e.latLng.lng();
                marker.position = e.latLng;
                latInput.value = lat.toFixed(8);
                lngInput.value = lng.toFixed(8);
                checkDeliveryRange(lat, lng, 'Clicked location');
            });
            
            const detectBtn = document.getElementById('btnCustomerDetectLoc');
            if (detectBtn) {
                detectBtn.addEventListener('click', function() {
                    if (!navigator.geolocation) {
                        alert('Geolocation is not supported by your browser.');
                        return;
                    }
                    detectBtn.disabled = true;
                    detectBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Detecting...';
                    navigator.geolocation.getCurrentPosition(
                        function(pos) {
                            detectBtn.disabled = false;
                            detectBtn.innerHTML = '<i class="bi bi-crosshair me-1"></i> Detect My Location';
                            const uLat = pos.coords.latitude;
                            const uLng = pos.coords.longitude;
                            
                            const newPos = {lat: uLat, lng: uLng};
                            marker.position = newPos;
                            map.setCenter(newPos);
                            
                            latInput.value = uLat.toFixed(8);
                            lngInput.value = uLng.toFixed(8);
                            checkDeliveryRange(uLat, uLng, 'Your GPS location');
                            
                            // Perform Reverse Geocoding
                            if (KravyoMaps.getLibs && KravyoMaps.getLibs().Geocoder) {
                                const geocoder = new (KravyoMaps.getLibs().Geocoder)();
                                geocoder.geocode({ location: newPos }, function(results, status) {
                                    if (status === 'OK' && results[0]) {
                                        const place = results[0];
                                        const addressInput = document.querySelector('gmp-place-autocomplete');
                                        const cityInput = document.querySelector('input[name="new_city"]');
                                        const zipInput = document.querySelector('input[name="new_pincode"]');
                                        
                                        if (addressInput) {
                                            // The web component's internal input can sometimes be set via value property
                                            addressInput.value = place.formatted_address;
                                        } else {
                                            document.querySelector('input[name="new_street_address"]').value = place.formatted_address;
                                        }
                                        
                                        let city = '';
                                        let pincode = '';
                                        
                                        place.address_components.forEach(component => {
                                            const types = component.types;
                                            if (types.includes('locality')) {
                                                city = component.long_name;
                                            }
                                            if (types.includes('postal_code')) {
                                                pincode = component.long_name;
                                            }
                                        });
                                        
                                        if (city && cityInput) cityInput.value = city;
                                        if (pincode && zipInput) zipInput.value = pincode;
                                    }
                                });
                            }
                        },
                        function(err) {
                            detectBtn.disabled = false;
                            detectBtn.innerHTML = '<i class="bi bi-crosshair me-1"></i> Detect My Location';
                            alert('Could not get your location: ' + err.message + '. Please click on the map manually.');
                        },
                        { enableHighAccuracy: true, timeout: 10000 }
                    );
                });
            }

            // Fix map issue when unhiding the form
            const toggleBtn = document.getElementById('toggleNewAddress');
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    // Google Maps trigger resize
                    setTimeout(function() {
                        google.maps.event.trigger(map, 'resize');
                        map.setCenter(marker.position);
                    }, 100);
                });
            }
        });
    }
});
</script>

