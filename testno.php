<?php
session_start();
require_once 'db.php';
require_once 'notification_manager.php';

// ONLY process AJAX requests - NO HTML OUTPUT
header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'data' => []];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $user_id = $_SESSION['user_id'] ?? 9;
    $username = $_SESSION['username'] ?? 'TestUser';
    $notificationManager = new NotificationManager($conn);
    
    try {
        switch ($_POST['action']) {
            case 'test_welcome':
                $result = $notificationManager->createWelcomeNotification($user_id, $username);
                $response['success'] = $result;
                $response['message'] = $result ? 'Welcome notification created!' : 'Failed to create welcome notification';
                break;
                
            case 'test_trending':
                $trending = $notificationManager->getTrendingContent(3);
                $response['success'] = true;
                $response['message'] = 'Found ' . count($trending) . ' trending items';
                $response['data'] = $trending;
                break;
                
            case 'test_recommendation':
                $trending = $notificationManager->getTrendingContent(2);
                if (!empty($trending)) {
                    $result1 = $notificationManager->createRecommendation($user_id, $trending[0]['title'], $trending[0]['content_id'], "trending now");
                    $response['success'] = $result1;
                    $response['message'] = 'Recommendation created: ' . ($result1 ? 'Success' : 'Failed');
                } else {
                    $response['message'] = 'No trending content found';
                }
                break;
                
            case 'view_notifications':
                $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $notifications = [];
                while ($row = $result->fetch_assoc()) {
                    $notifications[] = $row;
                }
                $stmt->close();
                $response['success'] = true;
                $response['data'] = $notifications;
                $response['message'] = 'Found ' . count($notifications) . ' notifications';
                break;
                
            default:
                $response['message'] = 'Unknown action';
        }
    } catch (Exception $e) {
        $response['message'] = 'Error: ' . $e->getMessage();
    }
}

echo json_encode($response);
exit;