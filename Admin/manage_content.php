<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Content - Streamify Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <!-- Video.js CSS -->
    <link href="https://vjs.zencdn.net/8.10.0/video-js.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #6f42c1;
            --secondary-color: #20c997;
            --bg: #f8f9fa;
            --card: #ffffff;
            --text: #212529;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            color: var(--text);
        }

        .sidebar {
            background-color: var(--card);
            min-height: 100vh;
            box-shadow: 0 0 15px rgba(177, 59, 255, 0.1);
        }

        .sidebar .nav-link {
            color: var(--text);
            padding: 12px 20px;
            margin: 5px 0;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: var(--primary-color);
            color: white;
        }

        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .navbar {
            background-color: var(--card);
            box-shadow: 0 2px 15px rgba(177, 59, 255, 0.1);
        }

        .card {
            background-color: var(--card);
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(177, 59, 255, 0.15);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: #5a32a3;
            border-color: #5a32a3;
        }

        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        .table {
            color: var(--text);
            margin-bottom: 0;
        }

        .table th {
            border-top: none;
            font-weight: 600;
            background-color: rgba(177, 59, 255, 0.1);
        }

.content-thumbnail {
    width: 60px;
    height: 90px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}
        .badge-movie {
            background-color: var(--primary-color);
        }

        .badge-tv-show {
            background-color: var(--secondary-color);
        }

        .badge-featured {
            background-color: #ffc107;
            color: #212529;
        }

        .stats-card {
            text-align: center;
            padding: 20px;
            border-left: 4px solid var(--primary-color);
        }

        .stats-card i {
            font-size: 2rem;
            margin-bottom: 10px;
            color: var(--primary-color);
        }

        .stats-card .number {
            font-size: 1.8rem;
            font-weight: bold;
            margin: 5px 0;
        }

        .modal-content {
            border-radius: 12px;
            border: none;
        }

        .modal-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: 12px 12px 0 0;
        }

        .modal-header .btn-close {
            filter: invert(1);
        }

        .step-progress {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            position: relative;
        }

        .step-progress::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 2px;
            background-color: #e9ecef;
            z-index: 1;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
        }

        .step-circle {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 5px;
            font-weight: bold;
            color: #6c757d;
        }

        .step.active .step-circle {
            background-color: var(--primary-color);
            color: white;
        }

        .step.completed .step-circle {
            background-color: var(--secondary-color);
            color: white;
        }

        .step-label {
            font-size: 0.8rem;
            color: #6c757d;
        }

        .step.active .step-label {
            color: var(--primary-color);
            font-weight: bold;
        }

        .step.completed .step-label {
            color: var(--secondary-color);
        }

        .content-preview {
            max-height: 300px;
            overflow: hidden;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .content-preview img {
            width: 100%;
            height: auto;
            object-fit: cover;
        }

        .episode-list {
            max-height: 300px;
            overflow-y: auto;
        }


    .episode-item {
        padding: 15px;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        margin-bottom: 15px;
        background-color: #f8f9fa;
    }

    .episode-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
        padding-bottom: 10px;
        border-bottom: 1px solid #dee2e6;
    }

    .episode-fields {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 15px;
        margin-bottom: 10px;
    }

    .episode-description {
        grid-column: 1 / -1;
    }

    .episode-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .episode-list {
        max-height: 500px;
        overflow-y: auto;
        padding: 10px;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        background-color: white;
    }

    .file-preview {
        max-width: 100%;
        max-height: 150px;
        border-radius: 8px;
        margin-top: 10px;
    }
    
    .alert-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
    }
    
    .step-hidden {
        display: none !important;
    }
    
    .media-section {
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
    }

    .form-section {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 15px;
    }
    </style>
</head>
<body>
    <?php
    // Database connection

    $conn = new mysqli('localhost', 'root', '', 'Streamify');
    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    }

    // Handle form submissions for delete only

    // Fetch all content with categories
    $content_query = "
        SELECT c.*, 
               GROUP_CONCAT(DISTINCT cat.name) as category_names,
               COUNT(DISTINCT e.episode_id) as episode_count
        FROM content c
        LEFT JOIN content_categories cc ON c.content_id = cc.content_id
        LEFT JOIN categories cat ON cc.category_id = cat.category_id
        LEFT JOIN episodes e ON c.content_id = e.content_id
        GROUP BY c.content_id
        ORDER BY c.created_at DESC
    ";
    $content_result = $conn->query($content_query);

    // Fetch all categories
    $categories_query = "SELECT * FROM categories ORDER BY name";
    $categories_result = $conn->query($categories_query);

    // Statistics
    $total_movies = $conn->query("SELECT COUNT(*) as count FROM content WHERE content_type = 'movie'")->fetch_assoc()['count'];
    $total_tv_shows = $conn->query("SELECT COUNT(*) as count FROM content WHERE content_type = 'tv_show'")->fetch_assoc()['count'];
    $total_featured = $conn->query("SELECT COUNT(*) as count FROM content WHERE featured = 1")->fetch_assoc()['count'];
    $total_views = $conn->query("SELECT SUM(views) as total FROM content")->fetch_assoc()['total'];
    ?>

<!-- Alert Container for Messages -->
<div class="alert-container"></div>
<div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'includes/sidebar.php'; ?>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 ml-auto p-0">
                <!-- Top Navbar -->
                <nav class="navbar navbar-expand-lg">
                    <div class="container-fluid">
                        <div class="navbar-nav me-auto">
                            <span class="navbar-text">
                                <h4 class="mb-0">Content Management</h4>
                            </span>
                        </div>
                        
                        <ul class="navbar-nav mb-2 mb-lg-0">
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown">
                                    <img src="https://ui-avatars.com/api/?name=Admin&background=b13bff&color=fff" class="user-avatar me-2" style="width: 30px; height: 30px; border-radius: 50%;">
                                    Admin
                                </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                            </ul>
                            </li>
                        </ul>
                    </div>
                </nav>

                <!-- Content Section -->
                <div class="p-4">
                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="card stats-card">
                                <i class="fas fa-film"></i>
                                <div class="number"><?php echo $total_movies; ?></div>
                                <div>Total Movies</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card stats-card">
                                <i class="fas fa-tv"></i>
                                <div class="number"><?php echo $total_tv_shows; ?></div>
                                <div>Total TV Shows</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card stats-card">
                                <i class="fas fa-star"></i>
                                <div class="number"><?php echo $total_featured; ?></div>
                                <div>Featured Content</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card stats-card">
                                <i class="fas fa-eye"></i>
                                <div class="number"><?php echo number_format($total_views); ?></div>
                                <div>Total Views</div>
                            </div>
                        </div>
                    </div>

                    <!-- Content Table Card -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">All Content</h5>
                            <div>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addContentModal">
                                    <i class="fas fa-plus me-1"></i> Add Content
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="contentTable" class="table table-hover">
<thead>
    <tr>
        <th>Thumbnail</th>
        <th>Title</th>
        <th>Type</th>
        <th>Release Year</th>
        <th>Rating</th>
        <th>Views</th>
        <th>Featured</th>
        <th>Premium</th>
        <th>Actions</th>
    </tr>
</thead>                                    <tbody>
                                        <?php while($content = $content_result->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <img src="<?php echo   $content['thumbnail_url'] ?: 'https://via.placeholder.com/80x60?text=No+Image'; ?>" 
                                                     alt="<?php echo $content['title']; ?>" class="content-thumbnail">
                                            </td>
                                            <td><?php echo htmlspecialchars($content['title']); ?></td>
                                            <td>
                                                <span class="badge <?php echo $content['content_type'] === 'movie' ? 'badge-movie' : 'badge-tv-show'; ?>">
                                                    <?php echo $content['content_type'] === 'movie' ? 'Movie' : 'TV Show'; ?>
                                                    <?php if($content['episode_count'] > 0): ?>
                                                        <span class="badge bg-light text-dark"><?php echo $content['episode_count']; ?> eps</span>
                                                    <?php endif; ?>
                                                </span>
                                            </td>
<td><?php echo $content['release_year'] ?: 'N/A'; ?></td>

<td><?php echo $content['rating'] ? $content['rating'] . '/10' : 'N/A'; ?></td>
                                            <td><?php echo number_format($content['views']); ?></td>
                                            <td>
                                                <span class="badge <?php echo $content['featured'] ? 'badge-featured' : 'bg-secondary'; ?>">
                                                    <?php echo $content['featured'] ? 'Yes' : 'No'; ?>
                                                </span>
                                            </td>
                                            <td>
    <span class="badge <?php echo $content['is_premium'] ? 'bg-warning' : 'bg-secondary'; ?>">
        <?php echo $content['is_premium'] ? 'Yes' : 'No'; ?>
    </span>
</td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary view-content" 
                                                            data-id="<?php echo $content['content_id']; ?>"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#viewContentModal">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-outline-secondary edit-content" 
                                                            data-id="<?php echo $content['content_id']; ?>"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editContentModal">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger delete-content" 
                                                            data-id="<?php echo $content['content_id']; ?>"
                                                            data-title="<?php echo htmlspecialchars($content['title']); ?>"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#deleteContentModal">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Content Modal -->
    <div class="modal fade" id="viewContentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Content Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="viewContentBody">
                    <!-- Content will be loaded via AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="viewFeedbackBtn" data-bs-toggle="modal" data-bs-target="#viewFeedbackModal">View Feedback</button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Feedback Modal -->
    <div class="modal fade" id="viewFeedbackModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Feedback for <span id="feedbackContentTitle"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="feedbackList">
                    <!-- Feedback will be loaded via AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Content Modal -->
    <div class="modal fade" id="editContentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Content</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="editContentBody">
                    <!-- Content will be loaded via AJAX -->
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Content Modal -->
<!-- Delete Content Modal -->
<div class="modal fade" id="deleteContentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Content</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete "<span id="delete_content_title"></span>"?</p>
                <p class="text-danger">This action cannot be undone and will delete all associated data.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>
<!-- Add Content Modal -->
<div class="modal fade" id="addContentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Content</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="step-progress mb-4">
                    <div class="step active" id="step1">
                        <div class="step-circle">1</div>
                        <div class="step-label">Basic Info</div>
                    </div>
                    <div class="step" id="step2">
                        <div class="step-circle">2</div>
                        <div class="step-label">Media & Type</div>
                    </div>
                    <div class="step" id="step3">
                        <div class="step-circle">3</div>
                        <div class="step-label">Review</div>
                    </div>
                </div>

                <form id="addContentForm" enctype="multipart/form-data" method="POST" action="add_content_handler.php">
                    <input type="hidden" name="action" value="add_content">
                    
                    <!-- Step 1: Basic Information -->
                    <div class="step-content" id="step1-content">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="add_title" class="form-label">Title *</label>
                                    <input type="text" class="form-control" id="add_title" name="title" required>
                                </div>
                                <div class="mb-3">
                                    <label for="add_description" class="form-label">Description</label>
                                    <textarea class="form-control" id="add_description" name="description" rows="3"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="add_categories" class="form-label">Categories</label>
                                    <select class="form-select" id="add_categories" name="categories[]" multiple>
                                        <?php 
                                        // Reset categories result pointer
                                        $categories_result->data_seek(0);
                                        while($category = $categories_result->fetch_assoc()): ?>
                                            <option value="<?php echo $category['category_id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                    <small class="form-text text-muted">Hold Ctrl to select multiple categories</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="add_release_year" class="form-label">Release Year *</label>
                                    <select class="form-select" id="add_release_year" name="release_year" required>
                                        <option value="">Select Year</option>
                                        <?php
                                        $current_year = date('Y');
                                        for ($year = $current_year; $year >= 1900; $year--) {
                                            echo "<option value='$year'>$year</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="add_duration" class="form-label">Duration (minutes)</label>
                                    <input type="number" class="form-control" id="add_duration" name="duration" min="1">
                                </div>
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="add_featured" name="featured" value="1">
                                        <label class="form-check-label" for="add_featured">
                                            Featured Content
                                        </label>
                                    </div>
                                </div>
                                <div class="mb-3">
    <div class="form-check">
        <input class="form-check-input" type="checkbox" id="add_is_premium" name="is_premium" value="1">
        <label class="form-check-label" for="add_is_premium">
            Premium Content (Only for subscribed users)
        </label>
    </div>
</div>
                                <div class="mb-3">
                                    <label class="form-label">Publish Option *</label>
                                    <div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="publish_option" id="publish_now" value="now" checked>
                                            <label class="form-check-label" for="publish_now">
                                                Publish Now
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="publish_option" id="schedule_later" value="schedule">
                                            <label class="form-check-label" for="schedule_later">
                                                Schedule for Later
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3" id="schedule_date_container" style="display: none;">
                                    <label for="add_schedule_date" class="form-label">Schedule Date & Time</label>
                                    <input type="datetime-local" class="form-control" id="add_schedule_date" name="schedule_date">
                                </div>
                                <div class="mb-3">
                                    <label for="add_rating" class="form-label">Initial Rating (0-10)</label>
                                    <input type="number" class="form-control" id="add_rating" name="rating" min="0" max="10" step="0.1">
                                </div>
                                <div class="mb-3">
</div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Media & Type -->
                    <div class="step-content" id="step2-content" style="display: none;">
                        <div class="row">
                            <div class="col-md-12 mb-4">
                                <div class="media-section">
                                    <h5>Content Type</h5>
                                    <div class="mb-3">
                                        <label for="add_content_type" class="form-label">Content Type *</label>
                                        <select class="form-select" id="add_content_type" name="content_type" required>
                                            <option value="movie">Movie</option>
                                            <option value="tv_show">TV Show</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="media-section">
                                    <h5>Images</h5>
                                    <div class="mb-3">
                                        <label for="add_thumbnail" class="form-label">Thumbnail *</label>
                                        <input type="file" class="form-control" id="add_thumbnail" name="thumbnail" accept="image/*" required>
                                        <div class="mt-2">
                                            <img id="add_thumbnail_preview" src="" alt="Thumbnail Preview" class="file-preview" style="display: none; max-height: 150px;">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="add_banner" class="form-label">Banner</label>
                                        <input type="file" class="form-control" id="add_banner" name="banner" accept="image/*">
                                        <div class="mt-2">
                                            <img id="add_banner_preview" src="" alt="Banner Preview" class="file-preview" style="display: none; max-height: 150px;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="media-section">
                                    <h5 id="video_section_title">Video File</h5>
                                    <div class="mb-3" id="add_video_container">
                                        <label for="add_video" class="form-label">Video File (for Movies)</label>
                                        <input type="file" class="form-control" id="add_video" name="video" accept="video/*">
                                        <div class="mt-2">
                                            <video id="add_video_preview" controls class="file-preview" style="display: none; max-height: 150px;">
                                                Your browser does not support the video tag.
                                            </video>
                                        </div>
                                    </div>
                                    
<div id="add_episodes_container" style="display: none;">
    <div class="form-section">
        <h5>Episodes Management</h5>
        <div id="add_episodes_list" class="episode-list mb-3"></div>
        <button type="button" class="btn btn-outline-primary" id="addNewEpisodeBtn">
            <i class="fas fa-plus me-1"></i> Add New Episode
        </button>
    </div>
</div>                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Review -->
                    <div class="step-content" id="step3-content" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Content Details</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Title:</strong></td>
                                        <td id="review_title"></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Type:</strong></td>
                                        <td id="review_content_type"></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Description:</strong></td>
                                        <td id="review_description"></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Categories:</strong></td>
                                        <td id="review_categories"></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Release Year:</strong></td>
                                        <td id="review_release_year"></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Duration:</strong></td>
                                        <td id="review_duration"></td>
                                    </tr>
<tr>
    <td><strong>Featured:</strong></td>
    <td id="review_featured"></td>
</tr>
<tr>
    <td><strong>Premium:</strong></td>
    <td id="review_premium"></td>
</tr>                                    <tr>
                                        <td><strong>Publish Option:</strong></td>
                                        <td id="review_publish_option"></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Schedule Date:</strong></td>
                                        <td id="review_schedule_date"></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Rating:</strong></td>
                                        <td id="review_rating"></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6>Media Preview</h6>
                                <div class="content-preview mb-3">
                                    <img id="review_thumbnail" src="" alt="Thumbnail Preview" style="width: 100%; max-height: 150px; object-fit: cover;">
                                </div>
                                <div class="content-preview">
                                    <img id="review_banner" src="" alt="Banner Preview" style="width: 100%; max-height: 150px; object-fit: cover;">
                                </div>
                            </div>
                        </div>
                        <div id="review_episodes_section" style="display: none;">
                            <h6>Episodes</h6>
                            <div id="review_episodes" class="episode-list"></div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="prevStepBtn" style="display: none;">Previous</button>
                <button type="button" class="btn btn-primary" id="nextStepBtn">Next</button>
                <button type="submit" form="addContentForm" class="btn btn-success" id="submitContentBtn" style="display: none;">Add Content</button>
            </div>
        </div>
    </div>
</div>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<!-- Video.js -->
<script src="https://vjs.zencdn.net/8.10.0/video.min.js"></script>

</body>
<script>
    let currentStep = 1;
    let episodeCounter = 1;
    let contentToDelete = null;
    let contentTitleToDelete = null;
    let currentAjaxRequest = null;

    $(document).ready(function() {
        // Initialize DataTable
        $('#contentTable').DataTable({
            pageLength: 100,
            responsive: true,
            order: [[1, 'asc']],
            drawCallback: function(settings) {
                // This ensures events work after page changes
                console.log('Table redrawn - page changed');
            }
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').alert('close');
        }, 5000);

        // Delete modal setup - EVENT DELEGATION
        $(document).on('click', '.delete-content', function() {
            contentToDelete = $(this).data('id');
            contentTitleToDelete = $(this).data('title');
            $('#delete_content_title').text(contentTitleToDelete);
        });

        // View content modal - load via AJAX - EVENT DELEGATION
        $(document).on('click', '.view-content', function() {
            const contentId = $(this).data('id');
            loadContentDetails(contentId);
        });

        // Edit content via ajax - EVENT DELEGATION
        $(document).on('click', '.edit-content', function() {
            const contentId = $(this).data('id');
            loadEditContent(contentId);
        });

        // View feedback modal
        $('#viewFeedbackBtn').on('click', function() {
            const contentId = $(this).data('content-id');
            $('#feedbackList').html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');
            
            $.ajax({
                url: 'get_content_feedback.php',
                type: 'GET',
                data: { content_id: contentId },
                success: function(response) {
                    $('#feedbackList').html(response);
                },
                error: function() {
                    $('#feedbackList').html('<div class="alert alert-danger">Error loading feedback.</div>');
                }
            });
        });

        // Add content modal - step navigation
        $('#nextStepBtn').on('click', function() {
            if (validateStep(currentStep)) {
                currentStep++;
                updateStepProgress();
            }
        });
        
        $('#prevStepBtn').on('click', function() {
            currentStep--;
            updateStepProgress();
        });
        
        // Reset steps when modal is closed
        $('#addContentModal').on('hidden.bs.modal', function() {
            currentStep = 1;
            episodeCounter = 1;
            updateStepProgress();
            $('#addContentForm')[0].reset();
            $('#add_episodes_list').empty();
            $('#add_thumbnail_preview, #add_banner_preview, #add_video_preview').hide();
            $('#schedule_date_container').hide();
            // Reset media visibility
            updateMediaVisibility();
        });

        // Content type change handler
        $('#add_content_type').on('change', function() {
            updateMediaVisibility();
        });
        
        // Publish option change handler
        $('input[name="publish_option"]').on('change', function() {
            if ($('#schedule_later').is(':checked')) {
                $('#schedule_date_container').show();
            } else {
                $('#schedule_date_container').hide();
            }
        });
        
        // File preview handlers
        $('#add_thumbnail').on('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#add_thumbnail_preview').attr('src', e.target.result).show();
                };
                reader.readAsDataURL(file);
            }
        });

        // Banner preview handler
        $('#add_banner').on('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#add_banner_preview').attr('src', e.target.result).show();
                };
                reader.readAsDataURL(file);
            }
        });

        $('#add_video').on('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const url = URL.createObjectURL(file);
                $('#add_video_preview').attr('src', url).show();
            }
        });
        
        // Add content form submission
        $('#addContentForm').on('submit', function(e) {
            e.preventDefault();
            
            if (validateStep(currentStep)) {
                const formData = new FormData(this);
                
                // Show loading state
                $('#submitContentBtn').html('<span class="spinner-border spinner-border-sm" role="status"></span> Adding...').prop('disabled', true);
                
                $.ajax({
                    url: 'add_content_handler.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        // Parse JSON response
                        let result;
                        try {
                            result = typeof response === 'string' ? JSON.parse(response) : response;
                        } catch (e) {
                            showAlert('Invalid response from server', 'danger');
                            $('#submitContentBtn').html('Add Content').prop('disabled', false);
                            return;
                        }
                        
                        if (result.success) {
                            showAlert(result.message, result.type);
                            $('#addContentModal').modal('hide');
                            // Refresh the page to show updated content
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            showAlert(result.message, result.type);
                            $('#submitContentBtn').html('Add Content').prop('disabled', false);
                        }
                    },
                    error: function(xhr, status, error) {
                        showAlert('Error adding content: ' + error, 'danger');
                        $('#submitContentBtn').html('Add Content').prop('disabled', false);
                    }
                });
            }
        });
        
        // Add episode button
        $('#addNewEpisodeBtn').on('click', function() {
            addEpisodeItem();
        });
        
        // Remove episode button (delegated event handler)
        $(document).on('click', '.remove-episode', function() {
            $(this).closest('.episode-item').remove();
            updateEpisodeNumbers();
        });

        // Confirm delete button
        $('#confirmDeleteBtn').on('click', function() {
            if (contentToDelete) {
                deleteContent(contentToDelete, contentTitleToDelete);
            }
        });

        // Initialize media visibility
        updateMediaVisibility();
    });

    // Function to load content details via AJAX
    function loadContentDetails(contentId) {
        // Cancel previous request if still pending
        if (currentAjaxRequest) {
            currentAjaxRequest.abort();
        }
        
        $('#viewContentBody').html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');
        
        currentAjaxRequest = $.ajax({
            url: 'get_content_details.php',
            type: 'GET',
            data: { content_id: contentId },
            timeout: 10000, // 10 second timeout
            success: function(response) {
                $('#viewContentBody').html(response);
                $('#viewFeedbackBtn').data('content-id', contentId);
                currentAjaxRequest = null;
            },
            error: function(xhr, status, error) {
                if (status !== 'abort') {
                    $('#viewContentBody').html('<div class="alert alert-danger">Error loading content details: ' + error + '</div>');
                }
                currentAjaxRequest = null;
            }
        });
    }

    // Function to load edit content via AJAX
    function loadEditContent(contentId) {
        // Cancel previous request if still pending
        if (currentAjaxRequest) {
            currentAjaxRequest.abort();
        }
        
        $('#editContentBody').html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');
        
        currentAjaxRequest = $.ajax({
            url: 'get_edit_content.php',
            type: 'GET',
            data: { content_id: contentId },
            timeout: 10000, // 10 second timeout
            success: function(response) {
                $('#editContentBody').html(response);
                currentAjaxRequest = null;
            },
            error: function(xhr, status, error) {
                if (status !== 'abort') {
                    $('#editContentBody').html('<div class="alert alert-danger">Error loading edit form: ' + error + '</div>');
                }
                currentAjaxRequest = null;
            }
        });
    }

    // Function to delete content via AJAX
    function deleteContent(contentId, contentTitle) {
        // Show loading state
        $('#confirmDeleteBtn').html('<span class="spinner-border spinner-border-sm" role="status"></span> Deleting...').prop('disabled', true);
        
        $.ajax({
            url: 'delete_content_handler.php',
            type: 'POST',
            data: {
                action: 'delete_content',
                content_id: contentId
            },
            success: function(response) {
                // Parse JSON response
                let result;
                try {
                    result = typeof response === 'string' ? JSON.parse(response) : response;
                } catch (e) {
                    showAlert('Invalid response from server', 'danger');
                    resetDeleteButton();
                    return;
                }
                
                if (result.success) {
                    showAlert(result.message, 'success');
                    $('#deleteContentModal').modal('hide');
                    
                    // Remove the row from the table
                    $(`button.delete-content[data-id="${contentId}"]`).closest('tr').fadeOut(300, function() {
                        $(this).remove();
                        // Refresh DataTable
                        $('#contentTable').DataTable().draw();
                    });
                    
                    // Update statistics if needed
                    updateStatistics();
                } else {
                    showAlert(result.message, 'danger');
                }
                
                resetDeleteButton();
            },
            error: function(xhr, status, error) {
                showAlert('Error deleting content: ' + error, 'danger');
                resetDeleteButton();
            }
        });
    }

    function resetDeleteButton() {
        $('#confirmDeleteBtn').html('Delete').prop('disabled', false);
    }

    function updateStatistics() {
        // You can implement AJAX call to refresh statistics if needed
        // For now, we'll just reload the page after a short delay to show updated stats
        setTimeout(() => {
            window.location.reload();
        }, 2000);
    }

    function addEpisodeItem() {
        const episodeItem = `
            <div class="episode-item" data-episode-id="${episodeCounter}">
                <div class="episode-header">
                    <h6 class="mb-0">Episode ${episodeCounter}</h6>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-episode">
                        <i class="fas fa-trash me-1"></i> Remove
                    </button>
                </div>
                <div class="episode-fields">
                    <div class="form-group">
                        <label class="form-label">Title *</label>
                        <input type="text" class="form-control" placeholder="Episode title" name="episode_titles[]" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Duration (minutes) *</label>
                        <input type="number" class="form-control" placeholder="Duration" name="episode_durations[]" min="1" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Release Date</label>
                        <input type="date" class="form-control" name="episode_dates[]">
                    </div>
                    <div class="form-group episode-description">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" placeholder="Episode description" name="episode_descriptions[]" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Video File</label>
                        <input type="file" class="form-control" name="episode_videos[]" accept="video/*">
                        <small class="form-text text-muted">Optional for now</small>
                    </div>
                </div>
            </div>
        `;
        $('#add_episodes_list').append(episodeItem);
        episodeCounter++;
        
        // Scroll to the new episode
        $('#add_episodes_list').animate({
            scrollTop: $('#add_episodes_list')[0].scrollHeight
        }, 500);
    }

    function updateEpisodeNumbers() {
        $('#add_episodes_list .episode-item').each(function(index) {
            const newNumber = index + 1;
            $(this).find('h6').text(`Episode ${newNumber}`);
            $(this).attr('data-episode-id', newNumber);
        });
        episodeCounter = $('#add_episodes_list .episode-item').length + 1;
    }

    function updateMediaVisibility() {
        const contentType = $('#add_content_type').val();
        if (contentType === 'movie') {
            $('#add_video_container').show();
            $('#add_episodes_container').hide();
            $('#video_section_title').text('Video File');
        } else {
            $('#add_video_container').hide();
            $('#add_episodes_container').show();
            $('#video_section_title').text('Episodes');
            
            // Auto-add first episode if none exist
            if ($('#add_episodes_list .episode-item').length === 0) {
                addEpisodeItem();
            }
        }
    }

    function updateStepProgress() {
        // Update step indicators
        $('.step').removeClass('active completed');
        
        for (let i = 1; i <= 3; i++) {
            if (i < currentStep) {
                $('#step' + i).addClass('completed');
            } else if (i === currentStep) {
                $('#step' + i).addClass('active');
            }
        }
        
        // Show/hide step content
        $('.step-content').hide();
        $('#step' + currentStep + '-content').show();
        
        // Update buttons
        if (currentStep === 1) {
            $('#prevStepBtn').hide();
            $('#nextStepBtn').show();
            $('#submitContentBtn').hide();
        } else if (currentStep === 3) {
            $('#prevStepBtn').show();
            $('#nextStepBtn').hide();
            $('#submitContentBtn').show();
            updateReviewStep();
        } else {
            $('#prevStepBtn').show();
            $('#nextStepBtn').show();
            $('#submitContentBtn').hide();
        }
    }

    function validateStep(step) {
        switch(step) {
            case 1:
                const title = $('#add_title').val().trim();
                if (!title) {
                    alert('Please enter a title.');
                    $('#add_title').focus();
                    return false;
                }
                
                const releaseYear = $('#add_release_year').val();
                if (!releaseYear) {
                    alert('Please select a release year.');
                    $('#add_release_year').focus();
                    return false;
                }
                
                if ($('#schedule_later').is(':checked') && !$('#add_schedule_date').val()) {
                    alert('Please select a schedule date.');
                    $('#add_schedule_date').focus();
                    return false;
                }
                return true;
            case 2:
                const thumbnail = $('#add_thumbnail').val();
                if (!thumbnail) {
                    alert('Please select a thumbnail.');
                    $('#add_thumbnail').focus();
                    return false;
                }
                
                // For TV shows, validate all episodes
                const contentType = $('#add_content_type').val();
                if (contentType === 'tv_show') {
                    const episodeCount = $('#add_episodes_list .episode-item').length;
                    if (episodeCount === 0) {
                        alert('Please add at least one episode for TV shows.');
                        return false;
                    }
                    
                    // Validate all episodes have titles and durations
                    let allEpisodesValid = true;
                    $('#add_episodes_list input[name="episode_titles[]"]').each(function() {
                        if (!$(this).val().trim()) {
                            alert('Please fill in all episode titles.');
                            $(this).focus();
                            allEpisodesValid = false;
                            return false; // break the loop
                        }
                    });
                    
                    if (allEpisodesValid) {
                        $('#add_episodes_list input[name="episode_durations[]"]').each(function() {
                            if (!$(this).val() || $(this).val() < 1) {
                                alert('Please fill in all episode durations (minimum 1 minute).');
                                $(this).focus();
                                allEpisodesValid = false;
                                return false; // break the loop
                            }
                        });
                    }
                    
                    return allEpisodesValid;
                }
                return true;
            default:
                return true;
        }
    }

    function updateReviewStep() {
        // Update review fields
        $('#review_title').text($('#add_title').val());
        $('#review_content_type').text($('#add_content_type').val() === 'movie' ? 'Movie' : 'TV Show');
        $('#review_description').text($('#add_description').val() || 'No description');
        
        // Get selected category names
        const selectedCategories = [];
        $('#add_categories option:selected').each(function() {
            selectedCategories.push($(this).text());
        });
        $('#review_categories').text(selectedCategories.join(', ') || 'None');
        
        $('#review_release_year').text($('#add_release_year').val());
        $('#review_duration').text(($('#add_duration').val() || '0') + ' minutes');
        $('#review_featured').text($('#add_featured').is(':checked') ? 'Yes' : 'No');
        $('#review_premium').text($('#add_is_premium').is(':checked') ? 'Yes' : 'No');         
        const publishOption = $('input[name="publish_option"]:checked').val();
        $('#review_publish_option').text(publishOption === 'now' ? 'Publish Now' : 'Schedule for Later');
        
        if (publishOption === 'schedule') {
            $('#review_schedule_date').text($('#add_schedule_date').val() || 'Not set');
        } else {
            $('#review_schedule_date').text('Immediate');
        }
        
        $('#review_rating').text($('#add_rating').val() || 'Not set');
        // Update media previews
        const thumbnailPreview = $('#add_thumbnail_preview').attr('src');
        if (thumbnailPreview) {
            $('#review_thumbnail').attr('src', thumbnailPreview);
        }
        
        const bannerPreview = $('#add_banner_preview').attr('src');
        if (bannerPreview) {
            $('#review_banner').attr('src', bannerPreview);
        }
        
        // Update episodes section
        const contentType = $('#add_content_type').val();
        if (contentType === 'tv_show') {
            $('#review_episodes_section').show();
            const episodesContainer = $('#review_episodes');
            episodesContainer.empty();
            
            $('#add_episodes_list .episode-item').each(function(index) {
                const title = $(this).find('input[name="episode_titles[]"]').val();
                const duration = $(this).find('input[name="episode_durations[]"]').val();
                const date = $(this).find('input[name="episode_dates[]"]').val();
                const description = $(this).find('textarea[name="episode_descriptions[]"]').val();
                
                episodesContainer.append(`
                    <div class="episode-item">
                        <div class="episode-header">
                            <strong>Episode ${index + 1}: ${title}</strong>
                            <span class="badge bg-primary">${duration} min</span>
                        </div>
                        ${description ? `<div class="episode-description"><small>${description}</small></div>` : ''}
                        ${date ? `<div class="episode-date"><small><strong>Release:</strong> ${date}</small></div>` : ''}
                    </div>
                `);
            });
        } else {
            $('#review_episodes_section').hide();
        }
    }

    function showAlert(message, type = 'info') {
        const alertContainer = $('.alert-container');
        const alertId = 'alert-' + Date.now();
        
        const iconClass = {
            'success': 'fa-check-circle',
            'danger': 'fa-exclamation-triangle',
            'warning': 'fa-exclamation-circle',
            'info': 'fa-info-circle'
        }[type] || 'fa-info-circle';
        
        const alertHtml = `
            <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show" role="alert">
                <i class="fas ${iconClass} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        alertContainer.append(alertHtml);
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            $(`#${alertId}`).alert('close');
        }, 5000);
        
        // Remove from DOM after fade out
        $(`#${alertId}`).on('closed.bs.alert', function() {
            $(this).remove();
        });
    }
</script>
<?php
// Close database connection
$conn->close();
?>