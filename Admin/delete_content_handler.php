<?php
// delete_content_handler.php
header('Content-Type: application/json');

// Database connection
$conn = new mysqli('localhost', 'root', '', 'Streamify');
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_content') {
    $content_id = $conn->real_escape_string($_POST['content_id']);
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Get content title for response message
        $title_result = $conn->query("SELECT title FROM content WHERE content_id = $content_id");
        $content_title = 'Content';
        if ($title_result && $title_result->num_rows > 0) {
            $content_data = $title_result->fetch_assoc();
            $content_title = $content_data['title'];
        }
        
        // Delete content categories first
        $conn->query("DELETE FROM content_categories WHERE content_id = $content_id");
        
        // Delete episodes if any
        $conn->query("DELETE FROM episodes WHERE content_id = $content_id");
        
        // Delete feedback
        $conn->query("DELETE FROM feedback WHERE content_id = $content_id");
        
        // Delete from watchlist and watch_history
        $conn->query("DELETE FROM watchlist WHERE content_id = $content_id");
        $conn->query("DELETE FROM watch_history WHERE content_id = $content_id");
        
        // Finally delete content
        $delete_result = $conn->query("DELETE FROM content WHERE content_id = $content_id");
        
        if ($delete_result) {
            $conn->commit();
            echo json_encode([
                'success' => true, 
                'message' => '"' . htmlspecialchars($content_title) . '" has been deleted successfully!'
            ]);
        } else {
            throw new Exception('Failed to delete content');
        }
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode([
            'success' => false, 
            'message' => 'Error deleting content: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}

$conn->close();
?>