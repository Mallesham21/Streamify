<?php
include "db.php";
session_start();

if (!isset($_SESSION['user_id']) ){
    echo json_encode(['success' => false, 'message' => 'Please login to manage your watchlist']);
    exit;
}

$user_id = $_SESSION['user_id'];
$content_id = intval($_POST['content_id']);
$action = $_POST['action'] === 'add' ? 'add' : 'remove';

if ($action === 'add') {
    // Check if already in watchlist
    $stmt = $conn->prepare("SELECT 1 FROM watchlist WHERE user_id = ? AND content_id = ?");
    $stmt->bind_param('ii', $user_id, $content_id);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows === 0) {
        $stmt = $conn->prepare("INSERT INTO watchlist (user_id, content_id, added_at) VALUES (?, ?, NOW())");
        $stmt->bind_param('ii', $user_id, $content_id);
        $stmt->execute();
    }
    echo json_encode(['success' => true, 'message' => 'Added to watchlist']);
} else {
    $stmt = $conn->prepare("DELETE FROM watchlist WHERE user_id = ? AND content_id = ?");
    $stmt->bind_param('ii', $user_id, $content_id);
    $stmt->execute();
    echo json_encode(['success' => true, 'message' => 'Removed from watchlist']);
}
?>