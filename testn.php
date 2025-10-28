<?php
session_start();
require_once 'db.php';

// Set a test user if not logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 9;
    $_SESSION['username'] = 'TestUser';
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Notifications</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f0f0f0; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; }
        .btn { padding: 10px 15px; margin: 5px; border: none; border-radius: 5px; cursor: pointer; }
        .btn-test { background: #007bff; color: white; }
        .result { margin-top: 20px; padding: 15px; border-radius: 5px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .notification { background: #f8f9fa; padding: 10px; margin: 5px 0; border-radius: 5px; border-left: 4px solid #007bff; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔔 Notification Tester</h1>
        <p>User ID: <?php echo $_SESSION['user_id']; ?></p>
        
        <div>
            <button class="btn btn-test" onclick="test('test_welcome')">Test Welcome</button>
            <button class="btn btn-test" onclick="test('test_trending')">Test Trending</button>
            <button class="btn btn-test" onclick="test('test_recommendation')">Test Recommendation</button>
            <button class="btn btn-test" onclick="test('view_notifications')">View Notifications</button>
        </div>
        
        <div id="result" class="result" style="display:none;"></div>
    </div>

    <script>
        async function test(action) {
            const resultDiv = document.getElementById('result');
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = 'Testing...';
            
            try {
                const response = await fetch('testno.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=' + action
                });
                
                const data = await response.json();
                
                let html = `<div class="${data.success ? 'success' : 'error'}">`;
                html += `<strong>${data.success ? '✅ SUCCESS' : '❌ ERROR'}</strong><br>`;
                html += data.message;
                html += '</div>';
                
                if (data.data) {
                    if (action === 'test_trending') {
                        html += '<h3>Trending Content:</h3>';
                        data.data.forEach(item => {
                            html += `<div class="notification">
                                <strong>${item.title}</strong><br>
                                Type: ${item.content_type} | Views: ${item.view_count || 0}
                            </div>`;
                        });
                    }
                    else if (action === 'view_notifications') {
                        html += '<h3>Notifications:</h3>';
                        if (data.data.length > 0) {
                            data.data.forEach(notif => {
                                html += `<div class="notification">
                                    ${notif.message}<br>
                                    <small>${new Date(notif.created_at).toLocaleString()}</small>
                                </div>`;
                            });
                        } else {
                            html += '<p>No notifications found</p>';
                        }
                    }
                }
                
                resultDiv.innerHTML = html;
                
            } catch (error) {
                resultDiv.innerHTML = `<div class="error">
                    <strong>❌ ERROR</strong><br>
                    ${error.message}
                </div>`;
            }
        }
        
        // Auto-test on load
        window.addEventListener('load', function() {
            test('test_trending');
        });
    </script>
</body>
</html>