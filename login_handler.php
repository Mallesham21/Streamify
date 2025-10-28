<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_email = trim($_POST['username_email']);
    $password = $_POST['password'];
    
    // Validate inputs
    if (empty($username_email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Please enter both username/email and password.']);
        exit;
    }
    
    // Prepare SQL statement
    $stmt = $conn->prepare("SELECT user_id, username, email, password_hash, profile_pic, is_premium FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param('ss', $username_email, $username_email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password_hash'])) {
            // Password is correct, start session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['profile_pic'] = $user['profile_pic'];
            $_SESSION['is_premium'] = $user['is_premium'];

            echo json_encode(['success' => true, 'message' => 'Login successful!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid username/email or password.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid username/email or password.']);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

$conn->close();
?>