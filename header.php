<?php
require_once 'db.php';

// Get user data if logged in
$user = null;
$is_premium = false;
$premium_badge = '';
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT username, email, mobile_no, profile_pic, role, subscription_type, is_premium FROM users WHERE user_id = ?");
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    $is_premium = $user['is_premium'] ?? false;
}
?>
<?php include 'modals.php'; ?>
  <style>
    :root {
      --streamify-primary: #b13bff;
      --streamify-gold: #ffd700;
      --streamify-premium: linear-gradient(135deg, #b13bff, #ffd700, #00ccff);
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
      border-bottom: 1px solid rgba(177, 59, 255, 0.1);
    }
    
    .navbar.scrolled {
      padding-top: 0.5rem;
      padding-bottom: 0.5rem;
      background-color: rgba(28, 15, 36, 0.98);
      border-bottom-color: rgba(177, 59, 255, 0.2);
    }
   /* Navbar */

.navbar-brand {
    font-weight: 800;
    font-size: 1.8rem;
    background: linear-gradient(45deg, #b13bff, #00ccff);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    position: relative;
}

.navbar-brand::after {
    content: '';
    position: absolute;
    bottom: -5px;
    left: 0;
    width: 100%;
    height: 2px;
    background: var(--streamify-premium);
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.navbar-brand:hover::after {
    transform: scaleX(1);
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

/* Premium Navigation Items */
.nav-link.premium-feature {
    background: linear-gradient(45deg, transparent 50%, rgba(255, 215, 0, 0.1) 50%);
    background-size: 200% 200%;
    background-position: 100% 100%;
    transition: all 0.3s ease;
    border-radius: 8px;
    margin: 0 0.25rem;
}

.nav-link.premium-feature:hover {
    background-position: 0% 0%;
    color: #ffd700;
    box-shadow: 0 4px 15px rgba(255, 215, 0, 0.3);
}

.nav-link.premium-feature::before {
    background: var(--streamify-gold);
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
      position: relative;
    }
    
    .user-avatar.premium {
        border: 2px solid transparent;
        background: linear-gradient(45deg, #b13bff, #ffd700, #00ccff) border-box;
        box-shadow: 0 0 10px rgba(255, 215, 0, 0.3);
    }
    
    .user-avatar:hover {
      transform: scale(1.1);
    }
    

    @keyframes glow {
        0%, 100% { box-shadow: 0 0 5px rgba(255, 215, 0, 0.5); }
        50% { box-shadow: 0 0 15px rgba(255, 215, 0, 0.8); }
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
      transition: all 0.3s ease;
    }
    
    .dropdown-item:hover {
      background-color: var(--streamify-primary);
      color: white;
      transform: translateX(5px);
    }
    
    .dropdown-item.premium {
        background: linear-gradient(45deg, rgba(255, 215, 0, 0.1), rgba(177, 59, 255, 0.1));
        border-left: 3px solid #ffd700;
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
      top: 2px;
      right: 2px;
      z-index: 999;
      font-size: 0.7rem;
      background-color: #ff4757;
      border: 2px solid var(--streamify-dark);
      min-width: 18px;
      height: 18px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
    }
    
    .notification-container {
      position: relative;
      display: inline-block;
      overflow: visible;
    }
    
    input placeholder{
      color: white;
    }
    .btn-login {
      background-color: var(--streamify-primary);
      border: none;
      color:#f5f5f5;
      border-radius: 20px;
      padding: 0.5rem 1.5rem;
      transition: all 0.3s ease;
    }
    
    .btn-login:hover {
      background-color: #9d00ff;
      transform: translateY(-2px);
    }
    
    .btn-premium {
        background: linear-gradient(45deg, #b13bff, #ffd700);
        border: none;
        color: #1c0f24;
        font-weight: 600;
        border-radius: 20px;
        padding: 0.5rem 1.5rem;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(255, 215, 0, 0.3);
    }
    
    .btn-premium:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(255, 215, 0, 0.4);
        color: #1c0f24;
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
      
      .notification-container {
        margin-right: 1rem;
      }
      
    }
  </style>

  <nav class="navbar navbar-expand-lg navbar-dark fixed-top mb-5">
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
            <a class="nav-link" href="popular.php">Popular</a>
          </li>
    
        </ul>
        
        <div class="search-box me-3">
          <i class="bi bi-search"></i>
          <form method="GET" action="browse.php">
          <input style="color:white; " class="form-control" type="search" name="search" placeholder="Search movies, shows..." aria-label="Search">
        </form>
        </div>
        
        <div class="d-flex align-items-center">
        <?php if ($user): ?>
            <!-- Logged in state -->
             <!-- Notification Bell -->
        <div class="notification-container me-3">
            <a href="notifications.php" class="nav-link position-relative">
                <i class="bi bi-bell fs-5 notification-bell"></i>
                <?php 
                // Get unread notification count
                $unreadCount = 0;
                if (isset($_SESSION['user_id'])) {
                    require_once "notification_manager.php";
                    $notificationManager = new NotificationManager($conn);
                    $unreadCount = $notificationManager->getUnreadCount($_SESSION['user_id']);
                }
                ?>
                <?php if ($unreadCount > 0): ?>
                    <span class="notification-badge">
                        <?= $unreadCount > 9 ? '9+' : $unreadCount ?>
                    </span>
                <?php endif; ?>
            </a>
        </div>
            <div class="dropdown">
              <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <?php if ($user['profile_pic'] && file_exists($user['profile_pic'])): ?>
                  <img src="<?php echo htmlspecialchars($user['profile_pic']); ?>" class="user-avatar me-2 <?= $is_premium ? 'premium' : '' ?>" alt="Profile">
                <?php else: ?>
                  <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23b13bff'%3E%3Cpath d='M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z'/%3E%3C/svg%3E" class="user-avatar me-2 <?= $is_premium ? 'premium' : '' ?>" alt="Default Profile">
                <?php endif; ?>
                <span class="d-none d-lg-inline text-white">
                  <?php echo htmlspecialchars($user['username']); ?>
                  <?= $premium_badge ?>
                </span>
              </a>
              
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                <li><a class="dropdown-item" href="watchlist.php"><i class="bi bi-bookmark me-2"></i>My Watchlist</a></li>
                <?php if (!$is_premium): ?>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-warning" href="subscription.php">
                  <i class="bi bi-gem me-2"></i>Upgrade to Premium
                </a></li>
                <?php endif; ?>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
              </ul>
            </div>
            
          <?php else: ?>
            <!-- Logged out state -->
            <div class="d-flex gap-2">
              <button class="btn btn-login" data-bs-toggle="modal" data-bs-target="#loginModal">
                <i class="bi bi-person-circle"></i> Login
              </button>
              <a href="subscription.php" class="btn btn-premium">
                <i class="bi bi-star-fill me-1"></i> Go Premium
              </a>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </nav>

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
      // Check if Bootstrap is loaded
      if (typeof bootstrap === 'undefined') {
        console.warn('Bootstrap JavaScript not loaded. Implementing manual dropdown.');
        
        // Manual dropdown implementation
        const userDropdown = document.getElementById('userDropdown');
        if (userDropdown) {
          const dropdownMenu = userDropdown.nextElementSibling;
          
          userDropdown.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Close all other dropdowns first
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
              if (menu !== dropdownMenu) {
                menu.classList.remove('show');
              }
            });
            
            // Toggle current dropdown
            if (dropdownMenu && dropdownMenu.classList.contains('dropdown-menu')) {
              dropdownMenu.classList.toggle('show');
            }
          });
          
          // Close dropdown when clicking outside
          document.addEventListener('click', function(e) {
            if (!userDropdown.parentElement.contains(e.target)) {
              if (dropdownMenu && dropdownMenu.classList.contains('dropdown-menu')) {
                dropdownMenu.classList.remove('show');
              }
            }
          });
          
          // Close dropdown when pressing Escape
          document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
              if (dropdownMenu && dropdownMenu.classList.contains('dropdown-menu')) {
                dropdownMenu.classList.remove('show');
              }
            }
          });
        }
      } else {
        // Bootstrap is loaded, use Bootstrap dropdowns
        try {
          // Initialize all dropdowns
          var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
          var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
            return new bootstrap.Dropdown(dropdownToggleEl);
          });
          
          console.log('Bootstrap dropdowns initialized:', dropdownList.length);
        } catch (error) {
          console.error('Error initializing Bootstrap dropdowns:', error);
          
          // Fallback to manual implementation
          const userDropdown = document.getElementById('userDropdown');
          if (userDropdown) {
            const dropdownMenu = userDropdown.nextElementSibling;
            
            userDropdown.addEventListener('click', function(e) {
              e.preventDefault();
              e.stopPropagation();
              
              if (dropdownMenu && dropdownMenu.classList.contains('dropdown-menu')) {
                dropdownMenu.classList.toggle('show');
              }
            });
            
            document.addEventListener('click', function(e) {
              if (!userDropdown.parentElement.contains(e.target)) {
                if (dropdownMenu && dropdownMenu.classList.contains('dropdown-menu')) {
                  dropdownMenu.classList.remove('show');
                }
              }
            });
          }
        }
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
<script>
setInterval(() => {
    fetch("cron.php")
        .then(response => console.log("Notification check triggered"));
}, 600000); // 600000ms = 10 minutes
</script>