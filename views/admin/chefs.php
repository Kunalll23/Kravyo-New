<div class="container py-4">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-shop-window text-warning me-2"></i>Kitchen Verification Queue
            </h2>
            <p class="text-muted mb-0">Review, approve, or reject home chef kitchen applications</p>
        </div>
        <a href="<?= url('/admin/dashboard') ?>" class="btn btn-outline-secondary mt-3 mt-md-0">
            <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>

    <!-- Filter Tabs -->
    <ul class="nav nav-pills admin-filter-tabs mb-4" role="tablist">
        <li class="nav-item">
            <a class="nav-link <?= $filter === 'all' ? 'active' : '' ?>" href="<?= url('/admin/chefs?status=all') ?>">
                All <span class="badge bg-secondary ms-1"><?= $counts['all'] ?? 0 ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $filter === 'pending' ? 'active' : '' ?>" href="<?= url('/admin/chefs?status=pending') ?>">
                <i class="bi bi-hourglass-split me-1"></i>Pending <span class="badge bg-warning text-dark ms-1"><?= $counts['pending'] ?? 0 ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $filter === 'approved' ? 'active' : '' ?>" href="<?= url('/admin/chefs?status=approved') ?>">
                <i class="bi bi-check-circle me-1"></i>Approved <span class="badge bg-success ms-1"><?= $counts['approved'] ?? 0 ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $filter === 'rejected' ? 'active' : '' ?>" href="<?= url('/admin/chefs?status=rejected') ?>">
                <i class="bi bi-x-circle me-1"></i>Rejected <span class="badge bg-danger ms-1"><?= $counts['rejected'] ?? 0 ?></span>
            </a>
        </li>
    </ul>

    <!-- Kitchen Cards -->
    <?php if (empty($kitchens)): ?>
        <div class="text-center py-5">
            <i class="bi bi-inbox display-3 text-muted d-block mb-3 opacity-25"></i>
            <p class="text-muted fw-semibold">No kitchens found for the selected filter.</p>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($kitchens as $kitchen): ?>
            <div class="col-lg-6">
                <div class="card kravyo-card border-0 admin-kitchen-card">
                    <!-- Banner -->
                    <?php if (!empty($kitchen['banner_image'])): ?>
                        <div class="admin-kitchen-banner">
                            <img src="<?= UPLOAD_URL . '/kitchens/' . $kitchen['banner_image'] ?>"
                                 alt="Kitchen Banner" class="w-100" style="height: 140px; object-fit: cover;">
                        </div>
                    <?php else: ?>
                        <div class="admin-kitchen-banner admin-kitchen-banner-placeholder">
                            <i class="bi bi-image text-muted opacity-50"></i>
                            <span class="text-muted small">No Banner</span>
                        </div>
                    <?php endif; ?>

                    <div class="card-body p-4">
                        <!-- Kitchen Header -->
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="fw-bold mb-1"><?= sanitize($kitchen['kitchen_name']) ?></h5>
                                <div class="d-flex align-items-center gap-2 text-muted small">
                                    <i class="bi bi-person"></i>
                                    <span><?= sanitize($kitchen['chef_name']) ?></span>
                                    <span>•</span>
                                    <span><?= sanitize($kitchen['chef_email']) ?></span>
                                </div>
                            </div>
                            <span class="badge-status badge-status-<?= $kitchen['approval_status'] ?>">
                                <?= ucfirst($kitchen['approval_status']) ?>
                            </span>
                        </div>

                        <!-- Kitchen Details -->
                        <div class="row g-2 mb-3 small">
                            <div class="col-6">
                                <i class="bi bi-geo-alt text-muted me-1"></i>
                                <strong>Location:</strong> <?= sanitize($kitchen['city'] ?: '--') ?>, <?= sanitize($kitchen['pincode'] ?: '--') ?>
                            </div>
                            <div class="col-6">
                                <i class="bi bi-telephone text-muted me-1"></i>
                                <strong>Phone:</strong> <?= sanitize($kitchen['chef_phone'] ?? '--') ?>
                            </div>
                            <div class="col-6">
                                <i class="bi bi-shield-check text-muted me-1"></i>
                                <strong>FSSAI:</strong>
                                <?= !empty($kitchen['fssai_license']) ? sanitize($kitchen['fssai_license']) : '<span class="text-danger">Not provided</span>' ?>
                            </div>
                            <div class="col-6">
                                <i class="bi bi-patch-check text-muted me-1"></i>
                                <strong>Hygiene:</strong>
                                <?php if ($kitchen['hygiene_badge'] === 'verified'): ?>
                                    <span class="badge-hygiene">Verified</span>
                                <?php else: ?>
                                    <span class="text-muted">Unverified</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Kitchen Story -->
                        <?php if (!empty($kitchen['personal_story'])): ?>
                            <div class="mb-3 p-3 bg-light rounded-3">
                                <small class="fw-semibold d-block mb-1"><i class="bi bi-chat-quote me-1"></i>Kitchen Story:</small>
                                <p class="small text-muted mb-0"><?= sanitize(substr($kitchen['personal_story'], 0, 200)) ?><?= strlen($kitchen['personal_story']) > 200 ? '...' : '' ?></p>
                            </div>
                        <?php endif; ?>

                        <!-- Hygiene Certificate Preview -->
                        <?php if (!empty($kitchen['hygiene_certificate_image'])): ?>
                            <div class="mb-3">
                                <small class="fw-semibold d-block mb-2"><i class="bi bi-file-earmark-image me-1"></i>Hygiene Certificate:</small>
                                <img src="<?= UPLOAD_URL . '/kitchens/' . $kitchen['hygiene_certificate_image'] ?>"
                                     alt="Hygiene Certificate" class="img-thumbnail" style="max-height: 120px;">
                            </div>
                        <?php endif; ?>

                        <!-- Admin Notes (if any) -->
                        <?php if (!empty($kitchen['admin_notes'])): ?>
                            <div class="alert alert-info py-2 px-3 small mb-3">
                                <i class="bi bi-sticky me-1"></i><strong>Admin Notes:</strong> <?= sanitize($kitchen['admin_notes']) ?>
                            </div>
                        <?php endif; ?>

                        <!-- Applied Date -->
                        <div class="text-muted small mb-3">
                            <i class="bi bi-calendar3 me-1"></i>Applied: <?= date('d M Y, h:i A', strtotime($kitchen['created_at'])) ?>
                        </div>

                        <!-- Action Buttons -->
                        <?php if ($kitchen['approval_status'] === 'pending'): ?>
                            <div class="d-flex gap-2">
                                <form action="<?= url('/admin/chef/verify') ?>" method="POST" class="flex-fill">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="kitchen_id" value="<?= $kitchen['id'] ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <input type="hidden" name="admin_notes" value="Kitchen meets all quality standards.">
                                    <button type="submit" class="btn btn-success w-100"
                                            onclick="return confirm('Approve this kitchen? This will make it visible to customers.')">
                                        <i class="bi bi-check-lg me-1"></i> Approve
                                    </button>
                                </form>
                                <button type="button" class="btn btn-outline-danger flex-fill"
                                        data-bs-toggle="modal" data-bs-target="#rejectModal<?= $kitchen['id'] ?>">
                                    <i class="bi bi-x-lg me-1"></i> Reject
                                </button>
                            </div>

                            <!-- Reject Modal -->
                            <div class="modal fade" id="rejectModal<?= $kitchen['id'] ?>" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <form action="<?= url('/admin/chef/verify') ?>" method="POST">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="kitchen_id" value="<?= $kitchen['id'] ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <div class="modal-header border-0 pb-0">
                                                <h5 class="modal-title fw-bold">
                                                    <i class="bi bi-x-circle text-danger me-2"></i>Reject Kitchen
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p class="text-muted">Rejecting <strong><?= sanitize($kitchen['kitchen_name']) ?></strong> by <?= sanitize($kitchen['chef_name']) ?>.</p>
                                                <div class="mb-3">
                                                    <label for="adminNotes<?= $kitchen['id'] ?>" class="form-label fw-semibold">
                                                        Reason for Rejection <span class="text-danger">*</span>
                                                    </label>
                                                    <textarea class="form-control" id="adminNotes<?= $kitchen['id'] ?>" name="admin_notes" rows="3"
                                                              placeholder="e.g., Incomplete address details. Please provide full address with landmark." required></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-0 pt-0">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger">
                                                    <i class="bi bi-x-lg me-1"></i>Confirm Rejection
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        <?php elseif ($kitchen['approval_status'] === 'approved'): ?>
                            <div class="d-flex align-items-center gap-2 text-success small">
                                <i class="bi bi-check-circle-fill"></i>
                                <span>This kitchen is approved and visible to customers.</span>
                            </div>

                        <?php elseif ($kitchen['approval_status'] === 'rejected'): ?>
                            <form action="<?= url('/admin/chef/verify') ?>" method="POST">
                                <?= csrf_field() ?>
                                <input type="hidden" name="kitchen_id" value="<?= $kitchen['id'] ?>">
                                <input type="hidden" name="action" value="approve">
                                <input type="hidden" name="admin_notes" value="Re-approved after review.">
                                <button type="submit" class="btn btn-outline-success btn-sm"
                                        onclick="return confirm('Re-approve this kitchen?')">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Re-Approve Kitchen
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
