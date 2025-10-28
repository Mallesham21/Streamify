<?php
session_start();
include "db.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($data['content_id']) || !isset($data['rating']) || 
    !is_numeric($data['content_id']) || !is_numeric($data['rating']) || 
    $data['rating'] < 1 || $data['rating'] > 5) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

$content_id = intval($data['content_id']);
$user_id = $_SESSION['user_id'];
$rating = intval($data['rating']);

try {
    // Check if content exists
    $content_stmt = $conn->prepare("SELECT content_id FROM content WHERE content_id = ?");
    $content_stmt->bind_param('i', $content_id);
    $content_stmt->execute();
    if ($content_stmt->get_result()->num_rows === 0) {
        throw new Exception('Content not found');
    }
    $content_stmt->close();

    // Insert or update rating
    $stmt = $conn->prepare("INSERT INTO ratings (content_id, user_id, rating) 
                          VALUES (?, ?, ?) 
                          ON DUPLICATE KEY UPDATE rating = ?");
    $stmt->bind_param('iiii', $content_id, $user_id, $rating, $rating);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}