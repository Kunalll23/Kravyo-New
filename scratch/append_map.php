<?php
$file = __DIR__ . '/../views/customer/checkout.php';
$content = file_get_contents($file);
$content = str_replace("});\n</script>", "});\n" . file_get_contents(__DIR__ . '/map.txt'), $content);
file_put_contents($file, $content);
echo "Appended map logic";
