<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session (keep for other session data if needed)
session_start();

// Set header for JSON response
header('Content-Type: application/json');

// Database connection
$conn = new mysqli('localhost', 'root', '', 'Streamify');
if ($conn->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => 'Connection failed: ' . $conn->connect_error,
        'type' => 'danger'
    ]);
    exit;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Check if action is set
    if (isset($_POST['action']) && $_POST['action'] === 'add_content') {
        
        // Get form data with proper validation
        $title = isset($_POST['title']) ? $conn->real_escape_string(trim($_POST['title'])) : '';
        $content_type = isset($_POST['content_type']) ? $conn->real_escape_string(trim($_POST['content_type'])) : '';
        $description = isset($_POST['description']) ? $conn->real_escape_string(trim($_POST['description'])) : '';
        $duration = isset($_POST['duration']) ? intval($_POST['duration']) : 0;
        $featured = isset($_POST['featured']) ? 1 : 0;
        $is_premium = isset($_POST['is_premium']) ? 1 : 0;
        $release_year = isset($_POST['release_year']) ? intval($_POST['release_year']) : date('Y');
$rating = isset($_POST['rating']) && !empty($_POST['rating']) ? floatval($_POST['rating']) : null;
        // Validate required fields
        if (empty($title) || empty($content_type)) {
            echo json_encode([
                'success' => false,
                'message' => "Title and content type are required",
                'type' => 'danger'
            ]);
            exit;
        }
        
        // Handle publish options
        $is_scheduled = 0;
        $schedule_date = null;
        
        if (isset($_POST['publish_option']) && $_POST['publish_option'] === 'schedule' && !empty($_POST['schedule_date'])) {
            $is_scheduled = 1;
            $schedule_date = $conn->real_escape_string($_POST['schedule_date']);
        }
        
        // Handle file uploads
        $thumbnail_url = '';
        $banner_url = '';
        $video_path = '';
        
        // Upload thumbnail (required)
        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === 0) {
            $thumbnail_url = uploadFile($_FILES['thumbnail'], 'thumbnails/', $title, 'thumbnail');
            if (empty($thumbnail_url)) {
                echo json_encode([
                    'success' => false,
                    'message' => "Error uploading thumbnail file",
                    'type' => 'danger'
                ]);
                exit;
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => "Thumbnail is required",
                'type' => 'danger'
            ]);
            exit;
        }
        
        // Upload banner (optional)
        if (isset($_FILES['banner']) && $_FILES['banner']['error'] === 0) {
            $banner_url = uploadFile($_FILES['banner'], 'banners/', $title, 'banner');
        }
        
        // Upload video (for movies)
        if ($content_type === 'movie' && isset($_FILES['video']) && $_FILES['video']['error'] === 0) {
            $video_path = uploadFile($_FILES['video'], 'videos/', $title, 'video');
        }
        
        // Insert content into database
    $sql = "INSERT INTO content (title, description, content_type, release_year, thumbnail_url, banner_url, video_path, rating, featured, is_premium, views, duration, is_scheduled, schedule_date) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            echo json_encode([
                'success' => false,
                'message' => "Database prepare error: " . $conn->error,
                'type' => 'danger'
            ]);
            exit;
        }
        
        // Convert integers to strings for bind_param
        $featured_str = (string)$featured;
        $is_premium_str = (string)$is_premium;
        $duration_str = (string)$duration;
        $is_scheduled_str = (string)$is_scheduled;
        
$stmt->bind_param("sssisssdsssss", 
    $title, 
    $description, 
    $content_type, 
    $release_year, 
    $thumbnail_url, 
    $banner_url, 
    $video_path, 
    $rating, 
    $featured_str, 
    $is_premium_str,
    $duration_str, 
    $is_scheduled_str, 
    $schedule_date
);        if ($stmt->execute()) {
            $content_id = $stmt->insert_id;
            
            // Handle categories
            if (isset($_POST['categories']) && is_array($_POST['categories'])) {
                foreach ($_POST['categories'] as $category_id) {
                    $category_id = intval($category_id);
                    $conn->query("INSERT INTO content_categories (content_id, category_id) VALUES ($content_id, $category_id)");
                }
            }
            
            // Handle episodes for TV shows
            if ($content_type === 'tv_show' && isset($_POST['episode_titles']) && is_array($_POST['episode_titles'])) {
                $episode_titles = $_POST['episode_titles'];
                $episode_count = count($episode_titles);
                
                for ($i = 0; $i < $episode_count; $i++) {
                    $episode_title = $conn->real_escape_string(trim($episode_titles[$i]));
                    $episode_duration = isset($_POST['episode_durations'][$i]) ? intval($_POST['episode_durations'][$i]) : 0;
                    $episode_date = isset($_POST['episode_dates'][$i]) && !empty($_POST['episode_dates'][$i]) ? $conn->real_escape_string($_POST['episode_dates'][$i]) : null;
                    $episode_description = isset($_POST['episode_descriptions'][$i]) ? $conn->real_escape_string(trim($_POST['episode_descriptions'][$i])) : '';
                    
                    // Handle episode video upload
                    $episode_video_path = '';
                    if (isset($_FILES['episode_videos']['name'][$i]) && $_FILES['episode_videos']['error'][$i] === 0) {
                        $episode_video = [
                            'name' => $_FILES['episode_videos']['name'][$i],
                            'type' => $_FILES['episode_videos']['type'][$i],
                            'tmp_name' => $_FILES['episode_videos']['tmp_name'][$i],
                            'error' => $_FILES['episode_videos']['error'][$i],
                            'size' => $_FILES['episode_videos']['size'][$i]
                        ];
                        $episode_video_path = uploadFile($episode_video, 'videos/', $title . '_ep' . ($i + 1), 'episode');
                    }
                    
                    $episode_sql = "INSERT INTO episodes (content_id, episode_number, title, description, duration_minutes, video_path, release_date) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?)";
                    
                    $episode_stmt = $conn->prepare($episode_sql);
                    if ($episode_stmt) {
                        // Convert integers to strings for episode bind_param
                        $episode_duration_str = (string)$episode_duration;
                        $episode_no = $i + 1;
                        $episode_stmt->bind_param("iississ", 
                            $content_id, 
                            $episode_no,
                            $episode_title, 
                            $episode_description, 
                            $episode_duration_str, 
                            $episode_video_path, 
                            $episode_date
                        );
                        $episode_stmt->execute();
                        $episode_stmt->close();
                    }
                }
            }
            
            // Log the activity
            $admin_id = 1; // Assuming admin user ID is 1
            $conn->query("INSERT INTO activity_logs (user_id, action, details, ip_address, user_agent) 
                         VALUES ($admin_id, 'content_added', '{\"content_id\":\"$content_id\",\"title\":\"$title\",\"content_type\":\"$content_type\"}', '127.0.0.1', 'Admin Panel')");
            
            echo json_encode([
                'success' => true,
                'message' => "Content '$title' added successfully!",
                'type' => 'success'
            ]);
            
        } else {
            echo json_encode([
                'success' => false,
                'message' => "Database execute error: " . $stmt->error,
                'type' => 'danger'
            ]);
        }
        $stmt->close();
        
    } else {
        echo json_encode([
            'success' => false,
            'message' => "Invalid action or no action specified",
            'type' => 'danger'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => "Invalid request method",
        'type' => 'danger'
    ]);
}

// File upload function (keep this function as is)
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
        $file_name = $content_title_clean . '_thumb.' . $file_extension;
    } elseif ($file_type === 'banner') {
        $file_name = $content_title_clean . '_banner.' . $file_extension;
    } elseif ($file_type === 'video') {
        $file_name = $content_title_clean . '_video.' . $file_extension;
    } elseif ($file_type === 'episode') {
        $file_name = $content_title_clean . '_episode.' . $file_extension;
    } else {
        $file_name = $content_title_clean . '.' . $file_extension;
    }
    
    $target_file = $target_dir . "." . $file_name;
    
    // Check if file already exists and generate unique name
    $counter = 1;
    while (file_exists($target_file)) {
        $file_name = $content_title_clean . '_' . $counter . '.' . $file_extension;
        $target_file = $target_dir . "." . $file_name;
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