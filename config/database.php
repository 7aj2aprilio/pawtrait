<?php
// Azure MySQL Configuration (FIXED)

define('DB_HOST', getenv('DB_HOST') ?: 'pawtrait-photobooth-server.mysql.database.azure.com');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'pawtrait-photobooth-database');
define('DB_USER', getenv('DB_USER') ?: 'yktjmthsur');
define('DB_PASS', getenv('DB_PASS') ?: 'sfl5DhAVHCMUwxa$');

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

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
