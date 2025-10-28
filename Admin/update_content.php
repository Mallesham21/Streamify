<?php
session_start();
header('Content-Type: application/json');

$conn = new mysqli('localhost', 'root', '', 'Streamify');
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error, 'type' => 'danger']);
    exit;
}

// Handle file size exceeded errors
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && $_SERVER['CONTENT_LENGTH'] > 0) {
    echo json_encode([
        'success' => false,
        'message' => 'File size too large. Maximum allowed: ' . ini_get('post_max_size'),
        'type' => 'danger'
    ]);
    exit;
}

// Check if this is an update content request
$is_update_request = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if action exists in POST (normal case)
    if (isset($_POST['action']) && $_POST['action'] === 'update_content') {
        $is_update_request = true;
    }
    // For large file uploads, you might need to check other indicators
    // or modify your form to always include the action
}

if ($is_update_request) {
    // YOUR EXISTING UPDATE LOGIC HERE
    $content_id = $conn->real_escape_string($_POST['content_id']);
    $title = $conn->real_escape_string($_POST['title']);
    $content_type = $conn->real_escape_string($_POST['content_type']);
    $description = $conn->real_escape_string($_POST['description']);
    $release_year = intval($_POST['release_year']);
    $duration = isset($_POST['duration']) ? intval($_POST['duration']) : NULL;
    $featured = isset($_POST['featured']) ? 1 : 0;
    $is_premium = isset($_POST['is_premium']) ? 1 : 0;
    $rating = isset($_POST['rating']) ? floatval($_POST['rating']) : NULL;

    // Handle publish options
    $publish_option = $conn->real_escape_string($_POST['publish_option']);
    $is_scheduled = ($publish_option === 'schedule') ? 1 : 0;
    $release_date = ($publish_option === 'schedule' && isset($_POST['schedule_date'])) 
        ? $conn->real_escape_string($_POST['schedule_date']) 
        : NULL;

    // Handle file uploads using the same upload function as add_content_handler.php
    $thumbnail_url = null;
    $banner_url = null;
    $video_path = null;

    // Upload thumbnail
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $thumbnail_url = uploadFile($_FILES['thumbnail'], 'thumbnails/', $title, 'thumbnail');
    }

    // Upload banner
    if (isset($_FILES['banner']) && $_FILES['banner']['error'] === UPLOAD_ERR_OK) {
        $banner_url = uploadFile($_FILES['banner'], 'banners/', $title, 'banner');
    }

    // Upload video (for movies)
    if ($content_type === 'movie' && isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
        $video_path = uploadFile($_FILES['video'], 'videos/', $title, 'video');
    }

    // Build update query
    $update_fields = [
        "title = '$title'",
        "content_type = '$content_type'",
        "description = '$description'",
        "release_year = $release_year",
        "duration = " . ($duration ? "'$duration'" : "NULL"),
        "featured = $featured",
        "is_premium = $is_premium",
        "rating = " . ($rating ? "'$rating'" : "NULL"),
        "is_scheduled = $is_scheduled",
        "schedule_date = " . ($release_date ? "'$release_date'" : "NULL")
    ];

    if ($thumbnail_url) {
        $update_fields[] = "thumbnail_url = '$thumbnail_url'";
    }
    if ($banner_url) {
        $update_fields[] = "banner_url = '$banner_url'";
    }
    if ($video_path) {
        $update_fields[] = "video_path = '$video_path'";
    }

    $update_query = "UPDATE content SET " . implode(', ', $update_fields) . " WHERE content_id = $content_id";
    
    if ($conn->query($update_query)) {
        // Update categories
        $conn->query("DELETE FROM content_categories WHERE content_id = $content_id");
        if (isset($_POST['categories'])) {
            foreach ($_POST['categories'] as $category_id) {
                $category_id = intval($category_id);
                $conn->query("INSERT INTO content_categories (content_id, category_id) VALUES ($content_id, $category_id)");
            }
        }

        // Update episodes for TV shows
        if ($content_type === 'tv_show') {
            // Update existing episodes
            if (isset($_POST['episode_titles'])) {
                foreach ($_POST['episode_titles'] as $episode_id => $title) {
                    $episode_id = intval($episode_id);
                    $title = $conn->real_escape_string($title);
                    $duration = intval($_POST['episode_durations'][$episode_id]);
                    $release_date = $conn->real_escape_string($_POST['episode_dates'][$episode_id]);
                    $description = $conn->real_escape_string($_POST['episode_descriptions'][$episode_id]);
                    
                    // Handle episode video upload
                    $episode_video_path = null;
                    if (isset($_FILES['episode_videos']['name'][$episode_id]) && $_FILES['episode_videos']['error'][$episode_id] === UPLOAD_ERR_OK) {
                        $episode_video = [
                            'name' => $_FILES['episode_videos']['name'][$episode_id],
                            'type' => $_FILES['episode_videos']['type'][$episode_id],
                            'tmp_name' => $_FILES['episode_videos']['tmp_name'][$episode_id],
                            'error' => $_FILES['episode_videos']['error'][$episode_id],
                            'size' => $_FILES['episode_videos']['size'][$episode_id]
                        ];
                        $episode_video_path = uploadFile($episode_video, 'videos/', $title . '_ep' . $episode_id, 'episode');
                    }
                    
                    $update_episode_fields = [
                        "title = '$title'",
                        "duration_minutes = $duration",
                        "release_date = " . ($release_date ? "'$release_date'" : "NULL"),
                        "description = '$description'"
                    ];
                    
                    if ($episode_video_path) {
                        $update_episode_fields[] = "video_path = '$episode_video_path'";
                    }
                    
                    $update_episode_query = "UPDATE episodes SET " . implode(', ', $update_episode_fields) . " WHERE episode_id = $episode_id";
                    $conn->query($update_episode_query);
                }
            }
            
            // Add new episodes
            if (isset($_POST['new_episode_titles'])) {
                foreach ($_POST['new_episode_titles'] as $index => $title) {
                    $title = $conn->real_escape_string($title);
                    $duration = intval($_POST['new_episode_durations'][$index]);
                    $release_date = $conn->real_escape_string($_POST['new_episode_dates'][$index]);
                    $description = $conn->real_escape_string($_POST['new_episode_descriptions'][$index]);
                    $episode_number = $index + 1;
                    
                    // Handle new episode video upload
                    $episode_video_path = null;
                    if (isset($_FILES['new_episode_videos']['tmp_name'][$index]) && $_FILES['new_episode_videos']['error'][$index] === UPLOAD_ERR_OK) {
                        $episode_video = [
                            'name' => $_FILES['new_episode_videos']['name'][$index],
                            'type' => $_FILES['new_episode_videos']['type'][$index],
                            'tmp_name' => $_FILES['new_episode_videos']['tmp_name'][$index],
                            'error' => $_FILES['new_episode_videos']['error'][$index],
                            'size' => $_FILES['new_episode_videos']['size'][$index]
                        ];
                        $episode_video_path = uploadFile($episode_video, 'videos/', $title . '_ep' . $episode_number, 'episode');
                    }
                    
                    $insert_episode_query = "INSERT INTO episodes (content_id, episode_number, title, duration_minutes, release_date, description, video_path) 
                                           VALUES ($content_id, $episode_number, '$title', $duration, " . ($release_date ? "'$release_date'" : "NULL") . ", '$description', " . ($episode_video_path ? "'$episode_video_path'" : "NULL") . ")";
                    $conn->query($insert_episode_query);
                }
            }
        }

        echo json_encode([
            'success' => true,
            'message' => 'Content updated successfully!',
            'type' => 'success'
        ]);
        
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error updating content: ' . $conn->error,
            'type' => 'danger'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request',
        'type' => 'danger'
    ]);
}

// File upload function (same as in add_content_handler.php)
function uploadFile($file, $target_dir, $content_title, $file_type) {
    // Create directory if it doesn't exist
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    // Sanitize filename
    $content_title_clean = preg_replace('/[^a-zA-Z0-9_-]/', '_', $content_title);
    
    // Generate filename based on file type
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if ($file_type === 'thumbnail') {
        $file_name = $content_title_clean . $file_extension;
    } elseif ($file_type === 'banner') {
        $file_name = $content_title_clean . $file_extension;
    } elseif ($file_type === 'video') {
        $file_name = $content_title_clean .  $file_extension;
    } elseif ($file_type === 'episode') {
        $file_name = $content_title_clean .  $file_extension;
    } else {
        $file_name = $content_title_clean . '.' . $file_extension;
    }
    
    $target_file = $target_dir . $file_name;
    
    // Check if file already exists and generate unique name
    $counter = 1;
    while (file_exists($target_file)) {
        $file_name = $content_title_clean . $file_extension;
        $target_file = $target_dir . $file_name;
        $counter++;
    }
    
    // Check file size (limit to 500MB for videos, 10MB for images)
    $max_size = (strpos($file['type'], 'video/') === 0) ? 500 * 1024 * 1024 : 10 * 1024 * 1024;
    if ($file['size'] > $max_size) {
        return '';
    }
    
    // Check file type
    $allowed_types = [];
    if (strpos($file['type'], 'image/') === 0) {
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    } elseif (strpos($file['type'], 'video/') === 0) {
        $allowed_types = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv'];
    }
    
    if (!in_array($file_extension, $allowed_types)) {
        return '';
    }
    
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return $target_file;
    } else {
        return '';
    }
}

// Close database connection
$conn->close();
?>