<?php
require_once 'db.php';
$showProfileSuccess = isset($_GET['success']) && $_GET['success'] == '1';
// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch user data
$user = null;
$stmt = $conn->prepare("SELECT username, email, mobile_no, profile_pic, subscription_type, is_premium FROM users WHERE user_id = ?");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Check if user is premium
$is_premium = $user['is_premium'] ?? false;

// Get subscription details if premium
$subscription_details = null;
if ($is_premium) {
    $stmt = $conn->prepare("SELECT us.end_date, s.name, s.features FROM user_subscriptions us 
                           JOIN subscriptions s ON us.sub_id = s.sub_id 
                           WHERE us.user_id = ? AND us.status = 'active' 
                           ORDER BY us.end_date DESC LIMIT 1");
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $subscription_details = $result->fetch_assoc();
    $stmt->close();
}

// Subscription type display mapping
$subscription_types = [
    'free' => 'Basic',
    'premium' => 'Premium'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | Streamify</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
    <style>
        :root {
            --streamify-bg: #1C0F24;
            --streamify-primary: #E50914;
            --streamify-secondary: #8E44AD;
            --streamify-gold: #ffd700;
            --streamify-premium: linear-gradient(135deg, #b13bff, #ffd700, #00ccff);
            --streamify-text: #F8F9FA;
            --streamify-text-light: #ADB5BD;
        }
        
        body {
            background-color: var(--streamify-bg);
            color: var(--streamify-text);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
.back-btn {
    position: absolute;
    top: 20px;
    left: 20px;
    color: var(--streamify-text);
    font-size: 1.5rem;
    z-index: 10;
    transition: all 0.3s ease;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
}

.back-btn:hover {
    color: var(--streamify-primary);
    transform: translateX(-3px);
    background: rgba(229, 9, 20, 0.2);
    box-shadow: 0 2px 10px rgba(229, 9, 20, 0.2);
}

.back-btn i {
    transition: transform 0.3s ease;
}

.back-btn:hover i {
    transform: scale(1.1);
}
        
        .profile-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            position: relative;
        }
        
        .profile-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: transform 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .profile-card:hover {
            transform: translateY(-5px);
        }
        
        .profile-card.premium::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--streamify-premium);
            animation: shimmer 3s infinite;
        }
        
        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        
        .profile-pic-container {
            width: 150px;
            height: 150px;
            margin: 0 auto 1.5rem;
            position: relative;
        }
        
        .profile-pic {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid transparent;
            background: linear-gradient(45deg, var(--streamify-primary), var(--streamify-secondary)) border-box;
            box-shadow: 0 4px 15px rgba(142, 68, 173, 0.3);
        }
        
        .profile-pic.premium {
            background: var(--streamify-premium) border-box;
            box-shadow: 0 4px 20px rgba(255, 215, 0, 0.4);
        }
        
        .profile-pic-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(45deg, var(--streamify-primary), var(--streamify-secondary));
            color: white;
            font-size: 3.5rem;
        }
        
        .profile-pic-placeholder.premium {
            background: var(--streamify-premium);
        }
        
        .user-info-item {
            margin-bottom: 1.25rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .user-info-label {
            color: var(--streamify-text-light);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.25rem;
        }
        
        .user-info-value {
            font-size: 1.1rem;
            font-weight: 500;
        }
        
        .subscription-badge {
            background: linear-gradient(45deg, var(--streamify-primary), var(--streamify-secondary));
            color: white;
            padding: 0.35rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .subscription-badge.premium {
            background: var(--streamify-premium);
            color: #1c0f24;
            animation: glow 2s infinite;
        }
        
        @keyframes glow {
            0%, 100% { box-shadow: 0 0 10px rgba(255, 215, 0, 0.5); }
            50% { box-shadow: 0 0 20px rgba(255, 215, 0, 0.8); }
        }
        
        .premium-benefits {
            background: linear-gradient(135deg, rgba(177, 59, 255, 0.1), rgba(255, 215, 0, 0.1));
            border-radius: 10px;
            padding: 1.5rem;
            margin: 1.5rem 0;
            border: 1px solid rgba(255, 215, 0, 0.2);
        }
        
        .benefit-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.75rem;
            color: var(--streamify-text);
        }
        
        .benefit-item i {
            color: var(--streamify-gold);
            margin-right: 0.75rem;
            font-size: 1.1rem;
        }
        
        .subscription-info {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 1rem;
            margin-top: 1rem;
        }
        


        .btn-streamify {
            background-color: var(--streamify-primary);
            border: none;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border-radius: 8px;
            color: white;
        }
        
        .btn-streamify:hover {
            background-color: #c40812;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(229, 9, 20, 0.3);
            color: white;
        }
        
        .btn-outline-streamify {
            border: 2px solid var(--streamify-primary);
            background: transparent;
            color: var(--streamify-primary);
            padding: 0.5rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border-radius: 8px;
        }
        
        .btn-outline-streamify:hover {
            background-color: var(--streamify-primary);
            color: white;
            transform: translateY(-2px);
        }
        
        .upgrade-btn {
            background: linear-gradient(45deg, #8E44AD, #3498db);
            border: none;
            color: white;
            font-weight: 600;
            padding: 0.5rem 1.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .upgrade-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(142, 68, 173, 0.4);
            color: white;
        }
        
        .premium-btn {
            background: var(--streamify-premium);
            border: none;
            color: #1c0f24;
            font-weight: 700;
            padding: 0.5rem 1.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 215, 0, 0.3);
        }
        
        .premium-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 215, 0, 0.4);
            color: #1c0f24;
        }
        
        .logout-btn {
            background: transparent;
            border: 2px solid #6c757d;
            color: #6c757d;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border-radius: 8px;
        }
        
        .logout-btn:hover {
            background: #6c757d;
            color: white;
            transform: translateY(-2px);
            border-color: #6c757d;
        }
        
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 2rem;
        }
        
        @media (max-width: 576px) {
            .profile-container {
                padding: 1rem;
            }
            
            .profile-card {
                padding: 1.5rem;
            }
            
            .profile-pic-container {
                width: 120px;
                height: 120px;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .subscription-type-container {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php include "header.php"; ?>
    
    <div class="profile-container">
        <a href="javascript:void(0)" onclick="history.back()" class="back-btn m-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        
        <div class="profile-card text-center <?= $is_premium ? 'premium' : '' ?>">
          
            <?php if ($showProfileSuccess): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    Profile updated successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <!-- Profile Picture -->
            <div class="profile-pic-container">
                <?php if (!empty($user['profile_pic'])): ?>
                    <img src="<?= htmlspecialchars($user['profile_pic']) ?>" alt="Profile Picture" class="profile-pic <?= $is_premium ? 'premium' : '' ?>">
                <?php else: ?>
                    <div class="profile-pic profile-pic-placeholder <?= $is_premium ? 'premium' : '' ?>">
                        <i class="bi bi-person-fill"></i>
                    </div>
                <?php endif; ?>
                <?php if ($is_premium): ?>
                    <div class="position-absolute top-0 start-100 translate-middle">
                        <span class="badge bg-warning text-dark">
                            <i class="bi bi-star-fill"></i> PREMIUM
                        </span>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- User Info -->
            <h2 class="mb-3"><?= htmlspecialchars($user['username']) ?></h2>
            
            <div class="subscription-status mb-4">
                <span class="subscription-badge <?= $is_premium ? 'premium' : '' ?>">
                    <i class="bi <?= $is_premium ? 'bi-gem' : 'bi-person' ?> me-1"></i>
                    <?= $subscription_types[$user['subscription_type'] ?? 'free'] ?> Member
                </span>
            </div>
            
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="user-info-item">
                        <div class="user-info-label">Email Address</div>
                        <div class="user-info-value"><?= htmlspecialchars($user['email']) ?></div>
                    </div>
                    
                    <div class="user-info-item">
                        <div class="user-info-label">Mobile Number</div>
                        <div class="user-info-value"><?= !empty($user['mobile_no']) ? htmlspecialchars($user['mobile_no']) : 'Not provided' ?></div>
                    </div>
                    
                    <!-- Fixed Subscription Type Alignment -->
                    <div class="user-info-item">
                        <div class="user-info-label">Subscription Type</div>
                        <div class="subscription-type-container">
                            <div class="subscription-type-value">
                                <span class="user-info-value"><?= $subscription_types[$user['subscription_type'] ?? 'free'] ?></span>
                                <?php if ($is_premium): ?>
                                    <span class="subscription-badge premium">
                                        <i class="bi bi-star-fill me-1"></i> Active
                                    </span>
                                <?php endif; ?>
                            </div>
                            <?php if (!$is_premium): ?>
                                <a href="subscription.php" class="btn upgrade-btn btn-sm">
                                    <i class="bi bi-arrow-up-circle me-1"></i> Upgrade
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($is_premium && $subscription_details): ?>
                        <div class="user-info-item">
                            <div class="user-info-label">Premium Expires</div>
                            <div class="user-info-value">
                                <?= date('F j, Y', strtotime($subscription_details['end_date'])) ?>
                            </div>
                        </div>
                        
                        <div class="premium-benefits">
                            <h5 class="mb-3"><i class="bi bi-stars me-2"></i>Your Premium Benefits</h5>
                            <div class="benefit-item">
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Ad-free streaming experience</span>
                            </div>
                            <div class="benefit-item">
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Access to premium content library</span>
                            </div>
                            <div class="benefit-item">
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Download content for offline viewing</span>
                            </div>
                            <div class="benefit-item">
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Early access to new releases</span>
                            </div>
                            <div class="benefit-item">
                                <i class="bi bi-check-circle-fill"></i>
                                <span>4K Ultra HD streaming quality</span>
                            </div>
                        </div>
                    <?php elseif (!$is_premium): ?>
                        <div class="premium-benefits">
                            <h5 class="mb-3"><i class="bi bi-gem me-2"></i>Upgrade to Premium</h5>
                            <p class="mb-3">Unlock exclusive features and enhance your streaming experience!</p>
                            <div class="benefit-item">
                                <i class="bi bi-star-fill"></i>
                                <span>Ad-free streaming</span>
                            </div>
                            <div class="benefit-item">
                                <i class="bi bi-star-fill"></i>
                                <span>Premium content library</span>
                            </div>
                            <div class="benefit-item">
                                <i class="bi bi-star-fill"></i>
                                <span>Download & watch offline</span>
                            </div>
                            <div class="benefit-item">
                                <i class="bi bi-star-fill"></i>
                                <span>Early access to new releases</span>
                            </div>
                            <div class="benefit-item">
                                <i class="bi bi-star-fill"></i>
                                <span>4K Ultra HD quality</span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Action Buttons - All Important Buttons Restored -->
            <div class="action-buttons">
                <a href="edit_profile.php" class="btn btn-streamify">
                    <i class="bi bi-pencil-square me-2"></i> Edit Profile
                </a>
                
            
                <a href="watchlist.php" class="btn btn-outline-streamify">
                    <i class="bi bi-bookmark me-2"></i> My Watchlist
                </a>
                
                <a href="logout.php" class="btn logout-btn">
                    <i class="bi bi-box-arrow-right me-2"></i> Log Out
                </a>
            </div>
        </div>
    </div>
    
    <?php include "footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add some interactive animations
        document.addEventListener('DOMContentLoaded', function() {
            const profileCard = document.querySelector('.profile-card');
            
            // Add hover effect
            profileCard.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
            });
            
            profileCard.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
            
            // Auto-dismiss alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
    </script>
</body>
</html>