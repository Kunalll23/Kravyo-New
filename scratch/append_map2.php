<?php
$file = __DIR__ . '/../views/customer/checkout.php';
$content = file_get_contents($file);
$map = file_get_contents(__DIR__ . '/map.txt');
$content = preg_replace('/\}\);\s*<\/script>/', "});\n" . $map, $content);
file_put_contents($file, $content);
echo "Appended map logic properly";
