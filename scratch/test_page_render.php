<?php
$html = file_get_contents('http://localhost/Kravyo/nearby-kitchens');
echo "Page Length: " . strlen($html) . " bytes\n";
echo "Has 'Nearby Cloud Kitchens': " . (strpos($html, 'Nearby Cloud Kitchens') !== false ? 'YES' : 'NO') . "\n";
echo "Has 'leaflet.js': " . (strpos($html, 'leaflet.js') !== false ? 'YES' : 'NO') . "\n";
echo "Has 'nearbyMap': " . (strpos($html, 'nearbyMap') !== false ? 'YES' : 'NO') . "\n";
echo "Has 'btnUseLocation': " . (strpos($html, 'btnUseLocation') !== false ? 'YES' : 'NO') . "\n";
echo "Has 'Mehul': " . (strpos($html, 'Mehul') !== false ? 'YES' : 'NO') . "\n";
echo "Has PHP Warnings/Errors: " . (preg_match('/(Warning|Fatal error|Parse error):/i', $html) ? 'YES' : 'NO') . "\n";
