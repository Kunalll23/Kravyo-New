<?php /* Phase 9: Chef Dashboard — Fully wired analytics */ ?>
<div class="container py-4">
    <!-- Chef Dashboard Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-speedometer2 text-warning me-2"></i>Chef Dashboard
            </h2>
            <p class="text-muted mb-0">Manage your kitchen, track orders, and grow your business</p>
        </div>
        <a href="<?= url('/chef/profile') ?>" class="btn btn-kravyo-primary mt-3 mt-md-0">
            <i class="bi bi-pencil-square me-1"></i> Edit Kitchen Profile
        </a>
    </div>

    <?php if (!$kitchen): ?>
        <!-- No Kitchen Found Alert -->
        <div class="alert alert-warning d-flex align-items-center" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-3 fs-4"></i>
            <div>
                <strong>Kitchen Profile Missing!</strong> Your kitchen profile could not be found.
                Please contact support or try registering again.
            </div>
        </div>
    <?php else: ?>

        <!-- Kitchen Status Banner -->
        <div class="kitchen-status-banner status-<?= $kitchen['approval_status'] ?> mb-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <h4 class="fw-bold mb-0"><?= sanitize($kitchen['kitchen_name']) ?></h4>
                        <span class="badge-status badge-status-<?= $kitchen['approval_status'] ?>">
                            <i class="bi bi-<?= $kitchen['approval_status'] === 'approved' ? 'check-circle-fill' : ($kitchen['approval_status'] === 'rejected' ? 'x-circle-fill' : 'hourglass-split') ?> me-1"></i>
                            <?= ucfirst($kitchen['approval_status']) ?>
                        </span>
                    </div>

                    <?php if ($kitchen['approval_status'] === 'pending'): ?>
                        <p class="mb-0 opacity-75">
                            <i class="bi bi-info-circle me-1"></i>
                            Your kitchen is under review. Our team will verify your details and approve your kitchen within 24-48 hours.
                            In the meantime, please complete your <a href="<?= url('/chef/profile') ?>" class="text-white fw-bold">kitchen profile</a>.
                        </p>
                    <?php elseif ($kitchen['approval_status'] === 'rejected'): ?>
                        <p class="mb-0 opacity-75">
                            <i class="bi bi-info-circle me-1"></i>
                            <strong>Reason:</strong> <?= sanitize($kitchen['admin_notes'] ?? 'No reason provided.') ?>
                            <br>Please update your profile and resubmit for review.
                        </p>
                    <?php else: ?>
                        <p class="mb-0 opacity-75">
                            <i class="bi bi-check2-all me-1"></i>
                            Your kitchen is verified and active! Customers can now discover and order from your kitchen.
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Availability Toggle (only for approved kitchens) -->
                <?php if ($kitchen['approval_status'] === 'approved'): ?>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="availability-toggle-container">
                        <label class="availability-label mb-2 d-block">Kitchen Status</label>
                        <div class="availability-switch" id="availabilityToggle" data-csrf="<?= csrf_token() ?>">
                            <div class="toggle-track <?= $kitchen['is_open'] ? 'active' : '' ?>">
                                <div class="toggle-thumb"></div>
                            </div>
                            <span class="toggle-text fw-bold <?= $kitchen['is_open'] ? 'text-success' : 'text-danger' ?>">
                                <?= $kitchen['is_open'] ? '● OPEN' : '● CLOSED' ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Phase 9: Real Analytics Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="stat-card stat-card-orders">
                    <div class="stat-icon"><i class="bi bi-bag-check"></i></div>
                    <div class="stat-value"><?= $totalOrders ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card stat-card-earnings">
                    <div class="stat-icon"><i class="bi bi-currency-rupee"></i></div>
                    <div class="stat-value">₹<?= number_format($totalEarnings, 0) ?></div>
                    <div class="stat-label">Total Earnings</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card stat-card-menu">
                    <div class="stat-icon"><i class="bi bi-egg-fried"></i></div>
                    <div class="stat-value"><?= $menuItemCount ?></div>
                    <div class="stat-label">Menu Items</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card stat-card-reviews">
                    <div class="stat-icon"><i class="bi bi-star-half"></i></div>
                    <div class="stat-value"><?= $avgRating > 0 ? $avgRating . ' ★' : '--' ?></div>
                    <div class="stat-label">Avg Rating (<?= $reviewCount ?>)</div>
                </div>
            </div>
        </div>

        <!-- This Month + 7-Day Chart + Top Dishes -->
        <div class="row g-4 mb-4">

            <!-- Monthly Earnings Card -->
            <div class="col-lg-4">
                <div class="card kravyo-card border-0 h-100">
                    <div class="card-header bg-transparent border-bottom py-3">
                        <h5 class="fw-bold mb-0"><i class="bi bi-calendar-month me-2 text-success"></i>This Month</h5>
                    </div>
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div>
                            <div class="display-5 fw-800 text-success mb-1">
                                ₹<?= number_format($monthlyEarnings['this_month'], 0) ?>
                            </div>
                            <div class="text-muted small mb-3">Revenue earned this month</div>

                            <?php if ($monthlyEarnings['last_month'] > 0 || $monthlyEarnings['this_month'] > 0): ?>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-<?= $monthTrend >= 0 ? 'success' : 'danger' ?> bg-opacity-15 text-<?= $monthTrend >= 0 ? 'success' : 'danger' ?> px-3 py-2 fw-bold">
                                        <i class="bi bi-arrow-<?= $monthTrend >= 0 ? 'up' : 'down' ?>-right me-1"></i>
                                        <?= abs($monthTrend) ?>% vs last month
                                    </span>
                                </div>
                                <div class="text-muted small mt-2">
                                    Last month: ₹<?= number_format($monthlyEarnings['last_month'], 0) ?>
                                </div>
                            <?php else: ?>
                                <div class="text-muted small">No data from previous month</div>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex gap-2 mt-3">
                            <a href="<?= url('/chef/orders') ?>" class="btn btn-sm btn-outline-primary flex-grow-1">
                                <i class="bi bi-bag-check me-1"></i>View Orders
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 7-Day Earnings Mini Chart -->
            <div class="col-lg-8">
                <div class="card kravyo-card border-0 h-100">
                    <div class="card-header bg-transparent border-bottom py-3">
                        <h5 class="fw-bold mb-0"><i class="bi bi-bar-chart-line me-2 text-primary"></i>Earnings — Last 7 Days</h5>
                    </div>
                    <div class="card-body p-4">
                        <?php if (empty($earnings7Days) || array_sum($earnings7Days) === 0.0): ?>
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-bar-chart display-3 opacity-25 d-block mb-2"></i>
                                <p class="small">No delivered orders in the last 7 days</p>
                            </div>
                        <?php else: ?>
                            <?php
                            $maxEarning = max($earnings7Days) ?: 1;
                            ?>
                            <div class="d-flex align-items-end gap-2" style="height: 140px;">
                                <?php foreach ($earnings7Days as $day => $amount): ?>
                                    <?php $barH = $maxEarning > 0 ? round(($amount / $maxEarning) * 120) : 4; ?>
                                    <div class="d-flex flex-column align-items-center flex-grow-1 gap-1" style="min-width:0">
                                        <?php if ($amount > 0): ?>
                                            <div class="fw-semibold text-primary" style="font-size:0.65rem;">₹<?= number_format($amount, 0) ?></div>
                                        <?php else: ?>
                                            <div style="font-size:0.65rem;">&nbsp;</div>
                                        <?php endif; ?>
                                        <div class="w-100 rounded-top <?= $amount > 0 ? 'bg-primary bg-opacity-75' : 'bg-light border' ?>"
                                             style="height: <?= max($barH, 6) ?>px; transition: height 0.4s ease;"></div>
                                        <div class="text-muted" style="font-size:0.6rem;"><?= date('D', strtotime($day)) ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Row: Kitchen Info + Top Dishes + Recent Reviews -->
        <div class="row g-4">

            <!-- Kitchen Information -->
            <div class="col-lg-4">
                <div class="card kravyo-card border-0 h-100">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3"><i class="bi bi-shop me-2 text-warning"></i>Kitchen Information</h5>
                        <table class="table table-borderless mb-0 small">
                            <tr>
                                <td class="text-muted fw-semibold" style="width:120px">Name</td>
                                <td class="fw-semibold"><?= sanitize($kitchen['kitchen_name']) ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold">Address</td>
                                <td><?= sanitize($kitchen['address'] ?: 'Not provided') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold">City</td>
                                <td><?= sanitize($kitchen['city'] ?: '--') ?> — <?= sanitize($kitchen['pincode'] ?: '--') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold">FSSAI</td>
                                <td>
                                    <?php if (!empty($kitchen['fssai_license'])): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success px-2 py-1">
                                            <i class="bi bi-shield-check me-1"></i><?= sanitize($kitchen['fssai_license']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">Not provided</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold">Hygiene</td>
                                <td>
                                    <?php if ($kitchen['hygiene_badge'] === 'verified'): ?>
                                        <span class="badge-hygiene"><i class="bi bi-patch-check-fill me-1"></i>Verified</span>
                                    <?php else: ?>
                                        <span class="text-muted">Not Yet Verified</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                        <div class="mt-3 d-flex flex-wrap gap-2">
                            <a href="<?= url('/chef/zero-waste') ?>" class="btn btn-sm btn-outline-success">
                                <i class="bi bi-recycle me-1"></i>Zero Waste
                            </a>
                            <a href="<?= url('/chef/subscriptions') ?>" class="btn btn-sm btn-outline-warning">
                                <i class="bi bi-calendar-week me-1"></i>Tiffins
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Selling Dishes -->
            <div class="col-lg-4">
                <div class="card kravyo-card border-0 h-100">
                    <div class="card-header bg-transparent border-bottom py-3">
                        <h5 class="fw-bold mb-0"><i class="bi bi-trophy me-2 text-warning"></i>Top Selling Dishes</h5>
                    </div>
                    <div class="card-body p-3">
                        <?php if (empty($topDishes)): ?>
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-egg-fried display-4 opacity-25 d-block mb-2"></i>
                                <p class="small">No delivered orders yet</p>
                                <a href="<?= url('/chef/menu') ?>" class="btn btn-sm btn-kravyo-outline">Add Dishes</a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($topDishes as $rank => $dish): ?>
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <div class="fw-800 text-muted" style="width:20px;font-size:0.8rem;"><?= $rank + 1 ?></div>
                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center overflow-hidden"
                                         style="width:36px;height:36px;flex-shrink:0;">
                                        <?php if (!empty($dish['image'])): ?>
                                            <img src="<?= url('/uploads/dishes/' . $dish['image']) ?>"
                                                 class="w-100 h-100 object-fit-cover" alt="">
                                        <?php else: ?>
                                            <span style="font-size:1.1rem;"><?= $dish['is_veg'] ? '🌿' : '🍗' ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="fw-semibold small text-truncate"><?= sanitize($dish['item_name']) ?></div>
                                        <div class="text-muted" style="font-size:0.7rem;"><?= (int)$dish['total_qty'] ?> orders · ₹<?= number_format((float)$dish['total_revenue'], 0) ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Reviews -->
            <div class="col-lg-4">
                <div class="card kravyo-card border-0 h-100">
                    <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0"><i class="bi bi-chat-quote me-2 text-info"></i>Recent Reviews</h5>
                        <?php if ($avgRating > 0): ?>
                            <span class="badge bg-warning text-dark fw-bold px-2">
                                <?= $avgRating ?> ★
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body p-3">
                        <?php if (empty($recentReviews)): ?>
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-star display-4 opacity-25 d-block mb-2"></i>
                                <p class="small">No reviews yet — complete orders to earn reviews!</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($recentReviews as $review): ?>
                                <div class="mb-3 pb-3 border-bottom">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <span class="fw-semibold small"><?= sanitize($review['customer_name']) ?></span>
                                        <span class="text-warning small">
                                            <?php for ($s = 1; $s <= 5; $s++): ?>
                                                <i class="bi bi-star<?= $s <= (int)$review['rating'] ? '-fill' : '' ?>"></i>
                                            <?php endfor; ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($review['review_text'])): ?>
                                        <p class="text-muted mb-0" style="font-size:0.78rem;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                                            "<?= sanitize($review['review_text']) ?>"
                                        </p>
                                    <?php endif; ?>
                                    <div class="text-muted mt-1" style="font-size:0.65rem;"><?= date('d M Y', strtotime($review['created_at'])) ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kitchen Story & Banner -->
        <?php if (!empty($kitchen['personal_story']) || !empty($kitchen['banner_image'])): ?>
        <div class="row g-4 mt-0">
            <?php if (!empty($kitchen['personal_story'])): ?>
            <div class="col-lg-6">
                <div class="card kravyo-card border-0">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3"><i class="bi bi-journal-text me-2 text-warning"></i>Your Kitchen Story</h5>
                        <p class="text-muted lh-lg"><?= nl2br(sanitize($kitchen['personal_story'])) ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <?php if (!empty($kitchen['banner_image'])): ?>
            <div class="col-lg-6">
                <div class="card kravyo-card border-0 overflow-hidden">
                    <div class="card-body p-3">
                        <h6 class="fw-bold mb-3"><i class="bi bi-image me-2 text-warning"></i>Kitchen Banner</h6>
                        <img src="<?= UPLOAD_URL . '/kitchens/' . $kitchen['banner_image'] ?>"
                             alt="Kitchen Banner" class="img-fluid rounded-3 w-100" style="max-height: 220px; object-fit: cover;">
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    <?php endif; ?>
</div>
