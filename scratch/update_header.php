<?php
$filePath = __DIR__ . '/../views/partials/header.php';
$content = file_get_contents($filePath);

$isCrlf = strpos($content, "\r\n") !== false;
$eol = $isCrlf ? "\r\n" : "\n";

// 1. In switch:
$searchChef = '        $_navKitchens      = url(\'/chef/kitchens\');';
$replaceChef = '        $_navKitchens      = url(\'/chef/kitchens\');' . $eol . '        $_navNearby        = url(\'/chef/nearby-kitchens\');';

$searchAdmin = '        $_navKitchens      = url(\'/admin/kitchens-browse\');';
$replaceAdmin = '        $_navKitchens      = url(\'/admin/kitchens-browse\');' . $eol . '        $_navNearby        = url(\'/admin/nearby-kitchens\');';

$searchCustomer = '        $_navKitchens      = url(\'/kitchens\');';
$replaceCustomer = '        $_navKitchens      = url(\'/kitchens\');' . $eol . '        $_navNearby        = url(\'/nearby-kitchens\');';

// 2. In navbar:
$searchNav = '<li class="nav-item">
                        <a class="nav-link" href="<?= $_navKitchens ?>"><i class="bi bi-shop me-1"></i> Explore Kitchens</a>
                    </li>';

$replaceNav = '<li class="nav-item">
                        <a class="nav-link" href="<?= $_navKitchens ?>"><i class="bi bi-shop me-1"></i> Explore Kitchens</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $_navNearby ?>"><i class="bi bi-geo-alt-fill text-danger me-1"></i> Nearby Map</a>
                    </li>';

if ($isCrlf) {
    $searchNav = str_replace("\n", "\r\n", $searchNav);
    $replaceNav = str_replace("\n", "\r\n", $replaceNav);
}

$content = str_replace($searchChef, $replaceChef, $content);
$content = str_replace($searchAdmin, $replaceAdmin, $content);
$content = str_replace($searchCustomer, $replaceCustomer, $content);
$content = str_replace($searchNav, $replaceNav, $content);

file_put_contents($filePath, $content);
echo "SUCCESS: header.php updated.\n";
