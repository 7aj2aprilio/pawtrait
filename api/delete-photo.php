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
        $file_deleted = false;
        $db_deleted = false;

        // Delete file if it exists
        $file_path = dirname(__DIR__) . '/' . $photo['file_path'];
        if (file_exists($file_path)) {
            if (unlink($file_path)) {
                $file_deleted = true;
            }
        } else {
            // File already gone, treat as deleted
            $file_deleted = true;
        }

        // Delete from database
        $deleteStmt = $pdo->prepare("DELETE FROM photos WHERE id = ? AND user_id = ?");
        if ($deleteStmt->execute([$photo_id, $user_id])) {
            $db_deleted = true;
        }

        if ($db_deleted) {
            echo json_encode([
                'success' => true, 
                'message' => 'Photo deleted successfully',
                'file_removed' => $file_deleted
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete photo from database']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Photo not found in database']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
