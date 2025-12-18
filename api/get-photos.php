<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM photos WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$photos = $stmt->fetchAll();

echo json_encode([
    'success' => true,
    'photos' => $photos
]);
