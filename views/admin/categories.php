<div class="container py-4">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-grid text-warning me-2"></i>Food Category Management
            </h2>
            <p class="text-muted mb-0">Create and manage food categories for the platform</p>
        </div>
        <a href="<?= url('/admin/dashboard') ?>" class="btn btn-outline-secondary mt-3 mt-md-0">
            <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>

    <div class="row g-4">
        <!-- Left: Category List -->
        <div class="col-lg-8">
            <div class="card kravyo-card border-0">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">
                        <i class="bi bi-list-ul me-2 text-muted"></i>All Categories
                        <span class="badge bg-secondary ms-2"><?= count($categories) ?></span>
                    </h5>

                    <?php if (empty($categories)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-folder-plus display-3 text-muted d-block mb-3 opacity-25"></i>
                            <p class="text-muted">No categories yet. Add your first category using the form.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:60px">#</th>
                                        <th>Category Name</th>
                                        <th>Description</th>
                                        <th class="text-center">Dishes</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $index => $cat): ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($cat['image'])): ?>
                                                <img src="<?= UPLOAD_URL . '/categories/' . $cat['image'] ?>"
                                                     alt="<?= sanitize($cat['category_name']) ?>"
                                                     class="rounded" width="40" height="40" style="object-fit: cover;">
                                            <?php else: ?>
                                                <div class="category-icon-placeholder">
                                                    <i class="bi bi-grid"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="fw-semibold"><?= sanitize($cat['category_name']) ?></td>
                                        <td class="text-muted small"><?= sanitize($cat['description'] ?? '--') ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">
                                                <?= $cat['item_count'] ?? 0 ?> items
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-primary me-1"
                                                    data-bs-toggle="modal" data-bs-target="#editCatModal<?= $cat['id'] ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <?php if (($cat['item_count'] ?? 0) == 0): ?>
                                                <form action="<?= url('/admin/category/delete/' . $cat['id']) ?>" method="POST" class="d-inline">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                                            onclick="return confirm('Delete category \'<?= sanitize($cat['category_name']) ?>\'?')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-outline-secondary" disabled
                                                        data-bs-toggle="tooltip" title="Cannot delete — has linked dishes">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>

                                    <!-- Edit Modal for each category -->
                                    <div class="modal fade" id="editCatModal<?= $cat['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <form action="<?= url('/admin/category/edit/' . $cat['id']) ?>" method="POST" enctype="multipart/form-data">
                                                    <?= csrf_field() ?>
                                                    <div class="modal-header border-0 pb-0">
                                                        <h5 class="modal-title fw-bold">
                                                            <i class="bi bi-pencil-square text-warning me-2"></i>Edit Category
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="category_name"
                                                                   value="<?= sanitize($cat['category_name']) ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Description</label>
                                                            <input type="text" class="form-control" name="description"
                                                                   value="<?= sanitize($cat['description'] ?? '') ?>"
                                                                   placeholder="Short description">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Category Image</label>
                                                            <input type="file" class="form-control" name="image" accept="image/*">
                                                            <?php if (!empty($cat['image'])): ?>
                                                                <small class="text-success mt-1 d-block">
                                                                    <i class="bi bi-check-circle me-1"></i>Image uploaded. Choose new to replace.
                                                                </small>
                                                            <?php endif; ?>
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

                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right: Add New Category Form -->
        <div class="col-lg-4">
            <div class="card kravyo-card border-0">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">
                        <i class="bi bi-plus-circle me-2 text-success"></i>Add New Category
                    </h5>
                    <form action="<?= url('/admin/category/add') ?>" method="POST" enctype="multipart/form-data">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label for="category_name" class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="category_name" name="category_name"
                                   placeholder="e.g., Desserts & Sweets" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label fw-semibold">Description</label>
                            <input type="text" class="form-control" id="description" name="description"
                                   placeholder="Short description of this category">
                        </div>

                        <div class="mb-4">
                            <label for="cat_image" class="form-label fw-semibold">Category Image</label>
                            <div class="upload-area" style="min-height: 120px; padding: 1rem;">
                                <div id="catAddPlaceholder">
                                    <i class="bi bi-image display-6 text-muted"></i>
                                    <p class="text-muted small mt-1 mb-0">Upload image (optional)</p>
                                </div>
                                <img src="" alt="Preview" class="upload-preview-img d-none" id="catAddPreview" style="max-height: 100px;">
                                <input type="file" class="upload-input" id="cat_image" name="image" accept="image/*">
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-kravyo-primary">
                                <i class="bi bi-plus-lg me-1"></i> Add Category
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Quick Info -->
            <div class="card kravyo-card border-0 mt-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3"><i class="bi bi-info-circle me-2 text-info"></i>Category Guidelines</h6>
                    <ul class="list-unstyled mb-0 small text-muted">
                        <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i>Category names must be unique</li>
                        <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i>Only empty categories can be deleted</li>
                        <li class="mb-0"><i class="bi bi-check2 text-success me-2"></i>Chefs select these when adding dishes</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
