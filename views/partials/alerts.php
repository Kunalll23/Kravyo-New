<?php
$flashes = Session::getFlashes();
foreach ($flashes as $type => $message):
?>
    <div class="alert alert-<?= sanitize($type) ?> alert-dismissible fade show" role="alert">
        <i class="bi bi-info-circle-fill me-2"></i>
        <?= sanitize($message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endforeach; ?>
