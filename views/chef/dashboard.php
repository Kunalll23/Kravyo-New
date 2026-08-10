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

        <!-- Quick Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="stat-card stat-card-orders">
                    <div class="stat-icon"><i class="bi bi-bag-check"></i></div>
                    <div class="stat-value">0</div>
                    <div class="stat-label">Total Orders</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card stat-card-earnings">
                    <div class="stat-icon"><i class="bi bi-currency-rupee"></i></div>
                    <div class="stat-value">₹0</div>
                    <div class="stat-label">Total Earnings</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card stat-card-menu">
                    <div class="stat-icon"><i class="bi bi-egg-fried"></i></div>
                    <div class="stat-value"><?= $menuItemCount ?? 0 ?></div>
                    <div class="stat-label">Menu Items</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card stat-card-reviews">
                    <div class="stat-icon"><i class="bi bi-star-half"></i></div>
                    <div class="stat-value">--</div>
                    <div class="stat-label">Avg Rating</div>
                </div>
            </div>
        </div>

        <!-- Kitchen Details Overview -->
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card kravyo-card border-0 h-100">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3"><i class="bi bi-shop me-2 text-warning"></i>Kitchen Information</h5>
                        <table class="table table-borderless mb-0">
                            <tr>
                                <td class="text-muted fw-semibold" style="width:160px">Kitchen Name</td>
                                <td class="fw-semibold"><?= sanitize($kitchen['kitchen_name']) ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold">Address</td>
                                <td><?= sanitize($kitchen['address'] ?: 'Not provided') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold">City / Pincode</td>
                                <td><?= sanitize($kitchen['city'] ?: '--') ?> — <?= sanitize($kitchen['pincode'] ?: '--') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold">FSSAI License</td>
                                <td>
                                    <?php if (!empty($kitchen['fssai_license'])): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success px-3 py-2">
                                            <i class="bi bi-shield-check me-1"></i><?= sanitize($kitchen['fssai_license']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">Not provided — <a href="<?= url('/chef/profile') ?>">Add now</a></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold">Hygiene Badge</td>
                                <td>
                                    <?php if ($kitchen['hygiene_badge'] === 'verified'): ?>
                                        <span class="badge-hygiene"><i class="bi bi-patch-check-fill me-1"></i>Verified</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary bg-opacity-25 text-dark px-3 py-2">Not Yet Verified</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card kravyo-card border-0 h-100">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3"><i class="bi bi-chat-quote me-2 text-warning"></i>Your Kitchen Story</h5>
                        <?php if (!empty($kitchen['personal_story'])): ?>
                            <p class="text-muted lh-lg"><?= nl2br(sanitize($kitchen['personal_story'])) ?></p>
                        <?php else: ?>
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-journal-text display-4 d-block mb-3 opacity-25"></i>
                                <p>Share your cooking journey and passion with customers!</p>
                                <a href="<?= url('/chef/profile') ?>" class="btn btn-sm btn-kravyo-outline">Add Your Story</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Banner Preview -->
        <?php if (!empty($kitchen['banner_image'])): ?>
        <div class="mt-4">
            <div class="card kravyo-card border-0 overflow-hidden">
                <div class="card-body p-3">
                    <h6 class="fw-bold mb-3"><i class="bi bi-image me-2 text-warning"></i>Kitchen Banner</h6>
                    <img src="<?= UPLOAD_URL . '/kitchens/' . $kitchen['banner_image'] ?>"
                         alt="Kitchen Banner" class="img-fluid rounded-3 w-100" style="max-height: 280px; object-fit: cover;">
                </div>
            </div>
        </div>
        <?php endif; ?>

    <?php endif; ?>
</div>
