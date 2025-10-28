<?php
// register_handler.php - Process registration form from modal

// Start session and include database connection
session_start();
require_once 'db.php';

// Set content type to JSON for AJAX responses
header('Content-Type: application/json');

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'errors' => []
];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mobile_no = trim($_POST['mobile_no'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Initialize errors array
    $errors = [];
    
    // Handle profile picture upload
    $profile_pic = null;
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_pic'];
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        // Validate file type
        if (!in_array($file['type'], $allowed_types)) {
            $errors['profile_pic'] = 'Please upload a valid image file (JPEG, PNG, or GIF)';
        }
        // Validate file size
        elseif ($file['size'] > $max_size) {
            $errors['profile_pic'] = 'File size must be less than 5MB';
        }
        else {
            // Create uploads directory if it doesn't exist
            $upload_dir = 'uploads/profile_pics/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Generate unique filename
            $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_' . time() . '.' . $file_extension;
            $filepath = $upload_dir . $filename;
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $profile_pic = $filepath;
            } else {
                $errors['profile_pic'] = 'Failed to upload image. Please try again.';
            }
        }
    }
    
    // Validate username
    if (empty($username)) {
        $errors['username'] = 'Username is required';
    } elseif (strlen($username) < 4) {
        $errors['username'] = 'Username must be at least 4 characters';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors['username'] = 'Username can only contain letters, numbers and underscores';
    } else {
        // Check if username exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
        if ($stmt) {
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $errors['username'] = 'Username is already taken';
            }
            $stmt->close();
        } else {
            $errors['username'] = 'Database error checking username';
        }
    }
    
    // Validate email
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address';
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        if ($stmt) {
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $errors['email'] = 'Email is already registered';
            }
            $stmt->close();
        } else {
            $errors['email'] = 'Database error checking email';
        }
    }
    
    // Validate mobile number
    if (!empty($mobile_no)) {
        // Remove any non-digit characters
        $mobile_no = preg_replace('/[^0-9]/', '', $mobile_no);
        
        // Validate mobile number format (basic validation)
        if (strlen($mobile_no) < 10) {
            $errors['mobile_no'] = 'Please enter a valid mobile number';
        } else {
            // Check if mobile number exists
            $stmt = $conn->prepare("SELECT user_id FROM users WHERE mobile_no = ?");
            if ($stmt) {
                $stmt->bind_param('s', $mobile_no);
                $stmt->execute();
                $stmt->store_result();
                if ($stmt->num_rows > 0) {
                    $errors['mobile_no'] = 'Mobile number is already registered';
                }
                $stmt->close();
            } else {
                $errors['mobile_no'] = 'Database error checking mobile number';
            }
        }
    }
    
    // Validate password
    if (empty($password)) {
        $errors['password'] = 'Password is required';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters';
    }
    
    // Validate confirm password
    if (empty($confirm_password)) {
        $errors['confirm_password'] = 'Please confirm your password';
    } elseif ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Passwords do not match';
    }
    
    // If no errors, register user
    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $created_at = date('Y-m-d H:i:s');
        
        $stmt = $conn->prepare("INSERT INTO users (username, email, mobile_no, password_hash, profile_pic, created_at) VALUES (?, ?, ?, ?, ?, ?)");
        
        if ($stmt) {
            $stmt->bind_param('ssssss', $username, $email, $mobile_no, $password_hash, $profile_pic, $created_at);
            
            if ($stmt->execute()) {
                // Get the new user ID
                $user_id = $stmt->insert_id;
                
                // Set session variables
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;
                $_SESSION['mobile_no'] = $mobile_no;
                $_SESSION['profile_pic'] = $profile_pic;
                
                // Create welcome notification and recommendations (optional)
                try {
                    if (file_exists('notification_manager.php')) {
                        require_once 'notification_manager.php';
                        $notificationManager = new NotificationManager($conn);
                        
                        // Create welcome notification
                        $notificationManager->createWelcomeNotification($user_id, $username);
                        
                        // Get trending content for recommendations
                        $trending_content = $notificationManager->getTrendingContent();
                        
                        if (!empty($trending_content)) {
                            // Pick the most trending item (highest rating/view count)
                            $trending_item = $trending_content[0];
                            
                            // Create trending recommendation notification
                            $notificationManager->createRecommendation(
                                $user_id, 
                                $trending_item['title'], 
                                $trending_item['content_id'], 
                                "trending right now"
                            );
                            
                            // Optional: Create 1-2 more recommendations from trending content
                            if (count($trending_content) > 1) {
                                $second_trending = $trending_content[1];
                                $notificationManager->createRecommendation(
                                    $user_id, 
                                    $second_trending['title'], 
                                    $second_trending['content_id'], 
                                    "popular among new users"
                                );
                            }
                        }
                    }
                } catch (Exception $e) {
                    // Silently fail notifications - don't break registration
                    error_log("Notification error: " . $e->getMessage());
                }
                
                // Set success response
                $response['success'] = true;
                $response['message'] = 'Registration successful! Welcome to Streamify.';
                
            } else {
                $response['message'] = 'Registration failed. Please try again.';
                $response['errors']['general'] = 'Database error: ' . $stmt->error;
            }
            
            $stmt->close();
        } else {
            $response['message'] = 'Registration failed. Please try again.';
            $response['errors']['general'] = 'Database preparation error: ' . $conn->error;
        }
    } else {
        // Set error response
        $response['message'] = 'Please fix the errors below.';
        $response['errors'] = $errors;
    }
} else {
    $response['message'] = 'Invalid request method.';
}

// Close database connection
$conn->close();

// Return JSON response
echo json_encode($response);
exit();