<?php /* Phase 8: Chef Zero Food Waste Management Panel */ ?>
<div class="container py-4">

    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h2 class="fw-800 mb-1">
                <i class="bi bi-recycle text-success me-2"></i>Zero Food Waste Listings
            </h2>
            <p class="text-muted mb-0">List unsold cooked meals at discounted prices to reduce waste and earn more</p>
        </div>
        <a href="<?= url('/chef/dashboard') ?>" class="btn btn-outline-secondary btn-sm mt-3 mt-md-0">
            <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>

    <!-- Stats Row -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card kravyo-card border-0 h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="stat-icon-box bg-success bg-opacity-10 text-success">
                        <i class="bi bi-broadcast fs-4"></i>
                    </div>
                    <div>
                        <div class="fs-3 fw-800 text-success"><?= (int) ($stats['active_count'] ?? 0) ?></div>
                        <div class="text-muted small">Live Now</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card kravyo-card border-0 h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="stat-icon-box bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-layers fs-4"></i>
                    </div>
                    <div>
                        <div class="fs-3 fw-800"><?= (int) ($stats['total'] ?? 0) ?></div>
                        <div class="text-muted small">Total Created</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card kravyo-card border-0 h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="stat-icon-box bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-bag-check fs-4"></i>
                    </div>
                    <div>
                        <div class="fs-3 fw-800"><?= (int) ($stats['sold_out_count'] ?? 0) ?></div>
                        <div class="text-muted small">Sold Out</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card kravyo-card border-0 h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="stat-icon-box bg-secondary bg-opacity-10 text-secondary">
                        <i class="bi bi-clock-history fs-4"></i>
                    </div>
                    <div>
                        <div class="fs-3 fw-800"><?= (int) ($stats['expired_count'] ?? 0) ?></div>
                        <div class="text-muted small">Expired</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add New Listing Form -->
    <div class="card kravyo-card border-0 mb-4">
        <div class="card-header bg-transparent border-bottom py-3 d-flex align-items-center justify-content-between">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-plus-circle text-success me-2"></i>List a Discounted Meal
            </h5>
            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#addListingForm" aria-expanded="true">
                <i class="bi bi-chevron-up" id="addFormChevron"></i>
            </button>
        </div>
        <div class="collapse show" id="addListingForm">
            <div class="card-body p-4">
                <?php if (empty($availableMenuItems)): ?>
                    <div class="alert alert-warning d-flex align-items-center mb-0">
                        <i class="bi bi-exclamation-triangle-fill me-3 fs-5"></i>
                        <div>
                            You have no available menu items. Please
                            <a href="<?= url('/chef/menu') ?>" class="fw-bold">add dishes to your menu</a>
                            first before creating a zero waste listing.
                        </div>
                    </div>
                <?php else: ?>
                    <form action="<?= url('/chef/zero-waste/add') ?>" method="POST" id="addZeroWasteForm">
                        <?= csrfField() ?>
                        <div class="row g-3">

                            <!-- Dish Selector -->
                            <div class="col-md-6">
                                <label for="zw_menu_item_id" class="form-label fw-semibold">
                                    <i class="bi bi-egg-fried me-1 text-warning"></i>Select Dish from Your Menu <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="zw_menu_item_id" name="menu_item_id" required
                                        onchange="populateOriginalPrice(this)">
                                    <option value="">— Choose a dish —</option>
                                    <?php foreach ($availableMenuItems as $item): ?>
                                        <option value="<?= (int) $item['id'] ?>"
                                                data-price="<?= number_format((float)$item['price'], 2, '.', '') ?>">
                                            <?= sanitize($item['item_name']) ?>
                                            (₹<?= number_format((float)$item['price'], 2) ?>)
                                            <?= $item['is_veg'] ? '🌿' : '🍗' ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Original Price (Read-only, auto-filled) -->
                            <div class="col-md-3">
                                <label for="zw_original_price_display" class="form-label fw-semibold">
                                    <i class="bi bi-tag me-1 text-muted"></i>Original Price
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="text" class="form-control bg-light" id="zw_original_price_display"
                                           value="—" readonly>
                                </div>
                            </div>

                            <!-- Discounted Price -->
                            <div class="col-md-3">
                                <label for="zw_discounted_price" class="form-label fw-semibold">
                                    <i class="bi bi-tag-fill me-1 text-success"></i>Discounted Price <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" class="form-control" id="zw_discounted_price"
                                           name="discounted_price" min="1" step="0.01"
                                           placeholder="e.g. 40" required>
                                </div>
                                <div class="form-text text-success fw-semibold" id="discountBadge"></div>
                            </div>

                            <!-- Quantity -->
                            <div class="col-md-3">
                                <label for="zw_quantity" class="form-label fw-semibold">
                                    <i class="bi bi-boxes me-1 text-primary"></i>Quantity Available <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control" id="zw_quantity"
                                       name="quantity_available" min="1" max="50" value="5" required>
                            </div>

                            <!-- Expiry Time -->
                            <div class="col-md-5">
                                <label for="zw_expiry_time" class="form-label fw-semibold">
                                    <i class="bi bi-alarm me-1 text-danger"></i>Available Until (Expiry Time) <span class="text-danger">*</span>
                                </label>
                                <input type="datetime-local" class="form-control" id="zw_expiry_time"
                                       name="expiry_time" required min="<?= date('Y-m-d\TH:i') ?>">
                                <div class="form-text">Deal expires automatically at this time</div>
                            </div>

                            <!-- Submit -->
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-broadcast me-2"></i>Publish Zero Waste Deal
                                </button>
                            </div>

                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Listings Table -->
    <div class="card kravyo-card border-0">
        <div class="card-header bg-transparent border-bottom py-3">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-list-ul text-warning me-2"></i>Your Zero Waste Listings
                <span class="badge bg-secondary ms-2"><?= count($listings) ?></span>
            </h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($listings)): ?>
                <div class="text-center py-5 px-4">
                    <div class="mb-3" style="font-size: 3.5rem;">♻️</div>
                    <h5 class="fw-bold text-muted">No listings yet</h5>
                    <p class="text-muted mb-4">Use the form above to list your unsold cooked meals at a discount — reduce waste, earn more!</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Dish</th>
                                <th>Original</th>
                                <th>Discounted</th>
                                <th>Discount</th>
                                <th>Qty</th>
                                <th>Expires At</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($listings as $listing): ?>
                                <?php
                                    $status     = $listing['status'];
                                    $isExpired  = strtotime($listing['expiry_time']) <= time();
                                    $effectiveStatus = $isExpired ? 'expired' : $status;
                                    $statusClass = match($effectiveStatus) {
                                        'active'   => 'success',
                                        'sold_out' => 'primary',
                                        default    => 'secondary',
                                    };
                                    $statusLabel = match($effectiveStatus) {
                                        'active'   => 'Live',
                                        'sold_out' => 'Sold Out',
                                        default    => 'Expired',
                                    };
                                    $minRemaining = (int) ($listing['minutes_remaining'] ?? 0);
                                ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-2">
                                            <?php if ($listing['dish_image']): ?>
                                                <img src="<?= url('/uploads/dishes/' . $listing['dish_image']) ?>"
                                                     alt="" width="40" height="40"
                                                     class="rounded-circle object-fit-cover">
                                            <?php else: ?>
                                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center"
                                                     style="width:40px;height:40px;font-size:1.2rem;">
                                                    <?= $listing['is_veg'] ? '🌿' : '🍗' ?>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="fw-semibold small"><?= sanitize($listing['item_name']) ?></div>
                                                <?php if ($effectiveStatus === 'active' && $minRemaining > 0): ?>
                                                    <small class="text-danger">
                                                        <i class="bi bi-clock me-1"></i>
                                                        <?php if ($minRemaining >= 60): ?>
                                                            <?= floor($minRemaining / 60) ?>h <?= $minRemaining % 60 ?>m left
                                                        <?php else: ?>
                                                            <?= $minRemaining ?>m left
                                                        <?php endif; ?>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-muted text-decoration-line-through">
                                            ₹<?= number_format((float)$listing['original_price'], 2) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-success">
                                            ₹<?= number_format((float)$listing['discounted_price'], 2) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success bg-opacity-10 text-success fw-semibold">
                                            <?= (int) $listing['discount_pct'] ?>% OFF
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fw-semibold"><?= (int) $listing['quantity_available'] ?></span>
                                    </td>
                                    <td>
                                        <span class="small text-muted">
                                            <?= date('d M, h:i A', strtotime($listing['expiry_time'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge-status badge-status-<?= $effectiveStatus === 'active' ? 'approved' : ($effectiveStatus === 'sold_out' ? 'pending' : 'rejected') ?>">
                                            <?= $statusLabel ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex gap-2 justify-content-end">
                                            <?php if (!$isExpired && $effectiveStatus !== 'expired'): ?>
                                                <!-- Toggle Status -->
                                                <form action="<?= url('/chef/zero-waste/toggle/' . $listing['id']) ?>" method="POST">
                                                    <?= csrfField() ?>
                                                    <button type="submit"
                                                            class="btn btn-sm <?= $status === 'active' ? 'btn-outline-warning' : 'btn-outline-success' ?>"
                                                            title="<?= $status === 'active' ? 'Mark Sold Out' : 'Reactivate' ?>">
                                                        <i class="bi bi-<?= $status === 'active' ? 'pause-circle' : 'play-circle' ?>"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <!-- Delete -->
                                            <form action="<?= url('/chef/zero-waste/delete/' . $listing['id']) ?>" method="POST"
                                                  onsubmit="return confirm('Remove this listing? This cannot be undone.')">
                                                <?= csrfField() ?>
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove Listing">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            </form>
                                        </div>
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
// Auto-fill original price when dish is selected, compute discount badge live
function populateOriginalPrice(select) {
    const option = select.options[select.selectedIndex];
    const price  = option.getAttribute('data-price');
    const display = document.getElementById('zw_original_price_display');
    display.value = price ? '₹' + parseFloat(price).toFixed(2).replace('₹','') : '—';
    computeDiscount();
}

function computeDiscount() {
    const select   = document.getElementById('zw_menu_item_id');
    const option   = select.options[select.selectedIndex];
    const original = parseFloat(option.getAttribute('data-price') || 0);
    const discounted = parseFloat(document.getElementById('zw_discounted_price').value || 0);
    const badge    = document.getElementById('discountBadge');

    if (original > 0 && discounted > 0 && discounted < original) {
        const pct = Math.round(((original - discounted) / original) * 100);
        badge.textContent = `🎉 ${pct}% discount — saving ₹${(original - discounted).toFixed(2)}`;
    } else if (discounted >= original && original > 0) {
        badge.textContent = '⚠️ Discounted price must be less than original price';
        badge.className = 'form-text text-danger fw-semibold';
    } else {
        badge.textContent = '';
    }
}

document.getElementById('zw_discounted_price')?.addEventListener('input', computeDiscount);

// Collapse chevron toggle
document.getElementById('addListingForm')?.addEventListener('show.bs.collapse', () => {
    document.getElementById('addFormChevron').className = 'bi bi-chevron-up';
});
document.getElementById('addListingForm')?.addEventListener('hide.bs.collapse', () => {
    document.getElementById('addFormChevron').className = 'bi bi-chevron-down';
});
</script>
