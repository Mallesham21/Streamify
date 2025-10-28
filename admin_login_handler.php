<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_username = trim($_POST['admin_username']);
    $admin_password = $_POST['admin_password'];
    
    // Validate inputs
    if (empty($admin_username) || empty($admin_password)) {
        echo json_encode(['success' => false, 'message' => 'Please enter both username and password.']);
        exit;
    }
    
    // Check if user exists and has admin role
    $stmt = $conn->prepare("SELECT user_id, username, email, password_hash, profile_pic, role FROM users WHERE username = ? AND role = 'admin'");
    $stmt->bind_param('s', $admin_username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($admin_password, $admin['password_hash'])) {
            // Password is correct, set admin session
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user_id'] = $admin['user_id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['admin_profile_pic'] = $admin['profile_pic'];
            $_SESSION['admin_role'] = $admin['role'];
            
            echo json_encode(['success' => true, 'message' => 'Admin login successful!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid admin credentials.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid admin credentials or insufficient permissions.']);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

$conn->close();
?>