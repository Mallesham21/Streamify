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
$stmt = $conn->prepare("SELECT username, email, profile_pic, subscription_type FROM users WHERE user_id = ?");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

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
            max-width: 600px;
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
        }
        
        .profile-card:hover {
            transform: translateY(-5px);
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
        
        .profile-pic-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(45deg, var(--streamify-primary), var(--streamify-secondary));
            color: white;
            font-size: 3.5rem;
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
        
        .btn-streamify {
            background-color: var(--streamify-primary);
            border: none;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border-radius: 8px;
        }
        
        .btn-streamify:hover {
            background-color: #c40812;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(229, 9, 20, 0.3);
        }
        
        .btn-outline-streamify {
            border-color: var(--streamify-primary);
            color: var(--streamify-primary);
        }
        
        .btn-outline-streamify:hover {
            background-color: var(--streamify-primary);
            color: white;
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
        }
        
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
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
        }
    </style>
</head>
<body>
    <?php include "header.php"; ?>
    
    <div class="profile-container">
        <a href="javascript:void(0)" onclick="history.back()" class="back-btn m-3">
    <i class="bi bi-arrow-left"></i>
</a>

        
        <div class="profile-card text-center">
          
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
                    <img src="<?= htmlspecialchars($user['profile_pic']) ?>" alt="Profile Picture" class="profile-pic">
                <?php else: ?>
                    <div class="profile-pic profile-pic-placeholder">
                        <i class="bi bi-person-fill"></i>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- User Info -->
            <div class="user-info">
                <div class="user-info-item">
                    <div class="user-info-label">Username</div>
                    <div class="user-info-value"><?= htmlspecialchars($user['username']) ?></div>
                </div>
                
                <div class="user-info-item">
                    <div class="user-info-label">Email</div>
                    <div class="user-info-value"><?= htmlspecialchars($user['email']) ?></div>
                </div>
                
                <div class="user-info-item">
                    <div class="user-info-label">Subscription</div>
                    <div class="d-flex justify-content-center align-items-center gap-2">
                        <div class="user-info-value">
                            <?= htmlspecialchars($subscription_types[$user['subscription_type']] ?? 'Basic') ?>
                        </div>
                        <span class="subscription-badge">
                            <i class="bi bi-star-fill"></i>
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="action-buttons mt-4">
                <a href="edit_profile.php" class="btn btn-streamify">
                    <i class="bi bi-pencil-square me-2"></i> Edit Profile
                </a>
                <a href="subscription.php" class="btn upgrade-btn">
                    <i class="bi bi-gem me-2"></i> Upgrade Subscription
                </a>
                <a href="logout.php" class="btn btn-outline-streamify">
                    <i class="bi bi-box-arrow-right me-2"></i> Log Out
                </a>
            </div>
        </div>
    </div>
    
    <?php include "footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>