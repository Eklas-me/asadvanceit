<?php
include_once('./class/function.php');
$obj = new AdvancedAdmin();

if (isset($_POST['id']) && isset($_POST['user_id'])) {
    $notification_id = $_POST['id'];
    $user_id = $_POST['user_id'];

    // Call the method to mark the notification as read for the specific user
    $obj->markNotificationAsRead($notification_id, $user_id);

    echo "Notification marked as read.";
}
?>