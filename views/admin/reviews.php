<div class="container py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-star-fill text-warning me-2"></i>Review Management
            </h2>
            <p class="text-muted mb-0">Monitor and moderate customer reviews across all kitchens.</p>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="<?= url('/admin/dashboard') ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <div class="card kravyo-card border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Customer</th>
                            <th>Kitchen</th>
                            <th>Rating</th>
                            <th style="max-width: 300px;">Review Text</th>
                            <th>Date</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($reviews)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-chat-square-quote display-4 d-block mb-3 opacity-50"></i>
                                    No reviews have been posted yet.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($reviews as $review): ?>
                                <tr>
                                    <td class="ps-4 text-muted small fw-bold">#<?= $review['id'] ?></td>
                                    <td class="fw-semibold text-dark"><?= sanitize($review['customer_name'] ?? 'Unknown Customer') ?></td>
                                    <td class="fw-semibold text-secondary"><?= sanitize($review['kitchen_name'] ?? 'Unknown Kitchen') ?></td>
                                    <td>
                                        <div class="text-warning">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <?php if ($i <= $review['rating']): ?>
                                                    <i class="bi bi-star-fill"></i>
                                                <?php else: ?>
                                                    <i class="bi bi-star"></i>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                        </div>
                                    </td>
                                    <td style="max-width: 300px;">
                                        <div class="text-truncate" title="<?= sanitize($review['review_text']) ?>">
                                            <?= sanitize($review['review_text']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?= date('d M Y', strtotime($review['created_at'])) ?></small>
                                    </td>
                                    <td class="text-end pe-4">
                                        <form action="<?= url('/admin/review/delete/' . $review['id']) ?>" method="POST" class="d-inline">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to permanently delete this review? This action cannot be undone.');">
                                                <i class="bi bi-trash me-1"></i>Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
