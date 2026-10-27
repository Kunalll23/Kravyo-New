<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="<?= url('/delivery/runs') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Back to Runs
        </a>
        <span class="badge <?= $run['status'] === 'completed' ? 'bg-success' : ($run['status'] === 'in_progress' ? 'bg-primary' : 'bg-warning text-dark') ?> px-3 py-2 rounded-pill fw-bold text-uppercase">
            <?= sanitize($run['status']) ?>
        </span>
    </div>

    <!-- Header Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
        <div class="card-body p-4 bg-light border-bottom">
            <div class="row align-items-center g-3">
                <div class="col-md-7">
                    <span class="badge bg-secondary px-2 py-1 rounded-pill small mb-2">TIFFIN MANIFEST</span>
                    <h3 class="fw-bold mb-1">Run #<?= sanitize($run['run_code']) ?></h3>
                    <div class="text-muted small">
                        <i class="bi bi-calendar-event me-1"></i> <?= date('D, M d, Y', strtotime($run['delivery_date'])) ?> &bull;
                        <span class="text-uppercase fw-bold text-primary"><?= sanitize($run['meal_type']) ?></span> &bull;
                        <i class="bi bi-geo-alt me-1"></i> <?= sanitize($run['city']) ?>
                    </div>
                </div>
                <div class="col-md-5 text-md-end">
                    <div class="d-inline-block text-start text-md-end me-md-3">
                        <small class="text-muted d-block" style="font-size:0.75rem;">BATCH PAYOUT</small>
                        <span class="fs-3 fw-bold text-success">₹<?= number_format($run['total_payout'], 2) ?></span>
                    </div>
                    <?php if ($run['status'] === 'assigned'): ?>
                        <form method="POST" action="<?= url('/delivery/run/' . $run['id'] . '/start') ?>" class="d-inline-block">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-success fw-bold px-4 py-2 rounded-pill shadow">
                                <i class="bi bi-play-circle-fill me-1"></i> Start Run Now
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php
        $completedCount = 0;
        foreach ($stops as $s) {
            if ($s['status'] === 'completed') $completedCount++;
        }
        $totalStops = count($stops);
        $pct = $totalStops > 0 ? round(($completedCount / $totalStops) * 100) : 0;
        ?>

        <!-- Progress Overview -->
        <div class="card-body px-4 py-3 bg-white">
            <div class="d-flex justify-content-between small text-muted mb-1">
                <span>Completed Stops: <strong><?= $completedCount ?> / <?= $totalStops ?></strong></span>
                <span><?= $pct ?>% Complete</span>
            </div>
            <div class="progress" style="height: 6px;">
                <div class="progress-bar bg-success" style="width: <?= $pct ?>%;"></div>
            </div>
        </div>
    </div>

    <!-- Waypoint Stop-by-Stop List -->
    <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
        <i class="bi bi-signpost-split-fill text-warning"></i> Sequential Stop-by-Stop Route
    </h5>

    <div class="d-flex flex-column gap-3 mb-5">
        <?php foreach ($stops as $idx => $stop): ?>
            <?php 
            $isPickup = ($stop['stop_type'] === 'pickup');
            $isCompleted = ($stop['status'] === 'completed');
            $cardBg = $isCompleted ? 'bg-white border-success' : ($isPickup ? 'bg-white border-primary' : 'bg-white border');
            ?>
            <div class="card border-2 shadow-sm rounded-4 <?= $cardBg ?> overflow-hidden">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge <?= $isCompleted ? 'bg-success' : 'bg-dark' ?> rounded-circle p-2" style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;">
                                <?= $stop['stop_sequence'] ?>
                            </span>
                            <?php if ($isPickup): ?>
                                <span class="badge bg-primary text-white px-3 py-1 rounded-pill fw-bold">
                                    <i class="bi bi-shop me-1"></i> KITCHEN PICKUP
                                </span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark px-3 py-1 rounded-pill fw-bold">
                                    <i class="bi bi-person-fill me-1"></i> CUSTOMER DROP-OFF
                                </span>
                            <?php endif; ?>

                            <?php if ($isCompleted): ?>
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1 rounded-pill small">
                                    <i class="bi bi-check-circle-fill me-1"></i> Completed at <?= date('h:i A', strtotime($stop['completed_at'])) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($stop['items_count'])): ?>
                            <span class="badge bg-light text-dark border px-2 py-1 rounded-pill small fw-semibold">
                                <i class="bi bi-box-seam me-1"></i> <?= $stop['items_count'] ?> <?= $isPickup ? 'Boxes to Collect' : 'Tiffin Box' ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="row g-3 align-items-center">
                        <div class="col-md-7">
                            <h5 class="fw-bold mb-1 text-dark">
                                <?= sanitize($stop['target_name']) ?>
                            </h5>
                            <p class="text-muted small mb-2">
                                <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                                <?= sanitize($stop['target_address']) ?>
                            </p>
                            <?php if (!empty($stop['special_instructions'])): ?>
                                <div class="small text-secondary bg-light p-2 rounded-2 border">
                                    <i class="bi bi-info-circle me-1 text-primary"></i> <?= sanitize($stop['special_instructions']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-5 text-md-end">
                            <div class="d-flex gap-2 justify-content-md-end mb-2">
                                <?php if (!empty($stop['contact_phone'])): ?>
                                    <a href="tel:<?= sanitize($stop['contact_phone']) ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                                        <i class="bi bi-telephone-fill text-success me-1"></i> Call
                                    </a>
                                <?php endif; ?>

                                <?php 
                                $mapQuery = !empty($stop['latitude']) && !empty($stop['longitude']) 
                                    ? $stop['latitude'] . ',' . $stop['longitude'] 
                                    : urlencode($stop['target_address']);
                                ?>
                                <a href="https://www.google.com/maps/search/?api=1&query=<?= $mapQuery ?>" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                    <i class="bi bi-cursor-fill me-1"></i> Map Directions
                                </a>
                            </div>

                            <!-- Stop Actions -->
                            <?php if (!$isCompleted && $run['status'] === 'in_progress'): ?>
                                <div class="mt-3 pt-3 border-top text-start text-md-end">
                                    <form method="POST" action="<?= url('/delivery/run/' . $run['id'] . '/stop/' . $stop['id'] . '/complete') ?>" class="d-inline-block text-start">
                                        <?= csrf_field() ?>

                                        <?php if (!$isPickup): ?>
                                            <!-- OTP Input for customer drop -->
                                            <div class="mb-2">
                                                <label class="form-label small fw-bold text-muted mb-1">Customer 4-Digit OTP:</label>
                                                <input type="text" name="delivery_otp" maxlength="6" class="form-control form-control-sm font-monospace text-center fw-bold" placeholder="e.g. 4821" required style="width:130px; letter-spacing: 2px;">
                                            </div>

                                            <!-- Reusable Box Return Checkbox -->
                                            <div class="form-check form-switch small mb-3">
                                                <input class="form-check-input" type="checkbox" name="container_returned" id="ret_<?= $stop['id'] ?>" value="1" checked>
                                                <label class="form-check-label text-success fw-semibold" for="ret_<?= $stop['id'] ?>">
                                                    <i class="bi bi-arrow-repeat me-1"></i> Collected Yesterday's Empty Dabba
                                                </label>
                                            </div>

                                            <button type="submit" class="btn btn-success fw-bold px-4 rounded-pill shadow-sm">
                                                <i class="bi bi-check2-circle me-1"></i> Complete Delivery
                                            </button>
                                        <?php else: ?>
                                            <!-- Pickup confirmation -->
                                            <button type="submit" class="btn btn-primary fw-bold px-4 rounded-pill shadow-sm">
                                                <i class="bi bi-box-arrow-in-down me-1"></i> Confirm Pickup (<?= $stop['items_count'] ?> Boxes)
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
