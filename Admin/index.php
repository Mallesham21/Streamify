<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Streamify Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom CSS -->
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
            box-shadow: 0 0 15px rgba(177, 59, 255, 0.1);
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
            box-shadow: 0 2px 15px rgba(177, 59, 255, 0.1);
        }

        .card {
            background-color: var(--card);
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(177, 59, 255, 0.15);
            transition: transform 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: #5a32a3;
            border-color: #5a32a3;
        }

        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        .stat-card {
            text-align: center;
            padding: 20px;
            border-left: 4px solid var(--primary-color);
        }

        .stat-card i {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: var(--primary-color);
        }

        .stat-card .number {
            font-size: 2rem;
            font-weight: bold;
            margin: 10px 0;
        }

        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }

        .table {
            color: var(--text);
            margin-bottom: 0;
        }

        .table th {
            border-top: none;
            font-weight: 600;
            background-color: rgba(177, 59, 255, 0.1);
        }

        .badge-premium {
            background-color: var(--secondary-color);
        }

        .badge-free {
            background-color: #6c757d;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .progress {
            height: 6px;
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

        .alert-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
        }

        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <?php
    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'Streamify');
    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    }

    // Fetch data for dashboard widgets
    // Total Users
    $users_query = "SELECT COUNT(*) as total_users FROM users";
    $users_result = $conn->query($users_query);
    $total_users = $users_result->fetch_assoc()['total_users'];

    // Total Content
    $content_query = "SELECT COUNT(*) as total_content FROM content";
    $content_result = $conn->query($content_query);
    $total_content = $content_result->fetch_assoc()['total_content'];

    // Active Subscriptions
    $subs_query = "SELECT COUNT(*) as active_subs FROM user_subscriptions WHERE status = 'active'";
    $subs_result = $conn->query($subs_query);
    $active_subs = $subs_result->fetch_assoc()['active_subs'];

    // Revenue Overview (for chart)
    $revenue_query = "SELECT 
    SUM(CASE WHEN s.sub_id = 1 THEN s.price ELSE 0 END) AS monthly_revenue,
    SUM(CASE WHEN s.sub_id = 2 THEN s.price ELSE 0 END) AS quarterly_revenue,
    SUM(CASE WHEN s.sub_id = 3 THEN s.price ELSE 0 END) AS yearly_revenue
FROM subscriptions s
JOIN user_subscriptions us ON s.sub_id = us.sub_id
WHERE us.status = 'active'";
    $revenue_result = $conn->query($revenue_query);
    $revenue_data = $revenue_result->fetch_assoc();

    // Recent User Activity
    $activity_query = "SELECT u.username, c.title, wh.progress_percent, wh.last_watched 
                      FROM watch_history wh
                      JOIN users u ON wh.user_id = u.user_id
                      JOIN content c ON wh.content_id = c.content_id
                      ORDER BY wh.last_watched DESC LIMIT 5";
    $activity_result = $conn->query($activity_query);

    // System Notifications
    $notifications_query = "SELECT message, created_at FROM notifications ORDER BY created_at DESC LIMIT 5";
    $notifications_result = $conn->query($notifications_query);

    // Content by category for chart
    $category_query = "SELECT cat.name, COUNT(cc.content_id) as count 
                      FROM categories cat
                      LEFT JOIN content_categories cc ON cat.category_id = cc.category_id
                      GROUP BY cat.category_id, cat.name";
    $category_result = $conn->query($category_query);
    $category_names = [];
    $category_counts = [];
    while($row = $category_result->fetch_assoc()) {
        $category_names[] = $row['name'];
        $category_counts[] = $row['count'];
    }

    // Additional statistics
    $total_movies = $conn->query("SELECT COUNT(*) as count FROM content WHERE content_type = 'movie'")->fetch_assoc()['count'];
    $total_tv_shows = $conn->query("SELECT COUNT(*) as count FROM content WHERE content_type = 'tv_show'")->fetch_assoc()['count'];
    $total_featured = $conn->query("SELECT COUNT(*) as count FROM content WHERE featured = 1")->fetch_assoc()['count'];
    $total_views = $conn->query("SELECT SUM(views) as total FROM content")->fetch_assoc()['total'];
    $total_feedback = $conn->query("SELECT COUNT(*) as count FROM feedback")->fetch_assoc()['count'];
    $total_premium_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE is_premium = 1")->fetch_assoc()['count'];
    ?>

    <!-- Alert Container for Messages -->
    <div class="alert-container"></div>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'includes/sidebar.php'; ?>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 ml-auto p-0">
       <!-- Top Navbar -->
<nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
        <div class="navbar-nav me-auto">
            <span class="navbar-text">
                <h4 class="mb-0">Dashboard Overview</h4>
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
                <!-- Dashboard Content -->
                <div class="p-4">

                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-md-2 mb-3">
                            <div class="card stats-card">
                                <i class="fas fa-users"></i>
                                <div class="number"><?php echo $total_users; ?></div>
                                <div>Total Users</div>
                            </div>
                        </div>
                        <div class="col-md-2 mb-3">
                            <div class="card stats-card">
                                <i class="fas fa-video"></i>
                                <div class="number"><?php echo $total_content; ?></div>
                                <div>Total Content</div>
                            </div>
                        </div>
                        <div class="col-md-2 mb-3">
                            <div class="card stats-card">
                                <i class="fas fa-credit-card"></i>
                                <div class="number"><?php echo $active_subs; ?></div>
                                <div>Active Subscriptions</div>
                            </div>
                        </div>
                        <div class="col-md-2 mb-3">
                            <div class="card stats-card">
                                <i class="fas fa-film"></i>
                                <div class="number"><?php echo $total_movies; ?></div>
                                <div>Total Movies</div>
                            </div>
                        </div>
                        <div class="col-md-2 mb-3">
                            <div class="card stats-card">
                                <i class="fas fa-tv"></i>
                                <div class="number"><?php echo $total_tv_shows; ?></div>
                                <div>Total TV Shows</div>
                            </div>
                        </div>
                        <div class="col-md-2 mb-3">
                            <div class="card stats-card">
                                <i class="fas fa-star"></i>
                                <div class="number"><?php echo $total_featured; ?></div>
                                <div>Featured Content</div>
                            </div>
                        </div>
                    </div>

                    <!-- Second Row Stats -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="card stats-card">
                                <i class="fas fa-eye"></i>
                                <div class="number"><?php echo number_format($total_views); ?></div>
                                <div>Total Views</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card stats-card">
                                <i class="fas fa-comment"></i>
                                <div class="number"><?php echo $total_feedback; ?></div>
                                <div>Total Feedback</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card stats-card">
                                <i class="fas fa-crown"></i>
                                <div class="number"><?php echo $total_premium_users; ?></div>
                                <div>Premium Users</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card stats-card">
                                <i class="fas fa-chart-line"></i>
                                <div class="number">₹<?php echo number_format($revenue_data['monthly_revenue'] + $revenue_data['quarterly_revenue'] + $revenue_data['yearly_revenue']); ?></div>
                                <div>Total Revenue</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Charts Row -->
                    <div class="row mb-4">
                        <div class="col-md-8 mb-3">
                            <div class="card p-3">
                                <h5 class="card-title">Revenue Overview</h5>
                                <div class="chart-container">
                                    <canvas id="revenueChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card p-3">
                                <h5 class="card-title">Content by Category</h5>
                                <div class="chart-container">
                                    <canvas id="categoryChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tables Row -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Recent User Activity</h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>User</th>
                                                    <th>Content</th>
                                                    <th>Progress</th>
                                                    <th>Last Watched</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while($activity = $activity_result->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo $activity['username']; ?></td>
                                                    <td><?php echo $activity['title']; ?></td>
                                                    <td>
                                                        <div class="progress">
                                                            <div class="progress-bar" role="progressbar" style="width: <?php echo $activity['progress_percent']; ?>%;"></div>
                                                        </div>
                                                        <small><?php echo $activity['progress_percent']; ?>%</small>
                                                    </td>
                                                    <td><?php echo date('M j, g:i A', strtotime($activity['last_watched'])); ?></td>
                                                </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Top Content</h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Title</th>
                                                    <th>Type</th>
                                                    <th>Rating</th>
                                                    <th>Views</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $top_content_query = "SELECT title, content_type, rating, views FROM content ORDER BY views DESC LIMIT 5";
                                                $top_content_result = $conn->query($top_content_query);
                                                while($content = $top_content_result->fetch_assoc()):
                                                ?>
                                                <tr>
                                                    <td><?php echo $content['title']; ?></td>
                                                    <td>
                                                        <span class="badge <?php echo $content['content_type'] == 'movie' ? 'badge-movie' : 'badge-tv-show'; ?>">
                                                            <?php echo $content['content_type'] == 'movie' ? 'Movie' : 'TV Show'; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <i class="fas fa-star text-warning"></i> <?php echo $content['rating']; ?>
                                                    </td>
                                                    <td><?php echo number_format($content['views']); ?></td>
                                                </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(revenueCtx, {
            type: 'bar',
            data: {
                labels: ['Monthly', 'Quarterly', 'Yearly'],
                datasets: [{
                    label: 'Revenue (₹)',
                    data: [
                        <?php echo $revenue_data['monthly_revenue'] ?? 0; ?>,
                        <?php echo $revenue_data['quarterly_revenue'] ?? 0; ?>,
                        <?php echo $revenue_data['yearly_revenue'] ?? 0; ?>
                    ],
                    backgroundColor: [
                        'rgba(111, 66, 193, 0.7)',
                        'rgba(32, 201, 151, 0.7)',
                        'rgba(13, 110, 253, 0.7)'
                    ],
                    borderColor: [
                        'rgba(111, 66, 193, 1)',
                        'rgba(32, 201, 151, 1)',
                        'rgba(13, 110, 253, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Category Chart
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        const categoryChart = new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($category_names); ?>,
                datasets: [{
                    data: <?php echo json_encode($category_counts); ?>,
                    backgroundColor: [
                        'rgba(111, 66, 193, 0.7)',
                        'rgba(32, 201, 151, 0.7)',
                        'rgba(13, 110, 253, 0.7)',
                        'rgba(255, 193, 7, 0.7)',
                        'rgba(220, 53, 69, 0.7)',
                        'rgba(253, 126, 20, 0.7)',
                        'rgba(102, 16, 242, 0.7)',
                        'rgba(32, 201, 151, 0.7)',
                        'rgba(214, 51, 132, 0.7)',
                        'rgba(13, 202, 240, 0.7)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Function to show alert messages
        function showAlert(message, type = 'info') {
            const alertContainer = $('.alert-container');
            const alertId = 'alert-' + Date.now();
            
            const iconClass = {
                'success': 'fa-check-circle',
                'danger': 'fa-exclamation-triangle',
                'warning': 'fa-exclamation-circle',
                'info': 'fa-info-circle'
            }[type] || 'fa-info-circle';
            
            const alertHtml = `
                <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show" role="alert">
                    <i class="fas ${iconClass} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            alertContainer.append(alertHtml);
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                $(`#${alertId}`).alert('close');
            }, 5000);
            
            // Remove from DOM after fade out
            $(`#${alertId}`).on('closed.bs.alert', function() {
                $(this).remove();
            });
        }

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').alert('close');
        }, 5000);
    </script>
</body>
</html>
<?php
// Close database connection
$conn->close();
?>