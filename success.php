<?php
define('ZWITCH_SECRET_KEY', 'your_test_secret_key');
define('ZWITCH_BASE_URL', 'https://sandbox.zwitchpay.com/v1');

$host = "localhost";
$user = "root";
$pass = "";
$db   = "streamify";
$conn = new mysqli($host, $user, $pass, $db);

$data = json_decode(file_get_contents("php://input"), true);
$plan_id = $data['plan_id'];
$payment_id = $data['payment_id'];

// Verify with Zwitch
$ch = curl_init(ZWITCH_BASE_URL . "/payments/" . $payment_id);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . ZWITCH_SECRET_KEY
]);
$response = curl_exec($ch);
curl_close($ch);

$verify = json_decode($response, true);

if ($verify && $verify['status'] === 'success') {
    $stmt = $conn->prepare("INSERT INTO user_subscriptions (user_id, sub_id, start_date, end_date, status, payment_id) VALUES (?, ?, NOW(), DATE_ADD(NOW(), INTERVAL ? DAY), 'active', ?)");
    $user_id = 123; // session normally
    $duration_days = 30; // or fetch from plan
    $stmt->bind_param("iiis", $user_id, $plan_id, $duration_days, $payment_id);
    $stmt->execute();
    echo json_encode(["ok" => true]);
} else {
    echo json_encode(["ok" => false, "error" => "Verification failed"]);
}