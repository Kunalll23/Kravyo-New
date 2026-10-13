<?php
$html = file_get_contents('http://localhost/Kravyo/zero-waste');
$pos1 = strpos($html, '<!-- Urgency Timer -->');
echo substr($html, $pos1, 600);
