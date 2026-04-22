<?php
// manage_subscription_handler.php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Streamify";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is admin (you might want to add proper authentication)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Handle different actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_subscription':
            addSubscription($conn);
            break;
        case 'update_subscription':
            updateSubscription($conn);
            break;
        case 'delete_subscription':
            deleteSubscription($conn);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

function addSubscription($conn) {
    // Validate required fields
    $required_fields = ['name', 'price', 'duration_days'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
            return;
        }
    }
    
    // Sanitize inputs
    $name = trim($conn->real_escape_string($_POST['name']));
    $price = floatval($_POST['price']);
    $duration_days = intval($_POST['duration_days']);
    $description = isset($_POST['description']) ? trim($conn->real_escape_string($_POST['description'])) : '';
    
    // Process features
    $features = [];
    if (isset($_POST['features']) && !empty($_POST['features'])) {
        $features_input = $_POST['features'];
        if (is_string($features_input)) {
            $features_array = json_decode($features_input, true);
            if (is_array($features_array)) {
                $features = $features_array;
            }
        }
    }
    
    // Validate price and duration
    if ($price <= 0) {
        echo json_encode(['success' => false, 'message' => 'Price must be greater than 0']);
        return;
    }
    
    if ($duration_days <= 0) {
        echo json_encode(['success' => false, 'message' => 'Duration must be greater than 0 days']);
        return;
    }
    
    // Check if subscription plan with same name already exists
    $check_sql = "SELECT sub_id FROM subscriptions WHERE name = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $name);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'A subscription plan with this name already exists']);
        $check_stmt->close();
        return;
    }
    $check_stmt->close();
    
    // Insert new subscription plan
    $insert_sql = "INSERT INTO subscriptions (name, price, duration_days, description, features) VALUES (?, ?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    
    // Convert features array to JSON string
    $features_json = !empty($features) ? json_encode($features) : null;
    
    $insert_stmt->bind_param("sdisss", $name, $price, $duration_days, $description, $features_json);
    
    if ($insert_stmt->execute()) {
        // Log the activity
        logActivity($conn, $_SESSION['user_id'], 'subscription_added', "Added subscription plan: $name");
        
        echo json_encode(['success' => true, 'message' => 'Subscription plan added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error adding subscription plan: ' . $insert_stmt->error]);
    }
    
    $insert_stmt->close();
}

function updateSubscription($conn) {
    // Validate required fields
    $required_fields = ['sub_id', 'name', 'price', 'duration_days'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
            return;
        }
    }
    
    // Sanitize inputs
    $sub_id = intval($_POST['sub_id']);
    $name = trim($conn->real_escape_string($_POST['name']));
    $price = floatval($_POST['price']);
    $duration_days = intval($_POST['duration_days']);
    $description = isset($_POST['description']) ? trim($conn->real_escape_string($_POST['description'])) : '';
    
    // Process features
    $features = [];
    if (isset($_POST['features']) && !empty($_POST['features'])) {
        $features_input = $_POST['features'];
        if (is_string($features_input)) {
            $features_array = json_decode($features_input, true);
            if (is_array($features_array)) {
                $features = $features_array;
            }
        }
    }
    
    // Validate price and duration
    if ($price <= 0) {
        echo json_encode(['success' => false, 'message' => 'Price must be greater than 0']);
        return;
    }
    
    if ($duration_days <= 0) {
        echo json_encode(['success' => false, 'message' => 'Duration must be greater than 0 days']);
        return;
    }
    
    // Check if subscription plan exists
    $check_sql = "SELECT sub_id, name FROM subscriptions WHERE sub_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $sub_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Subscription plan not found']);
        $check_stmt->close();
        return;
    }
    
    $old_plan = $check_result->fetch_assoc();
    $check_stmt->close();
    
    // Check if another subscription plan with same name already exists
    $check_name_sql = "SELECT sub_id FROM subscriptions WHERE name = ? AND sub_id != ?";
    $check_name_stmt = $conn->prepare($check_name_sql);
    $check_name_stmt->bind_param("si", $name, $sub_id);
    $check_name_stmt->execute();
    $check_name_result = $check_name_stmt->get_result();
    
    if ($check_name_result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Another subscription plan with this name already exists']);
        $check_name_stmt->close();
        return;
    }
    $check_name_stmt->close();
    
    // Update subscription plan
    $update_sql = "UPDATE subscriptions SET name = ?, price = ?, duration_days = ?, description = ?, features = ? WHERE sub_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    
    // Convert features array to JSON string
    $features_json = !empty($features) ? json_encode($features) : null;
    
    $update_stmt->bind_param("sdisssi", $name, $price, $duration_days, $description, $features_json, $sub_id);
    
    if ($update_stmt->execute()) {
        // Log the activity
        logActivity($conn, $_SESSION['user_id'], 'subscription_updated', "Updated subscription plan: {$old_plan['name']} to $name");
        
        echo json_encode(['success' => true, 'message' => 'Subscription plan updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating subscription plan: ' . $update_stmt->error]);
    }
    
    $update_stmt->close();
}

function deleteSubscription($conn) {
    // Validate required field
    if (empty($_POST['sub_id'])) {
        echo json_encode(['success' => false, 'message' => 'Subscription ID is required']);
        return;
    }
    
    $sub_id = intval($_POST['sub_id']);
    
    // Check if subscription plan exists
    $check_sql = "SELECT name FROM subscriptions WHERE sub_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $sub_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Subscription plan not found']);
        $check_stmt->close();
        return;
    }
    
    $plan = $check_result->fetch_assoc();
    $plan_name = $plan['name'];
    $check_stmt->close();
    
    // Check if there are active subscriptions for this plan
    $check_active_sql = "SELECT COUNT(*) as active_count FROM user_subscriptions WHERE sub_id = ? AND status = 'active'";
    $check_active_stmt = $conn->prepare($check_active_sql);
    $check_active_stmt->bind_param("i", $sub_id);
    $check_active_stmt->execute();
    $check_active_result = $check_active_stmt->get_result();
    $active_count = $check_active_result->fetch_assoc()['active_count'];
    $check_active_stmt->close();
    
    if ($active_count > 0) {
        echo json_encode(['success' => false, 'message' => "Cannot delete subscription plan. There are $active_count active subscriptions using this plan."]);
        return;
    }
    
    // Delete subscription plan
    $delete_sql = "DELETE FROM subscriptions WHERE sub_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $sub_id);
    
    if ($delete_stmt->execute()) {
        // Log the activity
        logActivity($conn, $_SESSION['user_id'], 'subscription_deleted', "Deleted subscription plan: $plan_name");
        
        echo json_encode(['success' => true, 'message' => 'Subscription plan deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error deleting subscription plan: ' . $delete_stmt->error]);
    }
    
    $delete_stmt->close();
}

function logActivity($conn, $user_id, $action, $details) {
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    
    $log_sql = "INSERT INTO activity_logs (user_id, action, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)";
    $log_stmt = $conn->prepare($log_sql);
    
    $details_json = json_encode(['message' => $details]);
    $log_stmt->bind_param("issss", $user_id, $action, $details_json, $ip_address, $user_agent);
    $log_stmt->execute();
    $log_stmt->close();
}

// Close database connection
$conn->close();
?>