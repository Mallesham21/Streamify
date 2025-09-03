<?php
require_once 'db.php';

// Get user data if logged in
$user = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT username, email, profile_pic, role, subscription_type FROM users WHERE user_id = ?");
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
}
?>
  <style>
    :root {
      --streamify-primary: #b13bff;
      --streamify-dark: #1c0f24;
      --streamify-light: #f8f9fa;
    }
/* Correct way to style placeholder text */
  .search-box input::placeholder {
    color: rgba(255, 255, 255, 0.3) !important;
  }

  .navbar {
      background-color: rgba(28, 15, 36, 0.95);
      backdrop-filter: blur(10px);
      transition: all 0.3s ease;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    
    .navbar.scrolled {
      padding-top: 0.5rem;
      padding-bottom: 0.5rem;
      background-color: rgba(28, 15, 36, 0.98);
    }
   /* Navbar */

.navbar-brand {
    font-weight: 800;
    font-size: 1.8rem;
    background: linear-gradient(45deg, #b13bff, #00ccff);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
    
   .nav-link {
  color: rgba(255, 255, 255, 0.8);
  padding: 0.5rem 1rem;
  position: relative;
  text-decoration: none;
  transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
  transform-style: preserve-3d;
  overflow: hidden;
}

.nav-link::before {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 2px;
  background: var(--streamify-primary);
  transform: scaleX(0);
  transform-origin: right;
  transition: transform 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
  z-index: -1;
}

.nav-link::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, transparent 100%);
  transform: translateY(-100%);
  transition: transform 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
  z-index: -1;
}

.nav-link:hover {
  color: var(--streamify-primary);
  transform: translateY(-2px);
  text-shadow: 0 5px 15px rgba(255, 255, 255, 0.3);
}

.nav-link:hover::before {
  transform: scaleX(1);
  transform-origin: left;
}

.nav-link:hover::after {
  transform: translateY(0);
}

/* Bonus: Active state */
.nav-link.active {
  color: var(--streamify-primary);
}

.nav-link.active::before {
  transform: scaleX(1);
}
    .search-box {
      position: relative;
      max-width: 400px;
    }
    
    .search-box input {
      background-color: rgba(255, 255, 255, 0.1);
      border: none;
      color: white;
      padding-left: 2.5rem;
      border-radius: 20px;
      transition: all 0.3s ease;
    }
    
    .search-box input:focus {
      background-color: rgba(255, 255, 255, 0.15);
      box-shadow: 0 0 0 0.25rem rgba(177, 59, 255, 0.25);
    }
    
    .search-box i {
      position: absolute;
      left: 1rem;
      top: 50%;
      transform: translateY(-50%);
      color: rgba(255, 255, 255, 0.7);
    }
    
    .user-avatar {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      object-fit: cover;
      transition: transform 0.3s ease;
      border: 2px solid rgba(255, 255, 255, 0.2);
    }
    
    .user-avatar:hover {
      transform: scale(1.1);
    }
    
    .dropdown-menu {
      background-color: #2a1b3d;
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 10px;
      margin-top: 0.5rem;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
      z-index: 1050;
    }
    
    .dropdown-item {
      color: rgba(255, 255, 255, 0.8);
      padding: 0.5rem 1rem;
    }
    
    .dropdown-item:hover {
      background-color: var(--streamify-primary);
      color: white;
    }
    
    .dropdown-divider {
      border-color: rgba(255, 255, 255, 0.1);
    }
    
    .notification-bell {
      position: relative;
      transition: transform 0.3s ease;
    }
    
    .notification-bell:hover {
      transform: rotate(15deg);
    }
    
    .notification-badge {
      position: absolute;
      top: -5px;
      right: -5px;
      font-size: 0.6rem;
      background-color: #ff4757;
    }
    input placeholder{
      color: white;
    }
    .btn-login {
      background-color: var(--streamify-primary);
      border: none;
      border-radius: 20px;
      padding: 0.5rem 1.5rem;
      transition: all 0.3s ease;
    }
    
    .btn-login:hover {
      background-color: #9d00ff;
      transform: translateY(-2px);
    }
    
    /* Skeleton loading for avatar */
    .avatar-skeleton {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      background: linear-gradient(90deg, #2a1b3d 25%, #3d2a4d 50%, #2a1b3d 75%);
      background-size: 200% 100%;
      animation: shimmer 1.5s infinite;
    }
    
    @keyframes shimmer {
      0% { background-position: 200% 0; }
      100% { background-position: -200% 0; }
    }
    
    @media (max-width: 992px) {
      .search-box {
        margin-top: 1rem;
        max-width: 100%;
      }
    }
  </style>

  <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container">
      <a class="navbar-brand" href="index.php">
        <i class="bi bi-play-circle-fill me-2"></i>Streamify
      </a>
      
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
        <span class="navbar-toggler-icon"></span>
      </button>
      
      <div class="collapse navbar-collapse" id="navbarContent">
        <ul class="navbar-nav me-auto">
          <li class="nav-item">
            <a class="nav-link" href="browse.php">Browse</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="movies.php">Movies</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="tvshows.php">TV Shows</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="categories.php">Categories</a>
          </li>
        </ul>
        
        <div class="search-box me-3">
          <i class="bi bi-search"></i>
          <input style="color:white; " class="form-control" type="search" placeholder="Search movies, shows..." aria-label="Search">
        </div>
        
        <div class="d-flex align-items-center">
        <?php if ($user): ?>
            <!-- Logged in state -->
            <div class="dropdown">
              <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <?php if ($user['profile_pic'] && file_exists($user['profile_pic'])): ?>
                  <img src="<?php echo htmlspecialchars($user['profile_pic']); ?>" class="user-avatar me-2" alt="Profile">
                <?php else: ?>
                  <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23b13bff'%3E%3Cpath d='M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z'/%3E%3C/svg%3E" class="user-avatar me-2" alt="Default Profile">
                <?php endif; ?>
                <span class="d-none d-lg-inline text-white"><?php echo htmlspecialchars($user['username']); ?></span>
              </a>
              
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                <li><a class="dropdown-item" href="watchlist.php"><i class="bi bi-bookmark me-2"></i>My Watchlist</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
              </ul>
            </div>
          <?php else: ?>
            <!-- Logged out state -->
            <a href="login.php" class="btn btn-login">Login</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </nav>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Navbar scroll effect
    window.addEventListener('scroll', function() {
      const navbar = document.querySelector('.navbar');
      if (window.scrollY > 50) {
        navbar.classList.add('scrolled');
      } else {
        navbar.classList.remove('scrolled');
      }
    });
    
    // Auto-focus search on larger screens
    if (window.innerWidth > 992) {
      const searchInput = document.querySelector('.search-box input');
      if (searchInput) {
        searchInput.focus();
      }
    }
    
    // Ensure dropdown functionality works
    document.addEventListener('DOMContentLoaded', function() {
      // Initialize all dropdowns
      var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
      var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
        return new bootstrap.Dropdown(dropdownToggleEl);
      });
      
      // Debug: Check if dropdown elements exist
      const userDropdown = document.getElementById('userDropdown');
      if (userDropdown) {
        console.log('User dropdown found:', userDropdown);
        
        // Test dropdown functionality
        userDropdown.addEventListener('click', function(e) {
          console.log('Dropdown clicked');
          e.preventDefault();
          
          // Manually toggle dropdown if Bootstrap doesn't work
          const dropdownMenu = this.nextElementSibling;
          if (dropdownMenu && dropdownMenu.classList.contains('dropdown-menu')) {
            dropdownMenu.classList.toggle('show');
          }
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
          if (!userDropdown.contains(e.target)) {
            const dropdownMenu = userDropdown.nextElementSibling;
            if (dropdownMenu && dropdownMenu.classList.contains('dropdown-menu')) {
              dropdownMenu.classList.remove('show');
            }
          }
        });
      } else {
        console.log('User dropdown not found');
      }
    });
    
    document.querySelectorAll('.nav-link').forEach(link => {
  link.addEventListener('mousemove', (e) => {
    const x = e.offsetX;
    const y = e.offsetY;
    const { width, height } = link.getBoundingClientRect();
    const moveX = ((x - width / 2) / width) * 10;
    const moveY = ((y - height / 2) / height) * 5;
    
    link.style.transform = `translateY(-2px) perspective(500px) rotateX(${moveY}deg) rotateY(${-moveX}deg)`;
  });
  
  link.addEventListener('mouseleave', () => {
    link.style.transform = 'translateY(0) perspective(500px) rotateX(0) rotateY(0)';
  });
});
  </script>
