<?php
// category.php
require_once "db.php";

$category_id = $_GET['id'] ?? 1;

// Get category name
$category_sql = "SELECT name FROM categories WHERE category_id = ?";
$stmt = $conn->prepare($category_sql);
$stmt->bind_param("i", $category_id);
$stmt->execute();
$category = $stmt->get_result()->fetch_assoc();

if (!$category) {
    header("Location: index.php");
    exit;
}

// Get content for this category
$content_sql = "
    SELECT c.content_id, c.title, c.description, CONCAT('admin/', c.thumbnail_url) AS thumbnail_url, 
           c.banner_url, c.rating, c.release_year, c.views, c.featured, c.is_premium,
           GROUP_CONCAT(cat.name ORDER BY cat.name SEPARATOR ', ') as categories
    FROM content c
    JOIN content_categories cc ON c.content_id = cc.content_id
    JOIN categories cat ON cc.category_id = cat.category_id
    WHERE cc.category_id = ?
    GROUP BY c.content_id
    ORDER BY c.views DESC";
$stmt = $conn->prepare($content_sql);
$stmt->bind_param("i", $category_id);
$stmt->execute();
$content = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get total content count
$total_content = count($content);

// Get all categories for sidebar/filter (if needed)
$categories_sql = "SELECT category_id, name FROM categories ORDER BY name";
$categories_result = $conn->query($categories_sql);
$all_categories = $categories_result->fetch_all(MYSQLI_ASSOC);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($category['name']) ?> | Streamify</title>
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
            height: 300px;
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
        
        /* Grid Layout for Category Content */
        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
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
            
            .content-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 1.5rem;
            }
        }
        
        @media (max-width: 576px) {
            .scroll-item {
                width: 180px;
            }
            
            .content-card img {
                height: 200px;
            }
            
            .content-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 1rem;
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
                    <i class="bi bi-collection me-3"></i><?= htmlspecialchars($category['name']) ?>
                </h1>
                <p class="lead mb-4">Discover amazing <?= htmlspecialchars($category['name']) ?> movies and TV shows</p>
                
                <!-- Quick Stats -->
                <div class="row g-3 mb-4 justify-content-center">
                    <div class="col-md-3 col-6">
                        <div class="stats-card">
                            <div class="stats-number"><?= $total_content ?></div>
                            <div class="stats-label">Titles Available</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="stats-card">
                            <div class="stats-number"><?= count($all_categories) ?></div>
                            <div class="stats-label">Total Genres</div>
                        </div>
                    </div>
                </div>
                
                <!-- Genre Filter -->
                <div class="genre-filter">
                    <a href="movies.php" class="genre-btn">
                        <i class="bi bi-arrow-left me-1"></i>All Movies
                    </a>
                    <?php foreach ($all_categories as $cat): ?>
                        <a href="category.php?id=<?= $cat['category_id'] ?>" 
                           class="genre-btn <?= $category_id == $cat['category_id'] ? 'active' : '' ?>">
                            <?= htmlspecialchars($cat['name']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Category Content -->
        <div class="filter-results-header">
            <h2 class="section-title fw-bold">
                <i class="bi bi-filter me-2"></i>
                <?= htmlspecialchars($category['name']) ?> Collection
                <span class="badge bg-streamify ms-2"><?= $total_content ?> titles</span>
            </h2>
            <p class="text-muted">Showing all content in the <?= htmlspecialchars($category['name']) ?> genre</p>
        </div>

        <?php if (empty($content)): ?>
            <div class="no-results">
                <i class="bi bi-film display-1 text-muted mb-3"></i>
                <h3>No Content Found</h3>
                <p class="text-muted">We're adding more <?= htmlspecialchars($category['name']) ?> content soon!</p>
                <a href="movies.php" class="btn btn-streamify mt-3">
                    <i class="bi bi-arrow-left me-1"></i>Back to All Movies
                </a>
            </div>
        <?php else: ?>
            <!-- Grid Layout for Category Content -->
            <div class="content-grid">
                <?php foreach ($content as $item): ?>
                    <div class="content-card" onclick="window.location.href='watch.php?id=<?= $item['content_id'] ?>'">
                        <?php if ($item['is_premium']): ?>
                            <span class="premium-badge">Premium</span>
                        <?php endif; ?>
                        
                        <?php if ($item['featured']): ?>
                            <span class="card-badge">FEATURED</span>
                        <?php endif; ?>
                        
                        <img src="<?= htmlspecialchars($item['thumbnail_url']) ?>" 
                             class="card-img-top" 
                             alt="<?= htmlspecialchars($item['title']) ?>"
                             onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 300 400\' fill=\'%23333\'%3E%3Crect width=\'300\' height=\'400\' fill=\'%23333\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'white\'%3ENo Image%3C/text%3E%3C/svg%3E'">
                        
<div class="card-body">
    <h5 class="card-title" style="color: var(--streamify-text)"><?= htmlspecialchars($item['title']) ?></h5>
    <p class="card-text mb-2">
        <small style="color: var(--streamify-text-muted)">
            <?= htmlspecialchars($item['categories']) ?> | 
            <?= $item['release_year'] ?>
        </small>
    </p>
    <div class="d-flex align-items-center justify-content-between">
        <div class="text-warning">
            <i class="bi bi-star-fill"></i>
            <span class="ms-1" style="color: var(--streamify-text)"><?= number_format($item['rating'], 1) ?></span>
        </div>
        <span class="small" style="color: var(--streamify-text-muted)"><?= number_format($item['views']) ?> views</span>
    </div>
    <?php if (!empty($item['description'])): ?>
        <p class="card-text mt-2 small" style="color: var(--streamify-text-muted)">
            <?= htmlspecialchars(substr($item['description'], 0, 80)) ?>...
        </p>
    <?php endif; ?>
</div>                    </div>
                <?php endforeach; ?>
            </div>
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

        // Animate content cards on scroll
        const contentCards = document.querySelectorAll('.content-card');
        contentCards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = `all 0.6s ease ${index * 0.1}s`;
            
            const cardObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);
            
            cardObserver.observe(card);
        });
    </script>
</body>
</html>