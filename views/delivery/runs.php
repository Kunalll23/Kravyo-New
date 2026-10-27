<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-box-seam-fill text-warning me-2"></i>Tiffin Batch Delivery Runs
            </h2>
            <p class="text-muted mb-0 small">
                City: <span class="badge bg-primary px-2 py-1 rounded-pill"><?= sanitize($city) ?></span> &bull; Multi-Kitchen Pickup &bull; Corridor Drop-offs
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= url('/delivery/dashboard') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Dashboard
            </a>
        </div>
    </div>

    <!-- Active Run Alert if in progress -->
    <?php if (!empty($activeRun)): ?>
        <div class="card border-0 shadow-lg rounded-4 mb-4 overflow-hidden" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
            <div class="card-body p-4 text-white">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="badge bg-warning text-dark px-3 py-1 rounded-pill fw-bold mb-2">
                            <i class="bi bi-bicycle me-1"></i> ACTIVE BATCH RUN
                        </span>
                        <h4 class="fw-bold mb-1">Run #<?= sanitize($activeRun['run_code']) ?></h4>
                        <div class="text-white-50 small">
                            <i class="bi bi-calendar3 me-1"></i> <?= date('M d, Y', strtotime($activeRun['delivery_date'])) ?> &bull; 
                            <span class="text-uppercase fw-bold text-info"><?= sanitize($activeRun['meal_type']) ?></span> &bull;
                            <?= count($activeRun['stops']) ?> Total Stops
                        </div>
                    </div>
                    <div class="text-end">
                        <small class="text-white-50 d-block">GUARANTEED PAYOUT</small>
                        <div class="fs-3 fw-bold text-success">₹<?= number_format($activeRun['total_payout'], 2) ?></div>
                    </div>
                </div>

                <?php 
                $completedStops = 0;
                foreach ($activeRun['stops'] as $s) {
                    if ($s['status'] === 'completed') $completedStops++;
                }
                $totalStops = count($activeRun['stops']);
                $pct = $totalStops > 0 ? round(($completedStops / $totalStops) * 100) : 0;
                ?>

                <!-- Progress Bar -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between small text-white-50 mb-1">
                        <span>Progress: <strong><?= $completedStops ?> of <?= $totalStops ?> Stops Completed</strong></span>
                        <span><?= $pct ?>%</span>
                    </div>
                    <div class="progress bg-secondary" style="height: 8px;">
                        <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" style="width: <?= $pct ?>%;"></div>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="<?= url('/delivery/run/' . $activeRun['id']) ?>" class="btn btn-warning fw-bold px-4 rounded-pill shadow">
                        <i class="bi bi-arrow-right-circle-fill me-1"></i> Open Step-by-Step Manifest
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Available Draft Runs for Claiming -->
    <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
        <i class="bi bi-collection-fill text-primary"></i> Available Tiffin Runs in <?= sanitize($city) ?>
    </h5>

    <?php if (empty($availableRuns)): ?>
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body text-center p-5">
                <i class="bi bi-inbox text-muted opacity-25" style="font-size: 4rem;"></i>
                <h5 class="mt-3 fw-bold text-muted">No Open Tiffin Runs Right Now</h5>
                <p class="text-muted small mb-0">
                    Runs are automatically compiled every morning before meal hours (10:30 AM for Lunch, 6:00 PM for Dinner).
                </p>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($availableRuns as $run): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 hover-shadow transition-all">
                        <div class="card-body p-4 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-light text-dark border px-2 py-1 rounded-pill small fw-semibold">
                                        #<?= sanitize($run['run_code']) ?>
                                    </span>
                                    <span class="badge bg-info text-dark text-uppercase px-2 py-1 rounded-pill">
                                        <?= sanitize($run['meal_type']) ?>
                                    </span>
                                </div>

                                <h5 class="fw-bold mb-2">
                                    <?= (int)$run['total_orders'] ?> Tiffins Run
                                </h5>

                                <div class="d-flex gap-3 text-muted small mb-3">
                                    <div>
                                        <i class="bi bi-shop text-primary me-1"></i>
                                        <strong><?= (int)$run['pickup_stops_count'] ?></strong> Kitchens
                                    </div>
                                    <div>
                                        <i class="bi bi-house-door text-success me-1"></i>
                                        <strong><?= (int)$run['dropoff_stops_count'] ?></strong> Customers
                                    </div>
                                    <div>
                                        <i class="bi bi-calendar3 me-1"></i>
                                        <?= date('M d', strtotime($run['delivery_date'])) ?>
                                    </div>
                                </div>

                                <div class="alert alert-light border small py-2 px-3 rounded-3 mb-3 text-secondary">
                                    <i class="bi bi-info-circle me-1 text-primary"></i>
                                    Collect all boxes from kitchens first, then drop along the corridor.
                                </div>
                            </div>

                            <div class="pt-3 border-top d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted d-block" style="font-size:0.75rem;">BATCH PAYOUT</small>
                                    <span class="fs-4 fw-bold text-success">₹<?= number_format($run['total_payout'], 2) ?></span>
                                </div>
                                <form method="POST" action="<?= url('/delivery/run/' . $run['id'] . '/accept') ?>" class="mb-0">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                                        <i class="bi bi-check2-circle me-1"></i> Accept Run
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
