<?php
include "db.php";


if (!isset($_SESSION['user_id']) || !isset($_POST['content_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$content_id = intval($_POST['content_id']);
$episode_id = isset($_POST['episode_id']) ? intval($_POST['episode_id']) : null;
$progress = isset($_POST['progress']) ? floatval($_POST['progress']) : null;
$action = isset($_POST['action']) ? $_POST['action'] : '';

// Check if record exists
$stmt = $conn->prepare("SELECT history_id FROM watch_history WHERE user_id = ? AND content_id = ?");
$stmt->bind_param('ii', $user_id, $content_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Update existing record
    $row = $result->fetch_assoc();
    $history_id = $row['history_id'];
    
    if ($action === 'complete') {
        $stmt = $conn->prepare("UPDATE watch_history SET progress_percent = 100, last_watched = NOW() WHERE history_id = ?");
        $stmt->bind_param('i', $history_id);
    } elseif ($progress !== null) {
        $stmt = $conn->prepare("UPDATE watch_history SET progress_percent = ?, last_watched = NOW() WHERE history_id = ?");
        $stmt->bind_param('di', $progress, $history_id);
    } else {
        $stmt = $conn->prepare("UPDATE watch_history SET last_watched = NOW() WHERE history_id = ?");
        $stmt->bind_param('i', $history_id);
    }
} else {
    // Insert new record
    if ($action === 'complete') {
        $stmt = $conn->prepare("INSERT INTO watch_history (user_id, content_id, progress_percent, last_watched) VALUES (?, ?, 100, NOW())");
    } elseif ($progress !== null) {
        $stmt = $conn->prepare("INSERT INTO watch_history (user_id, content_id, progress_percent, last_watched) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param('iid', $user_id, $content_id, $progress);
    } else {
        $stmt = $conn->prepare("INSERT INTO watch_history (user_id, content_id, last_watched) VALUES (?, ?, NOW())");
    }
}

$stmt->execute();
echo json_encode(['success' => true]);
?>