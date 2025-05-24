<?php
session_start();
include '../../dbcon.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    $_SESSION['error_message'] = "You must be logged in as a customer.";
    header("Location: ../../login.php");
    exit();
}

if ($_SESSION['account_status'] !== 'Active') {
    $_SESSION['error_message'] = "Your account is inactive. Please contact support.";
    header("Location: ../../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['schedule_id']) || !isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error_message'] = "Invalid request";
    header("Location: ../booking.php");
    exit();
}

$schedule_id = (int)$_POST['schedule_id'];

// Get customer_id from customers table
$customerQuery = $con->prepare("SELECT customer_id FROM customers WHERE user_id = ?");
$customerQuery->bind_param("i", $_SESSION['user_id']);
$customerQuery->execute();
$customerResult = $customerQuery->get_result()->fetch_assoc();
$customer_id = $customerResult['customer_id'] ?? 0;
$customerQuery->close();

if (!$customer_id) {
    $_SESSION['error_message'] = "Customer record not found";
    header("Location: ../booking.php");
    exit();
}

// Verify latest delivery is Delivered and belongs to the customer
$checkDelivery = $con->prepare("
    SELECT d.delivery_id, d.delivery_status, s.truck_id
    FROM deliveries d
    JOIN schedules s ON d.schedule_id = s.schedule_id
    WHERE d.schedule_id = ? AND s.customer_id = ?
    ORDER BY d.delivery_id DESC LIMIT 1
");
$checkDelivery->bind_param("ii", $schedule_id, $customer_id);
$checkDelivery->execute();
$latestDelivery = $checkDelivery->get_result()->fetch_assoc();
$checkDelivery->close();

if (!$latestDelivery) {
    $_SESSION['error_message'] = "No delivery found for this schedule";
    header("Location: ../booking.php");
    exit();
}

if ($latestDelivery['delivery_status'] !== 'Delivered') {
    $_SESSION['error_message'] = "Cannot mark as received - delivery not yet completed";
    header("Location: ../booking.php");
    exit();
}

$delivery_id = $latestDelivery['delivery_id'];
$truck_id = $latestDelivery['truck_id'];

$con->begin_transaction();
try {
    // Update delivery status to Received
    $updateDelivery = $con->prepare("
        UPDATE deliveries 
        SET delivery_status = 'Received', received_date = NOW()
        WHERE delivery_id = ?
    ");
    $updateDelivery->bind_param("i", $delivery_id);
    $updateDelivery->execute();
    $updateDelivery->close();

    // Update truck status to Available
    $updateTruck = $con->prepare("UPDATE trucks SET status = 'Available' WHERE truck_id = ?");
    $updateTruck->bind_param("i", $truck_id);
    $updateTruck->execute();
    $updateTruck->close();

    // Update user's last activity
    $updateActivity = $con->prepare("UPDATE users SET last_activity = NOW() WHERE user_id = ?");
    $updateActivity->bind_param("i", $_SESSION['user_id']);
    $updateActivity->execute();
    $updateActivity->close();

    $con->commit();
    $_SESSION['confirmation_message'] = "Delivery marked as received successfully";
} catch (Exception $e) {
    $con->rollback();
    $_SESSION['error_message'] = "Error marking delivery as received";
}

header("Location: ../booking.php");
exit();
?>