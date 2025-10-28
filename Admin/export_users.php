<?php
// Ensure this script is only accessible to authenticated admins if session system exists
// session_start();
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
//     header('HTTP/1.1 403 Forbidden');
//     echo 'Forbidden';
//     exit();
// }

// Database connection
$mysqli = new mysqli('localhost', 'root', '', 'Streamify');
if ($mysqli->connect_error) {
    header('HTTP/1.1 500 Internal Server Error');
    echo 'Database connection failed';
    exit();
}

// Prepare CSV filename with timestamp
$filename = 'users_export_' . date('Y-m-d_H-i-s') . '.csv';

// Set headers to force download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');

// Column headers
fputcsv($output, [
    'User ID',
    'Username',
    'Email',
    'Subscription',
    'Is Premium',
    'Created At',
    'Last Login'
]);

// Stream rows
$query = "SELECT user_id, username, email, subscription_type, is_premium, created_at, last_login FROM users WHERE role != 'admin' ORDER BY created_at DESC";
if ($result = $mysqli->query($query)) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['user_id'],
            $row['username'],
            $row['email'],
            $row['subscription_type'],
            $row['is_premium'] ? 'Yes' : 'No',
            $row['created_at'],
            $row['last_login']
        ]);
    }
    $result->free();
}

fclose($output);
$mysqli->close();
exit();
