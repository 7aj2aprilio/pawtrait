<?php
// Azure MySQL Configuration (FIXED)
// Database Configuration
// Uses environment variables if available (for Docker), otherwise uses local defaults

define('DB_HOST', getenv('DB_HOST') ?: 'pawtrait-photobooth-server.mysql.database.azure.com');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'pawtrait-photobooth-database');
define('DB_USER', getenv('DB_USER') ?: 'yktjmthsur');
define('DB_PASS', getenv('DB_PASS') ?: 'sfl5DhAVHCMUwxa$');
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'photobooth_db');

// Create PDO connection
try {
    $dsn = "mysql:host=" . DB_HOST .
           ";port=" . DB_PORT .
           ";dbname=" . DB_NAME .
           ";charset=utf8mb4";

    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,

        // 🔐 WAJIB untuk Azure MySQL
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ]);

    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}