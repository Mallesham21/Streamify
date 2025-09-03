<?php
require_once 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Initialize variables
$errors = [];
$success = false;

// Fetch user data
$user = null;
$stmt = $conn->prepare("SELECT username, email, profile_pic, subscription_type FROM users WHERE user_id = ?");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize inputs
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    // Check if this is a password change request
    $is_password_change = isset($_POST['change_password']);
    
    // Validate username
    if (empty($username)) {
        $errors['username'] = 'Username is required';
    } elseif (strlen($username) < 3) {
        $errors['username'] = 'Username must be at least 3 characters';
    }
    
    // Validate email
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    }
    
    // Handle profile picture upload
    $profile_pic = $user['profile_pic'];
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['profile_pic']['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            $errors['profile_pic'] = 'Only JPG, PNG, and GIF images are allowed';
        } else {
            $upload_dir = 'uploads/profile_pics/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
            $filename = 'user_' . $_SESSION['user_id'] . '_' . time() . '.' . $file_ext;
            $destination = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $destination)) {
                // Delete old profile pic if it exists and isn't a default
                if (!empty($profile_pic) && strpos($profile_pic, 'default') === false) {
                    @unlink($profile_pic);
                }
                $profile_pic = $destination;
            } else {
                $errors['profile_pic'] = 'Failed to upload image';
            }
        }
    }
    
    // Update database if no errors (for non-password changes)
    if (empty($errors) && !$is_password_change) {
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, profile_pic = ? WHERE user_id = ?");
        $stmt->bind_param('sssi', $username, $email, $profile_pic, $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            $success = true;
            // Update user data in session
            $_SESSION['username'] = $username;
            // Refresh user data
            $user['username'] = $username;
            $user['email'] = $email;
            $user['profile_pic'] = $profile_pic;
        } else {
            $errors['general'] = 'Failed to update profile. Please try again.';
        }
        $stmt->close();
    }
}

// Handle password change separately if it's a password change request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_password_change) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Verify current password
    $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $db_user = $result->fetch_assoc();
    $stmt->close();
    
    if (!password_verify($current_password, $db_user['password'])) {
        $errors['current_password'] = 'Current password is incorrect';
    }
    
    // Validate new password
    if (empty($new_password)) {
        $errors['new_password'] = 'New password is required';
    } elseif (strlen($new_password) < 8) {
        $errors['new_password'] = 'Password must be at least 8 characters';
    }
    
    if ($new_password !== $confirm_password) {
        $errors['confirm_password'] = 'Passwords do not match';
    }
    
    if (empty($errors)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->bind_param('si', $hashed_password, $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            $_SESSION['password_changed'] = true;
            header('Location: profile.php?success=1');
exit();
        } else {
            $errors['general'] = 'Failed to update password. Please try again.';
        }
        $stmt->close();
    }
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
    <title>Edit Profile | Streamify</title>
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
        
        .edit-profile-container {
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            position: relative;
        }
        
        .edit-profile-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: transform 0.3s ease;
        }
        
        .edit-profile-card:hover {
            transform: translateY(-5px);
        }
        
        .profile-pic-container {
            width: 150px;
            height: 150px;
            margin: 0 auto 1.5rem;
            position: relative;
            cursor: pointer;
        }
        
        .profile-pic {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid transparent;
            background: linear-gradient(45deg, var(--streamify-primary), var(--streamify-secondary)) border-box;
            box-shadow: 0 4px 15px rgba(142, 68, 173, 0.3);
            transition: all 0.3s ease;
        }
        
        .profile-pic:hover {
            opacity: 0.8;
        }
        
        .profile-pic-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(45deg, var(--streamify-primary), var(--streamify-secondary));
            color: white;
            font-size: 3.5rem;
        }
        
        .profile-pic-edit {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: var(--streamify-primary);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        
        .form-label {
            color: var(--streamify-text-light);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.25rem;
        }
        
        .form-control {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--streamify-text);
            padding: 0.75rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            background-color: rgba(255, 255, 255, 0.15);
            border-color: var(--streamify-primary);
            color: var(--streamify-text);
            box-shadow: 0 0 0 0.25rem rgba(229, 9, 20, 0.25);
        }
        
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }
        
        .invalid-feedback {
            color: #ff6b6b;
            font-size: 0.85rem;
        }
        
        .password-input-group {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #b13bff;
            z-index: 5;
            background: transparent;
            border: none;
            outline: none;
            font-size: 1.1rem;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .password-toggle:hover {
            color: var(--streamify-primary);
        }
        
        .password-toggle i {
            transition: all 0.2s ease;
        }
        
        .subscription-info {
            background: rgba(142, 68, 173, 0.2);
            border-left: 4px solid var(--streamify-secondary);
            padding: 1rem;
            border-radius: 0 8px 8px 0;
            margin-bottom: 1.5rem;
        }
        
        .btn-streamify {
            background-color: var(--streamify-primary);
            border: none;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border-radius: 8px;
            display: block;
            margin: 2rem auto 0;
            width: fit-content;
        }
        
        .btn-streamify:hover {
            background-color: #c40812;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(229, 9, 20, 0.3);
        }
        
        .btn-change-password {
            background-color: transparent;
            border: 1px solid var(--streamify-secondary);
            color: var(--streamify-secondary);
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            display: block;
            margin: 1rem auto;
        }
        
        .btn-change-password:hover {
            background-color: rgba(142, 68, 173, 0.2);
            transform: translateY(-2px);
        }
        
        .alert-success {
            background-color: rgba(40, 167, 69, 0.2);
            border-color: rgba(40, 167, 69, 0.3);
            color: #28a745;
        }
        
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.2);
            border-color: rgba(220, 53, 69, 0.3);
            color: #dc3545;
        }
        
        /* Modal styles */
        .modal-content {
            background-color: var(--streamify-bg);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .modal-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .modal-footer {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        @media (max-width: 576px) {
            .edit-profile-container {
                padding: 1rem;
            }
            
            .edit-profile-card {
                padding: 1.5rem;
            }
            
            .profile-pic-container {
                width: 120px;
                height: 120px;
            }
        }
    </style>
</head>
<body>
    <?php include "header.php"; ?>
    
    <div class="edit-profile-container">
        <a href="profile.php" class="back-btn m-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        
        <div class="edit-profile-card">
            <h2 class="text-center mb-4">Edit Profile</h2>
            
            <?php if ($success && !isset($_POST['change_password'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    Profile updated successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif (isset($errors['general']) && !isset($_POST['change_password'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= htmlspecialchars($errors['general']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" novalidate>
                <!-- Profile Picture Upload -->
                <div class="text-center mb-4">
                    <label for="profile_pic" class="profile-pic-container">
                        <?php if (!empty($user['profile_pic'])): ?>
                            <img src="<?= htmlspecialchars($user['profile_pic']) ?>" alt="Profile Picture" class="profile-pic">
                        <?php else: ?>
                            <div class="profile-pic profile-pic-placeholder">
                                <i class="bi bi-person-fill"></i>
                            </div>
                        <?php endif; ?>
                        <div class="profile-pic-edit">
                            <i class="bi bi-camera-fill"></i>
                        </div>
                    </label>
                    <input type="file" id="profile_pic" name="profile_pic" accept="image/*" class="d-none">
                    <?php if (isset($errors['profile_pic'])): ?>
                        <div class="invalid-feedback d-block"><?= htmlspecialchars($errors['profile_pic']) ?></div>
                    <?php endif; ?>
                                    <div class="text-center">
                                    <small class="text-secondary">Click to change profile picture (JPG, PNG, GIF)</small>
                                    </div>

                </div>

                
                <!-- Username -->
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>" 
                           id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                    <?php if (isset($errors['username'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['username']) ?></div>
                    <?php endif; ?>
                </div>
                
                <!-- Email -->
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                           id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                    <?php if (isset($errors['email'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div>
                    <?php endif; ?>
                </div>
                
                <!-- Subscription Info (readonly) -->
                <div class="subscription-info mb-4">
                    <div>
                        <div class="form-label">Subscription Plan</div>
                        <div class="fw-bold"><?= htmlspecialchars($subscription_types[$user['subscription_type']] ?? 'Basic') ?></div>
                    </div>
                </div>
                
                <!-- Change Password Button -->
                <button type="button" class="btn btn-change-password" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                    <i class="bi bi-key me-2"></i> Change Password
                </button>
                
                <!-- Save Button -->
                <button type="submit" class="btn btn-streamify">
                    <i class="bi bi-save-fill me-2"></i> Save Changes
                </button>
            </form>
        </div>
    </div>
    
    <!-- Change Password Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="changePasswordModalLabel">Change Password</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <?php if (isset($_SESSION['password_changed']) && $_SESSION['password_changed']): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                Password changed successfully!
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            <?php unset($_SESSION['password_changed']); ?>
                        <?php elseif (isset($errors['general']) && isset($_POST['change_password'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <?= htmlspecialchars($errors['general']) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Current Password -->
                        <div class="mb-3 password-input-group">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control <?= isset($errors['current_password']) ? 'is-invalid' : '' ?>" 
                                   id="current_password" name="current_password" required>
                            <button type="button" class="password-toggle" id="toggleCurrentPassword">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                            <?php if (isset($errors['current_password'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['current_password']) ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- New Password -->
                        <div class="mb-3 password-input-group">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control <?= isset($errors['new_password']) ? 'is-invalid' : '' ?>" 
                                   id="new_password" name="new_password" required>
                            <button type="button" class="password-toggle" id="toggleNewPassword">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                            <?php if (isset($errors['new_password'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['new_password']) ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Confirm Password -->
                        <div class="mb-3 password-input-group">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" 
                                   id="confirm_password" name="confirm_password" required>
                            <button type="button" class="password-toggle" id="toggleConfirmPassword">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                            <?php if (isset($errors['confirm_password'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['confirm_password']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-streamify" name="change_password">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <?php include "footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Profile picture click handler
        document.querySelector('.profile-pic-container').addEventListener('click', function() {
            document.getElementById('profile_pic').click();
        });
        
        // Preview profile picture when selected
        document.getElementById('profile_pic').addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                const profilePicContainer = document.querySelector('.profile-pic-container');
                
                reader.onload = function(event) {
                    // Remove placeholder if it exists
                    const placeholder = profilePicContainer.querySelector('.profile-pic-placeholder');
                    if (placeholder) {
                        profilePicContainer.removeChild(placeholder);
                    }
                    
                    // Create or update image element
                    let img = profilePicContainer.querySelector('img');
                    if (!img) {
                        img = document.createElement('img');
                        img.className = 'profile-pic';
                        profilePicContainer.insertBefore(img, profilePicContainer.querySelector('.profile-pic-edit'));
                    }
                    
                    img.src = event.target.result;
                };
                
                reader.readAsDataURL(e.target.files[0]);
            }
        });
        
        // Password toggle functionality
        function setupPasswordToggle(toggleId, inputId) {
            const toggle = document.querySelector(toggleId);
            const input = document.querySelector(inputId);
            const icon = toggle.querySelector('i');
            
            toggle.addEventListener('click', function() {
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                icon.classList.toggle('bi-eye');
                icon.classList.toggle('bi-eye-slash');
            });
        }
        
        // Set up all password toggles
        setupPasswordToggle('#toggleCurrentPassword', '#current_password');
        setupPasswordToggle('#toggleNewPassword', '#new_password');
        setupPasswordToggle('#toggleConfirmPassword', '#confirm_password');
        
        // Initialize modal if there are password errors
        <?php if (isset($_POST['change_password']) && !empty($errors)): ?>
            document.addEventListener('DOMContentLoaded', function() {
                var modal = new bootstrap.Modal(document.getElementById('changePasswordModal'));
                modal.show();
            });
        <?php endif; ?>
    </script>
</body>
</html>