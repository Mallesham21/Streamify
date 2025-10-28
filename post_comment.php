<?php
session_start();
include "db.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Please log in to submit a review']);
    exit;
}

$user_id = $_SESSION['user_id'];
$content_id = intval($_POST['content_id'] ?? 0);
$rating = intval($_POST['rating'] ?? 0);
$comment = trim($_POST['comment'] ?? '');

if ($content_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid content']);
    exit;
}

if ($rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'error' => 'Please select a valid rating']);
    exit;
}

if (empty($comment)) {
    echo json_encode(['success' => false, 'error' => 'Please write a comment']);
    exit;
}

// Check if user already reviewed this content
$check_stmt = $conn->prepare("SELECT feedback_id FROM feedback WHERE user_id = ? AND content_id = ?");
$check_stmt->bind_param('ii', $user_id, $content_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    // Update existing review
    $update_stmt = $conn->prepare("UPDATE feedback SET rating = ?, review_text = ?, created_at = NOW() WHERE user_id = ? AND content_id = ?");
    $update_stmt->bind_param('isii', $rating, $comment, $user_id, $content_id);
    $success = $update_stmt->execute();
    $update_stmt->close();
} else {
    // Insert new review
    $insert_stmt = $conn->prepare("INSERT INTO feedback (user_id, content_id, rating, review_text) VALUES (?, ?, ?, ?)");
    $insert_stmt->bind_param('iiis', $user_id, $content_id, $rating, $comment);
    $success = $insert_stmt->execute();
    $insert_stmt->close();
}

if ($success) {
    // Get user info for response
    $user_stmt = $conn->prepare("SELECT username, profile_pic FROM users WHERE user_id = ?");
    $user_stmt->bind_param('i', $user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $user_data = $user_result->fetch_assoc();
    $user_stmt->close();
    
    // Get updated average rating and total ratings
    $stats_stmt = $conn->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total_ratings FROM feedback WHERE content_id = ?");
    $stats_stmt->bind_param('i', $content_id);
    $stats_stmt->execute();
    $stats_result = $stats_stmt->get_result();
    $stats_data = $stats_result->fetch_assoc();
    $stats_stmt->close();
    
    echo json_encode([
        'success' => true,
        'username' => $user_data['username'],
        'profile_pic' => $user_data['profile_pic'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($user_data['username']) . '&background=random&color=fff',
        'rating' => $rating,
        'comment' => htmlspecialchars($comment),
        'date' => 'Just now',
        'new_avg_rating' => floatval($stats_data['avg_rating']),
        'new_total_ratings' => intval($stats_data['total_ratings'])
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to submit review']);
}

$conn->close();
?>