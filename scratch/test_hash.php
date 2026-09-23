<?php
$hash = '$2y$12$N7i20xHCejjPwWHYqdleZui3wDc1UxqJVg5jxvhLReNd.6odoRKUe';
$passwords_to_test = [
    'password', 'password123', 'admin', 'admin123', 'Admin@123', 'Admin123!', 
    '123456', '12345678', 'Kravyo@123', 'kravyo', 'Sanyaa123', 'sanyaa123'
];

$found = false;
foreach ($passwords_to_test as $p) {
    if (password_verify($p, $hash)) {
        echo "MATCH FOUND: $p\n";
        $found = true;
        break;
    }
}

if (!$found) {
    echo "No match found in common list.\n";
}
