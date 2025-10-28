<?php
// ===============================
// Razorpay Test Keys
// ===============================
$keyId = 'rzp_test_ROW346qokK2AC9';      // Replace with your Test Key ID
$keySecret = 'JKHTH8P5xxWXM1kGsBkrl5IB'; // Replace with your Test Key Secret

$orderId = null;
$amount = 10000; // ₹100 in paise

// If user clicked Pay button
if (isset($_POST['pay'])) {

    // Prepare data for Razorpay order
    $data = [
        'amount' => $amount,
        'currency' => 'INR',
        'receipt' => 'ORD' . time(),
        'payment_capture' => 1
    ];

    // cURL request to create order
    $ch = curl_init('https://api.razorpay.com/v1/orders');
    curl_setopt($ch, CURLOPT_USERPWD, $keyId . ':' . $keySecret);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'cURL Error: ' . curl_error($ch);
        exit;
    }
    curl_close($ch);

    $order = json_decode($response, true);
    $orderId = $order['id']; // Use this for checkout
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Razorpay Test Payment</title>
</head>
<body>
    <h2>Pay ₹100 via Razorpay (Test Mode)</h2>

    <form method="POST">
        <button type="submit" name="pay">Pay ₹100</button>
    </form>

    <?php if ($orderId) { ?>
        <!-- Razorpay Checkout via CDN -->
        <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
        <script>
            var options = {
                "key": "<?php echo $keyId; ?>", 
                "amount": "<?php echo $amount; ?>", 
                "currency": "INR",
                "name": "Test Store",
                "description": "Test Payment",
                "order_id": "<?php echo $orderId; ?>",
                "handler": function (response){
                    alert("Payment Successful!\nPayment ID: " + response.razorpay_payment_id);
                    // Optional: redirect or store payment_id in DB
                    // window.location.href = "success.php?payment_id=" + response.razorpay_payment_id;
                },
                "prefill": {
                    "name": "Test User",
                    "email": "test@example.com",
                    "contact": "9999999999"
                },
                "theme": {
                    "color": "#3399cc"
                }
            };
            var rzp1 = new Razorpay(options);
            rzp1.open();
        </script>
    <?php } ?>
</body>
</html>