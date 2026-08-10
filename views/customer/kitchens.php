<!-- Phase 5: Kitchen Browse Page — Explore Local Home Kitchens -->
<section class="kitchen-browse-hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <h1 class="fw-800 text-white mb-2">
                    <i class="bi bi-shop-window me-2 text-warning"></i>Explore Home Kitchens
                </h1>
                <p class="text-white-50 mb-0">Discover home chefs near you serving authentic, hygienic home-cooked meals</p>
            </div>
            <div class="col-lg-5 text-lg-end mt-3 mt-lg-0">
                <span class="badge bg-white text-dark px-3 py-2 fs-6 rounded-pill">
                    <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                    <?= count($kitchens) ?> Kitchen<?= count($kitchens) !== 1 ? 's' : '' ?> Found
                </span>
            </div>
        </div>
    </div>
</section>

<section class="py-4">
    <div class="container">
        <!-- Search & Filter Bar -->
        <div class="kitchen-filter-bar mb-4">
            <form method="GET" action="<?= url('/kitchens') ?>" id="kitchenFilterForm">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-4 col-md-6">
                        <label class="filter-label"><i class="bi bi-search me-1"></i> Search</label>
                        <input type="text" name="q" class="form-control filter-input"
                               placeholder="Search kitchen name, chef, or story..."
                               value="<?= sanitize($filters['keyword'] ?? '') ?>">
                    </div>
                    <div class="col-lg-2 col-md-3">
                        <label class="filter-label"><i class="bi bi-geo-alt me-1"></i> City</label>
                        <select name="city" class="form-select filter-input">
                            <option value="">All Cities</option>
                            <?php foreach ($cities as $city): ?>
                                <option value="<?= sanitize($city) ?>" <?= ($filters['city'] ?? '') === $city ? 'selected' : '' ?>>
                                    <?= sanitize($city) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-3">
                        <label class="filter-label"><i class="bi bi-mailbox me-1"></i> Pincode</label>
                        <input type="text" name="pincode" class="form-control filter-input"
                               placeholder="e.g. 395007"
                               value="<?= sanitize($filters['pincode'] ?? '') ?>"
                               maxlength="6">
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <div class="form-check filter-checkbox mt-2">
                            <input class="form-check-input" type="checkbox" name="open_only" id="openOnlyCheck"
                                   <?= !empty($filters['open_only']) ? 'checked' : '' ?>>
                            <label class="form-check-label fw-600" for="openOnlyCheck">
                                <i class="bi bi-clock text-success me-1"></i> Open Now Only
                            </label>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-6 d-flex gap-2">
                        <button type="submit" class="btn btn-kravyo-primary flex-fill">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                        <a href="<?= url('/kitchens') ?>" class="btn btn-outline-secondary" title="Clear Filters">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Kitchen Cards Grid -->
        <?php if (empty($kitchens)): ?>
            <div class="empty-state text-center py-5">
                <div class="empty-state-icon mb-3">
                    <i class="bi bi-shop"></i>
                </div>
                <h4 class="fw-bold text-muted">No Kitchens Found</h4>
                <p class="text-muted mb-4">Try adjusting your search filters or browse all available kitchens.</p>
                <a href="<?= url('/kitchens') ?>" class="btn btn-kravyo-primary">
                    <i class="bi bi-arrow-clockwise me-1"></i> Clear All Filters
                </a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($kitchens as $kitchen): ?>
                    <div class="col-lg-4 col-md-6">
                        <a href="<?= url('/kitchen/' . $kitchen['id']) ?>" class="text-decoration-none">
                            <div class="card kitchen-browse-card h-100">
                                <!-- Kitchen Banner -->
                                <div class="kitchen-card-banner">
                                    <?php if (!empty($kitchen['banner_image'])): ?>
                                        <img src="<?= UPLOAD_URL . '/kitchens/' . $kitchen['banner_image'] ?>"
                                             alt="<?= sanitize($kitchen['kitchen_name']) ?>" class="kitchen-card-banner-img">
                                    <?php else: ?>
                                        <div class="kitchen-card-banner-placeholder">
                                            <i class="bi bi-shop"></i>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Status Badge -->
                                    <?php if ($kitchen['is_open']): ?>
                                        <span class="kitchen-status-pill status-open">
                                            <i class="bi bi-circle-fill"></i> Open
                                        </span>
                                    <?php else: ?>
                                        <span class="kitchen-status-pill status-closed">
                                            <i class="bi bi-circle-fill"></i> Closed
                                        </span>
                                    <?php endif; ?>

                                    <!-- Hygiene Badge -->
                                    <?php if ($kitchen['hygiene_badge'] === 'verified'): ?>
                                        <span class="kitchen-hygiene-pill">
                                            <i class="bi bi-shield-check"></i> Verified
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <div class="card-body p-3">
                                    <h5 class="fw-bold mb-1 text-dark"><?= sanitize($kitchen['kitchen_name']) ?></h5>
                                    <p class="text-muted small mb-2">
                                        <i class="bi bi-person-fill me-1"></i><?= sanitize($kitchen['chef_name']) ?>
                                    </p>

                                    <div class="d-flex align-items-center gap-3 mb-2">
                                        <span class="small text-muted">
                                            <i class="bi bi-geo-alt-fill text-danger me-1"></i><?= sanitize($kitchen['city']) ?> — <?= sanitize($kitchen['pincode']) ?>
                                        </span>
                                    </div>

                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-1">
                                            <!-- Star Rating -->
                                            <?php
                                            $rating = $kitchen['avg_rating'] ?? 0;
                                            for ($i = 1; $i <= 5; $i++):
                                                if ($i <= floor($rating)):
                                            ?>
                                                    <i class="bi bi-star-fill text-warning star-sm"></i>
                                                <?php elseif ($i - 0.5 <= $rating): ?>
                                                    <i class="bi bi-star-half text-warning star-sm"></i>
                                                <?php else: ?>
                                                    <i class="bi bi-star text-warning star-sm"></i>
                                                <?php endif; endfor; ?>
                                            <span class="small text-muted ms-1">
                                                <?= $rating > 0 ? $rating : 'New' ?>
                                                <?php if (($kitchen['review_count'] ?? 0) > 0): ?>
                                                    (<?= $kitchen['review_count'] ?>)
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                        <span class="badge bg-light text-dark small">
                                            <i class="bi bi-egg-fried me-1"></i><?= (int) ($kitchen['dish_count'] ?? 0) ?> dishes
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
