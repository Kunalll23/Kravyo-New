<div class="container py-4">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-egg-fried text-warning me-2"></i>Manage Menu
            </h2>
            <p class="text-muted mb-0">Add, edit, and manage your kitchen's dishes</p>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="<?= url('/chef/dashboard') ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Dashboard
            </a>
            <button type="button" class="btn btn-kravyo-primary" data-bs-toggle="modal" data-bs-target="#addDishModal">
                <i class="bi bi-plus-lg me-1"></i> Add New Dish
            </button>
        </div>
    </div>

    <!-- Kitchen Status Warning -->
    <?php if ($kitchen['approval_status'] !== 'approved'): ?>
        <div class="alert alert-warning d-flex align-items-center mb-4">
            <i class="bi bi-exclamation-triangle-fill me-3 fs-4"></i>
            <div>
                <strong>Kitchen Not Approved Yet!</strong> You can add dishes to your menu now, but they won't be visible to customers until your kitchen is approved by the admin.
            </div>
        </div>
    <?php endif; ?>

    <!-- Menu Stats Bar -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-card stat-card-menu">
                <div class="stat-icon"><i class="bi bi-egg-fried"></i></div>
                <div class="stat-value"><?= count($dishes) ?></div>
                <div class="stat-label">Total Dishes</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card stat-card-approved">
                <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
                <div class="stat-value"><?= count(array_filter($dishes, fn($d) => $d['is_available'])) ?></div>
                <div class="stat-label">Available</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card stat-card-pending">
                <div class="stat-icon"><i class="bi bi-grid"></i></div>
                <div class="stat-value"><?= count($categories) ?></div>
                <div class="stat-label">Categories</div>
            </div>
        </div>
    </div>

    <!-- Dishes Grid -->
    <?php if (empty($dishes)): ?>
        <div class="card kravyo-card border-0">
            <div class="card-body p-5 text-center">
                <i class="bi bi-egg-fried display-1 text-muted d-block mb-3 opacity-25"></i>
                <h5 class="fw-bold text-muted">No Dishes Added Yet</h5>
                <p class="text-muted mb-4">Start building your menu by adding your first home-cooked dish!</p>
                <button type="button" class="btn btn-kravyo-primary btn-lg" data-bs-toggle="modal" data-bs-target="#addDishModal">
                    <i class="bi bi-plus-lg me-2"></i>Add Your First Dish
                </button>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($dishes as $dish): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card kravyo-card border-0 dish-card h-100">
                    <!-- Dish Image -->
                    <div class="dish-card-image">
                        <?php if (!empty($dish['image'])): ?>
                            <img src="<?= UPLOAD_URL . '/dishes/' . $dish['image'] ?>"
                                 alt="<?= sanitize($dish['item_name']) ?>" class="w-100">
                        <?php else: ?>
                            <div class="dish-image-placeholder">
                                <i class="bi bi-camera text-muted"></i>
                            </div>
                        <?php endif; ?>

                        <!-- Price Badge -->
                        <span class="dish-price-badge"><?= format_currency($dish['price']) ?></span>

                        <!-- Availability Badge -->
                        <?php if (!$dish['is_available']): ?>
                            <span class="dish-unavailable-badge">Unavailable</span>
                        <?php endif; ?>
                    </div>

                    <div class="card-body p-3">
                        <!-- Dish Name & Category -->
                        <h6 class="fw-bold mb-1"><?= sanitize($dish['item_name']) ?></h6>
                        <small class="text-muted d-block mb-2">
                            <i class="bi bi-grid me-1"></i><?= sanitize($dish['category_name']) ?>
                        </small>

                        <!-- Description -->
                        <?php if (!empty($dish['description'])): ?>
                            <p class="text-muted small mb-2"><?= sanitize(substr($dish['description'], 0, 80)) ?><?= strlen($dish['description']) > 80 ? '...' : '' ?></p>
                        <?php endif; ?>

                        <!-- Dietary Badges -->
                        <div class="d-flex flex-wrap gap-1 mb-3">
                            <?php if ($dish['is_veg']): ?>
                                <span class="dietary-badge dietary-veg"><i class="bi bi-circle-fill me-1"></i>Veg</span>
                            <?php else: ?>
                                <span class="dietary-badge dietary-nonveg"><i class="bi bi-circle-fill me-1"></i>Non-Veg</span>
                            <?php endif; ?>
                            <?php if ($dish['is_jain_available']): ?>
                                <span class="dietary-badge dietary-jain"><i class="bi bi-flower1 me-1"></i>Jain</span>
                            <?php endif; ?>
                            <?php if ($dish['is_diabetic_friendly']): ?>
                                <span class="dietary-badge dietary-diabetic"><i class="bi bi-heart-pulse me-1"></i>Diabetic</span>
                            <?php endif; ?>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary flex-fill"
                                    data-bs-toggle="modal" data-bs-target="#editDishModal<?= $dish['id'] ?>">
                                <i class="bi bi-pencil me-1"></i>Edit
                            </button>
                            <form action="<?= url('/chef/menu/delete/' . $dish['id']) ?>" method="POST" class="flex-fill">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger w-100"
                                        onclick="return confirm('Delete \'<?= sanitize($dish['item_name']) ?>\'? This cannot be undone.')">
                                    <i class="bi bi-trash me-1"></i>Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Edit Dish Modal -->
                <div class="modal fade" id="editDishModal<?= $dish['id'] ?>" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <form action="<?= url('/chef/menu/edit/' . $dish['id']) ?>" method="POST" enctype="multipart/form-data">
                                <?= csrf_field() ?>
                                <div class="modal-header border-0 pb-0">
                                    <h5 class="modal-title fw-bold">
                                        <i class="bi bi-pencil-square text-warning me-2"></i>Edit Dish
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row g-3">
                                        <div class="col-md-8">
                                            <label class="form-label fw-semibold">Dish Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="item_name"
                                                   value="<?= sanitize($dish['item_name']) ?>" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Price (₹) <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" name="price" step="0.01" min="1"
                                                   value="<?= $dish['price'] ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Category</label>
                                            <select class="form-select" name="category_id">
                                                <?php foreach ($categories as $cat): ?>
                                                    <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $dish['category_id'] ? 'selected' : '' ?>>
                                                        <?= sanitize($cat['category_name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Dish Image</label>
                                            <input type="file" class="form-control" name="image" accept="image/*">
                                            <?php if (!empty($dish['image'])): ?>
                                                <small class="text-success"><i class="bi bi-check-circle me-1"></i>Image exists</small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold">Description</label>
                                            <textarea class="form-control" name="description" rows="2"><?= sanitize($dish['description'] ?? '') ?></textarea>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold d-block mb-2">Dietary Tags & Availability</label>
                                            <div class="d-flex flex-wrap gap-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="is_veg" id="editVeg<?= $dish['id'] ?>"
                                                           <?= $dish['is_veg'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="editVeg<?= $dish['id'] ?>">🟢 Vegetarian</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="is_jain_available" id="editJain<?= $dish['id'] ?>"
                                                           <?= $dish['is_jain_available'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="editJain<?= $dish['id'] ?>">🙏 Jain Available</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="is_diabetic_friendly" id="editDiabetic<?= $dish['id'] ?>"
                                                           <?= $dish['is_diabetic_friendly'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="editDiabetic<?= $dish['id'] ?>">🩺 Diabetic-Friendly</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="is_available" id="editAvail<?= $dish['id'] ?>"
                                                           <?= $dish['is_available'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="editAvail<?= $dish['id'] ?>">✅ Available for Order</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer border-0 pt-0">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-kravyo-primary">
                                        <i class="bi bi-check-lg me-1"></i>Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Add New Dish Modal -->
<div class="modal fade" id="addDishModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="<?= url('/chef/menu/add') ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-plus-circle text-success me-2"></i>Add New Dish
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if (empty($categories)): ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            No food categories exist yet. Ask your admin to create categories first.
                        </div>
                    <?php else: ?>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Dish Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" name="item_name"
                                   placeholder="e.g., Paneer Butter Masala" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Price (₹) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control form-control-lg" name="price" step="0.01" min="1"
                                   placeholder="e.g., 120" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                            <select class="form-select" name="category_id" required>
                                <option value="">-- Select Category --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= sanitize($cat['category_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Dish Photo</label>
                            <input type="file" class="form-control" name="image" id="addDishImage" accept="image/*">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea class="form-control" name="description" rows="2"
                                      placeholder="Describe what's included in this dish..."></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold d-block mb-2">Dietary Tags</label>
                            <div class="d-flex flex-wrap gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_veg" id="addVeg" checked>
                                    <label class="form-check-label" for="addVeg">🟢 Vegetarian</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_jain_available" id="addJain">
                                    <label class="form-check-label" for="addJain">🙏 Jain Available</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_diabetic_friendly" id="addDiabetic">
                                    <label class="form-check-label" for="addDiabetic">🩺 Diabetic-Friendly</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <?php if (!empty($categories)): ?>
                        <button type="submit" class="btn btn-kravyo-primary btn-lg">
                            <i class="bi bi-plus-lg me-1"></i> Add Dish to Menu
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>
