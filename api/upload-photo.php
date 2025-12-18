<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_FILES['photo'])) {
    echo json_encode(['success' => false, 'message' => 'No photo uploaded']);
    exit;
}

$user_id = $_SESSION['user_id'];
$filter = $_POST['filter'] ?? 'none';
$file = $_FILES['photo'];

// Validate file
$allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
if (!in_array($file['type'], $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type']);
    exit;
}

// Generate unique filename
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'photo_' . $user_id . '_' . time() . '_' . uniqid() . '.' . $extension;
$upload_dir = '../uploads/photos/';
$file_path = $upload_dir . $filename;

// Create directory if not exists
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Move uploaded file
if (move_uploaded_file($file['tmp_name'], $file_path)) {
    // Save to database
    $stmt = $pdo->prepare("INSERT INTO photos (user_id, filename, original_filename, file_path, file_size, filter_applied) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $user_id,
        $filename,
        $file['name'],
        'uploads/photos/' . $filename,
        $file['size'],
        $filter
    ]);
    
    $photo_id = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Photo uploaded successfully',
        'photo_id' => $photo_id,
        'file_path' => 'uploads/photos/' . $filename
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to upload photo']);
}
