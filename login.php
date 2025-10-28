<?php

// Database configuration
require_once 'db.php';

// Initialize variables
$username_email = $password = '';
$error = '';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $username_email = trim($_POST['username_email']);
    $password = $_POST['password'];
    
    // Validate inputs
    if (empty($username_email) || empty($password)) {
        $error = 'Please enter both username/email and password.';
    } else {
        // Prepare SQL statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT user_id, username, email, password_hash FROM users WHERE username = ? OR email = ?");
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
                
                // Redirect to dashboard
                header('Location: index.php');
                exit();
            } else {
                $error = 'Invalid username/email or password.';
            }
        } else {
            $error = 'Invalid username/email or password.';
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
  <title>Streamify - Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <style>
    body {
      background-color: #1c0f24;
      font-family: 'Plus Jakarta Sans', sans-serif;
      color: white;
      overflow: hidden;
      animation: fadeIn 1s ease;
      outline-color: red;
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
    .login{
      color:white;
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
    .password-container {
      position: relative;
    }
    .error-message {
      color: #ff6b6b;
      font-size: 0.9rem;
      margin-top: 5px;
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
  </style>
</head>

<body>
  <div class="d-flex justify-content-center align-items-center vh-100">
    <div class="login card p-5" style="width: 100%; max-width: 400px;">
      <h2 class="text-center mb-4">Login to Streamify</h2>
      
      <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <?php echo htmlspecialchars($error); ?>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>
      
      <form method="POST" action="login.php">
        <div class="mb-3">
          <label for="usernameEmail" class="form-label">Username or Email</label>
          <input 
            type="text" 
            class="form-control" 
            id="usernameEmail" 
            name="username_email"
            value="<?php echo htmlspecialchars($username_email); ?>" 
            placeholder="Enter username or email" 
            required 
          />
        </div>
        
        <div class="mb-4 password-container">
          <label for="password" class="form-label">Password</label>
          <div class="position-relative">
            <input 
              type="password" 
              class="form-control" 
              id="password" 
              name="password"
              placeholder="Enter password" 
              required 
            />
            <button type="button" class="password-toggle" id="togglePassword">
              <i class="bi bi-eye-slash"></i>
            </button>
          </div>
        </div>
        

        <div class="d-grid">
          <button type="submit" class="btn btn-primary">Login</button>
        </div>
      </form>
      
      <p class="mt-3 text-center">
        Don't have an account? <a href="register.php">Register</a>
      </p>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Password visibility toggle
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');
    const icon = togglePassword.querySelector('i');
    
    togglePassword.addEventListener('click', function() {
      // Toggle the type attribute
      const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
      password.setAttribute('type', type);
      
      // Toggle the icon
      icon.classList.toggle('bi-eye');
      icon.classList.toggle('bi-eye-slash');
    });
    
    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
      const usernameEmail = document.getElementById('usernameEmail').value.trim();
      const password = document.getElementById('password').value;
      
      if (!usernameEmail || !password) {
        e.preventDefault();
        alert('Please fill in all fields.');
      }
    });
  </script>
</body>
</html>