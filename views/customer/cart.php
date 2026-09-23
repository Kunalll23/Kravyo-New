<!-- Phase 5: Shopping Cart Page -->
<section class="py-4">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h2 class="fw-800 mb-0">
                <i class="bi bi-cart3 me-2 text-warning"></i>Your Cart
            </h2>
            <?php if (!empty($cart['items'])): ?>
                <span class="badge bg-light text-dark px-3 py-2 rounded-pill fs-6">
                    <?= count($cart['items']) ?> item<?= count($cart['items']) !== 1 ? 's' : '' ?>
                    from <strong><?= sanitize($cart['kitchen_name'] ?? 'Unknown') ?></strong>
                </span>
            <?php endif; ?>
        </div>

        <?php if (empty($cart['items'])): ?>
            <!-- Empty Cart State -->
            <div class="empty-state text-center py-5">
                <div class="empty-state-icon mb-3">
                    <i class="bi bi-cart-x"></i>
                </div>
                <h4 class="fw-bold text-muted">Your Cart is Empty</h4>
                <p class="text-muted mb-4">Looks like you haven't added any delicious meals yet. Start exploring!</p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="<?= url('/menu') ?>" class="btn btn-kravyo-primary">
                        <i class="bi bi-egg-fried me-2"></i> Browse Dishes
                    </a>
                    <a href="<?= url('/kitchens') ?>" class="btn btn-kravyo-outline">
                        <i class="bi bi-shop me-2"></i> Explore Kitchens
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <!-- Cart Items -->
                <div class="col-lg-8">
                    <!-- Kitchen Header -->
                    <div class="cart-kitchen-header mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-shop text-warning"></i>
                            <span class="fw-bold">Ordering from:</span>
                            <a href="<?= url('/kitchen/' . $cart['kitchen_id']) ?>" class="text-decoration-none fw-bold text-dark">
                                <?= sanitize($cart['kitchen_name'] ?? 'Unknown Kitchen') ?>
                            </a>
                        </div>
                    </div>

                    <?php foreach ($cart['items'] as $itemKey => $item): ?>
                        <?php $isZeroWaste = !empty($item['is_zero_waste']); ?>
                        <div class="cart-item-card mb-3" id="cartItem_<?= sanitize($itemKey) ?>">
                            <div class="row g-0 align-items-center">
                                <!-- Item Image -->
                                <div class="col-auto">
                                    <div class="cart-item-img">
                                        <?php if (!empty($item['image'])): ?>
                                            <img src="<?= UPLOAD_URL . '/dishes/' . $item['image'] ?>"
                                                 alt="<?= sanitize($item['item_name']) ?>">
                                        <?php else: ?>
                                            <div class="cart-item-img-placeholder">
                                                <i class="bi bi-egg-fried"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Item Details -->
                                <div class="col">
                                    <div class="cart-item-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="fw-bold mb-1">
                                                    <?php if ($item['is_veg']): ?>
                                                        <i class="bi bi-circle-fill text-success me-1" style="font-size:0.6rem;"></i>
                                                    <?php else: ?>
                                                        <i class="bi bi-circle-fill text-danger me-1" style="font-size:0.6rem;"></i>
                                                    <?php endif; ?>
                                                    <?= sanitize($item['item_name']) ?>
                                                </h6>

                                                <!-- Tags / Badges -->
                                                <div class="d-flex flex-wrap gap-1 mb-2">
                                                    <?php if ($isZeroWaste): ?>
                                                        <span class="badge bg-success bg-opacity-15 text-success fw-semibold small px-2 py-1 border border-success border-opacity-25">
                                                            <i class="bi bi-recycle me-1"></i>Zero Waste Deal
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="cart-custom-tag">
                                                            🌶️ <?= sanitize($item['spice_level']) ?>
                                                        </span>
                                                        <span class="cart-custom-tag">
                                                            🫒 <?= sanitize($item['oil_level']) ?>
                                                        </span>
                                                        <?php if ($item['is_jain']): ?>
                                                            <span class="cart-custom-tag cart-custom-jain">
                                                                🌿 Jain Prep
                                                            </span>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <!-- Remove Button -->
                                            <form method="POST" action="<?= url('/cart/remove') ?>" class="cart-remove-form">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="item_key" value="<?= sanitize($itemKey) ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger border-0 cart-remove-btn"
                                                        title="Remove Item">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            </form>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center">
                                            <!-- Quantity Controls -->
                                            <div class="cart-qty-controls">
                                                <form method="POST" action="<?= url('/cart/update') ?>" class="d-inline cart-update-form">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="item_key" value="<?= sanitize($itemKey) ?>">
                                                    <input type="hidden" name="quantity" value="<?= max(0, $item['quantity'] - 1) ?>">
                                                    <button type="submit" class="qty-btn qty-minus-sm">
                                                        <i class="bi bi-dash"></i>
                                                    </button>
                                                </form>
                                                <span class="cart-qty-value"><?= $item['quantity'] ?></span>
                                                <form method="POST" action="<?= url('/cart/update') ?>" class="d-inline cart-update-form">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="item_key" value="<?= sanitize($itemKey) ?>">
                                                    <?php
                                                        $maxAllowed = $isZeroWaste ? (int)($item['max_qty'] ?? 50) : 10;
                                                    ?>
                                                    <input type="hidden" name="quantity" value="<?= min($maxAllowed, $item['quantity'] + 1) ?>">
                                                    <button type="submit" class="qty-btn qty-plus-sm" <?= $item['quantity'] >= $maxAllowed ? 'disabled' : '' ?>>
                                                        <i class="bi bi-plus"></i>
                                                    </button>
                                                </form>
                                            </div>

                                            <!-- Item Subtotal (with savings for zero-waste) -->
                                            <div class="cart-item-subtotal text-end">
                                                <?php if ($isZeroWaste && !empty($item['original_price'])): ?>
                                                    <small class="text-muted text-decoration-line-through d-block">
                                                        <?= format_currency($item['original_price']) ?> × <?= $item['quantity'] ?>
                                                    </small>
                                                    <div class="fw-bold text-success"><?= format_currency($item['price'] * $item['quantity']) ?></div>
                                                    <small class="text-success fw-semibold">
                                                        You save <?= format_currency(($item['original_price'] - $item['price']) * $item['quantity']) ?>
                                                    </small>
                                                <?php else: ?>
                                                    <small class="text-muted"><?= format_currency($item['price']) ?> × <?= $item['quantity'] ?></small>
                                                    <div class="fw-bold text-dark"><?= format_currency($item['price'] * $item['quantity']) ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>


                    <!-- Continue Shopping -->
                    <div class="mt-3">
                        <?php
                            // Detect if this cart is a zero-waste order
                            $cartHasZeroWaste = !empty(array_filter($cart['items'], fn($i) => !empty($i['is_zero_waste'])));
                        ?>
                        <?php if ($cartHasZeroWaste): ?>
                            <a href="<?= url('/zero-waste') ?>" class="btn btn-kravyo-outline btn-sm">
                                <i class="bi bi-recycle me-1"></i> Browse More Zero Waste Deals
                            </a>
                        <?php else: ?>
                            <a href="<?= url('/kitchen/' . $cart['kitchen_id']) ?>" class="btn btn-kravyo-outline btn-sm">
                                <i class="bi bi-plus-circle me-1"></i> Add More from <?= sanitize($cart['kitchen_name'] ?? 'this kitchen') ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Order Summary Sidebar -->
                <div class="col-lg-4">
                    <div class="cart-summary-card">
                        <h5 class="fw-bold mb-3"><i class="bi bi-receipt me-2"></i>Order Summary</h5>

                        <div class="cart-summary-rows">
                            <?php
                                $totalSavings = 0;
                                foreach ($cart['items'] as $item) {
                                    if (!empty($item['is_zero_waste']) && !empty($item['original_price'])) {
                                        $totalSavings += ($item['original_price'] - $item['price']) * $item['quantity'];
                                    }
                                }
                            ?>
                            <?php foreach ($cart['items'] as $item): ?>
                                <div class="d-flex justify-content-between small mb-2">
                                    <span class="text-muted">
                                        <?php if (!empty($item['is_zero_waste'])): ?>
                                            <i class="bi bi-recycle text-success me-1"></i>
                                        <?php endif; ?>
                                        <?= sanitize($item['item_name']) ?> × <?= $item['quantity'] ?>
                                    </span>
                                    <span class="fw-600"><?= format_currency($item['price'] * $item['quantity']) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <hr class="my-3">

                        <?php if ($totalSavings > 0): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-success small fw-semibold">
                                    <i class="bi bi-recycle me-1"></i>Zero Waste Savings
                                </span>
                                <span class="fw-600 text-success">−<?= format_currency($totalSavings) ?></span>
                            </div>
                        <?php endif; ?>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-bold fs-6">Grand Total</span>
                            <span class="fw-800 fs-5 text-dark" id="cartGrandTotal"><?= format_currency($cartTotal) ?></span>
                        </div>

                        <small class="text-muted d-block mb-3">
                            <i class="bi bi-info-circle me-1"></i> Delivery charges will be calculated at checkout.
                        </small>

                        <!-- Checkout Button (Phase 6) -->
                        <a href="<?= url('/checkout') ?>" class="btn btn-kravyo-primary btn-lg w-100">
                            <i class="bi bi-bag-check me-2"></i> Proceed to Checkout
                        </a>

                        <div class="text-center mt-3">
                            <a href="<?= url('/menu') ?>" class="small text-muted text-decoration-none">
                                <i class="bi bi-arrow-left me-1"></i> Continue Browsing
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
