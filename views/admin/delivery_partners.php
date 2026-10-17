
<?php
function dpTabUrl(?string $status, ?string $city): string {
    $p = [];
    if (!empty($status) && $status !== 'all') {
        $p['status'] = $status;
    }
    if (!empty($city) && $city !== 'all') {
        $p['city'] = $city;
    }
    $q = http_build_query($p);
    return url('/admin/delivery-partners' . ($q ? '?' . $q : ''));
}
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h2 class="fw-bold mb-1">Delivery Partners Management</h2>
        <p class="text-muted small mb-0">
            <?= count($partners) ?> partner<?= count($partners) === 1 ? '' : 's' ?> found
            <?= !empty($cityFilter) ? ' in <span class="badge bg-primary text-white">' . sanitize($cityFilter) . '</span>' : ' across all regions' ?>
        </p>
    </div>
    
    <!-- City Filter Form -->
    <div class="d-flex align-items-center gap-2">
        <form action="<?= url('/admin/delivery-partners') ?>" method="GET" class="d-flex align-items-center gap-2 m-0" id="cityFilterForm">
            <?php if (!empty($statusFilter)): ?>
                <input type="hidden" name="status" value="<?= sanitize($statusFilter) ?>">
            <?php endif; ?>
            <div class="input-group input-group-sm" style="min-width: 220px;">
                <span class="input-group-text bg-white text-danger border-end-0"><i class="bi bi-geo-alt-fill"></i></span>
                <select name="city" class="form-select form-select-sm border-start-0 fw-semibold" onchange="this.form.submit()">
                    <option value="">All Cities (Global)</option>
                    <?php foreach ($availableCities as $c): ?>
                        <option value="<?= sanitize($c) ?>" <?= ($cityFilter === $c) ? 'selected' : '' ?>>
                            <?= sanitize($c) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if (!empty($cityFilter)): ?>
                <a href="<?= dpTabUrl($statusFilter, null) ?>" class="btn btn-sm btn-outline-secondary text-nowrap" title="Reset City Filter">
                    <i class="bi bi-x-circle me-1"></i> Reset
                </a>
            <?php endif; ?>
        </form>
    </div>
</div>

<?php View::partial('flash_messages'); ?>

<div class="card border-0 mb-4 shadow-sm">
    <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
        <ul class="nav nav-tabs card-header-tabs" id="deliveryTabs">
            <li class="nav-item">
                <a class="nav-link <?= empty($statusFilter) ? 'active fw-bold text-primary' : 'text-muted' ?>" href="<?= dpTabUrl(null, $cityFilter) ?>">All Partners</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $statusFilter === DELIVERY_VERIFICATION_PENDING ? 'active fw-bold text-warning' : 'text-muted' ?>" href="<?= dpTabUrl(DELIVERY_VERIFICATION_PENDING, $cityFilter) ?>">Pending</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $statusFilter === DELIVERY_VERIFICATION_APPROVED ? 'active fw-bold text-success' : 'text-muted' ?>" href="<?= dpTabUrl(DELIVERY_VERIFICATION_APPROVED, $cityFilter) ?>">Approved</a>
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
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bi bi-geo-alt display-6 text-muted mb-2 d-block"></i>
                            No delivery partners found<?= !empty($cityFilter) ? ' in <strong>' . sanitize($cityFilter) . '</strong>' : '' ?>.
                            <?php if (!empty($cityFilter) || !empty($statusFilter)): ?>
                                <div class="mt-2">
                                    <a href="<?= url('/admin/delivery-partners') ?>" class="btn btn-sm btn-outline-primary">
                                        View All Partners
                                    </a>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($partners as $dp): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold"><?= sanitize($dp['full_name']) ?></div>
                                <div class="small text-muted"><i class="bi bi-telephone"></i> <?= sanitize($dp['phone']) ?></div>
                                <div class="small text-muted"><i class="bi bi-envelope"></i> <?= sanitize($dp['email']) ?></div>
                                <?php if (!empty($dp['city'])): ?>
                                    <div class="small text-muted mt-1">
                                        <span class="badge bg-light text-dark border">
                                            <i class="bi bi-geo-alt-fill text-danger me-1"></i><?= sanitize($dp['city']) ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
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
                                    <div class="small mt-1">
                                        <?php
                                        $isLive = false;
                                        $activityLabel = 'Offline';
                                        if (!empty($dp['last_seen_at'])) {
                                            $diffSeconds = time() - strtotime($dp['last_seen_at']);
                                            if ((int)$dp['is_online'] === 1 && $diffSeconds <= 300) { // Active within last 5 mins
                                                $isLive = true;
                                                $activityLabel = 'Online';
                                            } elseif ($diffSeconds < 3600) {
                                                $mins = max(1, round($diffSeconds / 60));
                                                $activityLabel = "Last seen {$mins}m ago";
                                            } elseif ($diffSeconds < 86400) {
                                                $hrs = round($diffSeconds / 3600);
                                                $activityLabel = "Last seen {$hrs}h ago";
                                            } else {
                                                $days = round($diffSeconds / 86400);
                                                $activityLabel = "Last seen {$days}d ago";
                                            }
                                        }
                                        ?>
                                        <?php if ($isLive): ?>
                                            <span class="text-success fw-semibold"><i class="bi bi-circle-fill text-success" style="font-size:0.55rem;"></i> Online</span>
                                        <?php else: ?>
                                            <span class="text-muted"><i class="bi bi-circle-fill text-secondary" style="font-size:0.55rem;"></i> <?= sanitize($activityLabel) ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <button type="button" 
                                        class="btn btn-sm btn-outline-primary" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#managePartnerModal"
                                        data-id="<?= $dp['id'] ?>"
                                        data-name="<?= sanitize($dp['full_name']) ?>"
                                        data-status="<?= $dp['verification_status'] ?>"
                                        data-notes="<?= sanitize($dp['admin_notes'] ?? '') ?>">
                                    Manage
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Single Root-Level Manage Modal (Prevents Stacking-Context Flicker & Hover Oscillation) -->
<div class="modal fade" id="managePartnerModal" tabindex="-1" aria-labelledby="managePartnerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="<?= url('/admin/delivery-partners/update') ?>" method="POST" class="modal-content text-start shadow-lg border-0" style="border-radius:16px;">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="modalPartnerId" value="">
            
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="managePartnerModalLabel">Manage Partner: <span id="modalPartnerName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Update Status</label>
                    <select name="status" id="modalPartnerStatus" class="form-select" required>
                        <option value="approved">Approve</option>
                        <option value="pending">Pending</option>
                        <option value="suspended">Suspend</option>
                        <option value="rejected">Reject</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Admin Notes (Optional)</label>
                    <textarea name="notes" id="modalPartnerNotes" class="form-control" rows="3" placeholder="Add optional admin notes here..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary fw-bold px-4">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const manageModal = document.getElementById('managePartnerModal');
    if (manageModal) {
        manageModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (!button) return;
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            const status = button.getAttribute('data-status');
            const notes = button.getAttribute('data-notes');

            document.getElementById('modalPartnerId').value = id || '';
            document.getElementById('modalPartnerName').textContent = name || '';
            document.getElementById('modalPartnerStatus').value = status || 'pending';
            document.getElementById('modalPartnerNotes').value = notes || '';
        });
    }
});
</script>

