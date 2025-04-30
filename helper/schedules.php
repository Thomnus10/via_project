<?php
$title = "Today's Schedules";
$activePage = "schedules";
session_start();
ob_start();
include '../dbcon.php';

// Check if user is logged in and is a helper
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'helper') {
    header("Location: ../login.php");
    exit();
}

// Handle payment status update
if (isset($_POST['update_payment'])) {
    $schedule_id = $_POST['schedule_id'];
    $payment_status = $_POST['payment_status'];
    
    // Check if payment record exists
    $check_stmt = $con->prepare("SELECT payment_id FROM payments WHERE schedule_id = ?");
    $check_stmt->bind_param("i", $schedule_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        // Update existing payment
        $update_stmt = $con->prepare("UPDATE payments SET status = ?, date = CURRENT_TIMESTAMP WHERE schedule_id = ?");
        $update_stmt->bind_param("si", $payment_status, $schedule_id);
        $update_stmt->execute();
    } else {
        // Create new payment record
        $insert_stmt = $con->prepare("INSERT INTO payments (schedule_id, total_amount, status) VALUES (?, 10000.00, ?)");
        $insert_stmt->bind_param("is", $schedule_id, $payment_status);
        $insert_stmt->execute();
    }
    
    // Redirect to refresh the page
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Get helper information
$helper_id = $_SESSION['user_id'];
$stmt = $con->prepare("SELECT h.helper_id, h.full_name FROM helpers h JOIN users u ON h.user_id = u.user_id WHERE u.user_id = ?");
$stmt->bind_param("i", $helper_id);
$stmt->execute();
$result = $stmt->get_result();
$helper = $result->fetch_assoc();

// Get helper's schedules
$stmt = $con->prepare("
    SELECT s.schedule_id, s.start_time, s.end_time, s.pick_up, s.destination, 
           t.truck_no, c.full_name AS customer_name, d.full_name AS driver_name,
           dl.delivery_status, p.status AS payment_status, p.payment_id
    FROM schedules s
    JOIN trucks t ON s.truck_id = t.truck_id
    JOIN customers c ON s.customer_id = c.customer_id
    JOIN drivers d ON s.driver_id = d.driver_id
    LEFT JOIN deliveries dl ON s.schedule_id = dl.schedule_id
    LEFT JOIN payments p ON s.schedule_id = p.schedule_id
    WHERE s.helper_id = ?
    ORDER BY s.start_time ASC
");
$stmt->bind_param("i", $helper['helper_id']);
$stmt->execute();
$schedules = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div class="container">
    <h2 class="mb-4">Welcome, <?php echo htmlspecialchars($helper['full_name']); ?></h2>
    <h3 class="mb-4">Delivery Schedules</h3>

    <?php if (empty($schedules)): ?>
        <div class="alert alert-info">You currently have no assigned delivery schedules.</div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($schedules as $schedule): ?>
                <?php
                // Determine status class
                $statusClass = '';
                if ($schedule['delivery_status'] === 'Pending') {
                    $statusClass = 'status-pending';
                } elseif ($schedule['delivery_status'] === 'In Transit') {
                    $statusClass = 'status-in-transit';
                } elseif ($schedule['delivery_status'] === 'Completed' || $schedule['delivery_status'] === 'Delivered') {
                    $statusClass = 'status-completed';
                } elseif ($schedule['delivery_status'] === 'Cancelled') {
                    $statusClass = 'status-cancelled';
                }
                ?>

                <div class="col-md-6 mb-4">
                    <div class="card <?php echo $statusClass; ?>">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                Delivery #<?php echo htmlspecialchars($schedule['schedule_id']); ?>
                                <span class="badge bg-secondary float-end">
                                    <?php echo htmlspecialchars($schedule['delivery_status']); ?>
                                </span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="card-text">
                                <strong>Truck:</strong> <?php echo htmlspecialchars($schedule['truck_no']); ?><br>
                                <strong>Driver:</strong> <?php echo htmlspecialchars($schedule['driver_name']); ?><br>
                                <strong>Customer:</strong> <?php echo htmlspecialchars($schedule['customer_name']); ?><br>
                                <strong>Pick-up:</strong> <?php echo htmlspecialchars($schedule['pick_up']); ?><br>
                                <strong>Destination:</strong> <?php echo htmlspecialchars($schedule['destination']); ?><br>
                                <strong>Start Time:</strong> <?php echo date('M j, Y g:i A', strtotime($schedule['start_time'])); ?><br>
                                <strong>End Time:</strong> <?php echo date('M j, Y g:i A', strtotime($schedule['end_time'])); ?><br>
                                <strong>Payment Status:</strong> 
                                <span class="badge <?php echo ($schedule['payment_status'] === 'Paid') ? 'bg-success' : 'bg-warning'; ?>">
                                    <?php echo htmlspecialchars($schedule['payment_status'] ?? 'Pending'); ?>
                                </span>
                            </p>
                            
                            <div class="d-flex flex-wrap gap-2">
                                <?php if ($schedule['delivery_status'] === 'Completed' || $schedule['delivery_status'] === 'Delivered'): ?>
                                    <a href="print_receipt.php?schedule_id=<?php echo $schedule['schedule_id']; ?>" class="btn btn-primary" target="_blank">
                                        <i class="fas fa-print"></i> Print Receipt
                                    </a>
                                    
                                    <?php if (!isset($schedule['payment_status']) || $schedule['payment_status'] === 'Pending'): ?>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="schedule_id" value="<?php echo $schedule['schedule_id']; ?>">
                                            <input type="hidden" name="payment_status" value="Paid">
                                            <button type="submit" name="update_payment" class="btn btn-success">
                                                <i class="fas fa-check-circle"></i> Mark as Paid
                                            </button>
                                        </form>
                                    <?php elseif ($schedule['payment_status'] === 'Paid'): ?>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="schedule_id" value="<?php echo $schedule['schedule_id']; ?>">
                                            <input type="hidden" name="payment_status" value="Pending">
                                            <button type="submit" name="update_payment" class="btn btn-warning">
                                                <i class="fas fa-times-circle"></i> Mark as Unpaid
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<?php
$content = ob_get_clean();
include "../layout/helper_layout.php";