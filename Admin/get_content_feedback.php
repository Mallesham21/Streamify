<?php
$conn = new mysqli('localhost', 'root', '', 'Streamify');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

$content_id = $conn->real_escape_string($_GET['content_id']);

$feedback_query = "
    SELECT f.*, u.username 
    FROM feedback f 
    JOIN users u ON f.user_id = u.user_id 
    WHERE f.content_id = $content_id 
    ORDER BY f.created_at DESC
";
$feedback_result = $conn->query($feedback_query);

if ($feedback_result->num_rows > 0) {
    while ($feedback = $feedback_result->fetch_assoc()) {
        echo '
        <div class="feedback-item">
            <div class="feedback-header">
                <div class="feedback-user">' . htmlspecialchars($feedback['username']) . '</div>
                <div class="feedback-rating">';
        
        // Display star rating
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $feedback['rating']) {
                echo '<i class="fas fa-star"></i>';
            } else {
                echo '<i class="far fa-star"></i>';
            }
        }
        
        echo '</div>
            </div>
            <div class="feedback-date">' . date('M j, Y g:i A', strtotime($feedback['created_at'])) . '</div>';
        
        if ($feedback['review_text']) {
            echo '<div class="feedback-text">' . htmlspecialchars($feedback['review_text']) . '</div>';
        }
        
        echo '</div>';
    }
} else {
    echo '<div class="text-center text-muted py-4">No feedback available for this content.</div>';
}

$conn->close();
?>