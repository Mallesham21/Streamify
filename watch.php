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

if ($content_id > 0) {
    // Increment view count
    $conn->query("UPDATE content SET views = views + 1 WHERE content_id = $content_id");
    
    // Fetch content
    $stmt = $conn->prepare("SELECT * FROM content WHERE content_id = ?");
    $stmt->bind_param('i', $content_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $content = $result->fetch_assoc();
    $stmt->close();



// Example: fetch row from DB
$contents = [
    "title"   => "My Example Content",
    "content_type"    => "movie",    // "movie" OR "tv_show"
    "list_id" => "abcd1234"    // PixelDrain list ID
];

// Fetch list metadata
$listApiUrl = "https://pixeldrain.com/api/list/" . $contents['list_id'];
$listData   = file_get_contents($listApiUrl);
$listJson   = json_decode($listData, true);

// Default values
$thumbnail = $banner = null;
$video     = null;
$episodes  = [];

if ($listJson && isset($listJson['files'])) {
    $files = $listJson['files'];

echo "<script>";
echo "alert(JSON.stringify(" . json_encode($files) . ", null, 2));";
echo "</script>";

    // Thumbnail
    if (isset($files[0])) {
        $thumbnail = "https://pixeldrain.com/api/file/" . $files[0]['id'] . "?download";
    }

    // Banner
    if (isset($files[1])) {
        $banner = "https://pixeldrain.com/api/file/" . $files[1]['id'] . "?download";
    }

    // Content type handling
                $video = "https://pixeldrain.com/api/file/" . $files[2]['id'] . "?download";

    if ($contents['content_type'] === "movie") {
        if (isset($files[2])) {
            $video = "https://pixeldrain.com/api/file/" . $files[2]['id'] . "?download";
        }
            echo "<script>alert('Movie link: $video')</script>";

    } elseif ($contents['content_type'] === "tv_show") {
        if (count($files) > 2) {
            foreach (array_slice($files, 2) as $index => $file) {
                $episodes[] = [
                    "title" => "Episode " . ($index + 1),
                    "url"   => "https://pixeldrain.com/api/file/" . $file['id'] . "?download"
                ];
            }
        }
    }
}

// ===== Now you have clean variables =====
// $thumbnail → URL for thumbnail
// $banner    → URL for banner
// $video     → Movie video (if type=movie)
// $episodes  → Array of episode videos (if type=tv_show)
    if ($content) {
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
            
            // Get watch progress
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

// Fetch comments and ratings data
$comments = [];
$avg_rating = null;
$user_rating = null;
$total_ratings = 0;

if ($content_id > 0) {
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
//More like this
if (!empty($category_ids)) {
    $related_sql = "
        SELECT 
            c.content_id,
            c.title,
            c.thumbnail_url,
            c.banner_url,
            c.release_year,
            c.rating,
            c.content_type
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
// 🔥 TRENDING NOW
$trendSql = "
SELECT c.content_id,
       c.title,
       c.thumbnail_url,
       c.release_year,
       c.views,
       GROUP_CONCAT(cat.name ORDER BY cat.name SEPARATOR ', ') AS categories
FROM        content            AS c
LEFT JOIN   content_categories AS cc  ON cc.content_id  = c.content_id
LEFT JOIN   categories         AS cat ON cat.category_id = cc.category_id
WHERE       c.views > 0
GROUP BY    c.content_id
ORDER BY    c.views DESC
LIMIT 15";

$trendResult = $conn->query($trendSql);
$trending = $trendResult->fetch_all(MYSQLI_ASSOC);
// Fetch latest 5 new releases (customize as needed)
$newReleasesSql = "
SELECT c.content_id,
       c.title,
       c.description,
       c.thumbnail_url,
       c.release_year,
       GROUP_CONCAT(cat.name ORDER BY cat.name SEPARATOR ', ') AS categories
FROM        content            AS c
LEFT JOIN   content_categories AS cc  ON cc.content_id  = c.content_id
LEFT JOIN   categories         AS cat ON cat.category_id = cc.category_id
GROUP BY    c.content_id
ORDER BY    c.release_year DESC, c.created_at DESC
";
$newReleasesResult = $conn->query($newReleasesSql);
$newReleases = $newReleasesResult->fetch_all(MYSQLI_ASSOC);

$topRatedSql = "
SELECT 
  c.content_id, 
  c.title, 
  c.thumbnail_url, 
  c.release_year, 
  c.rating, 
  GROUP_CONCAT(cat.name ORDER BY cat.name SEPARATOR ', ') AS categories
FROM content AS c
LEFT JOIN content_categories AS cc ON cc.content_id = c.content_id
LEFT JOIN categories AS cat ON cat.category_id = cc.category_id
GROUP BY c.content_id
ORDER BY c.rating DESC
LIMIT 5";
$topRatedResult = $conn->query($topRatedSql);
$topRated = $topRatedResult->fetch_all(MYSQLI_ASSOC);


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $content ? htmlspecialchars($content['title']) : 'Content Not Found' ?> | Streamify</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
    <style>
    :root {
        --primary-color: #f0c14b;
        --secondary-color: #f5576c;
        --dark-bg: #0a0a0a;
        --card-bg: rgba(255, 255, 255, 0.05);
        --card-border: rgba(255, 255, 255, 0.1);
        --primary-gradient: linear-gradient(135deg, #f0c14b 0%, #f5576c 100%);
        --secondary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    body {
        background: var(--dark-bg);
        color: #fff;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        overflow-x: hidden;
    }
    
    /* Hero Section */
    .hero-section {
        background: linear-gradient(135deg, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.75) 100%), 
                    url('<?= htmlspecialchars($content['banner_url'] ?? 
    "https://images.unsplash.com/photo-1489599102910-59206b8ca314?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1770&q=80") ?>');
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
    
    .hero-poster::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 30%;
        background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, transparent 100%);
        pointer-events: none;
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
    
    .btn-custom {
        background: var(--primary-color);
        border: none;
        color: #000;
        padding: 0.75rem 1.5rem;
        border-radius: 50px;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(240, 193, 75, 0.3);
    }
    
    .btn-custom:hover {
        background: #e6b740;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(240, 193, 75, 0.4);
    }
    
    .btn-outline-custom {
        background: transparent;
        border: 2px solid rgba(255, 255, 255, 0.3);
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 50px;
        font-weight: 600;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
    }
    
    .btn-outline-custom:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: rgba(255, 255, 255, 0.5);
        transform: translateY(-2px);
    }
    
    .rating-container {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 1.5rem 0;
        padding: 1rem;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 12px;
        backdrop-filter: blur(10px);
        max-width: fit-content;
    }
    
    .star-rating {
        display: flex;
        gap: 3px;
    }
    
    .star-rating i {
        font-size: 1.5rem;
        color: #fd7e14;
    }
    
    .action-buttons {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        margin-top: 2rem;
    }
    
    .hero-stats {
        display: flex;
        gap: 1.5rem;
        margin: 1.5rem 0;
        flex-wrap: wrap;
    }
    
    .stat-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9rem;
    }
    
    .stat-item i {
        color: var(--primary-color);
    }
    
    .hero-description {
        font-size: 1.1rem;
        line-height: 1.7;
        margin-bottom: 1.5rem;
        color: rgba(255, 255, 255, 0.85);
    }
    
    .hero-meta {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }
    
    /* Main Content Styles */
    .video-container {
        background: #000;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(0,0,0,0.5);
        position: relative;
    }
    
    .plyr {
        border-radius: 15px;
        overflow: hidden;
    }
    
    .progress-bar {
        height: 6px;
        background: rgba(255,255,255,0.2);
        border-radius: 3px;
        overflow: hidden;
        margin-bottom: 1rem;
    }
    
    .progress-bar .progress {
        height: 100%;
        background: var(--primary-gradient);
        border-radius: 3px;
        width: <?= $watch_progress ?>%;
        transition: width 0.3s ease;
    }
    
    .content-card {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: 15px;
        backdrop-filter: blur(10px);
        transition: all 0.3s ease;
    }
    
    .content-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.3);
        border-color: rgba(240, 193, 75, 0.3);
    }
    
    .episode-item {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: 10px;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .episode-item:hover {
        background: rgba(240, 193, 75, 0.1);
        border-color: var(--primary-color);
        transform: translateX(5px);
    }
    
    .episode-item.active {
        background: linear-gradient(135deg, rgba(240, 193, 75, 0.2), rgba(245, 87, 108, 0.2));
        border-color: var(--primary-color);
    }
    
    /* Content Cards */
    .movie-card {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.3s ease;
        height: 100%;
    }
    
    .movie-card:hover {
        transform: scale(1.05);
        box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        border-color: var(--primary-color);
    }
    
    .movie-card img {
        width: 100%;
        height: 200px;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    
    .movie-card:hover img {
        transform: scale(1.1);
    }
    
    .card-title {
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        text-fill-color: transparent;
        font-weight: 700;
    }
    
    .card-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background: var(--primary-gradient);
        color: #000;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: bold;
        z-index: 10;
    }
    
    .card-hover-actions {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .movie-card:hover .card-hover-actions {
        opacity: 1;
    }
    
    /* Comments */
    .comment-avatar {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--primary-color);
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
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    /* Scroll Container */
    .scroll-container {
        display: flex;
        overflow-x: auto;
        gap: 20px;
        padding: 10px 0;
        scroll-behavior: smooth;
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    
    .scroll-container::-webkit-scrollbar {
        display: none;
    }
    
    .scroll-item {
        flex: 0 0 auto;
        width: 250px;
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
    
    /* Responsive Design */
    @media (max-width: 992px) {
        .hero-title {
            font-size: 2.5rem;
        }
        
        .hero-poster {
            max-width: 280px;
            margin-bottom: 2rem;
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
        
        .rating-container {
            margin: 1.5rem auto;
        }
        
        .section-title {
            font-size: 1.5rem;
        }
        
        .hero-section {
            padding: 1rem 0;
        }
        
        .video-container {
            margin-bottom: 1rem;
        }
    }
    </style>
</head>
<body>
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
                    <img src="<?= htmlspecialchars($content['thumbnail_url'] ?? 'https://images.unsplash.com/photo-1536440136628-849c177e76a1?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=950&q=80') ?>" alt="<?= htmlspecialchars($content['title']) ?>">
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
$minutes = (int)$content['duration']; // e.g. 148
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
                
                <div class="action-buttons">
                    <?php if ($user_id): ?>
                        <button id="watchlistBtn" class="btn <?= $in_watchlist ? 'btn-custom' : 'btn-outline-custom' ?>">
                            <i class="bi bi-<?= $in_watchlist ? 'bookmark-check-fill' : 'bookmark-plus' ?>"></i> 
                            <?= $in_watchlist ? 'In Watchlist' : 'Add to Watchlist' ?>
                        </button>
                    <?php else: ?>
                        <button class="btn btn-outline-custom" data-bs-toggle="modal" data-bs-target="#loginModal">
                            <i class="bi bi-bookmark-plus"></i> Add to Watchlist
                        </button>
                    <?php endif; ?>
                    

                    <button class="btn btn-outline-custom" data-bs-toggle="modal" data-bs-target="#shareModal">
                        <i class="bi bi-share"></i> Share
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>


    <!-- Main Content Area -->
    <div class="container py-5">
        <div class="row g-4">
            <!-- Main Content Column -->
            <div class="col-lg-8">
                <!-- Video Player -->
                <div class="video-container mb-4">
                    <div class="progress-bar">
                        <div class="progress"></div>
                    </div>
                    <div class="ratio ratio-16x9">
                        <video id="player" playsinline controls data-poster="<?= htmlspecialchars($content['thumbnail_url']) ?>">
                            <?php if ($content['content_type'] === 'movie' && $content['video_path']): ?>
                                <source src="<?= $video ?>" type="video/mp4">
                            <?php elseif ($content['content_type'] === 'tv_show' && !empty($episodes)): ?>
                                <source src="<?= htmlspecialchars("https://pixeldrain.com/api/file/".$episodes[0]['video_path']."?download") ?>" type="video/mp4">
                            <?php endif; ?>
                        </video>
                    </div>
                </div>
                
<!-- About Section -->
<div class="content-card mb-4 w-100">
    <div class="card-body">
        <h3 class="section-title"><i class="bi bi-info-circle"></i> About</h3>
        <p class="text-light"><?= htmlspecialchars($content['description']) ?></p>
    </div>
</div>

<!-- Details Section -->
<div class="content-card mb-4 w-100">
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
                
                <!-- Comments Section -->
                <div class="content-card mb-4 w-100">
                    <div class="card-body">
                        <h3 class="section-title"><i class="bi bi-chat-left-text"></i> Comments</h3>
                        
                        <?php if ($user_id): ?>
<form id="commentForm" class="mb-4" method="POST">
    <div class="d-flex gap-3">
        <img src="<?= htmlspecialchars($user_profile_pic ?? "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23f0c14b'><path d='M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z'/></svg>") ?>" 
             class="comment-avatar" alt="Your profile picture">
        <div class="flex-grow-1">
            <input type="hidden" name="content_id" value="<?= $content_id ?>">
            <input type="hidden" name="user_id" value="<?= $user_id ?>">
            <textarea name="comment" class="form-control bg-dark text-light border-secondary mb-2" 
                     placeholder="Add a comment..." rows="3" required></textarea>
            <div class="d-flex justify-content-between align-items-center">
                <div id="commentStarRating" class="star-rating">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="bi bi-star<?= ($user_rating && $i <= $user_rating) ? '-fill text-warning' : '' ?> fs-5 comment-star star" data-value="<?= $i ?>"></i>
                    <?php endfor; ?>
                    <input type="hidden" name="rating" id="commentRating" value="<?= $user_rating ?? 0 ?>">
                </div>
                <button type="submit" class="btn btn-custom">Post Comment</button>
            </div>
        </div>
    </div>
</form>                        <?php else: ?>
                            <div class="alert alert-info">
                                <a href="login.php" class="text-primary">Login</a> to post a comment.
                            </div>
                        <?php endif; ?>
                        
                        <div id="commentsContainer">
                            <?php if (empty($comments)): ?>
                                <p class="text-muted">No comments yet. Be the first to comment!</p>
                            <?php else: ?>
                                <?php foreach ($comments as $comment): ?>
                                    <div class="d-flex gap-3 mb-4 p-3 content-card w-100">
                                        <img src="<?= htmlspecialchars($comment['profile_pic'] ?? "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23f0c14b'><path d='M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z'/></svg>") ?>" 
                                             class="comment-avatar" alt="<?= htmlspecialchars($comment['username']) ?>'s profile picture">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <strong class="text-warning"><?= htmlspecialchars($comment['username']) ?></strong>
                                                <small class="text-secondary"><?= date('M j, Y', strtotime($comment['date'])) ?></small>
                                                <div class="ms-2">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="bi bi-star<?= $i <= $comment['rating'] ? '-fill text-warning' : '' ?> fs-7"></i>
                                                    <?php endfor; ?>
                                                </div>
                                            </div>
                                            <p class="mb-0 text-light"><?= htmlspecialchars($comment['comment']) ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Sidebar -->
            <div class="col-lg-4">
                <!-- Episodes (for TV shows) -->
                <?php if ($content['content_type'] === 'tv_show' && !empty($episodes)): ?>
                <div class="content-card mb-4 w-100">
                    <div class="card-body">
                        <h4 class="section-title"><i class="bi bi-collection-play"></i> Episodes</h4>
                        <div class="episode-list">
                            <?php foreach ($episodes as $i => $ep): ?>
                                <div class="episode-item p-3 mb-2<?= $i === 0 ? ' active' : '' ?>" data-ep-src="<?= htmlspecialchars($ep['video_path']) ?>" data-ep-id="<?= $ep['episode_id'] ?>">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold text-warning">Episode <?= (int)$ep['episode_number'] ?></div>
                                            <div class="text-light"><?= htmlspecialchars($ep['title']) ?></div>
                                            <small class="text-muted"><?= (int)$ep['duration_minutes'] ?> min • <?= date('M j, Y', strtotime($ep['release_date'])) ?></small>
                                        </div>
                                        <i class="bi bi-play-circle fs-4 text-warning"></i>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Content Recommendations -->
    <div class="container mb-5">
        <!-- More Like This -->
        <?php if (!empty($related_content)): ?>
        <div class="mb-5">
            <h3 class="section-title fw-bold">More Like This <i class="bi bi-collection-play"></i></h3>
            <div class="scroll-container">
                <?php foreach ($related_content as $row): ?>
                    <div class="scroll-item">
                        <div class="card movie-card">
                            <?php if ($row['rating'] >= 8): ?>
                                <span class="card-badge">★ <?= htmlspecialchars($row['rating']) ?></span>
                            <?php endif; ?>
                            <img src="<?= htmlspecialchars($row['thumbnail_url'] ?: 'default-thumbnail.jpg') ?>" class="card-img-top" alt="<?= htmlspecialchars($row['title']) ?>">
                            <div class="card-hover-actions">
                                <a href="watch.php?id=<?= $row['content_id'] ?>" class="btn btn-primary btn-sm">
                                    <i class="bi bi-play-fill"></i> Play
                                </a>
                                <?php if ($user_id): ?>
                                    <button class="btn btn-outline-light btn-sm watchlist-btn" data-content-id="<?= $row['content_id'] ?>">
                                        <i class="bi bi-plus"></i> Add
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-outline-light btn-sm" data-bs-toggle="modal" data-bs-target="#loginModal">
                                        <i class="bi bi-plus"></i> Add
                                    </button>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($row['title']) ?></h5>
                                <p class="card-text">
                                    <?= $row['content_type'] === 'movie' ? 'Movie' : 'TV Show' ?> | 
                                    <?= $row['release_year'] ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Trending Now -->
        <div class="mb-5">
            <h3 class="section-title fw-bold">Trending Now <i class="bi bi-fire"></i></h3>
            <div class="scroll-container">
                <?php foreach ($trending as $row): ?>
                    <div class="scroll-item">
                        <div class="card movie-card">
                            <?php
                            $badge = '';
                            if ($row['views'] >= 1_000_000)
                                $badge = 'TOP 10';
                            elseif ($row['views'] >= 200_000)
                                $badge = 'HOT';
                            elseif ($row['release_year'] == date('Y'))
                                $badge = 'NEW';
                            if ($badge)
                                echo "<span class=\"card-badge\">{$badge}</span>";
                            ?>
                            <img src="<?= htmlspecialchars($row['thumbnail_url'] ?: 'default-thumbnail.jpg') ?>" class="card-img-top" alt="<?= htmlspecialchars($row['title']) ?>">
                            <div class="card-hover-actions">
                                <a href="watch.php?id=<?= $row['content_id'] ?>" class="btn btn-primary btn-sm">
                                    <i class="bi bi-play-fill"></i> Play
                                </a>
                                <button class="btn btn-outline-light btn-sm">
                                    <i class="bi bi-plus"></i> Add
                                </button>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($row['title']) ?></h5>
                                <p class="card-text">
                                    <?= htmlspecialchars($row['categories']) ?> |
                                    <?= $row['release_year'] ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- New Releases -->
        <div class="mb-5">
            <h3 class="section-title fw-bold">New Releases <i class="bi bi-stars"></i></h3>
            <div class="scroll-container">
                <?php foreach ($newReleases as $row): ?>
                    <div class="scroll-item">
                        <div class="card movie-card">
                            <span class="card-badge">NEW</span>
                            <img src="<?= htmlspecialchars($row['thumbnail_url'] ?: 'default-thumbnail.jpg') ?>" class="card-img-top" alt="<?= htmlspecialchars($row['title']) ?>">
                            <div class="card-hover-actions">
                                <a href="watch.php?id=<?= $row['content_id'] ?>" class="btn btn-primary btn-sm">
                                    <i class="bi bi-play-fill"></i> Play
                                </a>
                                <button class="btn btn-outline-light btn-sm"><i class="bi bi-plus"></i> Add</button>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($row['title']) ?></h5>
                                <p class="card-text"><?= htmlspecialchars($row['categories']) ?> | <?= $row['release_year'] ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Top Rated -->
        <div class="mb-5">
            <h3 class="section-title fw-bold">Top Rated <i class="bi bi-star-fill"></i></h3>
            <div class="scroll-container">
                <?php foreach ($topRated as $row): ?>
                    <div class="scroll-item">
                        <div class="card movie-card">
                            <span class="card-badge">★ <?= htmlspecialchars($row['rating']) ?></span>
                            <img src="<?= htmlspecialchars($row['thumbnail_url'] ?: 'default-thumbnail.jpg') ?>" class="card-img-top" alt="<?= htmlspecialchars($row['title']) ?>">
                            <div class="card-hover-actions">
                                <a href="watch.php?id=<?= $row['content_id'] ?>" class="btn btn-primary btn-sm">
                                    <i class="bi bi-play-fill"></i> Play
                                </a>
                                <button class="btn btn-outline-light btn-sm"><i class="bi bi-plus"></i> Add</button>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($row['title']) ?></h5>
                                <p class="card-text"><?= htmlspecialchars($row['categories']) ?> | <?= $row['release_year'] ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
<!-- Share Modal -->
<div class="modal fade" id="shareModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark">
            <div class="modal-header border-secondary">
                <h5 class="modal-title">Share</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-around mb-3">
                    <a href="#" class="text-decoration-none text-center share-btn">
                        <div class="bg-primary rounded-circle p-3 mb-2">
                            <i class="bi bi-facebook fs-4"></i>
                        </div>
                        <small>Facebook</small>
                    </a>
                    <a href="#" class="text-decoration-none text-center share-btn">
                        <div class="bg-dark rounded-circle p-3 mb-2">
                            <i class="bi bi-twitter-x fs-4"></i>
                        </div>
                        <small>X</small>
                    </a>
                    <a href="#" class="text-decoration-none text-center share-btn">
                        <div class="bg-gradient rounded-circle p-3 mb-2 insta-bg">
                            <i class="bi bi-instagram fs-4"></i>
                        </div>
                        <small>Instagram</small>
                    </a>
                    <a href="#" class="text-decoration-none text-center share-btn">
                        <div class="bg-success rounded-circle p-3 mb-2">
                            <i class="bi bi-link-45deg fs-4"></i>
                        </div>
                        <small>Copy Link</small>
                    </a>
                </div>
                <div class="input-group">
                    <input type="text" class="form-control bg-secondary text-light" 
                           value="<?= "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" ?>" readonly>
                    <button class="btn btn-custom" id="copyLinkBtn">
                        <i class="bi bi-clipboard"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
    
    <?php include "footer.php"?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.plyr.io/3.7.8/plyr.polyfilled.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    <script>
    // Initialize video player
    const player = new Plyr('#player', {
        controls: [
            'play-large', 'play', 'progress', 'current-time', 'duration', 'mute', 'volume', 
            'settings', 'pip', 'airplay', 'fullscreen'
        ],
        settings: ['speed', 'quality'],
        keyboard: { focused: true, global: true },
        storage: { enabled: true, key: 'plyr_<?= $content_id ?>' }
    });
    
    // Track watch progress
    let progressInterval;
    player.on('play', () => {
        // Send play event to server
        if (<?= $user_id ? 'true' : 'false' ?>) {
            fetch('track_watch.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `content_id=<?= $content_id ?>&action=play`
            });
            
            // Update progress every 10 seconds
            progressInterval = setInterval(() => {
                if (!player.paused) {
                    const percent = (player.currentTime / player.duration) * 100;
                    document.querySelector('.progress-bar .progress').style.width = `${percent}%`;
                    
                    // Update server every 30 seconds
                    if (Math.floor(player.currentTime) % 30 === 0) {
                        fetch('track_watch.php', {
                            method: 'POST',
                            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                            body: `content_id=<?= $content_id ?>&progress=${percent}`
                        });
                    }
                }
            }, 10000);
        }
    });
    
    player.on('pause', () => {
        if (progressInterval) clearInterval(progressInterval);
    });
    
    player.on('ended', () => {
        if (progressInterval) clearInterval(progressInterval);
        if (<?= $user_id ? 'true' : 'false' ?>) {
            fetch('track_watch.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `content_id=<?= $content_id ?>&action=complete`
            });
        }
    });
    
    // Episode selection (for series)
    const episodeItems = document.querySelectorAll('.episode-item');
    episodeItems.forEach(item => {
        item.addEventListener('click', function() {
            episodeItems.forEach(i => i.classList.remove('active'));
            this.classList.add('active');
            const src = this.getAttribute('data-ep-src');
            const epId = this.getAttribute('data-ep-id');
            
            if (src && player) {
                player.source = { 
                    type: 'video', 
                    sources: [{ src: src, type: src.endsWith('.m3u8') ? 'application/x-mpegURL' : 'video/mp4' }],
                    poster: '<?= htmlspecialchars($content['thumbnail_url']) ?>'
                };
                player.play();
                
                // Track episode change
                if (<?= $user_id ? 'true' : 'false' ?>) {
                    fetch('track_watch.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: `episode_id=${epId}&action=play`
                    });
                }
            }
        });
    });
    
    // Watchlist button
    const watchlistBtn = document.getElementById('watchlistBtn');
    if (watchlistBtn) {
        watchlistBtn.addEventListener('click', function() {
            const isInWatchlist = this.classList.contains('btn-custom');
            const action = isInWatchlist ? 'remove' : 'add';
            
            fetch('watchlist_action.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `content_id=<?= $content_id ?>&action=${action}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (isInWatchlist) {
                        this.classList.remove('btn-custom');
                        this.classList.add('btn-outline-custom');
                        this.innerHTML = '<i class="bi bi-bookmark-plus"></i> Add to Watchlist';
                    } else {
                        this.classList.remove('btn-outline-custom');
                        this.classList.add('btn-custom');
                        this.innerHTML = '<i class="bi bi-bookmark-check-fill"></i> In Watchlist';
                    }
                } else {
                    alert(data.message || 'Error updating watchlist');
                }
            });
        });
    }
    
    // Interactive rating only in comment section
    const userId = <?= json_encode($user_id) ?>;
    const contentId = <?= json_encode($content_id) ?>;
    const commentStarContainer = document.getElementById('commentStarRating');
    const commentRatingInput = document.getElementById('commentRating');
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
        updateCommentStars(commentUserRating);
    }
    
    // Comment form submission
    const commentForm = document.getElementById('commentForm');
    if (commentForm) {
        commentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            fetch('post_comment.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Add new comment to the top
                    const commentsContainer = document.getElementById('commentsContainer');
                    const newComment = `
                        <div class="d-flex gap-3 mb-4 p-3 content-card w-100">
                            <img src="${data.profile_pic}" class="comment-avatar" alt="${data.username}'s profile picture">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <strong class="text-warning">${data.username}</strong>
                                    <small class="text-secondary">${data.date}</small>
                                    <div class="ms-2">
                                        ${'<i class="bi bi-star-fill text-warning fs-7"></i>'.repeat(data.rating)}
                                        ${'<i class="bi bi-star fs-7"></i>'.repeat(5 - data.rating)}
                                    </div>
                                </div>
                                <p class="mb-0 text-light">${data.comment}</p>
                            </div>
                        </div>
                    `;
                    
                    if (commentsContainer.querySelector('.text-muted')) {
                        commentsContainer.innerHTML = newComment;
                    } else {
                        commentsContainer.insertAdjacentHTML('afterbegin', newComment);
                    }
                    
                    // Reset form
                    this.reset();
                    document.querySelectorAll('.comment-star').forEach(star => {
                        star.classList.remove('bi-star-fill', 'text-warning');
                        star.classList.add('bi-star');
                    });
                    document.getElementById('commentRating').value = 0;
                } else {
                    alert(data.error || 'Error posting comment');
                }
            });
        });
    }
    
    // Copy link button
    document.getElementById('copyLinkBtn')?.addEventListener('click', function() {
        const linkInput = document.querySelector('#shareModal input');
        linkInput.select();
        document.execCommand('copy');
        
        const originalText = this.innerHTML;
        this.innerHTML = '<i class="bi bi-check"></i> Copied!';
        setTimeout(() => {
            this.innerHTML = originalText;
        }, 2000);
    });
    </script>
</body>
</html>