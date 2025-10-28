<?php
// Start session and include database connection
session_start();
require_once 'db.php';

// Check if user is logged insession_start();

if (!isset($_SESSION['user_id'])) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            var loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
            loginModal.show();
        });
    </script>";
}


$user_id = $_SESSION['user_id'];

// Check if user already has an active subscription
$active_subscription = null;
$check_subscription_sql = "SELECT us.end_date, s.name, s.duration_days 
                          FROM user_subscriptions us 
                          JOIN subscriptions s ON us.sub_id = s.sub_id 
                          WHERE us.user_id = ? AND us.status = 'active' AND us.end_date > NOW()";
$stmt = $conn->prepare($check_subscription_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $active_subscription = $result->fetch_assoc();
}

// Handle subscription selection
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['subscribe'])) {
    $sub_id = $_POST['sub_id'];
    
    // Store subscription choice in session for payment page
    $_SESSION['selected_subscription'] = $sub_id;
    
    // Redirect to payment page
    header("Location: payment.php");
    exit();
}

// Get all subscription plans (excluding free plan if exists)
$subscriptions_sql = "SELECT * FROM subscriptions WHERE sub_id ORDER BY price ASC";
$subscriptions_result = $conn->query($subscriptions_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Streamify - Subscription Plans</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --streamify-primary: #b13bff;
            --streamify-secondary: #00ccff;
            --streamify-accent: #ff4b2b;
            --streamify-dark: #1c0f24;
            --streamify-light: #f8f9fa;
            --streamify-text: #e2e2e2;
            --streamify-text-muted: #a0a0a0;
        }
        
        body {
            background: linear-gradient(135deg, var(--streamify-dark) 0%, #2a1b3d 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--streamify-text);
        }
        
        .subscription-container {
            padding: 2rem 0;
        }
        
        .page-title {
            color: white;
            text-align: center;
            margin-bottom: 2rem;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        
        .subscription-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            overflow: hidden;
            margin-bottom: 2rem;
            height: 100%;
            backdrop-filter: blur(10px);
        }
        
        .subscription-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(177, 59, 255, 0.2);
            border-color: var(--streamify-primary);
        }
        
        .card-header {
            background: linear-gradient(to right, var(--streamify-primary), var(--streamify-secondary));
            color: white;
            padding: 2rem;
            text-align: center;
            position: relative;
        }
        
        .best-value {
            position: absolute;
            top: 0;
            right: 0;
            background: var(--streamify-accent);
            color: white;
            padding: 0.5rem 1.5rem;
            font-size: 0.8rem;
            font-weight: bold;
            border-bottom-left-radius: 15px;
            box-shadow: 0 2px 10px rgba(255, 75, 43, 0.3);
        }
        
        .card-body {
            padding: 2rem;
        }
        
        .price {
            font-size: 3rem;
            font-weight: 700;
            color: white;
            margin: 1rem 0;
        }
        
        .currency {
            font-size: 1.5rem;
            vertical-align: super;
        }
        
        .period {
            font-size: 1rem;
            color: var(--streamify-text-muted);
        }
        
        .plan-description {
            color: var(--streamify-text-muted);
            margin-bottom: 1.5rem;
            font-style: italic;
        }
        
        .features {
            list-style: none;
            padding: 0;
            margin: 1.5rem 0;
        }
        
        .features li {
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
        }
        
        .features li:last-child {
            border-bottom: none;
        }
        
        .features li i {
            color: var(--streamify-secondary);
            margin-right: 0.75rem;
            font-size: 1.1rem;
        }
        
        .btn-subscribe {
            background: linear-gradient(to right, var(--streamify-primary), var(--streamify-secondary));
            border: none;
            color: white;
            padding: 1rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
            font-size: 1.1rem;
        }
        
        .btn-subscribe:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 25px rgba(177, 59, 255, 0.4);
            color: white;
        }
        
        .active-subscription {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 3rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            border: 1px solid rgba(177, 59, 255, 0.3);
            text-align: center;
        }
        
        .icon-container {
            font-size: 3rem;
            margin-bottom: 1rem;
            background: linear-gradient(45deg, var(--streamify-primary), var(--streamify-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .savings-badge {
            background: rgba(76, 175, 80, 0.2);
            color: #4CAF50;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-left: 0.5rem;
        }
        
        .popular-plan {
            border: 2px solid var(--streamify-primary);
            box-shadow: 0 0 30px rgba(177, 59, 255, 0.3);
        }
        
        @media (max-width: 768px) {
            .subscription-card {
                margin-bottom: 1.5rem;
            }
            
            .price {
                font-size: 2.5rem;
            }
        }
    </style>
</head>
<body class="m-5">
    <?php include "header.php"; ?>
    
    <div class="container subscription-container">
        <h1 class="page-title">Choose Your Streamify Plan</h1>
        
        <?php if ($active_subscription): ?>
            <div class="active-subscription">
                <h3><i class="fas fa-crown text-warning"></i> You're Already Premium!</h3>
                <p class="lead">You have an active <?php echo htmlspecialchars($active_subscription['name']); ?> subscription until 
                <?php echo date('F j, Y', strtotime($active_subscription['end_date'])); ?>.</p>
                <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger text-center"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="row justify-content-center">
            <?php 
            $plans = [];
            while ($plan = $subscriptions_result->fetch_assoc()) {
                $plans[] = $plan;
            }
            
            // Calculate savings for comparison
            $monthly_equivalent = [];
            foreach ($plans as $plan) {
                $monthly_equivalent[$plan['sub_id']] = $plan['price'] / ($plan['duration_days'] / 30);
            }
            
            foreach ($plans as $index => $plan): 
                $is_popular = $plan['sub_id'] == 3; // 12-month plan is popular
                $features = json_decode($plan['features'], true);
                $monthly_price = $monthly_equivalent[$plan['sub_id']];
                $savings = '';
                
                // Calculate savings compared to monthly plan
                if ($plan['sub_id'] != 1 && isset($monthly_equivalent[1])) {
                    $savings_percentage = round((($monthly_equivalent[1] - $monthly_price) / $monthly_equivalent[1]) * 100);
                    if ($savings_percentage > 0) {
                        $savings = '<span class="savings-badge">Save '.$savings_percentage.'%</span>';
                    }
                }
            ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="subscription-card h-100 <?php echo $is_popular ? 'popular-plan' : ''; ?>">
                        <?php if ($is_popular): ?>
                            <div class="best-value">BEST VALUE</div>
                        <?php endif; ?>
                        
                        <div class="card-header">
                            <div class="icon-container">
                                <?php 
                                $icons = [
                                    1 => 'fas fa-star',
                                    2 => 'fas fa-gem',
                                    3 => 'fas fa-crown'
                                ];
                                ?>
                                <i class="<?php echo $icons[$plan['sub_id']]; ?>"></i>
                            </div>
                            <h3><?php echo htmlspecialchars($plan['name']); ?></h3>
                        </div>
                        
                        <div class="card-body text-center d-flex flex-column">
                            <div class="price">
                                <span class="currency">₹</span><?php echo $plan['price']; ?>
                                <?php echo $savings; ?>
                            </div>
                            <p class="period">
                                <?php 
$days = $plan['duration_days'];

if ($days >= 365) {
    echo '12 months'; // or '1 year' if you prefer
} elseif ($days >= 30) {
    $months = $days / 30;
    echo ($months == 1) ? '1 month' : intval($months) . ' months';
} else {
    echo $days . ' days';
}
?>
                                <?php if ($plan['sub_id'] != 1): ?>
                                    <br><small>₹<?php echo number_format($monthly_price, 2); ?>/month</small>
                                <?php endif; ?>
                            </p>
                            
                            <?php if (!empty($plan['description'])): ?>
                                <p class="plan-description"><?php echo htmlspecialchars($plan['description']); ?></p>
                            <?php endif; ?>
                            
                            <?php if (!empty($features)): ?>
                                <ul class="features text-start flex-grow-1">
                                    <?php foreach ($features as $feature): ?>
                                        <li><i class="fas fa-check"></i> <?php echo htmlspecialchars($feature); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                            
                            <?php if (!$active_subscription): ?>
                                <form method="POST" class="mt-auto">
                                    <input type="hidden" name="sub_id" value="<?php echo $plan['sub_id']; ?>">
                                    <button type="submit" name="subscribe" class="btn btn-subscribe">
                                        <i class="fas fa-rocket me-2"></i>Subscribe Now
                                    </button>
                                </form>
                            <?php else: ?>
                                <button class="btn btn-secondary mt-auto" disabled>
                                    <i class="fas fa-check me-2"></i>Current Plan
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Comparison Table -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
                    <div class="card-header text-center">
                        <h4 class="mb-0">Plan Comparison</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-dark table-hover">
                                <thead>
                                    <tr>
                                        <th>Feature</th>
                                        <?php foreach ($plans as $plan): ?>
                                            <th class="text-center"><?php echo htmlspecialchars($plan['name']); ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Price</td>
                                        <?php foreach ($plans as $plan): ?>
                                            <td class="text-center">₹<?php echo $plan['price']; ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                    <tr>
                                        <td>Duration</td>
                                        <?php foreach ($plans as $plan): ?>
                                            <td class="text-center">   <?php 
$days = $plan['duration_days'];

if ($days >= 365) {
    echo '12 months'; // or '1 year' if you prefer
} elseif ($days >= 30) {
    $months = $days / 30;
    echo ($months == 1) ? '1 month' : intval($months) . ' months';
} else {
    echo $days . ' days';
}
?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                    <tr>
                                        <td>Monthly Cost</td>
                                        <?php foreach ($plans as $plan): ?>
                                            <td class="text-center">₹<?php echo number_format($monthly_equivalent[$plan['sub_id']], 2); ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                    <tr>
                                        <td>Streaming Quality</td>
                                        <?php foreach ($plans as $plan): ?>
                                            <td class="text-center">
                                                <?php 
                                                if ($plan['sub_id'] == 3) echo '4K';
                                                elseif ($plan['sub_id'] == 2) echo 'HD';
                                                else echo 'HD';
                                                ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                    <tr>
                                        <td>Simultaneous Devices</td>
                                        <?php foreach ($plans as $plan): ?>
                                            <td class="text-center">
                                                <?php 
                                                if ($plan['sub_id'] == 3) echo '4';
                                                elseif ($plan['sub_id'] == 2) echo '2';
                                                else echo '1';
                                                ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-light">
                <i class="fas fa-arrow-left me-2"></i> Back to Home
            </a>
        </div>
    </div>
    
    <?php include "footer.php"; ?>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Add smooth scrolling to comparison table
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.subscription-card');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-10px) scale(1.02)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
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