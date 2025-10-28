<?php
// index.php (Dynamic Home - Hero Carousel)
require_once "db.php";

// Fetch top 3 featured movies (customize by adding is_featured or is_banner flag later)
$sql = "
SELECT *
FROM content
WHERE featured = 1
ORDER BY created_at DESC
LIMIT 5";
$result = $conn->query($sql);
$featured = $result->fetch_all(MYSQLI_ASSOC);

/* top-15 most-watched items with their category names */
// 🔥 TRENDING NOW
$trendSql = "
SELECT c.content_id,
       c.title,
       c.thumbnail_url,
       c.release_year,
       c.views,
       c.is_premium,
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

// Only attempt the query if the user is logged in
$continueWatching = [];
if (!empty($_SESSION['user_id'])) {

  $stmt = $conn->prepare("
    SELECT
    c.content_id,
    c.title,
    c.thumbnail_url,
    c.release_year,
    c.is_premium,
    wh.progress_percent,
    GROUP_CONCAT(cat.name ORDER BY cat.name SEPARATOR ', ')  AS categories,
    MAX(wh.last_watched) AS last_watched                    -- so we can sort later
FROM        watch_history       AS wh
JOIN        content             AS c   ON c.content_id = wh.content_id
LEFT JOIN   content_categories  AS cc  ON cc.content_id = c.content_id
LEFT JOIN   categories          AS cat ON cat.category_id  = cc.category_id
WHERE       wh.user_id = ?
  AND       wh.progress_percent BETWEEN 0 AND 99
GROUP BY    c.content_id, c.title, c.thumbnail_url, c.release_year, wh.progress_percent
ORDER BY    last_watched DESC
LIMIT 15;
  ");
  $stmt->bind_param('i', $_SESSION['user_id']);
  $stmt->execute();
  $continueWatching = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
}

// Fetch latest 5 new releases (customize as needed)
$newReleasesSql = "
SELECT c.content_id,
       c.title,
       c.description,
       c.thumbnail_url,
       c.release_year,
       c.is_premium,
       GROUP_CONCAT(cat.name ORDER BY cat.name SEPARATOR ', ') AS categories
FROM        content            AS c
LEFT JOIN   content_categories AS cc  ON cc.content_id  = c.content_id
LEFT JOIN   categories         AS cat ON cat.category_id = cc.category_id
GROUP BY    c.content_id
ORDER BY    c.release_year DESC, c.created_at DESC
LIMIT 10
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
  c.is_premium,
  GROUP_CONCAT(cat.name ORDER BY cat.name SEPARATOR ', ') AS categories
FROM content AS c
LEFT JOIN content_categories AS cc ON cc.content_id = c.content_id
LEFT JOIN categories AS cat ON cat.category_id = cc.category_id
GROUP BY c.content_id
ORDER BY c.rating DESC
LIMIT 5";
$topRatedResult = $conn->query($topRatedSql);
$topRated = $topRatedResult->fetch_all(MYSQLI_ASSOC);

// Fetch scheduled content
$scheduledSql = "
SELECT c.content_id,
       c.title,
       c.thumbnail_url,
       c.release_year,
       c.created_at,
       c.is_premium,
       c.schedule_date,
       GROUP_CONCAT(cat.name ORDER BY cat.name SEPARATOR ', ') AS categories
FROM content c
LEFT JOIN content_categories cc ON cc.content_id = c.content_id
LEFT JOIN categories cat ON cat.category_id = cc.category_id
WHERE c.is_scheduled = 1
GROUP BY c.content_id
ORDER BY c.created_at DESC
LIMIT 10";
$scheduledResult = $conn->query($scheduledSql);
$scheduledContent = $scheduledResult->fetch_all(MYSQLI_ASSOC);

// Fetch all categories for category-wise sections
$categoriesSql = "SELECT category_id, name FROM categories ORDER BY name";
$categoriesResult = $conn->query($categoriesSql);
$allCategories = $categoriesResult->fetch_all(MYSQLI_ASSOC);

// Fetch content for each category (limit 8 per category)
$categoryContent = [];
foreach ($allCategories as $category) {
    $catContentSql = "
    SELECT c.content_id, c.title, c.thumbnail_url, c.release_year, c.rating,c.is_premium
    FROM content c
    JOIN content_categories cc ON c.content_id = cc.content_id
    WHERE cc.category_id = ?
    ORDER BY c.views DESC
    LIMIT 8";
    $stmt = $conn->prepare($catContentSql);
    $stmt->bind_param("i", $category['category_id']);
    $stmt->execute();
    $categoryContent[$category['category_id']] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Streamify - Home</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap"
    rel="stylesheet" />
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
    
    /* Hero Carousel Styles */
    #heroCarousel {
      border-radius: 20px;
      overflow: hidden;
      margin-bottom: 3rem;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    }
    
    .carousel-inner {
      border-radius: 20px;
    }
    
    .carousel-item {
      height: 70vh;
      min-height: 500px;
    }
    
    .carousel-item::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(to bottom, rgba(28, 15, 36, 0.2) 0%, rgba(28, 15, 36, 0.8) 100%);
      z-index: 1;
    }
    
    .carousel-caption {
      bottom: 30%;
      z-index: 2;
      text-align: left;
      max-width: 600px;
    }
    
    .carousel-caption h1 {
      font-size: 3.5rem;
      font-weight: 800;
      margin-bottom: 1rem;
      text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
    }
    
    .carousel-caption p {
      font-size: 1.2rem;
      margin-bottom: 2rem;
      text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.7);
    }
    
    .carousel-indicators button {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      margin: 0 5px;
      background-color: var(--streamify-primary);
    }
    
    .carousel-control-prev,
    .carousel-control-next {
      width: 5%;
      opacity: 0.7;
      transition: opacity 0.3s ease;
    }
    
    .carousel-control-prev:hover,
    .carousel-control-next:hover {
      opacity: 1;
    }
    
    .carousel-control-prev-icon,
    .carousel-control-next-icon {
      background-color: var(--streamify-primary);
      border-radius: 50%;
      width: 40px;
      height: 40px;
      background-size: 60%;
    }
    
    /* Section Titles */
    .section-title {
      color: var(--streamify-text);
      font-size: 1.8rem;
      margin: 3rem 0 1.5rem;
      padding-bottom: 0.5rem;
      border-bottom: 2px solid var(--streamify-primary);
      display: inline-block;
    }
    
    .category-title {
      color: var(--streamify-text);
      font-size: 1.5rem;
      margin: 2rem 0 1rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .category-title a {
      color: var(--streamify-primary);
      text-decoration: none;
      font-size: 1rem;
    }
    
    .category-title a:hover {
      color: var(--streamify-secondary);
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
    
    /* Content Cards */
    .content-card {
      background: var(--streamify-card-bg);
      border: 1px solid var(--streamify-card-border);
      border-radius: 15px;
      overflow: hidden;
      transition: all 0.3s ease;
      height: 100%;
      position: relative;
      cursor: pointer;
    }
    
    .content-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
      border-color: var(--streamify-primary);
    }
    
    .content-card img {
      height: 100%;
      object-fit: cover;
      transition: transform 0.3s ease;
      width: 100%;
    }
    
    .content-card:hover img {
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
    
.release-date-badge {
  position: absolute;
  top: 12px;
  left: 12px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 0.4rem 1rem;
  border-radius: 25px;
  font-size: 0.75rem;
  font-weight: 700;
  z-index: 2;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
  border: 1px solid rgba(255, 255, 255, 0.2);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  backdrop-filter: blur(10px);
  animation: pulse-glow 2s infinite;
}

.premium-badge {
  position: absolute;
  top: 12px;
  right: 12px;
  background: linear-gradient(135deg, #ffd700 0%, #ff6b00 100%);
  color: #1c0f24;
  padding: 0.4rem 1rem;
  border-radius: 25px;
  font-size: 0.75rem;
  font-weight: 800;
  z-index: 2;
  box-shadow: 0 4px 15px rgba(255, 215, 0, 0.4);
  border: 1px solid rgba(255, 255, 255, 0.3);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  backdrop-filter: blur(10px);
  animation: shimmer 3s infinite;
}

/* Add these animations to your existing CSS */
@keyframes pulse-glow {
  0%, 100% {
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    transform: scale(1);
  }
  50% {
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
    transform: scale(1.05);
  }
}

@keyframes shimmer {
  0%, 100% {
    background: linear-gradient(135deg, #ffd700 0%, #ff6b00 100%);
    box-shadow: 0 4px 15px rgba(255, 215, 0, 0.4);
  }
  50% {
    background: linear-gradient(135deg, #ffed4e 0%, #ff8533 100%);
    box-shadow: 0 6px 20px rgba(255, 215, 0, 0.6);
  }
}

/* Optional: Add hover effects */
.release-date-badge:hover {
  transform: scale(1.1);
  transition: transform 0.3s ease;
}

.premium-badge:hover {
  transform: scale(1.1) rotate(5deg);
  transition: transform 0.3s ease;
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
    
    /* Buttons */
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
    
    .btn-remind-me {
      background: rgba(177, 59, 255, 0.2);
      border: 1px solid var(--streamify-primary);
      color: var(--streamify-primary);
      padding: 0.5rem 1rem;
      border-radius: 50px;
      font-size: 0.8rem;
      transition: all 0.3s ease;
      width: 100%;
      margin-top: 0.5rem;
    }
    
    .btn-remind-me:hover {
      background: var(--streamify-primary);
      color: white;
    }
    
    .btn-remind-me.reminder-set {
      background: var(--streamify-primary);
      color: white;
    }
    
    /* Progress bar */
    .progress {
      background-color: rgba(255, 255, 255, 0.2);
      border-radius: 0;
      height: 4px;
    }
    
    .progress-bar {
      background-color: var(--streamify-primary);
    }
    
    /* Alert styling */
    .alert {
      border: none;
      border-radius: 10px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }
    
    /* Footer */
    .footer {
      background: rgba(28, 15, 36, 0.95);
      border-top: 1px solid rgba(177, 59, 255, 0.2);
      padding: 3rem 0 1.5rem;
      margin-top: 4rem;
    }
    
    .footer h5 {
      color: var(--streamify-text);
      font-weight: 600;
      margin-bottom: 1rem;
    }
    
    .footer a {
      color: var(--streamify-text-muted);
      text-decoration: none;
      transition: color 0.3s ease;
    }
    
    .footer a:hover {
      color: var(--streamify-primary);
    }
    
    .social-links a {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 40px;
      height: 40px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
      margin-right: 10px;
      transition: all 0.3s ease;
    }
    
    .social-links a:hover {
      background: var(--streamify-primary);
      transform: translateY(-3px);
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
      .carousel-item {
        height: 50vh;
        min-height: 400px;
      }
      
      .carousel-caption {
        bottom: 20%;
      }
      
      .carousel-caption h1 {
        font-size: 2.5rem;
      }
      
      .carousel-caption p {
        font-size: 1rem;
      }
      
      .scroll-item {
        width: 220px;
      }
      
      .section-title {
        font-size: 1.5rem;
      }
    }
    
    @media (max-width: 576px) {
      .carousel-item {
        height: 40vh;
        min-height: 300px;
      }
      
      .carousel-caption h1 {
        font-size: 2rem;
      }
      
      .scroll-item {
        width: 180px;
      }
      
      .content-card img {
        height: 120px;
      }
    }
  </style>
</head>

<body class="pt-5">
  <!-- Header Section -->
  <?php include "header.php"; ?>
  
  <!-- Logout Success Message -->
  <?php if (isset($_GET['logout']) && $_GET['logout'] === 'success'): ?>
    <div class="alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-5" style="z-index: 1060;" role="alert">
      <i class="bi bi-check-circle me-2"></i>You have been successfully logged out.
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>
  
  <!-- Hero Carousel  -->
  <div class="container mt-4">
    <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
      <!-- Indicators -->
      <div class="carousel-indicators">
        <?php foreach ($featured as $index => $item): ?>
          <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?= $index ?>"
            class="<?= $index === 0 ? 'active' : '' ?>"></button>
        <?php endforeach; ?>
      </div>

      <!-- Slides -->
      <div class="carousel-inner rounded-3 overflow-hidden">
        <?php foreach ($featured as $index => $item): ?>
          <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
            <img src="<?= htmlspecialchars("admin/".$item['banner_url']) ?>" class="d-block w-100 h-100" alt="<?= htmlspecialchars($item['title']) ?>" style="object-fit: cover;">
            <div class="carousel-caption">
              <h1><?= htmlspecialchars($item['title']) ?></h1>
              <p class="d-none d-md-block"><?= htmlspecialchars(substr($item['description'], 0, 150)) ?>...</p>
              <div class="d-flex gap-3">
                <a href="watch.php?id=<?= $item['content_id'] ?>" class="btn btn-streamify px-4">
                  <i class="bi bi-play-fill me-2"></i>Watch Now
                </a>
                <?php if ($item['is_premium']): ?>
                  <span class="badge bg-warning text-dark align-self-center px-3 py-2">
                    <i class="bi bi-star-fill me-1"></i>Premium
                  </span>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Controls -->
      <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
      </button>
    </div>
  </div>

  <!-- Content Sections -->
  <div class="container">
    <!-- Scheduled Releases -->
    <?php if (!empty($scheduledContent)): ?>
      <h3 class="section-title fw-bold">Coming Soon <i class="bi bi-calendar-event"></i></h3>
      <div class="scroll-container">
        
        <?php foreach ($scheduledContent as $row): ?>
          <div class="scroll-item">
            <div class="card content-card" onclick="window.location.href='watch.php?id=<?= $row['content_id'] ?>'">
              <span class="release-date-badge">
                <?= date('M j', strtotime($row['schedule_date'])) ?>
              </span>
              <?php if ($row['is_premium']): ?>
                <span class="premium-badge">Premium</span>
              <?php endif; ?>
              
              <img src="<?= htmlspecialchars("admin/" . $row['thumbnail_url'] ?: 'default-thumbnail.jpg') ?>" class="card-img-top"
                alt="<?= htmlspecialchars($row['title']) ?>">

              <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($row['title']) ?></h5>
                <p class="card-text">
                  <?= htmlspecialchars($row['categories']) ?> |
                  <?= $row['release_year'] ?>
                </p>
                
                <button class="btn btn-remind-me" 
                        onclick="event.stopPropagation(); setReminder(<?= $row['content_id'] ?>, this)"
                        data-content-id="<?= $row['content_id'] ?>">
                  <i class="bi bi-bell me-1"></i> Remind Me
                </button>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Trending Now -->
    <h3 class="section-title fw-bold">Trending Now <i class="bi bi-fire"></i></h3>
    <div class="scroll-container">
      <?php foreach ($trending as $row): ?>
        <div class="scroll-item">
          <div class="card content-card" onclick="window.location.href='watch.php?id=<?= $row['content_id'] ?>'">
            <?php if ($row['is_premium']): ?>
              <span class="premium-badge">Premium</span>
            <?php endif; ?>

            <?php
            /* 🎯 dynamic badge rules */
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

            <img src="<?= htmlspecialchars("admin/" .$row['thumbnail_url'] ?: 'default-thumbnail.jpg') ?>" class="card-img-top"
              alt="<?= htmlspecialchars($row['title']) ?>">

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

    <?php if (!empty($continueWatching)): ?>
      <!-- Continue Watching -->
      <h3 class="section-title fw-bold">
        Continue Watching <i class="bi bi-arrow-clockwise"></i>
      </h3>
      <div class="scroll-container">
        <?php foreach ($continueWatching as $row): ?>
          <div class="scroll-item">
            <div class="card content-card" onclick="window.location.href='watch.php?id=<?= $row['content_id'] ?>&resume=1'">
              <?php if ($row['is_premium']): ?>
                <span class="premium-badge">Premium</span>
              <?php endif; ?>

              <img src="<?= htmlspecialchars("admin/" .$row['thumbnail_url'] ?: 'default-thumbnail.jpg') ?>" class="card-img-top"
                alt="<?= htmlspecialchars($row['title']) ?>">

              <!-- ✅ Progress bar BELOW the image -->
              <div class="progress w-100" style="height: 4px; margin-top: -4px;">
                <div class="progress-bar" role="progressbar"
                  style="width: <?= (float) $row['progress_percent'] ?>%">
                </div>
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
    <?php endif; ?>

    <!-- New Releases -->
    <h3 class="section-title fw-bold">New Releases <i class="bi bi-stars"></i></h3>
    <div class="scroll-container">
      <?php foreach ($newReleases as $row): ?>
        <div class="scroll-item">
          <div class="card content-card" onclick="window.location.href='watch.php?id=<?= $row['content_id'] ?>'">
            <span class="card-badge">NEW</span>
            <?php if ($row['is_premium']): ?>
              <span class="premium-badge">Premium</span>
            <?php endif; ?>
            
            <img src="<?= htmlspecialchars("admin/" .$row['thumbnail_url'] ?: 'default-thumbnail.jpg') ?>" class="card-img-top"
              alt="<?= htmlspecialchars($row['title']) ?>">
            <div class="card-body">
              <h5 class="card-title"><?= htmlspecialchars($row['title']) ?></h5>
              <p class="card-text"><?= htmlspecialchars($row['categories']) ?> | <?= $row['release_year'] ?></p>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    
    <!-- Top Rated -->
    <h3 class="section-title fw-bold">Top Rated <i class="bi bi-star-fill"></i></h3>
    <div class="scroll-container">
      <?php foreach ($topRated as $row): ?>
        <div class="scroll-item">
          <div class="card content-card" onclick="window.location.href='watch.php?id=<?= $row['content_id'] ?>'">
            <span class="card-badge">★ <?= htmlspecialchars($row['rating']) ?></span>
            <?php if ($row['is_premium']): ?>
              <span class="premium-badge">Premium</span>
            <?php endif; ?>
            
            <img src="<?= htmlspecialchars("admin/" .$row['thumbnail_url'] ?: 'default-thumbnail.jpg') ?>" class="card-img-top" alt="<?= htmlspecialchars($row['title']) ?>">
            <div class="card-body">
              <h5 class="card-title"><?= htmlspecialchars($row['title']) ?></h5>
              <p class="card-text"><?= htmlspecialchars($row['categories']) ?> | <?= $row['release_year'] ?></p>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Category-wise Sections -->
    <?php foreach ($allCategories as $category): ?>
      <?php if (!empty($categoryContent[$category['category_id']])): ?>
        <div class="category-title">
          <span class="section-title"><?= htmlspecialchars($category['name']) ?></span>
          <a href="category.php?id=<?= $category['category_id'] ?>">
            View All <i class="bi bi-arrow-right"></i>
          </a>
        </div>
        <div class="scroll-container">
          <?php foreach ($categoryContent[$category['category_id']] as $row): ?>
            <div class="scroll-item">
              <div class="card content-card" onclick="window.location.href='watch.php?id=<?= $row['content_id'] ?>'">
                <?php if ($row['is_premium']): ?>
                  <span class="premium-badge">Premium</span>
                <?php endif; ?>
                
                <img src="<?= htmlspecialchars("admin/" .$row['thumbnail_url'] ?: 'default-thumbnail.jpg') ?>" class="card-img-top" alt="<?= htmlspecialchars($row['title']) ?>">
                <div class="card-body">
                  <h5 class="card-title"><?= htmlspecialchars($row['title']) ?></h5>
                  <p class="card-text">
                    ★ <?= htmlspecialchars($row['rating']) ?> | <?= $row['release_year'] ?>
                  </p>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>

  <!-- Footer -->
  <?php include "footer.php" ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.plyr.io/3.7.8/plyr.polyfilled.js"></script>
  <script>
    // Auto-play video when in viewport
    document.addEventListener('DOMContentLoaded', function () {
      const heroVideo = document.querySelector('.hero-video');

      // Try to autoplay the video
      if (heroVideo) {
        heroVideo.play().catch(error => {
          // If autoplay fails, show a play button
          console.log('Autoplay prevented:', error);
        });
      }

      // Add hover effects to cards
      const cards = document.querySelectorAll('.content-card');
      cards.forEach(card => {
        card.addEventListener('mouseenter', function () {
          this.style.transform = 'translateY(-10px)';
        });
        card.addEventListener('mouseleave', function () {
          this.style.transform = 'translateY(0)';
        });
      });
      
      // Auto-dismiss alert after 3 seconds
      const alert = document.querySelector('.alert');
      if (alert) {
        setTimeout(() => {
          const bsAlert = new bootstrap.Alert(alert);
          bsAlert.close();
        }, 3000);
      }
      
      // Check for existing reminders and update button states
      checkExistingReminders();
    });

    // Check for existing reminders
    function checkExistingReminders() {
      <?php if (isset($_SESSION['user_id'])): ?>
        fetch('check_reminders.php')
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              data.reminders.forEach(contentId => {
                const button = document.querySelector(`.btn-remind-me[data-content-id="${contentId}"]`);
                if (button) {
                  button.innerHTML = '<i class="bi bi-bell-fill me-1"></i> Reminder Set';
                  button.classList.add('reminder-set');
                }
              });
            }
          })
          .catch(error => console.error('Error checking reminders:', error));
      <?php endif; ?>
    }

    // Set reminder function
    function setReminder(contentId, button) {
      <?php if (!isset($_SESSION['user_id'])): ?>
        showNotification('Please login to set reminders', 'warning');
        return;
      <?php endif; ?>

      const formData = new FormData();
      formData.append('content_id', contentId);

      fetch('set_reminder.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          if (data.action === 'set') {
            button.innerHTML = '<i class="bi bi-bell-fill me-1"></i> Reminder Set';
            button.classList.add('reminder-set');
            showNotification('Reminder set successfully!', 'success');
          } else {
            button.innerHTML = '<i class="bi bi-bell me-1"></i> Remind Me';
            button.classList.remove('reminder-set');
            showNotification('Reminder removed', 'info');
          }
        } else {
          showNotification(data.message || 'Error setting reminder', 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showNotification('Network error occurred', 'error');
      });
    }

    // Show notification function
    function showNotification(message, type = 'info') {
      // Remove existing notifications
      const existingNotifications = document.querySelectorAll('.custom-notification');
      existingNotifications.forEach(notification => notification.remove());

      const notification = document.createElement('div');
      notification.className = `custom-notification position-fixed top-0 end-0 m-3 alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show`;
      notification.style.zIndex = '1060';
      notification.innerHTML = `
        <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : type === 'error' ? 'x-circle' : 'info-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
      `;
      
      document.body.appendChild(notification);
      
      // Auto-remove after 5 seconds
      setTimeout(() => {
        if (notification.parentNode) {
          notification.remove();
        }
      }, 5000);
    }
  </script>
</body>
<script>
// Run notification checks every 30 minutes (1,800,000 milliseconds)
setInterval(() => {
    fetch("scheduled_notifications.php")
        .then(response => response.json())
        .then(data => {
            console.log("Auto notification check completed:", data);
        })
        .catch(error => {
            console.error("Notification check failed:", error);
        });
}, 1800000); // 30 minutes = 1,800,000 milliseconds

// Run immediately when page loads
document.addEventListener('DOMContentLoaded', function() {
    fetch("scheduled_notifications.php")
        .then(response => response.json())
        .then(data => {
            console.log("Initial notification check completed:", data);
        })
        .catch(error => {
            console.error("Initial notification check failed:", error);
        });
});
</script>
</html>
<?php $conn->close(); ?>