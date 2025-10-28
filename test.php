<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Video Upload</title>
</head>
<body>
    <h1>Upload a Video</h1>
    
    <form id="uploadForm">
        <input type="file" id="videoFile" accept="video/*" required>
        <button type="submit">Upload Video</button>
    </form>
    
    <div id="status"></div>
    <div id="result"></div>

    <script>
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const fileInput = document.getElementById('videoFile');
            const file = fileInput.files[0];
            
            if (!file) {
                document.getElementById('status').textContent = 'Please select a video file.';
                return;
            }
            
            uploadVideo(file);
        });
        
        function uploadVideo(file) {
            const statusDiv = document.getElementById('status');
            const resultDiv = document.getElementById('result');
            
            statusDiv.textContent = 'Uploading...';
            resultDiv.textContent = '';
            
            const formData = new FormData();
            formData.append('file', file);
            
            fetch('https://pixeldrain.com/api/file', {
                method: 'POST',
                headers: {
                    'Authorization': 'Basic ' + btoa(':' + 'fba04b85-918b-4dc2-866a-8358cc43a2b4')
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    statusDiv.textContent = 'Upload successful!';
                    resultDiv.innerHTML = `
                        <p>File ID: ${data.id}</p>
                        <p>Direct link: <a href="https://pixeldrain.com/u/${data.id}" target="_blank">https://pixeldrain.com/u/${data.id}</a></p>
                    `;
                } else {
                    statusDiv.textContent = 'Upload failed.';
                    resultDiv.textContent = 'Error: ' + (data.message || 'Unknown error');
                }
            })
            .catch(error => {
                statusDiv.textContent = 'Upload failed.';
                resultDiv.textContent = 'Error: ' + error.message;
            });
        }
    </script>
</body>
</html>