<?php
require_once "db.php";
require_once "notification_manager.php";

$notificationManager = new NotificationManager($conn);

$notificationManager->checkSubscriptionEnding();
$notificationManager->checkExpiredSubscriptions();
$notificationManager->checkScheduledContent();

echo "OK";
?>