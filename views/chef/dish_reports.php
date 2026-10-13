<div class="container-fluid">
    <div class="row">
        <?php View::partial('chef_sidebar'); ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <?php View::partial('flash_messages'); ?>

            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-4 border-bottom">
                <h1 class="h2"><i class="bi bi-chat-square-dots text-warning me-2"></i>Dish Reports & Feedback</h1>
            </div>

            <div class="alert alert-info">
                <i class="bi bi-info-circle-fill me-2"></i> This dashboard shows specific reports filed by customers regarding your dishes. Use this feedback to improve food quality.
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Order ID</th>
                                    <th>Dish</th>
                                    <th>Reason</th>
                                    <th>Customer Comment</th>
                                    <th>Status (Admin)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($reports)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No dish reports found. Excellent!</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($reports as $r): ?>
                                <tr>
                                    <td><?= date('M d, Y', strtotime($r['created_at'])) ?></td>
                                    <td><?= sanitize($r['customer_name']) ?></td>
                                    <td><?= $r['order_id'] ? '#' . sanitize($r['order_id']) : 'N/A' ?></td>
                                    <td>
                                        <?php if (!empty($r['dish_name'])): ?>
                                            <strong><?= sanitize($r['dish_name']) ?></strong> <small class="text-muted">(#<?= sanitize($r['target_id']) ?>)</small>
                                        <?php else: ?>
                                            <?= $r['target_id'] ? '#' . sanitize($r['target_id']) : 'N/A' ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-secondary"><?= sanitize($r['reason']) ?></span></td>
                                    <td><?= sanitize($r['description']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $r['status'] === 'pending' ? 'warning text-dark' : ($r['status'] === 'reviewed' ? 'info' : 'success') ?>">
                                            <?= ucfirst($r['status']) ?>
                                        </span>
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
