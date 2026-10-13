<?php
$filePath = __DIR__ . '/../views/customer/kitchens.php';
$content = file_get_contents($filePath);

$isCrlf = strpos($content, "\r\n") !== false;
$eol = $isCrlf ? "\r\n" : "\n";

$search = '<div class="col-lg-5 text-lg-end mt-3 mt-lg-0">
                <span class="badge bg-white text-dark px-3 py-2 fs-6 rounded-pill">
                    <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                    <?= count($kitchens) ?> Kitchen<?= count($kitchens) !== 1 ? \'s\' : \'\' ?> Found
                </span>
            </div>';

$replace = '<div class="col-lg-5 text-lg-end mt-3 mt-lg-0 d-flex flex-wrap justify-content-lg-end align-items-center gap-2">
                <a href="<?= ctx_url(\'/nearby-kitchens\') ?>" class="btn btn-warning text-dark fw-bold rounded-pill px-3 py-2 shadow-sm">
                    <i class="bi bi-map-fill me-1 text-danger"></i> Nearby Kitchens Map
                </a>
                <span class="badge bg-white text-dark px-3 py-2 fs-6 rounded-pill">
                    <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                    <?= count($kitchens) ?> Kitchen<?= count($kitchens) !== 1 ? \'s\' : \'\' ?> Found
                </span>
            </div>';

if ($isCrlf) {
    $search = str_replace("\n", "\r\n", $search);
    $replace = str_replace("\n", "\r\n", $replace);
}

$content = str_replace($search, $replace, $content);
file_put_contents($filePath, $content);
echo "SUCCESS: kitchens.php updated.\n";
