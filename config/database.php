<?php
/**
 * Kravyo - Database Connection Settings
 * Supports both local XAMPP environment and cloud deployments (TiDB / Render / Railway)
 */

$dbUrl = getenv('DATABASE_URL');

if (!empty($dbUrl)) {
    $parsed = parse_url($dbUrl);
    $driver   = $parsed['scheme'] ?? 'mysql';
    $host     = $parsed['host'] ?? '127.0.0.1';
    $port     = (string)($parsed['port'] ?? '3306');
    $username = isset($parsed['user']) ? urldecode($parsed['user']) : 'root';
    $password = isset($parsed['pass']) ? urldecode($parsed['pass']) : '';
    $dbname   = isset($parsed['path']) ? ltrim($parsed['path'], '/') : 'kravyo_db';
} else {
    $driver   = 'mysql';
    $host     = getenv('DB_HOST') ?: '127.0.0.1';
    $port     = getenv('DB_PORT') ?: '3306';
    $username = getenv('DB_USERNAME') !== false ? getenv('DB_USERNAME') : (getenv('DB_USER') !== false ? getenv('DB_USER') : 'root');
    $password = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '';
    $dbname   = getenv('DB_NAME') ?: (getenv('DB_DATABASE') ?: 'kravyo_db');
}

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::ATTR_PERSISTENT         => false,
];

// If connecting to a remote cloud database (e.g. TiDB Cloud) that requires TLS/SSL
if (!in_array($host, ['localhost', '127.0.0.1']) || getenv('DB_SSL') === 'true') {
    if (file_exists('/etc/ssl/certs/ca-certificates.crt')) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = '/etc/ssl/certs/ca-certificates.crt';
    } elseif (file_exists('C:\\xampp\\apache\\bin\\curl-ca-bundle.crt')) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = 'C:\\xampp\\apache\\bin\\curl-ca-bundle.crt';
    } else {
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }
}

return [
    'driver'    => $driver,
    'host'      => $host,
    'port'      => $port,
    'dbname'    => $dbname,
    'username'  => $username,
    'password'  => $password,
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'options'   => $options
];
