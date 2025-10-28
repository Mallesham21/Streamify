<?php
// config/database.php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Streamify";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$_SESSION['admin_logged_in']= TRUE;
// Set charset
$conn->set_charset("utf8mb4");
?>