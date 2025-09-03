<!DOCTYPE html><html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Streamify Movie Page</title>
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    />
    <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"
    />
    <style>
      body {
        background-color: #1c0f24;
        font-family: 'Plus Jakarta Sans', sans-serif;
        color: white;
        margin: 0;
      }
      .badge-custom {
        background-color: #3a204b;
        color: #b48dce;
      }
      .scroll-horizontal {
        overflow-x: auto;
        white-space: nowrap;
      }
      .scroll-horizontal .card {
        display: inline-block;
        margin-right: 1rem;
        min-width: 200px;
      }
      .plyr__video-embed,
      .ratio {
        margin-top: 0;
      }
      .rating-stars i {
        color: #b13bff;
        margin-right: 2px;
      }
      .like-dislike {
        display: flex;
        gap: 1.5rem;
        align-items: center;
        margin-top: 1rem;
        flex-wrap: wrap;
      }
      .like-dislike button {
        background: none;
        border: none;
        color: white;
        font-size: 1.4rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        transition: transform 0.2s;
      }
      .like-dislike button:hover {
        transform: scale(1.1);
        color: #0d6efd;
      }
      .like-dislike span {
        font-weight: 500;
      }
  </style>
  </head>
  <body>
  <!--Header section -->
  <?php include "header.php"; ?>
    <div class="container py-4">
      <!-- Plyr Player -->
      <div class="ratio ratio-16x9 mb-4">
        <video
          id="player"
          playsinline
          controls
          data-poster="https://cdn.plyr.io/static/demo/View_From_A_Blue_Moon_Trailer-HD.jpg"
        ></video>
      </div><!-- Title and Categories Below Video -->
  <div class="mb-4 text-center">
    <h2 class="fw-bold">The Quantum Realm</h2>
    <p class="text-muted">TV-MA · 2023 · 1 Season · Sci-Fi</p>
    <span class="badge rounded-pill badge-custom">Cyberpunk</span>
    <span class="badge rounded-pill badge-custom">Thriller</span>
    <span class="badge rounded-pill badge-custom">Dystopian</span>
  </div>

  <!-- Like/Dislike/Watchlist -->
  <div class="like-dislike">
    <button id="likeBtn">
      <i class="bi bi-hand-thumbs-up"></i>
      <span id="likeCount">124</span>
    </button>
    <button id="dislikeBtn">
      <i class="bi bi-hand-thumbs-down"></i>
      <span id="dislikeCount">6</span>
    </button>
    <button id="watchlistBtn">
      <i class="bi bi-bookmark-plus"></i>
      <span id="watchlistText">Watchlist</span>
    </button>
  </div>

  <!-- Info and Episode Responsive Row -->
  <div class="row mt-4">
    <div class="col-md-8">
      <h4>Info</h4>
      <p>
        In a world where the lines between reality and simulation blur, a group of elite hackers known as the 'Quantum Collective' navigates the digital frontier. Led by the enigmatic 'Zero', they uncover a conspiracy that threatens to unravel the fabric of both worlds.
      </p>
      <ul class="list-unstyled mb-4">
        <li><strong>Cast:</strong> Jane Holloway, Marcus Voss, Elara Stone</li>
        <li><strong>Director:</strong> Nolan Creed</li>
        <li><strong>Language:</strong> English</li>
      </ul>
    </div>

    <!-- Collapsible for Mobile View -->
    <div class="col-md-4">
      <div class="d-md-none mb-3">
        <button class="btn btn-outline-light w-100" type="button" data-bs-toggle="collapse" data-bs-target="#episodeCollapse">
          Show Episodes
        </button>
      </div>
      <div class="collapse d-md-block" id="episodeCollapse">
        <h4 class="mb-3">Episodes</h4>
        <div class="scroll-horizontal">
          <div class="card bg-dark text-white">
            <img src="https://via.placeholder.com/200x120" class="card-img-top" alt="Episode 1">
            <div class="card-body">
              <h5 class="card-title">Episode 1</h5>
              <p class="card-text">The Glitch · 45m</p>
            </div>
          </div>
          <div class="card bg-dark text-white">
            <img src="https://via.placeholder.com/200x120" class="card-img-top" alt="Episode 2">
            <div class="card-body">
              <h5 class="card-title">Episode 2</h5>
              <p class="card-text">Echoes · 42m</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- More Sliders -->
  <h4 class="mb-3 mt-5">You May Also Like</h4>
  <div class="scroll-horizontal mb-5">
    <div class="card bg-dark text-white">
      <img src="https://via.placeholder.com/200x120" class="card-img-top" alt="Movie 1">
      <div class="card-body">
        <h5 class="card-title">Digital Reign</h5>
      </div>
    </div>
    <div class="card bg-dark text-white">
      <img src="https://via.placeholder.com/200x120" class="card-img-top" alt="Movie 2">
      <div class="card-body">
        <h5 class="card-title">Code Breakers</h5>
      </div>
    </div>
  </div>

  <h4 class="mb-3">Popular on Streamify</h4>
  <div class="scroll-horizontal mb-5">
    <div class="card bg-dark text-white">
      <img src="https://via.placeholder.com/200x120" class="card-img-top" alt="Movie 3">
      <div class="card-body">
        <h5 class="card-title">Byte Wars</h5>
      </div>
    </div>
    <div class="card bg-dark text-white">
      <img src="https://via.placeholder.com/200x120" class="card-img-top" alt="Movie 4">
      <div class="card-body">
        <h5 class="card-title">Hack the System</h5>
      </div>
    </div>
  </div>

  <h4 class="mb-3">Trending Now</h4>
  <div class="scroll-horizontal">
    <div class="card bg-dark text-white">
      <img src="https://via.placeholder.com/200x120" class="card-img-top" alt="Movie 5">
      <div class="card-body">
        <h5 class="card-title">Virtual City</h5>
      </div>
    </div>
    <div class="card bg-dark text-white">
      <img src="https://via.placeholder.com/200x120" class="card-img-top" alt="Movie 6">
      <div class="card-body">
        <h5 class="card-title">Encrypted</h5>
      </div>
    </div>
  </div>
</div>
  <!--Footer section -->
<?php include "footer.php"; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.plyr.io/3.7.8/plyr.polyfilled.js"></script>
<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
<script>
  const video = document.getElementById('player');
  const source = 'https://streamable.com/y5l76l';

  if (Hls.isSupported()) {
    const hls = new Hls();
    hls.loadSource(source);
    hls.attachMedia(video);
    hls.on(Hls.Events.MANIFEST_PARSED, function () {
      const player = new Plyr(video, {
        controls: [
          'play', 'progress', 'current-time', 'mute', 'volume', 'settings', 'fullscreen'
        ],
        settings: ['quality', 'speed'],
      });
    });
  } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
    video.src = source;
    const player = new Plyr(video);
  }

  let likeCount = 124;
  let dislikeCount = 6;
  let liked = false;
  let disliked = false;
  let watchlisted = false;

  const likeBtn = document.getElementById('likeBtn');
  const dislikeBtn = document.getElementById('dislikeBtn');
  const watchlistBtn = document.getElementById('watchlistBtn');
  const likeCountSpan = document.getElementById('likeCount');
  const dislikeCountSpan = document.getElementById('dislikeCount');
  const watchlistText = document.getElementById('watchlistText');

  likeBtn.addEventListener('click', () => {
    if (!liked) {
      likeCount++;
      if (disliked) {
        dislikeCount--;
        disliked = false;
      }
      liked = true;
    } else {
      likeCount--;
      liked = false;
    }
    updateCounts();
  });

  dislikeBtn.addEventListener('click', () => {
    if (!disliked) {
      dislikeCount++;
      if (liked) {
        likeCount--;
        liked = false;
      }
      disliked = true;
    } else {
      dislikeCount--;
      disliked = false;
    }
    updateCounts();
  });

  watchlistBtn.addEventListener('click', () => {
    watchlisted = !watchlisted;
    watchlistText.innerText = watchlisted ? 'Added' : 'Watchlist';
    watchlistBtn.classList.toggle('text-success', watchlisted);
  });

  function updateCounts() {
    likeCountSpan.innerText = likeCount;
    dislikeCountSpan.innerText = dislikeCount;
  }
</script>

  </body>
</html>