<?php
/**
 * API: Get accessible frames for current user
 */
session_start();
require_once '../config/database.php';
require_once '../config/subscription.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $framesData = getAccessibleFrames($pdo, $user_id);
    $subscriptionStatus = getSubscriptionStatus($pdo, $user_id);
    
    echo json_encode([
        'success' => true,
        'frames' => $framesData['frames'],
        'can_upload' => $framesData['can_upload'],
        'max_frames' => $framesData['max_frames'],
        'current_count' => $framesData['current_count'],
        'subscription' => $subscriptionStatus
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
