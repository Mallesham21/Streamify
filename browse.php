<?php
require_once 'db.php';

// Get search and filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? intval($_GET['category']) : 0;
$content_type = isset($_GET['type']) ? $_GET['type'] : '';
$rating_min = isset($_GET['rating']) ? floatval($_GET['rating']) : 0;
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'title';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 12; // Items per page
$offset = ($page - 1) * $limit;

// Build the query
$where_conditions = [];
$params = [];
$param_types = '';

// Search condition
if (!empty($search)) {
    $where_conditions[] = "(c.title LIKE ? OR c.description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $param_types .= 'ss';
}

// Category filter
if ($category > 0) {
    $where_conditions[] = "cc.category_id = ?";
    $params[] = $category;
    $param_types .= 'i';
}

// Content type filter
if (!empty($content_type) && in_array($content_type, ['movie', 'tv_show'])) {
    $where_conditions[] = "c.content_type = ?";
    $params[] = $content_type;
    $param_types .= 's';
}

// Rating filter
if ($rating_min > 0) {
    $where_conditions[] = "c.rating >= ?";
    $params[] = $rating_min;
    $param_types .= 'd';
}

// Build WHERE clause
$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Sort options
$sort_options = [
    'title' => 'c.title ASC',
    'rating' => 'c.rating DESC',
    'views' => 'c.views DESC',
    'year' => 'c.release_year DESC',
    'newest' => 'c.created_at DESC'
];

$order_by = isset($sort_options[$sort_by]) ? $sort_options[$sort_by] : $sort_options['title'];

// Get total count for pagination
$count_query = "
    SELECT COUNT(DISTINCT c.content_id) as total
    FROM content c
    LEFT JOIN content_categories cc ON c.content_id = cc.content_id
    $where_clause
";

$count_stmt = $conn->prepare($count_query);
if (!empty($params)) {
    $count_stmt->bind_param($param_types, ...$params);
}
$count_stmt->execute();
$total_items = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_items / $limit);
$count_stmt->close();

// Get content with pagination
$content_query = "
    SELECT c.content_id, c.title, c.description, CONCAT('admin/', c.thumbnail_url) AS thumbnail_url, c.content_type, 
           c.rating, c.release_year, c.views, c.featured,
           GROUP_CONCAT(DISTINCT cat.name SEPARATOR ', ') as categories
    FROM content c
    LEFT JOIN content_categories cc ON c.content_id = cc.content_id
    LEFT JOIN categories cat ON cc.category_id = cat.category_id
    $where_clause
    GROUP BY c.content_id, c.title, c.description, c.thumbnail_url, c.content_type, c.rating, c.release_year, c.views, c.featured
    ORDER BY $order_by
    LIMIT ? OFFSET ?
";

$content_stmt = $conn->prepare($content_query);
$params[] = $limit;
$params[] = $offset;
$param_types .= 'ii';

if (!empty($params)) {
    $content_stmt->bind_param($param_types, ...$params);
}
$content_stmt->execute();
$content_items = $content_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$content_stmt->close();

// Get categories for filter dropdown
$categories_query = "SELECT category_id, name FROM categories ORDER BY name";
$categories_result = $conn->query($categories_query);
$categories = $categories_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Content | Streamify</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
    
    <style>
        :root {
            --streamify-primary: #b13bff;
            --streamify-secondary: #00ccff;
            --streamify-dark: #1c0f24;
            --streamify-light: #f8f9fa;
            --streamify-text: #e2e2e2;
            --streamify-text-muted: #a0a0a0;
        }
        
        body {
            background: linear-gradient(135deg, var(--streamify-dark) 0%, #2a1b3d 100%);
            color: var(--streamify-text);
            min-height: 100vh;
        }
        
        .search-section {
            background: rgba(28, 15, 36, 0.8);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(177, 59, 255, 0.2);
        }
        
        .search-box {
            position: relative;
        }
        
        .search-box input {
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(177, 59, 255, 0.3);
            color: white;
            padding: 1rem 1rem 1rem 3rem;
            border-radius: 50px;
            transition: all 0.3s ease;
        }
        
        .search-box input:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--streamify-primary);
            box-shadow: 0 0 0 0.25rem rgba(177, 59, 255, 0.25);
            color: white;
        }
        
        .search-box input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }
        
        .search-box i {
            position: absolute;
            left: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--streamify-primary);
            font-size: 1.2rem;
        }
        
        .filter-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .filter-card h6 {
            color: var(--streamify-primary);
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .form-select, .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
        }
        
        .form-select:focus, .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--streamify-primary);
            box-shadow: 0 0 0 0.25rem rgba(177, 59, 255, 0.25);
            color: white;
        }
        
        .form-select option {
            background: var(--streamify-dark);
            color: white;
        }
        
        .btn-streamify {
            background: linear-gradient(45deg, var(--streamify-primary), var(--streamify-secondary));
            border: none;
            color: white;
            padding: 0.75rem 2rem;
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
            border: 2px solid var(--streamify-primary);
            color: var(--streamify-primary);
            background: transparent;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            transition: all 0.3s ease;
        }
        
        .btn-outline-streamify:hover {
            background: var(--streamify-primary);
            color: white;
            transform: translateY(-2px);
        }
        
        .content-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
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
        }
        
        .content-card:hover img {
            transform: scale(1.05);
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .card-title {
            color: var(--streamify-text);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .card-text {
            color: var(--streamify-text-muted);
            font-size: 0.9rem;
            line-height: 1.4;
        }
        
        .badge-streamify {
            background: linear-gradient(45deg, var(--streamify-primary), var(--streamify-secondary));
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .rating-stars {
            color: #ffc107;
        }
        
        .featured-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: linear-gradient(45deg, #ff6b6b, #ffa500);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            z-index: 2;
        }
        
        .results-header {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .pagination .page-link {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--streamify-text);
            margin: 0 0.2rem;
            border-radius: 10px;
        }
        
        .pagination .page-link:hover {
            background: var(--streamify-primary);
            border-color: var(--streamify-primary);
            color: white;
        }
        
        .pagination .page-item.active .page-link {
            background: var(--streamify-primary);
            border-color: var(--streamify-primary);
        }
        
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: var(--streamify-primary);
            margin-bottom: 1rem;
        }
        
        .clear-filters {
            background: rgba(255, 107, 107, 0.2);
            border: 1px solid rgba(255, 107, 107, 0.3);
            color: #ff6b6b;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .clear-filters:hover {
            background: rgba(255, 107, 107, 0.3);
            color: #ff6b6b;
        }
    </style>
</head>
<body>
    <?php include "header.php"; ?>

    <div class="container mt-5 pt-4">
        <!-- Search and Filters Section -->
        <div class="search-section">
            <form method="GET" action="browse.php" class="row g-3">
                <div class="col-12">
                    <div class="search-box">
                        <i class="bi bi-search"></i>
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search movies, TV shows, descriptions..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="filter-card">
                        <h6><i class="bi bi-tags me-2"></i>Category</h6>
                        <select name="category" class="form-select">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['category_id']; ?>" 
                                        <?php echo $category == $cat['category_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="filter-card">
                        <h6><i class="bi bi-film me-2"></i>Type</h6>
                        <select name="type" class="form-select">
                            <option value="">All Types</option>
                            <option value="movie" <?php echo $content_type === 'movie' ? 'selected' : ''; ?>>Movies</option>
                            <option value="tv_show" <?php echo $content_type === 'tv_show' ? 'selected' : ''; ?>>TV Shows</option>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="filter-card">
                        <h6><i class="bi bi-star me-2"></i>Min Rating</h6>
                        <select name="rating" class="form-select">
                            <option value="">Any Rating</option>
                            <option value="7" <?php echo $rating_min == 7 ? 'selected' : ''; ?>>7.0+</option>
                            <option value="8" <?php echo $rating_min == 8 ? 'selected' : ''; ?>>8.0+</option>
                            <option value="9" <?php echo $rating_min == 9 ? 'selected' : ''; ?>>9.0+</option>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="filter-card">
                        <h6><i class="bi bi-sort-down me-2"></i>Sort By</h6>
                        <select name="sort" class="form-select">
                            <option value="title" <?php echo $sort_by === 'title' ? 'selected' : ''; ?>>Title A-Z</option>
                            <option value="rating" <?php echo $sort_by === 'rating' ? 'selected' : ''; ?>>Highest Rated</option>
                            <option value="views" <?php echo $sort_by === 'views' ? 'selected' : ''; ?>>Most Popular</option>
                            <option value="year" <?php echo $sort_by === 'year' ? 'selected' : ''; ?>>Newest First</option>
                            <option value="newest" <?php echo $sort_by === 'newest' ? 'selected' : ''; ?>>Recently Added</option>
                        </select>
                    </div>
                </div>
                
                <div class="col-12 text-center">
                    <button type="submit" class="btn btn-streamify me-3">
                        <i class="bi bi-search me-2"></i>Search & Filter
                    </button>
                    <?php if (!empty($search) || $category > 0 || !empty($content_type) || $rating_min > 0): ?>
                        <a href="browse.php" class="clear-filters">
                            <i class="bi bi-x-circle me-2"></i>Clear Filters
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Results Header -->
        <div class="results-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-0">
                        <i class="bi bi-collection me-2"></i>
                        Browse Content
                        <?php if ($total_items > 0): ?>
                            <span class="badge bg-primary ms-2"><?php echo $total_items; ?> results</span>
                        <?php endif; ?>
                    </h2>
                    <?php if (!empty($search) || $category > 0 || !empty($content_type) || $rating_min > 0): ?>
                        <p class="text-muted mb-0 mt-2">
                            <i class="bi bi-funnel me-1"></i>
                            Filtered results
                            <?php if (!empty($search)): ?>
                                for "<?php echo htmlspecialchars($search); ?>"
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>
                </div>
                <div class="col-md-4 text-md-end">
                    <?php if ($total_pages > 1): ?>
                        <small class="text-muted">
                            Page <?php echo $page; ?> of <?php echo $total_pages; ?>
                        </small>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Content Grid -->
        <?php if (empty($content_items)): ?>
            <div class="empty-state">
                <i class="bi bi-search"></i>
                <h3>No content found</h3>
                <p class="text-muted mb-4">
                    <?php if (!empty($search) || $category > 0 || !empty($content_type) || $rating_min > 0): ?>
                        Try adjusting your search criteria or filters.
                    <?php else: ?>
                        No content is available at the moment.
                    <?php endif; ?>
                </p>
                <a href="browse.php" class="btn btn-streamify">Browse All Content</a>
            </div>
        <?php else: ?>
            <div class="row row-cols-3 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">
                <?php foreach ($content_items as $item): ?>
                    <div class="col">
                        <div class="content-card position-relative">
                            <?php if ($item['featured']): ?>
                                <div class="featured-badge">
                                    <i class="bi bi-star-fill me-1"></i>Featured
                                </div>
                            <?php endif; ?>
                            
                            <img src="<?php echo htmlspecialchars($item['thumbnail_url']); ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo htmlspecialchars($item['title']); ?>"
                                 onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 300 200\' fill=\'%23333\'%3E%3Crect width=\'300\' height=\'200\' fill=\'%23333\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'white\'%3ENo Image%3C/text%3E%3C/svg%3E'">
                            
<div class="card-body">
    <div class="d-flex justify-content-between align-items-start mb-2">
        <h5 class="card-title text-white"><?php echo htmlspecialchars($item['title']); ?></h5>
        <span class="badge bg-streamify">
            <?php echo strtoupper(str_replace('_', ' ', $item['content_type'])); ?>
        </span>
    </div>
    
    <div class="d-flex align-items-center mb-2">
        <div class="rating-stars me-2 text-warning">
            <i class="bi bi-star-fill"></i>
            <span class="ms-1 text-white"><?php echo number_format($item['rating'], 1); ?></span>
        </div>
        <?php if ($item['release_year']): ?>
            <span class="text-streamify-light me-2">•</span>
            <span class="text-streamify-light"><?php echo $item['release_year']; ?></span>
        <?php endif; ?>
    </div>
    
    <?php if (!empty($item['categories'])): ?>
        <div class="mb-2">
            <small class="text-streamify-light">
                <i class="bi bi-tags me-1"></i>
                <?php echo htmlspecialchars($item['categories']); ?>
            </small>
        </div>
    <?php endif; ?>
    
    <p class="card-text text-streamify-light">
        <?php echo substr(htmlspecialchars($item['description']), 0, 100); ?>
        <?php echo strlen($item['description']) > 100 ? '...' : ''; ?>
    </p>
    
    <div class="d-flex justify-content-between align-items-center">
        <small class="text-streamify-light">
            <i class="bi bi-eye me-1"></i>
            <?php echo number_format($item['views']); ?> views
        </small>
        <a href="watch.php?id=<?php echo $item['content_id']; ?>" 
           class="btn btn-streamify btn-sm">
            <i class="bi bi-play-circle me-1"></i>Watch
        </a>
    </div>
</div>                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Content pagination" class="mt-5">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <?php include "footer.php"; ?>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-submit form on filter change
        document.querySelectorAll('.form-select').forEach(select => {
            select.addEventListener('change', function() {
                this.form.submit();
            });
        });
        
        // Search on Enter key
        document.querySelector('input[name="search"]').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                this.form.submit();
            }
        });
        
        // Smooth scroll to results after form submission
        if (window.location.search) {
            setTimeout(() => {
                document.querySelector('.results-header').scrollIntoView({ 
                    behavior: 'smooth',
                    block: 'start'
                });
            }, 100);
        }
    </script>
</body>
</html>
