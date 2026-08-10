<!-- Phase 7: Chef Tiffin Subscription Management -->
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-800 mb-1"><i class="bi bi-calendar2-week text-warning me-2"></i>Tiffin Subscription Plans</h2>
            <p class="text-muted mb-0">Create and manage weekly/monthly tiffin packages for your customers</p>
        </div>
        <a href="<?= url('/chef/dashboard') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>

    <!-- Quick Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card kravyo-card border-0 h-100">
                <div class="card-body p-4 d-flex align-items-center gap-3">
                    <div class="stat-icon-box bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-box-seam fs-4"></i>
                    </div>
                    <div>
                        <div class="fs-3 fw-800"><?= count($plans) ?></div>
                        <div class="text-muted small">Total Plans</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card kravyo-card border-0 h-100">
                <div class="card-body p-4 d-flex align-items-center gap-3">
                    <div class="stat-icon-box bg-success bg-opacity-10 text-success">
                        <i class="bi bi-people-fill fs-4"></i>
                    </div>
                    <div>
                        <div class="fs-3 fw-800"><?= $subscriberCount ?></div>
                        <div class="text-muted small">Active Subscribers</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card kravyo-card border-0 h-100">
                <div class="card-body p-4 d-flex align-items-center gap-3">
                    <div class="stat-icon-box bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-check-circle-fill fs-4"></i>
                    </div>
                    <div>
                        <div class="fs-3 fw-800"><?= count(array_filter($plans, fn($p) => $p['is_active'])) ?></div>
                        <div class="text-muted small">Active Plans</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create New Plan Card -->
    <div class="card kravyo-card border-0 mb-4">
        <div class="card-header bg-transparent border-bottom py-3">
            <h5 class="fw-bold mb-0"><i class="bi bi-plus-circle text-warning me-2"></i>Create New Tiffin Plan</h5>
        </div>
        <div class="card-body p-4">
            <form action="<?= url('/chef/subscription/add') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="plan_name" class="form-label fw-semibold">Plan Name</label>
                        <input type="text" class="form-control form-control-lg" id="plan_name" name="plan_name"
                               placeholder="e.g. Weekly Lunch Thali" required>
                    </div>
                    <div class="col-md-3">
                        <label for="plan_type" class="form-label fw-semibold">Plan Type</label>
                        <select class="form-select form-select-lg" id="plan_type" name="plan_type" required>
                            <option value="weekly">Weekly (7 Days)</option>
                            <option value="monthly">Monthly (30 Days)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="price" class="form-label fw-semibold">Price (₹)</label>
                        <input type="number" class="form-control form-control-lg" id="price" name="price"
                               placeholder="e.g. 1500" min="1" step="0.01" required>
                    </div>
                    <div class="col-md-3">
                        <label for="meals_per_day" class="form-label fw-semibold">Meals Per Day</label>
                        <select class="form-select form-select-lg" id="meals_per_day" name="meals_per_day">
                            <option value="1">1 Meal / Day</option>
                            <option value="2">2 Meals / Day</option>
                            <option value="3">3 Meals / Day</option>
                        </select>
                    </div>
                    <div class="col-md-9">
                        <label for="description" class="form-label fw-semibold">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="2"
                                  placeholder="Describe what's included in this plan (e.g. 2 rotis, dal, sabzi, rice, salad)"></textarea>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-kravyo-primary btn-lg">
                            <i class="bi bi-plus-lg me-1"></i> Create Plan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Existing Plans -->
    <div class="card kravyo-card border-0 mb-4">
        <div class="card-header bg-transparent border-bottom py-3">
            <h5 class="fw-bold mb-0"><i class="bi bi-list-ul text-warning me-2"></i>Your Subscription Plans</h5>
        </div>
        <div class="card-body p-4">
            <?php if (empty($plans)): ?>
                <div class="text-center py-5">
                    <div class="empty-state-icon mb-3"><i class="bi bi-calendar2-x"></i></div>
                    <h5 class="text-muted fw-bold">No Plans Created Yet</h5>
                    <p class="text-muted">Create your first tiffin plan above to start getting subscribers!</p>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($plans as $plan): ?>
                        <div class="col-lg-6">
                            <div class="subscription-card <?= $plan['is_active'] ? '' : 'subscription-card-inactive' ?>">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h5 class="fw-bold mb-1"><?= sanitize($plan['plan_name']) ?></h5>
                                        <span class="plan-type-badge plan-type-<?= $plan['plan_type'] ?>">
                                            <i class="bi bi-<?= $plan['plan_type'] === 'weekly' ? 'calendar-week' : 'calendar-month' ?> me-1"></i>
                                            <?= ucfirst($plan['plan_type']) ?>
                                        </span>
                                        <?php if (!$plan['is_active']): ?>
                                            <span class="badge bg-secondary ms-1">Inactive</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-end">
                                        <div class="fs-4 fw-800 text-primary"><?= format_currency($plan['price']) ?></div>
                                    </div>
                                </div>

                                <?php if (!empty($plan['description'])): ?>
                                    <p class="text-muted small mb-2"><?= sanitize($plan['description']) ?></p>
                                <?php endif; ?>

                                <div class="d-flex align-items-center gap-3 mb-3 text-muted small">
                                    <span><i class="bi bi-egg-fried me-1"></i><?= $plan['meals_per_day'] ?> meal<?= $plan['meals_per_day'] > 1 ? 's' : '' ?>/day</span>
                                    <span><i class="bi bi-clock me-1"></i>Created <?= date('M d, Y', strtotime($plan['created_at'])) ?></span>
                                </div>

                                <div class="d-flex gap-2 flex-wrap">
                                    <!-- Toggle Active -->
                                    <form action="<?= url('/chef/subscription/toggle/' . $plan['id']) ?>" method="POST" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm <?= $plan['is_active'] ? 'btn-outline-warning' : 'btn-outline-success' ?>">
                                            <i class="bi bi-<?= $plan['is_active'] ? 'pause-circle' : 'play-circle' ?> me-1"></i>
                                            <?= $plan['is_active'] ? 'Deactivate' : 'Activate' ?>
                                        </button>
                                    </form>

                                    <!-- Edit (Bootstrap Modal Trigger) -->
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                            data-bs-target="#editPlanModal<?= $plan['id'] ?>">
                                        <i class="bi bi-pencil me-1"></i> Edit
                                    </button>

                                    <!-- Delete -->
                                    <form action="<?= url('/chef/subscription/delete/' . $plan['id']) ?>" method="POST" class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to delete this plan?')">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash me-1"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editPlanModal<?= $plan['id'] ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title fw-bold">Edit Plan: <?= sanitize($plan['plan_name']) ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="<?= url('/chef/subscription/edit/' . $plan['id']) ?>" method="POST">
                                        <?= csrf_field() ?>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Plan Name</label>
                                                <input type="text" class="form-control" name="plan_name"
                                                       value="<?= sanitize($plan['plan_name']) ?>" required>
                                            </div>
                                            <div class="row g-3">
                                                <div class="col-6">
                                                    <label class="form-label fw-semibold">Plan Type</label>
                                                    <select class="form-select" name="plan_type">
                                                        <option value="weekly" <?= $plan['plan_type'] === 'weekly' ? 'selected' : '' ?>>Weekly</option>
                                                        <option value="monthly" <?= $plan['plan_type'] === 'monthly' ? 'selected' : '' ?>>Monthly</option>
                                                    </select>
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label fw-semibold">Price (₹)</label>
                                                    <input type="number" class="form-control" name="price"
                                                           value="<?= $plan['price'] ?>" min="1" step="0.01" required>
                                                </div>
                                            </div>
                                            <div class="mt-3">
                                                <label class="form-label fw-semibold">Meals Per Day</label>
                                                <select class="form-select" name="meals_per_day">
                                                    <option value="1" <?= $plan['meals_per_day'] == 1 ? 'selected' : '' ?>>1 Meal</option>
                                                    <option value="2" <?= $plan['meals_per_day'] == 2 ? 'selected' : '' ?>>2 Meals</option>
                                                    <option value="3" <?= $plan['meals_per_day'] == 3 ? 'selected' : '' ?>>3 Meals</option>
                                                </select>
                                            </div>
                                            <div class="mt-3">
                                                <label class="form-label fw-semibold">Description</label>
                                                <textarea class="form-control" name="description" rows="2"><?= sanitize($plan['description'] ?? '') ?></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-kravyo-primary">Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Active Subscribers Section -->
    <div class="card kravyo-card border-0">
        <div class="card-header bg-transparent border-bottom py-3">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-people-fill text-success me-2"></i>Active Subscribers
                <span class="badge bg-success ms-2"><?= $subscriberCount ?></span>
            </h5>
        </div>
        <div class="card-body p-4">
            <?php if (empty($activeSubscribers)): ?>
                <div class="text-center py-4">
                    <div class="empty-state-icon mb-3"><i class="bi bi-people"></i></div>
                    <h5 class="text-muted fw-bold">No Active Subscribers Yet</h5>
                    <p class="text-muted">Once customers subscribe to your tiffin plans, they'll appear here.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Customer</th>
                                <th>Plan</th>
                                <th>Duration</th>
                                <th>Delivery Address</th>
                                <th>Payment</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activeSubscribers as $sub): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?= sanitize($sub['customer_name']) ?></div>
                                        <small class="text-muted"><?= sanitize($sub['customer_phone']) ?></small>
                                    </td>
                                    <td>
                                        <span class="fw-semibold"><?= sanitize($sub['plan_name']) ?></span>
                                        <br>
                                        <span class="plan-type-badge plan-type-<?= $sub['plan_type'] ?> small">
                                            <?= ucfirst($sub['plan_type']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small>
                                            <?= date('M d', strtotime($sub['start_date'])) ?> — <?= date('M d, Y', strtotime($sub['end_date'])) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= sanitize($sub['street_address']) ?>, <?= sanitize($sub['delivery_city']) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $sub['payment_status'] === 'completed' ? 'success' : 'warning' ?>">
                                            <?= ucfirst($sub['payment_status']) ?>
                                        </span>
                                        <br>
                                        <small class="text-muted"><?= format_currency($sub['total_paid']) ?></small>
                                    </td>
                                    <td>
                                        <span class="subscription-status-badge status-<?= $sub['status'] ?>">
                                            <?= ucfirst($sub['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
