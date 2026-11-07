<?php
header('Content-Type: application/json');

$response = [
    'status' => 'ok',
    'php_version' => PHP_VERSION,
    'db' => 'testing...',
];

try {
    require_once __DIR__ . '/../config/app.php';
    require_once __DIR__ . '/../core/Database.php';
    $db = Database::getInstance();
    $stmt = $db->query('SELECT COUNT(*) FROM kitchens');
    $response['db'] = 'connected';
    $response['kitchens_count'] = (int)$stmt->fetchColumn();
} catch (Throwable $e) {
    $response['db'] = 'error: ' . $e->getMessage();
}

echo json_encode($response, JSON_PRETTY_PRINT);
