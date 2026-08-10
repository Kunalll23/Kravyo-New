<!-- Phase 7: My Tiffin Subscriptions (Customer Dashboard) -->
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-800 mb-1"><i class="bi bi-journal-check text-warning me-2"></i>My Subscriptions</h2>
            <p class="text-muted mb-0">Manage your active tiffin meal subscriptions</p>
        </div>
        <a href="<?= url('/subscriptions') ?>" class="btn btn-kravyo-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Browse Plans
        </a>
    </div>

    <!-- Active Subscriptions -->
    <div class="mb-5">
        <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
            <span class="category-dot bg-success"></span> Active Subscriptions
            <span class="badge bg-success rounded-pill"><?= count($activeSubscriptions) ?></span>
        </h5>

        <?php if (empty($activeSubscriptions)): ?>
            <div class="card kravyo-card border-0">
                <div class="card-body text-center py-5">
                    <div class="empty-state-icon mb-3"><i class="bi bi-calendar2-x"></i></div>
                    <h5 class="text-muted fw-bold">No Active Subscriptions</h5>
                    <p class="text-muted mb-3">You don't have any active tiffin subscriptions right now.</p>
                    <a href="<?= url('/subscriptions') ?>" class="btn btn-kravyo-primary">
                        <i class="bi bi-search me-1"></i> Explore Tiffin Plans
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($activeSubscriptions as $sub): ?>
                    <div class="col-lg-6">
                        <div class="card subscription-card h-100 subscription-card-active">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h5 class="fw-bold mb-1"><?= sanitize($sub['plan_name']) ?></h5>
                                        <span class="plan-type-badge plan-type-<?= $sub['plan_type'] ?>">
                                            <i class="bi bi-<?= $sub['plan_type'] === 'weekly' ? 'calendar-week' : 'calendar-month' ?> me-1"></i>
                                            <?= ucfirst($sub['plan_type']) ?>
                                        </span>
                                    </div>
                                    <span class="subscription-status-badge status-active">
                                        <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i>Active
                                    </span>
                                </div>

                                <!-- Kitchen -->
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <div class="avatar-circle-sm"><?= strtoupper(substr($sub['kitchen_name'], 0, 1)) ?></div>
                                    <div>
                                        <span class="fw-semibold small"><?= sanitize($sub['kitchen_name']) ?></span>
                                        <br>
                                        <small class="text-muted"><i class="bi bi-geo-alt me-1"></i><?= sanitize($sub['city']) ?></small>
                                    </div>
                                </div>

                                <!-- Dates & Details -->
                                <div class="bg-light rounded-3 p-3 mb-3">
                                    <div class="row g-2 text-center">
                                        <div class="col-4">
                                            <div class="small text-muted">Start Date</div>
                                            <div class="fw-bold small"><?= date('M d, Y', strtotime($sub['start_date'])) ?></div>
                                        </div>
                                        <div class="col-4">
                                            <div class="small text-muted">End Date</div>
                                            <div class="fw-bold small"><?= date('M d, Y', strtotime($sub['end_date'])) ?></div>
                                        </div>
                                        <div class="col-4">
                                            <div class="small text-muted">Meals/Day</div>
                                            <div class="fw-bold small"><?= $sub['meals_per_day'] ?></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Delivery Address -->
                                <p class="small text-muted mb-3">
                                    <i class="bi bi-truck me-1"></i>
                                    Delivering to: <?= sanitize($sub['street_address']) ?>, <?= sanitize($sub['delivery_city']) ?>
                                </p>

                                <!-- Days Remaining -->
                                <?php
                                    $today = new DateTime();
                                    $endDate = new DateTime($sub['end_date']);
                                    $daysLeft = max(0, (int) $today->diff($endDate)->format('%a'));
                                ?>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <span class="fw-bold text-primary"><?= $daysLeft ?></span>
                                        <span class="text-muted small">days remaining</span>
                                    </div>
                                    <div class="fw-bold"><?= format_currency($sub['total_paid']) ?></div>
                                </div>

                                <!-- Progress Bar -->
                                <?php
                                    $startDate = new DateTime($sub['start_date']);
                                    $totalDays = max(1, (int) $startDate->diff($endDate)->format('%a'));
                                    $elapsed = max(0, (int) $startDate->diff($today)->format('%a'));
                                    $progress = min(100, round(($elapsed / $totalDays) * 100));
                                ?>
                                <div class="progress mb-3" style="height: 6px;">
                                    <div class="progress-bar bg-success" style="width: <?= $progress ?>%"></div>
                                </div>

                                <!-- Cancel Button -->
                                <form action="<?= url('/subscription/cancel') ?>" method="POST"
                                      onsubmit="return confirm('Are you sure you want to cancel this subscription? This action cannot be undone.')">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="subscription_id" value="<?= $sub['id'] ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                        <i class="bi bi-x-circle me-1"></i> Cancel Subscription
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Past Subscriptions -->
    <?php if (!empty($pastSubscriptions)): ?>
        <div>
            <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                <span class="category-dot bg-secondary"></span> Past Subscriptions
                <span class="badge bg-secondary rounded-pill"><?= count($pastSubscriptions) ?></span>
            </h5>

            <div class="row g-3">
                <?php foreach ($pastSubscriptions as $sub): ?>
                    <div class="col-lg-6">
                        <div class="card subscription-card h-100 subscription-card-inactive">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="fw-bold mb-1"><?= sanitize($sub['plan_name']) ?></h6>
                                        <small class="text-muted"><?= sanitize($sub['kitchen_name']) ?></small>
                                    </div>
                                    <span class="subscription-status-badge status-<?= $sub['status'] ?>">
                                        <?= ucfirst($sub['status']) ?>
                                    </span>
                                </div>
                                <div class="d-flex gap-3 text-muted small">
                                    <span><i class="bi bi-calendar me-1"></i><?= date('M d', strtotime($sub['start_date'])) ?> — <?= date('M d, Y', strtotime($sub['end_date'])) ?></span>
                                    <span><i class="bi bi-currency-rupee me-1"></i><?= format_currency($sub['total_paid']) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
