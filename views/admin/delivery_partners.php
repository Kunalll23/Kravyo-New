
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <h2 class="fw-bold mb-0">Delivery Partners Management</h2>
    <div class="d-flex align-items-center bg-light p-2 rounded shadow-sm border">
        <form action="<?= url('/admin/delivery-partners/force-status') ?>" method="POST" class="d-flex align-items-center gap-2 m-0">
            <?= csrf_field() ?>
            <select name="city" class="form-select form-select-sm" style="width: 200px;">
                <option value="">All Cities (Global)</option>
                <?php foreach ($availableCities as $c): ?>
                    <option value="<?= sanitize($c) ?>"><?= sanitize($c) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="action" value="online" class="btn btn-sm btn-success fw-bold text-nowrap"><i class="bi bi-broadcast me-1"></i> Force Online</button>
            <button type="submit" name="action" value="offline" class="btn btn-sm btn-secondary fw-bold text-nowrap"><i class="bi bi-broadcast-pin me-1"></i> Force Offline</button>
        </form>
    </div>
</div>

<?php View::partial('flash_messages'); ?>

<div class="card kravyo-card border-0 mb-4">
    <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
        <ul class="nav nav-tabs card-header-tabs" id="deliveryTabs">
            <li class="nav-item">
                <a class="nav-link <?= empty($statusFilter) ? 'active fw-bold text-primary' : 'text-muted' ?>" href="<?= url('/admin/delivery-partners') ?>">All Partners</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $statusFilter === DELIVERY_VERIFICATION_PENDING ? 'active fw-bold text-warning' : 'text-muted' ?>" href="<?= url('/admin/delivery-partners?status=' . DELIVERY_VERIFICATION_PENDING) ?>">Pending</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $statusFilter === DELIVERY_VERIFICATION_APPROVED ? 'active fw-bold text-success' : 'text-muted' ?>" href="<?= url('/admin/delivery-partners?status=' . DELIVERY_VERIFICATION_APPROVED) ?>">Approved</a>
            </li>
        </ul>
    </div>
    
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Partner Info</th>
                    <th>Vehicle Details</th>
                    <th>Deliveries</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($partners)): ?>
                    <tr><td colspan="5" class="text-center py-4 text-muted">No delivery partners found.</td></tr>
                <?php else: ?>
                    <?php foreach ($partners as $dp): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold"><?= sanitize($dp['full_name']) ?></div>
                                <div class="small text-muted"><i class="bi bi-telephone"></i> <?= sanitize($dp['phone']) ?></div>
                                <div class="small text-muted"><i class="bi bi-envelope"></i> <?= sanitize($dp['email']) ?></div>
                            </td>
                            <td>
                                <div class="fw-semibold text-capitalize"><i class="bi bi-bicycle text-primary me-1"></i> <?= sanitize($dp['vehicle_type']) ?></div>
                                <div class="small text-muted">No: <span class="text-uppercase"><?= sanitize($dp['vehicle_number']) ?></span></div>
                                <div class="small text-muted">DL: <span class="text-uppercase"><?= sanitize($dp['driving_license_number']) ?></span></div>
                            </td>
                            <td>
                                <div><span class="badge bg-light text-dark border"><?= $dp['total_deliveries'] ?> Deliveries</span></div>
                                <div class="small fw-bold text-success mt-1">₹<?= number_format($dp['total_earnings'], 2) ?></div>
                            </td>
                            <td>
                                <?php
                                $statusColors = [
                                    'pending' => 'warning',
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    'suspended' => 'secondary'
                                ];
                                $color = $statusColors[$dp['verification_status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $color ?> rounded-pill text-capitalize mb-1">
                                    <?= $dp['verification_status'] ?>
                                </span>
                                <?php if ($dp['verification_status'] === 'approved'): ?>
                                    <div class="small">
                                        <?php if ($dp['is_online']): ?>
                                            <i class="bi bi-circle-fill text-success" style="font-size:0.6rem;"></i> Online
                                        <?php else: ?>
                                            <i class="bi bi-circle-fill text-muted" style="font-size:0.6rem;"></i> Offline
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#actionModal<?= $dp['id'] ?>">
                                    Manage
                                </button>
                                
                                <!-- Action Modal -->
                                <div class="modal fade" id="actionModal<?= $dp['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <form action="<?= url('/admin/delivery-partners/update') ?>" method="POST" class="modal-content text-start">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="id" value="<?= $dp['id'] ?>">
                                            
                                            <div class="modal-header border-0 pb-0">
                                                <h5 class="modal-title fw-bold">Manage Partner: <?= sanitize($dp['full_name']) ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Update Status</label>
                                                    <select name="status" class="form-select" required>
                                                        <option value="approved" <?= $dp['verification_status'] === 'approved' ? 'selected' : '' ?>>Approve</option>
                                                        <option value="pending" <?= $dp['verification_status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                                        <option value="suspended" <?= $dp['verification_status'] === 'suspended' ? 'selected' : '' ?>>Suspend</option>
                                                        <option value="rejected" <?= $dp['verification_status'] === 'rejected' ? 'selected' : '' ?>>Reject</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Admin Notes (Optional)</label>
                                                    <textarea name="notes" class="form-control" rows="2"><?= sanitize($dp['admin_notes']) ?></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-0 pt-0">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary fw-bold">Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

