<?php
// db.php
$host = 'localhost';
$dbname = 'Streamify';
$username = 'root'; // Change as needed
$password = ''; // Change as needed

session_start();
try {
    $conn = new mysqli($host, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}
function logout(){
    session_destroy();
    header('Location: index.php');
    exit;
}
$api_key = "fba04b85-918b-4dc2-866a-8358cc43a2b4";
?>