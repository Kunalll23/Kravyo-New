<?php
/**
 * Kravyo — Customer/Chef Notification Inbox (Module 1.13 & 2.8)
 * GET /notifications
 */
?>


<div class="container py-5" style="min-height:70vh;">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <!-- Header -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h2 class="fw-bold mb-1"><i class="bi bi-bell-fill text-warning me-2"></i>Notifications</h2>
                    <p class="text-muted mb-0">
                        <?= $totalCount ?> notification<?= $totalCount !== 1 ? 's' : '' ?> total
                    </p>
                </div>
                <?php if ($totalCount > 0): ?>
                <form action="<?= url('/notifications/mark-all') ?>" method="POST">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-check2-all me-1"></i>Mark All as Read
                    </button>
                </form>
                <?php endif; ?>
            </div>

            <?php require_once VIEW_PATH . '/partials/alerts.php'; ?>

            <!-- Notification List -->
            <?php if (empty($notifications)): ?>
                <div class="card border-0 shadow-sm text-center py-5">
                    <div class="card-body">
                        <i class="bi bi-bell-slash display-3 text-muted mb-3 d-block"></i>
                        <h5 class="text-muted">You're all caught up!</h5>
                        <p class="text-muted small">No notifications yet. They'll appear here when something happens.</p>
                        <a href="<?= url('/') ?>" class="btn btn-kravyo-primary btn-sm mt-2">Back to Home</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="card border-0 shadow-sm overflow-hidden">
                    <?php foreach ($notifications as $notif): ?>
                    <?php
                        $iconMap = [
                            'order_update'   => 'bi-bag-check-fill text-success',
                            'promotion'      => 'bi-megaphone-fill text-warning',
                            'system_alert'   => 'bi-exclamation-triangle-fill text-danger',
                            'kitchen_update' => 'bi-shop-window text-primary',
                        ];
                        $icon = $iconMap[$notif['type']] ?? 'bi-bell-fill text-secondary';
                    ?>
                    <div class="d-flex align-items-start gap-3 p-3 border-bottom <?= $notif['is_read'] ? 'bg-white' : 'bg-light' ?>"
                         style="transition:background .2s;">
                        <div class="flex-shrink-0 mt-1">
                            <i class="bi <?= $icon ?> fs-5"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="fw-semibold <?= $notif['is_read'] ? 'text-muted' : 'text-dark' ?>">
                                    <?= sanitize($notif['title']) ?>
                                </span>
                                <?php if (!$notif['is_read']): ?>
                                    <span class="badge bg-danger" style="font-size:0.6rem;">New</span>
                                <?php endif; ?>
                            </div>
                            <p class="mb-1 text-muted small"><?= sanitize($notif['message']) ?></p>
                            <div class="text-muted" style="font-size:0.75rem;">
                                <i class="bi bi-clock me-1"></i>
                                <?= date('d M Y, h:i A', strtotime($notif['created_at'])) ?>
                            </div>
                        </div>
                        <?php if ($notif['link']): ?>
                        <div class="flex-shrink-0">
                            <a href="<?= url($notif['link']) ?>" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                            <a class="page-link" href="<?= url('/notifications?page=' . $p) ?>"><?= $p ?></a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            <?php endif; ?>

        </div>
    </div>
</div>
