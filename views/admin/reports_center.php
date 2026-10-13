<div class="container-fluid">
    <div class="row">
        <?php View::partial('admin_sidebar'); ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <?php View::partial('flash_messages'); ?>

            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-4 border-bottom">
                <h1 class="h2"><i class="bi bi-shield-exclamation me-2 text-danger"></i>Resolution Center</h1>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Type</th>
                                    <th>Order ID</th>
                                    <th>Kitchen/Chef</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($reports)): ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">No reports found.</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($reports as $r): ?>
                                <tr>
                                    <td>#<?= $r['id'] ?></td>
                                    <td><?= date('M d, Y g:i A', strtotime($r['created_at'])) ?></td>
                                    <td><?= sanitize($r['customer_name']) ?></td>
                                    <td><span class="badge bg-secondary"><?= ucfirst(sanitize($r['target_type'])) ?></span></td>
                                    <td><?= $r['order_id'] ? '#' . sanitize($r['order_id']) : 'N/A' ?></td>
                                    <td><?= sanitize($r['kitchen_name']) ?></td>
                                    <td><?= sanitize($r['reason']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $r['status'] === 'pending' ? 'warning text-dark' : ($r['status'] === 'reviewed' ? 'info' : 'success') ?>">
                                            <?= ucfirst($r['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary action-btn"
                                            data-bs-toggle="modal" data-bs-target="#actionModal"
                                            data-id="<?= $r['id'] ?>"
                                            data-desc="<?= sanitize($r['description']) ?>"
                                            data-notes="<?= sanitize($r['admin_notes'] ?? '') ?>"
                                            data-status="<?= $r['status'] ?>">
                                            Review
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Action Modal -->
<div class="modal fade" id="actionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= url('/admin/reports-center/update') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="report_id" id="modalReportId">
                
                <div class="modal-header">
                    <h5 class="modal-title">Review Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Customer Description</label>
                        <p id="modalDesc" class="bg-light p-3 rounded border"></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Admin Notes</label>
                        <textarea name="admin_notes" id="modalNotes" class="form-control" rows="3" placeholder="Internal notes..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Status</label>
                        <select name="status" id="modalStatus" class="form-select">
                            <option value="pending">Pending</option>
                            <option value="reviewed">Reviewed</option>
                            <option value="resolved">Resolved</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.action-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('modalReportId').value = this.dataset.id;
        document.getElementById('modalDesc').textContent = this.dataset.desc;
        document.getElementById('modalNotes').value = this.dataset.notes;
        document.getElementById('modalStatus').value = this.dataset.status;
    });
});
</script>
