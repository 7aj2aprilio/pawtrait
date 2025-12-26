<?php
require_once 'config/database.php';

try {
    // Check if column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'google_id'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN google_id VARCHAR(255) UNIQUE DEFAULT NULL AFTER email");
        echo "Column 'google_id' added successfully.\n";
    } else {
        echo "Column 'google_id' already exists.\n";
    }
    
    // Make password nullable if it's not already (checking this is harder, so we'll just try to modify it)
    // Actually, schema said NOT NULL. We should make it nullable for Google users or keep it and set dummy. 
    // Plan said: "Make password nullable".
    
    $pdo->exec("ALTER TABLE users MODIFY COLUMN password VARCHAR(255) NULL");
    echo "Column 'password' modified to be nullable.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
