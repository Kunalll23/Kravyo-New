<?php
$filePath = __DIR__ . '/../app/controllers/ChefController.php';
$content = file_get_contents($filePath);

$search = "        // Validate required fields
        if (empty(\$data['kitchen_name']) || empty(\$data['address']) || empty(\$data['city']) || empty(\$data['pincode'])) {
            Session::setFlash('danger', 'Kitchen name, address, city, and pincode are required.');
            \$this->redirect('/chef/profile');
        }";

$isCrlf = strpos($content, "\r\n") !== false;
$eol = $isCrlf ? "\r\n" : "\n";

$insert = '

        // Latitude & Longitude location coordinates
        $rawLat = trim($_POST[\'latitude\'] ?? \'\');
        $rawLng = trim($_POST[\'longitude\'] ?? \'\');

        if ($rawLat !== \'\' && $rawLng !== \'\') {
            if (!is_numeric($rawLat) || !is_numeric($rawLng)) {
                Session::setFlash(\'danger\', \'Latitude and Longitude must be valid numbers.\');
                $this->redirect(\'/chef/profile\');
                return;
            }
            $latVal = (float)$rawLat;
            $lngVal = (float)$rawLng;
            if ($latVal < -90.0 || $latVal > 90.0 || $lngVal < -180.0 || $lngVal > 180.0) {
                Session::setFlash(\'danger\', \'Latitude must be between -90 and 90, and Longitude between -180 and 180.\');
                $this->redirect(\'/chef/profile\');
                return;
            }
            $data[\'latitude\']  = $latVal;
            $data[\'longitude\'] = $lngVal;
        }';

if ($isCrlf) {
    $search = str_replace("\n", "\r\n", $search);
    $insert = str_replace("\n", "\r\n", $insert);
}

if (strpos($content, $search) !== false) {
    $content = str_replace($search, $search . $insert, $content);
    file_put_contents($filePath, $content);
    echo "SUCCESS: ChefController updated.\n";
} else {
    echo "ERROR: Target content not found in ChefController.php\n";
    exit(1);
}
