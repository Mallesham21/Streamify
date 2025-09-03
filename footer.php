<style>
  .footer-glass {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(6px);
    border-top: 1px solid rgba(255, 255, 255, 0.1);
  }

  .text-gradient {
    background: linear-gradient(45deg, #b13bff, #00ccff);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  }

  .footer-link {
    color: #ccc;
    text-decoration: none;
    display: inline-block;
    margin-bottom: 0.5rem;
    transition: color 0.3s ease, transform 0.2s;
  }

  .footer-link:hover {
    color: #b13bff;
    transform: translateX(5px);
  }

  .footer-icon {
    color: #ccc;
    transition: transform 0.3s ease, color 0.3s ease;
  }

  .footer-icon:hover {
    color: #b13bff;
    transform: scale(1.2);
  }
</style>

<footer class="footer-glass text-white pt-5 pb-3 mt-5">
  <div class="container">
    <div class="row text-center text-md-start align-items-start g-4">
      
      <!-- Logo & About -->
      <div class="col-md-3">
        <h3 class="fw-bold text-gradient"><a href="index.php" class="text-decoration-none">Streamify</a></h3>
        <p class="small text-light-emphasis">
          Dive into a universe of entertainment. Stream unlimited movies and series with zero ads and full vibes.
        </p>
      </div>

      <!-- Explore Links -->
      <div class="col-md-3">
        <h5 class="fw-semibold text-light">Explore</h5>
        <ul class="list-unstyled mt-3">
          <li><a href="#" class="footer-link">Home</a></li>
          <li><a href="#" class="footer-link">Movies</a></li>
          <li><a href="#" class="footer-link">Series</a></li>
          <li><a href="#" class="footer-link">My Watchlist</a></li>
        </ul>
      </div>

      <!-- Features Column -->
      <div class="col-md-3">
        <h5 class="fw-semibold text-light">Features</h5>
        <ul class="list-unstyled mt-3">
          <li><a href="#" class="footer-link">Ad-Free Streaming</a></li>
          <li><a href="#" class="footer-link">Offline Downloads</a></li>
          <li><a href="#" class="footer-link">Multiple Devices</a></li>
          <li><a href="#" class="footer-link">4K Ultra HD</a></li>
        </ul>
      </div>

      <!-- Contact & Social -->
      <div class="col-md-3">
        <h5 class="fw-semibold text-light">Connect</h5>
        <p class="small"><i class="bi bi-envelope me-2"></i> support@streamify.com</p>
        <p class="small"><i class="bi bi-geo-alt me-2"></i> Pune, India</p>
        <div class="d-flex justify-content-center justify-content-md-start gap-3 fs-5 mt-2">
          <a href="#" class="footer-icon"><i class="bi bi-instagram"></i></a>
          <a href="#" class="footer-icon"><i class="bi bi-twitter-x"></i></a>
          <a href="#" class="footer-icon"><i class="bi bi-facebook"></i></a>
          <a href="#" class="footer-icon"><i class="bi bi-youtube"></i></a>
        </div>
      </div>
    </div>

    <hr class="border-secondary mt-4" />
    <p class="text-center small text-secondary mb-0">
      &copy; 2025 <span class="text-gradient">Streamify</span>. All rights reserved.
    </p>
  </div>
</footer>