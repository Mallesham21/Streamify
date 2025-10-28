<?php
// Start session and include database connection
session_start();
require_once 'db.php';
require_once 'notification_manager.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = false;
$error = '';
$plan_name = '';
$end_date_formatted = '';
$payment_id = '';

// Initialize NotificationManager
$notificationManager = new NotificationManager($conn);

// Verify payment and activate subscription
if (isset($_GET['payment_id']) && isset($_GET['subscription_id'])) {
    $razorpay_payment_id = $_GET['payment_id'];
    $sub_id = $_GET['subscription_id'];
    $razorpay_order_id = $_GET['order_id'] ?? '';
    $razorpay_signature = $_GET['signature'] ?? '';
    
    // Get subscription details
    $subscription_sql = "SELECT * FROM subscriptions WHERE sub_id = ?";
    $stmt = $conn->prepare($subscription_sql);
    $stmt->bind_param("i", $sub_id);
    $stmt->execute();
    $subscription = $stmt->get_result()->fetch_assoc();
    
    if ($subscription) {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Calculate dates
            $start_date = date('Y-m-d H:i:s');
            $end_date = date('Y-m-d H:i:s', strtotime("+{$subscription['duration_days']} days"));
            
            // Check if user already has an active subscription
            $check_active_sql = "SELECT id FROM user_subscriptions WHERE user_id = ? AND status = 'active' AND end_date > NOW()";
            $stmt = $conn->prepare($check_active_sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $active_sub = $stmt->get_result()->fetch_assoc();
            
            if ($active_sub) {
                // Update existing subscription to expired
                $update_sql = "UPDATE user_subscriptions SET status = 'expired' WHERE id = ?";
                $stmt = $conn->prepare($update_sql);
                $stmt->bind_param("i", $active_sub['id']);
                $stmt->execute();
            }
            
            // Insert into user_subscriptions
            $insert_sql = "INSERT INTO user_subscriptions (user_id, sub_id, start_date, end_date, status) 
                          VALUES (?, ?, ?, ?, 'active')";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param("iiss", $user_id, $sub_id, $start_date, $end_date);
            
            if ($stmt->execute()) {
                $subscription_id = $stmt->insert_id;
                
                // Insert into payments table
                $payment_sql = "INSERT INTO payments (user_id, subscription_id, amount, currency, payment_method, status, transaction_id, payment_date) 
                               VALUES (?, ?, ?, 'INR', 'razorpay', 'completed', ?, NOW())";
                $stmt = $conn->prepare($payment_sql);
                $stmt->bind_param("iids", $user_id, $subscription_id, $subscription['price'], $razorpay_payment_id);
                
                if ($stmt->execute()) {
                    // Update user's premium status
                    $update_user_sql = "UPDATE users SET subscription_type = 'premium', is_premium = 1 WHERE user_id = ?";
                    $stmt = $conn->prepare($update_user_sql);
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    
                    // Get user details for notification
                    $user_sql = "SELECT username FROM users WHERE user_id = ?";
                    $stmt = $conn->prepare($user_sql);
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $user = $stmt->get_result()->fetch_assoc();
                    
                    // Send subscription success notification
                    if ($user) {
                        $notificationManager->createSubscriptionSuccess($user_id, $subscription['name'], $end_date);
                    }
                    
                    // Log activity
                    $activity_sql = "INSERT INTO activity_logs (user_id, action, details, ip_address, user_agent) 
                                    VALUES (?, 'subscription_purchase', ?, ?, ?)";
                    $stmt = $conn->prepare($activity_sql);
                    $action_details = json_encode([
                        'subscription_id' => $sub_id,
                        'plan_name' => $subscription['name'],
                        'amount' => $subscription['price'],
                        'payment_id' => $razorpay_payment_id
                    ]);
                    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
                    $stmt->bind_param("isss", $user_id, $action_details, $ip_address, $user_agent);
                    $stmt->execute();
                    
                    // Commit transaction
                    $conn->commit();
                    
                    // Set success variables
                    $success = true;
                    $plan_name = $subscription['name'];
                    $end_date_formatted = date('F j, Y', strtotime($end_date));
                    $payment_id = $razorpay_payment_id;
                    
                    // Clear session
                    unset($_SESSION['selected_subscription']);
                    
                } else {
                    throw new Exception("Failed to record payment.");
                }
            } else {
                throw new Exception("Failed to activate subscription.");
            }
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $error = $e->getMessage();
        }
    } else {
        $error = "Invalid subscription selected.";
    }
} else {
    $error = "Invalid payment details.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Status | Streamify</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --streamify-primary: #b13bff;
            --streamify-secondary: #00ccff;
            --streamify-dark: #1c0f24;
            --streamify-success: #28a745;
            --streamify-danger: #dc3545;
        }
        
        body {
            background: linear-gradient(135deg, var(--streamify-dark) 0%, #2a1b3d 100%);
            min-height: 100vh;
            color: white;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .success-container {
            max-width: 600px;
            margin: 0 auto;
        }
        
        .status-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .btn-dashboard {
            background: linear-gradient(45deg, var(--streamify-primary), var(--streamify-secondary));
            border: none;
            color: white;
            padding: 1rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-dashboard:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(177, 59, 255, 0.4);
            color: white;
        }
        
        .btn-outline-light {
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 1rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-outline-light:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: white;
            color: white;
        }
        
        .success-icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
        }
        
        .feature-list {
            list-style: none;
            padding: 0;
        }
        
        .feature-list li {
            padding: 0.5rem 0;
            display: flex;
            align-items: center;
        }
        
        .feature-list li i {
            color: var(--streamify-success);
            margin-right: 0.75rem;
            font-size: 1.1rem;
        }
        
        .subscription-details {
            background: rgba(177, 59, 255, 0.1);
            border-radius: 15px;
            padding: 1.5rem;
            margin: 1.5rem 0;
        }
        
        .payment-info {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 1rem;
            font-family: monospace;
            font-size: 0.9rem;
            word-break: break-all;
        }
        
        .notification-badge {
            background: rgba(177, 59, 255, 0.2);
            border: 1px solid rgba(177, 59, 255, 0.3);
            color: var(--streamify-primary);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <?php include "header.php"; ?>
    
    <div class="container py-5">
        <div class="success-container">
            <div class="status-card p-5 text-center">
                <?php if ($success): ?>
                    <!-- Success State -->
                    <div class="success-icon text-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h1 class="text-success mb-3">Payment Successful!</h1>
                    <p class="lead mb-4">Welcome to Streamify Premium!</p>
                    

                    <!-- Subscription Details -->
                    <div class="subscription-details text-start">
                        <h5 class="mb-3"><i class="fas fa-crown me-2 text-warning"></i>Subscription Activated</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Plan:</strong><br>
                                <?php echo htmlspecialchars($plan_name); ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Valid Until:</strong><br>
                                <?php echo $end_date_formatted; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Premium Features -->
                    <div class="mb-4 text-start">
                        <h6 class="mb-3">You now have access to premium features:</h6>
                        <ul class="feature-list">
                            <li><i class="fas fa-check"></i> HD & 4K Streaming Quality</li>
                            <li><i class="fas fa-check"></i> Ad-free Viewing Experience</li>
                            <li><i class="fas fa-check"></i> Exclusive Premium Content</li>
                            <li><i class="fas fa-check"></i> Multiple Device Support</li>
                            <li><i class="fas fa-check"></i> Offline Downloads</li>
                            <li><i class="fas fa-check"></i> Early Access to New Releases</li>
                        </ul>
                    </div>
                    
                    <!-- Payment Information -->
                    <div class="mb-4">
                        <h6>Payment Information</h6>
                        <div class="payment-info">
                            <small>
                                Transaction ID: <?php echo htmlspecialchars($payment_id); ?><br>
                                Status: <span class="text-success">Completed</span>
                            </small>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="d-grid gap-2 d-md-block">
                        <a href="index.php" class="btn btn-dashboard me-md-3 mb-2">
                            <i class="fas fa-tachometer-alt me-2"></i>Back to Home
                        </a>
                        <a href="browse.php" class="btn btn-outline-light mb-2">
                            <i class="fas fa-play-circle me-2"></i>Start Watching
                        </a>
                    </div>
                    

                <?php else: ?>
                    <!-- Error State -->
                    <div class="success-icon text-danger">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <h1 class="text-danger mb-3">Payment Failed</h1>
                    <p class="lead mb-4"><?php echo $error ?? 'There was an issue processing your payment.'; ?></p>
                    
                    <!-- Troubleshooting Tips -->
                    <div class="alert alert-warning text-start mb-4">
                        <h6><i class="fas fa-lightbulb me-2"></i>Troubleshooting Tips:</h6>
                        <ul class="mb-0">
                            <li>Check your payment method details</li>
                            <li>Ensure sufficient balance in your account</li>
                            <li>Try using a different payment method</li>
                            <li>Contact your bank if issues persist</li>
                        </ul>
                    </div>
                    
                    <!-- Action Buttons for Error -->
                    <div class="d-grid gap-2 d-md-block">
                        <a href="subscription.php" class="btn btn-primary me-md-3 mb-2">
                            <i class="fas fa-arrow-left me-2"></i>Back to Subscriptions
                        </a>
                        <a href="support.php" class="btn btn-outline-light mb-2">
                            <i class="fas fa-headset me-2"></i>Contact Support
                        </a>
                    </div>
                    
                    <!-- Additional Help -->
                    <div class="mt-4">
                        <small class="text-muted">
                            If you were charged but see this error, please contact our support team immediately.
                        </small>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Security Notice -->
            <div class="text-center mt-4">
                <small class="text-muted">
                    <i class="fas fa-lock me-1"></i>
                    Your payment was securely processed by Razorpay. We do not store your payment details.
                </small>
            </div>
        </div>
    </div>
    
    <?php include "footer.php"; ?>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-redirect to dashboard after 10 seconds on success
        <?php if ($success): ?>
        setTimeout(function() {
            window.location.href = 'index.php';
        }, 15000);
        <?php endif; ?>
        
        // Add some interactive effects
        document.addEventListener('DOMContentLoaded', function() {
            const buttons = document.querySelectorAll('.btn');
            buttons.forEach(button => {
                button.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px)';
                });
                button.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });
    </script>
</body>
</html>

<?php
// Close database connection
$conn->close();
?>