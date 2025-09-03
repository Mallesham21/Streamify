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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Streamify - Home</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap"
    rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link rel="stylesheet" href="style.css">
</head>

<body>
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
  <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
    <!-- Indicators -->
    <div class="carousel-indicators">
      <?php foreach ($featured as $index => $item): ?>
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?= $index ?>"
          class="<?= $index === 0 ? 'active' : '' ?> bg-primary"></button>
      <?php endforeach; ?>
    </div>

    <!-- Slides -->
    <div class="carousel-inner rounded-3 overflow-hidden" style="height: 70vh;">
      <?php foreach ($featured as $index => $item): ?>
        <div class="carousel-item <?= $index === 0 ? 'active' : '' ?> h-100">
          <div class="w-100 h-100 d-flex align-items-end"
            style="background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.7)), url('<?= htmlspecialchars($item['thumbnail_url']) ?>') center/cover;">
            <div class="container text-white pb-5 mb-5">
              <h1 class="display-4 fw-bold"><?= htmlspecialchars($item['title']) ?></h1>
              <p class="lead"><?= htmlspecialchars($item['description']) ?></p>
              <div class="d-flex gap-3">
                <a href="watch.php?id=<?= $item['content_id'] ?>" class="btn btn-primary btn-lg px-4">
                  <i class="bi bi-play-fill me-2"></i>Watch Now
                </a>
                <button class="btn btn-outline-light btn-lg px-4">
                  <i class="bi bi-plus me-2"></i>My List
                </button>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Controls -->
    <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
      <span class="carousel-control-prev-icon bg-primary rounded-circle p-3" aria-hidden="true"></span>
      <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
      <span class="carousel-control-next-icon bg-primary rounded-circle p-3" aria-hidden="true"></span>
      <span class="visually-hidden">Next</span>
    </button>
  </div>

  <!-- Content Sections -->
  <div class="container">
    <!-- Trending Now -->
    <h3 class="section-title fw-bold">Trending Now <i class="bi bi-fire"></i></h3>
    <div class="scroll-container">
      <?php foreach ($trending as $row): ?>
        <div class="scroll-item">
          <div class="card content-card">

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

            <img src="<?= htmlspecialchars($row['thumbnail_url'] ?: 'default-thumbnail.jpg') ?>" class="card-img-top"
              alt="<?= htmlspecialchars($row['title']) ?>">

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

    <?php if (!empty($continueWatching)): ?>
      <!-- Continue Watching -->
      <h3 class="section-title fw-bold">
        Continue Watching <i class="bi bi-arrow-clockwise"></i>
      </h3>
      <div class="scroll-container">
        <?php foreach ($continueWatching as $row): ?>
          <div class="scroll-item">
            <div class="card content-card">

              <img src="<?= htmlspecialchars($row['thumbnail_url'] ?: 'default-thumbnail.jpg') ?>" class="card-img-top"
                alt="<?= htmlspecialchars($row['title']) ?>">

              <!-- ✅ Progress bar BELOW the image -->
              <div class="progress w-100" style="height: 4px; margin-top: -4px;">
                <div class="progress-bar bg-primary" role="progressbar"
                  style="width: <?= (float) $row['progress_percent'] ?>%">
                </div>
              </div>

              <div class="card-hover-actions">
                <!-- Continue from last position -->
                <a href="watch.php?id=<?= $row['content_id'] ?>&resume=1" class="btn btn-primary btn-sm">
                  <i class="bi bi-play-fill"></i> Continue
                </a>

                <!-- Remove from list (optional AJAX) -->
                <button class="btn btn-outline-light btn-sm" data-remove-id="<?= $row['content_id'] ?>">
                  <i class="bi bi-x"></i> Remove
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
    <?php endif; ?>

    <!-- New Releases -->
    <h3 class="section-title fw-bold">New Releases <i class="bi bi-stars"></i></h3>
    <div class="scroll-container">
      <?php foreach ($newReleases as $row): ?>
        <div class="scroll-item">
          <div class="card content-card">
            <span class="card-badge">NEW</span>
            <img src="<?= htmlspecialchars($row['thumbnail_url'] ?: 'default-thumbnail.jpg') ?>" class="card-img-top"
              alt="<?= htmlspecialchars($row['title']) ?>">
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
    <!-- Top Rated -->
<h3 class="section-title fw-bold">Top Rated <i class="bi bi-star-fill"></i></h3>
<div class="scroll-container">
  <?php foreach ($topRated as $row): ?>
    <div class="scroll-item">
      <div class="card content-card">
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

  <!-- Footer -->
  <?php include "footer.php" ?>


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Auto-play video when in viewport
    document.addEventListener('DOMContentLoaded', function () {
      const heroVideo = document.querySelector('.hero-video');

      // Try to autoplay the video
      heroVideo.play().catch(error => {
        // If autoplay fails, show a play button
        console.log('Autoplay prevented:', error);
      });

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
    });
  </script>
</body>

</html>