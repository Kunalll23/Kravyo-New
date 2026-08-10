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

                                                <!-- Customization Tags -->
                                                <div class="d-flex flex-wrap gap-1 mb-2">
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
                                                    <input type="hidden" name="quantity" value="<?= min(10, $item['quantity'] + 1) ?>">
                                                    <button type="submit" class="qty-btn qty-plus-sm" <?= $item['quantity'] >= 10 ? 'disabled' : '' ?>>
                                                        <i class="bi bi-plus"></i>
                                                    </button>
                                                </form>
                                            </div>

                                            <!-- Item Subtotal -->
                                            <div class="cart-item-subtotal">
                                                <small class="text-muted"><?= format_currency($item['price']) ?> × <?= $item['quantity'] ?></small>
                                                <div class="fw-bold text-dark"><?= format_currency($item['price'] * $item['quantity']) ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Continue Shopping -->
                    <div class="mt-3">
                        <a href="<?= url('/kitchen/' . $cart['kitchen_id']) ?>" class="btn btn-kravyo-outline btn-sm">
                            <i class="bi bi-plus-circle me-1"></i> Add More from <?= sanitize($cart['kitchen_name'] ?? 'this kitchen') ?>
                        </a>
                    </div>
                </div>

                <!-- Order Summary Sidebar -->
                <div class="col-lg-4">
                    <div class="cart-summary-card">
                        <h5 class="fw-bold mb-3"><i class="bi bi-receipt me-2"></i>Order Summary</h5>

                        <div class="cart-summary-rows">
                            <?php foreach ($cart['items'] as $item): ?>
                                <div class="d-flex justify-content-between small mb-2">
                                    <span class="text-muted"><?= sanitize($item['item_name']) ?> × <?= $item['quantity'] ?></span>
                                    <span class="fw-600"><?= format_currency($item['price'] * $item['quantity']) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <hr class="my-3">

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
