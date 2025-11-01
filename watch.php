<?php
include "db.php";
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

// Get content ID from GET
$content_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$content = null;
$categories = [];
$episodes = [];
$in_watchlist = false;
$watch_progress = 0;
$is_scheduled = false;
$time_until_release = null;
$can_watch_video = false;
// Ensure this is always defined to avoid undefined variable notices when user is not logged in
$is_premium_user = false;

//get user details
if($user_id) {
    $user_id = intval($user_id);
    $user_stmt = $conn->prepare('SELECT is_premium FROM users WHERE user_id = ?');
    $user_stmt->bind_param('i', $user_id);
    $user_stmt->execute();
    $is_premium_user = $user_stmt->get_result()->fetch_assoc()['is_premium'];
}

if ($content_id > 0) {
    // Fetch content
    $stmt = $conn->prepare("SELECT * FROM content WHERE content_id = ?");
    $stmt->bind_param('i', $content_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $content = $result->fetch_assoc();
    $stmt->close();

    if ($content) {
        // Check if content is scheduled AND schedule date is in the future
        $current_time = time();
        $schedule_timestamp = !empty($content['schedule_date']) ? strtotime($content['schedule_date']) : 0;
        $is_scheduled = $content['is_scheduled'] && $schedule_timestamp > $current_time;
        
        if ($is_scheduled) {
            $schedule_date = new DateTime($content['schedule_date']);
            $current_date = new DateTime();
            $time_until_release = $current_date->diff($schedule_date);
        } else {
            // If schedule date has passed, update the database to mark it as no longer scheduled
            if ($content['is_scheduled'] && $schedule_timestamp <= $current_time) {
                $update_stmt = $conn->prepare("UPDATE content SET is_scheduled = 0 WHERE content_id = ?");
                $update_stmt->bind_param('i', $content_id);
                $update_stmt->execute();
                $update_stmt->close();
                
                // Refresh the content data
                $content['is_scheduled'] = 0;
                $is_scheduled = false;
            }
            
            // Increment view count only for available content that's not scheduled
            if (!$is_scheduled) {
                $conn->query("UPDATE content SET views = views + 1 WHERE content_id = $content_id");
            }
        }

        // Determine if user can watch the video
        $can_watch_video = !$is_scheduled && $user_id && (!$content['is_premium'] || $is_premium_user);

        // Use local video files from database with  prefix for images
        $thumbnail = 'admin/' . $content['thumbnail_url'];
        $banner = 'admin/' . $content['banner_url'];
        $video =   $content['video_path'];
        echo "<script>console.log('Video Path: " . addslashes($video) . "');</script>"; 

        // Fetch categories
        $cat_stmt = $conn->prepare("SELECT cat.name, cat.category_id FROM categories cat JOIN content_categories cc ON cc.category_id = cat.category_id WHERE cc.content_id = ?");
        $cat_stmt->bind_param('i', $content_id);
        $cat_stmt->execute();
        $cat_result = $cat_stmt->get_result();
        $category_ids = [];
        while ($row = $cat_result->fetch_assoc()) {
            $categories[] = $row['name'];
            $category_ids[] = $row['category_id'];
        }
        $cat_stmt->close();

        // Fetch episodes if tv_show
        if ($content['content_type'] === 'tv_show') {
            $ep_stmt = $conn->prepare("SELECT * FROM episodes WHERE content_id = ? ORDER BY episode_number ASC");
            $ep_stmt->bind_param('i', $content_id);
            $ep_stmt->execute();
            $ep_result = $ep_stmt->get_result();
            while ($row = $ep_result->fetch_assoc()) {
                $episodes[] = $row;
            }
            $ep_stmt->close();
        }
        
        // Check if in watchlist
        if ($user_id) {
            $watchlist_stmt = $conn->prepare("SELECT 1 FROM watchlist WHERE user_id = ? AND content_id = ?");
            $watchlist_stmt->bind_param('ii', $user_id, $content_id);
            $watchlist_stmt->execute();
            $in_watchlist = $watchlist_stmt->get_result()->num_rows > 0;
            $watchlist_stmt->close();
            
            // Get watch progress (only for available content)
            if (!$is_scheduled) {
                $progress_stmt = $conn->prepare("SELECT progress_percent FROM watch_history WHERE user_id = ? AND content_id = ?");
                $progress_stmt->bind_param('ii', $user_id, $content_id);
                $progress_stmt->execute();
                $progress_result = $progress_stmt->get_result();
                if ($progress_result->num_rows > 0) {
                    $watch_progress = $progress_result->fetch_assoc()['progress_percent'];
                }
                $progress_stmt->close();
            }
        }
    }
}

// Fetch comments and ratings data (only for available content)
$comments = [];
$avg_rating = null;
$user_rating = null;
$total_ratings = 0;

if ($content_id > 0 && !$is_scheduled) {
    // First query: Get average rating and total count
    $rating_stmt = $conn->prepare("
        SELECT 
            AVG(rating) AS avg_rating,
            COUNT(*) AS total_ratings
        FROM feedback
        WHERE content_id = ?
    ");
    $rating_stmt->bind_param('i', $content_id);
    $rating_stmt->execute();
    $rating_result = $rating_stmt->get_result();
    
    if ($rating_row = $rating_result->fetch_assoc()) {
        $avg_rating = $rating_row['avg_rating'] ? round($rating_row['avg_rating'], 1) : 'N/A';
        $total_ratings = $rating_row['total_ratings'];
    }
    $rating_stmt->close();

    // Second query: Get comments with user details
    $comment_stmt = $conn->prepare("
        SELECT 
            f.feedback_id,
            f.review_text AS comment,
            f.rating,
            f.created_at AS date,
            u.username,
            u.profile_pic
        FROM feedback f
        JOIN users u ON f.user_id = u.user_id
        WHERE f.content_id = ?
        ORDER BY f.created_at DESC
        LIMIT 10
    ");
    $comment_stmt->bind_param('i', $content_id);
    $comment_stmt->execute();
    $comment_result = $comment_stmt->get_result();
    
    while ($row = $comment_result->fetch_assoc()) {
        $comments[] = [
            'review_id' => $row['feedback_id'],
            'comment' => $row['comment'],
            'rating' => $row['rating'],
            'date' => $row['date'],
            'username' => $row['username'],
            'profile_pic' => $row['profile_pic']
        ];
    }
    $comment_stmt->close();

    // Fetch user's specific rating if logged in
    if ($user_id) {
        $stmt = $conn->prepare("SELECT rating FROM feedback WHERE content_id = ? AND user_id = ?");
        $stmt->bind_param('ii', $content_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_rating = $result->fetch_assoc()['rating'] ?? null;
        $stmt->close();
    }
}

// More like this
if (!empty($category_ids)) {
    $related_sql = "
        SELECT 
            c.content_id,
            c.title,
            CONCAT('admin/', c.thumbnail_url) AS thumbnail_url,
            CONCAT('admin/', c.banner_url) AS banner_url,
            release_year,
            c.rating,
            c.content_type,
            c.is_premium,
            c.is_scheduled,
            c.schedule_date
        FROM content c
        JOIN content_categories cc ON cc.content_id = c.content_id
        WHERE cc.category_id IN (".implode(',', $category_ids).")
        AND c.content_id != ?
        GROUP BY c.content_id
        ORDER BY c.views DESC
        LIMIT 6
    ";
    
    $related_stmt = $conn->prepare($related_sql);
    $related_stmt->bind_param('i', $content_id);
    $related_stmt->execute();
    $related_result = $related_stmt->get_result();
    $related_content = $related_result->fetch_all(MYSQLI_ASSOC);
    $related_stmt->close();
}
// Determine video container state
$video_state = 'available';
if ($is_scheduled) {
    $video_state = 'scheduled';
} elseif ($content['is_premium'] && !$is_premium_user) {
    $video_state = 'premium_locked';
} elseif (!$user_id) {
    $video_state = 'login_required';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $content ? htmlspecialchars($content['title']) : 'Content Not Found' ?> | Streamify</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
    <style>
    :root {
        --streamify-primary: #b13bff;
        --streamify-secondary: #00ccff;
        --streamify-dark: #1c0f24;
        --streamify-light: #f8f9fa;
        --streamify-text: #e2e2e2;
        --streamify-text-muted: #a0a0a0;
        --streamify-gradient: linear-gradient(135deg, var(--streamify-primary), var(--streamify-secondary));
        --streamify-card-bg: rgba(255, 255, 255, 0.05);
        --streamify-card-border: rgba(255, 255, 255, 0.1);
    }
    
    body {
        background: linear-gradient(135deg, var(--streamify-dark) 0%, #2a1b3d 100%);
        color: var(--streamify-text);
        min-height: 100vh;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }
    
    /* Header Styles */
    .navbar {
        background: rgba(28, 15, 36, 0.95) !important;
        backdrop-filter: blur(10px);
        border-bottom: 1px solid rgba(177, 59, 255, 0.2);
    }
    
    .navbar-brand {
        font-weight: 800;
        background: var(--streamify-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .nav-link {
        color: var(--streamify-text) !important;
        font-weight: 500;
        transition: color 0.3s ease;
    }
    
    .nav-link:hover {
        color: var(--streamify-primary) !important;
    }
    
    .nav-link.active {
        color: var(--streamify-primary) !important;
        font-weight: 600;
    }
    
    /* Hero Section */
    .hero-section {
        background: linear-gradient(135deg, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.75) 100%), 
                    url('<?= htmlspecialchars($banner ?? $content["banner_url"] ?? "https://images.unsplash.com/photo-1489599102910-59206b8ca314?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1770&q=80") ?>');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        padding: 3rem 0;
        position: relative;
        overflow: hidden;
        min-height: 85vh;
        display: flex;
        align-items: center;
    }
    
    .hero-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: radial-gradient(circle at 75% 30%, rgba(120, 119, 198, 0.3) 0%, transparent 50%);
        pointer-events: none;
    }
    
    .hero-content {
        position: relative;
        z-index: 2;
    }
    
    .hero-poster {
        position: relative;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
        transform: perspective(1000px) rotateY(-10deg);
        transition: transform 0.5s ease;
        max-width: 350px;
        margin: 0 auto;
    }
    
    .hero-poster:hover {
        transform: perspective(1000px) rotateY(0);
    }
    
    .hero-poster img {
        width: 100%;
        display: block;
    }
    
    .hero-title {
        font-size: 3.5rem;
        font-weight: 800;
        margin-bottom: 1.5rem;
        background: linear-gradient(to right, #fff, #aaa);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
    }
    
    .badge-custom {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        color: #fff;
        font-weight: 500;
        padding: 0.5rem 1rem;
        border-radius: 50px;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .btn-streamify {
        background: var(--streamify-gradient);
        border: none;
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 50px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-streamify:hover {
        background: linear-gradient(45deg, #9d00ff, #00a8e6);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(177, 59, 255, 0.3);
    }
    
    .btn-outline-streamify {
        background: transparent;
        border: 2px solid rgba(255, 255, 255, 0.3);
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 50px;
        font-weight: 600;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
    }
    
    .btn-outline-streamify:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: rgba(255, 255, 255, 0.5);
        transform: translateY(-2px);
    }
    
    /* Countdown Timer */
    .countdown-timer {
        background: linear-gradient(135deg, rgba(177, 59, 255, 0.2), rgba(0, 204, 255, 0.2));
        border: 2px solid var(--streamify-primary);
        border-radius: 15px;
        padding: 2rem;
        text-align: center;
        margin: 2rem 0;
        backdrop-filter: blur(10px);
    }
    
    .countdown-title {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
        color: var(--streamify-text);
    }
    
    .countdown-display {
        display: flex;
        justify-content: center;
        gap: 1rem;
        margin-bottom: 1rem;
    }
    
    .countdown-item {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        padding: 1rem;
        min-width: 80px;
        backdrop-filter: blur(10px);
    }
    
    .countdown-number {
        font-size: 2rem;
        font-weight: 800;
        color: var(--streamify-primary);
        display: block;
    }
    
    .countdown-label {
        font-size: 0.8rem;
        color: var(--streamify-text-muted);
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .premium-feature-badge {
        background: linear-gradient(45deg, #ffd700, #ff6b00);
        color: #1c0f24;
        padding: 0.5rem 1rem;
        border-radius: 25px;
        font-weight: 700;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        box-shadow: 0 4px 15px rgba(255, 215, 0, 0.4);
        border: 1px solid rgba(255, 255, 255, 0.3);
        animation: shimmer 3s infinite;
    }
    
 /* Video Container */
.video-container {
    background: #000;
    border-radius: 15px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.5);
    position: relative;
    max-width: 100%;
    overflow: hidden;
    min-height: 400px;
}

.video-container .ratio {
    aspect-ratio: 16 / 9;
    width: 100%;
    position: relative;
}

.video-container video {
    width: 100%;
    height: 100%;
    object-fit: contain;
    display: block;
}

.plyr {
    border-radius: 15px;
    overflow: hidden;
    width: 100%;
    height: 100%;
}

/* Video States - Only background styling, no min-height */
.video-container.scheduled-content {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
}

.video-container.premium-locked {
    background: linear-gradient(135deg, rgba(255, 193, 7, 0.1), rgba(255, 107, 0, 0.1));
}

.video-container.login-required {
    background: linear-gradient(135deg, rgba(13, 202, 240, 0.1), rgba(111, 66, 193, 0.1));
}

/* Video Overlay - Fixed positioning */
.video-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    padding: 2rem;
    z-index: 20;
    border-radius: 15px;
}

.video-overlay.scheduled {
    background: rgba(28, 15, 36, 0.95);
    backdrop-filter: blur(10px);
}

.video-overlay.premium {
    background: rgba(28, 15, 36, 0.95);
    backdrop-filter: blur(10px);
}

.video-overlay.login {
    background: rgba(28, 15, 36, 0.85);
    backdrop-filter: blur(5px);
    cursor: pointer;
    transition: background 0.3s ease;
}

.video-overlay.login:hover {
    background: rgba(28, 15, 36, 0.8);
}

/* Ensure video is always visible when it should be */
.video-container:not(.scheduled-content):not(.premium-locked) .ratio {
    display: block !important;
}

.video-container:not(.scheduled-content):not(.premium-locked) video {
    display: block !important;
}

.video-container:not(.scheduled-content):not(.premium-locked) .plyr {
    display: block !important;
}

/* Hide overlay when video should be fully visible */
.video-container.available-content .video-overlay {
    display: none !important;
}
    .progress-bar {
        height: 6px;
        background: rgba(255,255,255,0.2);
        border-radius: 3px;
        overflow: hidden;
        margin-bottom: 1rem;
        width: 100%;
    }
    
    .progress-bar .progress {
        height: 100%;
        background: var(--streamify-gradient);
        border-radius: 3px;
        width: <?= $watch_progress ?>%;
        transition: width 0.3s ease;
    }
    
    /* Content Cards */
    .content-card {
        background: var(--streamify-card-bg);
        border: 1px solid var(--streamify-card-border);
        border-radius: 15px;
        backdrop-filter: blur(10px);
        transition: all 0.3s ease;
    }
    
    .content-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.3);
        border-color: rgba(177, 59, 255, 0.3);
    }
    
    /* Scroll Container */
    .scroll-container {
        display: flex;
        overflow-x: auto;
        padding: 1rem 0.5rem;
        scrollbar-width: thin;
        scrollbar-color: var(--streamify-primary) rgba(255, 255, 255, 0.1);
        gap: 1rem;
        margin-bottom: 2rem;
        scroll-behavior: smooth;
    }
    
    .scroll-container::-webkit-scrollbar {
        height: 8px;
    }
    
    .scroll-container::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
    }
    
    .scroll-container::-webkit-scrollbar-thumb {
        background: var(--streamify-primary);
        border-radius: 10px;
    }
    
    .scroll-item {
        flex: 0 0 auto;
        width: 250px;
        transition: transform 0.3s ease;
    }
    
    .scroll-item:hover {
        transform: translateY(-5px);
    }
    
    /* Movie Cards */
    .movie-card {
        background: var(--streamify-card-bg);
        border: 1px solid var(--streamify-card-border);
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.3s ease;
        height: 100%;
        cursor: pointer;
        position: relative;
    }
    
    .movie-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        border-color: var(--streamify-primary);
    }
    
    .movie-card img {
        width: 100%;
        height: 300px;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    
    .movie-card:hover img {
        transform: scale(1.05);
    }
    
    .card-badge {
        position: absolute;
        top: 10px;
        left: 10px;
        background: var(--streamify-gradient);
        color: white;
        padding: 0.3rem 0.8rem;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 600;
        z-index: 2;
    }
    
    .premium-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background: linear-gradient(45deg, #ffc107, #ff9800);
        color: #212529;
        padding: 0.3rem 0.8rem;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 600;
        z-index: 2;
    }
    
    .scheduled-badge {
        position: absolute;
        top: 10px;
        left: 10px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        padding: 0.3rem 0.8rem;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 600;
        z-index: 2;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .card-title {
        color: var(--streamify-text);
        font-weight: 600;
        margin-bottom: 0.5rem;
        font-size: 1rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .card-text {
        color: var(--streamify-text-muted);
        font-size: 0.85rem;
        margin-bottom: 0;
    }
    
    /* Episode Items */
    .episode-item {
        background: var(--streamify-card-bg);
        border: 1px solid var(--streamify-card-border);
        border-radius: 10px;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .episode-item:hover {
        background: rgba(177, 59, 255, 0.1);
        border-color: var(--streamify-primary);
        transform: translateX(5px);
    }
    
    .episode-item.active {
        background: linear-gradient(135deg, rgba(177, 59, 255, 0.2), rgba(0, 204, 255, 0.2));
        border-color: var(--streamify-primary);
    }
    
    /* Comments */
    .comment-avatar {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--streamify-primary);
    }
    
    .star-rating {
        display: flex;
        gap: 3px;
    }
    
    .star-rating i {
        font-size: 1.5rem;
        color: #fd7e14;
    }
    
    .star-rating .star {
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .star-rating .star:hover {
        transform: scale(1.2);
    }
    
    /* Section Titles */
    .section-title {
        color: var(--streamify-text);
        font-size: 1.8rem;
        margin: 3rem 0 1.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid var(--streamify-primary);
        display: inline-block;
        font-weight: 700;
    }
    
    /* Animations */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .badge-custom {
        animation: fadeIn 0.5s ease forwards;
        opacity: 0;
    }
    
    .badge-custom:nth-child(1) { animation-delay: 0.1s; }
    .badge-custom:nth-child(2) { animation-delay: 0.2s; }
    .badge-custom:nth-child(3) { animation-delay: 0.3s; }
    
    @keyframes slideInFromLeft {
        from { opacity: 0; transform: translateX(-30px); }
        to { opacity: 1; transform: translateX(0); }
    }
    
    .hero-title {
        animation: slideInFromLeft 0.8s ease forwards;
    }
    
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .action-buttons .btn {
        animation: fadeInUp 0.8s ease forwards;
        opacity: 0;
    }
    
    .action-buttons .btn:nth-child(1) { animation-delay: 0.4s; }
    .action-buttons .btn:nth-child(2) { animation-delay: 0.5s; }
    
    @keyframes shimmer {
        0%, 100% {
            background: linear-gradient(45deg, #ffd700, #ff6b00);
            box-shadow: 0 4px 15px rgba(255, 215, 0, 0.4);
        }
        50% {
            background: linear-gradient(45deg, #ffed4e, #ff8533);
            box-shadow: 0 6px 20px rgba(255, 215, 0, 0.6);
        }
    }
    
    /* Toast Notifications */
    .custom-toast {
        background: linear-gradient(135deg, var(--streamify-primary), var(--streamify-secondary));
        border: none;
        border-radius: 10px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }
    
    /* Responsive Design */
    @media (max-width: 992px) {
        .hero-title {
            font-size: 2.5rem;
        }
        
        .hero-poster {
            max-width: 280px;
            margin-bottom: 2rem;
        }
        
        .countdown-display {
            gap: 0.5rem;
        }
        
        .countdown-item {
            min-width: 60px;
            padding: 0.5rem;
        }
        
        .countdown-number {
            font-size: 1.5rem;
        }
    }
    
    @media (max-width: 768px) {
        .hero-title {
            font-size: 2rem;
        }
        
        .hero-poster {
            max-width: 240px;
            transform: none;
        }
        
        .action-buttons {
            justify-content: center;
        }
        
        .section-title {
            font-size: 1.5rem;
        }
        
        .hero-section {
            padding: 1rem 0;
        }
        
        .video-container {
            margin-bottom: 1rem;
            min-height: 300px;
        }
        
        .scroll-item {
            width: 220px;
        }
        
        .countdown-display {
            flex-wrap: wrap;
        }
    }
    
    @media (max-width: 576px) {
        .scroll-item {
            width: 180px;
        }
        
        .movie-card img {
            height: 120px;
        }
        
        .countdown-item {
            min-width: 50px;
            padding: 0.5rem;
        }
        
        .countdown-number {
            font-size: 1.2rem;
        }
        
        .video-container {
            min-height: 250px;
        }
    }
    /* Instagram share button styling */
    .insta-bg {
        background: linear-gradient(45deg, #f58529 0%, #dd2a7b 50%, #8134af 100%);
        color: #fff !important;
        border: none !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 6px 18px rgba(129,52,175,0.25);
        transition: transform 0.18s ease, filter 0.18s ease;
    }
    .insta-bg:hover {
        filter: brightness(0.95);
        transform: translateY(-2px);
    }
    </style>
</head>
<body class="pt-5">
    <?php include "header.php";?>
    
    <?php if (!$content): ?>
        <div class="container py-5">
            <div class="alert alert-danger">Content not found.</div>
        </div>
    <?php else: ?>
    
    <!-- Hero Section-->
    <section class="hero-section">
        <div class="container hero-content">
            <div class="row align-items-center">
                <div class="col-lg-4 col-md-5 mb-4 mb-md-0">
                    <div class="hero-poster">
                        <img src="<?= htmlspecialchars(
                            $thumbnail
                            ?? $content['thumbnail_url']
                            ?? 'https://images.unsplash.com/photo-1536440136628-849c177e76a1?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=950&q=80'
                        ) ?>" alt="<?= htmlspecialchars($content['title']) ?>">
                        <?php if ($is_scheduled): ?>
                            <div class="scheduled-badge">COMING SOON</div>
                        <?php elseif ($content['is_premium']): ?>
                            <div class="premium-badge">PREMIUM</div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-8 col-md-7">
                    <h1 class="hero-title"><?= htmlspecialchars($content['title']) ?></h1>
                    
                    <div class="hero-meta">
                        <?php foreach ($categories as $index => $cat): ?>
                            <span class="badge badge-custom" style="animation-delay: <?= $index * 0.1 + 0.1 ?>s"><?= htmlspecialchars($cat) ?></span>
                        <?php endforeach; ?>
                        
                        <span class="text-light stat-item"><i class="bi bi-calendar"></i> <?= htmlspecialchars($content['release_year']) ?></span>
                        
                        <?php if ($content['content_type'] === 'movie'): ?>
                            <span class="text-light stat-item"><i class="bi bi-film"></i> Movie</span>
                            <?php if (!empty($content['duration'])): ?>
                                <?php
                                $minutes = (int)$content['duration'];
                                $hours = floor($minutes / 60);
                                $mins = $minutes % 60;
                                ?>
                                <span class="text-light stat-item">
                                    <i class="bi bi-clock"></i> 
                                    <?= $hours ?>h <?= $mins ?>m
                                </span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-light stat-item"><i class="bi bi-tv"></i> TV Show</span>
                            <span class="text-light stat-item"><i class="bi bi-list-ol"></i> <?= count($episodes) ?> Episodes</span>
                        <?php endif; ?>
                    </div>
                    
                    <p class="hero-description"><?= htmlspecialchars($content['description']) ?></p>
                    
                    <?php if ($is_scheduled): ?>
                        <!-- Countdown Timer -->
                        <div class="countdown-timer">
                            <h3 class="countdown-title"><i class="bi bi-clock"></i> Coming Soon</h3>
                            <div class="countdown-display">
                                <div class="countdown-item">
                                    <span class="countdown-number" id="countdown-days">00</span>
                                    <span class="countdown-label">Days</span>
                                </div>
                                <div class="countdown-item">
                                    <span class="countdown-number" id="countdown-hours">00</span>
                                    <span class="countdown-label">Hours</span>
                                </div>
                                <div class="countdown-item">
                                    <span class="countdown-number" id="countdown-minutes">00</span>
                                    <span class="countdown-label">Minutes</span>
                                </div>
                                <div class="countdown-item">
                                    <span class="countdown-number" id="countdown-seconds">00</span>
                                    <span class="countdown-label">Seconds</span>
                                </div>
                            </div>
                            <p class="text-light mb-0">Releasing on <?= date('F j, Y g:i A', strtotime($content['schedule_date'])) ?></p>
                        </div>
                    <?php else: ?>
                        <div class="hero-info-container">
                            <div class="rating-container">
                                <div class="star-rating" aria-label="Average rating" title="Average rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="bi bi-star<?= (is_numeric($avg_rating) && $i <= round($avg_rating)) ? '-fill text-warning' : '' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span id="avgRating" class="fw-bold text-warning"><?= $avg_rating ?></span>
                                    <small class="text-light ms-1">(<?= $total_ratings ?> ratings)</small>
                                </div>
                            </div>
                            
                            <div class="hero-stats">
                                <span class="text-light stat-item"><i class="bi bi-eye"></i> <?= number_format($content['views']) ?> views</span>
                                <?php if ($content['content_type'] === 'tv_show'): ?>
                                    <span class="text-light stat-item"><i class="bi bi-collection-play"></i> <?= count($episodes) ?> episodes</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="action-buttons">
                        <!-- Watchlist Button -->
                        <button id="watchlistBtn" class="btn <?= $user_id ? ($in_watchlist ? 'btn-streamify' : 'btn-outline-streamify') : 'btn-outline-streamify' ?>" 
                                <?= !$user_id ? 'data-bs-toggle="modal" data-bs-target="#loginModal"' : '' ?>>
                            <i class="bi bi-<?= $user_id ? ($in_watchlist ? 'bookmark-check-fill' : 'bookmark-plus') : 'bookmark-plus' ?>"></i> 
                            <?= $user_id ? ($in_watchlist ? 'In Watchlist' : 'Add to Watchlist') : 'Add to Watchlist' ?>
                        </button>
                        
                        <!-- Download Button (Premium Feature) -->
                        <?php if (!$is_scheduled): ?>
                            <button class="btn <?= $is_premium_user ? 'btn-streamify' : 'btn-outline-streamify' ?>" 
                                    id="downloadBtn"
                                    <?= !$is_premium_user ? 'data-bs-toggle="modal" data-bs-target="#premiumModal"' : '' ?>>
                                <i class="bi bi-download"></i> 
                                <?= $is_premium_user ? 'Download' : 'Download' ?>
                            </button>
                        <?php endif; ?>
                        
                        <!-- Share Button -->
                        <button class="btn btn-outline-streamify" data-bs-toggle="modal" data-bs-target="#shareModal">
                            <i class="bi bi-share"></i> Share
                        </button>
                        
                        <!-- Remind Me Button for Scheduled Content -->
                        <?php if ($is_scheduled && $user_id): ?>
                            <button class="btn btn-outline-streamify" id="remindMeBtn">
                                <i class="bi bi-bell"></i> Remind Me
                            </button>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($content['is_premium'] && !$is_premium_user && !$is_scheduled): ?>
                        <div class="mt-3">
                            <span class="premium-feature-badge">
                                <i class="bi bi-star-fill me-1"></i> Premium Content
                            </span>
                            <p class="text-warning mt-1">
                                <i class="bi bi-info-circle"></i> This is premium content. Upgrade to watch and download.
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content Area -->
    <div class="container py-5">
        <div class="row g-4">
            <!-- Main Content Column -->
            <div class="<?= ($content['content_type'] === 'tv_show' && !empty($episodes) && !$is_scheduled) ? 'col-lg-8' : 'col-12' ?>">
                <!-- Video Player Section -->
                <div class="video-container mb-4 <?= $video_state === 'scheduled' ? 'scheduled-content' : ($video_state === 'premium_locked' ? 'premium-locked' : ($video_state === 'login_required' ? 'login-required' : '')) ?>">
                    <?php if ($video_state === 'scheduled'): ?>
                        <!-- Scheduled Content - No Video Player -->
                        <div class="video-overlay scheduled">
                            <i class="bi bi-clock display-1 text-warning mb-3"></i>
                            <h3 class="text-warning mb-2">Coming Soon</h3>
                            <p class="text-light text-center mb-3">
                                This content will be available on<br>
                                <strong><?= date('F j, Y g:i A', strtotime($content['schedule_date'])) ?></strong>
                            </p>
                            <?php if ($user_id): ?>
                                <button class="btn btn-streamify" id="setReminderBtn">
                                    <i class="bi bi-bell me-2"></i>Set Reminder
                                </button>
                            <?php else: ?>
                                <button class="btn btn-streamify" data-bs-toggle="modal" data-bs-target="#loginModal">
                                    <i class="bi bi-bell me-2"></i>Login to Set Reminder
                                </button>
                            <?php endif; ?>
                        </div>

                    <?php elseif ($video_state === 'premium_locked'): ?>
                        <!-- Premium Content - No Video Player -->
                        <div class="video-overlay premium">
                            <i class="bi bi-star-fill display-1 text-warning mb-3"></i>
                            <h3 class="text-warning mb-2">Premium Content</h3>
                            <p class="text-light text-center mb-3">
                                Upgrade to premium to watch this exclusive content<br>
                                and enjoy all premium features
                            </p>
                            <button class="btn btn-streamify" data-bs-toggle="modal" data-bs-target="#premiumModal">
                                <i class="bi bi-rocket-takeoff me-2"></i>Upgrade to Premium
                            </button>
                            <p class="text-muted mt-3 small">
                                <i class="bi bi-info-circle me-1"></i>
                                Premium features include: 4K streaming, offline downloads, ad-free experience
                            </p>
                        </div>

                    <?php elseif ($video_state === 'login_required'): ?>
                        <!-- Login Required - Show Video Player but require login -->
                        <div class="ratio">
                            <video id="player" playsinline controls data-poster="<?= htmlspecialchars($banner) ?? $content["banner_url"] ?>">
                                <?php if ($content['content_type'] === 'movie' && $content['video_path']): ?>
                                    <source src="<?= "admin/" . htmlspecialchars($video) ?>" type="video/mp4">
                                <?php elseif ($content['content_type'] === 'tv_show'): ?>
                                    <?php if (!empty($episodes) && !empty($episodes[0]['video_path'])): ?>
                                        <source src="<?= "admin/" . htmlspecialchars($episodes[0]['video_path']) ?>" type="video/mp4">
                                    <?php elseif ($content['video_path']): ?>
                                        <!-- Fallback to content video_path if no episodes -->
                                        <source src="<?= "admin/" . htmlspecialchars($video) ?>" type="video/mp4">
                                    <?php endif; ?>
                                <?php endif; ?>
                            </video>
                        </div>
                        <div class="video-overlay login" onclick="handleGuestVideoClick(event)">
                            <i class="bi bi-play-circle display-1 text-primary mb-3"></i>
                            <h3 class="text-primary mb-2">Start Watching</h3>
                            <p class="text-light text-center mb-3">
                                Login to watch this video and track your progress
                            </p>
                            <button class="btn btn-streamify" data-bs-toggle="modal" data-bs-target="#loginModal" onclick="event.stopPropagation()">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Login to Watch
                            </button>
                            <p class="text-muted mt-2 small">Don't have an account? 
                                <a href="#" class="text-warning" data-bs-toggle="modal" data-bs-target="#loginModal" onclick="event.stopPropagation()">Sign up free</a>
                            </p>
                        </div>

                    <?php else: ?>
                        <!-- Available Content - Full Video Player -->
                        <?php if ($user_id): ?>
                            <div class="progress-bar">
                                <div class="progress"></div>
                            </div>
                        <?php endif; ?>
                        <div class="ratio">
                            <video id="player" playsinline controls data-poster="<?= htmlspecialchars($banner) ?? $content["banner_url"] ?>">
                                <?php if ($content['content_type'] === 'movie' && $content['video_path']): ?>
                                    <source src="<?= "admin/" . htmlspecialchars($video) ?>" type="video/mp4">
                                <?php elseif ($content['content_type'] === 'tv_show'): ?>
                                    <?php if (!empty($episodes) && !empty($episodes[0]['video_path'])): ?>
                                        <source src="<?= "admin/" . htmlspecialchars($episodes[0]['video_path']) ?>" type="video/mp4">
                                    <?php elseif ($content['video_path']): ?>
                                        <!-- Fallback to content video_path if no episodes -->
                                        <source src="<?= "admin/" . htmlspecialchars($video) ?>" type="video/mp4">
                                    <?php endif; ?>
                                <?php endif; ?>
                            </video>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- About Section -->
                <div class="content-card mb-4 w-100 p-3">
                    <div class="card-body">
                        <h3 class="section-title"><i class="bi bi-info-circle"></i> About</h3>
                        <p class="text-light"><?= htmlspecialchars($content['description']) ?></p>
                    </div>
                </div>

                <!-- Details Section -->
                <div class="content-card mb-4 w-100 p-3">
                    <div class="card-body">
                        <h4 class="section-title"><i class="bi bi-info-circle"></i> Details</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="text-light"><strong><i class="bi bi-calendar text-warning"></i> Release Year:</strong> <?= htmlspecialchars($content['release_year']) ?></p>
                                <p class="text-light"><strong><i class="bi bi-collection text-warning"></i> Type:</strong> <?= $content['content_type'] === 'movie' ? 'Movie' : 'TV Show' ?></p>
                                <?php if ($content['content_type'] === 'movie'): ?>
                                    <p class="text-light"><strong><i class="bi bi-clock text-warning"></i> Duration:</strong> 120 min</p>
                                <?php else: ?>
                                    <p class="text-light"><strong><i class="bi bi-list-ol text-warning"></i> Episodes:</strong> <?= count($episodes) ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <p class="text-light"><strong><i class="bi bi-star text-warning"></i> Rating:</strong> <?= $content['rating'] ?>/10</p>
                                <p class="text-light"><strong><i class="bi bi-eye text-warning"></i> Views:</strong> <?= number_format($content['views']) ?></p>
                                <p class="text-light"><strong><i class="bi bi-tags text-warning"></i> Categories:</strong> <?= implode(', ', $categories) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Episodes Sidebar -->
            <?php if ($content['content_type'] === 'tv_show' && !empty($episodes) && !$is_scheduled): ?>
                <div class="col-lg-4">
                    <div class="content-card p-3">
                        <h4 class="section-title"><i class="bi bi-list-ol"></i> Episodes</h4>
                        <div class="episodes-list" style="max-height: 500px; overflow-y: auto;">
                            <?php foreach ($episodes as $index => $episode): ?>
                                <div class="episode-item p-3 mb-2 <?= $index === 0 ? 'active' : '' ?>" 
                                     data-episode-id="<?= $episode['episode_id'] ?>" 
                                    data-video-src="<?= !empty($episode['video_path']) ? 'admin/' . htmlspecialchars($episode['video_path']) : 'admin/videos/default.mp4' ?>">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1 text-light">Episode <?= $episode['episode_number'] ?></h6>
                                            <p class="mb-0 small text-light opacity-75"><?= htmlspecialchars($episode['title']) ?></p>
                                        </div>
                                        <span class="badge bg-secondary"><?= $episode['duration_minutes'] ?>m</span>
                                    </div>
                                    <?php if ($is_premium_user): ?>
                                        <div class="mt-2">
                                            <button class="btn btn-sm btn-outline-light download-episode" 
                                                    data-episode-id="<?= $episode['episode_id'] ?>"
                                                    data-episode-title="<?= htmlspecialchars($episode['title']) ?>">
                                                <i class="bi bi-download"></i> Download
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
   <!-- Comments & Ratings Section -->
<?php if (!$is_scheduled): ?>
    <div class="row mt-4">
        <div class="col-12">
            <div class="content-card p-4">
                <h3 class="section-title"><i class="bi bi-chat-left-text"></i> Comments & Ratings</h3>
                
                <!-- Overall Rating Summary -->
                <div class="rating-summary mb-4 p-3 bg-dark rounded">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-3">
                                <div class="text-center">
                                    <h2 class="text-warning mb-0"><?= is_numeric($avg_rating) ? $avg_rating : '0.0' ?></h2>
                                    <div class="star-rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="bi bi-star<?= (is_numeric($avg_rating) && $i <= round($avg_rating)) ? '-fill text-warning' : '' ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <small class="text-muted"><?= $total_ratings ?> ratings</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="rating-bars">
                                <?php
                                // Calculate rating distribution
                                $rating_distribution = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
                                if (!empty($comments)) {
                                    foreach ($comments as $comment) {
                                        if (isset($comment['rating']) && isset($rating_distribution[$comment['rating']])) {
                                            $rating_distribution[$comment['rating']]++;
                                        }
                                    }
                                }
                                ?>
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <?php
                                    $percentage = $total_ratings > 0 ? ($rating_distribution[$i] / $total_ratings) * 100 : 0;
                                    ?>
                                    <div class="d-flex align-items-center mb-1">
                                        <small class="text-muted me-2" style="width: 20px;"><?= $i ?></small>
                                        <i class="bi bi-star-fill text-warning me-2"></i>
                                        <div class="progress flex-grow-1 bg-secondary" style="height: 8px;">
                                            <div class="progress-bar bg-warning" style="width: <?= $percentage ?>%"></div>
                                        </div>
                                        <small class="text-muted ms-2" style="width: 40px;"><?= round($percentage) ?>%</small>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rating Form -->
                <?php if ($user_id): ?>
                    <div class="rating-form mb-4 p-3 bg-dark rounded">
                        <h5 class="text-light mb-3">Rate this content</h5>
                        <form id="commentForm" method="POST">
                            <input type="hidden" name="content_id" value="<?= $content_id ?>">
                            <input type="hidden" name="action" value="submit_rating">
                            
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="star-rating" id="commentStarRating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="bi comment-star <?= ($user_rating && $i <= $user_rating) ? 'bi-star-fill text-warning' : 'bi-star' ?>" 
                                           data-value="<?= $i ?>" style="cursor: pointer; font-size: 1.8rem;"></i>
                                    <?php endfor; ?>
                                </div>
                                <input type="hidden" id="commentRating" name="rating" value="<?= $user_rating ?: 0 ?>">
                                <span id="selectedRating" class="text-light fw-bold">
                                    <?= $user_rating ? "You rated: {$user_rating}/5" : 'Select your rating' ?>
                                </span>
                            </div>
                            
                            <div class="mb-3">
                                <textarea class="form-control bg-dark text-light border-secondary" 
                                          id="commentText" name="comment" 
                                          placeholder="Share your thoughts about this content..." 
                                          rows="4" required></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-streamify" id="submitReview">
                                <i class="bi bi-send"></i> Submit Review
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info d-flex align-items-center">
                        <i class="bi bi-info-circle me-2"></i>
                        Please <a href="#" class="alert-link fw-bold p-1" data-bs-toggle="modal" data-bs-target="#loginModal"> login </a> to rate and comment.
                    </div>
                <?php endif; ?>
                
                <!-- Comments List -->
                <div id="commentsContainer">
                    <?php if (empty($comments)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-chat-square-text display-4 text-muted mb-3"></i>
                            <p class="text-muted">No comments yet. Be the first to share your thoughts!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($comments as $comment): ?>
                            <div class="comment-item mb-4 p-3 bg-dark rounded">
                                <div class="d-flex align-items-start gap-3">
                                    <img src="<?= htmlspecialchars($comment['profile_pic'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($comment['username']) . '&background=random&color=fff') ?>" 
                                         alt="<?= htmlspecialchars($comment['username']) ?>" 
                                         class="comment-avatar">
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <h6 class="mb-0 text-light"><?= htmlspecialchars($comment['username']) ?></h6>
                                                <div class="star-rating">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="bi bi-star<?= $i <= $comment['rating'] ? '-fill text-warning' : '' ?> fs-6"></i>
                                                    <?php endfor; ?>
                                                </div>
                                            </div>
                                            <small class="text-muted"><?= date('M j, Y g:i A', strtotime($comment['date'])) ?></small>
                                        </div>
                                        <p class="text-light mb-0"><?= htmlspecialchars($comment['comment']) ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
                <!-- Separate Category Sections -->
        <?php if (!empty($categories)): ?>
            <?php foreach ($categories as $index => $category): ?>
                <?php
                // Get category ID for the current category name
                $cat_stmt = $conn->prepare("SELECT category_id FROM categories WHERE name = ?");
                $cat_stmt->bind_param('s', $category);
                $cat_stmt->execute();
                $cat_result = $cat_stmt->get_result();
                $current_category = $cat_result->fetch_assoc();
                $cat_stmt->close();
                
                if ($current_category) {
                    $current_category_id = $current_category['category_id'];
                    
                    // Fetch content from this specific category
                    $cat_content_sql = "
                        SELECT DISTINCT
                            c.content_id,
                            c.title,
                            CONCAT('admin/', c.thumbnail_url) AS thumbnail_url,
                            CONCAT('admin/', c.banner_url) AS banner_url,
                            c.release_year,
                            c.rating,
                            c.content_type,
                            c.is_premium,
                            c.is_scheduled,
                            c.schedule_date
                        FROM content c
                        JOIN content_categories cc ON cc.content_id = c.content_id
                        WHERE cc.category_id = ?
                        AND c.content_id != ?
                        AND c.is_scheduled = 0
                        GROUP BY c.content_id
                        ORDER BY c.views DESC
                        LIMIT 8
                    ";
                    
                    $cat_content_stmt = $conn->prepare($cat_content_sql);
                    $cat_content_stmt->bind_param('ii', $current_category_id, $content_id);
                    $cat_content_stmt->execute();
                    $cat_content_result = $cat_content_stmt->get_result();
                    $category_content = $cat_content_result->fetch_all(MYSQLI_ASSOC);
                    $cat_content_stmt->close();
                    
                    if (!empty($category_content)):
                ?>
                        <div class="row mt-4">
                            <div class="col-12">
                                <h3 class="section-title">
                                    <i class="bi bi-tag"></i> More <?= htmlspecialchars($category) ?> Content
                                </h3>
                                <div class="scroll-container">
                                    <?php foreach ($category_content as $item): ?>
                                        <div class="scroll-item">
                                            <div class="movie-card position-relative">
                                                <a href="watch.php?id=<?= $item['content_id'] ?>" class="text-decoration-none">
                                                    <img src="<?= htmlspecialchars($item['thumbnail_url']) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                                                    <div class="card-badge"><?= $item['content_type'] === 'movie' ? 'Movie' : 'TV Show' ?></div>
                                                    <?php if ($item['is_premium']): ?>
                                                        <div class="premium-badge">PREMIUM</div>
                                                    <?php endif; ?>
                                                    <div class="card-body">
                                                        <h6 class="card-title"><?= htmlspecialchars($item['title']) ?></h6>
                                                        <p class="card-text"><?= htmlspecialchars($item['release_year']) ?> • Rating: <?= $item['rating'] ?>/10</p>
                                                        <small class="text-muted"><?= htmlspecialchars($category) ?></small>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                <?php 
                    endif;
                }
                ?>
            <?php endforeach; ?>
        <?php endif; ?>
        <!-- More Like This Section -->
        <?php if (!empty($related_content)): ?>
            <div class="row mt-4">
                <div class="col-12">
                    <h3 class="section-title"><i class="bi bi-collection-play"></i> More Like This</h3>
                    <div class="scroll-container">
                        <?php foreach ($related_content as $item): ?>
                            <div class="scroll-item">
                                <div class="movie-card position-relative">
                                    <a href="watch.php?id=<?= $item['content_id'] ?>" class="text-decoration-none">
                                        <img src="<?= htmlspecialchars($item['thumbnail_url']) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                                        <?php if ($item['is_scheduled']): ?>
                                            <div class="scheduled-badge">COMING SOON</div>
                                        <?php else: ?>
                                            <div class="card-badge"><?= $item['content_type'] === 'movie' ? 'Movie' : 'TV Show' ?></div>
                                        <?php endif; ?>
                                        <?php if ($item['is_premium']): ?>
                                            <div class="premium-badge">PREMIUM</div>
                                        <?php endif; ?>
                                        <div class="card-body">
                                            <h6 class="card-title"><?= htmlspecialchars($item['title']) ?></h6>
                                            <p class="card-text"><?= htmlspecialchars($item['release_year']) ?> • Rating: <?= $item['rating'] ?>/10</p>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
                <!-- Recently Added Section -->
        <?php
        // Fetch recently added content
        $recent_stmt = $conn->prepare("
            SELECT 
                content_id,
                title,
                CONCAT('admin/', thumbnail_url) AS thumbnail_url,
                CONCAT('admin/', banner_url) AS banner_url,
                release_year,
                rating,
                content_type,
                is_premium,
                is_scheduled,
                schedule_date,
                created_at
            FROM content 
            WHERE is_scheduled = 0
            ORDER BY created_at DESC 
            LIMIT 10
        ");
        $recent_stmt->execute();
        $recent_result = $recent_stmt->get_result();
        $recent_content = $recent_result->fetch_all(MYSQLI_ASSOC);
        $recent_stmt->close();
        ?>
        
        <?php if (!empty($recent_content)): ?>
            <div class="row mt-4">
                <div class="col-12">
                    <h3 class="section-title"><i class="bi bi-clock"></i> Recently Added</h3>
                    <div class="scroll-container">
                        <?php foreach ($recent_content as $item): ?>
                            <div class="scroll-item">
                                <div class="movie-card position-relative">
                                    <a href="watch.php?id=<?= $item['content_id'] ?>" class="text-decoration-none">
                                        <img src="<?= htmlspecialchars($item['thumbnail_url']) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                                        <?php if ($item['is_scheduled']): ?>
                                            <div class="scheduled-badge">COMING SOON</div>
                                        <?php else: ?>
                                            <div class="card-badge"><?= $item['content_type'] === 'movie' ? 'Movie' : 'TV Show' ?></div>
                                        <?php endif; ?>
                                        <?php if ($item['is_premium']): ?>
                                            <div class="premium-badge">PREMIUM</div>
                                        <?php endif; ?>
                                        <div class="card-body">
                                            <h6 class="card-title"><?= htmlspecialchars($item['title']) ?></h6>
                                            <p class="card-text"><?= htmlspecialchars($item['release_year']) ?> • Rating: <?= $item['rating'] ?>/10</p>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Trending Now Section -->
        <?php
        // Fetch trending content (most viewed in last 30 days)
        $trending_stmt = $conn->prepare("
            SELECT 
                content_id,
                title,
                CONCAT('admin/', thumbnail_url) AS thumbnail_url,
                CONCAT('admin/', banner_url) AS banner_url,
                release_year,
                rating,
                content_type,
                is_premium,
                is_scheduled,
                schedule_date,
                views
            FROM content 
            WHERE is_scheduled = 0
            ORDER BY views DESC 
            LIMIT 10
        ");
        $trending_stmt->execute();
        $trending_result = $trending_stmt->get_result();
        $trending_content = $trending_result->fetch_all(MYSQLI_ASSOC);
        $trending_stmt->close();
        ?>
        
        <?php if (!empty($trending_content)): ?>
            <div class="row mt-4">
                <div class="col-12">
                    <h3 class="section-title"><i class="bi bi-fire"></i> Trending Now</h3>
                    <div class="scroll-container">
                        <?php foreach ($trending_content as $item): ?>
                            <div class="scroll-item">
                                <div class="movie-card position-relative">
                                    <a href="watch.php?id=<?= $item['content_id'] ?>" class="text-decoration-none">
                                        <img src="<?= htmlspecialchars($item['thumbnail_url']) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                                        <?php if ($item['is_scheduled']): ?>
                                            <div class="scheduled-badge">COMING SOON</div>
                                        <?php else: ?>
                                            <div class="card-badge"><?= $item['content_type'] === 'movie' ? 'Movie' : 'TV Show' ?></div>
                                        <?php endif; ?>
                                        <?php if ($item['is_premium']): ?>
                                            <div class="premium-badge">PREMIUM</div>
                                        <?php endif; ?>
                                        <div class="card-body">
                                            <h6 class="card-title"><?= htmlspecialchars($item['title']) ?></h6>
                                            <p class="card-text"><?= htmlspecialchars($item['release_year']) ?> • <?= number_format($item['views']) ?> views</p>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Premium Content Section -->
        <?php
        // Fetch premium content
        $premium_stmt = $conn->prepare("
            SELECT 
                content_id,
                title,
                CONCAT('admin/', thumbnail_url) AS thumbnail_url,
                CONCAT('admin/', banner_url) AS banner_url,
                release_year,
                rating,
                content_type,
                is_premium,
                is_scheduled,
                schedule_date
            FROM content 
            WHERE is_premium = 1 AND is_scheduled = 0
            ORDER BY views DESC 
            LIMIT 10
        ");
        $premium_stmt->execute();
        $premium_result = $premium_stmt->get_result();
        $premium_content = $premium_result->fetch_all(MYSQLI_ASSOC);
        $premium_stmt->close();
        ?>
        
        <?php if (!empty($premium_content)): ?>
            <div class="row mt-4">
                <div class="col-12">
                    <h3 class="section-title"><i class="bi bi-star-fill text-warning"></i> Premium Content</h3>
                    <div class="scroll-container">
                        <?php foreach ($premium_content as $item): ?>
                            <div class="scroll-item">
                                <div class="movie-card position-relative">
                                    <a href="watch.php?id=<?= $item['content_id'] ?>" class="text-decoration-none">
                                        <img src="<?= htmlspecialchars($item['thumbnail_url']) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                                        <div class="card-badge"><?= $item['content_type'] === 'movie' ? 'Movie' : 'TV Show' ?></div>
                                        <div class="premium-badge">PREMIUM</div>
                                        <div class="card-body">
                                            <h6 class="card-title"><?= htmlspecialchars($item['title']) ?></h6>
                                            <p class="card-text"><?= htmlspecialchars($item['release_year']) ?> • Rating: <?= $item['rating'] ?>/10</p>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Coming Soon Section -->
        <?php
        // Fetch scheduled/coming soon content
        $scheduled_stmt = $conn->prepare("
            SELECT 
                content_id,
                title,
                CONCAT('admin/', thumbnail_url) AS thumbnail_url,
                CONCAT('admin/', banner_url) AS banner_url,
                release_year,
                rating,
                content_type,
                is_premium,
                is_scheduled,
                schedule_date
            FROM content 
            WHERE is_scheduled = 1 AND schedule_date > NOW()
            ORDER BY schedule_date ASC 
            LIMIT 10
        ");
        $scheduled_stmt->execute();
        $scheduled_result = $scheduled_stmt->get_result();
        $scheduled_content = $scheduled_result->fetch_all(MYSQLI_ASSOC);
        $scheduled_stmt->close();
        ?>
        
        <?php if (!empty($scheduled_content)): ?>
            <div class="row mt-4">
                <div class="col-12">
                    <h3 class="section-title"><i class="bi bi-clock-history"></i> Coming Soon</h3>
                    <div class="scroll-container">
                        <?php foreach ($scheduled_content as $item): ?>
                            <div class="scroll-item">
                                <div class="movie-card position-relative">
                                    <a href="watch.php?id=<?= $item['content_id'] ?>" class="text-decoration-none">
                                        <img src="<?= htmlspecialchars($item['thumbnail_url']) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                                        <div class="scheduled-badge">COMING SOON</div>
                                        <?php if ($item['is_premium']): ?>
                                            <div class="premium-badge">PREMIUM</div>
                                        <?php endif; ?>
                                        <div class="card-body">
                                            <h6 class="card-title"><?= htmlspecialchars($item['title']) ?></h6>
                                            <p class="card-text">Releases: <?= date('M j, Y', strtotime($item['schedule_date'])) ?></p>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Share Modal -->
    <div class="modal fade" id="shareModal" tabindex="-1" aria-labelledby="shareModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-light">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title" id="shareModalLabel">
                        <i class="bi bi-share-fill me-2"></i>Share this content
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="shareLink" class="form-label">Share Link</label>
                        <div class="input-group">
                            <input type="text" class="form-control bg-secondary border-secondary text-light" 
                                   id="shareLink" value="<?= "https://streamify.com/watch.php?id={$content_id}" ?>" readonly>
                            <button class="btn btn-outline-light" type="button" id="copyLinkBtn">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="share-buttons text-center">
                        <h6 class="mb-3">Share on social media</h6>
                        <div class="d-flex justify-content-center gap-3 flex-wrap">
                            <a href="https://facebook.com/sharer/sharer.php?u=<?= urlencode("https://streamify.com/watch.php?id={$content_id}") ?>" 
                               target="_blank" class="btn btn-primary share-btn rounded-circle p-3" style="width: 60px; height: 60px;">
                                <i class="bi bi-facebook"></i>
                            </a>
                            <a href="https://twitter.com/intent/tweet?url=<?= urlencode("https://streamify.com/watch.php?id={$content_id}") ?>&text=<?= urlencode("Check out {$content['title']} on Streamify!") ?>" 
                               target="_blank" class="btn btn-info share-btn rounded-circle p-3" style="width: 60px; height: 60px;">
                                <i class="bi bi-twitter"></i>
                            </a>
                            <a href="https://wa.me/?text=<?= urlencode("Check out {$content['title']} on Streamify! https://streamify.com/watch.php?id={$content_id}") ?>" 
                               target="_blank" class="btn btn-success share-btn rounded-circle p-3" style="width: 60px; height: 60px;">
                                <i class="bi bi-whatsapp"></i>
                            </a>
                            <a href="https://www.instagram.com/" 
                               target="_blank" class="btn insta-bg share-btn rounded-circle p-3" style="width: 60px; height: 60px;">
                                <i class="bi bi-instagram"></i>
                            </a>
                            <a href="mailto:?subject=Check out <?= urlencode($content['title']) ?>&body=<?= urlencode("I found this amazing content on Streamify: https://streamify.com/watch.php?id={$content_id}") ?>" 
                               class="btn btn-danger share-btn rounded-circle p-3" style="width: 60px; height: 60px;">
                                <i class="bi bi-envelope-fill"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Premium Subscription Modal -->
    <div class="modal fade" id="premiumModal" tabindex="-1" aria-labelledby="premiumModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-light">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title" id="premiumModalLabel">
                        <i class="bi bi-star-fill text-warning me-2"></i>Upgrade to Premium
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <i class="bi bi-rocket-takeoff display-1 text-warning"></i>
                        <h4 class="text-warning mt-3">Unlock Premium Features</h4>
                    </div>
                    
                    <div class="premium-features mb-4">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-check-circle-fill text-success me-3 fs-5"></i>
                            <span>Download content for offline viewing</span>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-check-circle-fill text-success me-3 fs-5"></i>
                            <span>Access to exclusive premium content</span>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-check-circle-fill text-success me-3 fs-5"></i>
                            <span>Ad-free streaming experience</span>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-check-circle-fill text-success me-3 fs-5"></i>
                            <span>4K Ultra HD quality</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill text-success me-3 fs-5"></i>
                            <span>Multiple device streaming</span>
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <a href="subscription.php" class="btn btn-warning btn-lg w-100">
                            <i class="bi bi-lightning-charge-fill me-2"></i>View Subscription Plans
                        </a>
                        <p class="text-muted mt-2 small">Cancel anytime • No commitment</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Login Modal -->
<?php include "modals.php"; ?>
    <?php endif; ?>
    
    <?php include "footer.php";?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.plyr.io/3.7.8/plyr.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Video player state management
        const videoState = '<?= $video_state ?>';
        let player = null;
  // Interactive rating only in comment section
    const userId = <?= json_encode($user_id) ?>;
    const contentId = <?= json_encode($content_id) ?>;
    const commentStarContainer = document.getElementById('commentStarRating');
    const commentRatingInput = document.getElementById('commentRating');
    const selectedRatingSpan = document.getElementById('selectedRating');
    let commentUserRating = parseInt(commentRatingInput?.value || 0);
    function updateCommentStars(rating) {
        if (!commentStarContainer) return;
        commentStarContainer.querySelectorAll('.comment-star').forEach((star, idx) => {
            if (idx < rating) {
                star.classList.add('bi-star-fill', 'text-warning');
                star.classList.remove('bi-star');
            } else {
                star.classList.remove('bi-star-fill', 'text-warning');
                star.classList.add('bi-star');
            }
        });
        
        // Update selected rating text
        if (selectedRatingSpan) {
            selectedRatingSpan.textContent = rating > 0 ? `You rated: ${rating}/5` : 'Select your rating';
        }
    }

    if (commentStarContainer && userId) {
        commentStarContainer.querySelectorAll('.comment-star').forEach(star => {
            star.addEventListener('mouseenter', function() {
                updateCommentStars(parseInt(this.dataset.value));
            });
            
            star.addEventListener('mouseleave', function() {
                updateCommentStars(commentUserRating);
            });
            
            star.addEventListener('click', function() {
                const rating = parseInt(this.dataset.value);
                commentUserRating = rating;
                updateCommentStars(commentUserRating);
                if (commentRatingInput) commentRatingInput.value = rating;
            });
        });
        
        // Initialize stars with current user rating
        updateCommentStars(commentUserRating);
    }
    
    // Comment form submission
    const commentForm = document.getElementById('commentForm');
    if (commentForm) {
        commentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('#submitReview');
            const originalText = submitBtn.innerHTML;
            
            // Validate rating
            const rating = parseInt(formData.get('rating'));
            if (rating === 0) {
                showToast('Please select a rating', 'warning');
                return;
            }
            
            // Show loading state
            submitBtn.innerHTML = '<i class="bi bi-arrow-repeat spinner-border spinner-border-sm me-2"></i>Submitting...';
            submitBtn.disabled = true;
            
            fetch('post_comment.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Review submitted successfully!', 'success');
                    
                    // Add new comment to the top
                    const commentsContainer = document.getElementById('commentsContainer');
                    const newComment = `
                        <div class="comment-item mb-4 p-3 bg-dark rounded">
                            <div class="d-flex align-items-start gap-3">
                                <img src="${data.profile_pic}" class="comment-avatar" alt="${data.username}">
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <h6 class="mb-0 text-light">${data.username}</h6>
                                            <div class="star-rating">
                                                ${'<i class="bi bi-star-fill text-warning fs-6"></i>'.repeat(data.rating)}
                                                ${'<i class="bi bi-star fs-6"></i>'.repeat(5 - data.rating)}
                                            </div>
                                        </div>
                                        <small class="text-muted">Just now</small>
                                    </div>
                                    <p class="text-light mb-0">${data.comment}</p>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    // Update comments container
                    if (commentsContainer.querySelector('.text-center')) {
                        commentsContainer.innerHTML = newComment;
                    } else {
                        commentsContainer.insertAdjacentHTML('afterbegin', newComment);
                    }
                    
                    // Update rating summary
                    updateRatingSummary(data.new_avg_rating, data.new_total_ratings);
                    
                    // Reset form
                    this.reset();
                    commentUserRating = 0;
                    updateCommentStars(0);
                    commentRatingInput.value = 0;
                    
                } else {
                    showToast(data.error || 'Error submitting review', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred. Please try again.', 'danger');
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    }
    
    function updateRatingSummary(newAvgRating, newTotalRatings) {
        // Update average rating display
        const avgRatingElement = document.querySelector('.rating-summary h2');
        if (avgRatingElement) {
            avgRatingElement.textContent = newAvgRating.toFixed(1);
        }
        
        // Update total ratings count
        const totalRatingsElement = document.querySelector('.rating-summary small');
        if (totalRatingsElement) {
            totalRatingsElement.textContent = `${newTotalRatings} ratings`;
        }
        
        // Update stars in summary
        const summaryStars = document.querySelectorAll('.rating-summary .star-rating i');
        summaryStars.forEach((star, index) => {
            if (index < Math.round(newAvgRating)) {
                star.classList.add('bi-star-fill', 'text-warning');
                star.classList.remove('bi-star');
            } else {
                star.classList.remove('bi-star-fill', 'text-warning');
                star.classList.add('bi-star');
            }
        });
    }
        // Initialize video player only when appropriate
        function initializeVideoPlayer() {
            // Don't initialize for scheduled or premium locked content
            if (videoState === 'scheduled' || videoState === 'premium_locked') {
                console.log('Video player not initialized for:', videoState);
                return;
            }

            const videoElement = document.getElementById('player');
            if (!videoElement) {
                console.log('Video element not found');
                return;
            }

            // Check if video has valid sources
            const videoSources = videoElement.querySelectorAll('source');
            const hasValidSource = Array.from(videoSources).some(source => source.src && source.src.length > 0);

            if (!hasValidSource) {
                console.log('No valid video sources found');
                return;
            }

            // Initialize Plyr player
            player = new Plyr('#player', {
                controls: ['play', 'progress', 'current-time', 'mute', 'volume', 'settings', 'fullscreen'],
                ratio: '16:9',
                autoplay: false
            });

            <?php if (!$user_id): ?>
            // Guest user handling
            player.on('play', function(event) {
                console.log('Guest user attempted to play video');
                player.pause();
                showLoginPrompt();
                return false;
            });

            player.on('seeked', function(event) {
                console.log('Guest user attempted to seek');
                player.pause();
                showLoginPrompt();
            });

            // Disable right-click context menu on video
            videoElement.addEventListener('contextmenu', function(e) {
                e.preventDefault();
                showLoginPrompt();
            });
            <?php else: ?>
            // Logged-in user handling - track progress
            player.on('timeupdate', function() {
                const currentTime = player.currentTime;
                const duration = player.duration;
                
                if (duration > 0) {
                    const progress = (currentTime / duration) * 100;
                    
                    // Update progress bar
                    const progressBar = document.querySelector('.progress-bar .progress');
                    if (progressBar) {
                        progressBar.style.width = progress + '%';
                    }
                    
                    // Save progress every 10 seconds
                    if (Math.floor(currentTime) % 10 === 0) {
                        saveWatchProgress(<?= $content_id ?>, progress);
                    }
                }
            });

            // Handle video ended
            player.on('ended', function() {
                saveWatchProgress(<?= $content_id ?>, 100);
                showToast('Video completed!', 'success');
            });
            <?php endif; ?>

            console.log('Video player initialized successfully for state:', videoState);
        }

       // Countdown Timer for Scheduled Content
<?php if ($is_scheduled && $time_until_release): ?>
function updateCountdown() {
    const scheduleDate = new Date('<?= $content['schedule_date'] ?>').getTime();
    const now = new Date().getTime();
    const distance = scheduleDate - now;
    
    if (distance < 0) {
        // Content is now available, reload page
        location.reload();
        return;
    }
    
    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((distance % (1000 * 60)) / 1000);
    
    document.getElementById('countdown-days').textContent = days.toString().padStart(2, '0');
    document.getElementById('countdown-hours').textContent = hours.toString().padStart(2, '0');
    document.getElementById('countdown-minutes').textContent = minutes.toString().padStart(2, '0');
    document.getElementById('countdown-seconds').textContent = seconds.toString().padStart(2, '0');
}

updateCountdown();
setInterval(updateCountdown, 1000);
<?php endif; ?>
        // Initialize video player
        initializeVideoPlayer();

        // Function to show login prompt
        function showLoginPrompt() {
            showToast('Please login to watch videos', 'warning');
            
            // Show login modal after a short delay
            setTimeout(() => {
                const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
                loginModal.show();
            }, 800);
        }

        // Handle video click for guest users
        function handleGuestVideoClick(event) {
            // Don't trigger if user clicked on a button that already has modal handling
            if (event.target.closest('[data-bs-toggle="modal"]')) {
                return;
            }
            
            event.preventDefault();
            event.stopPropagation();
            showLoginPrompt();
            return false;
        }

        // Save watch progress for logged-in users
        function saveWatchProgress(contentId, progress) {
            fetch('track_watch.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `content_id=${contentId}&progress=${progress}`
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    console.error('Failed to save progress:', data.message);
                }
            })
            .catch(error => console.error('Error saving progress:', error));
        }

        // Toast notification function
        function showToast(message, type = 'info') {
            // Remove any existing toasts
            const existingToasts = document.querySelectorAll('.custom-toast');
            existingToasts.forEach(toast => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            });
            
            const bgColors = {
                'success': 'linear-gradient(135deg, #198754, #20c997)',
                'danger': 'linear-gradient(135deg, #dc3545, #e83e8c)',
                'warning': 'linear-gradient(135deg, #ffc107, #fd7e14)',
                'info': 'linear-gradient(135deg, #0dcaf0, #6f42c1)'
            };
            
            const toastHtml = `
                <div class="toast custom-toast align-items-center text-white border-0 position-fixed" 
                     style="top: 100px; right: 20px; z-index: 9999; background: ${bgColors[type] || bgColors.info}" 
                     role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body d-flex align-items-center">
                            <i class="bi bi-${getToastIcon(type)} me-2"></i>
                            ${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', toastHtml);
            
            const toastElement = document.querySelector('.custom-toast');
            const toast = new bootstrap.Toast(toastElement, {
                autohide: true,
                delay: type === 'success' ? 3000 : 5000
            });
            toast.show();
            
            // Remove toast from DOM after hide
            toastElement.addEventListener('hidden.bs.toast', function() {
                if (this.parentNode) {
                    this.parentNode.removeChild(this);
                }
            });
        }

        function getToastIcon(type) {
            const icons = {
                'success': 'check-circle-fill',
                'danger': 'exclamation-triangle-fill',
                'warning': 'exclamation-triangle-fill',
                'info': 'info-circle-fill'
            };
            return icons[type] || 'info-circle-fill';
        }

        // Rest of your existing JavaScript code for watchlist, downloads, ratings, etc.
        // [Keep all your existing functionality here]
        // Download functionality
const downloadBtn = document.getElementById('downloadBtn');
if (downloadBtn) {
    downloadBtn.addEventListener('click', function(e) {
        const isLoggedIn = <?= $user_id ? 'true' : 'false' ?>;
        const isPremiumUser = <?= $is_premium_user ? 'true' : 'false' ?>;
        const contentId = <?= $content_id ?>;
        const contentType = '<?= $content["content_type"] ?>';
        
        if (!isLoggedIn) {
            e.preventDefault();
            showToast('Please log in to download content', 'warning');
            const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
            loginModal.show();
            return;
        }
        
        if (!isPremiumUser) {
            e.preventDefault();
            showToast('Premium subscription required for downloads', 'warning');
            const premiumModal = new bootstrap.Modal(document.getElementById('premiumModal'));
            premiumModal.show();
            return;
        }
        
        // Handle download based on content type
        if (contentType === 'movie') {
            initiateDownload(contentId, 'movie');
        } else if (contentType === 'tv_show') {
            // For TV shows, show episode selection
            showEpisodeDownloadModal();
        }
    });
}

// Episode download buttons
document.querySelectorAll('.download-episode').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        
        const episodeId = this.getAttribute('data-episode-id');
        const episodeTitle = this.getAttribute('data-episode-title');
        const contentId = <?= $content_id ?>;
        
        initiateDownload(contentId, 'episode', episodeId, episodeTitle);
    });
});

function initiateDownload(contentId, type, episodeId = null, episodeTitle = null) {
    showToast('Preparing download...', 'info');
    
    const formData = new FormData();
    formData.append('content_id', contentId);
    formData.append('type', type);
    
    if (episodeId) {
        formData.append('episode_id', episodeId);
    }
    
    fetch('download_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            // Try to get error message
            return response.text().then(text => {
                try {
                    const error = JSON.parse(text);
                    throw new Error(error.error || 'Download failed');
                } catch (e) {
                    throw new Error('Download failed: ' + response.statusText);
                }
            });
        }
        return response.blob();
    })
    .then(blob => {
        // Create download link
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = url;
        
        // Set filename
        const filename = episodeTitle 
            ? `<?= preg_replace('/[^a-zA-Z0-9]/', '_', $content['title']) ?>_Episode_${episodeTitle}.mp4`
            : `<?= preg_replace('/[^a-zA-Z0-9]/', '_', $content['title']) ?>.mp4`;
        
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        
        // Cleanup
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
        
        showToast('Download started!', 'success');
        
        // Log download activity
        logDownloadActivity(contentId, type, episodeId);
    })
    .catch(error => {
        console.error('Download error:', error);
        showToast('Download failed. Please try again.', 'danger');
    });
}

function logDownloadActivity(contentId, type, episodeId = null) {
    fetch('log_activity.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `content_id=${contentId}&type=${type}&episode_id=${episodeId}&action=download`
    })
    .catch(error => console.error('Activity log error:', error));
}

function showEpisodeDownloadModal() {
    // Create a simple modal for episode selection
    const modalHtml = `
        <div class="modal fade" id="episodeDownloadModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content bg-dark text-light">
                    <div class="modal-header border-secondary">
                        <h5 class="modal-title">
                            <i class="bi bi-download me-2"></i>Download Episodes
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-light mb-3">Select episodes to download:</p>
                        <div class="episodes-download-list" style="max-height: 300px; overflow-y: auto;">
                            <?php if ($content['content_type'] === 'tv_show' && !empty($episodes)): ?>
                                <?php foreach ($episodes as $episode): ?>
                                    <div class="episode-download-item d-flex justify-content-between align-items-center p-2 mb-2 bg-secondary rounded">
                                        <div>
                                            <h6 class="mb-1 text-light">Episode <?= $episode['episode_number'] ?></h6>
                                            <p class="mb-0 text-muted small"><?= htmlspecialchars($episode['title']) ?></p>
                                        </div>
                                        <button class="btn btn-sm btn-streamify download-episode-modal" 
                                                data-episode-id="<?= $episode['episode_id'] ?>"
                                                data-episode-title="<?= htmlspecialchars($episode['title']) ?>">
                                            <i class="bi bi-download"></i> Download
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('episodeDownloadModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to DOM
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('episodeDownloadModal'));
    modal.show();
    
    // Add event listeners to download buttons in modal
    document.querySelectorAll('.download-episode-modal').forEach(btn => {
        btn.addEventListener('click', function() {
            const episodeId = this.getAttribute('data-episode-id');
            const episodeTitle = this.getAttribute('data-episode-title');
            const contentId = <?= $content_id ?>;
            
            initiateDownload(contentId, 'episode', episodeId, episodeTitle);
            modal.hide();
        });
    });
    
    // Remove modal from DOM when hidden
    document.getElementById('episodeDownloadModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}
// Comments and Ratings functionality
        // Watchlist functionality
        const watchlistBtn = document.getElementById('watchlistBtn');
        if (watchlistBtn) {
            watchlistBtn.addEventListener('click', function(e) {
                const isLoggedIn = <?= $user_id ? 'true' : 'false' ?>;
                
                if (!isLoggedIn) {
                    e.preventDefault();
                    showToast('Please log in to add content to your watchlist', 'warning');
                    return;
                }
                
                const contentId = <?= $content_id ?>;
                const isInWatchlist = this.classList.contains('btn-streamify');
                
                fetch('watchlist_handler.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `content_id=${contentId}&action=${isInWatchlist ? 'remove' : 'add'}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        
                        if (isInWatchlist) {
                            this.classList.remove('btn-streamify');
                            this.classList.add('btn-outline-streamify');
                            this.innerHTML = '<i class="bi bi-bookmark-plus"></i> Add to Watchlist';
                        } else {
                            this.classList.remove('btn-outline-streamify');
                            this.classList.add('btn-streamify');
                            this.innerHTML = '<i class="bi bi-bookmark-check-fill"></i> In Watchlist';
                        }
                    } else {
                        showToast(data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('An error occurred. Please try again.', 'danger');
                });
            });
        }

        // Remind Me functionality for scheduled content
        const remindMeBtn = document.getElementById('remindMeBtn');
        const setReminderBtn = document.getElementById('setReminderBtn');
        
        function handleReminder() {
            const contentId = <?= $content_id ?>;
            
            fetch('set_reminder.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `content_id=${contentId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Reminder set successfully! You will be notified when this content is available.', 'success');
                    if (remindMeBtn) remindMeBtn.disabled = true;
                    if (setReminderBtn) setReminderBtn.disabled = true;
                } else {
                    showToast(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred. Please try again.', 'danger');
            });
        }
        
        if (remindMeBtn) {
            remindMeBtn.addEventListener('click', handleReminder);
        }
        
        if (setReminderBtn) {
            setReminderBtn.addEventListener('click', handleReminder);
        }

        // Quick login form handling
        const quickLoginForm = document.getElementById('quickLoginForm');
        if (quickLoginForm) {
            quickLoginForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                
                // Show loading state
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="bi bi-arrow-repeat spinner-border spinner-border-sm me-2"></i>Logging in...';
                submitBtn.disabled = true;
                
                fetch('login.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Login successful!', 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        showToast(data.message || 'Login failed', 'danger');
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Login error:', error);
                    showToast('Login failed. Please try again.', 'danger');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                });
            });
        }

        // Episode switching for TV shows
        const episodeItems = document.querySelectorAll('.episode-item');
        episodeItems.forEach(item => {
            item.addEventListener('click', function() {
                // Remove active class from all episodes
                episodeItems.forEach(ep => ep.classList.remove('active'));
                // Add active class to clicked episode
                this.classList.add('active');
                
                // Change video source
                const videoSrc = this.getAttribute('data-video-src');
                if (videoSrc && player) {
                    player.source = {
                        type: 'video',
                        sources: [{
                            src: videoSrc,
                            type: 'video/mp4'
                        }]
                    };
                }
            });
        });

        // Share modal functionality
        const copyLinkBtn = document.getElementById('copyLinkBtn');
        if (copyLinkBtn) {
            copyLinkBtn.addEventListener('click', function() {
                const shareLink = document.getElementById('shareLink');
                shareLink.select();
                shareLink.setSelectionRange(0, 99999);
                navigator.clipboard.writeText(shareLink.value).then(() => {
                    const originalIcon = this.innerHTML;
                    this.innerHTML = '<i class="bi bi-check"></i>';
                    showToast('Link copied to clipboard!', 'success');
                    setTimeout(() => {
                        this.innerHTML = originalIcon;
                    }, 2000);
                });
            });
        }
    });
    </script>
</body>
</html>