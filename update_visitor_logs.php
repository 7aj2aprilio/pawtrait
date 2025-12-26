<?php
/**
 * Database Update Script - Add visitor_logs table
 * Run this file once to add the visitor_logs table to your database
 */

require_once 'config/database.php';

try {
    echo "Adding visitor_logs table...\n";
    
    $sql = "
    CREATE TABLE IF NOT EXISTS visitor_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        visit_date DATE NOT NULL,
        page_views INT DEFAULT 1,
        unique_visitors INT DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_date (visit_date),
        INDEX idx_visit_date (visit_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $pdo->exec($sql);
    
    echo "✅ Success! visitor_logs table has been created.\n";
    echo "You can now use the Analytics dashboard.\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
