<?php
session_start();
require_once "db.php";
require_once "notification_manager.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$notificationManager = new NotificationManager($conn);

// Handle actions
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'mark_all_read':
            $notificationManager->markAllAsRead($user_id);
            header("Location: notifications.php");
            exit;
            
        case 'mark_read':
            if (isset($_GET['id'])) {
                $notificationManager->markAsRead($_GET['id'], $user_id);
            }
            header("Location: notifications.php");
            exit;
            
        case 'delete':
            if (isset($_GET['id'])) {
                $notificationManager->deleteNotification($_GET['id'], $user_id);
            }
            header("Location: notifications.php");
            exit;
            
        case 'clear_read':
            $notificationManager->clearReadNotifications($user_id);
            header("Location: notifications.php");
            exit;
    }
}

// Filter notifications by type
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$filter_condition = "";
if ($filter !== 'all') {
    $filter_condition = "AND n.type = ?";
}

// Fetch notifications with filter
$sql = "
    SELECT n.*, c.title as content_title, c.thumbnail_url, c.content_type,
           e.title as episode_title, e.episode_number
    FROM notifications n 
    LEFT JOIN content c ON n.related_id = c.content_id 
    LEFT JOIN episodes e ON n.related_id = e.episode_id
    WHERE n.user_id = ? 
    {$filter_condition}
    ORDER BY n.created_at DESC 
    LIMIT 50
";

$notificationsStmt = $conn->prepare($sql);
if ($filter !== 'all') {
    $notificationsStmt->bind_param("is", $user_id, $filter);
} else {
    $notificationsStmt->bind_param("i", $user_id);
}

$notificationsStmt->execute();
$notifications = $notificationsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$notificationsStmt->close();

// Get unread count
$unreadCount = $notificationManager->getUnreadCount($user_id);

// Get notification counts by type
$typeCountsStmt = $conn->prepare("
    SELECT type, COUNT(*) as count 
    FROM notifications 
    WHERE user_id = ? 
    GROUP BY type
");
$typeCountsStmt->bind_param("i", $user_id);
$typeCountsStmt->execute();
$typeCountsResult = $typeCountsStmt->get_result();
$typeCounts = [];
$totalCount = 0;
while ($row = $typeCountsResult->fetch_assoc()) {
    $typeCounts[$row['type']] = $row['count'];
    $totalCount += $row['count'];
}
$typeCountsStmt->close();

// Notification type labels and icons
$typeInfo = [
    'welcome' => ['label' => 'Welcome', 'icon' => 'bi-emoji-smile', 'color' => 'icon-success'],
    'subscription_success' => ['label' => 'Subscription', 'icon' => 'bi-check-circle', 'color' => 'icon-success'],
    'subscription_ending' => ['label' => 'Subscription', 'icon' => 'bi-exclamation-triangle', 'color' => 'icon-warning'],
    'subscription_expired' => ['label' => 'Subscription', 'icon' => 'bi-x-circle', 'color' => 'icon-danger'],
    'content_available' => ['label' => 'New Content', 'icon' => 'bi-play-circle', 'color' => 'icon-primary'],
    'reminder_set' => ['label' => 'Reminder', 'icon' => 'bi-bell', 'color' => 'icon-info'],
    'new_episode' => ['label' => 'New Episode', 'icon' => 'bi-tv', 'color' => 'icon-primary'],
    'recommendation' => ['label' => 'Recommendation', 'icon' => 'bi-star', 'color' => 'icon-warning'],
    'system' => ['label' => 'System', 'icon' => 'bi-megaphone', 'color' => 'icon-info'],
    'watchlist_reminder' => ['label' => 'Watchlist', 'icon' => 'bi-bookmark', 'color' => 'icon-info'],
    'general' => ['label' => 'General', 'icon' => 'bi-bell', 'color' => 'icon-info']
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Streamify</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --streamify-primary: #b13bff;
            --streamify-secondary: #00ccff;
            --streamify-dark: #1c0f24;
            --streamify-light: #f8f9fa;
            --streamify-text: #e2e2e2;
            --streamify-text-muted: #a0a0a0;
            --streamify-gradient: linear-gradient(135deg, var(--streamify-primary), var(--streamify-secondary));
        }
        
        body {
            background: linear-gradient(135deg, var(--streamify-dark) 0%, #2a1b3d 100%);
            color: var(--streamify-text);
            min-height: 100vh;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        
        .notification-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .notification-card:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--streamify-primary);
            transform: translateY(-2px);
        }
        
        .notification-card.unread {
            border-left: 4px solid var(--streamify-primary);
            background: rgba(177, 59, 255, 0.1);
        }
        
        .notification-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            flex-shrink: 0;
        }
        
        .icon-success {
            background: rgba(40, 167, 69, 0.2);
            color: #28a745;
        }
        
        .icon-info {
            background: rgba(23, 162, 184, 0.2);
            color: #17a2b8;
        }
        
        .icon-warning {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
        }
        
        .icon-primary {
            background: rgba(177, 59, 255, 0.2);
            color: var(--streamify-primary);
        }
        
        .icon-danger {
            background: rgba(220, 53, 69, 0.2);
            color: #dc3545;
        }
        
        .notification-time {
            color: var(--streamify-text-muted);
            font-size: 0.85rem;
        }
        
        .btn-mark-read {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--streamify-text);
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }
        
        .btn-mark-read:hover {
            background: var(--streamify-primary);
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--streamify-text-muted);
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        .filter-badge {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--streamify-text);
            padding: 0.5rem 1rem;
            border-radius: 25px;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
            margin: 0.25rem;
        }
        
        .filter-badge:hover, .filter-badge.active {
            background: var(--streamify-primary);
            color: white;
            border-color: var(--streamify-primary);
        }
        
        .notification-actions {
            position: absolute;
            top: 1rem;
            right: 1rem;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .notification-card:hover .notification-actions {
            opacity: 1;
        }
        
        .btn-notification-action {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: var(--streamify-text);
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-left: 0.25rem;
            transition: all 0.3s ease;
        }
        
        .btn-notification-action:hover {
            background: var(--streamify-primary);
            color: white;
        }
        
        .notification-type-badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.1);
            color: var(--streamify-text-muted);
        }
    </style>
</head>
<body class="pt-5">
    <!-- Header Section -->
    <?php include "header.php"; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0">
                        Notifications 
                        <?php if ($unreadCount > 0): ?>
                            <span class="badge bg-primary ms-2"><?= $unreadCount ?> unread</span>
                        <?php endif; ?>
                    </h1>
                    <div class="d-flex gap-2">
                        <?php if ($unreadCount > 0): ?>
                            <a href="notifications.php?action=mark_all_read" class="btn btn-mark-read">
                                <i class="bi bi-check-all me-1"></i> Mark all as read
                            </a>
                        <?php endif; ?>
                        <?php if ($totalCount > 0): ?>
                            <a href="notifications.php?action=clear_read" class="btn btn-mark-read" 
                               onclick="return confirm('Clear all read notifications?')">
                                <i class="bi bi-trash me-1"></i> Clear read
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Filter Section -->
                <div class="mb-4">
                    <h6 class="mb-2">Filter by type:</h6>
                    <div class="d-flex flex-wrap">
                        <a href="notifications.php" class="filter-badge <?= $filter === 'all' ? 'active' : '' ?>">
                            All <span class="badge bg-secondary ms-1"><?= $totalCount ?></span>
                        </a>
                        <?php foreach ($typeInfo as $type => $info): ?>
                            <?php if (isset($typeCounts[$type])): ?>
                                <a href="notifications.php?filter=<?= $type ?>" 
                                   class="filter-badge <?= $filter === $type ? 'active' : '' ?>">
                                    <?= $info['label'] ?> <span class="badge bg-secondary ms-1"><?= $typeCounts[$type] ?></span>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <?php if (empty($notifications)): ?>
                    <div class="empty-state">
                        <i class="bi bi-bell"></i>
                        <h4>No notifications yet</h4>
                        <p>You'll see important updates here</p>
                        <a href="browse.php" class="btn btn-primary mt-3">
                            <i class="bi bi-play-circle me-2"></i> Start Browsing
                        </a>
                    </div>
                <?php else: ?>
                    <div class="notifications-list">
                        <?php foreach ($notifications as $notification): 
                            $type = $notification['type'];
                            $typeData = $typeInfo[$type] ?? $typeInfo['general'];
                        ?>
                            <div class="notification-card <?= $notification['is_read'] ? '' : 'unread' ?>">
                                <div class="notification-actions">
                                    <?php if (!$notification['is_read']): ?>
                                        <a href="notifications.php?action=mark_read&id=<?= $notification['notification_id'] ?>" 
                                           class="btn-notification-action" title="Mark as read">
                                            <i class="bi bi-check"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="notifications.php?action=delete&id=<?= $notification['notification_id'] ?>" 
                                       class="btn-notification-action" 
                                       onclick="return confirm('Delete this notification?')"
                                       title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                                
                                <div class="d-flex align-items-start">
                                    <div class="notification-icon <?= $typeData['color'] ?>">
                                        <i class="bi <?= $typeData['icon'] ?>"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <h6 class="mb-0"><?= htmlspecialchars($notification['title']) ?></h6>
                                            <span class="notification-type-badge"><?= $typeData['label'] ?></span>
                                        </div>
                                        <p class="mb-2"><?= htmlspecialchars($notification['message']) ?></p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="notification-time">
                                                <i class="bi bi-clock me-1"></i>
                                                <?= date('M j, Y g:i A', strtotime($notification['created_at'])) ?>
                                            </small>
                                            <?php if ($notification['content_title'] || $notification['episode_title']): ?>
                                                <?php
                                                $watchUrl = "";
                                                $linkText = "View Content";
                                                
                                                if ($notification['episode_title']) {
                                                    $watchUrl = "watch.php?id=" . $notification['related_id'];
                                                    $linkText = "Watch Episode";
                                                } elseif ($notification['content_type'] === 'tv_show') {
                                                    $watchUrl = "watch.php?id=" . $notification['related_id'];
                                                    $linkText = "View Show";
                                                } else {
                                                    $watchUrl = "watch.php?id=" . $notification['related_id'];
                                                }
                                                ?>
                                                <a href="<?= $watchUrl ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <?= $linkText ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-refresh notifications every 30 seconds if there are unread ones
        <?php if ($unreadCount > 0): ?>
        setInterval(function() {
            fetch('notifications.php')
                .then(response => response.text())
                .then(html => {
                    // Simple check for new notifications - in a real app, you'd use AJAX
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newUnreadCount = doc.querySelector('.badge')?.textContent || '0';
                    
                    // Update badge in header if exists
                    const headerBadge = document.querySelector('.notification-badge');
                    if (headerBadge) {
                        headerBadge.textContent = newUnreadCount;
                    }
                });
        }, 30000);
        <?php endif; ?>
    </script>
</body>
</html>