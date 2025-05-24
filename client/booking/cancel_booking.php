<?php
session_start();
include '../../dbcon.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $schedule_id = $_POST['schedule_id'];
    
    // Verify the booking belongs to the current user
    $verifyQuery = $con->prepare("
        SELECT s.schedule_id, s.start_time
        FROM schedules s
        JOIN customers c ON s.customer_id = c.customer_id
        JOIN users u ON c.user_id = u.user_id
        WHERE s.schedule_id = ? AND u.user_id = ?
    ");
    $verifyQuery->bind_param("ii", $schedule_id, $_SESSION['user_id']);
    $verifyQuery->execute();
    $booking = $verifyQuery->get_result()->fetch_assoc();
    
    if (!$booking) {
        $_SESSION['error_message'] = "Unauthorized action";
        header("Location: ../booking.php");
        exit();
    }
    
    // Check if the booking is in the future
    $now = new DateTime();
    $start_time = new DateTime($booking['start_time']);
    
    if ($start_time > $now) {
        // Start transaction
        $con->begin_transaction();
        
        try {
            // Update delivery status to Cancelled
            $updateDelivery = $con->prepare("
                INSERT INTO deliveries (schedule_id, delivery_status, delivery_datetime)
                VALUES (?, 'Cancelled', NOW())
            ");
            $updateDelivery->bind_param("i", $schedule_id);
            $updateDelivery->execute();
            
            // Update payment status to Cancelled
            $paymentQuery = $con->prepare("
                SELECT payment_id, status 
                FROM payments  
                WHERE schedule_id = ? 
                ORDER BY payment_id DESC 
                LIMIT 1
            ");
            $paymentQuery->bind_param("i", $schedule_id);
            $paymentQuery->execute();
            $paymentResult = $paymentQuery->get_result()->fetch_assoc();
            
            if ($paymentResult) {
                $updatePayment = $con->prepare("
                    UPDATE payments 
                    SET status = 'Cancelled' 
                    WHERE payment_id = ?
                ");
                $updatePayment->bind_param("i", $paymentResult['payment_id']);
                $updatePayment->execute();
            }
            
            // Commit transaction
            $con->commit();
            $_SESSION['confirmation_message'] = "Booking cancelled successfully";
            header("Location: ../booking.php");
        } catch (Exception $e) {
            $con->rollback();
            $_SESSION['error_message'] = "Cancellation failed: " . $e->getMessage();
            header("Location: ../booking.php");
        }
    } else {
        $_SESSION['error_message'] = "Cannot cancel past bookings";
        header("Location: ../booking.php");
    }
} else {
    header("Location: ../booking.php");
}
?>