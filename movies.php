<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Streamify - Movies</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      background-color: #1c0f24;
      font-family: 'Plus Jakarta Sans', sans-serif;
      color: white;
      animation: fadeIn 1s ease;
    }
    .navbar {
      border-bottom: 1px solid #3a204b;
    }
    .scroll-container {
      overflow-x: auto;
      white-space: nowrap;
      padding-bottom: 8px;
    }
    .scroll-item {
      display: inline-block;
      margin-right: 12px;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .scroll-item img {
      border-radius: 12px;
      width: 170px;
      height: 240px;
      object-fit: cover;
    }
    .scroll-item:hover {
      transform: scale(1.08);
      box-shadow: 0 8px 16px rgba(177, 59, 255, 0.4);
      cursor: pointer;
    }
    .section-title {
      margin-top: 40px;
      margin-bottom: 15px;
    }
    .btn-primary {
      background-color: #b13bff;
      border: none;
      transition: background-color 0.3s ease;
    }
    .btn-primary:hover {
      background-color: #9d00ff;
    }
    .scroll-container::-webkit-scrollbar {
      height: 6px;
    }
    .scroll-container::-webkit-scrollbar-thumb {
      background-color: #b13bff;
      border-radius: 10px;
    }
    @keyframes fadeIn {
      from {opacity: 0;}
      to {opacity: 1;}
    }
  </style>
</head>

<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg">
    <div class="container">
      <a class="navbar-brand text-white fw-bold" href="index.html">Streamify</a>
      <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
        <ul class="navbar-nav me-4 gap-3">
          <li class="nav-item"><a class="nav-link text-white" href="index.html">Home</a></li>
          <li class="nav-item"><a class="nav-link text-white" href="series.html">Series</a></li>
          <li class="nav-item"><a class="nav-link text-white" href="movies.html">Movies</a></li>
          <li class="nav-item"><a class="nav-link text-white" href="plans.html">Plans</a></li>
        </ul>
        <a href="login.html" class="btn btn-primary">Login</a>
      </div>
    </div>
  </nav>

  <!-- Movies Content -->
  <div class="container">
    <!-- Trending Movies -->
    <h3 class="section-title fw-bold">Trending Movies</h3>
    <div class="scroll-container">
      <div class="scroll-item"><img src="https://i.ibb.co/hDvhJK4/trend1.jpg" alt="Trending Movie" /></div>
      <div class="scroll-item"><img src="https://i.ibb.co/6Dy4RQf/trend2.jpg" /></div>
      <div class="scroll-item"><img src="https://i.ibb.co/LP4xN1F/trend3.jpg" /></div>
      <div class="scroll-item"><img src="https://i.ibb.co/KwsHXH4/trend4.jpg" /></div>
      <div class="scroll-item"><img src="https://i.ibb.co/zfy3Z2v/trend5.jpg" /></div>
    </div>

    <!-- New Releases -->
    <h3 class="section-title fw-bold">New Releases</h3>
    <div class="scroll-container">
      <div class="scroll-item"><img src="https://i.ibb.co/txSmpm6/new1.jpg" /></div>
      <div class="scroll-item"><img src="https://i.ibb.co/dWmCcTS/new2.jpg" /></div>
      <div class="scroll-item"><img src="https://i.ibb.co/4g2Rhxv/new3.jpg" /></div>
      <div class="scroll-item"><img src="https://i.ibb.co/QKc5MkS/new4.jpg" /></div>
      <div class="scroll-item"><img src="https://i.ibb.co/YjNtrmW/new5.jpg" /></div>
    </div>

    <!-- Action Movies -->
    <h3 class="section-title fw-bold">Action</h3>
    <div class="scroll-container">
      <div class="scroll-item"><img src="https://i.ibb.co/Yc0jBnv/pop1.jpg" /></div>
      <div class="scroll-item"><img src="https://i.ibb.co/xLQx9rR/pop2.jpg" /></div>
      <div class="scroll-item"><img src="https://i.ibb.co/hcQ2nb1/pop3.jpg" /></div>
      <div class="scroll-item"><img src="https://i.ibb.co/bKdrfbH/pop4.jpg" /></div>
      <div class="scroll-item"><img src="https://i.ibb.co/hFJcYc2/pop5.jpg" /></div>
    </div>

    <!-- Comedy Movies -->
    <h3 class="section-title fw-bold">Comedy</h3>
    <div class="scroll-container">
      <div class="scroll-item"><img src="https://i.ibb.co/pPS3m38/movie1.jpg" /></div>
      <div class="scroll-item"><img src="https://i.ibb.co/hZjJkg6/movie2.jpg" /></div>
      <div class="scroll-item"><img src="https://i.ibb.co/JkcN6cS/movie3.jpg" /></div>
      <div class="scroll-item"><img src="https://i.ibb.co/4NvTW9K/movie4.jpg" /></div>
      <div class="scroll-item"><img src="https://i.ibb.co/5rcyQTC/movie5.jpg" /></div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="border-top border-secondary py-4 mt-5">
    <div class="container text-center">
      <div class="mb-3">
        <a href="#" class="text-white me-3">About</a>
        <a href="#" class="text-white me-3">Privacy</a>
        <a href="#" class="text-white me-3">Terms</a>
        <a href="#" class="text-white">Help</a>
      </div>
      <div class="mb-3">
        <a href="#"><img src="https://i.ibb.co/QYgNgXq/instagram.png" width="30" /></a>
        <a href="#" class="ms-3"><img src="https://i.ibb.co/pKdGn5q/twitter.png" width="30" /></a>
        <a href="#" class="ms-3"><img src="https://i.ibb.co/9Z2nsd4/youtube.png" width="30" /></a>
      </div>
      <div>&copy; 2025 Streamify. All rights reserved.</div>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>