<?php
// test-db-connection.php

// Hardcode values temporarily to test the connection itself
// Replace these with the values you pasted if they differ!
$host = 'pawtrait-photobooth-server.mysql.database.azure.com';
$user = 'yktjmthsur'; // From your message
$pass = 'sfl5DhAVHCMUwxa$'; // From your message
$db   = 'pawtrait-photobooth-database';
$port = 3306;

echo "<h3>Database Connection Test</h3>";
echo "Host: $host<br>";
echo "User: $user<br>";
echo "DB: $db<br>";
echo "Port: $port<br>";
echo "<hr>";

try {
    // 1. Test Simple TCP Socket Open (ignores PHP/PDO, tests network)
    echo "Testing Network Reachability TCP/3306... ";
    $fp = @fsockopen($host, $port, $errno, $errstr, 5);
    if (!$fp) {
        throw new Exception("Network Error: Cannot reach server ($errno: $errstr). <br><strong>CHECK AZURE FIREWALL: Add your Client IP to the allowed list!</strong>");
    }
    echo "<span style='color:green'>OK (Server is reachable)</span><br>";
    fclose($fp);
    
    // 2. Test PDO Connection
    echo "Testing PDO Connection... ";
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        // SSL Options for Azure
        PDO::MYSQL_ATTR_SSL_CA => null,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ];
    
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "<span style='color:green'>SUCCESS! Connected to Database.</span>";
    
} catch (Exception $e) {
    echo "<span style='color:red'>FAILED</span><br>";
    echo "Error: " . $e->getMessage();
}
?>
