<?php include BASE_PATH . '/views/partials/header.php'; ?>

<div class="container-fluid bg-light min-vh-100 py-4">
    <div class="row">
        <?php include BASE_PATH . '/views/partials/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
                <h1 class="h2 fw-bold text-dark"><i class="bi bi-calendar2-check text-primary me-2"></i> Tiffin Subscriptions</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <form class="d-flex" method="GET" action="/admin/subscriptions">
                        <select name="status" class="form-select form-select-sm me-2" onchange="this.form.submit()">
                            <option value="">All Statuses</option>
                            <option value="pending" <?= ($statusFilter === 'pending') ? 'selected' : '' ?>>Pending</option>
                            <option value="active" <?= ($statusFilter === 'active') ? 'selected' : '' ?>>Active</option>
                            <option value="cancelled" <?= ($statusFilter === 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
                            <option value="completed" <?= ($statusFilter === 'completed') ? 'selected' : '' ?>>Completed</option>
                        </select>
                    </form>
                </div>
            </div>

            <?php include BASE_PATH . '/views/partials/alerts.php'; ?>

            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="ps-4">Customer</th>
                                    <th scope="col">Plan Details</th>
                                    <th scope="col">Kitchen</th>
                                    <th scope="col">Dates</th>
                                    <th scope="col">Status</th>
                                    <th scope="col" class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($subscriptions)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                            No subscriptions found.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($subscriptions as $sub): ?>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-bold"><?= sanitize($sub['customer_name']) ?></div>
                                                <div class="small text-muted"><?= sanitize($sub['customer_phone']) ?></div>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-primary"><?= sanitize($sub['plan_name']) ?></div>
                                                <div class="small text-muted">
                                                    <?= ucfirst($sub['plan_type']) ?> &bull; <?= $sub['meals_per_day'] ?> meals/day
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark"><?= sanitize($sub['kitchen_name']) ?></div>
                                                <div class="small text-muted"><i class="bi bi-geo-alt"></i> <?= sanitize($sub['kitchen_city']) ?></div>
                                            </td>
                                            <td>
                                                <div class="small">
                                                    <span class="d-block"><strong>Start:</strong> <?= date('M d, Y', strtotime($sub['start_date'])) ?></span>
                                                    <span class="d-block text-muted"><strong>End:</strong> <?= date('M d, Y', strtotime($sub['end_date'])) ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <?php
                                                $badgeClass = 'bg-secondary';
                                                if ($sub['status'] === SUBSCRIPTION_STATUS_PENDING) $badgeClass = 'bg-warning text-dark';
                                                elseif ($sub['status'] === SUBSCRIPTION_STATUS_ACTIVE) $badgeClass = 'bg-success';
                                                elseif ($sub['status'] === SUBSCRIPTION_STATUS_CANCELLED) $badgeClass = 'bg-danger';
                                                ?>
                                                <span class="badge rounded-pill <?= $badgeClass ?>">
                                                    <?= ucfirst($sub['status']) ?>
                                                </span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <?php if ($sub['status'] === SUBSCRIPTION_STATUS_PENDING): ?>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <form method="POST" action="/admin/subscription/approve/<?= $sub['id'] ?>" class="d-inline">
                                                            <input type="hidden" name="csrf_token" value="<?= Session::get('csrf_token') ?>">
                                                            <button type="submit" class="btn btn-success" title="Approve">
                                                                <i class="bi bi-check-lg"></i> Approve
                                                            </button>
                                                        </form>
                                                        <form method="POST" action="/admin/subscription/reject/<?= $sub['id'] ?>" class="d-inline ms-1">
                                                            <input type="hidden" name="csrf_token" value="<?= Session::get('csrf_token') ?>">
                                                            <button type="submit" class="btn btn-danger" title="Reject" onclick="return confirm('Are you sure you want to reject this subscription?');">
                                                                <i class="bi bi-x-lg"></i> Reject
                                                            </button>
                                                        </form>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted small">No actions</span>
                                                <?php endif; ?>
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

<?php include BASE_PATH . '/views/partials/footer.php'; ?>
