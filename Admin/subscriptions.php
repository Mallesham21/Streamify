<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Subscriptions - Streamify Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <style>
        /* Your existing CSS styles remain the same */
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

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
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

        .subscription-plan {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s;
        }

        .subscription-plan:hover {
            box-shadow: 0 4px 15px rgba(177, 59, 255, 0.2);
            transform: translateY(-2px);
        }

        .subscription-plan.featured {
            border-color: var(--primary-color);
            position: relative;
            overflow: hidden;
        }

        .subscription-plan.featured::before {
            content: 'Most Popular';
            position: absolute;
            top: 10px;
            right: -30px;
            background-color: var(--primary-color);
            color: white;
            padding: 5px 30px;
            transform: rotate(45deg);
            font-size: 0.8rem;
            font-weight: bold;
        }

        .plan-price {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
        }

        .plan-duration {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .plan-features {
            list-style: none;
            padding: 0;
            margin: 15px 0;
        }

        .plan-features li {
            padding: 5px 0;
            display: flex;
            align-items: center;
        }

        .plan-features li i {
            color: var(--secondary-color);
            margin-right: 10px;
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
    </style>
</head>
<body>
    <?php
    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'Streamify');
    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    }

    // Fetch all subscription plans
    $subscriptions_query = "SELECT * FROM subscriptions ORDER BY duration_days";
    $subscriptions_result = $conn->query($subscriptions_query);

    // Subscription statistics
    $total_plans_query = "SELECT COUNT(*) as total FROM subscriptions";
    $active_subscriptions_query = "SELECT COUNT(*) as active FROM user_subscriptions WHERE status = 'active'";
    $total_revenue_query = "SELECT SUM(amount) as revenue FROM payments WHERE status = 'completed'";
    
    $total_plans = $conn->query($total_plans_query)->fetch_assoc()['total'];
    $active_subscriptions = $conn->query($active_subscriptions_query)->fetch_assoc()['active'];
    $total_revenue = $conn->query($total_revenue_query)->fetch_assoc()['revenue'] ?: 0;

    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'add_subscription') {
            // Process add subscription
            $required_fields = ['name', 'price', 'duration_days'];
            $valid = true;
            
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    $valid = false;
                    $message = "Field '$field' is required";
                    $message_type = 'danger';
                    break;
                }
            }
            
            if ($valid) {
                $name = trim($conn->real_escape_string($_POST['name']));
                $price = floatval($_POST['price']);
                $duration_days = intval($_POST['duration_days']);
                $description = isset($_POST['description']) ? trim($conn->real_escape_string($_POST['description'])) : '';
                
                // Process features
                $features = [];
                if (isset($_POST['features']) && !empty($_POST['features'])) {
                    $features_text = $_POST['features'];
                    $features_array = explode("\n", $features_text);
                    $features = array_filter(array_map('trim', $features_array));
                }
                
                // Check if subscription plan with same name already exists
                $check_sql = "SELECT sub_id FROM subscriptions WHERE name = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("s", $name);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                
                if ($check_result->num_rows > 0) {
                    $message = 'A subscription plan with this name already exists';
                    $message_type = 'danger';
                } else {
                    // Insert new subscription plan
                    $insert_sql = "INSERT INTO subscriptions (name, price, duration_days, description, features) VALUES (?, ?, ?, ?, ?)";
                    $insert_stmt = $conn->prepare($insert_sql);
                    
                    // Convert features array to JSON string
                    $features_json = !empty($features) ? json_encode($features) : null;
                    
                    $insert_stmt->bind_param("sdiss", $name, $price, $duration_days, $description, $features_json);
                    
                    if ($insert_stmt->execute()) {
                        $message = 'Subscription plan added successfully';
                        $message_type = 'success';
                        
                        // Refresh the page to show updated data
                        echo '<script>setTimeout(function() { window.location.href = "subscriptions.php"; }, 1500);</script>';
                    } else {
                        $message = 'Error adding subscription plan: ' . $insert_stmt->error;
                        $message_type = 'danger';
                    }
                    
                    $insert_stmt->close();
                }
                $check_stmt->close();
            }
        }
        elseif ($action === 'update_subscription') {
            // Process update subscription
            $required_fields = ['sub_id', 'name', 'price', 'duration_days'];
            $valid = true;
            
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    $valid = false;
                    $message = "Field '$field' is required";
                    $message_type = 'danger';
                    break;
                }
            }
            
            if ($valid) {
                $sub_id = intval($_POST['sub_id']);
                $name = trim($conn->real_escape_string($_POST['name']));
                $price = floatval($_POST['price']);
                $duration_days = intval($_POST['duration_days']);
                $description = isset($_POST['description']) ? trim($conn->real_escape_string($_POST['description'])) : '';
                
                // Process features
                $features = [];
                if (isset($_POST['features']) && !empty($_POST['features'])) {
                    $features_text = $_POST['features'];
                    $features_array = explode("\n", $features_text);
                    $features = array_filter(array_map('trim', $features_array));
                }
                
                // Check if subscription plan exists
                $check_sql = "SELECT sub_id, name FROM subscriptions WHERE sub_id = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("i", $sub_id);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                
                if ($check_result->num_rows === 0) {
                    $message = 'Subscription plan not found';
                    $message_type = 'danger';
                } else {
                    $old_plan = $check_result->fetch_assoc();
                    
                    // Check if another subscription plan with same name already exists
                    $check_name_sql = "SELECT sub_id FROM subscriptions WHERE name = ? AND sub_id != ?";
                    $check_name_stmt = $conn->prepare($check_name_sql);
                    $check_name_stmt->bind_param("si", $name, $sub_id);
                    $check_name_stmt->execute();
                    $check_name_result = $check_name_stmt->get_result();
                    
                    if ($check_name_result->num_rows > 0) {
                        $message = 'Another subscription plan with this name already exists';
                        $message_type = 'danger';
                    } else {
                        // Update subscription plan
                        $update_sql = "UPDATE subscriptions SET name = ?, price = ?, duration_days = ?, description = ?, features = ? WHERE sub_id = ?";
                        $update_stmt = $conn->prepare($update_sql);
                        
                        // Convert features array to JSON string
                        $features_json = !empty($features) ? json_encode($features) : null;
                        
                        $update_stmt->bind_param("sdissi", $name, $price, $duration_days, $description, $features_json, $sub_id);
                        
                        if ($update_stmt->execute()) {
                            $message = 'Subscription plan updated successfully';
                            $message_type = 'success';
                            
                            // Refresh the page to show updated data
                            echo '<script>setTimeout(function() { window.location.href = "subscriptions.php"; }, 1500);</script>';
                        } else {
                            $message = 'Error updating subscription plan: ' . $update_stmt->error;
                            $message_type = 'danger';
                        }
                        
                        $update_stmt->close();
                    }
                    $check_name_stmt->close();
                }
                $check_stmt->close();
            }
        }
        elseif ($action === 'delete_subscription') {
            // Process delete subscription
            if (empty($_POST['sub_id'])) {
                $message = 'Subscription ID is required';
                $message_type = 'danger';
            } else {
                $sub_id = intval($_POST['sub_id']);
                
                // Check if subscription plan exists
                $check_sql = "SELECT name FROM subscriptions WHERE sub_id = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("i", $sub_id);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                
                if ($check_result->num_rows === 0) {
                    $message = 'Subscription plan not found';
                    $message_type = 'danger';
                } else {
                    $plan = $check_result->fetch_assoc();
                    $plan_name = $plan['name'];
                    
                    // Check if there are active subscriptions for this plan
                    $check_active_sql = "SELECT COUNT(*) as active_count FROM user_subscriptions WHERE sub_id = ? AND status = 'active'";
                    $check_active_stmt = $conn->prepare($check_active_sql);
                    $check_active_stmt->bind_param("i", $sub_id);
                    $check_active_stmt->execute();
                    $check_active_result = $check_active_stmt->get_result();
                    $active_count = $check_active_result->fetch_assoc()['active_count'];
                    $check_active_stmt->close();
                    
                    if ($active_count > 0) {
                        $message = "Cannot delete subscription plan. There are $active_count active subscriptions using this plan.";
                        $message_type = 'danger';
                    } else {
                        // Delete subscription plan
                        $delete_sql = "DELETE FROM subscriptions WHERE sub_id = ?";
                        $delete_stmt = $conn->prepare($delete_sql);
                        $delete_stmt->bind_param("i", $sub_id);
                        
                        if ($delete_stmt->execute()) {
                            $message = 'Subscription plan deleted successfully';
                            $message_type = 'success';
                            
                            // Refresh the page to show updated data
                            echo '<script>setTimeout(function() { window.location.href = "subscriptions.php"; }, 1500);</script>';
                        } else {
                            $message = 'Error deleting subscription plan: ' . $delete_stmt->error;
                            $message_type = 'danger';
                        }
                        
                        $delete_stmt->close();
                    }
                }
                $check_stmt->close();
            }
        }
    }
    ?>

    <!-- Alert Container for Messages -->
    <div class="alert-container">
        <?php if (isset($message) && isset($message_type)): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                <i class="fas <?php echo $message_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?> me-2"></i>
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    </div>

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
                                <h4 class="mb-0">Manage Subscriptions</h4>
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
                
                <!-- Subscription Content -->
                <div class="p-4">
                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="card stats-card">
                                <i class="fas fa-crown"></i>
                                <div class="number"><?php echo $total_plans; ?></div>
                                <div>Total Plans</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card stats-card">
                                <i class="fas fa-users"></i>
                                <div class="number"><?php echo $active_subscriptions; ?></div>
                                <div>Active Subscriptions</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card stats-card">
                                <i class="fas fa-money-bill-wave"></i>
                                <div class="number">₹<?php echo number_format($total_revenue, 2); ?></div>
                                <div>Total Revenue</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card stats-card">
                                <i class="fas fa-chart-line"></i>
                                <div class="number"><?php echo $total_plans > 0 ? number_format($active_subscriptions / $total_plans, 1) : '0'; ?></div>
                                <div>Avg per Plan</div>
                            </div>
                        </div>
                    </div>

                    <!-- Subscription Plans Card -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Subscription Plans</h5>
                            <div>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSubscriptionModal">
                                    <i class="fas fa-plus me-1"></i> Add New Plan
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="subscriptionsTable" class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Plan Name</th>
                                            <th>Price</th>
                                            <th>Duration</th>
                                            <th>Description</th>
                                            <th>Features</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        // Reset pointer for subscriptions result
                                        $subscriptions_result->data_seek(0);
                                        while($plan = $subscriptions_result->fetch_assoc()): 
                                            $features = json_decode($plan['features'], true);
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold"><?php echo $plan['name']; ?></div>
                                                <small class="text-muted">ID: <?php echo $plan['sub_id']; ?></small>
                                            </td>
                                            <td>
                                                <span class="fw-bold text-primary">₹<?php echo $plan['price']; ?></span>
                                            </td>
                                            <td><?php echo $plan['duration_days']; ?> days</td>
                                            <td><?php echo $plan['description']; ?></td>
                                            <td>
                                                <?php if($features): ?>
                                                    <ul class="list-unstyled mb-0">
                                                        <?php foreach(array_slice($features, 0, 2) as $feature): ?>
                                                            <li><small><i class="fas fa-check text-success me-1"></i><?php echo $feature; ?></small></li>
                                                        <?php endforeach; ?>
                                                        <?php if(count($features) > 2): ?>
                                                            <li><small class="text-muted">+<?php echo count($features) - 2; ?> more</small></li>
                                                        <?php endif; ?>
                                                    </ul>
                                                <?php else: ?>
                                                    <span class="text-muted">No features</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button class="btn btn-sm btn-outline-info edit-subscription-btn"
                                                            data-sub_id="<?php echo $plan['sub_id']; ?>"
                                                            data-name="<?php echo htmlspecialchars($plan['name']); ?>"
                                                            data-price="<?php echo $plan['price']; ?>"
                                                            data-duration_days="<?php echo $plan['duration_days']; ?>"
                                                            data-description="<?php echo htmlspecialchars($plan['description']); ?>"
                                                            data-features='<?php echo json_encode($features); ?>'
                                                            title="Edit Plan">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger delete-subscription-btn"
                                                            data-sub_id="<?php echo $plan['sub_id']; ?>"
                                                            data-name="<?php echo htmlspecialchars($plan['name']); ?>"
                                                            title="Delete Plan">
                                                        <i class="fas fa-trash"></i>
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

                    <!-- Subscription Plans Preview -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Subscription Plans Preview</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php 
                                // Reset pointer for subscriptions result again
                                $subscriptions_result->data_seek(0);
                                while($plan = $subscriptions_result->fetch_assoc()): 
                                    $is_featured = $plan['sub_id'] == 2; // Mark 3-month plan as featured
                                    $features = json_decode($plan['features'], true);
                                ?>
                                <div class="col-md-4 mb-4">
                                    <div class="subscription-plan <?php echo $is_featured ? 'featured' : ''; ?>">
                                        <h4 class="text-center"><?php echo $plan['name']; ?></h4>
                                        <div class="text-center mb-3">
                                            <span class="plan-price">₹<?php echo $plan['price']; ?></span>
                                            <div class="plan-duration"><?php echo $plan['duration_days']; ?> days</div>
                                        </div>
                                        <p class="text-center text-muted"><?php echo $plan['description']; ?></p>
                                        <ul class="plan-features">
                                            <?php if($features): ?>
                                                <?php foreach($features as $feature): ?>
                                                    <li><i class="fas fa-check"></i> <?php echo $feature; ?></li>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </ul>
                                        <div class="text-center">
                                            <span class="badge bg-primary">Plan ID: <?php echo $plan['sub_id']; ?></span>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Subscription Modal -->
    <div class="modal fade" id="addSubscriptionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Subscription Plan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="subscriptions.php">
                    <input type="hidden" name="action" value="add_subscription">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="plan_name" class="form-label">Plan Name *</label>
                            <input type="text" class="form-control" id="plan_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="plan_price" class="form-label">Price (₹) *</label>
                            <input type="number" class="form-control" id="plan_price" name="price" min="0" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label for="plan_duration" class="form-label">Duration (days) *</label>
                            <input type="number" class="form-control" id="plan_duration" name="duration_days" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label for="plan_description" class="form-label">Description</label>
                            <textarea class="form-control" id="plan_description" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Features (one per line)</label>
                            <textarea class="form-control" id="plan_features" name="features" rows="4" placeholder="HD Streaming&#10;Multiple Devices&#10;Offline Downloads"></textarea>
                            <small class="form-text text-muted">Enter each feature on a new line</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Plan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Subscription Modal -->
    <div class="modal fade" id="editSubscriptionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Subscription Plan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="subscriptions.php">
                    <input type="hidden" name="action" value="update_subscription">
                    <input type="hidden" id="edit_sub_id" name="sub_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_plan_name" class="form-label">Plan Name *</label>
                            <input type="text" class="form-control" id="edit_plan_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_plan_price" class="form-label">Price (₹) *</label>
                            <input type="number" class="form-control" id="edit_plan_price" name="price" min="0" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_plan_duration" class="form-label">Duration (days) *</label>
                            <input type="number" class="form-control" id="edit_plan_duration" name="duration_days" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_plan_description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit_plan_description" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Features (one per line)</label>
                            <textarea class="form-control" id="edit_plan_features" name="features" rows="4"></textarea>
                            <small class="form-text text-muted">Enter each feature on a new line</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Plan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Subscription Modal -->
    <div class="modal fade" id="deleteSubscriptionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Subscription Plan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="subscriptions.php">
                    <input type="hidden" name="action" value="delete_subscription">
                    <input type="hidden" id="delete_sub_id" name="sub_id">
                    <div class="modal-body">
                        <p>Are you sure you want to delete the "<span id="delete_plan_name"></span>" plan?</p>
                        <p class="text-danger">This action cannot be undone. Users subscribed to this plan will be affected.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Plan</button>
                    </div>
                </form>
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
            $('#subscriptionsTable').DataTable({
                "pageLength": 10,
                "responsive": true,
                "order": [[0, 'asc']]
            });

            // Edit subscription modal
            $(document).on('click', '.edit-subscription-btn', function() {
                const btn = $(this);
                $('#edit_sub_id').val(btn.data('sub_id'));
                $('#edit_plan_name').val(btn.data('name'));
                $('#edit_plan_price').val(btn.data('price'));
                $('#edit_plan_duration').val(btn.data('duration_days'));
                $('#edit_plan_description').val(btn.data('description'));
                
                // Convert features array to text
                const features = btn.data('features');
                if (features && Array.isArray(features)) {
                    $('#edit_plan_features').val(features.join('\n'));
                } else {
                    $('#edit_plan_features').val('');
                }
                
                $('#editSubscriptionModal').modal('show');
            });

            // Delete subscription modal
            $(document).on('click', '.delete-subscription-btn', function() {
                const subId = $(this).data('sub_id');
                const planName = $(this).data('name');
                $('#delete_sub_id').val(subId);
                $('#delete_plan_name').text(planName);
                $('#deleteSubscriptionModal').modal('show');
            });

            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').alert('close');
            }, 5000);
        });
    </script>
</body>
</html>
<?php
// Close database connection
$conn->close();
?>