<?php
// modals.php - Modal forms for login, register, and admin

// Database configuration
require_once 'db.php';
?>

<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: rgba(28, 15, 36, 0.95); backdrop-filter: blur(10px); border: 1px solid rgba(177, 59, 255, 0.3);">
            <div class="modal-header border-secondary">
                <h5 class="modal-title text-white" id="loginModalLabel">
                    <i class="bi bi-person-circle me-2"></i>Login to Streamify
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="loginForm" method="POST" action="login_handler.php">
                    <div class="mb-3">
                        <label for="modalUsernameEmail" class="form-label text-white">Username or Email</label>
                        <input 
                            type="text" 
                            class="form-control bg-dark text-white border-secondary" 
                            id="modalUsernameEmail" 
                            name="username_email"
                            placeholder="Enter username or email" 
                            required 
                        />
                    </div>
                    
                    <div class="mb-3 password-container">
                        <label for="modalPassword" class="form-label text-white">Password</label>
                        <div class="position-relative">
                            <input 
                                type="password" 
                                class="form-control bg-dark text-white border-secondary" 
                                id="modalPassword" 
                                name="password"
                                placeholder="Enter password" 
                                required 
                            />
                            <button type="button" class="password-toggle position-absolute end-0 top-50 translate-middle-y me-3 bg-transparent border-0 text-purple" id="modalTogglePassword">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-streamify">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Login
                        </button>
                    </div>
                </form>
                
                <div class="text-center mt-3">
                    <p class="text-white mb-2">Don't have an account? 
                        <a href="#" class="text-purple" data-bs-toggle="modal" data-bs-target="#registerModal" data-bs-dismiss="modal">Register here</a>
                    </p>
                    <p class="text-white mb-0">
                        <a href="#" class="text-purple" data-bs-toggle="modal" data-bs-target="#adminModal" data-bs-dismiss="modal">Admin Login</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Register Modal -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: rgba(28, 15, 36, 0.95); backdrop-filter: blur(10px); border: 1px solid rgba(177, 59, 255, 0.3);">
            <div class="modal-header border-secondary">
                <h5 class="modal-title text-white" id="registerModalLabel">
                    <i class="bi bi-person-plus me-2"></i>Create Your Account
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="registerForm" method="POST" action="register_handler.php" enctype="multipart/form-data">
                    <!-- Profile Picture Section -->
                    <div class="profile-pic-container text-center mb-4">
                        <img id="modalProfilePreview" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23b13bff'%3E%3Cpath d='M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z'/%3E%3C/svg%3E" 
                            alt="Profile Picture" class="profile-pic-preview" />
                        <label for="modalProfilePic" class="profile-pic-label mt-2">
                            <i class="bi bi-camera"></i> Choose Photo
                        </label>
                        <input type="file" id="modalProfilePic" name="profile_pic" class="profile-pic-input" accept="image/*" />
                        <div class="profile-pic-text">Click to upload profile picture (optional)</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="modalUsername" class="form-label text-white">Username</label>
                        <input 
                            type="text" 
                            class="form-control bg-dark text-white border-secondary" 
                            id="modalUsername" 
                            name="username"
                            placeholder="Enter username" 
                            required 
                        />
                    </div>
                    
                    <div class="mb-3">
                        <label for="modalEmail" class="form-label text-white">Email Address</label>
                        <input 
                            type="email" 
                            class="form-control bg-dark text-white border-secondary" 
                            id="modalEmail" 
                            name="email"
                            placeholder="Enter email" 
                            required 
                        />
                    </div>
                    <div class="mb-3">
    <label for="mobile_no" class="form-label">Mobile Number</label>
    <input type="tel" class="form-control bg-dark text-white border-secondary" id="mobile_no" name="mobile_no" 
           placeholder="Enter your mobile number">
</div>
                    <div class="mb-3 password-container">
                        <label for="modalRegisterPassword" class="form-label text-white">Password</label>
                        <div class="position-relative">
                            <input 
                                type="password" 
                                class="form-control bg-dark text-white border-secondary" 
                                id="modalRegisterPassword" 
                                name="password"
                                placeholder="Enter password" 
                                required 
                            />
                            <button type="button" class="password-toggle position-absolute end-0 top-50 translate-middle-y me-3 bg-transparent border-0 text-purple" id="modalToggleRegisterPassword">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-4 password-container">
                        <label for="modalConfirmPassword" class="form-label text-white">Confirm Password</label>
                        <div class="position-relative">
                            <input 
                                type="password" 
                                class="form-control bg-dark text-white border-secondary" 
                                id="modalConfirmPassword" 
                                name="confirm_password"
                                placeholder="Confirm password" 
                                required 
                            />
                            <button type="button" class="password-toggle position-absolute end-0 top-50 translate-middle-y me-3 bg-transparent border-0 text-purple" id="modalToggleConfirmPassword">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-streamify">
                            <i class="bi bi-person-plus me-2"></i>Register
                        </button>
                    </div>
                </form>
                
                <div class="text-center mt-3">
                    <p class="text-white mb-0">Already have an account? 
                        <a href="#" class="text-purple" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">Login here</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Admin Login Modal -->
<div class="modal fade" id="adminModal" tabindex="-1" aria-labelledby="adminModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: rgba(28, 15, 36, 0.95); backdrop-filter: blur(10px); border: 1px solid rgba(255, 193, 7, 0.3);">
            <div class="modal-header border-warning">
                <h5 class="modal-title text-warning" id="adminModalLabel">
                    <i class="bi bi-shield-lock me-2"></i>Admin Login
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="adminForm" method="POST" action="admin_login_handler.php">
                    <div class="mb-3">
                        <label for="adminUsername" class="form-label text-white">Admin Username</label>
                        <input 
                            type="text" 
                            class="form-control bg-dark text-white border-warning" 
                            id="adminUsername" 
                            name="admin_username"
                            placeholder="Enter admin username" 
                            required 
                        />
                        <div class="form-text text-warning">
                            <i class="bi bi-info-circle me-1"></i>Must have admin role in system
                        </div>
                    </div>
                    
                    <div class="mb-4 password-container">
                        <label for="adminPassword" class="form-label text-white">Admin Password</label>
                        <div class="position-relative">
                            <input 
                                type="password" 
                                class="form-control bg-dark text-white border-warning" 
                                id="adminPassword" 
                                name="admin_password"
                                placeholder="Enter admin password" 
                                required 
                            />
                            <button type="button" class="password-toggle position-absolute end-0 top-50 translate-middle-y me-3 bg-transparent border-0 text-warning" id="adminTogglePassword">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-warning fw-bold">
                            <i class="bi bi-shield-check me-2"></i>Admin Login
                        </button>
                    </div>
                </form>
                
                <div class="text-center mt-3">
                    <p class="text-white mb-0">
                        <a href="#" class="text-warning" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">
                            <i class="bi bi-arrow-left me-1"></i>Back to User Login
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Response Toast -->
<div class="toast align-items-center text-white bg-success border-0 position-fixed top-0 end-0 m-3" 
     style="z-index: 9999;" id="responseToast" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
        <div class="toast-body" id="toastMessage"></div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
</div>

<style>
    .text-purple { color: #b13bff !important; }
    .text-purple:hover { color: #9d00ff !important; }
    
    .btn-streamify {
        background: linear-gradient(135deg, #b13bff, #00ccff);
        border: none;
        color: white;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-streamify:hover {
        background: linear-gradient(135deg, #9d00ff, #00a8e6);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(177, 59, 255, 0.3);
    }
    
    .profile-pic-preview {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        border: 3px solid #b13bff;
        object-fit: cover;
        margin: 0 auto;
        display: block;
        background-color: #22102c;
        transition: all 0.3s ease;
    }
    
    .profile-pic-preview:hover {
        border-color: #9d00ff;
        transform: scale(1.05);
    }
    
    .profile-pic-input {
        display: none;
    }
    
    .profile-pic-label {
        background-color: #b13bff;
        color: white;
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-block;
        font-size: 0.8rem;
    }
    
    .profile-pic-label:hover {
        background-color: #9d00ff;
        transform: scale(1.05);
    }
    
    .profile-pic-text {
        font-size: 0.7rem;
        color: rgba(255, 255, 255, 0.7);
        margin-top: 0.5rem;
    }
    
    .password-toggle {
        color: #b13bff;
        background: transparent;
        border: none;
        outline: none;
    }
    
    .form-control:focus {
        border-color: #b13bff;
        box-shadow: 0 0 0 0.2rem rgba(177, 59, 255, 0.25);
    }
    
    .border-warning {
        border-color: #ffc107 !important;
    }

    /* Error styling */
    .is-invalid {
        border-color: #dc3545 !important;
    }
    .invalid-feedback {
        display: block;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 0.875em;
        color: #dc3545;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password toggle functionality
    function setupPasswordToggle(toggleId, inputId) {
        const toggle = document.querySelector(toggleId);
        const input = document.querySelector(inputId);
        
        if (toggle && input) {
            toggle.addEventListener('click', function() {
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                const icon = this.querySelector('i');
                icon.classList.toggle('bi-eye');
                icon.classList.toggle('bi-eye-slash');
            });
        }
    }
    
    // Setup all password toggles
    setupPasswordToggle('#modalTogglePassword', '#modalPassword');
    setupPasswordToggle('#modalToggleRegisterPassword', '#modalRegisterPassword');
    setupPasswordToggle('#modalToggleConfirmPassword', '#modalConfirmPassword');
    setupPasswordToggle('#adminTogglePassword', '#adminPassword');
    
    // Profile picture preview
    const profilePicInput = document.getElementById('modalProfilePic');
    const profilePreview = document.getElementById('modalProfilePreview');
    
    if (profilePicInput && profilePreview) {
        profilePicInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    profilePreview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
// Form submissions with AJAX - UPDATED VERSION
function setupFormSubmit(formId, successRedirect = null) {
    const form = document.getElementById(formId);
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Show loading state
            submitBtn.innerHTML = '<i class="bi bi-arrow-repeat spinner-border spinner-border-sm me-2"></i>Processing...';
            submitBtn.disabled = true;
            
            // Clear previous errors
            clearFormErrors(formId);
            
            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                // First check if we got a response
                if (!response) {
                    throw new Error('No response from server');
                }
                
                // Check if response is OK (status 200-299)
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                // Try to parse JSON
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                
                if (data && data.success) {
                    showToast(data.message, 'success');
                    
                    if (successRedirect) {
                        setTimeout(() => {
                            window.location.href = successRedirect;
                        }, 1500);
                    } else {
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    }
                } else {
                    // Handle case where data exists but success is false
                    const errorMessage = data && data.message ? data.message : 'Registration failed. Please try again.';
                    showToast(errorMessage, 'danger');
                    
                    // Display form errors if any
                    if (data && data.errors) {
                        displayFormErrors(formId, data.errors);
                    }
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                
                // More specific error messages
                let errorMsg = 'An error occurred. Please try again.';
                if (error.message.includes('JSON')) {
                    errorMsg = 'Invalid response from server. Please try again.';
                } else if (error.message.includes('HTTP')) {
                    errorMsg = 'Server error. Please try again later.';
                } else if (error.message.includes('Network')) {
                    errorMsg = 'Network error. Please check your connection.';
                }
                
                showToast(errorMsg, 'danger');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    }
}
    // Function to display form errors
    function displayFormErrors(formId, errors) {
        const form = document.getElementById(formId);
        
        for (const field in errors) {
            const input = form.querySelector(`[name="${field}"]`);
            if (input) {
                input.classList.add('is-invalid');
                // Create or update error message
                let errorDiv = input.parentNode.querySelector('.invalid-feedback');
                if (!errorDiv) {
                    errorDiv = document.createElement('div');
                    errorDiv.className = 'invalid-feedback';
                    input.parentNode.appendChild(errorDiv);
                }
                errorDiv.textContent = errors[field];
            }
        }
    }

    // Function to clear form errors
    function clearFormErrors(formId) {
        const form = document.getElementById(formId);
        const invalidInputs = form.querySelectorAll('.is-invalid');
        invalidInputs.forEach(input => {
            input.classList.remove('is-invalid');
        });
        
        const errorMessages = form.querySelectorAll('.invalid-feedback');
        errorMessages.forEach(msg => {
            msg.remove();
        });
    }
    
    // Setup form submissions
    setupFormSubmit('loginForm');
    setupFormSubmit('registerForm');
    setupFormSubmit('adminForm', 'admin/index.php');
    
  
// Toast notification function - SIMPLIFIED VERSION
function showToast(message, type) {
    const toast = document.getElementById('responseToast');
    const toastMessage = document.getElementById('toastMessage');
    
    if (!toast || !toastMessage) {
        console.error('Toast elements not found');
        alert(message); // Fallback to alert if toast not found
        return;
    }
    
    // Set message
    toastMessage.textContent = message;
    
    // Set background color based on type
    if (type === 'success') {
        toast.style.background = 'linear-gradient(135deg, #198754, #20c997)';
    } else if (type === 'danger') {
        toast.style.background = 'linear-gradient(135deg, #dc3545, #e83e8c)';
    } else {
        toast.style.background = 'linear-gradient(135deg, #b13bff, #00ccff)';
    }
    
    // Use Bootstrap's toast if available, otherwise simple show/hide
    if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
        const toastInstance = new bootstrap.Toast(toast);
        toastInstance.show();
    } else {
        // Fallback: simple show/hide
        toast.style.display = 'block';
        setTimeout(() => {
            toast.style.display = 'none';
        }, 3000);
    }
    
    console.log('Toast shown:', message);
}
    
    // Clear form when modal is hidden
    const modals = ['loginModal', 'registerModal', 'adminModal'];
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.addEventListener('hidden.bs.modal', function() {
                const form = this.querySelector('form');
                if (form) {
                    form.reset();
                    clearFormErrors(form.id);
                    // Reset profile preview
                    if (modalId === 'registerModal') {
                        const preview = document.getElementById('modalProfilePreview');
                        if (preview) {
                            preview.src = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23b13bff'%3E%3Cpath d='M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z'/%3E%3C/svg%3E";
                        }
                    }
                }
            });
        }
    });
});
</script>
