<?php
// TMDb API key
$apiKey = "7e1340463fa8af2611d51d91fb7fcc46";

// Base URL for images
$imageBase = "https://image.tmdb.org/t/p/w500";
$bannerBase = "https://image.tmdb.org/t/p/original";

// Default search query
$query = isset($_GET['q']) ? urlencode($_GET['q']) : "Avengers";

// TMDb Search API
$url = "https://api.themoviedb.org/3/search/movie?api_key=$apiKey&language=en-US&query=$query&page=1";

$response = file_get_contents($url);
$data = json_decode($response, true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Streamify - Movie Search</title>
  <style>
    body { font-family: Arial, sans-serif; background: #111; color: #fff; margin: 0; padding: 20px; }
    h1 { text-align: center; }
    form { text-align: center; margin-bottom: 30px; }
    input[type="text"] { padding: 10px; width: 300px; border-radius: 5px; border: none; }
    button { padding: 10px 15px; border: none; background: #e50914; color: #fff; border-radius: 5px; cursor: pointer; }
    .grid { display: flex; flex-wrap: wrap; gap: 20px; justify-content: center; }
    .card { background: #222; border-radius: 10px; overflow: hidden; width: 250px; text-align: center; }
    .card img { width: 100%; border-bottom: 2px solid #444; }
    .card h3 { font-size: 16px; margin: 10px; }
    .card p { font-size: 13px; margin: 0 10px 10px; color: #bbb; }
    .downloads { margin: 10px; }
    .downloads a { 
      display: inline-block; 
      margin: 5px; 
      padding: 8px 12px; 
      background: #e50914; 
      color: #fff; 
      text-decoration: none; 
      border-radius: 5px;
    }
    .downloads a:hover { background: #b20710; }
  </style>
</head>
<body>
  <h1>🎬 Search Movies</h1>
  
  <!-- Search bar -->
  <form method="GET">
    <input type="text" name="q" placeholder="Enter movie name..." value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
    <button type="submit">Search</button>
  </form>

  <div class="grid">
    <?php if (!empty($data['results'])): ?>
      <?php foreach ($data['results'] as $movie): ?>
        <div class="card">
          <!-- Poster -->
          <?php if (!empty($movie['poster_path'])): ?>
            <img src="<?php echo $imageBase . $movie['poster_path']; ?>" alt="<?php echo $movie['title']; ?>">
          <?php endif; ?>
          
          <h3><?php echo $movie['title']; ?></h3>
          <p>⭐ <?php echo $movie['vote_average']; ?> | 📅 <?php echo $movie['release_date']; ?></p>

          <!-- Download buttons -->
          <div class="downloads">
            <?php if (!empty($movie['poster_path'])): ?>
              <a href="<?php echo $imageBase . $movie['poster_path']; ?>" download="<?php echo $movie['title']; ?>_poster.jpg">Download Poster</a>
            <?php endif; ?>
            
            <?php if (!empty($movie['backdrop_path'])): ?>
              <a href="<?php echo $bannerBase . $movie['backdrop_path']; ?>" download="<?php echo $movie['title']; ?>_banner.jpg">Download Banner</a>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p style="text-align:center;">No results found. Try another movie!</p>
    <?php endif; ?>
  </div>
</body>
</html>