
<div class="col-md-3 col-lg-2 sidebar p-0">
    <div class="p-3">
        <h4 class="text-center mb-4">Streamify Admin</h4>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>" href="index.php">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'manage_users.php' ? 'active' : '' ?>" href="manage_users.php">
                    <i class="fas fa-users"></i> Manage Users
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'manage_content.php' ? 'active' : '' ?>" href="manage_content.php">
                    <i class="fas fa-video"></i> Manage Content
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'feedback.php' ? 'active' : '' ?>" href="feedback.php">
                    <i class="fas fa-star"></i> Feedback
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'subscriptions.php' ? 'active' : '' ?>" href="subscriptions.php">
                    <i class="fas fa-credit-card"></i> Subscriptions
                </a>
            </li>
        </ul>
    </div>
</div>