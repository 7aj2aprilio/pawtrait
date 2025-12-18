<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['photo_id'])) {
    echo json_encode(['success' => false, 'message' => 'Photo ID is required']);
    exit;
}

$photo_id = $input['photo_id'];
$user_id = $_SESSION['user_id'];

try {
    // Get photo path first to delete file
    $stmt = $pdo->prepare("SELECT file_path FROM photos WHERE id = ? AND user_id = ?");
    $stmt->execute([$photo_id, $user_id]);
    $photo = $stmt->fetch();

    if ($photo) {
        // Delete from database
        $deleteStmt = $pdo->prepare("DELETE FROM photos WHERE id = ? AND user_id = ?");
        $deleteStmt->execute([$photo_id, $user_id]);

        // Delete file if it exists
        $file_path = '../' . $photo['file_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        echo json_encode(['success' => true, 'message' => 'Photo deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Photo not found']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error deleting photo: ' . $e->getMessage()]);
}
