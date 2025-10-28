<?php
// includes/functions.php

function getDashboardStats($conn) {
    $stats = [];
    
    // Total Users
    $result = $conn->query("SELECT COUNT(*) as total FROM users ");
    $stats['total_users'] = $result->fetch_assoc()['total'];
    
    // Active Users (last 15 minutes)
    $result = $conn->query("SELECT COUNT(*) as active FROM users ");
    $stats['active_users'] = $result->fetch_assoc()['active'];
    
    // Total Content
    $result = $conn->query("SELECT COUNT(*) as total FROM content ");
    $stats['total_content'] = $result->fetch_assoc()['total'];
    
    // Monthly Revenue
    $result = $conn->query("SELECT SUM(amount) as revenue FROM payments WHERE status = 'completed' AND MONTH(payment_date) = MONTH(CURRENT_DATE())");
    $stats['revenue'] = $result->fetch_assoc()['revenue'] ?? 0;
    
    // Recent Content
    $result = $conn->query("SELECT c.*,
       cat.name AS category
FROM content c
LEFT JOIN content_categories cc ON c.content_id = cc.content_id
LEFT JOIN categories cat ON cc.category_id = cat.category_id
ORDER BY c.created_at DESC
LIMIT 5;");
    $stats['recent_content'] = [];
    while ($row = $result->fetch_assoc()) {
        $stats['recent_content'][] = $row;
    }
    
    // Recent Activity
    $stats['recent_activity'] = [
        [
            'icon' => 'person-plus',
            'color' => 'success',
            'action' => 'New User Registration',
            'details' => 'John Doe registered with Premium subscription',
            'user' => 'System',
            'time' => '2 mins ago'
        ],
        [
            'icon' => 'upload',
            'color' => 'primary',
            'action' => 'Content Uploaded',
            'details' => 'New movie "The Last Adventure" uploaded',
            'user' => 'Admin User',
            'time' => '15 mins ago'
        ],
        [
            'icon' => 'credit-card',
            'color' => 'warning',
            'action' => 'Payment Received',
            'details' => 'Monthly subscription payment from user@example.com',
            'user' => 'Payment Gateway',
            'time' => '1 hour ago'
        ]
    ];
    
    return $stats;
}

function logAdminAction($conn, $admin_id, $action, $details = '') {
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $admin_id, $action, $details, $ip_address, $user_agent);
    $stmt->execute();
}

function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $units[$pow];
}
?>