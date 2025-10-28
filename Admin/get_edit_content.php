<?php
$conn = new mysqli('localhost', 'root', '', 'Streamify');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

$content_id = $conn->real_escape_string($_GET['content_id']);

// Get content details
$content_query = "SELECT * FROM content WHERE content_id = $content_id";
$content_result = $conn->query($content_query);
$content = $content_result->fetch_assoc();

// Get content categories
$content_categories_query = "SELECT category_id FROM content_categories WHERE content_id = $content_id";
$content_categories_result = $conn->query($content_categories_query);
$content_category_ids = [];
while ($row = $content_categories_result->fetch_assoc()) {
    $content_category_ids[] = $row['category_id'];
}

// Get all categories
$categories_query = "SELECT * FROM categories ORDER BY name";
$categories_result = $conn->query($categories_query);

// Get episodes if it's a TV show
$episodes = [];
if ($content['content_type'] === 'tv_show') {
    $episodes_query = "SELECT * FROM episodes WHERE content_id = $content_id ORDER BY episode_number";
    $episodes_result = $conn->query($episodes_query);
    while ($episode = $episodes_result->fetch_assoc()) {
        $episodes[] = $episode;
    }
}

$conn->close();
?>

<div class="step-progress mb-4">
    <div class="step active" id="editStep1">
        <div class="step-circle">1</div>
        <div class="step-label">Basic Info</div>
    </div>
    <div class="step" id="editStep2">
        <div class="step-circle">2</div>
        <div class="step-label">Media & Type</div>
    </div>
    <div class="step" id="editStep3">
        <div class="step-circle">3</div>
        <div class="step-label">Review</div>
    </div>
</div>

<form id="editContentForm" method="POST" action="update_content.php" enctype="multipart/form-data">
    <input type="hidden" name="content_id" value="<?php echo $content['content_id']; ?>">
    <input type="hidden" name="action" value="update_content">
    
    <!-- Step 1: Basic Information -->
    <div class="step-content" id="editStep1-content">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="edit_title" class="form-label">Title *</label>
                    <input type="text" class="form-control" id="edit_title" name="title" 
                           value="<?php echo htmlspecialchars($content['title']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="edit_description" class="form-label">Description</label>
                    <textarea class="form-control" id="edit_description" name="description" rows="3"><?php echo htmlspecialchars($content['description']); ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="edit_categories" class="form-label">Categories</label>
                    <select class="form-select" id="edit_categories" name="categories[]" multiple>
                        <?php 
                        // Reset categories result pointer
                        $categories_result->data_seek(0);
                        while($category = $categories_result->fetch_assoc()): ?>
                            <option value="<?php echo $category['category_id']; ?>" 
                                <?php echo in_array($category['category_id'], $content_category_ids) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <small class="form-text text-muted">Hold Ctrl to select multiple categories</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="edit_release_year" class="form-label">Release Year *</label>
                    <select class="form-select" id="edit_release_year" name="release_year" required>
                        <option value="">Select Year</option>
                        <?php
                        $current_year = date('Y');
                        $release_year = $content['release_year'];
                        for ($year = $current_year; $year >= 1900; $year--) {
                            $selected = ($year == $release_year) ? 'selected' : '';
                            echo "<option value='$year' $selected>$year</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="edit_duration" class="form-label">Duration (minutes)</label>
                    <input type="number" class="form-control" id="edit_duration" name="duration" 
                           value="<?php echo $content['duration']; ?>" min="1">
                </div>
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="edit_featured" name="featured" value="1" 
                               <?php echo $content['featured'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="edit_featured">
                            Featured Content
                        </label>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="edit_is_premium" name="is_premium" value="1" 
                               <?php echo $content['is_premium'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="edit_is_premium">
                            Premium Content (Only for subscribed users)
                        </label>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Publish Status *</label>
                    <div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="publish_option" id="edit_publish_now" 
                                   value="now" <?php echo !$content['is_scheduled'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="edit_publish_now">
                                Published Now
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="publish_option" id="edit_schedule_later" 
                                   value="schedule" <?php echo $content['is_scheduled'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="edit_schedule_later">
                                Schedule for Later
                            </label>
                        </div>
                    </div>
                </div>
                <div class="mb-3" id="edit_schedule_date_container" 
                     style="<?php echo $content['is_scheduled'] ? '' : 'display: none;'; ?>">
                    <label for="edit_schedule_date" class="form-label">Schedule Date & Time</label>
                    <input type="datetime-local" class="form-control" id="edit_schedule_date" name="schedule_date"
                           value="<?php echo $content['release_date'] ? date('Y-m-d\TH:i', strtotime($content['release_date'])) : ''; ?>">
                </div>
                <div class="mb-3">
                    <label for="edit_rating" class="form-label">Rating (0-10)</label>
                    <input type="number" class="form-control" id="edit_rating" name="rating" 
                           value="<?php echo $content['rating']; ?>" min="0" max="10" step="0.1">
                </div>
                <div class="mb-3">
</div>
            </div>
        </div>
    </div>

    <!-- Step 2: Media & Type -->
    <div class="step-content" id="editStep2-content" style="display: none;">
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="media-section">
                    <h5>Content Type</h5>
                    <div class="mb-3">
                        <label for="edit_content_type" class="form-label">Content Type *</label>
                        <select class="form-select" id="edit_content_type" name="content_type" required>
                            <option value="movie" <?php echo $content['content_type'] === 'movie' ? 'selected' : ''; ?>>Movie</option>
                            <option value="tv_show" <?php echo $content['content_type'] === 'tv_show' ? 'selected' : ''; ?>>TV Show</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="media-section">
                    <h5>Images</h5>
                    <div class="mb-3">
                        <label for="edit_thumbnail" class="form-label">Thumbnail</label>
                        <input type="file" class="form-control" id="edit_thumbnail" name="thumbnail" accept="image/*">
                        <?php if ($content['thumbnail_url']): ?>
                        <div class="mt-2">
                            <img src="<?php echo $content['thumbnail_url']; ?>" alt="Current Thumbnail" 
                                 class="file-preview" id="current_edit_thumbnail">
                            <small class="form-text text-muted">Current thumbnail</small>
                        </div>
                        <?php endif; ?>
                        <div class="mt-2">
                            <img id="edit_thumbnail_preview" src="" alt="New Thumbnail Preview" 
                                 class="file-preview" style="display: none; max-height: 150px;">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_banner" class="form-label">Banner</label>
                        <input type="file" class="form-control" id="edit_banner" name="banner" accept="image/*">
                        <?php if ($content['banner_url']): ?>
                        <div class="mt-2">
                            <img src="<?php echo $content['banner_url']; ?>" alt="Current Banner" 
                                 class="file-preview" id="current_edit_banner">
                            <small class="form-text text-muted">Current banner</small>
                        </div>
                        <?php endif; ?>
                        <div class="mt-2">
                            <img id="edit_banner_preview" src="" alt="New Banner Preview" 
                                 class="file-preview" style="display: none; max-height: 150px;">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="media-section">
                    <h5 id="edit_video_section_title">
                        <?php echo $content['content_type'] === 'movie' ? 'Video File' : 'Episodes'; ?>
                    </h5>
                    
                    <!-- Video for Movies -->
                    <div class="mb-3" id="edit_video_container" 
                         style="<?php echo $content['content_type'] === 'movie' ? '' : 'display: none;'; ?>">
                        <label for="edit_video" class="form-label">Video File</label>
                        <input type="file" class="form-control" id="edit_video" name="video" accept="video/*">
                        <?php if ($content['video_path']): ?>
                        <div class="mt-2">
                            <video controls class="file-preview" id="current_edit_video">
                                <source src="<?php echo $content['video_path']; ?>" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                            <small class="form-text text-muted">Current video</small>
                        </div>
                        <?php endif; ?>
                        <div class="mt-2">
                            <video id="edit_video_preview" controls class="file-preview" style="display: none; max-height: 150px;">
                                Your browser does not support the video tag.
                            </video>
                        </div>
                    </div>
                    
                    <!-- Episodes for TV Shows -->
                    <div id="edit_episodes_container" 
                         style="<?php echo $content['content_type'] === 'tv_show' ? '' : 'display: none;'; ?>">
                        <label class="form-label">Episodes</label>
                        <div id="edit_episodes_list" class="episode-list">
                            <?php foreach ($episodes as $episode): ?>
                            <div class="episode-item" data-episode-id="<?php echo $episode['episode_id']; ?>">
                                <div class="episode-info">
                                    <strong>Episode <?php echo $episode['episode_number']; ?></strong>
                                    <div class="row mt-2">
                                        <div class="col-md-4">
                                            <input type="text" class="form-control form-control-sm" 
                                                   name="episode_titles[<?php echo $episode['episode_id']; ?>]" 
                                                   value="<?php echo htmlspecialchars($episode['title']); ?>" 
                                                   placeholder="Episode Title" required>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" class="form-control form-control-sm" 
                                                   name="episode_durations[<?php echo $episode['episode_id']; ?>]" 
                                                   value="<?php echo $episode['duration_minutes']; ?>" 
                                                   placeholder="Duration (min)" min="1" required>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="date" class="form-control form-control-sm" 
                                                   name="episode_dates[<?php echo $episode['episode_id']; ?>]" 
                                                   value="<?php echo $episode['release_date']; ?>">
                                        </div>
                                        <div class="col-md-2">
                                            <input type="file" class="form-control form-control-sm" 
                                                   name="episode_videos[<?php echo $episode['episode_id']; ?>]" 
                                                   accept="video/*">
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-12">
                                            <textarea class="form-control form-control-sm" 
                                                      name="episode_descriptions[<?php echo $episode['episode_id']; ?>]" 
                                                      placeholder="Episode Description" rows="2"><?php echo htmlspecialchars($episode['description']); ?></textarea>
                                        </div>
                                    </div>
                                    <?php if ($episode['video_path']): ?>
                                    <div class="row mt-2">
                                        <div class="col-md-12">
                                            <small class="text-muted">
                                                Current video: <?php echo basename($episode['video_path']); ?>
                                            </small>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="episode-actions">
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-edit-episode">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="addEditEpisodeBtn">
                            <i class="fas fa-plus me-1"></i> Add Episode
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 3: Review -->
    <div class="step-content" id="editStep3-content" style="display: none;">
        <div class="row">
            <div class="col-md-6">
                <h6>Content Details</h6>
                <table class="table table-sm">
                    <tr>
                        <td><strong>Title:</strong></td>
                        <td id="edit_review_title"></td>
                    </tr>
                    <tr>
                        <td><strong>Type:</strong></td>
                        <td id="edit_review_content_type"></td>
                    </tr>
                    <tr>
                        <td><strong>Description:</strong></td>
                        <td id="edit_review_description"></td>
                    </tr>
                    <tr>
                        <td><strong>Categories:</strong></td>
                        <td id="edit_review_categories"></td>
                    </tr>
                    <tr>
                        <td><strong>Release Year:</strong></td>
                        <td id="edit_review_release_year"></td>
                    </tr>
                    <tr>
                        <td><strong>Duration:</strong></td>
                        <td id="edit_review_duration"></td>
                    </tr>
                    <tr>
                        <td><strong>Featured:</strong></td>
                        <td id="edit_review_featured"></td>
                    </tr>
                    <tr>
                        <td><strong>Premium:</strong></td>
                        <td id="edit_review_premium"></td>
                    </tr>
                    <tr>
                        <td><strong>Publish Status:</strong></td>
                        <td id="edit_review_publish_option"></td>
                    </tr>
                    <tr>
                        <td><strong>Schedule Date:</strong></td>
                        <td id="edit_review_schedule_date"></td>
                    </tr>
                    <tr>
                        <td><strong>Rating:</strong></td>
                        <td id="edit_review_rating"></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6>Media Preview</h6>
                <div class="content-preview mb-3">
                    <img id="edit_review_thumbnail" src="<?php echo $content['thumbnail_url']; ?>" 
                         alt="Thumbnail Preview" style="width: 100%; max-height: 150px; object-fit: cover;">
                </div>
                <?php if ($content['banner_url']): ?>
                <div class="content-preview">
                    <img id="edit_review_banner" src="<?php echo $content['banner_url']; ?>" 
                         alt="Banner Preview" style="width: 100%; max-height: 150px; object-fit: cover;">
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div id="edit_review_episodes_section" style="<?php echo $content['content_type'] === 'tv_show' ? '' : 'display: none;'; ?>">
            <h6>Episodes</h6>
            <div id="edit_review_episodes" class="episode-list"></div>
        </div>
    </div>
</form>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" id="editPrevStepBtn" style="display: none;">Previous</button>
    <button type="button" class="btn btn-primary" id="editNextStepBtn">Next</button>
    <button type="submit" form="editContentForm" class="btn btn-success" id="editSubmitBtn" style="display: none;">Update Content</button>
</div>

<script>
let editCurrentStep = 1;

$(document).ready(function() {
    // Edit modal step navigation
    $('#editNextStepBtn').on('click', function() {
        if (validateEditStep(editCurrentStep)) {
            editCurrentStep++;
            updateEditStepProgress();
        }
    });
    
    $('#editPrevStepBtn').on('click', function() {
        editCurrentStep--;
        updateEditStepProgress();
    });
    
    // Content type change handler
    $('#edit_content_type').on('change', function() {
        updateEditMediaVisibility();
    });
    
    // Publish option change handler
    $('input[name="publish_option"]').on('change', function() {
        if ($('#edit_schedule_later').is(':checked')) {
            $('#edit_schedule_date_container').show();
        } else {
            $('#edit_schedule_date_container').hide();
        }
    });
    
    // File preview handlers
    $('#edit_thumbnail').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#edit_thumbnail_preview').attr('src', e.target.result).show();
                $('#current_edit_thumbnail').hide();
            };
            reader.readAsDataURL(file);
        }
    });
    
    $('#edit_banner').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#edit_banner_preview').attr('src', e.target.result).show();
                $('#current_edit_banner').hide();
            };
            reader.readAsDataURL(file);
        }
    });
    
    $('#edit_video').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const url = URL.createObjectURL(file);
            $('#edit_video_preview').attr('src', url).show();
            $('#current_edit_video').hide();
        }
    });
    
    // Add episode button for edit modal
    $('#addEditEpisodeBtn').on('click', function() {
        const episodeNumber = $('#edit_episodes_list .episode-item').length + 1;
        const episodeItem = `
            <div class="episode-item" data-episode-id="new-${episodeNumber}">
                <div class="episode-info">
                    <strong>Episode ${episodeNumber}</strong>
                    <div class="row mt-2">
                        <div class="col-md-4">
                            <input type="text" class="form-control form-control-sm" placeholder="Episode Title" name="new_episode_titles[]" required>
                        </div>
                        <div class="col-md-3">
                            <input type="number" class="form-control form-control-sm" placeholder="Duration (min)" name="new_episode_durations[]" min="1" required>
                        </div>
                        <div class="col-md-3">
                            <input type="date" class="form-control form-control-sm" name="new_episode_dates[]">
                        </div>
                        <div class="col-md-2">
                            <input type="file" class="form-control form-control-sm" name="new_episode_videos[]" accept="video/*">
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-12">
                            <textarea class="form-control form-control-sm" placeholder="Episode Description" name="new_episode_descriptions[]" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="episode-actions">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-edit-episode">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
        $('#edit_episodes_list').append(episodeItem);
    });
    
    // Remove episode button for edit modal
    $(document).on('click', '.remove-edit-episode', function() {
        $(this).closest('.episode-item').remove();
        // Update episode numbers
        $('#edit_episodes_list .episode-item').each(function(index) {
            $(this).find('strong').text(`Episode ${index + 1}`);
        });
    });

    // Initialize edit modal
    updateEditMediaVisibility();
});

function updateEditMediaVisibility() {
    const contentType = $('#edit_content_type').val();
    if (contentType === 'movie') {
        $('#edit_video_container').show();
        $('#edit_episodes_container').hide();
        $('#edit_video_section_title').text('Video File');
    } else {
        $('#edit_video_container').hide();
        $('#edit_episodes_container').show();
        $('#edit_video_section_title').text('Episodes');
    }
}

function updateEditStepProgress() {
    // Update step indicators
    $('.step').removeClass('active completed');
    
    for (let i = 1; i <= 3; i++) {
        if (i < editCurrentStep) {
            $('#editStep' + i).addClass('completed');
        } else if (i === editCurrentStep) {
            $('#editStep' + i).addClass('active');
        }
    }
    
    // Show/hide step content
    $('.step-content').hide();
    $('#editStep' + editCurrentStep + '-content').show();
    
    // Update buttons
    if (editCurrentStep === 1) {
        $('#editPrevStepBtn').hide();
        $('#editNextStepBtn').show();
        $('#editSubmitBtn').hide();
    } else if (editCurrentStep === 3) {
        $('#editPrevStepBtn').show();
        $('#editNextStepBtn').hide();
        $('#editSubmitBtn').show();
        updateEditReviewStep();
    } else {
        $('#editPrevStepBtn').show();
        $('#editNextStepBtn').show();
        $('#editSubmitBtn').hide();
    }
}

function validateEditStep(step) {
    switch(step) {
        case 1:
            const title = $('#edit_title').val().trim();
            if (!title) {
                alert('Please enter a title.');
                $('#edit_title').focus();
                return false;
            }
            
            const releaseYear = $('#edit_release_year').val();
            if (!releaseYear) {
                alert('Please select a release year.');
                $('#edit_release_year').focus();
                return false;
            }
            
            if ($('#edit_schedule_later').is(':checked') && !$('#edit_schedule_date').val()) {
                alert('Please select a schedule date.');
                $('#edit_schedule_date').focus();
                return false;
            }
            return true;
        case 2:
            // For TV shows, validate all episodes have titles
            const contentType = $('#edit_content_type').val();
            if (contentType === 'tv_show') {
                let allEpisodesValid = true;
                $('#edit_episodes_list input[name^="episode_titles"], #edit_episodes_list input[name^="new_episode_titles"]').each(function() {
                    if (!$(this).val().trim()) {
                        alert('Please fill in all episode titles.');
                        $(this).focus();
                        allEpisodesValid = false;
                        return false; // break the loop
                    }
                });
                
                return allEpisodesValid;
            }
            return true;
        default:
            return true;
    }
}

function updateEditReviewStep() {
    // Update review fields
    $('#edit_review_title').text($('#edit_title').val());
    $('#edit_review_content_type').text($('#edit_content_type').val() === 'movie' ? 'Movie' : 'TV Show');
    $('#edit_review_description').text($('#edit_description').val() || 'No description');
    
    // Get selected category names
    const selectedCategories = [];
    $('#edit_categories option:selected').each(function() {
        selectedCategories.push($(this).text());
    });
    $('#edit_review_categories').text(selectedCategories.join(', ') || 'None');
    
    $('#edit_review_release_year').text($('#edit_release_year').val());
    $('#edit_review_duration').text(($('#edit_duration').val() || '0') + ' minutes');
    $('#edit_review_featured').text($('#edit_featured').is(':checked') ? 'Yes' : 'No');
    $('#edit_review_premium').text($('#edit_is_premium').is(':checked') ? 'Yes' : 'No');
    
    const publishOption = $('input[name="publish_option"]:checked').val();
    $('#edit_review_publish_option').text(publishOption === 'now' ? 'Published Now' : 'Scheduled for Later');
    
    if (publishOption === 'schedule') {
        $('#edit_review_schedule_date').text($('#edit_schedule_date').val() || 'Not set');
    } else {
        $('#edit_review_schedule_date').text('Immediate');
    }
    
    $('#edit_review_rating').text($('#edit_rating').val() || 'Not set');
    // Update media previews
    const newThumbnailPreview = $('#edit_thumbnail_preview').attr('src');
    if (newThumbnailPreview) {
        $('#edit_review_thumbnail').attr('src', newThumbnailPreview);
    }
    
    const newBannerPreview = $('#edit_banner_preview').attr('src');
    if (newBannerPreview) {
        $('#edit_review_banner').attr('src', newBannerPreview);
    }
    
    // Update episodes section
    const contentType = $('#edit_content_type').val();
    if (contentType === 'tv_show') {
        $('#edit_review_episodes_section').show();
        const episodesContainer = $('#edit_review_episodes');
        episodesContainer.empty();
        
        $('#edit_episodes_list .episode-item').each(function(index) {
            const episodeId = $(this).data('episode-id');
            const title = $(this).find('input[name^="episode_titles"], input[name^="new_episode_titles"]').val();
            const duration = $(this).find('input[name^="episode_durations"], input[name^="new_episode_durations"]').val();
            const date = $(this).find('input[name^="episode_dates"], input[name^="new_episode_dates"]').val();
            
            episodesContainer.append(`
                <div class="episode-item">
                    <strong>Episode ${index + 1}:</strong> ${title} (${duration} min)
                    ${date ? `<br><small>Release: ${date}</small>` : ''}
                </div>
            `);
        });
    } else {
        $('#edit_review_episodes_section').hide();
    }
}
// Submit form via AJAX
$(document).on('click', '#editSubmitBtn', function() {
    if (typeof validateEditStep === 'function' && !validateEditStep(editCurrentStep)) {
        return;
    }

    const formData = new FormData($('#editContentForm')[0]);
    const btn = $(this);

    btn.html('<span class="spinner-border spinner-border-sm" role="status"></span> Updating...')
       .prop('disabled', true);

    $.ajax({
        url: 'update_content.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            let result;

            try {
                result = typeof response === 'string' ? JSON.parse(response) : response;
            } catch (e) {
                showAlert('Invalid server response', 'danger');
                btn.html('Update Content').prop('disabled', false);
                return;
            }

            if (result.success) {
                showAlert(result.message, 'success');
                $('#editContentModal').modal('hide');
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert(result.message || 'Update failed', 'danger');
                btn.html('Update Content').prop('disabled', false);
            }
        },
        error: function(xhr, status, error) {
            showAlert('Error updating content: ' + error, 'danger');
            btn.html('Update Content').prop('disabled', false);
        }
    });
});
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
}</script>

