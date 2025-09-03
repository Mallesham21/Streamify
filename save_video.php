<?php
// save_video.php
$video_url = $_POST['video_url'] ?? '';
if ($video_url) {
    $conn = new mysqli("localhost", "root", "", "streamify");
    if ($conn->connect_error) die("DB Connection Failed");
    $stmt = $conn->prepare("INSERT INTO videos (video_url) VALUES (?)");
    $stmt->bind_param("s", $video_url);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    echo "Saved to DB";
} else {
    echo "No video URL received";
}
?>