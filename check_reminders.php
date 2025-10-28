<?php
session_start();
require_once "db.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $conn->prepare("
        SELECT r.content_id, c.title, c.schedule_date 
        FROM reminders r 
        JOIN content c ON r.content_id = c.content_id 
        WHERE r.user_id = ? AND r.status = 'active'
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $reminders = [];
    while ($row = $result->fetch_assoc()) {
        $reminders[] = [
            'content_id' => $row['content_id'],
            'title' => $row['title'],
            'schedule_date' => $row['schedule_date']
        ];
    }
    
    echo json_encode(['success' => true, 'reminders' => $reminders]);
    
} catch (Exception $e) {
    error_log("Check reminders error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}

$conn->close();
?>