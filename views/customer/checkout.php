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
                                    <label class="filter-label">Street Address *</label>
                                    <input type="text" name="new_street_address" class="form-control filter-input"
                                           placeholder="House no, building, street name...">
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

                            <label class="payment-method-card">
                                <input type="radio" name="payment_method" value="upi" class="payment-radio">
                                <div class="payment-card-inner">
                                    <div class="payment-icon upi-icon">
                                        <i class="bi bi-phone"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-0">UPI Payment</h6>
                                        <small class="text-muted">GPay, PhonePe, Paytm etc.</small>
                                    </div>
                                </div>
                                <div class="payment-check"><i class="bi bi-check-circle-fill"></i></div>
                            </label>
                        </div>
                    </div>

                    <!-- Special Instructions -->
                    <div class="checkout-section">
                        <h5 class="checkout-section-title">
                            <i class="bi bi-chat-left-text me-2 text-primary"></i>Special Instructions
                            <span class="text-muted fw-normal fs-6">(Optional)</span>
                        </h5>
                        <textarea name="special_instructions" class="form-control filter-input" rows="3"
                                  placeholder="Any special requests for the chef? e.g. Extra salt, no coriander..."
                                  maxlength="500"></textarea>
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
                            <?php foreach ($cart['items'] as $item): ?>
                                <div class="checkout-item d-flex justify-content-between align-items-start mb-2">
                                    <div class="flex-grow-1">
                                        <div class="small fw-600">
                                            <?php if ($item['is_veg']): ?>
                                                <i class="bi bi-circle-fill text-success me-1" style="font-size:0.5rem;"></i>
                                            <?php else: ?>
                                                <i class="bi bi-circle-fill text-danger me-1" style="font-size:0.5rem;"></i>
                                            <?php endif; ?>
                                            <?= sanitize($item['item_name']) ?> × <?= $item['quantity'] ?>
                                        </div>
                                        <div class="d-flex gap-1 mt-1">
                                            <span class="cart-custom-tag">🌶️ <?= sanitize($item['spice_level']) ?></span>
                                            <span class="cart-custom-tag">🫒 <?= sanitize($item['oil_level']) ?></span>
                                            <?php if ($item['is_jain']): ?>
                                                <span class="cart-custom-tag cart-custom-jain">🌿 Jain</span>
                                            <?php endif; ?>
                                        </div>
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
