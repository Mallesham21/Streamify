<?php
$api_key = "fba04b85-918b-4dc2-866a-8358cc43a2b4"; // Pixeldrain API key
$uploaded_url = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file_path = $_FILES['file']['tmp_name'];
    $file_name = basename($_FILES['file']['name']);

    $ch = curl_init();
    $url = "https://pixeldrain.com/api/file/" . urlencode($file_name);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_PUT, true);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, ":" . $api_key);

    // Open file
    $file_handle = fopen($file_path, 'r');
    curl_setopt($ch, CURLOPT_INFILE, $file_handle);
    curl_setopt($ch, CURLOPT_INFILESIZE, filesize($file_path));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/octet-stream',
    ]);

    // Progress tracking
    curl_setopt($ch, CURLOPT_NOPROGRESS, false);
    curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function ($resource, $download_size, $downloaded, $upload_size, $uploaded) {
        if ($upload_size > 0) {
            echo "<script>
                document.getElementById('progress').innerHTML = '" . round($uploaded / $upload_size * 100) . "%';
            </script>";
            ob_flush();
            flush();
        }
    });

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    fclose($file_handle);

    if (curl_errno($ch)) {
        echo "❌ cURL Error: " . curl_error($ch);
    } else {
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($http_code >= 200 && $http_code < 300) {
            $result = json_decode($response, true);
            $uploaded_url = "https://pixeldrain.com/u/" . $result['id'];
            echo $result['id'];
        } else {
            echo "❌ API Error (HTTP $http_code): " . $response;
        }
    }
    curl_close($ch);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Video Upload & Play</title>
</head>
<body>
<h2>Upload Video to Pixeldrain</h2>
<form method="POST" enctype="multipart/form-data">
    <input type="file" name="file" required>
    <button type="submit">Upload</button>
</form>
<p>Upload Progress: <span id="progress">0%</span></p>

<?php if ($uploaded_url): ?>
    <h3>Uploaded Video</h3>
    <video width="640" controls>
        <source src="<?php echo $uploaded_url; ?>" type="video/mp4">
        Your browser does not support HTML5 video.
    </video>
    <p><a href="<?php echo $uploaded_url; ?>" target="_blank">Download Link</a></p>
<?php endif; ?>
</body>
</html>