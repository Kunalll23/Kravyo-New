<!-- Phase 5: Full Dish Catalog / Browse Menu Page -->
<section class="kitchen-browse-hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <h1 class="fw-800 text-white mb-2">
                    <i class="bi bi-journal-text me-2 text-warning"></i>Browse Dishes
                </h1>
                <p class="text-white-50 mb-0">Discover home-cooked meals from verified kitchens across all categories</p>
            </div>
            <div class="col-lg-5 text-lg-end mt-3 mt-lg-0">
                <span class="badge bg-white text-dark px-3 py-2 fs-6 rounded-pill">
                    <i class="bi bi-egg-fried text-warning me-1"></i>
                    <?= count($dishes) ?> Dish<?= count($dishes) !== 1 ? 'es' : '' ?> Available
                </span>
            </div>
        </div>
    </div>
</section>

<section class="py-4">
    <div class="container">
        <div class="row g-4">
            <!-- Sidebar Filters -->
            <div class="col-lg-3">
                <div class="menu-filter-sidebar">
                    <form method="GET" action="<?= url('/menu') ?>" id="menuFilterForm">
                        <h6 class="fw-bold mb-3"><i class="bi bi-funnel me-1"></i> Filters</h6>

                        <!-- Search -->
                        <div class="mb-3">
                            <label class="filter-label">Search Dish</label>
                            <input type="text" name="q" class="form-control filter-input"
                                   placeholder="e.g. Thali, Paratha..."
                                   value="<?= sanitize($filters['keyword'] ?? '') ?>">
                        </div>

                        <!-- Category -->
                        <div class="mb-3">
                            <label class="filter-label">Category</label>
                            <select name="category" class="form-select filter-input">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= ($filters['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                        <?= sanitize($cat['category_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Dietary Filters -->
                        <div class="mb-3">
                            <label class="filter-label">Dietary Preference</label>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="veg" id="filterVeg"
                                       <?= !empty($filters['is_veg']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="filterVeg">
                                    <span class="dietary-badge dietary-veg"><i class="bi bi-circle-fill me-1"></i>Veg Only</span>
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="jain" id="filterJain"
                                       <?= !empty($filters['is_jain_available']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="filterJain">
                                    <span class="dietary-badge dietary-jain"><i class="bi bi-flower1 me-1"></i>Jain Available</span>
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="diabetic" id="filterDiabetic"
                                       <?= !empty($filters['is_diabetic_friendly']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="filterDiabetic">
                                    <span class="dietary-badge dietary-diabetic"><i class="bi bi-heart-pulse me-1"></i>Diabetic Friendly</span>
                                </label>
                            </div>
                        </div>

                        <!-- City -->
                        <div class="mb-3">
                            <label class="filter-label">City</label>
                            <select name="city" class="form-select filter-input">
                                <option value="">All Cities</option>
                                <?php foreach ($cities as $city): ?>
                                    <option value="<?= sanitize($city) ?>" <?= ($filters['city'] ?? '') === $city ? 'selected' : '' ?>>
                                        <?= sanitize($city) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Sort -->
                        <div class="mb-3">
                            <label class="filter-label">Sort By</label>
                            <select name="sort" class="form-select filter-input">
                                <option value="newest" <?= ($filters['sort'] ?? '') === 'newest' ? 'selected' : '' ?>>Newest First</option>
                                <option value="price_low" <?= ($filters['sort'] ?? '') === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                                <option value="price_high" <?= ($filters['sort'] ?? '') === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                                <option value="name" <?= ($filters['sort'] ?? '') === 'name' ? 'selected' : '' ?>>Name: A-Z</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-kravyo-primary w-100 mb-2">
                            <i class="bi bi-funnel-fill me-1"></i> Apply Filters
                        </button>
                        <a href="<?= url('/menu') ?>" class="btn btn-outline-secondary w-100 btn-sm">
                            <i class="bi bi-x-lg me-1"></i> Clear All
                        </a>
                    </form>
                </div>
            </div>

            <!-- Dish Grid -->
            <div class="col-lg-9">
                <?php if (empty($dishes)): ?>
                    <div class="empty-state text-center py-5">
                        <div class="empty-state-icon mb-3"><i class="bi bi-egg-fried"></i></div>
                        <h4 class="fw-bold text-muted">No Dishes Found</h4>
                        <p class="text-muted mb-4">Try adjusting your filters or search term.</p>
                        <a href="<?= url('/menu') ?>" class="btn btn-kravyo-primary">
                            <i class="bi bi-arrow-clockwise me-1"></i> Clear Filters
                        </a>
                    </div>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($dishes as $dish): ?>
                            <div class="col-lg-4 col-md-6">
                                <div class="card dish-card h-100">
                                    <a href="<?= url('/dish/' . $dish['id']) ?>" class="text-decoration-none">
                                        <div class="dish-card-image">
                                            <?php if (!empty($dish['image'])): ?>
                                                <img src="<?= UPLOAD_URL . '/dishes/' . $dish['image'] ?>"
                                                     alt="<?= sanitize($dish['item_name']) ?>">
                                            <?php else: ?>
                                                <div class="dish-image-placeholder">
                                                    <i class="bi bi-egg-fried"></i>
                                                </div>
                                            <?php endif; ?>
                                            <span class="dish-price-badge"><?= format_currency($dish['price']) ?></span>
                                        </div>
                                    </a>

                                    <div class="card-body p-3">
                                        <a href="<?= url('/dish/' . $dish['id']) ?>" class="text-decoration-none">
                                            <h6 class="fw-bold mb-1 text-dark"><?= sanitize($dish['item_name']) ?></h6>
                                        </a>

                                        <!-- Kitchen info -->
                                        <p class="text-muted small mb-2">
                                            <a href="<?= url('/kitchen/' . $dish['kitchen_id']) ?>" class="text-decoration-none text-muted">
                                                <i class="bi bi-shop me-1"></i><?= sanitize($dish['kitchen_name']) ?>
                                            </a>
                                            <span class="mx-1">•</span>
                                            <i class="bi bi-geo-alt me-1"></i><?= sanitize($dish['city']) ?>
                                        </p>

                                        <!-- Dietary Badges -->
                                        <div class="d-flex flex-wrap gap-1 mb-2">
                                            <?php if ($dish['is_veg']): ?>
                                                <span class="dietary-badge dietary-veg"><i class="bi bi-circle-fill me-1"></i>Veg</span>
                                            <?php else: ?>
                                                <span class="dietary-badge dietary-nonveg"><i class="bi bi-circle-fill me-1"></i>Non-Veg</span>
                                            <?php endif; ?>
                                            <?php if ($dish['is_jain_available']): ?>
                                                <span class="dietary-badge dietary-jain"><i class="bi bi-flower1 me-1"></i>Jain</span>
                                            <?php endif; ?>
                                            <?php if ($dish['is_diabetic_friendly']): ?>
                                                <span class="dietary-badge dietary-diabetic"><i class="bi bi-heart-pulse me-1"></i>Diabetic</span>
                                            <?php endif; ?>
                                        </div>

                                        <a href="<?= url('/dish/' . $dish['id']) ?>" class="btn btn-kravyo-primary btn-sm w-100">
                                            <i class="bi bi-cart-plus me-1"></i> View & Customize
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
