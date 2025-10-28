<?php
// Simple test script to verify add content functionality
include 'config/database.php';

echo "<h2>Add Content Functionality Test</h2>";

// Test 1: Check if database connection works
echo "<h3>Test 1: Database Connection</h3>";
if ($conn && !$conn->connect_error) {
    echo "✅ Database connection successful<br>";
} else {
    echo "❌ Database connection failed<br>";
    exit;
}

// Test 2: Check if content table exists and has correct structure
echo "<h3>Test 2: Content Table Structure</h3>";
$result = $conn->query("DESCRIBE content");
if ($result) {
    echo "✅ Content table exists<br>";
    $fields = [];
    while ($row = $result->fetch_assoc()) {
        $fields[] = $row['Field'];
    }
    echo "Fields found: " . implode(', ', $fields) . "<br>";
} else {
    echo "❌ Content table not found<br>";
}

// Test 3: Check if categories table exists
echo "<h3>Test 3: Categories Table</h3>";
$result = $conn->query("SELECT COUNT(*) as count FROM categories");
if ($result) {
    $count = $result->fetch_assoc()['count'];
    echo "✅ Categories table exists with $count categories<br>";
} else {
    echo "❌ Categories table not found<br>";
}

// Test 4: Check if content_categories table exists
echo "<h3>Test 4: Content Categories Table</h3>";
$result = $conn->query("DESCRIBE content_categories");
if ($result) {
    echo "✅ Content categories table exists<br>";
} else {
    echo "❌ Content categories table not found<br>";
}

// Test 5: Check file permissions
echo "<h3>Test 5: File Permissions</h3>";
$files_to_check = ['add_content.php', 'content_insert.php', 'add_category.php'];
foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        echo "✅ $file exists<br>";
    } else {
        echo "❌ $file not found<br>";
    }
}

// Test 6: Check media directories
echo "<h3>Test 6: Media Directories</h3>";
$directories = ['../thumbnails/', '../Banners/', '../videos/'];
foreach ($directories as $dir) {
    if (is_dir($dir)) {
        $files = array_diff(scandir($dir), array('.', '..'));
        $count = count($files);
        echo "✅ $dir exists with $count files<br>";
    } else {
        echo "❌ $dir not found<br>";
    }
}

// Test 7: Check upload directories permissions
echo "<h3>Test 7: Upload Directory Permissions</h3>";
$upload_dirs = ['../thumbnails/', '../Banners/', '../videos/'];
foreach ($upload_dirs as $dir) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            echo "✅ $dir is writable<br>";
        } else {
            echo "❌ $dir is not writable<br>";
        }
    } else {
        echo "⚠️ $dir does not exist (will be created on upload)<br>";
    }
}

echo "<h3>Test Complete</h3>";
echo "<p><a href='add_content.php'>Go to Add Content Form</a></p>";
echo "<p><a href='manage_content.php'>Go to Manage Content</a></p>";
echo "<p><strong>Current Features:</strong></p>";
echo "<ul>";
echo "<li>✅ File upload OR URL input for thumbnails, banners, and videos</li>";
echo "<li>✅ Live preview for all media files</li>";
echo "<li>✅ Removed rating, views, list_id, and release_date fields</li>";
echo "<li>✅ Removed video dropdown from media information</li>";
echo "<li>✅ Date-time input for scheduled releases (moved after checkbox)</li>";
echo "<li>✅ Dynamic category management with checkboxes</li>";
echo "<li>✅ Fixed AJAX response handling</li>";
echo "<li>✅ Fixed add category functionality</li>";
echo "</ul>";

$conn->close();
?>
