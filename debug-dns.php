<?php
header('Content-Type: text/plain');
echo "DNS Resolution Test\n";
echo "===================\n\n";

$hosts = [
    'google.com',
    'p-server.mysql.database.azure.com',
    'pawtrait-photobooth-server.mysql.database.azure.com'
];

foreach ($hosts as $host) {
    echo "Testing: $host\n";
    $ip = gethostbyname($host);
    if ($ip !== $host) {
        echo "Result: RESOLVED to $ip ✅\n";
    } else {
        echo "Result: FAILED to resolve ❌\n";
        
        // Try to get more info
        $records = dns_get_record($host, DNS_A);
        if (empty($records)) {
            echo "A records: NONE found\n";
        } else {
            print_r($records);
        }
    }
    echo "-------------------\n";
}

echo "\nEnvironment Variables Check:\n";
echo "DB_HOST: " . getenv('DB_HOST') . "\n";
echo "APPSETTING_DB_HOST: " . getenv('APPSETTING_DB_HOST') . "\n";
?>
