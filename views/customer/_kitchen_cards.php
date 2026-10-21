<?php
/**
 * Partial for rendering Kitchen browse cards grid or empty state
 * Shared between initial SSR page load and live AJAX filter requests
 */
?>
<?php if (empty($kitchens)): ?>
    <div class="empty-state text-center py-5">
        <div class="empty-state-icon mb-3">
            <i class="bi bi-shop text-muted" style="font-size: 3rem;"></i>
        </div>
        <h4 class="fw-bold text-muted">No Kitchens Found</h4>
        <p class="text-muted mb-4">Try adjusting your search query, selecting another city, or clearing your filters.</p>
        <button type="button" class="btn btn-kravyo-primary px-4 py-2 rounded-pill shadow-sm" id="resetFiltersEmptyBtn">
            <i class="bi bi-arrow-clockwise me-1"></i> Reset All Filters
        </button>
    </div>
<?php else: ?>
    <div class="row g-4" id="kitchensRow">
        <?php foreach ($kitchens as $kitchen): ?>
            <div class="col-lg-4 col-md-6">
                <a href="<?= ctx_url('/kitchen/' . $kitchen['id']) ?>" class="text-decoration-none">
                    <div class="card kitchen-browse-card h-100">
                        <!-- Kitchen Banner -->
                        <div class="kitchen-card-banner position-relative">
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
                            <h5 class="fw-bold mb-1 text-dark kitchen-title-clamp" title="<?= sanitize($kitchen['kitchen_name']) ?>"><?= sanitize($kitchen['kitchen_name']) ?></h5>
                            <p class="text-muted small mb-2">
                                <i class="bi bi-person-fill me-1 text-secondary"></i><?= sanitize($kitchen['chef_name']) ?>
                            </p>

                            <div class="d-flex align-items-center gap-3 mb-2">
                                <span class="small text-muted">
                                    <i class="bi bi-geo-alt-fill text-danger me-1"></i><?= sanitize($kitchen['city']) ?> — <?= sanitize($kitchen['pincode']) ?>
                                </span>
                            </div>

                            <div class="d-flex align-items-center justify-content-between pt-2 border-top">
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
                                            <i class="bi bi-star text-muted star-sm"></i>
                                        <?php endif; endfor; ?>
                                    <span class="small text-muted ms-1">
                                        <?= $rating > 0 ? $rating : 'New' ?>
                                        <?php if (($kitchen['review_count'] ?? 0) > 0): ?>
                                            (<?= $kitchen['review_count'] ?>)
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <span class="badge bg-light text-dark small border">
                                    <i class="bi bi-egg-fried me-1 text-primary"></i><?= (int) ($kitchen['dish_count'] ?? 0) ?> dishes
                                </span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
