<?php
// Include necessary files
include("./class/function.php");
$obj = new AdvancedAdmin();

// Assuming you already have a class instance ($obj)
if (isset($_POST['id'])) {
    $notification_id = $_POST['id'];

    // Call the method to mark the notification as read
    $result = $obj->markNotificationAsRead($notification_id);

    // Return a response to the client
    echo $result;
}
?>