<?php
/**
 * API: Upload custom frame (Premium users only)
 */
session_start();
require_once '../config/database.php';
require_once '../config/subscription.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if user can upload frames
$framesData = getAccessibleFrames($pdo, $user_id);

if (!$framesData['can_upload']) {
    echo json_encode([
        'success' => false, 
        'message' => 'Upgrade to Premium package to upload custom frames',
        'show_packages' => true
    ]);
    exit;
}

// Check frame limit
if ($framesData['current_count'] >= $framesData['max_frames']) {
    echo json_encode([
        'success' => false, 
        'message' => 'You have reached your maximum frame limit (' . $framesData['max_frames'] . ' frames)'
    ]);
    exit;
}

if (!isset($_FILES['frame'])) {
    echo json_encode(['success' => false, 'message' => 'No frame file uploaded']);
    exit;
}

$file = $_FILES['frame'];

// Validate file type
$allowed_types = ['image/png', 'image/jpeg', 'image/jpg'];
if (!in_array($file['type'], $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only PNG and JPG allowed.']);
    exit;
}

// Validate file size (max 5MB)
$max_size = 5 * 1024 * 1024;
if ($file['size'] > $max_size) {
    echo json_encode(['success' => false, 'message' => 'File too large. Maximum 5MB allowed.']);
    exit;
}

// Generate unique filename
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'custom_frame_' . $user_id . '_' . time() . '_' . uniqid() . '.' . $extension;
$upload_dir = '../uploads/frames/';
$file_path = $upload_dir . $filename;

// Create directory if not exists
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Move uploaded file
if (move_uploaded_file($file['tmp_name'], $file_path)) {
    // Save to database
    $stmt = $pdo->prepare("INSERT INTO custom_frames (user_id, filename, file_path) VALUES (?, ?, ?)");
    $stmt->execute([
        $user_id,
        $filename,
        'uploads/frames/' . $filename
    ]);
    
    $frame_id = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Frame uploaded successfully',
        'frame' => [
            'id' => 'custom_' . $frame_id,
            'filename' => $filename,
            'path' => 'uploads/frames/' . $filename,
            'is_custom' => true
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to upload frame']);
}
