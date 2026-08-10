<div class="container text-center py-5">
    <div class="display-1 text-warning fw-bold">500</div>
    <h2 class="fw-bold mb-3">Internal System Error</h2>
    <p class="text-muted mb-4"><?= isset($error) ? sanitize($error) : 'An unexpected error occurred while processing your request.' ?></p>
    <a href="<?= url('/') ?>" class="btn btn-kravyo-primary px-4">
        <i class="bi bi-house-door me-2"></i> Return to Home
    </a>
</div>
