<?php
require_once "db.php";

class NotificationManager {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    // Create welcome notification for new users
    public function createWelcomeNotification($user_id, $username) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO notifications (user_id, type, title, message, created_at) 
                VALUES (?, 'welcome', 'Welcome to Streamify!', ?, NOW())
            ");
            $message = "Welcome aboard, {$username}! Start exploring thousands of movies and TV shows.";
            $stmt->bind_param("is", $user_id, $message);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error creating welcome notification: " . $e->getMessage());
            return false;
        }
    }
    
    // Create subscription success notification
    public function createSubscriptionSuccess($user_id, $plan_name, $end_date) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO notifications (user_id, type, title, message, created_at) 
                VALUES (?, 'subscription_success', 'Subscription Activated', ?, NOW())
            ");
            $formatted_date = date('M j, Y', strtotime($end_date));
            $message = "Your {$plan_name} has been successfully activated! Your subscription is valid until {$formatted_date}.";
            $stmt->bind_param("is", $user_id, $message);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error creating subscription success notification: " . $e->getMessage());
            return false;
        }
    }
    
    // Create subscription ending notification
    public function createSubscriptionEnding($user_id, $days_left) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO notifications (user_id, type, title, message, created_at) 
                VALUES (?, 'subscription_ending', 'Subscription Ending Soon', ?, NOW())
            ");
            $message = "Your premium subscription will expire in {$days_left} days. Renew now to continue enjoying uninterrupted streaming.";
            $stmt->bind_param("is", $user_id, $message);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error creating subscription ending notification: " . $e->getMessage());
            return false;
        }
    }
    
    // Create subscription expired notification
    public function createSubscriptionExpired($user_id) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO notifications (user_id, type, title, message, created_at) 
                VALUES (?, 'subscription_expired', 'Subscription Expired', ?, NOW())
            ");
            $message = "Your premium subscription has expired. Upgrade now to regain access to premium content.";
            $stmt->bind_param("is", $user_id, $message);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error creating subscription expired notification: " . $e->getMessage());
            return false;
        }
    }
    
    // Create content available notification
    public function createContentAvailable($user_id, $content_id, $content_title) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO notifications (user_id, type, title, message, related_id, created_at) 
                VALUES (?, 'content_available', 'New Content Available', ?, ?, NOW())
            ");
            $message = "The content \"{$content_title}\" you were waiting for is now available to watch.";
            $stmt->bind_param("isi", $user_id, $message, $content_id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error creating content available notification: " . $e->getMessage());
            return false;
        }
    }
    
    // Create reminder set notification
    public function createReminderSet($user_id, $content_title, $reminder_date) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO notifications (user_id, type, title, message, created_at) 
                VALUES (?, 'reminder_set', 'Reminder Set', ?, NOW())
            ");
            $formatted_date = date('M j, Y', strtotime($reminder_date));
            $message = "You will be notified when \"{$content_title}\" releases on {$formatted_date}";
            $stmt->bind_param("is", $user_id, $message);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error creating reminder set notification: " . $e->getMessage());
            return false;
        }
    }
    
    // Create new episode notification
    public function createNewEpisode($user_id, $show_title, $episode_title, $episode_id) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO notifications (user_id, type, title, message, related_id, created_at) 
                VALUES (?, 'new_episode', 'New Episode Available', ?, ?, NOW())
            ");
            $message = "A new episode \"{$episode_title}\" of \"{$show_title}\" is now available.";
            $stmt->bind_param("isi", $user_id, $message, $episode_id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error creating new episode notification: " . $e->getMessage());
            return false;
        }
    }
    
    // Create recommendation notification
    public function createRecommendation($user_id, $content_title, $content_id, $reason = "based on your watching history") {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO notifications (user_id, type, title, message, related_id, created_at) 
                VALUES (?, 'recommendation', 'Recommended for You', ?, ?, NOW())
            ");
            $message = "You might like \"{$content_title}\" {$reason}.";
            $stmt->bind_param("isi", $user_id, $message, $content_id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error creating recommendation notification: " . $e->getMessage());
            return false;
        }
    }
    
    // Create system announcement
    public function createSystemAnnouncement($user_id, $title, $message) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO notifications (user_id, type, title, message, created_at) 
                VALUES (?, 'system', ?, ?, NOW())
            ");
            $stmt->bind_param("iss", $user_id, $title, $message);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error creating system announcement: " . $e->getMessage());
            return false;
        }
    }
    
    // Create watchlist reminder
    public function createWatchlistReminder($user_id, $content_title, $content_id) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO notifications (user_id, type, title, message, related_id, created_at) 
                VALUES (?, 'watchlist_reminder', 'Continue Watching', ?, ?, NOW())
            ");
            $message = "Don't forget to watch \"{$content_title}\" from your watchlist.";
            $stmt->bind_param("isi", $user_id, $message, $content_id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error creating watchlist reminder: " . $e->getMessage());
            return false;
        }
    }
    
    // Create general notification
    public function createGeneralNotification($user_id, $title, $message, $related_id = null) {
        try {
            if ($related_id) {
                $stmt = $this->conn->prepare("
                    INSERT INTO notifications (user_id, type, title, message, related_id, created_at) 
                    VALUES (?, 'general', ?, ?, ?, NOW())
                ");
                $stmt->bind_param("issi", $user_id, $title, $message, $related_id);
            } else {
                $stmt = $this->conn->prepare("
                    INSERT INTO notifications (user_id, type, title, message, created_at) 
                    VALUES (?, 'general', ?, ?, NOW())
                ");
                $stmt->bind_param("iss", $user_id, $title, $message);
            }
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error creating general notification: " . $e->getMessage());
            return false;
        }
    }
    
    // Check and send scheduled content notifications
    public function checkScheduledContent() {
        try {
            $sql = "
                SELECT c.content_id, c.title, r.user_id, r.reminder_id 
                FROM content c 
                JOIN reminders r ON c.content_id = r.content_id 
                WHERE c.is_scheduled = 1 
                AND c.schedule_date <= NOW() 
                AND r.status = 'active'
            ";
            
            $result = $this->conn->query($sql);
            
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    // Create content available notification
                    $this->createContentAvailable($row['user_id'], $row['content_id'], $row['title']);
                    
                    // Update reminder status
                    $updateStmt = $this->conn->prepare("
                        UPDATE reminders SET status = 'notified' 
                        WHERE reminder_id = ?
                    ");
                    $updateStmt->bind_param("i", $row['reminder_id']);
                    $updateStmt->execute();
                    $updateStmt->close();
                }
                $result->free();
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error checking scheduled content: " . $e->getMessage());
            return false;
        }
    }
    
    // Check for subscription ending notifications
    public function checkSubscriptionEnding() {
        try {
            $sql = "
                SELECT us.user_id, us.end_date, s.name as plan_name, u.username 
                FROM user_subscriptions us 
                JOIN subscriptions s ON us.sub_id = s.sub_id 
                JOIN users u ON us.user_id = u.user_id 
                WHERE us.status = 'active' 
                AND DATEDIFF(us.end_date, CURDATE()) BETWEEN 1 AND 7
            ";
            
            $result = $this->conn->query($sql);
            
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $days_left = date_diff(new DateTime(), new DateTime($row['end_date']))->days;
                    $this->createSubscriptionEnding($row['user_id'], $days_left);
                }
                $result->free();
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error checking subscription ending: " . $e->getMessage());
            return false;
        }
    }
    
    // Check for expired subscriptions
    public function checkExpiredSubscriptions() {
        try {
            $sql = "
                SELECT us.user_id 
                FROM user_subscriptions us 
                WHERE us.status = 'active' 
                AND us.end_date < NOW()
            ";
            
            $result = $this->conn->query($sql);
            
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $this->createSubscriptionExpired($row['user_id']);
                    
                    // Update subscription status
                    $updateStmt = $this->conn->prepare("
                        UPDATE user_subscriptions SET status = 'expired' 
                        WHERE user_id = ? AND status = 'active'
                    ");
                    $updateStmt->bind_param("i", $row['user_id']);
                    $updateStmt->execute();
                    $updateStmt->close();
                    
                    // Update user premium status
                    $userUpdateStmt = $this->conn->prepare("
                        UPDATE users SET subscription_type = 'free', is_premium = 0 
                        WHERE user_id = ?
                    ");
                    $userUpdateStmt->bind_param("i", $row['user_id']);
                    $userUpdateStmt->execute();
                    $userUpdateStmt->close();
                }
                $result->free();
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error checking expired subscriptions: " . $e->getMessage());
            return false;
        }
    }
    
    // Get unread notification count for user
    public function getUnreadCount($user_id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT COUNT(*) as count FROM notifications 
                WHERE user_id = ? AND is_read = 0
            ");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            error_log("Error getting unread count: " . $e->getMessage());
            return 0;
        }
    }
    
    // Get notifications for user
    public function getUserNotifications($user_id, $limit = 10, $offset = 0) {
        $notifications = [];
        try {
            $stmt = $this->conn->prepare("
                SELECT * FROM notifications 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT ? OFFSET ?
            ");
            $stmt->bind_param("iii", $user_id, $limit, $offset);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $notifications[] = $row;
            }
            
            $stmt->close();
        } catch (Exception $e) {
            error_log("Error getting user notifications: " . $e->getMessage());
        }
        
        return $notifications;
    }
    
    // Mark notification as read
    public function markAsRead($notification_id, $user_id) {
        try {
            $stmt = $this->conn->prepare("
                UPDATE notifications SET is_read = 1 
                WHERE notification_id = ? AND user_id = ?
            ");
            $stmt->bind_param("ii", $notification_id, $user_id);
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        } catch (Exception $e) {
            error_log("Error marking notification as read: " . $e->getMessage());
            return false;
        }
    }
    
    // Mark all notifications as read for user
    public function markAllAsRead($user_id) {
        try {
            $stmt = $this->conn->prepare("
                UPDATE notifications SET is_read = 1 
                WHERE user_id = ? AND is_read = 0
            ");
            $stmt->bind_param("i", $user_id);
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        } catch (Exception $e) {
            error_log("Error marking all notifications as read: " . $e->getMessage());
            return false;
        }
    }
    
    // Delete notification
    public function deleteNotification($notification_id, $user_id) {
        try {
            $stmt = $this->conn->prepare("
                DELETE FROM notifications 
                WHERE notification_id = ? AND user_id = ?
            ");
            $stmt->bind_param("ii", $notification_id, $user_id);
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        } catch (Exception $e) {
            error_log("Error deleting notification: " . $e->getMessage());
            return false;
        }
    }
    
    // Clear all read notifications for user
    public function clearReadNotifications($user_id) {
        try {
            $stmt = $this->conn->prepare("
                DELETE FROM notifications 
                WHERE user_id = ? AND is_read = 1
            ");
            $stmt->bind_param("i", $user_id);
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        } catch (Exception $e) {
            error_log("Error clearing read notifications: " . $e->getMessage());
            return false;
        }
    }
    
    // Get latest releases
    public function getLatestReleases($limit = 3) {
        $latest_content = [];
        
        try {
            $query = "
                SELECT 
                    content_id,
                    title,
                    content_type,
                    created_at as release_date
                FROM content 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                ORDER BY created_at DESC 
                LIMIT ?
            ";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('i', $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $latest_content[] = $row;
            }
            
            $stmt->close();
            
        } catch (Exception $e) {
            error_log("Error getting latest releases: " . $e->getMessage());
        }
        
        return $latest_content;
    }
    
    // Get trending content
    public function getTrendingContent($limit = 5) {
        $trending_content = [];
        
        try {
            $query = "
                SELECT 
                    content_id,
                    title,
                    content_type,
                    views as view_count,
                    COALESCE(rating, 0) as avg_rating,
                    (views * 0.7 + COALESCE(rating, 0) * 100 * 0.3) as trending_score
                FROM content 
                WHERE views > 0
                ORDER BY trending_score DESC, views DESC
                LIMIT ?
            ";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('i', $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $trending_content[] = $row;
            }
            
            $stmt->close();
            
        } catch (Exception $e) {
            error_log("Error getting trending content: " . $e->getMessage());
        }
        
        return $trending_content;
    }
    
    // Run all scheduled checks
    public function runScheduledChecks() {
        $results = [
            'scheduled_content' => $this->checkScheduledContent(),
            'subscription_ending' => $this->checkSubscriptionEnding(),
            'expired_subscriptions' => $this->checkExpiredSubscriptions()
        ];
        
        return $results;
    }
    
    // Get notification statistics for user
    public function getNotificationStats($user_id) {
        $stats = [
            'total' => 0,
            'unread' => 0,
            'read' => 0
        ];
        
        try {
            $stmt = $this->conn->prepare("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread,
                    SUM(CASE WHEN is_read = 1 THEN 1 ELSE 0 END) as read_count
                FROM notifications 
                WHERE user_id = ?
            ");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            if ($result) {
                $stats['total'] = $result['total'];
                $stats['unread'] = $result['unread'];
                $stats['read'] = $result['read_count'];
            }
            
        } catch (Exception $e) {
            error_log("Error getting notification stats: " . $e->getMessage());
        }
        
        return $stats;
    }
}

// Usage example:
// $notificationManager = new NotificationManager($conn);
// $notificationManager->createWelcomeNotification($user_id, $username);
?>