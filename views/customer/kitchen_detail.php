<!-- Phase 5: Chef Kitchen Profile & Menu Detail Page -->
<section class="chef-profile-hero">
    <div class="container">
        <div class="row align-items-end g-4">
            <div class="col-lg-8">
                <!-- Kitchen Banner -->
                <div class="chef-banner-wrap mb-3">
                    <?php if (!empty($kitchen['banner_image'])): ?>
                        <img src="<?= UPLOAD_URL . '/kitchens/' . $kitchen['banner_image'] ?>"
                             alt="<?= sanitize($kitchen['kitchen_name']) ?>" class="chef-banner-img">
                    <?php else: ?>
                        <div class="chef-banner-placeholder">
                            <i class="bi bi-shop fs-1"></i>
                            <span>Kitchen Banner</span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="d-flex align-items-center gap-3 mb-2">
                    <h1 class="fw-800 text-white mb-0"><?= sanitize($kitchen['kitchen_name']) ?></h1>
                    <?php if ($kitchen['hygiene_badge'] === 'verified'): ?>
                        <span class="badge badge-hygiene"><i class="bi bi-shield-check me-1"></i> Hygiene Verified</span>
                    <?php endif; ?>
                </div>

                <p class="text-white-50 mb-2">
                    <i class="bi bi-person-fill me-1"></i> Chef <?= sanitize($kitchen['chef_name']) ?>
                    <span class="mx-2">|</span>
                    <i class="bi bi-geo-alt-fill me-1"></i> <?= sanitize($kitchen['city']) ?>, <?= sanitize($kitchen['pincode']) ?>
                </p>

                <!-- Availability Status -->
                <?php if ($kitchen['is_open']): ?>
                    <span class="badge bg-success px-3 py-2 rounded-pill">
                        <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> Currently Open — Accepting Orders
                    </span>
                <?php else: ?>
                    <span class="badge bg-danger px-3 py-2 rounded-pill">
                        <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> Currently Closed
                    </span>
                <?php endif; ?>
            </div>

            <div class="col-lg-4">
                <!-- Stats Card -->
                <div class="chef-stats-card">
                    <div class="row g-3 text-center">
                        <div class="col-4">
                            <div class="chef-stat-value"><?= (int) ($kitchen['dish_count'] ?? 0) ?></div>
                            <div class="chef-stat-label">Dishes</div>
                        </div>
                        <div class="col-4">
                            <div class="chef-stat-value">
                                <?php if ($avgRating > 0): ?>
                                    <?= $avgRating ?> <i class="bi bi-star-fill text-warning" style="font-size: 0.75em;"></i>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </div>
                            <div class="chef-stat-label">Rating</div>
                        </div>
                        <div class="col-4">
                            <div class="chef-stat-value"><?= $reviewCount ?></div>
                            <div class="chef-stat-label">Reviews</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-4">
    <div class="container">
        <!-- Chef Personal Story -->
        <?php if (!empty($kitchen['personal_story'])): ?>
            <div class="chef-story-card mb-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-book text-warning fs-5"></i>
                    <h5 class="fw-bold mb-0">Our Kitchen Story</h5>
                </div>
                <p class="mb-0 text-muted"><?= nl2br(sanitize($kitchen['personal_story'])) ?></p>
            </div>
        <?php endif; ?>

        <!-- Tabbed Content: Menu & Reviews -->
        <ul class="nav nav-pills chef-detail-tabs mb-4" id="chefTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="menu-tab" data-bs-toggle="pill"
                        data-bs-target="#menuPanel" type="button" role="tab">
                    <i class="bi bi-egg-fried me-1"></i> Menu
                    <span class="badge bg-white text-dark ms-1"><?= (int) ($kitchen['dish_count'] ?? 0) ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="reviews-tab" data-bs-toggle="pill"
                        data-bs-target="#reviewsPanel" type="button" role="tab">
                    <i class="bi bi-chat-dots me-1"></i> Reviews
                    <span class="badge bg-white text-dark ms-1"><?= $reviewCount ?></span>
                </button>
            </li>
        </ul>

        <div class="tab-content" id="chefTabContent">
            <!-- Menu Tab -->
            <div class="tab-pane fade show active" id="menuPanel" role="tabpanel">
                <?php if (empty($menuByCategory)): ?>
                    <div class="empty-state text-center py-5">
                        <div class="empty-state-icon mb-3"><i class="bi bi-egg-fried"></i></div>
                        <h5 class="text-muted fw-bold">No Dishes Available Yet</h5>
                        <p class="text-muted">This kitchen hasn't added any menu items yet. Check back soon!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($menuByCategory as $categoryName => $items): ?>
                        <div class="mb-4">
                            <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                                <span class="category-dot"></span>
                                <?= sanitize($categoryName) ?>
                                <span class="badge bg-light text-muted small rounded-pill"><?= count($items) ?></span>
                            </h5>

                            <div class="row g-3">
                                <?php foreach ($items as $item): ?>
                                    <div class="col-lg-4 col-md-6">
                                        <div class="card dish-card h-100">
                                            <div class="dish-card-image">
                                                <?php if (!empty($item['image'])): ?>
                                                    <img src="<?= UPLOAD_URL . '/dishes/' . $item['image'] ?>"
                                                         alt="<?= sanitize($item['item_name']) ?>">
                                                <?php else: ?>
                                                    <div class="dish-image-placeholder">
                                                        <i class="bi bi-egg-fried"></i>
                                                    </div>
                                                <?php endif; ?>

                                                <span class="dish-price-badge"><?= format_currency($item['price']) ?></span>

                                                <?php if (!$item['is_available']): ?>
                                                    <span class="dish-unavailable-badge">Unavailable</span>
                                                <?php endif; ?>
                                            </div>

                                            <div class="card-body p-3">
                                                <h6 class="fw-bold mb-1"><?= sanitize($item['item_name']) ?></h6>
                                                <?php if (!empty($item['description'])): ?>
                                                    <p class="text-muted small mb-2 dish-desc-truncate"><?= sanitize($item['description']) ?></p>
                                                <?php endif; ?>

                                                <!-- Dietary Badges -->
                                                <div class="d-flex flex-wrap gap-1 mb-2">
                                                    <?php if ($item['is_veg']): ?>
                                                        <span class="dietary-badge dietary-veg"><i class="bi bi-circle-fill me-1"></i>Veg</span>
                                                    <?php else: ?>
                                                        <span class="dietary-badge dietary-nonveg"><i class="bi bi-circle-fill me-1"></i>Non-Veg</span>
                                                    <?php endif; ?>
                                                    <?php if ($item['is_jain_available']): ?>
                                                        <span class="dietary-badge dietary-jain"><i class="bi bi-flower1 me-1"></i>Jain</span>
                                                    <?php endif; ?>
                                                    <?php if ($item['is_diabetic_friendly']): ?>
                                                        <span class="dietary-badge dietary-diabetic"><i class="bi bi-heart-pulse me-1"></i>Diabetic</span>
                                                    <?php endif; ?>
                                                </div>

                                                <?php if ($item['is_available']): ?>
                                                    <a href="<?= url('/dish/' . $item['id']) ?>" class="btn btn-kravyo-primary btn-sm w-100">
                                                        <i class="bi bi-cart-plus me-1"></i> View & Add to Cart
                                                    </a>
                                                <?php else: ?>
                                                    <button class="btn btn-outline-secondary btn-sm w-100" disabled>
                                                        <i class="bi bi-x-circle me-1"></i> Currently Unavailable
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Reviews Tab -->
            <div class="tab-pane fade" id="reviewsPanel" role="tabpanel">
                <?php if (empty($reviews)): ?>
                    <div class="empty-state text-center py-5">
                        <div class="empty-state-icon mb-3"><i class="bi bi-chat-dots"></i></div>
                        <h5 class="text-muted fw-bold">No Reviews Yet</h5>
                        <p class="text-muted">Be the first to order and share your experience!</p>
                    </div>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($reviews as $review): ?>
                            <div class="col-lg-6">
                                <div class="review-card">
                                    <div class="d-flex align-items-center gap-3 mb-2">
                                        <div class="avatar-circle">
                                            <?= strtoupper(substr($review['customer_name'] ?? 'U', 0, 1)) ?>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-0"><?= sanitize($review['customer_name'] ?? 'Anonymous') ?></h6>
                                            <small class="text-muted"><?= date('M d, Y', strtotime($review['created_at'])) ?></small>
                                        </div>
                                        <div class="ms-auto">
                                            <div class="d-flex align-items-center gap-1">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="bi bi-star-fill <?= $i <= $review['rating'] ? 'text-warning' : 'text-muted opacity-25' ?> star-sm"></i>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if (!empty($review['review_text'])): ?>
                                        <p class="mb-0 text-muted small"><?= nl2br(sanitize($review['review_text'])) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
