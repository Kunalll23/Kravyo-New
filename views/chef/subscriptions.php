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
                        <select class="form-select form-select-lg" id="meals_per_day" name="meals_per_day" onchange="toggleMealTimings('create', this.value)">
                            <option value="1">1 Meal / Day (Lunch)</option>
                            <option value="2">2 Meals / Day (Lunch & Dinner)</option>
                            <option value="3">3 Meals / Day (Breakfast, Lunch & Dinner)</option>
                        </select>
                    </div>
                    <div class="col-md-9">
                        <label for="description" class="form-label fw-semibold">Include</label>
                        <textarea class="form-control" id="description" name="description" rows="2"
                                  placeholder="e.g. 4 Phulkas, Dal Tadka, Sabzi, Jeera Rice, Salad"></textarea>
                    </div>

                    <!-- Dynamic Meal Delivery Timings for 1, 2, 3 meal plans -->
                    <div class="col-12">
                        <div class="p-3 bg-light rounded-3 border">
                            <label class="form-label fw-bold mb-2 text-dark">
                                <i class="bi bi-clock-history text-warning me-1"></i> Delivery Time Windows for Meals
                            </label>
                            <div class="row g-3">
                                <div class="col-md-4" id="create_breakfast_group" style="display: none;">
                                    <label class="form-label small fw-semibold text-muted">
                                        <i class="bi bi-sunrise text-warning me-1"></i>Breakfast Time Window
                                    </label>
                                    <input type="text" class="form-control" name="breakfast_time" id="create_breakfast_time"
                                           value="08:00 AM - 09:00 AM" placeholder="e.g. 08:00 AM - 09:00 AM">
                                </div>
                                <div class="col-md-4" id="create_lunch_group">
                                    <label class="form-label small fw-semibold text-muted">
                                        <i class="bi bi-sun text-primary me-1"></i>Lunch Time Window
                                    </label>
                                    <input type="text" class="form-control" name="lunch_time" id="create_lunch_time"
                                           value="12:30 PM - 01:30 PM" placeholder="e.g. 12:30 PM - 01:30 PM" required>
                                </div>
                                <div class="col-md-4" id="create_dinner_group" style="display: none;">
                                    <label class="form-label small fw-semibold text-muted">
                                        <i class="bi bi-moon-stars text-info me-1"></i>Dinner Time Window
                                    </label>
                                    <input type="text" class="form-control" name="dinner_time" id="create_dinner_time"
                                           value="07:30 PM - 08:30 PM" placeholder="e.g. 07:30 PM - 08:30 PM">
                                </div>
                            </div>
                            <small class="text-muted d-block mt-2">
                                <i class="bi bi-info-circle me-1"></i>Specify when meals are delivered to customers for this plan.
                            </small>
                        </div>
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
                <div class="row g-4">
                    <?php foreach ($plans as $plan): ?>
                        <div class="col-lg-6">
                            <div class="card subscription-card h-100 border shadow-sm <?= $plan['is_active'] ? '' : 'subscription-card-inactive' ?>">
                                <div class="card-body p-4 d-flex flex-column justify-content-between">
                                    <div>
                                        <!-- Header: Title, Badges & Price -->
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div class="pe-2">
                                                <h5 class="fw-bold mb-1 text-dark"><?= sanitize($plan['plan_name']) ?></h5>
                                                <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                                                    <span class="plan-type-badge plan-type-<?= $plan['plan_type'] ?>">
                                                        <i class="bi bi-<?= $plan['plan_type'] === 'weekly' ? 'calendar-week' : 'calendar-month' ?> me-1"></i>
                                                        <?= ucfirst($plan['plan_type']) ?>
                                                    </span>
                                                    <span class="badge bg-light text-secondary border">
                                                        <i class="bi bi-egg-fried me-1 text-warning"></i><?= $plan['meals_per_day'] ?> meal<?= $plan['meals_per_day'] > 1 ? 's' : '' ?>/day
                                                    </span>
                                                    <?php if (!$plan['is_active']): ?>
                                                        <span class="badge bg-secondary">Inactive</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="text-end text-nowrap">
                                                <div class="fs-4 fw-800 text-primary"><?= format_currency($plan['price']) ?></div>
                                                <small class="text-muted fw-normal">/ <?= $plan['plan_type'] === 'weekly' ? '7 days' : '30 days' ?></small>
                                            </div>
                                        </div>

                                        <!-- What's Included -->
                                        <?php if (!empty($plan['description'])): ?>
                                            <div class="p-2 px-3 bg-light rounded-3 mb-3 border">
                                                <small class="text-muted d-block">
                                                    <strong class="text-dark"><i class="bi bi-box2-heart text-warning me-1"></i>Includes:</strong>
                                                    <?= sanitize($plan['description']) ?>
                                                </small>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Delivery Timings Schedule -->
                                        <div class="mb-3">
                                            <small class="text-muted d-block mb-1 fw-semibold" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                                                <i class="bi bi-clock-history me-1"></i>Delivery Windows
                                            </small>
                                            <?php $mealSchedule = Subscription::getMealSchedule($plan); ?>
                                            <div class="d-flex flex-wrap gap-2">
                                                <?php foreach ($mealSchedule as $mKey => $mInfo): ?>
                                                    <span class="badge bg-white text-dark border py-1 px-2" style="font-size: 0.8rem; font-weight: 500;">
                                                        <i class="bi <?= $mInfo['icon'] ?> text-<?= $mInfo['badge_color'] ?> me-1"></i>
                                                        <strong><?= $mInfo['label'] ?>:</strong> <?= sanitize($mInfo['time']) ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Footer: Created Date & Actions -->
                                    <div class="border-top pt-3 mt-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
                                        <small class="text-muted">
                                            <i class="bi bi-calendar3 me-1"></i>Created <?= date('M d, Y', strtotime($plan['created_at'])) ?>
                                        </small>

                                        <div class="d-flex gap-2">
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
                                                <select class="form-select" name="meals_per_day" onchange="toggleMealTimings('edit_<?= $plan['id'] ?>', this.value)">
                                                    <option value="1" <?= $plan['meals_per_day'] == 1 ? 'selected' : '' ?>>1 Meal (Lunch)</option>
                                                    <option value="2" <?= $plan['meals_per_day'] == 2 ? 'selected' : '' ?>>2 Meals (Lunch & Dinner)</option>
                                                    <option value="3" <?= $plan['meals_per_day'] == 3 ? 'selected' : '' ?>>3 Meals (Breakfast, Lunch & Dinner)</option>
                                                </select>
                                            </div>

                                            <!-- Meal Timings in Modal -->
                                            <div class="mt-3 p-3 bg-light rounded-3 border">
                                                <label class="form-label fw-bold mb-2 small text-dark">
                                                    <i class="bi bi-clock-history text-warning me-1"></i> Meal Delivery Timings
                                                </label>
                                                <div class="row g-2">
                                                    <div class="col-12" id="edit_<?= $plan['id'] ?>_breakfast_group" style="<?= $plan['meals_per_day'] >= 3 ? '' : 'display:none;' ?>">
                                                        <label class="form-label small text-muted mb-1"><i class="bi bi-sunrise text-warning me-1"></i>Breakfast Time</label>
                                                        <input type="text" class="form-control form-control-sm" name="breakfast_time"
                                                               value="<?= sanitize($plan['breakfast_time'] ?? '08:00 AM - 09:00 AM') ?>" placeholder="e.g. 08:00 AM - 09:00 AM">
                                                    </div>
                                                    <div class="col-12" id="edit_<?= $plan['id'] ?>_lunch_group">
                                                        <label class="form-label small text-muted mb-1"><i class="bi bi-sun text-primary me-1"></i>Lunch Time</label>
                                                        <input type="text" class="form-control form-control-sm" name="lunch_time"
                                                               value="<?= sanitize($plan['lunch_time'] ?? '12:30 PM - 01:30 PM') ?>" placeholder="e.g. 12:30 PM - 01:30 PM" required>
                                                    </div>
                                                    <div class="col-12" id="edit_<?= $plan['id'] ?>_dinner_group" style="<?= $plan['meals_per_day'] >= 2 ? '' : 'display:none;' ?>">
                                                        <label class="form-label small text-muted mb-1"><i class="bi bi-moon-stars text-info me-1"></i>Dinner Time</label>
                                                        <input type="text" class="form-control form-control-sm" name="dinner_time"
                                                               value="<?= sanitize($plan['dinner_time'] ?? '07:30 PM - 08:30 PM') ?>" placeholder="e.g. 07:30 PM - 08:30 PM">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mt-3">
                                                <label class="form-label fw-semibold">Include</label>
                                                <textarea class="form-control" name="description" rows="2" placeholder="e.g. 4 Phulkas, Dal Tadka, Sabzi, Jeera Rice, Salad"><?= sanitize($plan['description'] ?? '') ?></textarea>
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

    <!-- Pending Subscription Requests -->
    <?php if (!empty($pendingSubscribers)): ?>
        <div class="card kravyo-card border-warning mb-4">
            <div class="card-header bg-warning bg-opacity-10 border-bottom py-3">
                <h5 class="fw-bold mb-0 text-warning-emphasis">
                    <i class="bi bi-clock-history me-2"></i>Pending Requests
                    <span class="badge bg-warning ms-2"><?= count($pendingSubscribers) ?></span>
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Customer</th>
                                <th>Plan Requested</th>
                                <th>Duration</th>
                                <th>Delivery Address</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingSubscribers as $sub): ?>
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
                                        <form action="<?= url('/chef/subscription/approve/' . $sub['id']) ?>" method="POST" class="d-inline">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="bi bi-check-lg"></i> Approve
                                            </button>
                                        </form>
                                        <form action="<?= url('/chef/subscription/reject/' . $sub['id']) ?>" method="POST" class="d-inline ms-1" onsubmit="return confirm('Reject this subscription request?');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="bi bi-x-lg"></i> Reject
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Today's Deliveries Manifest -->
    <?php
        $bCount = count(array_filter($todaysDeliveries, fn($d) => strtolower($d['meal_type']) === 'breakfast'));
        $lCount = count(array_filter($todaysDeliveries, fn($d) => strtolower($d['meal_type']) === 'lunch'));
        $dCount = count(array_filter($todaysDeliveries, fn($d) => strtolower($d['meal_type']) === 'dinner'));

        $pendingCount  = count(array_filter($todaysDeliveries, fn($d) => $d['status'] === 'pending'));
        $pendingBCount = count(array_filter($todaysDeliveries, fn($d) => strtolower($d['meal_type']) === 'breakfast' && $d['status'] === 'pending'));
        $pendingLCount = count(array_filter($todaysDeliveries, fn($d) => strtolower($d['meal_type']) === 'lunch' && $d['status'] === 'pending'));
        $pendingDCount = count(array_filter($todaysDeliveries, fn($d) => strtolower($d['meal_type']) === 'dinner' && $d['status'] === 'pending'));
    ?>
    <div class="card kravyo-card border-0 mb-4">
        <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center flex-wrap gap-2">
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-bicycle text-primary me-2"></i>Today's Tiffin Deliveries
                    <span class="badge bg-primary ms-2" id="deliveriesCountBadge"><?= count($todaysDeliveries) ?></span>
                </h5>
            </div>

            <!-- Meal Type Filter Option Buttons -->
            <div class="btn-group btn-group-sm" role="group" aria-label="Filter deliveries by meal">
                <button type="button" class="btn btn-outline-secondary active" data-meal-filter="all" onclick="filterDeliveries('all', this)">
                    <i class="bi bi-grid-fill me-1"></i>All (<?= count($todaysDeliveries) ?>)
                </button>
                <button type="button" class="btn btn-outline-warning text-dark" data-meal-filter="breakfast" onclick="filterDeliveries('breakfast', this)">
                    <i class="bi bi-sunrise me-1"></i>Breakfast (<?= $bCount ?>)
                </button>
                <button type="button" class="btn btn-outline-primary" data-meal-filter="lunch" onclick="filterDeliveries('lunch', this)">
                    <i class="bi bi-sun me-1"></i>Lunch (<?= $lCount ?>)
                </button>
                <button type="button" class="btn btn-outline-info text-dark" data-meal-filter="dinner" onclick="filterDeliveries('dinner', this)">
                    <i class="bi bi-moon-stars me-1"></i>Dinner (<?= $dCount ?>)
                </button>
            </div>
        </div>
        <div class="card-body p-4">
            <?php if (empty($todaysDeliveries)): ?>
                <div class="text-center py-4">
                    <div class="empty-state-icon mb-3"><i class="bi bi-check-circle text-success"></i></div>
                    <h5 class="text-muted fw-bold">No Deliveries Today</h5>
                    <p class="text-muted">You have no active subscriptions requiring a tiffin delivery today.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Customer</th>
                                <th>Tiffin Details</th>
                                <th>Address</th>
                                <th>Instructions</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($todaysDeliveries as $delivery): ?>
                                <tr class="delivery-row" data-meal-type="<?= strtolower($delivery['meal_type']) ?>" data-delivery-id="<?= $delivery['id'] ?>" data-status="<?= $delivery['status'] ?>">
                                    <td>
                                        <div class="fw-semibold"><?= sanitize($delivery['customer_name']) ?></div>
                                        <small class="text-muted"><?= sanitize($delivery['customer_phone']) ?></small>
                                    </td>
                                    <td>
                                        <span class="fw-semibold"><?= sanitize($delivery['plan_name']) ?></span><br>
                                        <span class="badge bg-secondary"><?= ucfirst($delivery['meal_type']) ?></span>
                                        <?php if (!empty($delivery['delivery_time'])): ?>
                                            <span class="badge bg-light text-dark border ms-1">
                                                <i class="bi bi-clock text-primary me-1"></i><?= sanitize($delivery['delivery_time']) ?>
                                            </span>
                                        <?php endif; ?>
                                        <small class="text-muted d-block mt-1"><?= $delivery['meals_per_day'] ?> meal<?= $delivery['meals_per_day'] > 1 ? 's' : '' ?>/day</small>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= sanitize($delivery['street_address']) ?><br>
                                            <?= sanitize($delivery['delivery_city']) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if (!empty($delivery['special_instructions'])): ?>
                                            <small class="text-danger"><i class="bi bi-exclamation-circle me-1"></i><?= sanitize($delivery['special_instructions']) ?></small>
                                        <?php else: ?>
                                            <small class="text-muted">None</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                            $badgeClass = 'bg-secondary';
                                            if ($delivery['status'] === 'pending') $badgeClass = 'bg-warning text-dark';
                                            if ($delivery['status'] === 'dispatched') $badgeClass = 'bg-info text-dark';
                                            if ($delivery['status'] === 'delivered') $badgeClass = 'bg-success';
                                        ?>
                                        <span class="badge <?= $badgeClass ?>"><?= ucfirst($delivery['status']) ?></span>
                                    </td>
                                    <td>
                                        <?php if ($delivery['status'] === 'pending'): ?>
                                            <form action="<?= url('/chef/subscription/delivery/status') ?>" method="POST" class="d-inline">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="delivery_id" value="<?= $delivery['id'] ?>">
                                                <input type="hidden" name="status" value="dispatched">
                                                <button type="submit" class="btn btn-sm btn-info text-white shadow-sm" title="Prepare & Dispatch single order">
                                                    <i class="bi bi-bicycle me-1"></i>Prepare &amp; Dispatch Rider
                                                </button>
                                            </form>
                                        <?php elseif ($delivery['status'] === 'dispatched'): ?>
                                            <span class="text-info small fw-bold"><i class="bi bi-broadcast me-1"></i> Dispatched to Rider</span>
                                        <?php else: ?>
                                            <span class="text-muted small"><i class="bi bi-check-all text-success fs-5"></i> Delivered</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr id="noFilteredDeliveriesRow" style="display: none;">
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="bi bi-funnel text-muted fs-4 d-block mb-1"></i>
                                    No deliveries found for this meal filter today.
                                </td>
                            </tr>
                        </tbody>
                    </table>
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

<script>
let currentActiveMealFilter = 'all';

function toggleMealTimings(prefix, mealsCount) {
    const count = parseInt(mealsCount, 10);
    const bGroup = document.getElementById(prefix + '_breakfast_group');
    const lGroup = document.getElementById(prefix + '_lunch_group');
    const dGroup = document.getElementById(prefix + '_dinner_group');

    if (bGroup) {
        bGroup.style.display = (count >= 3) ? 'block' : 'none';
    }
    if (lGroup) {
        lGroup.style.display = 'block';
    }
    if (dGroup) {
        dGroup.style.display = (count >= 2) ? 'block' : 'none';
    }
}

function filterDeliveries(mealType, btnElement) {
    currentActiveMealFilter = (mealType || 'all').toLowerCase();

    document.querySelectorAll('[data-meal-filter]').forEach(b => {
        b.classList.remove('active');
    });
    btnElement.classList.add('active');

    const rows = document.querySelectorAll('.delivery-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const rowMeal = (row.getAttribute('data-meal-type') || '').toLowerCase();
        
        if (currentActiveMealFilter === 'all' || rowMeal === currentActiveMealFilter) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    const noRow = document.getElementById('noFilteredDeliveriesRow');
    if (noRow) {
        noRow.style.display = (visibleCount === 0) ? '' : 'none';
    }

    const badge = document.getElementById('deliveriesCountBadge');
    if (badge) {
        badge.textContent = visibleCount;
    }
}
</script>
