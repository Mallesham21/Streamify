<?php
require_once "db.php";

// Get popular content (based on views and ratings)
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 24;
$offset = ($page - 1) * $limit;

// Get total popular content count
$total_sql = "SELECT COUNT(*) as total FROM content WHERE views > 1000";
$total_result = $conn->query($total_sql);
$total_content = $total_result ? $total_result->fetch_assoc()['total'] : 0;

// Get popular content (movies and TV shows with highest views and ratings)
$popular_sql = "
    SELECT c.content_id, c.title, c.description, CONCAT('admin/', c.thumbnail_url) AS thumbnail_url, 
           c.banner_url, c.rating, c.release_year, c.views, c.featured, c.is_premium, c.content_type,
           GROUP_CONCAT(cat.name ORDER BY cat.name SEPARATOR ', ') as categories
    FROM content c
    LEFT JOIN content_categories cc ON c.content_id = cc.content_id
    LEFT JOIN categories cat ON cc.category_id = cat.category_id
    WHERE c.views > 1000
    GROUP BY c.content_id
    ORDER BY c.views DESC, c.rating DESC
    LIMIT ? OFFSET ?
";

$stmt = $conn->prepare($popular_sql);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
$popular_content = $result->fetch_all(MYSQLI_ASSOC);

// Get trending content (recent popular content)
$trending_sql = "
    SELECT c.content_id, c.title, CONCAT('admin/', c.thumbnail_url) AS thumbnail_url, 
           c.rating, c.release_year, c.views, c.is_premium, c.content_type,
           GROUP_CONCAT(cat.name ORDER BY cat.name SEPARATOR ', ') as categories
    FROM content c
    LEFT JOIN content_categories cc ON c.content_id = cc.content_id
    LEFT JOIN categories cat ON cc.category_id = cat.category_id
    WHERE c.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY c.content_id
    ORDER BY c.views DESC
    LIMIT 10
";

$trending_result = $conn->query($trending_sql);
$trending_content = $trending_result ? $trending_result->fetch_all(MYSQLI_ASSOC) : [];

// Get top rated content
$top_rated_sql = "
    SELECT c.content_id, c.title, CONCAT('admin/', c.thumbnail_url) AS thumbnail_url, 
           c.rating, c.release_year, c.views, c.is_premium, c.content_type,
           GROUP_CONCAT(cat.name ORDER BY cat.name SEPARATOR ', ') as categories
    FROM content c
    LEFT JOIN content_categories cc ON c.content_id = cc.content_id
    LEFT JOIN categories cat ON cc.category_id = cat.category_id
    WHERE c.rating >= 8.0
    GROUP BY c.content_id
    ORDER BY c.rating DESC
    LIMIT 10
";

$top_rated_result = $conn->query($top_rated_sql);
$top_rated_content = $top_rated_result ? $top_rated_result->fetch_all(MYSQLI_ASSOC) : [];

// Calculate total pages
$total_pages = ceil($total_content / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Popular & Trending | Streamify</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
    
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
        
        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, rgba(177, 59, 255, 0.1) 0%, rgba(0, 204, 255, 0.1) 100%);
            border-radius: 20px;
            padding: 3rem 2rem;
            margin-bottom: 3rem;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="%23ffffff" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="%23ffffff" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="%23ffffff" opacity="0.1"/><circle cx="10" cy="60" r="0.5" fill="%23ffffff" opacity="0.1"/><circle cx="90" cy="40" r="0.5" fill="%23ffffff" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
            pointer-events: none;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
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
        
        .movie-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: linear-gradient(135deg, #b13bff 0%, #6a11cb 100%);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            z-index: 2;
        }
        
        .tv-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: linear-gradient(135deg, #00ccff 0%, #0066ff 100%);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            z-index: 2;
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
        
        /* Stats Cards */
        .stats-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .stats-card:hover {
            background: rgba(255, 255, 255, 0.08);
            transform: translateY(-5px);
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: 800;
            color: var(--streamify-primary);
        }
        
        .stats-label {
            color: var(--streamify-text-muted);
            font-size: 0.9rem;
        }
        
        /* Grid Layout */
        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }
        
        .grid-item {
            transition: transform 0.3s ease;
        }
        
        .grid-item:hover {
            transform: translateY(-5px);
        }
        
        /* Pagination */
        .pagination {
            justify-content: center;
            margin: 2rem 0;
        }
        
        .page-link {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--streamify-text);
            margin: 0 0.2rem;
            border-radius: 10px;
        }
        
        .page-link:hover {
            background: var(--streamify-primary);
            border-color: var(--streamify-primary);
            color: white;
        }
        
        .page-item.active .page-link {
            background: var(--streamify-primary);
            border-color: var(--streamify-primary);
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .scroll-item {
                width: 220px;
            }
            
            .content-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 1rem;
            }
            
            .hero-section {
                padding: 2rem 1rem;
            }
            
            .section-title {
                font-size: 1.5rem;
            }
        }
        
        @media (max-width: 576px) {
            .scroll-item {
                width: 180px;
            }
            
            .content-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
            
            .content-card img {
                height: 120px;
            }
        }
    </style>
</head>
<body class="pt-5">
    <?php include "header.php"; ?>

    <div class="container mt-4">
        <!-- Hero Section -->
        <div class="hero-section">
            <div class="hero-content text-center">
                <h1 class="display-4 fw-bold mb-3">
                    <i class="bi bi-fire me-3"></i>Popular & Trending
                </h1>
                <p class="lead mb-4">Discover what everyone is watching - the most popular and trending content right now</p>
                
                <!-- Quick Stats -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-number"><?php echo $total_content; ?></div>
                            <div class="stats-label">Popular Titles</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-number"><?php echo number_format(array_sum(array_column($popular_content, 'views'))); ?></div>
                            <div class="stats-label">Total Views</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-number">9.8</div>
                            <div class="stats-label">Highest Rating</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-number"><?php echo date('Y'); ?></div>
                            <div class="stats-label">Current Year</div>
                        </div>
                    </div>
                </div>
                
                <!-- Navigation -->
                <div class="d-flex gap-2 justify-content-center flex-wrap">
                    <a href="movies.php" class="btn btn-streamify">
                        <i class="bi bi-film me-1"></i>Movies
                    </a>
                    <a href="tvshows.php" class="btn btn-streamify">
                        <i class="bi bi-tv me-1"></i>TV Shows
                    </a>
                    <a href="popular.php" class="btn btn-streamify active">
                        <i class="bi bi-fire me-1"></i>Popular
                    </a>
                </div>
            </div>
        </div>

        <!-- Trending Now Section -->
        <?php if (!empty($trending_content)): ?>
            <h3 class="section-title fw-bold">
                <i class="bi bi-graph-up-arrow me-2"></i>Trending Now
                <span class="badge bg-danger ms-2">Live</span>
            </h3>
            <p class="text-muted mb-4">What's hot this week - the most watched content in the last 7 days</p>
            
            <div class="scroll-container">
                <?php foreach ($trending_content as $item): ?>
                    <div class="scroll-item">
                        <div class="card content-card" onclick="window.location.href='watch.php?id=<?= $item['content_id'] ?>'">
                            <span class="card-badge">TRENDING</span>
                            <?php if ($item['content_type'] == 'movie'): ?>
                                <span class="movie-badge">MOVIE</span>
                            <?php else: ?>
                                <span class="tv-badge">TV SHOW</span>
                            <?php endif; ?>
                            <?php if ($item['is_premium']): ?>
                                <span class="premium-badge">Premium</span>
                            <?php endif; ?>
                            
                            <img src="<?php echo htmlspecialchars($item['thumbnail_url']); ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo htmlspecialchars($item['title']); ?>"
                                 onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 300 400\' fill=\'%23333\'%3E%3Crect width=\'300\' height=\'400\' fill=\'%23333\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'white\'%3ENo Image%3C/text%3E%3C/svg%3E'">
                            
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($item['title']); ?></h5>
                                <p class="card-text">
                                    <?php echo htmlspecialchars($item['categories']); ?> | 
                                    <?php echo $item['release_year']; ?>
                                </p>
                                <div class="d-flex align-items-center">
                                    <div class="text-warning me-2">
                                        <i class="bi bi-star-fill"></i>
                                        <span class="ms-1"><?php echo number_format($item['rating'], 1); ?></span>
                                    </div>
                                    <span class="text-muted">•</span>
                                    <span class="text-muted ms-2"><?php echo number_format($item['views']); ?> views</span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Top Rated Section -->
        <?php if (!empty($top_rated_content)): ?>
            <h3 class="section-title fw-bold">
                <i class="bi bi-trophy-fill me-2"></i>Top Rated
                <span class="badge bg-warning ms-2">8.0+ Rating</span>
            </h3>
            <p class="text-muted mb-4">Critically acclaimed content with the highest ratings</p>
            
            <div class="scroll-container">
                <?php foreach ($top_rated_content as $item): ?>
                    <div class="scroll-item">
                        <div class="card content-card" onclick="window.location.href='watch.php?id=<?= $item['content_id'] ?>'">
                            <span class="card-badge">TOP RATED</span>
                            <?php if ($item['content_type'] == 'movie'): ?>
                                <span class="movie-badge">MOVIE</span>
                            <?php else: ?>
                                <span class="tv-badge">TV SHOW</span>
                            <?php endif; ?>
                            <?php if ($item['is_premium']): ?>
                                <span class="premium-badge">Premium</span>
                            <?php endif; ?>
                            
                            <img src="<?php echo htmlspecialchars($item['thumbnail_url']); ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo htmlspecialchars($item['title']); ?>"
                                 onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 300 400\' fill=\'%23333\'%3E%3Crect width=\'300\' height=\'400\' fill=\'%23333\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'white\'%3ENo Image%3C/text%3E%3C/svg%3E'">
                            
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($item['title']); ?></h5>
                                <p class="card-text">
                                    <?php echo htmlspecialchars($item['categories']); ?> | 
                                    <?php echo $item['release_year']; ?>
                                </p>
                                <div class="d-flex align-items-center">
                                    <div class="text-warning me-2">
                                        <i class="bi bi-star-fill"></i>
                                        <span class="ms-1"><?php echo number_format($item['rating'], 1); ?></span>
                                    </div>
                                    <span class="text-muted">•</span>
                                    <span class="text-muted ms-2"><?php echo number_format($item['views']); ?> views</span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- All Popular Content -->
        <h3 class="section-title fw-bold">
            <i class="bi bi-people-fill me-2"></i>Most Popular
            <span class="badge bg-streamify ms-2">All Time</span>
        </h3>
        <p class="text-muted mb-4">The most watched content on Streamify - sorted by view count</p>

        <?php if (!empty($popular_content)): ?>
            <div class="content-grid">
                <?php foreach ($popular_content as $item): ?>
                    <div class="grid-item">
                        <div class="card content-card" onclick="window.location.href='watch.php?id=<?= $item['content_id'] ?>'">
                            <span class="card-badge">POPULAR</span>
                            <?php if ($item['content_type'] == 'movie'): ?>
                                <span class="movie-badge">MOVIE</span>
                            <?php else: ?>
                                <span class="tv-badge">TV SHOW</span>
                            <?php endif; ?>
                            <?php if ($item['is_premium']): ?>
                                <span class="premium-badge">Premium</span>
                            <?php endif; ?>
                            
                            <img src="<?php echo htmlspecialchars($item['thumbnail_url']); ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo htmlspecialchars($item['title']); ?>"
                                 onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 300 400\' fill=\'%23333\'%3E%3Crect width=\'300\' height=\'400\' fill=\'%23333\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'white\'%3ENo Image%3C/text%3E%3C/svg%3E'">
                            
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($item['title']); ?></h5>
                                <p class="card-text">
                                    <?php echo htmlspecialchars($item['categories']); ?> | 
                                    <?php echo $item['release_year']; ?>
                                </p>
                                <div class="d-flex align-items-center">
                                    <div class="text-warning me-2">
                                        <i class="bi bi-star-fill"></i>
                                        <span class="ms-1"><?php echo number_format($item['rating'], 1); ?></span>
                                    </div>
                                    <span class="text-muted">•</span>
                                    <span class="text-muted ms-2"><?php echo number_format($item['views']); ?> views</span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Popular content pagination">
                    <ul class="pagination">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="popular.php?page=<?php echo $page - 1; ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="popular.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="popular.php?page=<?php echo $page + 1; ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-emoji-frown display-1 text-muted mb-3"></i>
                <h3>No Popular Content Found</h3>
                <p class="text-muted">There's no popular content available at the moment.</p>
                <a href="movies.php" class="btn btn-streamify mt-3">
                    <i class="bi bi-film me-1"></i>Browse Movies
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php include "footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add smooth scrolling to scroll containers
        document.addEventListener('DOMContentLoaded', function() {
            const scrollContainers = document.querySelectorAll('.scroll-container');
            
            scrollContainers.forEach(container => {
                let isDown = false;
                let startX;
                let scrollLeft;

                container.addEventListener('mousedown', (e) => {
                    isDown = true;
                    container.classList.add('active');
                    startX = e.pageX - container.offsetLeft;
                    scrollLeft = container.scrollLeft;
                });

                container.addEventListener('mouseleave', () => {
                    isDown = false;
                    container.classList.remove('active');
                });

                container.addEventListener('mouseup', () => {
                    isDown = false;
                    container.classList.remove('active');
                });

                container.addEventListener('mousemove', (e) => {
                    if (!isDown) return;
                    e.preventDefault();
                    const x = e.pageX - container.offsetLeft;
                    const walk = (x - startX) * 2;
                    container.scrollLeft = scrollLeft - walk;
                });
            });
        });
    </script>
</body>
</html>