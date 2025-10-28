<?php
// Start session and include database connection
session_start();
require_once 'db.php';

// Check if user is logged in and has selected a subscription
if (!isset($_SESSION['user_id']) || !isset($_SESSION['selected_subscription'])) {
    header("Location: subscription.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$sub_id = $_SESSION['selected_subscription'];

// Get subscription details
$subscription_sql = "SELECT * FROM subscriptions WHERE sub_id = ?";
$stmt = $conn->prepare($subscription_sql);
$stmt->bind_param("i", $sub_id);
$stmt->execute();
$subscription = $stmt->get_result()->fetch_assoc();

// Get user details
$user_sql = "SELECT username, email, mobile_no FROM users WHERE user_id = ?";
$stmt = $conn->prepare($user_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$subscription || !$user) {
    header("Location: subscription.php");
    exit();
}

// Razorpay Test Configuration
$razorpay_key_id = 'rzp_test_ROW346qokK2AC9';
$razorpay_key_secret = 'JKHTH8P5xxWXM1kGsBkrl5IB';
$amount = $subscription['price'] * 100; // Convert to paise

// Generate order when page loads
$orderId = null;
$orderData = null;

// Prepare data for Razorpay order
$data = [
    'amount' => $amount,
    'currency' => 'INR',
    'receipt' => 'STREAMIFY_' . time() . '_' . $user_id,
    'payment_capture' => 1,
    'notes' => [
        'user_id' => $user_id,
        'subscription_id' => $sub_id,
        'plan_name' => $subscription['name']
    ]
];

// Create Razorpay order
$ch = curl_init('https://api.razorpay.com/v1/orders');
curl_setopt($ch, CURLOPT_USERPWD, $razorpay_key_id . ':' . $razorpay_key_secret);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
if (curl_errno($ch)) {
    $error = 'Payment gateway error: ' . curl_error($ch);
} else {
    $orderData = json_decode($response, true);
    if (isset($orderData['id'])) {
        $orderId = $orderData['id'];
    } else {
        $error = 'Failed to create payment order';
    }
}
curl_close($ch);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment | Streamify</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --streamify-primary: #b13bff;
            --streamify-secondary: #00ccff;
            --streamify-dark: #1c0f24;
            --streamify-success: #28a745;
        }
        
        body {
            background: linear-gradient(135deg, var(--streamify-dark) 0%, #2a1b3d 100%);
            min-height: 100vh;
            color: white;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .payment-container {
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .payment-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .btn-pay {
            background: linear-gradient(45deg, var(--streamify-primary), var(--streamify-secondary));
            border: none;
            color: white;
            padding: 1rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn-pay:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(177, 59, 255, 0.4);
        }
        
        .security-badge {
            background: rgba(40, 167, 69, 0.2);
            border: 1px solid rgba(40, 167, 69, 0.3);
            color: var(--streamify-success);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
        }
        
        .razorpay-logo {
            height: 30px;
            margin-left: 10px;
            vertical-align: middle;
        }
        
        .payment-methods {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin: 20px 0;
        }
        
        .payment-method-icon {
            font-size: 2rem;
            color: var(--streamify-primary);
        }
    </style>
</head>
<body>
    <?php include "header.php"; ?>
    
    <div class="container py-5">
        <div class="payment-container">
            <div class="text-center mb-5">
                <h1 class="display-5 fw-bold">Complete Your Payment</h1>
                <p class="lead">Secure payment powered by Razorpay</p>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="row">
                <!-- Order Summary -->
                <div class="col-lg-5 mb-4">
                    <div class="payment-card p-4 h-100">
                        <h3 class="mb-4"><i class="fas fa-receipt me-2"></i>Order Summary</h3>
                        
                        <div class="d-flex justify-content-between align-items-center mb-3 p-3 rounded" style="background: rgba(177, 59, 255, 0.1);">
                            <div>
                                <h5 class="mb-1"><?php echo htmlspecialchars($subscription['name']); ?></h5>
                                <small class="text-muted">
                                    <?php 
                                    $days = $subscription['duration_days'];
                                    if ($days >= 365) {
                                        echo '12 months subscription';
                                    } elseif ($days >= 30) {
                                        $months = $days / 30;
                                        echo ($months == 1 ? '1 month' : intval($months) . ' months') . ' subscription';
                                    } else {
                                        echo $days . ' days subscription';
                                    }
                                    ?>
                                </small>
                            </div>
                            <div class="text-end">
                                <strong class="h4">₹<?php echo $subscription['price']; ?></strong>
                            </div>
                        </div>
                        
                        <?php 
                        $features = json_decode($subscription['features'], true);
                        if (!empty($features)): 
                        ?>
                            <div class="mb-4">
                                <h6 class="mb-3">Plan Features:</h6>
                                <ul class="list-unstyled">
                                    <?php foreach ($features as $feature): ?>
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success me-2"></i>
                                            <?php echo htmlspecialchars($feature); ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <div class="border-top pt-3">
                            <div class="d-flex justify-content-between align-items-center fw-bold fs-5">
                                <span>Total Amount:</span>
                                <span class="text-primary">₹<?php echo number_format($subscription['price'] ); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Payment Section -->
                <div class="col-lg-7">
                    <div class="payment-card p-4">
                        <h3 class="mb-4"><i class="fas fa-credit-card me-2"></i>Payment Details</h3>
                        
                        <div class="text-center mb-4">
                            <div class="payment-methods">
                                <div class="text-center">
                                    <i class="fab fa-cc-visa payment-method-icon"></i>
                                    <div><small>Visa</small></div>
                                </div>
                                <div class="text-center">
                                    <i class="fab fa-cc-mastercard payment-method-icon"></i>
                                    <div><small>Mastercard</small></div>
                                </div>
                                <div class="text-center">
                                    <i class="fab fa-cc-amex payment-method-icon"></i>
                                    <div><small>Amex</small></div>
                                </div>
                                <div class="text-center">
                                    <i class="fas fa-mobile-alt payment-method-icon"></i>
                                    <div><small>UPI</small></div>
                                </div>
                                <div class="text-center">
                                    <i class="fas fa-wallet payment-method-icon"></i>
                                    <div><small>Wallet</small></div>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($orderId): ?>
                            <button id="rzp-button" class="btn btn-pay mb-4">
                                <i class="fas fa-lock me-2"></i>
                                Pay ₹<?php echo number_format($subscription['price'] ); ?>
                            </button>
                        <?php else: ?>
                            <div class="alert alert-warning text-center">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Unable to initialize payment. Please try again.
                            </div>
                        <?php endif; ?>
                        
                        <!-- Security Badge -->
                        <div class="text-center mb-3">
                            <div class="security-badge d-inline-flex align-items-center">
                                <i class="fas fa-lock me-2"></i>
                                <span>Secure SSL Encryption • Powered by </span>
                                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 96 32'%3E%3Cpath fill='%2334287A' d='M32 0h-4v32h4V0zm-8 0h-4v32h4V0zm-8 0H4v32h12V0zm48 0h-4v32h4V0zm-8 0h-4v32h4V0zm-8 0h-4v32h4V0zm16 0h-4v32h4V0zm8 0h-4v32h4V0zm8 0h-4v32h4V0z'/%3E%3C/svg%3E" 
                                     alt="Razorpay" class="razorpay-logo">
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <small class="text-muted">
                                By continuing, you agree to our <a href="#" class="text-primary">Terms of Service</a> 
                                and <a href="#" class="text-primary">Privacy Policy</a>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include "footer.php"; ?>
    
    <!-- Razorpay Checkout Script -->
 <?php if ($orderId): ?>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
    document.getElementById('rzp-button').onclick = function(e){
        e.preventDefault();
        
        var options = {
            "key": "<?php echo $razorpay_key_id; ?>",
            "amount": "<?php echo $amount; ?>",
            "currency": "INR",
            "name": "Streamify",
            "description": "Subscription: <?php echo htmlspecialchars($subscription['name']); ?>",
            "image": "https://yourdomain.com/streamify-logo.png",
            "order_id": "<?php echo $orderId; ?>",
            "handler": function (response){
                // Payment successful - redirect to success page
                window.location.href = "payment_success.php?payment_id=" + response.razorpay_payment_id + 
                                      "&order_id=" + response.razorpay_order_id + 
                                      "&signature=" + response.razorpay_signature +
                                      "&subscription_id=<?php echo $sub_id; ?>";
            },
            "prefill": {
                "name": "<?php echo htmlspecialchars($user['username']); ?>",
                "email": "<?php echo htmlspecialchars($user['email']); ?>",
                "contact": "<?php echo !empty($user['mobile_no']) ? htmlspecialchars($user['mobile_no']) : '9999999999'; ?>"
            },
            "notes": {
                "user_id": "<?php echo $user_id; ?>",
                "subscription_id": "<?php echo $sub_id; ?>"
            },
            "theme": {
                "color": "#b13bff"
            },
            "modal": {
                "ondismiss": function(){
                    console.log("Payment cancelled");
                }
            }
        };
        
        var rzp1 = new Razorpay(options);
        rzp1.open();
    }
</script>
<?php endif; ?>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Close database connection
$conn->close();
?>