<?php
require_once 'db.php';

// Get filter parameters
$genre = isset($_GET['genre']) ? intval($_GET['genre']) : 0;
$year = isset($_GET['year']) ? intval($_GET['year']) : 0;
$rating = isset($_GET['rating']) ? floatval($_GET['rating']) : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'popular';

// Get categories for filter
$categories_sql = "SELECT category_id, name FROM categories ORDER BY name";
$categories_result = $conn->query($categories_sql);
$categories = $categories_result->fetch_all(MYSQLI_ASSOC);

// Get total movie count
$total_movies_sql = "SELECT COUNT(*) as total FROM content WHERE content_type = 'movie'";
$total_movies_result = $conn->query($total_movies_sql);
$total_movies = $total_movies_result->fetch_assoc()['total'];

// Function to get movies by category
function getMoviesByCategory($conn, $category_id = 0, $limit = 15) {
    if ($category_id > 0) {
        // Filter by specific category
        $sql = "
            SELECT c.content_id, c.title, c.description, CONCAT('admin/', c.thumbnail_url) AS thumbnail_url, 
                   c.banner_url, c.rating, c.release_year, c.views, c.featured, c.is_premium,
                   GROUP_CONCAT(cat.name ORDER BY cat.name SEPARATOR ', ') as categories
            FROM content c
            JOIN content_categories cc ON c.content_id = cc.content_id
            JOIN categories cat ON cc.category_id = cat.category_id
            WHERE c.content_type = 'movie' AND cc.category_id = ?
            GROUP BY c.content_id
            ORDER BY c.views DESC
            LIMIT ?
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $category_id, $limit);
    } else {
        // Get all movies
        $sql = "
            SELECT c.content_id, c.title, c.description, CONCAT('admin/', c.thumbnail_url) AS thumbnail_url, 
                   c.banner_url, c.rating, c.release_year, c.views, c.featured, c.is_premium,
                   GROUP_CONCAT(cat.name ORDER BY cat.name SEPARATOR ', ') as categories
            FROM content c
            LEFT JOIN content_categories cc ON c.content_id = cc.content_id
            LEFT JOIN categories cat ON cc.category_id = cat.category_id
            WHERE c.content_type = 'movie'
            GROUP BY c.content_id
            ORDER BY c.views DESC
            LIMIT ?
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $limit);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Function to get movies by category name (for specific sections)
function getMoviesByCategoryName($conn, $category_name, $limit = 10) {
    $sql = "
        SELECT c.content_id, c.title, CONCAT('admin/', c.thumbnail_url) AS thumbnail_url, 
               c.rating, c.release_year, c.views, c.is_premium,
               GROUP_CONCAT(cat.name ORDER BY cat.name SEPARATOR ', ') as categories
        FROM content c
        JOIN content_categories cc ON c.content_id = cc.content_id
        JOIN categories cat ON cc.category_id = cat.category_id
        WHERE c.content_type = 'movie' AND cat.name = ?
        GROUP BY c.content_id
        ORDER BY c.views DESC
        LIMIT ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $category_name, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Get featured movies (only if no specific genre is selected)
if ($genre === 0) {
    $featured_sql = "
        SELECT c.content_id, c.title, c.description, CONCAT('admin/', c.thumbnail_url) AS thumbnail_url, 
               c.banner_url, c.rating, c.release_year, c.views, c.featured, c.is_premium,
               GROUP_CONCAT(cat.name ORDER BY cat.name SEPARATOR ', ') as categories
        FROM content c
        LEFT JOIN content_categories cc ON c.content_id = cc.content_id
        LEFT JOIN categories cat ON cc.category_id = cat.category_id
        WHERE c.content_type = 'movie' AND c.featured = 1
        GROUP BY c.content_id
        ORDER BY c.rating DESC
        LIMIT 5
    ";
    $featured_result = $conn->query($featured_sql);
    $featured_movies = $featured_result->fetch_all(MYSQLI_ASSOC);
} else {
    $featured_movies = [];
}

// Get filtered movies if a genre is selected
if ($genre > 0) {
    $filtered_movies = getMoviesByCategory($conn, $genre, 50);
    $selected_category = array_filter($categories, function($cat) use ($genre) {
        return $cat['category_id'] == $genre;
    });
    $selected_category = !empty($selected_category) ? reset($selected_category) : null;
} else {
    $filtered_movies = [];
    $selected_category = null;
}

// Get category-specific movies (only show when no specific genre filter is applied)
if ($genre === 0) {
    $action_movies = getMoviesByCategoryName($conn, 'Action', 10);
    $drama_movies = getMoviesByCategoryName($conn, 'Drama', 10);
    $comedy_movies = getMoviesByCategoryName($conn, 'Comedy', 10);
    $scifi_movies = getMoviesByCategoryName($conn, 'Sci-Fi', 10);
    
    // Latest Movies
    $latest_sql = "
        SELECT c.content_id, c.title, CONCAT('admin/', c.thumbnail_url) AS thumbnail_url, 
               c.rating, c.release_year, c.views, c.is_premium,
               GROUP_CONCAT(cat.name ORDER BY cat.name SEPARATOR ', ') as categories
        FROM content c
        LEFT JOIN content_categories cc ON c.content_id = cc.content_id
        LEFT JOIN categories cat ON cc.category_id = cat.category_id
        WHERE c.content_type = 'movie'
        GROUP BY c.content_id
        ORDER BY c.release_year DESC, c.created_at DESC
        LIMIT 10
    ";
    $latest_result = $conn->query($latest_sql);
    $latest_movies = $latest_result->fetch_all(MYSQLI_ASSOC);
} else {
    $action_movies = [];
    $drama_movies = [];
    $comedy_movies = [];
    $scifi_movies = [];
    $latest_movies = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movies | Streamify</title>
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
        
        /* Genre Filter */
        .genre-filter {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
            justify-content: center;
        }
        
        .genre-btn {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--streamify-text);
            padding: 0.5rem 1rem;
            border-radius: 25px;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        
        .genre-btn:hover, .genre-btn.active {
            background: var(--streamify-primary);
            border-color: var(--streamify-primary);
            color: white;
            transform: translateY(-2px);
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
        
        /* Filter Results Header */
        .filter-results-header {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        /* No Results Message */
        .no-results {
            text-align: center;
            padding: 3rem;
            color: var(--streamify-text-muted);
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .scroll-item {
                width: 220px;
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
                    <i class="bi bi-film me-3"></i>Movies
                </h1>
                <p class="lead mb-4">Discover the latest blockbusters, timeless classics, and hidden gems</p>
                
                <!-- Quick Stats -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-number"><?php echo $total_movies; ?></div>
                            <div class="stats-label">Movies Available</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-number"><?php echo count($categories); ?></div>
                            <div class="stats-label">Genres</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-number"><?php echo date('Y'); ?></div>
                            <div class="stats-label">Latest Year</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-number">9.5</div>
                            <div class="stats-label">Top Rating</div>
                        </div>
                    </div>
                </div>
                
                <!-- Genre Filter -->
                <div class="genre-filter">
                    <a href="movies.php" class="genre-btn <?php echo $genre == 0 ? 'active' : ''; ?>">
                        <i class="bi bi-grid me-1"></i>All Movies
                    </a>
                    <?php foreach ($categories as $cat): ?>
                        <a href="movies.php?genre=<?php echo $cat['category_id']; ?>" 
                           class="genre-btn <?php echo $genre == $cat['category_id'] ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Filter Results -->
        <?php if ($genre > 0 && !empty($filtered_movies)): ?>
            <div class="filter-results-header">
                <h2 class="section-title fw-bold">
                    <i class="bi bi-filter me-2"></i>
                    <?php echo htmlspecialchars($selected_category['name']); ?> Movies
                    <span class="badge bg-streamify ms-2"><?php echo count($filtered_movies); ?> movies</span>
                </h2>
                <p class="text-muted">Showing all movies in the <?php echo htmlspecialchars($selected_category['name']); ?> genre</p>
                <a href="movies.php" class="btn btn-streamify mt-2">
                    <i class="bi bi-arrow-left me-1"></i>Back to All Movies
                </a>
            </div>

            <div class="scroll-container">
                <?php foreach ($filtered_movies as $movie): ?>
                    <div class="scroll-item">
                        <div class="card content-card" onclick="window.location.href='watch.php?id=<?= $movie['content_id'] ?>'">
                            <?php if ($movie['is_premium']): ?>
                                <span class="premium-badge">Premium</span>
                            <?php endif; ?>
                            
                            <img src="<?php echo htmlspecialchars($movie['thumbnail_url']); ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo htmlspecialchars($movie['title']); ?>"
                                 onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 300 400\' fill=\'%23333\'%3E%3Crect width=\'300\' height=\'400\' fill=\'%23333\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'white\'%3ENo Image%3C/text%3E%3C/svg%3E'">
                            
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($movie['title']); ?></h5>
                                <p class="card-text">
                                    <?php echo htmlspecialchars($movie['categories']); ?> | 
                                    <?php echo $movie['release_year']; ?>
                                </p>
                                <div class="d-flex align-items-center">
                                    <div class="text-warning me-2">
                                        <i class="bi bi-star-fill"></i>
                                        <span class="ms-1"><?php echo number_format($movie['rating'], 1); ?></span>
                                    </div>
                                    <span class="text-muted">•</span>
                                    <span class="text-muted ms-2"><?php echo number_format($movie['views']); ?> views</span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif ($genre > 0 && empty($filtered_movies)): ?>
            <div class="no-results">
                <i class="bi bi-film display-1 text-muted mb-3"></i>
                <h3>No Movies Found</h3>
                <p class="text-muted">No movies found in the <?php echo htmlspecialchars($selected_category['name']); ?> genre.</p>
                <a href="movies.php" class="btn btn-streamify mt-3">
                    <i class="bi bi-arrow-left me-1"></i>Back to All Movies
                </a>
            </div>
        <?php endif; ?>

        <!-- Regular Movie Sections (only show when no genre filter is applied) -->
        <?php if ($genre === 0): ?>
            <!-- Featured Movies -->
            <?php if (!empty($featured_movies)): ?>
                <h3 class="section-title fw-bold">
                    <i class="bi bi-star-fill me-2"></i>Featured Movies
                </h3>
                <div class="scroll-container">
                    <?php foreach ($featured_movies as $movie): ?>
                        <div class="scroll-item">
                            <div class="card content-card" onclick="window.location.href='watch.php?id=<?= $movie['content_id'] ?>'">
                                <span class="card-badge">FEATURED</span>
                                <?php if ($movie['is_premium']): ?>
                                    <span class="premium-badge">Premium</span>
                                <?php endif; ?>
                                
                                <img src="<?php echo htmlspecialchars($movie['thumbnail_url']); ?>" 
                                     class="card-img-top" 
                                     alt="<?php echo htmlspecialchars($movie['title']); ?>"
                                     onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 300 400\' fill=\'%23333\'%3E%3Crect width=\'300\' height=\'400\' fill=\'%23333\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'white\'%3ENo Image%3C/text%3E%3C/svg%3E'">
                                
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($movie['title']); ?></h5>
                                    <p class="card-text">
                                        <?php echo htmlspecialchars($movie['categories']); ?> | 
                                        <?php echo $movie['release_year']; ?>
                                    </p>
                                    <div class="d-flex align-items-center">
                                        <div class="text-warning me-2">
                                            <i class="bi bi-star-fill"></i>
                                            <span class="ms-1"><?php echo number_format($movie['rating'], 1); ?></span>
                                        </div>
                                        <span class="text-muted">•</span>
                                        <span class="text-muted ms-2"><?php echo number_format($movie['views']); ?> views</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Action Movies -->
            <?php if (!empty($action_movies)): ?>
                <h3 class="section-title fw-bold">
                    <i class="bi bi-lightning-fill me-2"></i>Action Movies
                </h3>
                <div class="scroll-container">
                    <?php foreach ($action_movies as $movie): ?>
                        <div class="scroll-item">
                            <div class="card content-card" onclick="window.location.href='watch.php?id=<?= $movie['content_id'] ?>'">
                                <span class="card-badge">ACTION</span>
                                <?php if ($movie['is_premium']): ?>
                                    <span class="premium-badge">Premium</span>
                                <?php endif; ?>
                                
                                <img src="<?php echo htmlspecialchars($movie['thumbnail_url']); ?>" 
                                     class="card-img-top" 
                                     alt="<?php echo htmlspecialchars($movie['title']); ?>"
                                     onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 300 400\' fill=\'%23333\'%3E%3Crect width=\'300\' height=\'400\' fill=\'%23333\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'white\'%3ENo Image%3C/text%3E%3C/svg%3E'">
                                
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($movie['title']); ?></h5>
                                    <p class="card-text">
                                        <?php echo htmlspecialchars($movie['categories']); ?> | 
                                        <?php echo $movie['release_year']; ?>
                                    </p>
                                    <div class="d-flex align-items-center">
                                        <div class="text-warning me-2">
                                            <i class="bi bi-star-fill"></i>
                                            <span class="ms-1"><?php echo number_format($movie['rating'], 1); ?></span>
                                        </div>
                                        <span class="text-muted">•</span>
                                        <span class="text-muted ms-2"><?php echo number_format($movie['views']); ?> views</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Drama Movies -->
            <?php if (!empty($drama_movies)): ?>
                <h3 class="section-title fw-bold">
                    <i class="bi bi-heart-fill me-2"></i>Drama Movies
                </h3>
                <div class="scroll-container">
                    <?php foreach ($drama_movies as $movie): ?>
                        <div class="scroll-item">
                            <div class="card content-card" onclick="window.location.href='watch.php?id=<?= $movie['content_id'] ?>'">
                                <span class="card-badge">DRAMA</span>
                                <?php if ($movie['is_premium']): ?>
                                    <span class="premium-badge">Premium</span>
                                <?php endif; ?>
                                
                                <img src="<?php echo htmlspecialchars($movie['thumbnail_url']); ?>" 
                                     class="card-img-top" 
                                     alt="<?php echo htmlspecialchars($movie['title']); ?>"
                                     onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 300 400\' fill=\'%23333\'%3E%3Crect width=\'300\' height=\'400\' fill=\'%23333\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'white\'%3ENo Image%3C/text%3E%3C/svg%3E'">
                                
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($movie['title']); ?></h5>
                                    <p class="card-text">
                                        <?php echo htmlspecialchars($movie['categories']); ?> | 
                                        <?php echo $movie['release_year']; ?>
                                    </p>
                                    <div class="d-flex align-items-center">
                                        <div class="text-warning me-2">
                                            <i class="bi bi-star-fill"></i>
                                            <span class="ms-1"><?php echo number_format($movie['rating'], 1); ?></span>
                                        </div>
                                        <span class="text-muted">•</span>
                                        <span class="text-muted ms-2"><?php echo number_format($movie['views']); ?> views</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Comedy Movies -->
            <?php if (!empty($comedy_movies)): ?>
                <h3 class="section-title fw-bold">
                    <i class="bi bi-emoji-laughing-fill me-2"></i>Comedy Movies
                </h3>
                <div class="scroll-container">
                    <?php foreach ($comedy_movies as $movie): ?>
                        <div class="scroll-item">
                            <div class="card content-card" onclick="window.location.href='watch.php?id=<?= $movie['content_id'] ?>'">
                                <span class="card-badge">COMEDY</span>
                                <?php if ($movie['is_premium']): ?>
                                    <span class="premium-badge">Premium</span>
                                <?php endif; ?>
                                
                                <img src="<?php echo htmlspecialchars($movie['thumbnail_url']); ?>" 
                                     class="card-img-top" 
                                     alt="<?php echo htmlspecialchars($movie['title']); ?>"
                                     onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 300 400\' fill=\'%23333\'%3E%3Crect width=\'300\' height=\'400\' fill=\'%23333\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'white\'%3ENo Image%3C/text%3E%3C/svg%3E'">
                                
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($movie['title']); ?></h5>
                                    <p class="card-text">
                                        <?php echo htmlspecialchars($movie['categories']); ?> | 
                                        <?php echo $movie['release_year']; ?>
                                    </p>
                                    <div class="d-flex align-items-center">
                                        <div class="text-warning me-2">
                                            <i class="bi bi-star-fill"></i>
                                            <span class="ms-1"><?php echo number_format($movie['rating'], 1); ?></span>
                                        </div>
                                        <span class="text-muted">•</span>
                                        <span class="text-muted ms-2"><?php echo number_format($movie['views']); ?> views</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Sci-Fi Movies -->
            <?php if (!empty($scifi_movies)): ?>
                <h3 class="section-title fw-bold">
                    <i class="bi bi-rocket-fill me-2"></i>Sci-Fi Movies
                </h3>
                <div class="scroll-container">
                    <?php foreach ($scifi_movies as $movie): ?>
                        <div class="scroll-item">
                            <div class="card content-card" onclick="window.location.href='watch.php?id=<?= $movie['content_id'] ?>'">
                                <span class="card-badge">SCI-FI</span>
                                <?php if ($movie['is_premium']): ?>
                                    <span class="premium-badge">Premium</span>
                                <?php endif; ?>
                                
                                <img src="<?php echo htmlspecialchars($movie['thumbnail_url']); ?>" 
                                     class="card-img-top" 
                                     alt="<?php echo htmlspecialchars($movie['title']); ?>"
                                     onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 300 400\' fill=\'%23333\'%3E%3Crect width=\'300\' height=\'400\' fill=\'%23333\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'white\'%3ENo Image%3C/text%3E%3C/svg%3E'">
                                
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($movie['title']); ?></h5>
                                    <p class="card-text">
                                        <?php echo htmlspecialchars($movie['categories']); ?> | 
                                        <?php echo $movie['release_year']; ?>
                                    </p>
                                    <div class="d-flex align-items-center">
                                        <div class="text-warning me-2">
                                            <i class="bi bi-star-fill"></i>
                                            <span class="ms-1"><?php echo number_format($movie['rating'], 1); ?></span>
                                        </div>
                                        <span class="text-muted">•</span>
                                        <span class="text-muted ms-2"><?php echo number_format($movie['views']); ?> views</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Latest Movies -->
            <?php if (!empty($latest_movies)): ?>
                <h3 class="section-title fw-bold">
                    <i class="bi bi-calendar-event-fill me-2"></i>Latest Movies
                </h3>
                <div class="scroll-container">
                    <?php foreach ($latest_movies as $movie): ?>
                        <div class="scroll-item">
                            <div class="card content-card" onclick="window.location.href='watch.php?id=<?= $movie['content_id'] ?>'">
                                <span class="card-badge">NEW</span>
                                <?php if ($movie['is_premium']): ?>
                                    <span class="premium-badge">Premium</span>
                                <?php endif; ?>
                                
                                <img src="<?php echo htmlspecialchars($movie['thumbnail_url']); ?>" 
                                     class="card-img-top" 
                                     alt="<?php echo htmlspecialchars($movie['title']); ?>"
                                     onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 300 400\' fill=\'%23333\'%3E%3Crect width=\'300\' height=\'400\' fill=\'%23333\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'white\'%3ENo Image%3C/text%3E%3C/svg%3E'">
                                
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($movie['title']); ?></h5>
                                    <p class="card-text">
                                        <?php echo htmlspecialchars($movie['categories']); ?> | 
                                        <?php echo $movie['release_year']; ?>
                                    </p>
                                    <div class="d-flex align-items-center">
                                        <div class="text-warning me-2">
                                            <i class="bi bi-star-fill"></i>
                                            <span class="ms-1"><?php echo number_format($movie['rating'], 1); ?></span>
                                        </div>
                                        <span class="text-muted">•</span>
                                        <span class="text-muted ms-2"><?php echo number_format($movie['views']); ?> views</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <?php include "footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Smooth scrolling for horizontal containers
        document.querySelectorAll('.scroll-container').forEach(container => {
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

        // Intersection Observer for animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe all section titles
        document.querySelectorAll('.section-title').forEach(title => {
            title.style.opacity = '0';
            title.style.transform = 'translateY(30px)';
            title.style.transition = 'all 0.6s ease';
            observer.observe(title);
        });
    </script>
</body>
</html>