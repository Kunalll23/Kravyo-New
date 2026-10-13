<!-- Phase 6: Checkout Page — Address Selection & Payment Method -->
<section class="py-4">
    <div class="container">
        <h2 class="fw-800 mb-4">
            <i class="bi bi-bag-check me-2 text-warning"></i>Checkout
        </h2>

        <form method="POST" action="<?= url('/order/place') ?>" id="checkoutForm">
            <?= csrf_field() ?>

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

                            <label class="payment-method-card" id="razorpayLabel">
                                <input type="radio" name="payment_method" value="online" class="payment-radio" id="razorpayRadio">
                                <div class="payment-card-inner">
                                    <div class="payment-icon text-primary">
                                        <i class="bi bi-credit-card"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-0">Pay Online (Razorpay)</h6>
                                        <small class="text-muted">Cards, UPI, NetBanking</small>
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

<!-- Razorpay Integration Script -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkoutForm = document.getElementById('checkoutForm');
    
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
            
            if (paymentMethod === 'online') {
                e.preventDefault(); // Stop normal form submission
                
                // Validate address before proceeding
                const addressId = document.querySelector('input[name="address_id"]:checked');
                const newStreet = document.querySelector('input[name="new_street_address"]');
                if (!addressId && (!newStreet || newStreet.value.trim() === '')) {
                    alert('Please select or add a delivery address first.');
                    return;
                }

                // Show loading state on button
                const btn = document.getElementById('placeOrderBtn');
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

                                const verifyForm = new FormData();
                                verifyForm.append('razorpay_payment_id', response.razorpay_payment_id);
                                verifyForm.append('razorpay_order_id', response.razorpay_order_id);
                                verifyForm.append('razorpay_signature', response.razorpay_signature);
                                verifyForm.append('kravyo_order_id', data.kravyo_order_id);
                                verifyForm.append('csrf_token', "<?= $_SESSION['csrf_token'] ?? '' ?>");
                                
                                fetch("<?= url('/order/verify-payment') ?>", {
                                    method: "POST",
                                    body: verifyForm
                                })
                                .then(res => res.json())
                                .then(vData => {
                                    if(vData.success) {
                                        window.location.href = "<?= url('/order/track/') ?>" + data.kravyo_order_id;
                                    } else {
                                        alert('Payment verification failed. If money was deducted, please contact support.');
                                        btn.innerHTML = originalBtnHtml;
                                        btn.disabled = false;
                                    }
                                }).catch(err => {
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
                });
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
            });
            
            // Attach Autocomplete
            KravyoMaps.initAutocomplete('new_street_address', map, marker, function(pos, place) {
                latInput.value = pos.lat.toFixed(8);
                lngInput.value = pos.lng.toFixed(8);
                
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

