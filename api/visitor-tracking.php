<?php
/**
 * Visitor Tracking API
 * Tracks page views and unique visitors for analytics
 */

header('Content-Type: application/json');
require_once '../config/database.php';

try {
    $today = date('Y-m-d');
    
    // Check if entry exists for today
    $stmt = $pdo->prepare("SELECT id FROM visitor_logs WHERE visit_date = ?");
    $stmt->execute([$today]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Update existing entry - increment page views
        $stmt = $pdo->prepare("
            UPDATE visitor_logs 
            SET page_views = page_views + 1,
                updated_at = CURRENT_TIMESTAMP
            WHERE visit_date = ?
        ");
        $stmt->execute([$today]);
    } else {
        // Insert new entry for today
        $stmt = $pdo->prepare("
            INSERT INTO visitor_logs (visit_date, page_views, unique_visitors)
            VALUES (?, 1, 1)
        ");
        $stmt->execute([$today]);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Visit tracked successfully'
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error tracking visit: ' . $e->getMessage()
    ]);
}
