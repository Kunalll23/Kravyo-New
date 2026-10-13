<?php
$file = 'c:/xampp/htdocs/Kravyo/views/partials/header.php';
$content = file_get_contents($file);

// Remove Search Icon block
$content = preg_replace('/<a href="<\?= url\(\'\/kitchens\'\) \?>" class="btn btn-link text-white p-0 border-0 me-2" title="Search">.*?<\/a>/is', '', $content);

// Remove Cart Icon block
$content = preg_replace('/<a href="<\?= url\(\'\/cart\'\) \?>" class="btn btn-link text-white position-relative p-0 border-0 me-2" title="Cart">.*?<\/a>/is', '', $content);

file_put_contents($file, $content);
echo "Icons removed.\n";
