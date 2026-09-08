<div class="container py-4">
    <!-- Admin Dashboard Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-shield-lock text-warning me-2"></i>Admin Dashboard
            </h2>
            <p class="text-muted mb-0">Platform overview and management console</p>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="<?= url('/admin/chefs') ?>" class="btn btn-kravyo-primary">
                <i class="bi bi-shop me-1"></i> Verify Kitchens
            </a>
            <a href="<?= url('/admin/users') ?>" class="btn btn-outline-secondary">
                <i class="bi bi-people me-1"></i> Users
            </a>
        </div>
    </div>

    <!-- Stats Cards Row -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-2">
            <div class="stat-card stat-card-users">
                <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
                <div class="stat-value"><?= $stats['total_users'] ?? 0 ?></div>
                <div class="stat-label">Total Users</div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="stat-card stat-card-chefs">
                <div class="stat-icon"><i class="bi bi-shop"></i></div>
                <div class="stat-value"><?= $stats['total_chefs'] ?? 0 ?></div>
                <div class="stat-label">Chefs</div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="stat-card stat-card-pending">
                <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
                <div class="stat-value"><?= $stats['pending_kitchens'] ?? 0 ?></div>
                <div class="stat-label">Pending Approval</div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="stat-card stat-card-approved">
                <div class="stat-icon"><i class="bi bi-check-circle-fill"></i></div>
                <div class="stat-value"><?= $stats['approved_kitchens'] ?? 0 ?></div>
                <div class="stat-label">Approved Kitchens</div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="stat-card" style="background:linear-gradient(135deg,#4361ee,#3a0ca3);color:#fff;">
                <div class="stat-icon"><i class="bi bi-bag-check"></i></div>
                <div class="stat-value"><?= $stats['total_orders'] ?? 0 ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="stat-card" style="background:linear-gradient(135deg,#2a9d8f,#264653);color:#fff;">
                <div class="stat-icon"><i class="bi bi-currency-rupee"></i></div>
                <div class="stat-value">₹<?= number_format((float)($stats['platform_revenue'] ?? 0), 0) ?></div>
                <div class="stat-label">Platform Revenue</div>
            </div>
        </div>
    </div>

    <!-- Pending Kitchens Quick List -->
    <div class="card kravyo-card border-0">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-hourglass-split me-2 text-warning"></i>Kitchens Awaiting Approval
                </h5>
                <a href="<?= url('/admin/chefs?status=pending') ?>" class="btn btn-sm btn-outline-warning">View All</a>
            </div>

            <?php if (empty($pendingKitchens)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-check-circle display-3 text-success d-block mb-3 opacity-50"></i>
                    <p class="text-muted fw-semibold">All kitchens are reviewed! No pending approvals.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Chef Name</th>
                                <th>Kitchen Name</th>
                                <th>City</th>
                                <th>FSSAI</th>
                                <th>Applied On</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($pendingKitchens, 0, 5) as $pk): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-circle"><i class="bi bi-person"></i></div>
                                        <div>
                                            <strong><?= sanitize($pk['chef_name']) ?></strong>
                                            <br><small class="text-muted"><?= sanitize($pk['chef_email']) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td class="fw-semibold"><?= sanitize($pk['kitchen_name']) ?></td>
                                <td><?= sanitize($pk['city'] ?: '--') ?></td>
                                <td>
                                    <?php if (!empty($pk['fssai_license'])): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success"><?= sanitize($pk['fssai_license']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><small class="text-muted"><?= date('d M Y', strtotime($pk['created_at'])) ?></small></td>
                                <td class="text-end">
                                    <a href="<?= url('/admin/chefs?status=pending') ?>" class="btn btn-sm btn-warning">
                                        <i class="bi bi-eye me-1"></i>Review
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Admin Navigation -->
    <div class="row g-3 mt-3">
        <div class="col-md-4">
            <a href="<?= url('/admin/chefs') ?>" class="card kravyo-card border-0 text-decoration-none text-dark h-100">
                <div class="card-body p-4 text-center">
                    <i class="bi bi-shop display-4 text-warning d-block mb-2"></i>
                    <h6 class="fw-bold">Kitchen Management</h6>
                    <p class="text-muted small mb-0">Approve, reject, and manage kitchen profiles</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="<?= url('/admin/categories') ?>" class="card kravyo-card border-0 text-decoration-none text-dark h-100">
                <div class="card-body p-4 text-center">
                    <i class="bi bi-grid display-4 text-info d-block mb-2"></i>
                    <h6 class="fw-bold">Food Categories</h6>
                    <p class="text-muted small mb-0">Manage food categories & tags (Phase 4)</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="<?= url('/admin/reports') ?>" class="card kravyo-card border-0 text-decoration-none text-dark h-100">
                <div class="card-body p-4 text-center">
                    <i class="bi bi-graph-up display-4 text-success d-block mb-2"></i>
                    <h6 class="fw-bold">Analytics & Reports</h6>
                    <p class="text-muted small mb-0">Revenue, order trends & top kitchen insights</p>
                </div>
            </a>
        </div>
    </div>
</div>
