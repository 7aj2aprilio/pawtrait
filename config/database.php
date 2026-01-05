<?php
// config/database.php

// Helper biar Azure bisa baca variabelnya (APPSETTING_ prefix)
if (!function_exists('getEnvVar')) {
    function getEnvVar($key, $default = null) {
        $val = getenv($key);
        if ($val === false) {
            $val = getenv("APPSETTING_$key");
        }
        return ($val !== false) ? $val : $default;
    }
}

// Database Configuration
define('DB_HOST', getEnvVar('DB_HOST', 'localhost'));
define('DB_PORT', getEnvVar('DB_PORT', '3306'));
define('DB_NAME', getEnvVar('DB_NAME', 'photobooth_db'));
define('DB_USER', getEnvVar('DB_USER', 'root'));
define('DB_PASS', getEnvVar('DB_PASS', ''));

// Create PDO connection
try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    // 🔐 Tambahkan SSL jika di Azure (BIASANYA WAJIB)
    if (DB_HOST !== 'localhost' && DB_HOST !== 'db') {
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

} catch (PDOException $e) {
    // Tampilkan detail error biar gampang debug
    header('Content-Type: text/plain');
    die("Database connection failed: " . $e->getMessage() . "\nHost: " . DB_HOST);
}
?>