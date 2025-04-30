<?php
session_start();
include "../../dbcon.php";

// Check if logged in and role is driver
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== "driver") {
    header("Location: ../../unauthorized.php");
    exit();
}

if (!isset($_POST['schedule_id']) || !isset($_POST['new_status'])) {
    $_SESSION['error'] = "Missing required parameters";
    header("Location: ../schedules.php");
    exit();
}

$schedule_id = $_POST['schedule_id'];
$new_status = $_POST['new_status'];
$driver_id = $_SESSION['driver_id'];

// Validate status
$allowed_statuses = ['Pending', 'In Transit', 'Delivered', 'Completed', 'Cancelled'];
if (!in_array($new_status, $allowed_statuses)) {
    $_SESSION['error'] = "Invalid status";
    header("Location: ../schedules.php");
    exit();
}

// First verify this schedule belongs to the logged-in driver
$verifyQuery = $con->prepare("SELECT schedule_id FROM schedules WHERE schedule_id = ? AND driver_id = ?");
$verifyQuery->bind_param("ii", $schedule_id, $driver_id);
$verifyQuery->execute();
$verifyResult = $verifyQuery->get_result();

if ($verifyResult->num_rows === 0) {
    $_SESSION['error'] = "Schedule not found or not authorized";
    header("Location: ../schedules.php");
    exit();
}

// Insert new delivery status record
$insertQuery = $con->prepare("INSERT INTO deliveries (schedule_id, delivery_status) VALUES (?, ?)");
$insertQuery->bind_param("is", $schedule_id, $new_status);

if ($insertQuery->execute()) {
    $_SESSION['success'] = "Delivery status updated successfully";
} else {
    $_SESSION['error'] = "Failed to update delivery status: " . $con->error;
}

// Redirect back to schedules page
header("Location: ../schedules.php");
exit();
?>