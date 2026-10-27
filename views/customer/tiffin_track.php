<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Back Navigation -->
            <div class="mb-3">
                <a href="<?= url('/my-subscriptions') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i> My Subscriptions
                </a>
            </div>

            <!-- Main Tracking Card -->
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-4">
                <!-- Header Banner -->
                <div class="card-body p-4 text-white" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-warning text-dark px-3 py-1 rounded-pill fw-bold text-uppercase">
                            <i class="bi bi-box-seam me-1"></i> DAILY TIFFIN TRACKER
                        </span>
                        <span class="badge bg-light text-dark px-2 py-1 rounded-pill small">
                            <?= date('D, M d', strtotime($tracking['delivery_date'])) ?>
                        </span>
                    </div>
                    <h3 class="fw-bold mb-1">Today's <?= ucfirst($tracking['meal_type']) ?> Delivery</h3>
                    <div class="text-white-50 small">
                        From: <strong class="text-white"><?= sanitize($tracking['kitchen_name']) ?></strong> &bull; <?= sanitize($tracking['delivery_time']) ?>
                    </div>
                </div>

                <!-- Status Context Card -->
                <div class="card-body p-4 bg-white">
                    <?php if ($tracking['my_stop_status'] === 'completed'): ?>
                        <!-- Delivered State -->
                        <div class="text-center py-4">
                            <div class="avatar-circle mx-auto mb-3 bg-success text-white shadow" style="width:70px;height:70px;display:flex;align-items:center;justify-content:center;border-radius:50%;font-size:2rem;">
                                <i class="bi bi-check-lg"></i>
                            </div>
                            <h4 class="fw-bold text-success mb-1">Tiffin Delivered!</h4>
                            <p class="text-muted small mb-3">
                                Delivered at <?= date('h:i A', strtotime($tracking['completed_at'])) ?>. Enjoy your home-cooked meal!
                            </p>
                            <?php if (!empty($tracking['container_returned'])): ?>
                                <span class="badge bg-success-subtle text-success border border-success px-3 py-2 rounded-pill small">
                                    <i class="bi bi-arrow-repeat me-1"></i> Yesterday's Empty Dabba Exchanged Successfully
                                </span>
                            <?php endif; ?>
                        </div>

                    <?php elseif ($tracking['run_status'] === 'in_progress'): ?>
                        <!-- In Progress Run -->
                        <?php if ($tracking['is_next']): ?>
                            <!-- Active Leg: Customer is Next! -->
                            <div class="alert alert-success border-success py-3 px-4 rounded-4 shadow-sm mb-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="spinner-grow text-success" role="status" style="width: 2rem; height: 2rem;"></div>
                                    <div>
                                        <h5 class="fw-bold text-success mb-1">You Are Next!</h5>
                                        <div class="text-dark small">
                                            Rider is currently on the way directly to your doorstep. Estimated arrival: <strong>5–10 mins</strong>.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Queue Position Info -->
                            <div class="card border-0 bg-light rounded-4 p-4 mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <span class="text-muted small d-block">YOUR QUEUE POSITION</span>
                                        <h4 class="fw-bold text-primary mb-0">
                                            Stop #<?= $tracking['my_stop_sequence'] ?> <span class="text-muted fs-6 fw-normal">of <?= $tracking['total_dropoff_stops'] ?> customers</span>
                                        </h4>
                                    </div>
                                    <span class="badge bg-primary px-3 py-2 rounded-pill fw-semibold">
                                        <?= $tracking['stops_away'] ?> Stop<?= $tracking['stops_away'] > 1 ? 's' : '' ?> Away
                                    </span>
                                </div>

                                <div class="progress mb-2" style="height: 8px;">
                                    <?php 
                                    $progressPct = max(5, round((($tracking['current_active_sequence'] - 1) / max(1, $tracking['my_stop_sequence'])) * 100));
                                    ?>
                                    <div class="progress-bar bg-primary progress-bar-striped progress-bar-animated" style="width: <?= $progressPct ?>%;"></div>
                                </div>
                                <div class="d-flex justify-content-between small text-muted">
                                    <span>Pickups Completed</span>
                                    <span>Your Doorstep</span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- OTP Box for Handover -->
                        <div class="card border border-warning bg-warning bg-opacity-10 rounded-4 p-4 text-center mb-4">
                            <span class="text-muted small fw-bold text-uppercase d-block mb-1">YOUR DELIVERY OTP</span>
                            <div class="display-6 fw-bold font-monospace text-dark tracking-widest mb-1" style="letter-spacing: 6px;">
                                <?= sanitize($tracking['delivery_otp']) ?>
                            </div>
                            <small class="text-muted">Share this 4-digit code with your rider when they reach your doorstep.</small>
                        </div>

                    <?php else: ?>
                        <!-- Preparing in Kitchen -->
                        <div class="card border-0 bg-light rounded-4 p-4 text-center my-3">
                            <div class="avatar-circle mx-auto mb-3 bg-warning text-dark shadow-sm" style="width:60px;height:60px;display:flex;align-items:center;justify-content:center;border-radius:50%;font-size:1.5rem;">
                                <i class="bi bi-fire"></i>
                            </div>
                            <h5 class="fw-bold mb-1">Chef is Cooking Your Tiffin</h5>
                            <p class="text-muted small mb-0">
                                Your lunch run has been compiled (#<?= sanitize($tracking['run_code']) ?>). Rider pickup begins at 11:30 AM!
                            </p>
                        </div>
                    <?php endif; ?>

                    <!-- Zero Waste Empty Box Reminder -->
                    <div class="alert alert-light border rounded-3 p-3 mb-4 d-flex align-items-center gap-3">
                        <i class="bi bi-recycle text-success fs-3"></i>
                        <div class="small">
                            <strong class="text-success">Zero-Waste Exchange:</strong>
                            <span class="text-muted">Please keep yesterday's clean empty container ready to swap when today's fresh lunch arrives!</span>
                        </div>
                    </div>

                    <!-- Rider Info (if assigned) -->
                    <?php if (!empty($tracking['rider_name'])): ?>
                        <div class="border-top pt-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-circle bg-light border text-dark fw-bold" style="width:40px;height:40px;display:flex;align-items:center;justify-content:center;border-radius:50%;">
                                        <i class="bi bi-bicycle text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold small"><?= sanitize($tracking['rider_name']) ?></div>
                                        <div class="text-muted small" style="font-size:0.75rem;">
                                            <?= sanitize($tracking['vehicle_type'] ?? 'Motorcycle') ?> &bull; <?= sanitize($tracking['vehicle_number'] ?? '') ?>
                                        </div>
                                    </div>
                                </div>
                                <?php if (!empty($tracking['rider_phone'])): ?>
                                    <a href="tel:<?= sanitize($tracking['rider_phone']) ?>" class="btn btn-outline-success btn-sm rounded-pill px-3">
                                        <i class="bi bi-telephone me-1"></i> Call Rider
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
