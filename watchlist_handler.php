<?php
session_start();
include "db.php";

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to manage your watchlist']);
    exit;
}

$user_id = intval($_SESSION['user_id']);
$content_id = isset($_POST['content_id']) ? intval($_POST['content_id']) : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($content_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid content']);
    exit;
}

// Verify content exists
$stmt = $conn->prepare("SELECT content_id FROM content WHERE content_id = ?");
$stmt->bind_param('i', $content_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Content not found']);
    exit;
}
$stmt->close();

try {
    if ($action === 'add') {
        // Check if already in watchlist
        $check_stmt = $conn->prepare("SELECT watchlist_id FROM watchlist WHERE user_id = ? AND content_id = ?");
        $check_stmt->bind_param('ii', $user_id, $content_id);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Already in watchlist']);
            exit;
        }
        $check_stmt->close();
        
        // Add to watchlist
        $insert_stmt = $conn->prepare("INSERT INTO watchlist (user_id, content_id, added_at) VALUES (?, ?, NOW())");
        $insert_stmt->bind_param('ii', $user_id, $content_id);
        
        if ($insert_stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Added to watchlist']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add to watchlist']);
        }
        $insert_stmt->close();
        
    } elseif ($action === 'remove') {
        // Remove from watchlist
        $delete_stmt = $conn->prepare("DELETE FROM watchlist WHERE user_id = ? AND content_id = ?");
        $delete_stmt->bind_param('ii', $user_id, $content_id);
        
        if ($delete_stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Removed from watchlist']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to remove from watchlist']);
        }
        $delete_stmt->close();
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
?>