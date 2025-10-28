<?php
require_once "db.php";
require_once "notification_manager.php";

// Create database connection
$notificationManager = new NotificationManager($conn);

// Run all scheduled checks
$results = $notificationManager->runScheduledChecks();

// Return JSON response
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'timestamp' => date('Y-m-d H:i:s'),
    'results' => $results,
    'message' => 'Scheduled notifications checked successfully'
]);
?>