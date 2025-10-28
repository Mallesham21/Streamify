<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Feedback - Streamify Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <style>
        :root {
            --primary-color: #6f42c1;
            --secondary-color: #20c997;
            --bg: #f8f9fa;
            --card: #ffffff;
            --text: #212529;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            color: var(--text);
        }

        .sidebar {
            background-color: var(--card);
            min-height: 100vh;
            box-shadow: 0 0 15px rgba(177,59,255,0.1);
        }

        .sidebar .nav-link {
            color: var(--text);
            padding: 12px 20px;
            margin: 5px 0;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: var(--primary-color);
            color: white;
        }

        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .navbar {
            background-color: var(--card);
            box-shadow: 0 2px 15px rgba(177,59,255,0.1);
        }

        .card {
            background-color: var(--card);
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(177,59,255,0.15);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .stats-card {
            text-align: center;
            padding: 20px;
            border-left: 4px solid var(--primary-color);
        }

        .stats-card i {
            font-size: 2rem;
            margin-bottom: 10px;
            color: var(--primary-color);
        }

        .stats-card .number {
            font-size: 1.8rem;
            font-weight: bold;
            margin: 5px 0;
        }

        .rating-star {
            color: #f1c40f;
        }
    </style>
</head>
<body>
<?php
$conn = new mysqli('localhost', 'root', '', 'Streamify');
if ($conn->connect_error) { die('Connection failed: ' . $conn->connect_error); }

// Stats
$total_feedback = $conn->query("SELECT COUNT(*) as c FROM feedback")->fetch_assoc()['c'] ?? 0;
$avg_rating = $conn->query("SELECT AVG(rating) as a FROM feedback WHERE rating IS NOT NULL")->fetch_assoc()['a'] ?? 0;
$recent_feedback = $conn->query("SELECT COUNT(*) as c FROM feedback WHERE created_at >= NOW() - INTERVAL 7 DAY")->fetch_assoc()['c'] ?? 0;

// Feedback list
$sql = "SELECT f.feedback_id, f.user_id, f.content_id, f.rating, f.review_text, f.created_at, 
               u.username, u.email, c.title, c.content_type
        FROM feedback f
        JOIN users u ON f.user_id = u.user_id
        JOIN content c ON f.content_id = c.content_id
        ORDER BY f.created_at DESC";
$rows = $conn->query($sql);
?>
<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 ml-auto p-0">
            <!-- Top Navbar - Same as manage_users.php -->
            <nav class="navbar navbar-expand-lg">
                <div class="container-fluid">
                    <div class="navbar-nav me-auto">
                        <span class="navbar-text">
                            <h4 class="mb-0">Feedbacks</h4>
                        </span>
                    </div>
                    
                    <ul class="navbar-nav mb-2 mb-lg-0">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown">
                                <img src="https://ui-avatars.com/api/?name=Admin&background=b13bff&color=fff" class="user-avatar me-2">
                                Admin
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>

            <div class="p-4">
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="card stats-card">
                            <i class="fas fa-comments"></i>
                            <div class="number"><?php echo $total_feedback; ?></div>
                            <div>Total Feedback</div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card stats-card">
                            <i class="fas fa-star rating-star"></i>
                            <div class="number"><?php echo number_format((float)$avg_rating, 1); ?></div>
                            <div>Average Rating</div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card stats-card">
                            <i class="fas fa-clock"></i>
                            <div class="number"><?php echo $recent_feedback; ?></div>
                            <div>Last 7 Days</div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">All Feedback</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="feedbackTable" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Content</th>
                                        <th>Rating</th>
                                        <th>Review</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($rows): while($r = $rows->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold"><?php echo htmlspecialchars($r['username']); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($r['email']); ?></small>
                                        </td>
                                        <td>
                                            <div class="fw-bold"><?php echo htmlspecialchars($r['title']); ?></div>
                                            <span class="badge <?php echo ($r['content_type'] === 'movie') ? 'bg-primary' : 'bg-info'; ?>">
                                                <?php echo ($r['content_type'] === 'movie') ? 'Movie' : 'TV Show'; ?>
                                            </span>
                                        </td>
                                        <td><i class="fas fa-star rating-star"></i> <?php echo (int)$r['rating']; ?></td>
                                        <td><?php echo htmlspecialchars(mb_strimwidth($r['review_text'] ?? '', 0, 60, '...')); ?></td>
                                        <td><?php echo date('M j, Y g:i A', strtotime($r['created_at'])); ?></td>
                                    </tr>
                                    <?php endwhile; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script>
$(function(){
    $('#feedbackTable').DataTable({
        pageLength: 10,
        language: {
            search: 'Search feedback:',
            lengthMenu: 'Show _MENU_ items per page',
            info: 'Showing _START_ to _END_ of _TOTAL_ items',
            paginate: { previous: "<i class='fas fa-chevron-left'></i>", next: "<i class='fas fa-chevron-right'></i>" }
        }
    });
});
</script>
</body>
</html>
<?php $conn->close(); ?>