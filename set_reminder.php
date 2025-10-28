<?php
session_start();
require_once "db.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to set reminders']);
    exit;
}

if (!isset($_POST['content_id'])) {
    echo json_encode(['success' => false, 'message' => 'Content ID is required']);
    exit;
}

$user_id = $_SESSION['user_id'];
$content_id = intval($_POST['content_id']);

try {
    // Get content details to check if it's scheduled
    $contentStmt = $conn->prepare("SELECT title, schedule_date FROM content WHERE content_id = ?");
    $contentStmt->bind_param("i", $content_id);
    $contentStmt->execute();
    $contentResult = $contentStmt->get_result();
    
    if ($contentResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Content not found']);
        exit;
    }
    
    $content = $contentResult->fetch_assoc();
    $contentStmt->close();

    // Check if reminder already exists
    $checkStmt = $conn->prepare("SELECT reminder_id FROM reminders WHERE user_id = ? AND content_id = ?");
    $checkStmt->bind_param("ii", $user_id, $content_id);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        // Remove reminder
        $row = $result->fetch_assoc();
        $deleteStmt = $conn->prepare("DELETE FROM reminders WHERE reminder_id = ?");
        $deleteStmt->bind_param("i", $row['reminder_id']);
        $deleteStmt->execute();
        $deleteStmt->close();
        
        // Create notification for reminder removal
        $notificationStmt = $conn->prepare("
            INSERT INTO notifications (user_id, type, title, message, related_id, created_at) 
            VALUES (?, 'general', 'Reminder Removed', 'Reminder for \"{$content['title']}\" has been removed', ?, NOW())
        ");
        $notificationStmt->bind_param("ii", $user_id, $content_id);
        $notificationStmt->execute();
        $notificationStmt->close();
        
        echo json_encode(['success' => true, 'action' => 'removed', 'message' => 'Reminder removed successfully']);
    } else {
        // Set reminder date based on content schedule
        $reminder_date = $content['schedule_date'] ? $content['schedule_date'] : NULL;
        
        // Add reminder
        $insertStmt = $conn->prepare("
            INSERT INTO reminders (user_id, content_id, status, reminder_date, created_at) 
            VALUES (?, ?, 'active', ?, NOW())
        ");
        $insertStmt->bind_param("iis", $user_id, $content_id, $reminder_date);
        $insertStmt->execute();
        $insertStmt->close();
        
        // Create notification for reminder set
        $message = $content['schedule_date'] 
            ? "You will be notified when \"{$content['title']}\" releases on " . date('M j, Y', strtotime($content['schedule_date']))
            : "You will be notified when \"{$content['title']}\" becomes available";
            
        $notificationStmt = $conn->prepare("
            INSERT INTO notifications (user_id, type, title, message, related_id, created_at) 
            VALUES (?, 'reminder_set', 'Reminder Set', ?, ?, NOW())
        ");
        $notificationStmt->bind_param("isi", $user_id, $message, $content_id);
        $notificationStmt->execute();
        $notificationStmt->close();
        
        echo json_encode(['success' => true, 'action' => 'set', 'message' => 'Reminder set successfully']);
    }
    
    $checkStmt->close();
    
} catch (Exception $e) {
    error_log("Reminder error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}

$conn->close();
?>