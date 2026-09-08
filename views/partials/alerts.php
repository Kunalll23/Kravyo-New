<?php
// Flash messages are set internally by controllers — never from raw user input.
// We allow HTML in message bodies (e.g., links) intentionally.
$flashes = Session::getFlashes();
foreach ($flashes as $type => $message):
    // Restrict type to a safe Bootstrap class name
    $safeType = preg_match('/^(success|danger|warning|info|primary|secondary)$/', $type) ? $type : 'info';
?>
    <div class="alert alert-<?= $safeType ?> alert-dismissible fade show" role="alert">
        <i class="bi bi-info-circle-fill me-2"></i>
        <?= $message ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endforeach; ?>
