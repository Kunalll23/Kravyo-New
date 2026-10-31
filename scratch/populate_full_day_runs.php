<?php
define('ROOT_PATH', __DIR__ . '/..');
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
require_once ROOT_PATH . '/core/Database.php';

$db = Database::getInstance();
$today = date('Y-m-d');
$kid = 43; // Mehul Chinchangare's Kitchen

Database::withTransaction(function() use ($db, $today, $kid) {
    // Delete any old draft/test runs for Mehul for today to avoid duplicates
    $oldRuns = $db->query("SELECT dr.id FROM delivery_runs dr 
                           JOIN delivery_run_stops drs ON drs.run_id = dr.id AND drs.stop_type = 'pickup' AND drs.target_id = $kid 
                           WHERE dr.status = 'draft'")->fetchAll(PDO::FETCH_COLUMN);
    if (!empty($oldRuns)) {
        $in = implode(',', $oldRuns);
        $db->exec("DELETE FROM delivery_run_stops WHERE run_id IN ($in)");
        $db->exec("DELETE FROM delivery_runs WHERE id IN ($in)");
    }

    $kitchen = $db->query("SELECT * FROM kitchens WHERE id = $kid")->fetch(PDO::FETCH_ASSOC);

    // Common customer stops
    $customersNorth = [
        ['name' => 'Vikram Desai 69', 'addr' => 'Apt 5, Somnath Heritage, Gandhi Road, Bardoli', 'phone' => '9619799020', 'lat' => 21.1180, 'lng' => 73.1120],
        ['name' => 'Amit Kumar 77',   'addr' => 'Plot 42, Green Park Society, Gandhi Road, Bardoli', 'phone' => '9609477138', 'lat' => 21.1195, 'lng' => 73.1140],
        ['name' => 'Rahul Sharma 50', 'addr' => 'B-101, Anand Vatika, Station Road, Bardoli', 'phone' => '9161839102', 'lat' => 21.1210, 'lng' => 73.1165],
        ['name' => 'Kunalll',         'addr' => 'B-204, Shyam Residency, Station Road, Bardoli', 'phone' => '9601686114', 'lat' => 21.1230, 'lng' => 73.1180],
        ['name' => 'Sneha Gupta 74',  'addr' => 'Flat 204, Gokul Dham Society, Baben, Bardoli', 'phone' => '9182679895', 'lat' => 21.1250, 'lng' => 73.1210],
    ];

    $customersSouth = [
        ['name' => 'Priya Patel 96',   'addr' => 'House 11, Sardar Patel Society, Dhulia Road, Bardoli', 'phone' => '9117598244', 'lat' => 21.1140, 'lng' => 73.1090],
        ['name' => 'aashsis',          'addr' => 'Hostel Block B, Maliba Campus, Mahuva-Bardoli Rd, Tarsadi', 'phone' => '9601686112', 'lat' => 21.1090, 'lng' => 73.1050],
        ['name' => 'Ritu Agarwal 86',  'addr' => 'Flat 102, Krishna Park, Dhulia Road, Bardoli', 'phone' => '9719963730', 'lat' => 21.1120, 'lng' => 73.1070],
        ['name' => 'Pooja Reddy 20',   'addr' => 'House 18, Tirupati Township, Dhulia Road, Bardoli', 'phone' => '9947141437', 'lat' => 21.1110, 'lng' => 73.1060],
        ['name' => 'Neha Singh 86',    'addr' => 'Apt 302, Bapa Sitaram Society, Mahuva-Bardoli', 'phone' => '9117598200', 'lat' => 21.1080, 'lng' => 73.1040],
    ];

    $customersDinner = [
        ['name' => 'Priya Patel 96',   'addr' => 'House 11, Sardar Patel Society, Dhulia Road, Bardoli', 'phone' => '9117598244', 'lat' => 21.1140, 'lng' => 73.1090],
        ['name' => 'Ritu Agarwal 86',  'addr' => 'Flat 102, Krishna Park, Dhulia Road, Bardoli', 'phone' => '9719963730', 'lat' => 21.1120, 'lng' => 73.1070],
        ['name' => 'Vikram Desai 69',  'addr' => 'Apt 5, Somnath Heritage, Gandhi Road, Bardoli', 'phone' => '9619799020', 'lat' => 21.1180, 'lng' => 73.1120],
        ['name' => 'Pooja Reddy 20',   'addr' => 'House 18, Tirupati Township, Dhulia Road, Bardoli', 'phone' => '9947141437', 'lat' => 21.1110, 'lng' => 73.1060],
        ['name' => 'Amit Kumar 77',    'addr' => 'Plot 42, Green Park Society, Gandhi Road, Bardoli', 'phone' => '9609477138', 'lat' => 21.1195, 'lng' => 73.1140],
        ['name' => 'Neha Singh 86',    'addr' => 'Apt 302, Bapa Sitaram Society, Mahuva-Bardoli', 'phone' => '9117598200', 'lat' => 21.1080, 'lng' => 73.1040],
    ];

    $runsDef = [
        [
            'code'      => 'TR-' . date('Ymd') . '-01-BRKF',
            'meal'      => 'breakfast',
            'city'      => 'Bardoli (Central Sector)',
            'orders'    => 5,
            'payout'    => 170.00,
            'customers' => array_slice($customersNorth, 0, 5)
        ],
        [
            'code'      => 'TR-' . date('Ymd') . '-02-NORT',
            'meal'      => 'lunch',
            'city'      => 'Bardoli (North Sector)',
            'orders'    => 5,
            'payout'    => 170.00,
            'customers' => $customersNorth
        ],
        [
            'code'      => 'TR-' . date('Ymd') . '-03-SOUT',
            'meal'      => 'lunch',
            'city'      => 'Bardoli (South Sector)',
            'orders'    => 5,
            'payout'    => 170.00,
            'customers' => $customersSouth
        ],
        [
            'code'      => 'TR-' . date('Ymd') . '-04-DINN',
            'meal'      => 'dinner',
            'city'      => 'Bardoli (Express Corridor)',
            'orders'    => 6,
            'payout'    => 188.00,
            'customers' => $customersDinner
        ],
    ];

    foreach ($runsDef as $r) {
        $stmt = $db->prepare("INSERT INTO delivery_runs 
            (run_code, meal_type, delivery_date, city, status, total_orders, total_payout, created_at, updated_at)
            VALUES (?, ?, ?, ?, 'draft', ?, ?, NOW(), NOW())");
        $stmt->execute([
            $r['code'],
            $r['meal'],
            $today,
            $r['city'],
            $r['orders'],
            $r['payout']
        ]);
        $runId = (int)$db->lastInsertId();

        // Stop 1: Pickup from Kitchen
        $pickupStmt = $db->prepare("INSERT INTO delivery_run_stops 
            (run_id, stop_sequence, stop_type, target_id, target_name, target_address, contact_phone, latitude, longitude, items_count, special_instructions, status, created_at)
            VALUES (?, 1, 'pickup', ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
        $pickupStmt->execute([
            $runId,
            $kid,
            $kitchen['kitchen_name'],
            $kitchen['address'] ?? 'Jakat Naka, Bardoli, Gujarat 394601',
            $kitchen['phone'] ?? '9725800582',
            $kitchen['latitude'] ?? 21.1164,
            $kitchen['longitude'] ?? 73.1080,
            $r['orders'],
            "Collect {$r['orders']} packed " . ucfirst($r['meal']) . " tiffins from chef"
        ]);

        // Customer Dropoff Stops
        $seq = 2;
        foreach ($r['customers'] as $c) {
            $otp = sprintf('%04d', rand(1000, 9999));
            $dropStmt = $db->prepare("INSERT INTO delivery_run_stops 
                (run_id, stop_sequence, stop_type, target_id, target_name, target_address, contact_phone, latitude, longitude, items_count, special_instructions, status, delivery_otp, created_at)
                VALUES (?, ?, 'dropoff', 1, ?, ?, ?, ?, ?, 1, 'Hand over fresh tiffin at doorstep', 'pending', ?, NOW())");
            $dropStmt->execute([
                $runId,
                $seq++,
                $c['name'],
                $c['addr'],
                $c['phone'],
                $c['lat'],
                $c['lng'],
                $otp
            ]);
        }
        echo "Created Run #{$r['code']} ({$r['meal']}) with " . ($seq - 1) . " stops (1 Pickup + {$r['orders']} Drops).\n";
    }
});

echo "Full day tiffin runs created successfully!\n";

