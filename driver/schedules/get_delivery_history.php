<?php
session_start();
include "../../dbcon.php";

// Check if logged in and role is driver
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== "driver") {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (!isset($_GET['schedule_id']) || !is_numeric($_GET['schedule_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid schedule ID']);
    exit();
}

$schedule_id = intval($_GET['schedule_id']);
$driver_id = $_SESSION['driver_id'];

// First verify this schedule belongs to the logged-in driver
$verifyQuery = $con->prepare("SELECT schedule_id FROM schedules WHERE schedule_id = ? AND driver_id = ?");
$verifyQuery->bind_param("ii", $schedule_id, $driver_id);
$verifyQuery->execute();
$verifyResult = $verifyQuery->get_result();

if ($verifyResult->num_rows === 0) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Schedule not found or not authorized']);
    exit();
}

// Get delivery history
$historyQuery = $con->prepare("
    SELECT 
        delivery_id,
        delivery_status,
        DATE_FORMAT(delivery_datetime, '%M %d, %Y %h:%i %p') as delivery_datetime
    FROM 
        deliveries
    WHERE 
        schedule_id = ?
    ORDER BY 
        delivery_datetime DESC
");

$historyQuery->bind_param("i", $schedule_id);
$historyQuery->execute();
$historyResult = $historyQuery->get_result();

$history = [];
while ($row = $historyResult->fetch_assoc()) {
    $history[] = $row;
}

header('Content-Type: application/json');
echo json_encode($history);
exit();
?>