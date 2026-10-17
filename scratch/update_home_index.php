<?php
$filePath = __DIR__ . '/../views/home/index.php';
$content = file_get_contents($filePath);

$isCrlf = strpos($content, "\r\n") !== false;
$eol = $isCrlf ? "\r\n" : "\n";

$search = '<a href="<?= ctx_url(\'/kitchens\') ?>" class="btn btn-kravyo-primary btn-lg">
                        <i class="bi bi-search me-2"></i> Explore Local Kitchens
                    </a>';

$replace = '<a href="<?= ctx_url(\'/kitchens\') ?>" class="btn btn-kravyo-primary btn-lg">
                        <i class="bi bi-search me-2"></i> Explore Local Kitchens
                    </a>' . $eol . '                    <a href="<?= ctx_url(\'/nearby-kitchens\') ?>" class="btn btn-warning text-dark btn-lg fw-bold shadow-sm">
                        <i class="bi bi-geo-alt-fill text-danger me-2"></i> Nearby Map
                    </a>';

if ($isCrlf) 
    {
    $search = str_replace("\n", "\r\n", $search);
    $replace = str_replace("\n", "\r\n", $replace);
        }

$content = str_replace($search, $replace, $content);
file_put_contents($filePath, $content);
echo "SUCCESS: home/index.php updated.\n";
