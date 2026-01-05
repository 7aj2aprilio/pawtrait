<?php
// config/midtrans.php

// Helper function to load env variables if not already loaded (e.g. by Composer's phpdotenv)
if (!function_exists('loadEnv')) {
    function loadEnv($path) {
        if (!file_exists($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

// Load .env from project root
loadEnv(__DIR__ . '/../.env');

// Midtrans Configuration Constants
// Helper biar Azure bisa baca variabelnya
function getAzureEnv($key) {
    // Coba baca standar
    $val = getenv($key);
    // Kalau kosong, coba baca versi Azure (yang ada prefixnya)
    if ($val === false) {
        $val = getenv("APPSETTING_$key");
    }
    return $val;
}

// Midtrans Configuration Constants
define('MIDTRANS_MERCHANT_ID', getAzureEnv('MIDTRANS_MERCHANT_ID'));
define('MIDTRANS_CLIENT_KEY', getAzureEnv('MIDTRANS_CLIENT_KEY'));
define('MIDTRANS_SERVER_KEY', getAzureEnv('MIDTRANS_SERVER_KEY'));
define('MIDTRANS_IS_PRODUCTION', filter_var(getAzureEnv('MIDTRANS_IS_PRODUCTION'), FILTER_VALIDATE_BOOLEAN));
define('MIDTRANS_IS_SANITIZED', filter_var(getAzureEnv('MIDTRANS_IS_SANITIZED'), FILTER_VALIDATE_BOOLEAN));
define('MIDTRANS_3DS', filter_var(getAzureEnv('MIDTRANS_IS_3DS'), FILTER_VALIDATE_BOOLEAN));

// Midtrans API URLs (Sandbox vs Production)
if (MIDTRANS_IS_PRODUCTION) {
    define('MIDTRANS_API_URL', 'https://app.midtrans.com/snap/v1');
    define('MIDTRANS_SNAP_URL', 'https://app.midtrans.com/snap/snap.js');
    define('MIDTRANS_CORE_API_URL', 'https://api.midtrans.com/v2');
} else {
    define('MIDTRANS_API_URL', 'https://app.sandbox.midtrans.com/snap/v1');
    define('MIDTRANS_SNAP_URL', 'https://app.sandbox.midtrans.com/snap/snap.js');
    define('MIDTRANS_CORE_API_URL', 'https://api.sandbox.midtrans.com/v2');
}
