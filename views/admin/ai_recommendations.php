<?php /* Admin Module 3.7: AI Recommendation Management */ ?>
<div class="container py-4">

    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="bi bi-cpu-fill text-primary me-2"></i>AI Recommendation Management</h2>
            <p class="text-muted mb-0">Monitor, configure, and test the Kravyo AI recommendation engine</p>
        </div>
        <a href="<?= url('/admin/dashboard') ?>" class="btn btn-outline-secondary btn-sm mt-3 mt-md-0">
            <i class="bi bi-arrow-left me-1"></i> Dashboard
        </a>
    </div>

    <!-- ── Section 1: AI System Status ──────────────────────────────────────── -->
    <div class="row g-4 mb-4">
        <div class="col-lg-5">
            <div class="card kravyo-card border-0 h-100">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h5 class="fw-bold mb-0"><i class="bi bi-activity me-2 text-success"></i>AI System Status</h5>
                </div>
                <div class="card-body p-4">

                    <!-- Online / Offline badge -->
                    <?php if ($aiOnline): ?>
                        <div class="d-flex align-items-center gap-3 mb-4 p-3 rounded-3" style="background: rgba(25,135,84,0.08);">
                            <div class="fs-1 text-success"><i class="bi bi-check-circle-fill"></i></div>
                            <div>
                                <div class="fw-bold fs-5 text-success">ONLINE</div>
                                <div class="text-muted small">Python AI server is responding normally</div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="d-flex align-items-center gap-3 mb-4 p-3 rounded-3" style="background: rgba(220,53,69,0.08);">
                            <div class="fs-1 text-danger"><i class="bi bi-x-circle-fill"></i></div>
                            <div>
                                <div class="fw-bold fs-5 text-danger">OFFLINE</div>
                                <div class="text-muted small">Python AI server is unreachable. PHP popularity fallback is active.</div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted fw-semibold ps-0">Service</td>
                            <td><?= $aiOnline ? sanitize($health['service']) : '<span class="text-muted">—</span>' ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold ps-0">Latency</td>
                            <td><?= $aiOnline ? '<span class="badge bg-success bg-opacity-10 text-success">' . $health['latency_ms'] . ' ms</span>' : '<span class="text-muted">—</span>' ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold ps-0">Endpoint</td>
                            <td><code>http://127.0.0.1:5000/</code></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold ps-0">Algorithm</td>
                            <td><code>TF-IDF Cosine Similarity</code></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold ps-0">Libraries</td>
                            <td><code>Pandas · NumPy · Scikit-learn</code></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold ps-0">n-gram range</td>
                            <td><code>(1, 2) — unigrams + bigrams</code></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold ps-0">Max TF-IDF features</td>
                            <td><code>500</code></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold ps-0">Config source</td>
                            <td>
                                <?php if ($configIsFresh): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success">Live from Python</span>
                                <?php else: ?>
                                    <span class="badge bg-warning bg-opacity-10 text-warning">Defaults (server offline)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- ── Section 2: AI Configuration ─────────────────────────────────── -->
        <div class="col-lg-7">
            <div class="card kravyo-card border-0 h-100">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h5 class="fw-bold mb-0"><i class="bi bi-sliders me-2 text-warning"></i>AI Configuration</h5>
                </div>
                <div class="card-body p-4">

                    <?php if (!$aiOnline): ?>
                        <div class="alert alert-warning border-0 mb-4">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <strong>Python AI server is offline.</strong> Configuration changes cannot be applied until the server is restarted. The displayed values are the last known defaults.
                        </div>
                    <?php endif; ?>

                    <form action="<?= url('/admin/ai-config/update') ?>" method="POST" id="ai-config-form">
                        <?= csrf_field() ?>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Content Similarity Weight</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="similarity_weight" name="similarity_weight"
                                           value="<?= number_format((float)$aiConfig['similarity_weight'], 2) ?>"
                                           step="0.01" min="0" max="1" required
                                           <?= !$aiOnline ? 'disabled' : '' ?>>
                                    <span class="input-group-text">0–1</span>
                                </div>
                                <small class="text-muted">Current: <strong><?= number_format((float)$aiConfig['similarity_weight'] * 100, 0) ?>%</strong> — personalised TF-IDF cosine score</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Popularity Weight</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="popularity_weight" name="popularity_weight"
                                           value="<?= number_format((float)$aiConfig['popularity_weight'], 2) ?>"
                                           step="0.01" min="0" max="1" required
                                           <?= !$aiOnline ? 'disabled' : '' ?>>
                                    <span class="input-group-text">0–1</span>
                                </div>
                                <small class="text-muted">Current: <strong><?= number_format((float)$aiConfig['popularity_weight'] * 100, 0) ?>%</strong> — platform-wide order popularity</small>
                            </div>
                        </div>

                        <!-- Visual blended score bar -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted text-uppercase">Score Formula Preview</label>
                            <div class="progress" style="height: 24px; border-radius: 8px;">
                                <?php $simPct = (float)$aiConfig['similarity_weight'] * 100; $popPct = 100 - $simPct; ?>
                                <div class="progress-bar bg-primary" style="width: <?= $simPct ?>%">Similarity <?= number_format($simPct, 0) ?>%</div>
                                <div class="progress-bar bg-warning" style="width: <?= $popPct ?>%">Popularity <?= number_format($popPct, 0) ?>%</div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Default Recommendation Limit</label>
                                <input type="number" class="form-control" name="default_limit"
                                       value="<?= (int)$aiConfig['default_limit'] ?>"
                                       min="1" max="20" required
                                       <?= !$aiOnline ? 'disabled' : '' ?>>
                                <small class="text-muted">Number of dishes shown per recommendation (1–20)</small>
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <button type="submit" class="btn btn-kravyo-primary w-100" <?= !$aiOnline ? 'disabled' : '' ?>>
                                    <i class="bi bi-save me-2"></i>Save AI Configuration
                                </button>
                            </div>
                        </div>

                        <div class="alert alert-info border-0 py-2 mb-0" style="font-size: 0.83rem;">
                            <i class="bi bi-info-circle me-1"></i>
                            Both weights <strong>must sum to exactly 1.0</strong>. The new values take effect immediately on the next recommendation request (no server restart needed).
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Section 3: AI Data Overview ──────────────────────────────────────── -->
    <div class="card kravyo-card border-0 mb-4">
        <div class="card-header bg-transparent border-bottom py-3">
            <h5 class="fw-bold mb-0"><i class="bi bi-bar-chart-fill me-2 text-info"></i>AI Data Overview</h5>
        </div>
        <div class="card-body">
            <div class="row g-3 text-center">
                <div class="col-6 col-md-2">
                    <div class="fw-bold fs-3"><?= number_format($dataStats['available_menu_items']) ?></div>
                    <div class="text-muted small">Menu Items<br>Available</div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="fw-bold fs-3 text-success"><?= number_format($dataStats['total_orders']) ?></div>
                    <div class="text-muted small">Total Orders</div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="fw-bold fs-3 text-primary"><?= number_format($dataStats['total_order_items']) ?></div>
                    <div class="text-muted small">Total Order<br>Items</div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="fw-bold fs-3 text-warning"><?= number_format($dataStats['customers_with_history']) ?></div>
                    <div class="text-muted small">Customers w/<br>History</div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="fw-bold fs-3 text-danger"><?= number_format($dataStats['total_customers']) ?></div>
                    <div class="text-muted small">Total<br>Customers</div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="fw-bold fs-3 text-secondary"><?= number_format($dataStats['active_kitchens']) ?></div>
                    <div class="text-muted small">Active<br>Kitchens</div>
                </div>
            </div>
            <?php
            $coldStart = max(0, ($dataStats['total_customers'] ?? 0) - ($dataStats['customers_with_history'] ?? 0));
            ?>
            <p class="text-muted mt-3 mb-0 small text-center">
                <i class="bi bi-snow me-1"></i>
                <strong><?= $coldStart ?></strong> customer(s) currently receive popularity-based cold-start recommendations (no order history yet).
            </p>
        </div>
    </div>

    <!-- ── Section 4: Test Recommendations ──────────────────────────────────── -->
    <div class="card kravyo-card border-0">
        <div class="card-header bg-transparent border-bottom py-3">
            <h5 class="fw-bold mb-0"><i class="bi bi-play-circle-fill me-2 text-success"></i>Test Recommendations</h5>
        </div>
        <div class="card-body p-4">

            <?php if (!$aiOnline): ?>
                <div class="alert alert-danger border-0 mb-4">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Python AI server is offline.</strong> Test recommendations are unavailable. Please start the Python server first.
                </div>
            <?php endif; ?>

            <form action="<?= url('/admin/ai-test') ?>" method="POST" class="row g-3 mb-4">
                <?= csrf_field() ?>
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Customer ID</label>
                    <input type="number" class="form-control" name="test_user_id" min="1"
                           placeholder="e.g. 5"
                           value="<?= isset($testUserId) ? (int)$testUserId : '' ?>"
                           required <?= !$aiOnline ? 'disabled' : '' ?>>
                    <small class="text-muted">Enter a valid customer user ID from the database</small>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Limit</label>
                    <input type="number" class="form-control" name="test_limit" min="1" max="20"
                           value="<?= isset($testLimit) ? (int)$testLimit : (int)$aiConfig['default_limit'] ?>"
                           <?= !$aiOnline ? 'disabled' : '' ?>>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-success w-100" <?= !$aiOnline ? 'disabled' : '' ?>>
                        <i class="bi bi-play-fill me-2"></i>Run AI Test
                    </button>
                </div>
            </form>

            <!-- Test Results -->
            <?php if (isset($testResult)): ?>
                <hr>
                <h6 class="fw-bold mb-3">
                    Test Results for <span class="text-primary"><?= $testUserName ?></span>
                    (ID: <?= (int)$testUserId ?>)
                </h6>

                <?php if (!empty($testResult['error'])): ?>
                    <div class="alert alert-danger border-0">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <?= sanitize($testResult['error']) ?>
                    </div>
                <?php elseif (empty($testResult['items'])): ?>
                    <div class="alert alert-warning border-0">
                        <i class="bi bi-info-circle me-2"></i>
                        No recommendations generated. This customer may have no order history (cold-start).
                    </div>
                <?php else: ?>
                    <?php if (!empty($testResult['cold_start'])): ?>
                        <div class="alert alert-info border-0 mb-3 py-2" style="font-size: 0.85rem;">
                            <i class="bi bi-snow me-1"></i> <strong>Cold-start mode:</strong> No personal order history. Recommendations are based on platform popularity.
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success border-0 mb-3 py-2" style="font-size: 0.85rem;">
                            <i class="bi bi-stars me-1"></i> <strong>Personalised recommendations</strong> using algorithm: <code><?= sanitize($testResult['algorithm'] ?? '') ?></code>
                        </div>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Dish</th>
                                    <th>Kitchen</th>
                                    <th>Category</th>
                                    <th>Final Score</th>
                                    <th>Similarity</th>
                                    <th>Popularity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($testResult['items'] as $i => $dish): ?>
                                    <tr>
                                        <td class="text-muted small fw-bold"><?= $i + 1 ?></td>
                                        <td class="fw-semibold"><?= sanitize($dish['item_name']) ?></td>
                                        <td class="text-muted"><?= sanitize($dish['kitchen_name']) ?></td>
                                        <td><?= sanitize($dish['category_name']) ?></td>
                                        <td>
                                            <span class="badge bg-primary text-white fw-bold">
                                                <?= number_format((float)$dish['final_score'], 4) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info text-dark">
                                                <?= number_format((float)$dish['sim_score'], 4) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning text-dark">
                                                <?= number_format((float)$dish['pop_score'], 4) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

</div>
