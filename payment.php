<?php
// Zwitch API credentials
$api_key = 'ak_test_6uKYkkiv4g2B1wMzuKS5ZLLzNZtOEDN94Ifv';
$secret_key = 'sk_test_fCGmkeUzWDSZBZi39xEi2jbDS72TGmgV7vhL';

// Customer & transaction details (usually collected from form)
$customer_name = "John Doe";
$customer_email = "john@example.com";
$amount = 10000; // in paise (i.e., ₹100.00)

// Prepare the payload
$data = [
    "amount" => $amount,
    "currency" => "INR",
    "description" => "Streamify Premium Subscription",
    "customer" => [
        "name" => $customer_name,
        "email" => $customer_email
    ],
    "callback_url" => "https://yourdomain.com/zwitch_callback.php",
    "redirect_url" => "https://yourdomain.com/payment_success.php"
];

// Initialize cURL
$ch = curl_init('https://sandbox.zwitch.io/v1/payment_links');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'X-API-KEY: ' . $api_key,
    'X-API-SECRET: ' . $secret_key
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

// Execute and handle response
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Decode response
$result = json_decode($response, true);

// Check and redirect
if ($httpcode == 200 && isset($result['data']['short_url'])) {
    header("Location: " . $result['data']['short_url']);
    exit;
} else {
    echo "<h3>Payment Initialization Failed</h3>";
    echo "<pre>" . print_r($result, true) . "</pre>";
}
?>