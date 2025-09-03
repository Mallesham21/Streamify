<?php
require_once 'header.php';
require_once 'connect.php';

// Function to fetch content with categories
function getContentWithCategories($conn, $type = null, $limit = 5) {
    $query = "SELECT c.*, GROUP_CONCAT(cat.name SEPARATOR ', ') as categories 
              FROM content c
              LEFT JOIN content_categories cc ON c.content_id = cc.content_id
              LEFT JOIN categories cat ON cc.category_id = cat.category_id";
    
    if ($type) {
        $query .= " WHERE c.content_type = '$type'";
    }
    
    $query .= " GROUP BY c.content_id ORDER BY c.created_at DESC LIMIT $limit";
    
    $result = $conn->query($query);
    $content = [];
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $content[] = $row;
        }
    }
    
    return $content;
}

// Fetch different content sections
$trendingContent = getContentWithCategories($conn, null, 6);
$continueWatching = [];
$newReleases = getContentWithCategories($conn, null, 5);
$recommended = getContentWithCategories($conn, null, 5);

// For logged in users, fetch their watch history
if(isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $query = "SELECT wh.*, c.title, c.description, c.thumbnail_url, c.content_type, 
                     GROUP_CONCAT(cat.name SEPARATOR ', ') as categories
              FROM watch_history wh
              JOIN content c ON wh.content_id = c.content_id
              LEFT JOIN content_categories cc ON c.content_id = cc.content_id
              LEFT JOIN categories cat ON cc.category_id = cat.category_id
              WHERE wh.user_id = $userId
              GROUP BY wh.history_id
              ORDER BY wh.last_watched DESC
              LIMIT 5";
    
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $continueWatching[] = $row;
        }
    }
}
?>

<!-- Hero Carousel -->
<div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
    <!-- Indicators -->
    <div class="carousel-indicators">
        <?php for($i = 0; $i < 3; $i++): ?>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?= $i ?>" 
                    class="<?= $i === 0 ? 'active' : '' ?> bg-primary"></button>
        <?php endfor; ?>
    </div>
    
    <!-- Slides -->
    <div class="carousel-inner rounded-3 overflow-hidden" style="height: 70vh;">
        <?php 
        $featuredContent = array_slice($trendingContent, 0, 3);
        foreach($featuredContent as $index => $content): 
        ?>
        <div class="carousel-item <?= $index === 0 ? 'active' : '' ?> h-100">
            <div class="w-100 h-100 d-flex align-items-end" 
                 style="background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.7)), url('<?= htmlspecialchars($content['thumbnail_url']) ?>') center/cover;">
                <div class="container text-white pb-5 mb-5">
                    <h1 class="display-4 fw-bold"><?= htmlspecialchars($content['title']) ?></h1>
                    <p class="lead"><?= htmlspecialchars($content['description']) ?></p>
                    <div class="d-flex gap-3">
                        <a href="watch.php?id=<?= $content['content_id'] ?>" class="btn btn-primary btn-lg px-4">
                            <i class="bi bi-play-fill me-2"></i>Watch Now
                        </a>
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <form action="watchlist.php" method="post" class="d-inline">
                                <input type="hidden" name="content_id" value="<?= $content['content_id'] ?>">
                                <button type="submit" class="btn btn-outline-light btn-lg px-4" name="add_to_watchlist">
                                    <i class="bi bi-plus me-2"></i>My List
                                </button>
                            </form>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-outline-light btn-lg px-4">
                                <i class="bi bi-plus me-2"></i>My List
                            </a>
                        <?php endif; ?>
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
        <?php foreach($trendingContent as $content): ?>
        <div class="scroll-item">
            <div class="card content-card">
                <span class="card-badge">NEW</span>
                <img src="<?= htmlspecialchars($content['thumbnail_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($content['title']) ?>">
                <div class="card-hover-actions">
                    <a href="watch.php?id=<?= $content['content_id'] ?>" class="btn btn-primary btn-sm"><i class="bi bi-play-fill"></i> Play</a>
                    <a href="details.php?id=<?= $content['content_id'] ?>" class="btn btn-outline-light btn-sm"><i class="bi bi-info-circle"></i> Details</a>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <form action="watchlist.php" method="post" class="d-inline">
                            <input type="hidden" name="content_id" value="<?= $content['content_id'] ?>">
                            <button type="submit" class="btn btn-outline-light btn-sm" name="add_to_watchlist"><i class="bi bi-plus"></i> Add</button>
                        </form>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline-light btn-sm"><i class="bi bi-plus"></i> Add</a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($content['title']) ?></h5>
                    <p class="card-text"><?= htmlspecialchars($content['categories']) ?> | <?= $content['release_year'] ?></p>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Continue Watching (only shown if user is logged in) -->
    <?php if(!empty($continueWatching)): ?>
    <h3 class="section-title fw-bold">Continue Watching <i class="bi bi-arrow-clockwise"></i></h3>
    <div class="scroll-container">
        <?php foreach($continueWatching as $content): ?>
        <div class="scroll-item">
            <div class="card content-card">
                <div class="progress position-absolute top-0 start-0 w-100" style="height: 4px;">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $content['progress_percent'] ?>%"></div>
                </div>
                <img src="<?= htmlspecialchars($content['thumbnail_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($content['title']) ?>">
                <div class="card-hover-actions">
                    <a href="watch.php?id=<?= $content['content_id'] ?>" class="btn btn-primary btn-sm"><i class="bi bi-play-fill"></i> Continue</a>
                    <a href="details.php?id=<?= $content['content_id'] ?>" class="btn btn-outline-light btn-sm"><i class="bi bi-info-circle"></i> Details</a>
                    <form action="watch_history.php" method="post" class="d-inline">
                        <input type="hidden" name="content_id" value="<?= $content['content_id'] ?>">
                        <button type="submit" class="btn btn-outline-light btn-sm" name="remove_history"><i class="bi bi-x"></i> Remove</button>
                    </form>
                </div>
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($content['title']) ?></h5>
                    <p class="card-text"><?= htmlspecialchars($content['categories']) ?> | <?= $content['release_year'] ?></p>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- New Releases -->
    <h3 class="section-title fw-bold">New Releases <i class="bi bi-stars"></i></h3>
    <div class="scroll-container">
        <?php foreach($newReleases as $content): ?>
        <div class="scroll-item">
            <div class="card content-card">
                <span class="card-badge">NEW</span>
                <img src="<?= htmlspecialchars($content['thumbnail_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($content['title']) ?>">
                <div class="card-hover-actions">
                    <a href="watch.php?id=<?= $content['content_id'] ?>" class="btn btn-primary btn-sm"><i class="bi bi-play-fill"></i> Play</a>
                    <a href="details.php?id=<?= $content['content_id'] ?>" class="btn btn-outline-light btn-sm"><i class="bi bi-info-circle"></i> Details</a>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <form action="watchlist.php" method="post" class="d-inline">
                            <input type="hidden" name="content_id" value="<?= $content['content_id'] ?>">
                            <button type="submit" class="btn btn-outline-light btn-sm" name="add_to_watchlist"><i class="bi bi-plus"></i> Add</button>
                        </form>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline-light btn-sm"><i class="bi bi-plus"></i> Add</a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($content['title']) ?></h5>
                    <p class="card-text"><?= htmlspecialchars($content['categories']) ?> | <?= $content['release_year'] ?></p>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Recommended for You -->
    <h3 class="section-title fw-bold">Recommended for You <i class="bi bi-lightbulb"></i></h3>
    <div class="scroll-container">
        <?php foreach($recommended as $content): ?>
        <div class="scroll-item">
            <div class="card content-card">
                <img src="<?= htmlspecialchars($content['thumbnail_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($content['title']) ?>">
                <div class="card-hover-actions">
                    <a href="watch.php?id=<?= $content['content_id'] ?>" class="btn btn-primary btn-sm"><i class="bi bi-play-fill"></i> Play</a>
                    <a href="details.php?id=<?= $content['content_id'] ?>" class="btn btn-outline-light btn-sm"><i class="bi bi-info-circle"></i> Details</a>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <form action="watchlist.php" method="post" class="d-inline">
                            <input type="hidden" name="content_id" value="<?= $content['content_id'] ?>">
                            <button type="submit" class="btn btn-outline-light btn-sm" name="add_to_watchlist"><i class="bi bi-plus"></i> Add</button>
                        </form>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline-light btn-sm"><i class="bi bi-plus"></i> Add</a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($content['title']) ?></h5>
                    <p class="card-text"><?= htmlspecialchars($content['categories']) ?> | <?= $content['release_year'] ?></p>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once 'footer.php'; ?>