<?php
require_once dirname(__DIR__) . '/config/database.php';
$config = require dirname(__DIR__) . '/config/database.php';

try {
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$addresses = [
    // Surat
    ['city' => 'Surat', 'latitude' => 21.1959, 'longitude' => 72.7933, 'address' => '101, Rander Rd, Adajan', 'pincode' => '395009'],
    ['city' => 'Surat', 'latitude' => 21.1418, 'longitude' => 72.7709, 'address' => 'VIP Road, Vesu', 'pincode' => '395007'],
    ['city' => 'Surat', 'latitude' => 21.1578, 'longitude' => 72.7754, 'address' => 'Dumas Rd, Piplod', 'pincode' => '395007'],
    ['city' => 'Surat', 'latitude' => 21.2163, 'longitude' => 72.8576, 'address' => 'Baroda Pristine, Varachha', 'pincode' => '395006'],
    ['city' => 'Surat', 'latitude' => 21.2319, 'longitude' => 72.8250, 'address' => 'Katargam Main Rd, Katargam', 'pincode' => '395004'],
    ['city' => 'Surat', 'latitude' => 21.1685, 'longitude' => 72.7942, 'address' => 'City Light Town', 'pincode' => '395007'],
    ['city' => 'Surat', 'latitude' => 21.1643, 'longitude' => 72.8407, 'address' => 'Udhana Main Rd', 'pincode' => '394210'],

    // Mumbai
    ['city' => 'Mumbai', 'latitude' => 19.1363, 'longitude' => 72.8277, 'address' => 'Lokhandwala Complex, Andheri West', 'pincode' => '400053'],
    ['city' => 'Mumbai', 'latitude' => 19.0596, 'longitude' => 72.8295, 'address' => 'Linking Road, Bandra West', 'pincode' => '400050'],
    ['city' => 'Mumbai', 'latitude' => 18.9154, 'longitude' => 72.8048, 'address' => 'Colaba Causeway', 'pincode' => '400005'],
    ['city' => 'Mumbai', 'latitude' => 19.0178, 'longitude' => 72.8478, 'address' => 'Shivaji Park, Dadar', 'pincode' => '400028'],
    ['city' => 'Mumbai', 'latitude' => 19.1075, 'longitude' => 72.8263, 'address' => 'Juhu Tara Rd, Juhu', 'pincode' => '400049'],
    ['city' => 'Mumbai', 'latitude' => 19.1197, 'longitude' => 72.9051, 'address' => 'Hiranandani Gardens, Powai', 'pincode' => '400076'],
    ['city' => 'Mumbai', 'latitude' => 19.1846, 'longitude' => 72.8426, 'address' => 'Mindspace, Malad West', 'pincode' => '400064'],
    ['city' => 'Mumbai', 'latitude' => 19.2312, 'longitude' => 72.8401, 'address' => 'LT Road, Borivali West', 'pincode' => '400092'],

    // Delhi
    ['city' => 'Delhi', 'latitude' => 28.6304, 'longitude' => 77.2177, 'address' => 'Rajiv Chowk, Connaught Place', 'pincode' => '110001'],
    ['city' => 'Delhi', 'latitude' => 28.5494, 'longitude' => 77.2001, 'address' => 'Hauz Khas Village', 'pincode' => '110016'],
    ['city' => 'Delhi', 'latitude' => 28.5276, 'longitude' => 77.1610, 'address' => 'Nelson Mandela Marg, Vasant Kunj', 'pincode' => '110070'],
    ['city' => 'Delhi', 'latitude' => 28.6514, 'longitude' => 77.1908, 'address' => 'Ajmal Khan Rd, Karol Bagh', 'pincode' => '110005'],
    ['city' => 'Delhi', 'latitude' => 28.5921, 'longitude' => 77.0460, 'address' => 'Sector 12, Dwarka', 'pincode' => '110075'],
    ['city' => 'Delhi', 'latitude' => 28.7041, 'longitude' => 77.1025, 'address' => 'Sector 9, Rohini', 'pincode' => '110085'],
    ['city' => 'Delhi', 'latitude' => 28.5677, 'longitude' => 77.2433, 'address' => 'Central Market, Lajpat Nagar', 'pincode' => '110024'],

    // Pune
    ['city' => 'Pune', 'latitude' => 18.5362, 'longitude' => 73.8939, 'address' => 'North Main Road, Koregaon Park', 'pincode' => '411001'],
    ['city' => 'Pune', 'latitude' => 18.5913, 'longitude' => 73.7389, 'address' => 'Rajiv Gandhi Infotech Park, Hinjewadi', 'pincode' => '411057'],
    ['city' => 'Pune', 'latitude' => 18.5658, 'longitude' => 73.9137, 'address' => 'Phoenix Marketcity Rd, Viman Nagar', 'pincode' => '411014'],
    ['city' => 'Pune', 'latitude' => 18.5074, 'longitude' => 73.8077, 'address' => 'Karve Road, Kothrud', 'pincode' => '411038'],
    ['city' => 'Pune', 'latitude' => 18.5147, 'longitude' => 73.9317, 'address' => 'Magarpatta City, Hadapsar', 'pincode' => '411028'],
    ['city' => 'Pune', 'latitude' => 18.5590, 'longitude' => 73.7868, 'address' => 'Baner Road, Baner', 'pincode' => '411045'],
    ['city' => 'Pune', 'latitude' => 18.5472, 'longitude' => 73.9048, 'address' => 'Kalyani Nagar', 'pincode' => '411006'],

    // Bangalore
    ['city' => 'Bangalore', 'latitude' => 12.9719, 'longitude' => 77.6412, 'address' => '100 Feet Rd, Indiranagar', 'pincode' => '560038'],
    ['city' => 'Bangalore', 'latitude' => 12.9279, 'longitude' => 77.6271, 'address' => '5th Block, Koramangala', 'pincode' => '560095'],
    ['city' => 'Bangalore', 'latitude' => 12.9698, 'longitude' => 77.7499, 'address' => 'ITPL Main Rd, Whitefield', 'pincode' => '560066'],
    ['city' => 'Bangalore', 'latitude' => 12.9121, 'longitude' => 77.6446, 'address' => '27th Main Rd, HSR Layout', 'pincode' => '560102'],
    ['city' => 'Bangalore', 'latitude' => 12.9299, 'longitude' => 77.5824, 'address' => '4th Block, Jayanagar', 'pincode' => '560011'],
    ['city' => 'Bangalore', 'latitude' => 13.0031, 'longitude' => 77.5714, 'address' => 'Margosa Road, Malleshwaram', 'pincode' => '560003'],
    ['city' => 'Bangalore', 'latitude' => 12.9569, 'longitude' => 77.7011, 'address' => 'Outer Ring Rd, Marathahalli', 'pincode' => '560037'],

    // Jaipur
    ['city' => 'Jaipur', 'latitude' => 26.8522, 'longitude' => 75.8115, 'address' => 'JLN Marg, Malviya Nagar', 'pincode' => '302017'],
    ['city' => 'Jaipur', 'latitude' => 26.9056, 'longitude' => 75.7412, 'address' => 'Queens Road, Vaishali Nagar', 'pincode' => '302021'],
    ['city' => 'Jaipur', 'latitude' => 26.9069, 'longitude' => 75.8016, 'address' => 'Statue Circle, C Scheme', 'pincode' => '302005'],
    ['city' => 'Jaipur', 'latitude' => 26.8582, 'longitude' => 75.7645, 'address' => 'VT Road, Mansarovar', 'pincode' => '302020'],
    ['city' => 'Jaipur', 'latitude' => 26.9270, 'longitude' => 75.7877, 'address' => 'Kabir Marg, Bani Park', 'pincode' => '302016'],
    ['city' => 'Jaipur', 'latitude' => 26.8927, 'longitude' => 75.8285, 'address' => 'Govind Marg, Raja Park', 'pincode' => '302004'],
    ['city' => 'Jaipur', 'latitude' => 26.8662, 'longitude' => 75.8021, 'address' => 'Tonk Road', 'pincode' => '302015']
];

$stmt = $pdo->query("SELECT id FROM kitchens ORDER BY id ASC");
$kitchens = $stmt->fetchAll(PDO::FETCH_ASSOC);

$updateStmt = $pdo->prepare("UPDATE kitchens SET address = ?, city = ?, pincode = ?, latitude = ?, longitude = ? WHERE id = ?");

$addressCount = count($addresses);

foreach ($kitchens as $index => $kitchen) {
    // If there are more kitchens than addresses, cycle through the addresses
    $addr = $addresses[$index % $addressCount];
    
    $updateStmt->execute([
        $addr['address'],
        $addr['city'],
        $addr['pincode'],
        $addr['latitude'],
        $addr['longitude'],
        $kitchen['id']
    ]);
}

echo "Successfully updated " . count($kitchens) . " kitchens with new addresses.";
