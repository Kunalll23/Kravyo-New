<div class="container py-4">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-pencil-square text-warning me-2"></i>Edit Kitchen Profile
            </h2>
            <p class="text-muted mb-0">Update your kitchen details, story, and upload images</p>
        </div>
        <a href="<?= url('/chef/dashboard') ?>" class="btn btn-outline-secondary mt-3 mt-md-0">
            <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>

    <?php if (!$kitchen): ?>
        <div class="alert alert-danger">Kitchen profile not found. Please contact support.</div>
    <?php else: ?>

    <form action="<?= url('/chef/profile/update') ?>" method="POST" enctype="multipart/form-data" id="kitchenProfileForm">
        <?= csrf_field() ?>

        <div class="row g-4">
            <!-- Left Column: Kitchen Details -->
            <div class="col-lg-7">
                <div class="card kravyo-card border-0">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4"><i class="bi bi-shop me-2 text-warning"></i>Kitchen Details</h5>

                        <div class="mb-3">
                            <label for="kitchen_name" class="form-label fw-semibold">Kitchen Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-shop text-muted"></i></span>
                                <input type="text" class="form-control form-control-lg border-start-0" id="kitchen_name" name="kitchen_name"
                                       value="<?= sanitize($kitchen['kitchen_name'] ?? '') ?>" placeholder="e.g., Sunita's Kitchen" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="personal_story" class="form-label fw-semibold">
                                <i class="bi bi-chat-quote me-1 text-muted"></i>Your Kitchen Story
                            </label>
                            <textarea class="form-control" id="personal_story" name="personal_story" rows="5"
                                      placeholder="Share your cooking journey, specialties, and what makes your food special..."><?= sanitize($kitchen['personal_story'] ?? '') ?></textarea>
                            <small class="form-text text-muted">This story will be visible to customers on your kitchen page.</small>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label fw-semibold">Street Address <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-geo-alt text-muted"></i></span>
                                <input type="text" class="form-control border-start-0" id="address" name="address"
                                       value="<?= sanitize($kitchen['address'] ?? '') ?>" placeholder="e.g., 102 Green Park Society, Ring Road" required>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="city" class="form-label fw-semibold">City <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="city" name="city"
                                       value="<?= sanitize($kitchen['city'] ?? '') ?>" placeholder="e.g., Surat" required>
                            </div>
                            <div class="col-md-6">
                                <label for="pincode" class="form-label fw-semibold">Pincode <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="pincode" name="pincode"
                                       value="<?= sanitize($kitchen['pincode'] ?? '') ?>" placeholder="e.g., 395007" pattern="[0-9]{6}" title="6-digit pincode" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FSSAI & Hygiene Section -->
                <div class="card kravyo-card border-0 mt-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4"><i class="bi bi-shield-check me-2 text-success"></i>FSSAI & Hygiene Verification</h5>

                        <div class="mb-3">
                            <label for="fssai_license" class="form-label fw-semibold">FSSAI License Number</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-upc-scan text-muted"></i></span>
                                <input type="text" class="form-control border-start-0" id="fssai_license" name="fssai_license"
                                       value="<?= sanitize($kitchen['fssai_license'] ?? '') ?>" placeholder="e.g., 21522001000123">
                            </div>
                            <small class="form-text text-muted">14-digit FSSAI license number. Leave blank if not available yet.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Hygiene Certificate / Document Upload</label>
                            <div class="upload-area" id="certUploadArea">
                                <?php if (!empty($kitchen['hygiene_certificate_image'])): ?>
                                    <img src="<?= UPLOAD_URL . '/kitchens/' . $kitchen['hygiene_certificate_image'] ?>"
                                         alt="Hygiene Certificate" class="upload-preview-img" id="certPreview">
                                    <p class="small text-success mt-2 mb-0"><i class="bi bi-check-circle me-1"></i>Certificate uploaded. Choose a new file to replace.</p>
                                <?php else: ?>
                                    <div id="certPlaceholder">
                                        <i class="bi bi-cloud-arrow-up display-4 text-muted"></i>
                                        <p class="text-muted mt-2 mb-0">Click or drag to upload hygiene certificate</p>
                                        <small class="text-muted">JPG, PNG, or WebP — Max 5 MB</small>
                                    </div>
                                    <img src="" alt="Certificate Preview" class="upload-preview-img d-none" id="certPreview">
                                <?php endif; ?>
                                <input type="file" class="upload-input" id="hygiene_certificate_image" name="hygiene_certificate_image"
                                       accept="image/jpeg,image/png,image/webp">
                            </div>
                        </div>

                        <!-- Current Badge Status -->
                        <div class="d-flex align-items-center gap-2 mt-3 p-3 bg-light rounded-3">
                            <i class="bi bi-info-circle text-primary"></i>
                            <span class="small">Current Hygiene Status:
                                <?php if ($kitchen['hygiene_badge'] === 'verified'): ?>
                                    <span class="badge-hygiene ms-2"><i class="bi bi-patch-check-fill me-1"></i>Verified</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary ms-2">Not Verified</span>
                                    <span class="text-muted"> — Will be reviewed by admin upon kitchen approval.</span>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Banner Upload & Summary -->
            <div class="col-lg-5">
                <div class="card kravyo-card border-0">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4"><i class="bi bi-image me-2 text-warning"></i>Kitchen Banner Image</h5>

                        <div class="upload-area upload-area-large" id="bannerUploadArea">
                            <?php if (!empty($kitchen['banner_image'])): ?>
                                <img src="<?= UPLOAD_URL . '/kitchens/' . $kitchen['banner_image'] ?>"
                                     alt="Kitchen Banner" class="upload-preview-img" id="bannerPreview">
                                <p class="small text-success mt-2 mb-0"><i class="bi bi-check-circle me-1"></i>Banner uploaded. Choose a new file to replace.</p>
                            <?php else: ?>
                                <div id="bannerPlaceholder">
                                    <i class="bi bi-camera display-3 text-muted"></i>
                                    <p class="text-muted mt-2 mb-0">Upload your kitchen banner photo</p>
                                    <small class="text-muted">Recommended: 1200x400 pixels</small>
                                </div>
                                <img src="" alt="Banner Preview" class="upload-preview-img d-none" id="bannerPreview">
                            <?php endif; ?>
                            <input type="file" class="upload-input" id="banner_image" name="banner_image"
                                   accept="image/jpeg,image/png,image/webp,image/gif">
                        </div>
                    </div>
                </div>

                <!-- Approval Status Card -->
                <div class="card kravyo-card border-0 mt-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3"><i class="bi bi-clipboard-check me-2 text-warning"></i>Approval Status</h5>
                        <div class="d-flex align-items-center gap-3 p-3 rounded-3 bg-light">
                            <div class="status-dot status-dot-<?= $kitchen['approval_status'] ?>"></div>
                            <div>
                                <strong class="d-block"><?= ucfirst($kitchen['approval_status']) ?></strong>
                                <?php if ($kitchen['approval_status'] === 'pending'): ?>
                                    <small class="text-muted">Under review by admin team</small>
                                <?php elseif ($kitchen['approval_status'] === 'approved'): ?>
                                    <small class="text-success">Your kitchen is live!</small>
                                <?php elseif ($kitchen['approval_status'] === 'rejected'): ?>
                                    <small class="text-danger"><?= sanitize($kitchen['admin_notes'] ?? 'Please update and resubmit') ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tips Card -->
                <div class="card kravyo-card border-0 mt-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3"><i class="bi bi-lightbulb me-2 text-warning"></i>Tips for Quick Approval</h6>
                        <ul class="list-unstyled mb-0 small text-muted">
                            <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i>Fill in all kitchen details completely</li>
                            <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i>Upload a clear banner image of your kitchen</li>
                            <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i>Add your FSSAI license number if available</li>
                            <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i>Upload hygiene certificate or clean kitchen photo</li>
                            <li class="mb-0"><i class="bi bi-check2 text-success me-2"></i>Write a compelling personal story</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="mt-4 d-flex gap-3 justify-content-end">
            <a href="<?= url('/chef/dashboard') ?>" class="btn btn-outline-secondary btn-lg px-4">Cancel</a>
            <button type="submit" class="btn btn-kravyo-primary btn-lg px-5">
                <i class="bi bi-check-lg me-1"></i> Save Kitchen Profile
            </button>
        </div>
    </form>

    <?php endif; ?>
</div>
