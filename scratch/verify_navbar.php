<?php
$html = file_get_contents('http://localhost/Kravyo/');
echo "Has container-fluid: " . (strpos($html, 'container-fluid') !== false ? 'YES' : 'NO') . "\n";
echo "Has Nearby Map: " . (strpos($html, 'Nearby Map') !== false ? 'YES' : 'NO') . "\n";
echo "Has style.css: " . (strpos($html, 'style.css') !== false ? 'YES' : 'NO') . "\n";
echo "Any PHP errors/warnings: " . (preg_match('/(Fatal error|Parse error|Warning):/i', $html) ? 'YES' : 'NO') . "\n";

// Check the navbar section in HTML
if (preg_match('/<nav class="navbar.*?<\/nav>/s', $html, $matches)) {
    echo "\n=== NAVBAR HTML SNIPPET ===\n";
    echo substr($matches[0], 0, 800) . "...\n";
}
