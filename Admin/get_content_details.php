<?php
$conn = new mysqli('localhost', 'root', '', 'Streamify');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

$content_id = $conn->real_escape_string($_GET['content_id']);

// Get content details
$content_query = "
    SELECT c.*, 
           GROUP_CONCAT(DISTINCT cat.name) as category_names,
           COUNT(DISTINCT e.episode_id) as episode_count
    FROM content c
    LEFT JOIN content_categories cc ON c.content_id = cc.content_id
    LEFT JOIN categories cat ON cc.category_id = cat.category_id
    LEFT JOIN episodes e ON c.content_id = e.content_id
    WHERE c.content_id = $content_id
    GROUP BY c.content_id
";
$content_result = $conn->query($content_query);
$content = $content_result->fetch_assoc();

// Get episodes if it's a TV show
$episodes = [];
if ($content['content_type'] === 'tv_show') {
    $episodes_query = "SELECT * FROM episodes WHERE content_id = $content_id ORDER BY episode_number";
    $episodes_result = $conn->query($episodes_query);
    while ($episode = $episodes_result->fetch_assoc()) {
        $episodes[] = $episode;
    }
}

// Get feedback statistics
$feedback_stats_query = "
    SELECT 
        COUNT(*) as total_reviews,
        AVG(rating) as avg_rating,
        SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
        SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
        SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
        SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
        SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
    FROM feedback 
    WHERE content_id = $content_id
";
$feedback_stats = $conn->query($feedback_stats_query)->fetch_assoc();

$conn->close();
?>

<div class="row">
    <div class="col-md-4">
        <!-- Media Section -->
<!-- Media Section -->
<div class="card mb-4">
    <div class="card-header">
        <h6 class="card-title mb-0">Media</h6>
    </div>
    <div class="card-body">
        <!-- Thumbnail -->
        <div class="mb-4">
            <div class="thumbnail-container">
                <img src="<?php echo $content['thumbnail_url'] ?: 'https://via.placeholder.com/120x180?text=No+Thumbnail'; ?>" 
                     alt="<?php echo $content['title']; ?>" class="img-fluid">
            </div>
            <div class="text-center mt-2">
                <small class="text-muted">Thumbnail (120x180)</small>
            </div>
        </div>
        
        <!-- Banner -->
        <?php if ($content['banner_url']): ?>
        <div class="mt-4">
            <div class="banner-container">
                <img src="<?php echo $content['banner_url']; ?>" 
                     alt="<?php echo $content['title']; ?> Banner" class="img-fluid">
            </div>
            <div class="text-center mt-2">
                <small class="text-muted">Banner</small>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>        <!-- Status Badges -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Status</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <span class="badge <?php echo $content['content_type'] === 'movie' ? 'badge-movie' : 'badge-tv-show'; ?> p-2">
                        <i class="fas <?php echo $content['content_type'] === 'movie' ? 'fa-film' : 'fa-tv'; ?> me-1"></i>
                        <?php echo $content['content_type'] === 'movie' ? 'Movie' : 'TV Show'; ?>
                    </span>
                    
                    <span class="badge <?php echo $content['featured'] ? 'badge-featured' : 'bg-secondary'; ?> p-2">
                        <i class="fas fa-star me-1"></i>
                        <?php echo $content['featured'] ? 'Featured' : 'Not Featured'; ?>
                    </span>
                    
                    <span class="badge <?php echo $content['is_premium'] ? 'bg-warning' : 'bg-secondary'; ?> p-2">
                        <i class="fas fa-crown me-1"></i>
                        <?php echo $content['is_premium'] ? 'Premium Content' : 'Free Content'; ?>
                    </span>
                    
                    <?php if ($content['is_scheduled']): ?>
                    <span class="badge bg-info p-2">
                        <i class="fas fa-clock me-1"></i>
                        Scheduled: <?php echo date('M j, Y g:i A', strtotime($content['schedule_date'])); ?>
                    </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <!-- Basic Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">Basic Information</h6>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-8">
                        <label class="form-label">Title</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($content['title']); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Release Year</label>
                        <input type="text" class="form-control" value="<?php echo $content['release_year']; ?>" readonly>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" rows="3" readonly><?php echo htmlspecialchars($content['description'] ?: 'No description available'); ?></textarea>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Categories</label>
                    <div class="d-flex flex-wrap gap-2">
                        <?php 
                        $categories = $content['category_names'] ? explode(',', $content['category_names']) : [];
                        foreach ($categories as $category): 
                            if (trim($category)): ?>
                            <span class="badge bg-primary"><?php echo htmlspecialchars(trim($category)); ?></span>
                        <?php endif; endforeach; 
                        if (empty($categories) || !$content['category_names']): ?>
                            <span class="text-muted">No categories assigned</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Video Content -->
        <?php if ($content['content_type'] === 'movie' && $content['video_path']): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">Video Preview</h6>
            </div>
            <div class="card-body">
                <video class="video-js vjs-default-skin" controls preload="auto" width="100%" height="300" data-setup="{}">
                    <source src="<?php echo $content['video_path']; ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
                <div class="mt-2">
                    <small class="text-muted">Video Path: <?php echo $content['video_path']; ?></small>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Episodes Section -->
        <?php if ($content['content_type'] === 'tv_show'): ?>
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0">Episodes (<?php echo count($episodes); ?>)</h6>
                <span class="badge bg-primary"><?php echo $content['episode_count']; ?> total</span>
            </div>
            <div class="card-body">
                <div class="episode-list">
                    <?php if (count($episodes) > 0): ?>
                        <?php foreach ($episodes as $episode): ?>
                        <div class="episode-item">
                            <div class="episode-info">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong>Episode <?php echo $episode['episode_number']; ?>: <?php echo htmlspecialchars($episode['title']); ?></strong>
                                        <div class="text-muted small mt-1">
                                            <span class="me-3">
                                                <i class="fas fa-clock me-1"></i><?php echo $episode['duration_minutes']; ?> min
                                            </span>
                                            <?php if ($episode['release_date']): ?>
                                            <span class="me-3">
                                                <i class="fas fa-calendar me-1"></i><?php echo date('M j, Y', strtotime($episode['release_date'])); ?>
                                            </span>
                                            <?php endif; ?>
                                            <?php if ($episode['rating']): ?>
                                            <span>
                                                <i class="fas fa-star text-warning me-1"></i><?php echo $episode['rating']; ?>/10
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($episode['description']): ?>
                                        <div class="episode-description text-muted mt-2">
                                            <?php echo htmlspecialchars($episode['description']); ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($episode['video_path']): ?>
                                    <div class="episode-actions">
                                        <button class="btn btn-sm btn-outline-primary view-episode" 
                                                data-video="<?php echo $episode['video_path']; ?>"
                                                data-title="Episode <?php echo $episode['episode_number']; ?>: <?php echo htmlspecialchars($episode['title']); ?>">
                                            <i class="fas fa-play me-1"></i>Play
                                        </button>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-tv fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No episodes available for this TV show.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Statistics & Metadata -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Content Statistics</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Total Views</label>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-eye text-primary me-2"></i>
                                <span class="fs-5 fw-bold"><?php echo number_format($content['views']); ?></span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Feedback Rating</label>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-star text-warning me-2"></i>
                                <span class="fs-5 fw-bold">
                                    <?php echo $feedback_stats['avg_rating'] ? number_format($feedback_stats['avg_rating'], 1) . '/5' : 'No ratings'; ?>
                                </span>
                                <small class="text-muted ms-2">(<?php echo $feedback_stats['total_reviews']; ?> reviews)</small>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Created Date</label>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-calendar text-success me-2"></i>
                                <span><?php echo date('M j, Y g:i A', strtotime($content['created_at'])); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Publishing Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Publish Status</label>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-globe me-2 text-<?php echo $content['is_scheduled'] ? 'info' : 'success'; ?>"></i>
                                <span>
                                    <?php echo $content['is_scheduled'] ? 'Scheduled' : 'Published'; ?>
                                </span>
                            </div>
                        </div>
                        <?php if ($content['is_scheduled']): ?>
                        <div class="mb-3">
                            <label class="form-label">Scheduled Date</label>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-clock me-2 text-info"></i>
                                <span><?php echo date('M j, Y g:i A', strtotime($content['schedule_date'])); ?></span>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="mb-3">
                            <label class="form-label">Content ID</label>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-hashtag me-2 text-muted"></i>
                                <span class="font-monospace">#<?php echo $content['content_id']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<style> 
.episode-item {
    padding: 15px;
    border-bottom: 1px solid #e9ecef;
    transition: background-color 0.2s;
}

.episode-item:hover {
    background-color: #f8f9fa;
}

.episode-item:last-child {
    border-bottom: none;
}

.badge-movie {
    background-color: #6f42c1;
}

.badge-tv-show {
    background-color: #20c997;
}

.badge-featured {
    background-color: #ffc107;
    color: #212529;
}

/* Thumbnail specific styling for vertical rectangle format */
.thumbnail-container {
    width: 120px;
    height: 180px;
    margin: 0 auto;
    display: flex;
    justify-content: center;
    align-items: center;
}

.thumbnail-container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

/* Banner specific styling */
.banner-container {
    width: 100%;
    height: 200px;
    margin: 0 auto;
    display: flex;
    justify-content: center;
    align-items: center;
}

.banner-container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

/* Text alignment for media labels */
.media-label {
    text-align: center;
    margin-top: 8px;
    color: #6c757d;
    font-size: 0.875rem;
}
</style>
<script>
// Episode video player
$(document).on('click', '.view-episode', function() {
    const videoPath = $(this).data('video');
    const episodeTitle = $(this).data('title');
    
    // Create a modal to show the episode video
    const videoModal = `
        <div class="modal fade" id="episodeVideoModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">${episodeTitle}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-0">
                        <video class="video-js vjs-default-skin vjs-big-play-centered" 
                               controls preload="auto" 
                               width="100%" height="400" 
                               data-setup='{"fluid": true}'>
                            <source src="${videoPath}" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    $('body').append(videoModal);
    const modal = new bootstrap.Modal(document.getElementById('episodeVideoModal'));
    modal.show();
    
    $('#episodeVideoModal').on('hidden.bs.modal', function() {
        $(this).remove();
    });
});

// Initialize Video.js players when modal opens
$(document).on('shown.bs.modal', '#episodeVideoModal', function() {
    if (typeof videojs !== 'undefined') {
        videojs(document.querySelector('#episodeVideoModal video'));
    }
});
</script>
