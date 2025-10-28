<?php
session_start();
include "db.php";
$type = 'episode';
$episode_id = 1;
$content_id = 21;
// header('Content-Type: application/json');

// if (!isset($_SESSION['user_id'])) {
//     http_response_code(401);
//     echo json_encode(['error' => 'Authentication required']);
//     exit;
// }

// if (!isset($_SESSION['is_premium']) || !$_SESSION['is_premium']) {
//     http_response_code(403);
//     echo json_encode(['error' => 'Premium subscription required']);
//     exit;
// }

// $content_id = intval($_POST['content_id'] ?? 0);
// $type = $_POST['type'] ?? '';
// $episode_id = intval($_POST['episode_id'] ?? 0);

// if ($content_id <= 0) {
//     http_response_code(400);
//     echo json_encode(['error' => 'Invalid content']);
//     exit;
// }

// Get file path based on type
$file_path = '';
$filename = '';

if ($type === 'movie') {
    $stmt = $conn->prepare("SELECT video_path, title FROM content WHERE content_id = ?");
    $stmt->bind_param('i', $content_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $file_path = $row['video_path'];
        $filename = preg_replace('/[^a-zA-Z0-9]/', '_', $row['title']) . '.mp4';
    }
    $stmt->close();
} elseif ($type === 'episode' && $episode_id > 0) {
    $stmt = $conn->prepare("SELECT e.video_path, e.title, c.title as content_title 
                           FROM episodes e 
                           JOIN content c ON e.content_id = c.content_id 
                           WHERE e.episode_id = ? AND e.content_id = ?");
    $stmt->bind_param('ii', $episode_id, $content_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $file_path = "admin/" . $row['video_path'];
        $filename = preg_replace('/[^a-zA-Z0-9]/', '_', $row['content_title']) . '_' . 
                   preg_replace('/[^a-zA-Z0-9]/', '_', $row['title']) . '.mp4';
    }
    $stmt->close();
}

if (empty($file_path) || !file_exists($file_path)) {
    http_response_code(404);
    echo json_encode(['error' => 'File not found']);
    exit;
}

// Set headers for download
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file_path));

// Output file
readfile($file_path);
exit;
?>