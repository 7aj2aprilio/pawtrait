<?php
// check-env-azure.php
header('Content-Type: text/plain');

$vars = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 'MIDTRANS_SERVER_KEY'];

echo "--- Azure Environment Check ---\n\n";

foreach ($vars as $key) {
    $val = getenv($key);
    if ($val === false) {
        $val = $_ENV[$key] ?? null; // Try $_ENV as fallback
    }

    if ($val) {
        // Mask the value for security
        $masked = substr($val, 0, 3) . '***' . substr($val, -3);
        echo "[$key] : SET ($masked)\n";
    } else {
        echo "[$key] : MISSING ❌\n";
    }
}

echo "\n-------------------------------\n";
echo "PHP Version: " . phpversion() . "\n";
?>
