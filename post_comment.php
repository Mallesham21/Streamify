<?php
include "db.php";

header('Content-Type: application/json');

// Validate user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Please login to comment']);
    exit;
}

// Validate required fields
if (empty($_POST['content_id']) || empty($_POST['comment']) || empty($_POST['rating'])) {
    echo json_encode(['success' => false, 'error' => 'All fields are required']);
    exit;
}

$user_id = intval($_SESSION['user_id']);
$content_id = intval($_POST['content_id']);
$comment = trim($_POST['comment']);
$rating = intval($_POST['rating']);

// Validate rating range
if ($rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'error' => 'Invalid rating']);
    exit;
}

// Check if user already commented
$check_stmt = $conn->prepare("SELECT feedback_id FROM feedback WHERE user_id = ? AND content_id = ?");
$check_stmt->bind_param('ii', $user_id, $content_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    // Update existing comment
    $stmt = $conn->prepare("UPDATE feedback SET review_text = ?, rating = ?, created_at = NOW() WHERE user_id = ? AND content_id = ?");
    $stmt->bind_param('siii', $comment, $rating, $user_id, $content_id);
} else {
    // Insert new comment
    $stmt = $conn->prepare("INSERT INTO feedback (user_id, content_id, review_text, rating, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param('iisi', $user_id, $content_id, $comment, $rating);
}

if ($stmt->execute()) {
    // Get user info for response
    $user_stmt = $conn->prepare("SELECT username, profile_pic FROM users WHERE user_id = ?");
    $user_stmt->bind_param('i', $user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $user = $user_result->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'username' => $user['username'],
        'profile_pic' => $user['profile_pic'],
        'comment' => $comment,
        'rating' => $rating,
        'date' => date('M j, Y')
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>