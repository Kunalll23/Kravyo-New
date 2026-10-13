<?php
$f = 'app/controllers/CustomerController.php';
$c = file_get_contents($f);
$old = "        \$city = trim(\$_GET['city'] ?? '');\n        \$lat  = isset(\$_GET['lat']) && is_numeric(\$_GET['lat']) ? (float)\$_GET['lat'] : null;\n        \$lng  = isset(\$_GET['lng']) && is_numeric(\$_GET['lng']) ? (float)\$_GET['lng'] : null;\n        \$rad  = isset(\$_GET['radius']) && is_numeric(\$_GET['radius']) ? max(1.0, min(200.0, (float)\$_GET['radius'])) : 10.0;";
$old2 = str_replace("\n", "\r\n", $old);

$new = "\$city = trim(\$_GET['city'] ?? '');
        \$lat  = isset(\$_GET['lat']) && is_numeric(\$_GET['lat']) ? (float)\$_GET['lat'] : null;
        \$lng  = isset(\$_GET['lng']) && is_numeric(\$_GET['lng']) ? (float)\$_GET['lng'] : null;

        if (\$lat === null || \$lng === null) {
            \$userId = (int) Session::get('user_id');
            if (\$userId > 0) {
                require_once APP_PATH . '/models/Address.php';
                \$addressModel = new Address();
                \$userAddresses = \$addressModel->findByUserId(\$userId);
                if (!empty(\$userAddresses) && !empty(\$userAddresses[0]['latitude'])) {
                    \$lat = (float)\$userAddresses[0]['latitude'];
                    \$lng = (float)\$userAddresses[0]['longitude'];
                }
            }
        }

        \$defaultRadius = isset(\$_GET['route_to']) ? 50.0 : 10.0;
        \$rad  = isset(\$_GET['radius']) && is_numeric(\$_GET['radius']) ? max(1.0, min(200.0, (float)\$_GET['radius'])) : \$defaultRadius;";

$c = str_replace($old, $new, $c);
$c = str_replace($old2, $new, $c);
file_put_contents($f, $c);
echo "Done";
