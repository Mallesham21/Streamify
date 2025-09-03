<?php
if (isset($_GET['url']) && isset($_GET['name'])) {
    $url = $_GET['url'];
    $name = $_GET['name'];

    // Fetch the file from TMDb
    $imageData = file_get_contents($url);

    // Send headers for download
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($name) . '"');
    header('Content-Length: ' . strlen($imageData));

    echo $imageData;
    exit;
}
?>