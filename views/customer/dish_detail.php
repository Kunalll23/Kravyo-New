<!-- Phase 5: Single Dish Detail & Meal Customization Page -->
<section class="py-4">
    <div class="container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= url('/menu') ?>" class="text-decoration-none"><i class="bi bi-journal-text me-1"></i>Browse Dishes</a></li>
                <li class="breadcrumb-item"><a href="<?= url('/kitchen/' . $dish['kitchen_id']) ?>" class="text-decoration-none"><?= sanitize($dish['kitchen_name']) ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= sanitize($dish['item_name']) ?></li>
            </ol>
        </nav>

        <div class="row g-4">
            <!-- Dish Image & Info -->
            <div class="col-lg-6">
                <div class="dish-detail-image-wrap">
                    <?php if (!empty($dish['image'])): ?>
                        <img src="<?= UPLOAD_URL . '/dishes/' . $dish['image'] ?>"
                             alt="<?= sanitize($dish['item_name']) ?>"
                             class="dish-detail-img">
                    <?php else: ?>
                        <div class="dish-detail-placeholder">
                            <i class="bi bi-egg-fried"></i>
                            <span>No Image Available</span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Kitchen Info Card -->
                <div class="kitchen-info-card mt-3">
                    <a href="<?= url('/kitchen/' . $dish['kitchen_id']) ?>" class="text-decoration-none">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-circle" style="width:48px;height:48px;font-size:1.2rem;">
                                <?= strtoupper(substr($dish['chef_name'], 0, 1)) ?>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark"><?= sanitize($dish['kitchen_name']) ?></h6>
                                <small class="text-muted">
                                    <i class="bi bi-person me-1"></i>Chef <?= sanitize($dish['chef_name']) ?>
                                    <span class="mx-1">•</span>
                                    <i class="bi bi-geo-alt me-1"></i><?= sanitize($dish['city']) ?>
                                </small>
                            </div>
                            <div class="ms-auto">
                                <?php if ($dish['hygiene_badge'] === 'verified'): ?>
                                    <span class="badge badge-hygiene"><i class="bi bi-shield-check me-1"></i>Verified</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Dish Details & Customization Form -->
            <div class="col-lg-6">
                <div class="dish-detail-info">
                    <!-- Category & Dietary badges -->
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                        <span class="badge bg-light text-dark rounded-pill px-3 py-2">
                            <i class="bi bi-tag me-1"></i><?= sanitize($dish['category_name']) ?>
                        </span>
                        <?php if ($dish['is_veg']): ?>
                            <span class="dietary-badge dietary-veg"><i class="bi bi-circle-fill me-1"></i>Veg</span>
                        <?php else: ?>
                            <span class="dietary-badge dietary-nonveg"><i class="bi bi-circle-fill me-1"></i>Non-Veg</span>
                        <?php endif; ?>
                        <?php if ($dish['is_jain_available']): ?>
                            <span class="dietary-badge dietary-jain"><i class="bi bi-flower1 me-1"></i>Jain Available</span>
                        <?php endif; ?>
                        <?php if ($dish['is_diabetic_friendly']): ?>
                            <span class="dietary-badge dietary-diabetic"><i class="bi bi-heart-pulse me-1"></i>Diabetic Friendly</span>
                        <?php endif; ?>
                    </div>

                    <h2 class="fw-800 mb-2"><?= sanitize($dish['item_name']) ?></h2>

                    <?php if (!empty($dish['description'])): ?>
                        <p class="text-muted mb-3"><?= nl2br(sanitize($dish['description'])) ?></p>
                    <?php endif; ?>

                    <div class="dish-detail-price mb-4">
                        <?= format_currency($dish['price']) ?>
                    </div>

                    <!-- Customization & Add to Cart Form -->
                    <?php if ($dish['is_available'] && $dish['is_open']): ?>
                        <form method="POST" action="<?= url('/cart/add') ?>" id="addToCartForm" class="customization-form">
                            <?= csrf_field() ?>
                            <input type="hidden" name="menu_item_id" value="<?= $dish['id'] ?>">

                            <h6 class="fw-bold mb-3 customization-heading">
                                <i class="bi bi-sliders me-1"></i> Customize Your Meal
                            </h6>

                            <!-- Spice Level -->
                            <div class="customization-group mb-3">
                                <label class="customization-label">
                                    <i class="bi bi-fire me-1 text-danger"></i> Spice Level
                                </label>
                                <div class="customization-options">
                                    <label class="customization-option">
                                        <input type="radio" name="spice_level" value="Low" class="btn-check" id="spiceLow">
                                        <span class="customization-btn spice-low">🌶️ Low</span>
                                    </label>
                                    <label class="customization-option">
                                        <input type="radio" name="spice_level" value="Medium" class="btn-check" id="spiceMed" checked>
                                        <span class="customization-btn spice-medium">🌶️🌶️ Medium</span>
                                    </label>
                                    <label class="customization-option">
                                        <input type="radio" name="spice_level" value="High" class="btn-check" id="spiceHigh">
                                        <span class="customization-btn spice-high">🌶️🌶️🌶️ High</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Oil Level -->
                            <div class="customization-group mb-3">
                                <label class="customization-label">
                                    <i class="bi bi-droplet me-1 text-warning"></i> Oil Level
                                </label>
                                <div class="customization-options">
                                    <label class="customization-option">
                                        <input type="radio" name="oil_level" value="Normal" class="btn-check" id="oilNormal" checked>
                                        <span class="customization-btn">Normal Oil</span>
                                    </label>
                                    <label class="customization-option">
                                        <input type="radio" name="oil_level" value="Less Oil" class="btn-check" id="oilLess">
                                        <span class="customization-btn">🫒 Less Oil</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Jain Preparation Toggle -->
                            <?php if ($dish['is_jain_available']): ?>
                                <div class="customization-group mb-3">
                                    <label class="customization-label">
                                        <i class="bi bi-flower1 me-1 text-purple"></i> Jain Preparation
                                    </label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_jain" id="jainToggle" style="width: 3em; height: 1.5em;">
                                        <label class="form-check-label ms-2 fw-500" for="jainToggle">
                                            Prepare without onion & garlic
                                        </label>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Quantity -->
                            <div class="customization-group mb-4">
                                <label class="customization-label">
                                    <i class="bi bi-hash me-1"></i> Quantity
                                </label>
                                <div class="quantity-selector">
                                    <button type="button" class="qty-btn qty-minus" id="qtyMinus">
                                        <i class="bi bi-dash-lg"></i>
                                    </button>
                                    <input type="number" name="quantity" id="qtyInput" value="1" min="1" max="10" class="qty-input" readonly>
                                    <button type="button" class="qty-btn qty-plus" id="qtyPlus">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Total & Submit -->
                            <div class="d-flex align-items-center gap-3">
                                <div>
                                    <small class="text-muted">Total</small>
                                    <div class="dish-detail-price" id="itemTotal"><?= format_currency($dish['price']) ?></div>
                                </div>
                                <button type="submit" class="btn btn-kravyo-primary btn-lg flex-fill">
                                    <i class="bi bi-cart-plus me-2"></i> Add to Cart
                                </button>
                            </div>
                        </form>
                    <?php elseif (!$dish['is_open']): ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-clock me-2"></i>
                            <strong>Kitchen Closed</strong> — This kitchen is not currently accepting orders. Please check back later.
                        </div>
                    <?php else: ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-x-circle me-2"></i>
                            <strong>Currently Unavailable</strong> — This dish is temporarily unavailable.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
