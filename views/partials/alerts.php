<?php
// Flash messages are set internally by controllers — never from raw user input.
// We allow HTML in message bodies (e.g., links) intentionally.
$flashes = Session::getFlashes();
foreach ($flashes as $type => $message):
    // Restrict type to a safe Bootstrap class name
    $safeType = preg_match('/^(success|danger|warning|info|primary|secondary)$/', $type) ? $type : 'info';
    $icon = match($safeType) {
        'success' => 'bi-check-circle-fill text-success',
        'danger'  => 'bi-exclamation-triangle-fill text-danger',
        'warning' => 'bi-exclamation-circle-fill text-warning',
        default   => 'bi-info-circle-fill text-primary',
    };
?>
    <div class="alert alert-<?= $safeType ?> alert-dismissible fade show shadow-sm border-0 d-flex align-items-center mb-3" role="alert" style="border-radius: 12px;">
        <i class="bi <?= $icon ?> fs-5 me-2 flex-shrink-0"></i>
        <div class="flex-grow-1 small fw-semibold text-dark"><?= $message ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endforeach; ?>
