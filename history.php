<?php
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's watch history with content details
$history_query = "
    SELECT c.content_id, c.title, c.description, CONCAT('admin/', c.thumbnail_url) AS thumbnail_url, c.content_type, 
           c.rating as content_rating, GROUP_CONCAT(cat.name SEPARATOR ', ') as categories,
           wh.progress_percent, wh.last_watched, wh.rating as user_rating
    FROM watch_history wh
    JOIN content c ON wh.content_id = c.content_id
    LEFT JOIN content_categories cc ON c.content_id = cc.content_id
    LEFT JOIN categories cat ON cc.category_id = cat.category_id
    WHERE wh.user_id = ?
    GROUP BY c.content_id, c.title, c.description, c.thumbnail_url, c.content_type, c.rating, wh.progress_percent, wh.last_watched, wh.rating
    ORDER BY wh.last_watched DESC
";

$stmt = $conn->prepare($history_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$history_items = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Watch History | Streamify</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Streamify custom colors -->
<style>
    :root {
        --streamify-primary: #6a11cb;
        --streamify-secondary: #2575fc;
        --streamify-dark: #1a1a2e;
        --streamify-light: #f8f9fa;
        --streamify-text: #e2e2e2;
        --streamify-text-muted: #a0a0a0;
    }
    body {
        background-color: var(--streamify-dark);
        color: var(--streamify-text);
    }
    .card {
        background-color: #16213e;
        border: none;
        transition: transform 0.3s;
        color: var(--streamify-text);
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.3);
    }
    .btn-streamify {
        background: linear-gradient(to right, var(--streamify-primary), var(--streamify-secondary));
        border: none;
        color: white;
    }
    .btn-streamify:hover {
        background: linear-gradient(to right, #5a0cb0, #1a65e0);
        color: white;
    }
    .empty-state {
        height: 60vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .badge-streamify {
        background-color: rgba(106, 17, 203, 0.2);
        color: #b57aff;
    }
    
    /* New text color rules */
    .card-title {
        color: var(--streamify-text) !important;
    }
    .card-text {
        color: var(--streamify-text-muted) !important;
    }
    .text-muted {
        color: var(--streamify-text-muted) !important;
    }
    .text-warning {
        color: #ffc107 !important;
    }
    .btn-outline-light {
        color: var(--streamify-text);
        border-color: var(--streamify-text-muted);
    }
    .btn-outline-light:hover {
        color: var(--streamify-dark);
        background-color: var(--streamify-text);
    }
    
    .progress-bar-container {
        margin-top: 10px;
        margin-bottom: 10px;
    }
    
    .progress {
        height: 6px;
        background-color: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        overflow: hidden;
    }
    
    .progress-bar {
        background: linear-gradient(to right, var(--streamify-primary), var(--streamify-secondary));
        transition: width 0.3s ease;
    }
    
    .watch-date {
        font-size: 0.85rem;
        color: var(--streamify-text-muted);
    }
    
    .progress-text {
        font-size: 0.85rem;
        color: var(--streamify-text-muted);
        margin-top: 5px;
    }
</style></head>
<body>
    <?php include "header.php"; ?>

    <!-- Main Content -->
    <div class="container" style="margin-top: 100px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="fw-bold">Watch History</h1>
            <span class="badge bg-primary rounded-pill"><?php echo count($history_items); ?> items</span>
        </div>

        <?php if (empty($history_items)): ?>
            <!-- Empty State -->
            <div class="empty-state text-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-clock-history mb-3" viewBox="0 0 16 16">
                    <path d="M8.515 1.019A7 7 0 0 0 8 1 7 7 0 0 0 7 1.019V0h1v1.019zM11 8a3 3 0 1 1-6 0 3 3 0 0 1 6 0zm-1 0a2 2 0 1 0-4 0 2 2 0 0 0 4 0z"/>
                    <path d="M7.5 7.5V9h3V7.5H9.5V5.5h-2V7.5H7.5z"/>
                    <path d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14zm0-1A6 6 0 1 1 8 2a6 6 0 0 1 0 12z"/>
                </svg>
                <h2 class="mb-3">Your watch history is empty</h2>
                <p class="text-muted mb-4">Start watching movies and TV shows to see them here</p>
                <a href="index.php" class="btn btn-streamify">Browse Content</a>
            </div>
        <?php else: ?>
            <!-- History Items -->
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">
                <?php foreach ($history_items as $item): 
                    $progress = floatval($item['progress_percent']);
                    $is_completed = $progress >= 100;
                    $watched_date = new DateTime($item['last_watched']);
                    $now = new DateTime();
                    $diff = $now->diff($watched_date);
                    
                    // Format date display
                    if ($diff->days == 0) {
                        $date_display = 'Today';
                    } elseif ($diff->days == 1) {
                        $date_display = 'Yesterday';
                    } elseif ($diff->days < 7) {
                        $date_display = $diff->days . ' days ago';
                    } else {
                        $date_display = $watched_date->format('M j, Y');
                    }
                ?>
                    <div class="col">
                        <div class="card h-100">
                            <div class="position-relative">
                                <img src="<?php echo htmlspecialchars($item['thumbnail_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($item['title']); ?>">
                                <?php if ($is_completed): ?>
                                    <span class="position-absolute top-0 end-0 m-2 badge bg-success">
                                        <i class="bi bi-check-circle me-1"></i>Completed
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title mb-0"><?php echo htmlspecialchars($item['title']); ?></h5>
                                    <span class="badge badge-streamify"><?php echo strtoupper(str_replace('_', ' ', $item['content_type'])); ?></span>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#ffc107" class="bi bi-star-fill me-1" viewBox="0 0 16 16">
                                        <path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
                                    </svg>
                                    <span class="text-warning me-2"><?php echo number_format($item['content_rating'], 1); ?></span>
                                    <?php if (!empty($item['categories'])): ?>
                                        <span class="text-muted"><?php echo htmlspecialchars($item['categories']); ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Progress Bar -->
                                <div class="progress-bar-container">
                                    <div class="progress">
                                        <div class="progress-bar" role="progressbar" style="width: <?php echo $progress; ?>%" aria-valuenow="<?php echo $progress; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <div class="progress-text">
                                        <?php if ($is_completed): ?>
                                            <i class="bi bi-check-circle-fill text-success me-1"></i>Completed
                                        <?php else: ?>
                                            <?php echo number_format($progress, 1); ?>% watched
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <p class="card-text text-muted mb-2"><?php echo substr(htmlspecialchars($item['description']), 0, 100); ?>...</p>
                                <p class="watch-date mb-0">
                                    <i class="bi bi-clock me-1"></i>Watched <?php echo $date_display; ?>
                                </p>
                            </div>
                            <div class="card-footer bg-transparent border-top-0">
                                <div class="d-flex justify-content-between">
                                    <a href="watch.php?id=<?php echo $item['content_id']; ?>" class="btn btn-sm btn-outline-light">
                                        <?php if ($is_completed): ?>
                                            <i class="bi bi-arrow-clockwise me-1"></i>Watch Again
                                        <?php else: ?>
                                            <i class="bi bi-play-circle me-1"></i>Continue Watching
                                        <?php endif; ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php include "footer.php"; ?>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

