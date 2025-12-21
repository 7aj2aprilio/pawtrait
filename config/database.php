<?php

$host = getenv('DB_HOST');
$db   = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');
$port = getenv('DB_PORT') ?: 3306;

/**
 * DEBUG SEMENTARA (boleh dihapus nanti)
 */
if (!$host) {
    die("DB_HOST NOT SET");
}

$dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,

        // 🔴 INI YANG WAJIB DI AZURE
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
        PDO::MYSQL_ATTR_SSL_CA => null,
    ]);

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
