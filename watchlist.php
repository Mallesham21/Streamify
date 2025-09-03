<?php
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle remove from watchlist
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_content'])) {
    $content_id = intval($_POST['content_id']);
    
    $stmt = $conn->prepare("DELETE FROM watchlist WHERE user_id = ? AND content_id = ?");
    $stmt->bind_param("ii", $user_id, $content_id);
    $stmt->execute();
    $stmt->close();
    
    // Refresh the page to show updated watchlist
    header("Location: watchlist.php");
    exit();
}

// Fetch user's watchlist with content details
$watchlist_query = "
    SELECT c.content_id, c.title, c.description, c.thumbnail_url, c.content_type, 
           c.rating as content_rating, GROUP_CONCAT(cat.name SEPARATOR ', ') as categories,
           MAX(w.added_at) as added_at
    FROM watchlist w
    JOIN content c ON w.content_id = c.content_id
    LEFT JOIN content_categories cc ON c.content_id = cc.content_id
    LEFT JOIN categories cat ON cc.category_id = cat.category_id
    WHERE w.user_id = ?
    GROUP BY c.content_id, c.title, c.description, c.thumbnail_url, c.content_type, c.rating
    ORDER BY added_at DESC
";

$stmt = $conn->prepare($watchlist_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$watchlist_items = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Watchlist | Streamify</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
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
</style></head>
<body>
    <?php include "header.php"; ?>

    <!-- Main Content -->
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="fw-bold">My Watchlist</h1>
            <span class="badge bg-primary rounded-pill"><?php echo count($watchlist_items); ?> items</span>
        </div>

        <?php if (empty($watchlist_items)): ?>
            <!-- Empty State -->
            <div class="empty-state text-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-bookmark-star mb-3" viewBox="0 0 16 16">
                    <path d="M7.84 4.1a.178.178 0 0 1 .32 0l.634 1.285a.178.178 0 0 0 .134.098l1.42.206c.145.021.204.2.098.303L9.42 6.993a.178.178 0 0 0-.051.158l.242 1.414a.178.178 0 0 1-.258.187l-1.27-.668a.178.178 0 0 0-.165 0l-1.27.668a.178.178 0 0 1-.257-.187l.242-1.414a.178.178 0 0 0-.05-.158l-1.03-1.001a.178.178 0 0 1 .098-.303l1.42-.206a.178.178 0 0 0 .134-.098L7.84 4.1z"/>
                    <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v13.5a.5.5 0 0 1-.777.416L8 13.101l-5.223 2.815A.5.5 0 0 1 2 15.5V2zm2-1a1 1 0 0 0-1 1v12.566l4.723-2.482a.5.5 0 0 1 .554 0L13 14.566V2a1 1 0 0 0-1-1H4z"/>
                </svg>
                <h2 class="mb-3">Your watchlist is empty</h2>
                <p class="text-muted mb-4">Start adding movies and TV shows to watch later</p>
                <a href="index.php" class="btn btn-streamify">Browse Content</a>
            </div>
        <?php else: ?>
            <!-- Watchlist Items -->
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">
                <?php foreach ($watchlist_items as $item): ?>
                    <div class="col">
                        <div class="card h-100">
                            <img src="<?php echo htmlspecialchars($item['thumbnail_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($item['title']); ?>">
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
                                <p class="card-text text-muted"><?php echo substr(htmlspecialchars($item['description']), 0, 100); ?>...</p>
                            </div>
                            <div class="card-footer bg-transparent border-top-0">
                                <div class="d-flex justify-content-between">
                                    <a href="watch.php?id=<?php echo $item['content_id']; ?>" class="btn btn-sm btn-outline-light">View Details</a>
                                    <form method="POST" action="watchlist.php">
                                        <input type="hidden" name="content_id" value="<?php echo $item['content_id']; ?>">
                                        <button type="submit" name="remove_content" class="btn btn-sm btn-outline-danger">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                                <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                                                <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                                            </svg>
                                            Remove
                                        </button>
                                    </form>
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