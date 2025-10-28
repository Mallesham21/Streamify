<style>
  .footer-glass {
    background: rgba(28, 15, 36, 0.95);
    backdrop-filter: blur(10px);
    border-top: 1px solid rgba(177, 59, 255, 0.2);
    position: relative;
    overflow: hidden;
  }
  
  .footer-glass::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 1px;
    background: linear-gradient(90deg, transparent, var(--streamify-primary), transparent);
  }

  .text-gradient {
    background: linear-gradient(45deg, #b13bff, #00ccff);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-weight: 800;
  }

  .footer-link {
    color: #ccc;
    text-decoration: none;
    display: inline-block;
    margin-bottom: 0.5rem;
    transition: color 0.3s ease, transform 0.2s;
    position: relative;
  }

  .footer-link:hover {
    color: #b13bff;
    transform: translateX(5px);
  }

  .footer-link::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 0;
    height: 1px;
    background: linear-gradient(90deg, #b13bff, #00ccff);
    transition: width 0.3s ease;
  }

  .footer-link:hover::after {
    width: 100%;
  }

  .footer-icon {
    color: #ccc;
    transition: transform 0.3s ease, color 0.3s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 50%;
    margin-right: 10px;
  }

  .footer-icon:hover {
    color: #b13bff;
    transform: translateY(-3px);
    background: rgba(177, 59, 255, 0.1);
    box-shadow: 0 5px 15px rgba(177, 59, 255, 0.2);
  }

  .footer-section-title {
    color: #fff;
    font-weight: 600;
    margin-bottom: 1.5rem;
    position: relative;
    padding-bottom: 0.5rem;
  }

  .footer-section-title::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 30px;
    height: 2px;
    background: linear-gradient(90deg, #b13bff, #00ccff);
    border-radius: 2px;
  }

  .footer-about {
    color: #a0a0a0;
    line-height: 1.6;
  }

  .footer-contact-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 1rem;
    color: #a0a0a0;
  }

  .footer-contact-icon {
    color: #b13bff;
    margin-right: 10px;
    font-size: 1.1rem;
    margin-top: 2px;
  }

  .footer-bottom {
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    padding-top: 1.5rem;
  }

  /* Animation for the footer */
  @keyframes float {
    0%, 100% {
      transform: translateY(0);
    }
    50% {
      transform: translateY(-5px);
    }
  }

  .footer-icon {
    animation: float 3s ease-in-out infinite;
  }

  .footer-icon:nth-child(2) {
    animation-delay: 0.2s;
  }

  .footer-icon:nth-child(3) {
    animation-delay: 0.4s;
  }

  .footer-icon:nth-child(4) {
    animation-delay: 0.6s;
  }

  /* Responsive adjustments */
  @media (max-width: 768px) {
    .footer-section-title::after {
      left: 50%;
      transform: translateX(-50%);
    }
    
    .footer-contact-item {
      justify-content: center;
      text-align: center;
    }
    
    .footer-contact-item div {
      text-align: left;
    }
  }

  @media (max-width: 576px) {
    .footer-section-title {
      text-align: center;
    }
    
    .footer-section-title::after {
      left: 50%;
      transform: translateX(-50%);
    }
    
    .footer-contact-item {
      flex-direction: column;
      align-items: center;
      text-align: center;
    }
    
    .footer-contact-icon {
      margin-right: 0;
      margin-bottom: 5px;
    }
  }
</style>

<footer class="footer-glass text-white pt-5 pb-3">
  <div class="container">
    <div class="row text-center text-md-start align-items-start g-4">
      
      <!-- Logo & About -->
      <div class="col-lg-4 col-md-6">
        <h3 class="fw-bold text-gradient mb-3">
          <a href="index.php" class="text-decoration-none">Streamify</a>
        </h3>
        <p class="footer-about">
          Dive into a universe of entertainment. Stream unlimited movies and series with zero ads and full vibes. 
          Experience premium content in stunning 4K quality anytime, anywhere.
        </p>
      </div>

      <!-- Explore Links -->
      <div class="col-lg-2 col-md-3 col-sm-6">
        <h5 class="footer-section-title">Explore</h5>
        <ul class="list-unstyled">
          <li><a href="index.php" class="footer-link">Home</a></li>
          <li><a href="movies.php" class="footer-link">Movies</a></li>
          <li><a href="tvshows.php" class="footer-link">TV Shows</a></li>
          <li><a href="watchlist.php" class="footer-link">My Watchlist</a></li>
          <li><a href="category.php" class="footer-link">Categories</a></li>
        </ul>
      </div>

      <!-- Features Column -->
      <div class="col-lg-2 col-md-3 col-sm-6">
        <h5 class="footer-section-title">Features</h5>
        <ul class="list-unstyled">
          <li><a href="#" class="footer-link">Ad-Free Streaming</a></li>
          <li><a href="#" class="footer-link">Offline Downloads</a></li>
          <li><a href="#" class="footer-link">Multiple Devices</a></li>
          <li><a href="#" class="footer-link">4K Ultra HD</a></li>
          <li><a href="#" class="footer-link">Personalized Profiles</a></li>
        </ul>
      </div>

      <!-- Contact & Social -->
      <div class="col-lg-4 col-md-12">
        <h5 class="footer-section-title">Connect With Us</h5>
        
        <div class="footer-contact-item">
          <i class="bi bi-envelope footer-contact-icon"></i>
          <div>
            <p class="mb-0 fw-semibold">Email</p>
            <p class="mb-0 small">support@streamify.com</p>
          </div>
        </div>
        
        <div class="footer-contact-item">
          <i class="bi bi-geo-alt footer-contact-icon"></i>
          <div>
            <p class="mb-0 fw-semibold">Location</p>
            <p class="mb-0 small">Pune, India</p>
          </div>
        </div>
        
        <div class="footer-contact-item">
          <i class="bi bi-telephone footer-contact-icon"></i>
          <div>
            <p class="mb-0 fw-semibold">Customer Care</p>
            <p class="mb-0 small">+91 98765 43210</p>
          </div>
        </div>

        <!-- Social Media Links -->
        <div class="mt-4">
          <h6 class="fw-semibold mb-3">Follow Us</h6>
          <div class="d-flex justify-content-center justify-content-md-start">
            <a href="#" class="footer-icon" title="Instagram">
              <i class="bi bi-instagram"></i>
            </a>
            <a href="#" class="footer-icon" title="Twitter">
              <i class="bi bi-twitter-x"></i>
            </a>
            <a href="#" class="footer-icon" title="Facebook">
              <i class="bi bi-facebook"></i>
            </a>
            <a href="#" class="footer-icon" title="YouTube">
              <i class="bi bi-youtube"></i>
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer Bottom -->
    <div class="footer-bottom mt-5">
      <div class="row">
        <div class="col-12 text-center">
          <p class="small text-secondary mb-0">
            &copy; 2025 <span class="text-gradient fw-bold">Streamify</span>. All rights reserved.
          </p>
        </div>
      </div>
    </div>
  </div>
</footer>