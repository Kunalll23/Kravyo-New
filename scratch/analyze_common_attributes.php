<?php
require_once __DIR__ . '/../config/app.php';
require_once ROOT_PATH . '/core/Database.php';

$db = Database::getInstance();
$sql = "
    SELECT table_name, column_name, data_type, column_type, character_maximum_length, numeric_precision, numeric_scale
    FROM information_schema.columns
    WHERE table_schema = 'kravyo_db'
    ORDER BY table_name, ordinal_position
";

$rows = $db->query($sql)->fetchAll();

$colMap = [];
$suffixMap = []; // For analyzing common roots/suffixes like *_id, *_name, *_at, is_*

foreach ($rows as $r) {
    $col = $r['column_name'];
    $tbl = $r['table_name'];
    $ctype = $r['column_type'];
    
    $colMap[$col][] = [
        'table' => $tbl,
        'type'  => $ctype,
        'dtype' => $r['data_type'],
        'len'   => $r['character_maximum_length'] ?? $r['numeric_precision']
    ];

    // Suffix detection (e.g. *_id)
    if (preg_match('/_id$/', $col) || $col === 'id') {
        $suffixMap['ID attributes (Primary & Foreign Keys)'][] = [
            'col' => $col,
            'table' => $tbl,
            'type' => $ctype
        ];
    }
    if (preg_match('/_at$/', $col)) {
        $suffixMap['Timestamp attributes (*_at)'][] = [
            'col' => $col,
            'table' => $tbl,
            'type' => $ctype
        ];
    }
    if (preg_match('/^is_/', $col)) {
        $suffixMap['Boolean / Flag attributes (is_*)'][] = [
            'col' => $col,
            'table' => $tbl,
            'type' => $ctype
        ];
    }
    if (preg_match('/status/', $col)) {
        $suffixMap['Status attributes (*status*)'][] = [
            'col' => $col,
            'table' => $tbl,
            'type' => $ctype
        ];
    }
    if (preg_match('/(price|amount|fee|total|cost|commission)/', $col)) {
        $suffixMap['Monetary / Financial attributes'][] = [
            'col' => $col,
            'table' => $tbl,
            'type' => $ctype
        ];
    }
    if (preg_match('/(lat|lng|latitude|longitude)/', $col)) {
        $suffixMap['Geolocation attributes (Coordinates)'][] = [
            'col' => $col,
            'table' => $tbl,
            'type' => $ctype
        ];
    }
}

echo "=========================================================================\n";
echo "1. EXACT IDENTICAL COLUMN NAMES APPEARING IN MULTIPLE TABLES:\n";
echo "=========================================================================\n";
uasort($colMap, fn($a, $b) => count($b) <=> count($a));

foreach ($colMap as $col => $items) {
    if (count($items) >= 2) {
        $types = array_unique(array_column($items, 'type'));
        $tblList = implode(', ', array_unique(array_column($items, 'table')));
        echo sprintf("%-20s | Appears in %2d tables | Data Type & Length: %-25s | Tables: %s\n", 
            $col, count($items), implode(' / ', $types), $tblList);
    }
}

echo "\n=========================================================================\n";
echo "2. COMMON CONCEPTUAL ATTRIBUTE FAMILIES (e.g. student_id, teacher_id -> id):\n";
echo "=========================================================================\n";
foreach ($suffixMap as $fam => $items) {
    echo "\n>>> Family: $fam (Total instances: " . count($items) . ")\n";
    $groupedCols = [];
    foreach ($items as $it) {
        $groupedCols[$it['col']]['tables'][] = $it['table'];
        $groupedCols[$it['col']]['type'] = $it['type'];
    }
    foreach ($groupedCols as $cname => $meta) {
        echo sprintf("    - %-22s | %-18s | Found in: %s\n", 
            $cname, $meta['type'], implode(', ', $meta['tables']));
    }
}
