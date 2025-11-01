<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Streamify Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- DataTables CSS -->
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

        .badge-admin {
            background-color: var(--primary-color);
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

        .modal-content {
            border-radius: 12px;
            border: none;
        }

        .modal-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: 12px 12px 0 0;
        }

        .modal-header .btn-close {
            filter: invert(1);
        }

        .subscription-details {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
        }

        .subscription-details h6 {
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .subscription-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .subscription-info .info-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .subscription-info .info-item:last-child {
            border-bottom: none;
        }

        .subscription-status {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .alert-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
        }

        .action-buttons {
            display: flex;
            gap: 5px;
        }

        .action-buttons .btn {
            padding: 0.25rem 0.5rem;
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

    // Fetch all users with subscription details
    $users_query = "SELECT u.user_id, u.username, u.email, u.profile_pic, 
                           u.subscription_type, u.is_premium, u.created_at, u.last_login,
                           us.sub_id, us.start_date, us.end_date, us.status as subscription_status,
                           s.name as plan_name, s.price, s.duration_days
                   FROM users u
                   LEFT JOIN user_subscriptions us ON u.user_id = us.user_id AND us.status = 'active'
                   LEFT JOIN subscriptions s ON us.sub_id = s.sub_id
                   WHERE u.role != 'admin' 
                   ORDER BY u.created_at DESC";
    $users_result = $conn->query($users_query);

    // User statistics
    $total_users_query = "SELECT COUNT(*) as total FROM users WHERE role != 'admin'";
    $premium_users_query = "SELECT COUNT(*) as premium FROM users WHERE is_premium = 1 AND role != 'admin'";
    $active_today_query = "SELECT COUNT(DISTINCT user_id) as active_today FROM watch_history WHERE DATE(last_watched) = CURDATE()";
    $new_users_today_query = "SELECT COUNT(*) as new_today FROM users WHERE DATE(created_at) = CURDATE() AND role != 'admin'";

    $total_users = $conn->query($total_users_query)->fetch_assoc()['total'];
    $premium_users = $conn->query($premium_users_query)->fetch_assoc()['premium'];
    $active_today = $conn->query($active_today_query)->fetch_assoc()['active_today'];
    $new_users_today = $conn->query($new_users_today_query)->fetch_assoc()['new_today'];
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
                                <h4 class="mb-0">User Management</h4>
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

                <!-- Users Content -->
                <div class="p-4">
                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="card stats-card">
                                <i class="fas fa-users"></i>
                                <div class="number"><?php echo $total_users; ?></div>
                                <div>Total Users</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card stats-card">
                                <i class="fas fa-crown"></i>
                                <div class="number"><?php echo $premium_users; ?></div>
                                <div>Premium Users</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card stats-card">
                                <i class="fas fa-eye"></i>
                                <div class="number"><?php echo $active_today; ?></div>
                                <div>Active Today</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card stats-card">
                                <i class="fas fa-user-plus"></i>
                                <div class="number"><?php echo $new_users_today; ?></div>
                                <div>New Today</div>
                            </div>
                        </div>
                    </div>

                    <!-- Users Table Card -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">All Users</h5>
                            <div>
                                <button class="btn btn-outline-primary me-2" onclick="window.location.href='export_users.php'">
                                    <i class="fas fa-download me-1"></i> Export
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="usersTable" class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Email</th>
                                            <th>Subscription</th>
                                            <th>Plan</th>
                                            <th>Status</th>
                                            <th>Joined</th>
                                            <th>Last Login</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($user = $users_result->fetch_assoc()): 
                                            $subscription_active = $user['subscription_status'] === 'active';
                                            $days_remaining = $subscription_active ? floor((strtotime($user['end_date']) - time()) / (60 * 60 * 24)) : 0;
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="<?php echo "../" . $user['profile_pic'] ?: 'https://ui-avatars.com/api/?name=' . urlencode($user['username']) . '&background=b13bff&color=fff'; ?>" 
                                                         class="user-avatar me-3">
                                                    <div>
                                                        <div class="fw-bold"><?php echo $user['username']; ?></div>
                                                        <small class="text-muted">ID: <?php echo $user['user_id']; ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo $user['email']; ?></td>
                                            <td>
                                                <span class="badge <?php echo $user['is_premium'] ? 'badge-premium' : 'badge-free'; ?>">
                                                    <?php echo $user['is_premium'] ? 'Premium' : 'Free'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if($user['plan_name']): ?>
                                                    <span class="fw-bold"><?php echo $user['plan_name']; ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">No active plan</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if($subscription_active): ?>
                                                    <span class="subscription-status text-success">
                                                        <i class="fas fa-circle"></i>
                                                        Active (<?php echo $days_remaining; ?> days left)
                                                    </span>
                                                <?php else: ?>
                                                    <span class="subscription-status text-secondary">
                                                        <i class="fas fa-circle"></i>
                                                        <?php echo $user['subscription_status'] ? ucfirst($user['subscription_status']) : 'No subscription'; ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                            <td>
                                                <?php if($user['last_login']): ?>
                                                    <?php echo date('M j, g:i A', strtotime($user['last_login'])); ?>
                                                <?php else: ?>
                                                    <span class="text-muted">Never</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button 
                                                        class="btn btn-sm btn-outline-info view-user-btn" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#userDetailsModal"
                                                        data-user_id="<?php echo $user['user_id']; ?>"
                                                        data-username="<?php echo htmlspecialchars($user['username']); ?>"
                                                        data-email="<?php echo htmlspecialchars($user['email']); ?>"
                                                        data-profile_pic="<?php echo "../" . $user['profile_pic'] ?: 'https://ui-avatars.com/api/?name=' . urlencode($user['username']) . '&background=b13bff&color=fff'; ?>"
                                                        data-subscription="<?php echo $user['is_premium'] ? 'Premium' : 'Free'; ?>"
                                                        data-is_premium="<?php echo $user['is_premium'] ? 'Yes' : 'No'; ?>"
                                                        data-created_at="<?php echo date('M j, Y', strtotime($user['created_at'])); ?>"
                                                        data-last_login="<?php echo $user['last_login'] ? date('M j, g:i A', strtotime($user['last_login'])) : 'Never'; ?>"
                                                        data-plan_name="<?php echo $user['plan_name'] ?: 'No active plan'; ?>"
                                                        data-subscription_status="<?php echo $user['subscription_status'] ? ucfirst($user['subscription_status']) : 'No subscription'; ?>"
                                                        data-start_date="<?php echo $user['start_date'] ? date('M j, Y', strtotime($user['start_date'])) : 'N/A'; ?>"
                                                        data-end_date="<?php echo $user['end_date'] ? date('M j, Y', strtotime($user['end_date'])) : 'N/A'; ?>"
                                                        data-days_remaining="<?php echo $days_remaining; ?>"
                                                        data-price="<?php echo $user['price'] ? '₹' . $user['price'] : 'N/A'; ?>"
                                                        data-duration_days="<?php echo $user['duration_days'] ?: 'N/A'; ?>"
                                                        title="View Details"
                                                    >
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            </td>
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

    <!-- User Details Modal -->
    <div class="modal fade" id="userDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">User Details - <span id="md_username_title"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-3 text-center mb-3">
                            <img id="md_avatar" src="" class="rounded-circle" style="width: 100px; height: 100px; object-fit: cover;" />
                        </div>
                        <div class="col-md-9">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">User ID</label>
                                    <input id="md_user_id" type="text" class="form-control" readonly />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Username</label>
                                    <input id="md_username" type="text" class="form-control" readonly />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email</label>
                                    <input id="md_email" type="text" class="form-control" readonly />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Subscription Type</label>
                                    <input id="md_subscription" type="text" class="form-control" readonly />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Premium Status</label>
                                    <input id="md_is_premium" type="text" class="form-control" readonly />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Joined Date</label>
                                    <input id="md_created_at" type="text" class="form-control" readonly />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Last Login</label>
                                    <input id="md_last_login" type="text" class="form-control" readonly />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Subscription Details Section -->
                    <div class="subscription-details">
                        <h6><i class="fas fa-crown me-2"></i>Subscription Details</h6>
                        <div class="subscription-info">
                            <div class="info-item">
                                <span class="fw-bold">Plan Name:</span>
                                <span id="md_plan_name"></span>
                            </div>
                            <div class="info-item">
                                <span class="fw-bold">Subscription Status:</span>
                                <span id="md_subscription_status"></span>
                            </div>
                            <div class="info-item">
                                <span class="fw-bold">Start Date:</span>
                                <span id="md_start_date"></span>
                            </div>
                            <div class="info-item">
                                <span class="fw-bold">End Date:</span>
                                <span id="md_end_date"></span>
                            </div>
                            <div class="info-item">
                                <span class="fw-bold">Days Remaining:</span>
                                <span id="md_days_remaining"></span>
                            </div>
                            <div class="info-item">
                                <span class="fw-bold">Price:</span>
                                <span id="md_price"></span>
                            </div>
                            <div class="info-item">
                                <span class="fw-bold">Duration:</span>
                                <span id="md_duration_days"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize DataTables
            $('#usersTable').DataTable({
                "pageLength": 10,
                "responsive": true,
                "order": [[0, 'desc']],
                "language": {
                    "search": "Search users:",
                    "lengthMenu": "Show _MENU_ users per page",
                    "info": "Showing _START_ to _END_ of _TOTAL_ users",
                    "paginate": {
                        "previous": "<i class='fas fa-chevron-left'></i>",
                        "next": "<i class='fas fa-chevron-right'></i>"
                    }
                }
            });

            // View Details modal population
            $(document).on('click', '.view-user-btn', function() {
                const btn = $(this);
                $('#md_avatar').attr('src', btn.data('profile_pic'));
                $('#md_user_id').val(btn.data('user_id'));
                $('#md_username').val(btn.data('username'));
                $('#md_username_title').text(btn.data('username'));
                $('#md_email').val(btn.data('email'));
                $('#md_subscription').val(btn.data('subscription'));
                $('#md_is_premium').val(btn.data('is_premium'));
                $('#md_created_at').val(btn.data('created_at'));
                $('#md_last_login').val(btn.data('last_login'));
                
                // Subscription details
                $('#md_plan_name').text(btn.data('plan_name'));
                $('#md_subscription_status').text(btn.data('subscription_status'));
                $('#md_start_date').text(btn.data('start_date'));
                $('#md_end_date').text(btn.data('end_date'));
                $('#md_days_remaining').text(btn.data('days_remaining') + ' days');
                $('#md_price').text(btn.data('price'));
                $('#md_duration_days').text(btn.data('duration_days') + ' days');
            });

            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').alert('close');
            }, 5000);
        });

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
    </script>
</body>
</html>
<?php
// Close database connection
$conn->close();
?>