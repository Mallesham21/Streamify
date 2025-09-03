<?php
require_once 'db.php';

// Initialize variables
$uploaded_url = "";
$error = "";
$success = "";
$api_key = "fba04b85-918b-4dc2-866a-8358cc43a2b4"; // Pixeldrain API key

// Process form submission
// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs - null-safe trimming
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $release_year = isset($_POST['release_year']) ? intval($_POST['release_year']) : 0;
    $content_type = isset($_POST['content_type']) ? $_POST['content_type'] : '';
    $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    $is_featured = isset($_POST['featured']) ? 1 : 0;
    
    // Basic validation
    if (empty($title)) {
        $error = "Title is required";
    } elseif (empty($description)) {
        $error = "Description is required";
    } elseif ($release_year < 1900 || $release_year > date('Y') + 5) {
        $error = "Invalid release year";
    } elseif (!isset($_FILES['video_file']) || $_FILES['video_file']['error'] == UPLOAD_ERR_NO_FILE) {
        $error = "Video file is required";
    } else {
        // Rest of your upload processing code...
        // Handle thumbnail upload
        $thumbnail_url = null;
        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] == UPLOAD_ERR_OK) {
            $thumbnail_dir = 'thumbnails/';
            if (!file_exists($thumbnail_dir)) {
                mkdir($thumbnail_dir, 0777, true);
            }
            $thumbnail_ext = pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION);
            $thumbnail_name = uniqid() . '.' . $thumbnail_ext;
            $thumbnail_path = $thumbnail_dir . $thumbnail_name;
            
            if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $thumbnail_path)) {
                $thumbnail_url = $thumbnail_path;
            }
        }
        
        // Handle banner upload
        $banner_url = null;
        if (isset($_FILES['banner']) && $_FILES['banner']['error'] == UPLOAD_ERR_OK) {
            $banner_dir = 'banners/';
            if (!file_exists($banner_dir)) {
                mkdir($banner_dir, 0777, true);
            }
            $banner_ext = pathinfo($_FILES['banner']['name'], PATHINFO_EXTENSION);
            $banner_name = uniqid() . '.' . $banner_ext;
            $banner_path = $banner_dir . $banner_name;
            
            if (move_uploaded_file($_FILES['banner']['tmp_name'], $banner_path)) {
                $banner_url = $banner_path;
            }
        }
        
        // Handle video upload to Pixeldrain
        $file_path = $_FILES['video_file']['tmp_name'];
        $file_name = basename($_FILES['video_file']['name']);
        
        $ch = curl_init();
        $url = "https://pixeldrain.com/api/file/" . urlencode($file_name);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_PUT, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, ":" . $api_key);
        
        $file_handle = fopen($file_path, 'r');
        curl_setopt($ch, CURLOPT_INFILE, $file_handle);
        curl_setopt($ch, CURLOPT_INFILESIZE, filesize($file_path));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/octet-stream']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        fclose($file_handle);
        
        if (curl_errno($ch)) {
            $error = "Upload error: " . curl_error($ch);
        } else {
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($http_code >= 200 && $http_code < 300) {
                $result = json_decode($response, true);
                $uploaded_url = "https://pixeldrain.com/u/" . $result['id'];
                $video_path = "videos/" . $result['id'] . ".mp4";
                
                // Insert into database
                $stmt = $conn->prepare("INSERT INTO content (title, description, release_year, content_type, thumbnail_url, banner_url, video_path, featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssissssi", $title, $description, $release_year, $content_type, $thumbnail_url, $banner_url, $video_path, $is_featured);
                
                if ($stmt->execute()) {
                    $content_id = $stmt->insert_id;
                    
                    // Insert into content_categories
                    $stmt = $conn->prepare("INSERT INTO content_categories (content_id, category_id) VALUES (?, ?)");
                    $stmt->bind_param("ii", $content_id, $category_id);
                    $stmt->execute();
                    
                    // Handle episodes if it's a TV show
                    if ($content_type === 'tv_show' && isset($_POST['episodes'])) {
                        foreach ($_POST['episodes'] as $episode) {
                            if (!empty($episode['title']) && !empty($episode['description']) && isset($_FILES['episode_videos']['tmp_name'][$episode['index']])) {
                                $episode_file = $_FILES['episode_videos']['tmp_name'][$episode['index']];
                                $episode_file_name = basename($_FILES['episode_videos']['name'][$episode['index']]);
                                
                                // Upload episode video to Pixeldrain
                                $ch_ep = curl_init();
                                $url_ep = "https://pixeldrain.com/api/file/" . urlencode($episode_file_name);
                                curl_setopt($ch_ep, CURLOPT_URL, $url_ep);
                                curl_setopt($ch_ep, CURLOPT_PUT, true);
                                curl_setopt($ch_ep, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                                curl_setopt($ch_ep, CURLOPT_USERPWD, ":" . $api_key);
                                
                                $file_handle_ep = fopen($episode_file, 'r');
                                curl_setopt($ch_ep, CURLOPT_INFILE, $file_handle_ep);
                                curl_setopt($ch_ep, CURLOPT_INFILESIZE, filesize($episode_file));
                                curl_setopt($ch_ep, CURLOPT_HTTPHEADER, ['Content-Type: application/octet-stream']);
                                curl_setopt($ch_ep, CURLOPT_RETURNTRANSFER, true);
                                
                                $response_ep = curl_exec($ch_ep);
                                fclose($file_handle_ep);
                                
                                if (!curl_errno($ch_ep)) {
                                    $result_ep = json_decode($response_ep, true);
                                    $episode_video_path = "videos/episodes/" . $result_ep['id'] . ".mp4";
                                    
                                    // Insert episode into database
                                    $stmt_ep = $conn->prepare("INSERT INTO episodes (content_id, episode_number, title, description, duration_minutes, release_date, rating, video_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                                    $stmt_ep->bind_param("iissdiss", $content_id, $episode['number'], $episode['title'], $episode['description'], $episode['duration'], $episode['release_date'], $episode['rating'], $episode_video_path);
                                    $stmt_ep->execute();
                                }
                                curl_close($ch_ep);
                            }
                        }
                    }
                    
                    $success = "Content uploaded successfully!";
                } else {
                    $error = "Database error: " . $stmt->error;
                }
            } else {
                $error = "API Error (HTTP $http_code): " . $response;
            }
        }
        curl_close($ch);
    }
}

// Get categories for dropdown
$categories = [];
$result = $conn->query("SELECT * FROM categories");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Streamify Admin - Video Upload</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #6a00ff;
            --secondary: #b13bff;
            --accent: #ffffff;
            --bg-dark: #0f0c1d;
            --bg-light: #1a1730;
            --text-light: #f0f0f0;
            --text-dark: #333333;
            --glass: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-light);
        }
        
        .card {
            background-color: var(--bg-light);
            border: 1px solid var(--glass-border);
            border-radius: 15px;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(106, 0, 255, 0.3);
        }
        
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
            border-radius: 50px;
            padding: 10px 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary);
            border-color: var(--secondary);
            transform: translateY(-2px);
        }
        
        .form-control, .form-select {
            background-color: var(--glass);
            border: 1px solid var(--glass-border);
            color: var(--text-light);
            border-radius: 10px;
            padding: 12px 15px;
        }
        
        .form-control:focus, .form-select:focus {
            background-color: var(--glass);
            color: var(--text-light);
            border-color: var(--secondary);
            box-shadow: 0 0 0 0.25rem rgba(177, 59, 255, 0.25);
        }
        
        .input-group-text {
            background-color: var(--glass);
            border: 1px solid var(--glass-border);
            color: var(--text-light);
            border-radius: 10px 0 0 10px;
        }
        
        .progress {
            height: 10px;
            border-radius: 5px;
            background-color: var(--glass);
        }
        
        .progress-bar {
            background-color: var(--secondary);
            transition: width 0.3s ease;
        }
        
        .upload-area {
            border: 2px dashed var(--glass-border);
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background-color: var(--glass);
        }
        
        .upload-area:hover {
            border-color: var(--secondary);
            background-color: rgba(177, 59, 255, 0.1);
        }
        
        .video-preview {
            border-radius: 15px;
            overflow: hidden;
            background-color: #000;
        }
        
        .is-invalid {
            border-color: #ff3860 !important;
        }
        
        .invalid-feedback {
            color: #ff3860;
        }
        
        .episode-card {
            background-color: rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
        }
        
        .episode-header {
            cursor: pointer;
            padding: 10px;
            background-color: rgba(106, 0, 255, 0.2);
            border-radius: 8px;
            margin-bottom: 10px;
        }
        
        @media (max-width: 768px) {
            .card {
                margin-bottom: 20px;
            }
        }
        
        /* Fix text visibility */
        .text-muted {
            color: #aaa !important;
        }
        
        .form-label, .form-check-label {
            color: var(--text-light) !important;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row mb-4">
            <div class="col-12 text-center">
                <h1 class="display-4 fw-bold" style="color: var(--secondary);">
                    <i class="fas fa-film me-2"></i>Streamify Admin
                </h1>
                <p class="lead">Upload new movies or TV shows to your streaming platform</p>
            </div>
        </div>
        
        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <form id="uploadForm" method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-lg-6">
                    <div class="card mb-4 p-4">
                        <h3 class="mb-4"><i class="fas fa-info-circle me-2"></i>Content Details</h3>
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                            <div class="invalid-feedback">Please provide a title.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                            <div class="invalid-feedback">Please provide a description.</div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="release_year" class="form-label">Release Year</label>
                                <input type="number" class="form-control" id="release_year" name="release_year" min="1900" max="<?php echo date('Y') + 5; ?>" required>
                                <div class="invalid-feedback">Please provide a valid year.</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="content_type" class="form-label">Content Type</label>
                                <select class="form-select" id="content_type" name="content_type" required>
                                    <option value="movie">Movie</option>
                                    <option value="tv_show">TV Show</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Category</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['category_id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="featured" name="featured">
                            <label class="form-check-label" for="featured">Featured Content</label>
                        </div>
                    </div>
                    
                    <div class="card mb-4 p-4">
                        <h3 class="mb-4"><i class="fas fa-image me-2"></i>Thumbnail</h3>
                        <div class="mb-3">
                            <label for="thumbnail" class="form-label">Upload Thumbnail (Optional)</label>
                            <input class="form-control" type="file" id="thumbnail" name="thumbnail" accept="image/*">
                            <div class="invalid-feedback">Please select a valid image file.</div>
                        </div>
                        <div id="thumbnailPreview" class="mt-3 text-center"></div>
                    </div>
                    
                    <div class="card mb-4 p-4">
                        <h3 class="mb-4"><i class="fas fa-image me-2"></i>Banner</h3>
                        <div class="mb-3">
                            <label for="banner" class="form-label">Upload Banner (Optional)</label>
                            <input class="form-control" type="file" id="banner" name="banner" accept="image/*">
                            <div class="invalid-feedback">Please select a valid image file.</div>
                        </div>
                        <div id="bannerPreview" class="mt-3 text-center"></div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="card mb-4 p-4">
                        <h3 class="mb-4"><i class="fas fa-video me-2"></i>Video Upload</h3>
                        
                        <div class="upload-area mb-3" id="dropArea">
                            <input type="file" id="video_file" name="video_file" accept="video/*" class="d-none" required>
                            <i class="fas fa-cloud-upload-alt fa-3x mb-3" style="color: var(--secondary);"></i>
                            <h5>Drag & Drop your video file here</h5>
                            <p class="text-muted">or click to browse files</p>
                            <button type="button" class="btn btn-outline-primary mt-2" id="browseBtn">Browse Files</button>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Upload Progress</label>
                            <div class="progress">
                                <div id="uploadProgress" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                            </div>
                        </div>
                        
                        <div id="fileInfo" class="mb-3 d-none">
                            <p><strong>Selected file:</strong> <span id="fileName"></span></p>
                            <p><strong>File size:</strong> <span id="fileSize"></span></p>
                        </div>
                        
                        <div id="videoPreview" class="video-preview mb-3 d-none">
                            <video id="previewPlayer" controls class="w-100"></video>
                        </div>
                    </div>
                    
                    <!-- Episodes Section (shown only for TV shows) -->
                    <div class="card mb-4 p-4" id="episodesSection" style="display: none;">
                        <h3 class="mb-4"><i class="fas fa-list-ol me-2"></i>Episodes</h3>
                        <div id="episodesContainer">
                            <!-- Episode fields will be added here dynamically -->
                        </div>
                        <button type="button" class="btn btn-outline-primary mt-3" id="addEpisodeBtn">
                            <i class="fas fa-plus me-2"></i>Add Episode
                        </button>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                            <i class="fas fa-upload me-2"></i>Upload Content
                        </button>
                    </div>
                </div>
            </div>
        </form>
        
        <?php if ($uploaded_url): ?>
        <div class="card mt-4 p-4">
            <h3 class="mb-4"><i class="fas fa-check-circle me-2" style="color: #4CAF50;"></i>Upload Successful</h3>
            <div class="row">
                <div class="col-md-6">
                    <h5>Video Link:</h5>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="videoLink" value="<?php echo $uploaded_url; ?>" readonly>
                        <button class="btn btn-outline-secondary" type="button" id="copyBtn">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    <a href="<?php echo $uploaded_url; ?>" target="_blank" class="btn btn-outline-primary">
                        <i class="fas fa-external-link-alt me-2"></i>Open in Pixeldrain
                    </a>
                </div>
                <div class="col-md-6">
                    <h5>Embedded Player:</h5>
                    <div class="ratio ratio-16x9">
                        <video controls class="w-100">
                            <source src="<?php echo $uploaded_url; ?>" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // File upload handling
        const dropArea = document.getElementById('dropArea');
        const fileInput = document.getElementById('video_file');
        const browseBtn = document.getElementById('browseBtn');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const fileInfo = document.getElementById('fileInfo');
        const videoPreview = document.getElementById('videoPreview');
        const previewPlayer = document.getElementById('previewPlayer');
        const thumbnailInput = document.getElementById('thumbnail');
        const thumbnailPreview = document.getElementById('thumbnailPreview');
        const bannerInput = document.getElementById('banner');
        const bannerPreview = document.getElementById('bannerPreview');
        const contentTypeSelect = document.getElementById('content_type');
        const episodesSection = document.getElementById('episodesSection');
        const episodesContainer = document.getElementById('episodesContainer');
        const addEpisodeBtn = document.getElementById('addEpisodeBtn');
        let episodeCount = 0;

        // Show/hide episodes section based on content type
        contentTypeSelect.addEventListener('change', function() {
            if (this.value === 'tv_show') {
                episodesSection.style.display = 'block';
            } else {
                episodesSection.style.display = 'none';
            }
        });

        // Add episode button click handler
        addEpisodeBtn.addEventListener('click', function() {
            episodeCount++;
            const episodeHtml = `
                <div class="episode-card" id="episode-${episodeCount}">
                    <div class="episode-header d-flex justify-content-between align-items-center" onclick="toggleEpisode(${episodeCount})">
                        <h5 class="mb-0">Episode ${episodeCount}</h5>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="episode-content">
                        <input type="hidden" name="episodes[${episodeCount}][index]" value="${episodeCount}">
                        <input type="hidden" name="episodes[${episodeCount}][number]" value="${episodeCount}">
                        
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="episodes[${episodeCount}][title]" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="episodes[${episodeCount}][description]" rows="2" required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Duration (minutes)</label>
                                <input type="number" class="form-control" name="episodes[${episodeCount}][duration]" min="1" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Release Date</label>
                                <input type="date" class="form-control" name="episodes[${episodeCount}][release_date]" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Rating (1-10)</label>
                            <input type="number" class="form-control" name="episodes[${episodeCount}][rating]" min="1" max="10" step="0.1">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Video File</label>
                            <input type="file" class="form-control" name="episode_videos[]" accept="video/*" required>
                        </div>
                        
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeEpisode(${episodeCount})">
                            <i class="fas fa-trash me-1"></i>Remove Episode
                        </button>
                    </div>
                </div>
            `;
            episodesContainer.insertAdjacentHTML('beforeend', episodeHtml);
        });

        // Toggle episode visibility
        window.toggleEpisode = function(episodeId) {
            const episode = document.getElementById(`episode-${episodeId}`);
            const content = episode.querySelector('.episode-content');
            const icon = episode.querySelector('.fa-chevron-down');
            
            if (content.style.display === 'none' || !content.style.display) {
                content.style.display = 'block';
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            } else {
                content.style.display = 'none';
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            }
        };

        // Remove episode
        window.removeEpisode = function(episodeId) {
            const episode = document.getElementById(`episode-${episodeId}`);
            episode.remove();
            // Re-number remaining episodes
            const remainingEpisodes = episodesContainer.querySelectorAll('.episode-card');
            remainingEpisodes.forEach((ep, index) => {
                const header = ep.querySelector('.episode-header h5');
                header.textContent = `Episode ${index + 1}`;
                // Update hidden input values
                const numberInput = ep.querySelector('input[name^="episodes"][name$="[number]"]');
                const indexInput = ep.querySelector('input[name^="episodes"][name$="[index]"]');
                if (numberInput) numberInput.value = index + 1;
                if (indexInput) indexInput.value = index;
            });
            episodeCount = remainingEpisodes.length;
        };

        // Prevent default drag behaviors
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        // Highlight drop area when item is dragged over it
        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, unhighlight, false);
        });

        function highlight() {
            dropArea.style.borderColor = '#b13bff';
            dropArea.style.backgroundColor = 'rgba(177, 59, 255, 0.1)';
        }

        function unhighlight() {
            dropArea.style.borderColor = 'rgba(255, 255, 255, 0.2)';
            dropArea.style.backgroundColor = 'rgba(255, 255, 255, 0.1)';
        }

        // Handle dropped files
        dropArea.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files.length > 0) {
                fileInput.files = files;
                handleFiles(files);
            }
        }

        // Browse button click
        browseBtn.addEventListener('click', () => {
            fileInput.click();
        });

        // File input change
        fileInput.addEventListener('change', () => {
            if (fileInput.files.length > 0) {
                handleFiles(fileInput.files);
            }
        });

        // Thumbnail preview
        thumbnailInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                if (!file.type.match('image.*')) {
                    thumbnailInput.classList.add('is-invalid');
                    return;
                }
                
                thumbnailInput.classList.remove('is-invalid');
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    thumbnailPreview.innerHTML = `
                        <img src="${e.target.result}" class="img-thumbnail" style="max-height: 200px;">
                    `;
                }
                
                reader.readAsDataURL(file);
            }
        });

        // Banner preview
        bannerInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                if (!file.type.match('image.*')) {
                    bannerInput.classList.add('is-invalid');
                    return;
                }
                
                bannerInput.classList.remove('is-invalid');
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    bannerPreview.innerHTML = `
                        <img src="${e.target.result}" class="img-fluid" style="max-height: 200px;">
                    `;
                }
                
                reader.readAsDataURL(file);
            }
        });

        // Handle selected files
        function handleFiles(files) {
            const file = files[0];
            
            if (!file.type.match('video.*')) {
                fileInput.classList.add('is-invalid');
                return;
            }
            
            fileInput.classList.remove('is-invalid');
            
            // Display file info
            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);
            fileInfo.classList.remove('d-none');
            
            // Create video preview
            const videoURL = URL.createObjectURL(file);
            previewPlayer.src = videoURL;
            videoPreview.classList.remove('d-none');
            
            // Simulate upload progress (in a real app, this would be from the actual upload)
            if (document.getElementById('uploadForm').getAttribute('data-uploading') !== 'true') {
                simulateProgress();
            }
        }

        // Format file size
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Form validation
        const form = document.getElementById('uploadForm');
        const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');

        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    input.classList.add('is-invalid');
                    isValid = false;
                } else {
                    input.classList.remove('is-invalid');
                }
            });

            if (!fileInput.files.length) {
                fileInput.classList.add('is-invalid');
                isValid = false;
            } else {
                fileInput.classList.remove('is-invalid');
            }

            // Validate episodes if TV show
            if (contentTypeSelect.value === 'tv_show') {
                const episodeInputs = form.querySelectorAll('[name^="episodes"]');
                episodeInputs.forEach(input => {
                    if (!input.value.trim() && input.required) {
                        input.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        input.classList.remove('is-invalid');
                    }
                });
            }

            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            } else {
                // Disable submit button during upload
                document.getElementById('submitBtn').disabled = true;
                document.getElementById('submitBtn').innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Uploading...';
                form.setAttribute('data-uploading', 'true');
            }
        });

        // Copy link button
        if (document.getElementById('copyBtn')) {
            document.getElementById('copyBtn').addEventListener('click', () => {
                const copyText = document.getElementById('videoLink');
                copyText.select();
                copyText.setSelectionRange(0, 99999);
                document.execCommand('copy');
                
                const copyBtn = document.getElementById('copyBtn');
                copyBtn.innerHTML = '<i class="fas fa-check"></i>';
                setTimeout(() => {
                    copyBtn.innerHTML = '<i class="fas fa-copy"></i>';
                }, 2000);
            });
        }

        // Simulate upload progress (for demo)
        function simulateProgress() {
            let progress = 0;
            const progressBar = document.getElementById('uploadProgress');
            const interval = setInterval(() => {
                progress += Math.random() * 10;
                if (progress >= 100) {
                    progress = 100;
                    clearInterval(interval);
                }
                progressBar.style.width = progress + '%';
                progressBar.textContent = Math.round(progress) + '%';
                progressBar.setAttribute('aria-valuenow', progress);
            }, 300);
        }
    </script>
</body>
</html>
<?php
$conn->close();
?>