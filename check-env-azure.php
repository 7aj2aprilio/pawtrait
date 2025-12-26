<?php
// check-env-azure.php
header('Content-Type: text/plain');

$targets = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 'MIDTRANS_SERVER_KEY'];

echo "--- Azure Environment Check (Deep Debug) ---\n\n";

// 1. Check Specific Targets
echo "1. Checking Target Variables:\n";
foreach ($targets as $key) {
    $val = getenv($key);
    if ($val === false) { $val = $_ENV[$key] ?? null; }
    if ($val === false) { $val = $_SERVER[$key] ?? null; }

    if ($val) {
        $masked = substr($val, 0, 3) . '***' . substr($val, -3);
        echo "   [$key] : SET ✅ ($masked)\n";
    } else {
        echo "   [$key] : MISSING ❌\n";
    }
}

// 2. List ALL Keys (Keys Only, for security)
echo "\n2. Available Environment Keys (Values Hidden):\n";
$all_keys = array_merge(array_keys($_ENV), array_keys($_SERVER));
$all_keys = array_unique($all_keys);
sort($all_keys);

foreach ($all_keys as $k) {
    // Filter out standard system envs to reduce noise
    if (strpos($k, 'APPSETTING_') === 0 || strpos($k, 'DB_') === 0 || strpos($k, 'MYSQL') !== false || strpos($k, 'AZURE') !== false) {
        echo "   - $k\n";
    }
}

echo "\n-------------------------------\n";
echo "PHP Version: " . phpversion() . "\n";
?>
