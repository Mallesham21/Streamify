<?php
require_once 'db.php';

// Initialize variables
$username = $email = $password = $confirm_password = '';
$errors = [];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
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
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors['username'] = 'Username is already taken';
        }
        $stmt->close();
    }
    
    // Validate email
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address';
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors['email'] = 'Email is already registered';
        }
        $stmt->close();
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
        
        $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, profile_pic, created_at) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('sssss', $username, $email, $password_hash, $profile_pic, $created_at);
        
        if ($stmt->execute()) {
            // Get the new user ID
            $user_id = $stmt->insert_id;
            
            // Set session variables
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            $_SESSION['profile_pic'] = $profile_pic;
            
            // Redirect to dashboard
            header('Location: index.php');
            exit();
        } else {
            $errors['general'] = 'Registration failed. Please try again.';
        }
        
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Streamify - Register</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<style>
    body {
      background-color: #1c0f24;
      font-family: 'Plus Jakarta Sans', sans-serif;
      color: white;
      overflow: hidden;
      animation: fadeIn 1s ease;
    }
    .card {
      background: rgba(255, 255, 255, 0.05);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 20px;
      transform: translateY(30px);
      opacity: 0;
      animation: slideUp 0.8s ease forwards;
    }
    .btn-primary {
      background-color: #b13bff;
      border: none;
      transition: background-color 0.3s ease, transform 0.3s ease;
    }
    .btn-primary:hover {
      background-color: #9d00ff;
      transform: scale(1.05);
    }
    .register {
      color: white;
    }
      input.form-control {
      background-color: #22102c;
      color: white !important; /* Force white text */
      border: 1px solid transparent;
      transition: border-color 0.3s ease;
      padding-right: 40px; /* Add space for toggle button */
    }
    input.form-control:focus {
      border-color: #b13bff;
      box-shadow: none;
      color: black !important;
    }
    input::placeholder {
      color: #aaa !important;
    }
    a {
      color: #b13bff;
      text-decoration: none;
      transition: color 0.3s ease;
    }
    a:hover {
      color: #9d00ff;
      text-decoration: underline;
    }
    .error-message {
      color: #ff6b6b;
      font-size: 0.9rem;
      margin-top: 5px;
    }
    .password-container {
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
    }
    .profile-pic-container {
      text-align: center;
      margin-bottom: 2rem;
    }
    .profile-pic-preview {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      border: 3px solid #b13bff;
      object-fit: cover;
      margin: 0 auto 1rem;
      display: block;
      background-color: #22102c;
      transition: all 0.3s ease;
    }
    .profile-pic-preview:hover {
      border-color: #9d00ff;
      transform: scale(1.05);
    }
    .profile-pic-input {
      display: none;
    }
    .profile-pic-label {
      background-color: #b13bff;
      color: white;
      padding: 0.5rem 1rem;
      border-radius: 25px;
      cursor: pointer;
      transition: all 0.3s ease;
      display: inline-block;
      font-size: 0.9rem;
    }
    .profile-pic-label:hover {
      background-color: #9d00ff;
      transform: scale(1.05);
    }
    .profile-pic-text {
      font-size: 0.8rem;
      color: rgba(255, 255, 255, 0.7);
      margin-top: 0.5rem;
    }
    @keyframes slideUp {
      to {
        transform: translateY(0);
        opacity: 1;
      }
    }
    @keyframes fadeIn {
      from {opacity: 0;}
      to {opacity: 1;}
    }
  </style></head>

<body>
  <div class="d-flex justify-content-center align-items-center vh-100">
    <div class="register card p-5" style="width: 100%; max-width: 420px;">
      <h2 class="text-center mb-4">Create Your Account</h2>
      
      <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <?php echo htmlspecialchars($errors['general']); ?>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>
      
      <form method="POST" action="register.php" enctype="multipart/form-data">
        <!-- Profile Picture Section -->
        <div class="profile-pic-container">
          <img id="profilePreview" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23b13bff'%3E%3Cpath d='M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z'/%3E%3C/svg%3E" 
               alt="Profile Picture" class="profile-pic-preview" />
          <label for="profile_pic" class="profile-pic-label">
            <i class="bi bi-camera"></i> Choose Photo
          </label>
          <input type="file" id="profile_pic" name="profile_pic" class="profile-pic-input" accept="image/*" />
          <div class="profile-pic-text">Click to upload profile picture (optional)</div>
          <?php if (!empty($errors['profile_pic'])): ?>
            <div class="error-message"><?php echo htmlspecialchars($errors['profile_pic']); ?></div>
          <?php endif; ?>
        </div>
        
        <div class="mb-3">
          <label for="username" class="form-label">Username</label>
          <input 
            type="text" 
            class="form-control <?php echo !empty($errors['username']) ? 'is-invalid' : ''; ?>" 
            id="username" 
            name="username"
            value="<?php echo htmlspecialchars($username); ?>" 
            placeholder="Enter username" 
            required 
          />
          <?php if (!empty($errors['username'])): ?>
            <div class="error-message"><?php echo htmlspecialchars($errors['username']); ?></div>
          <?php endif; ?>
        </div>
        
        <div class="mb-3">
          <label for="email" class="form-label">Email Address</label>
          <input 
            type="email" 
            class="form-control <?php echo !empty($errors['email']) ? 'is-invalid' : ''; ?>" 
            id="email" 
            name="email"
            value="<?php echo htmlspecialchars($email); ?>" 
            placeholder="Enter email" 
            required 
          />
          <?php if (!empty($errors['email'])): ?>
            <div class="error-message"><?php echo htmlspecialchars($errors['email']); ?></div>
          <?php endif; ?>
        </div>
        
        <div class="mb-3 password-container">
          <label for="password" class="form-label">Password</label>
          <div class="position-relative">
            <input 
              type="password" 
              class="form-control <?php echo !empty($errors['password']) ? 'is-invalid' : ''; ?>" 
              id="password" 
              name="password"
              placeholder="Enter password" 
              required 
            />
            <button type="button" class="password-toggle" id="togglePassword">
              <i class="bi bi-eye-slash"></i>
            </button>
          </div>
          <?php if (!empty($errors['password'])): ?>
            <div class="error-message"><?php echo htmlspecialchars($errors['password']); ?></div>
          <?php endif; ?>
        </div>
        
        <div class="mb-4 password-container">
          <label for="confirmPassword" class="form-label">Confirm Password</label>
          <div class="position-relative">
            <input 
              type="password" 
              class="form-control <?php echo !empty($errors['confirm_password']) ? 'is-invalid' : ''; ?>" 
              id="confirmPassword" 
              name="confirm_password"
              placeholder="Confirm password" 
              required 
            />
            <button type="button" class="password-toggle" id="toggleConfirmPassword">
              <i class="bi bi-eye-slash"></i>
            </button>
          </div>
          <?php if (!empty($errors['confirm_password'])): ?>
            <div class="error-message"><?php echo htmlspecialchars($errors['confirm_password']); ?></div>
          <?php endif; ?>
        </div>
        
        <div class="d-grid">
          <button type="submit" class="btn btn-primary">Register</button>
        </div>
      </form>
      
      <p class="mt-3 text-center">
        Already have an account? <a href="login.php">Login here</a>
      </p>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Profile picture preview
    document.getElementById('profile_pic').addEventListener('change', function(e) {
      const file = e.target.files[0];
      const preview = document.getElementById('profilePreview');
      
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          preview.src = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    });
    
    // Password visibility toggle
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
    
    // Set up both password toggles
    setupPasswordToggle('#togglePassword', '#password');
    setupPasswordToggle('#toggleConfirmPassword', '#confirmPassword');
    
    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
      const password = document.getElementById('password').value;
      const confirmPassword = document.getElementById('confirmPassword').value;
      
      if (password !== confirmPassword) {
        e.preventDefault();
        alert('Passwords do not match');
      }
    });
  </script>
</body>
</html>