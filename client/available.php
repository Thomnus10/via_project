<?php
$title = "Available Dates";
$activePage = "available";
session_start();
ob_start();
include "../dbcon.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: ../login.php");
    exit();
}

if ($_SESSION['account_status'] !== 'Active') {
    echo "<script>alert('Your account is " . strtolower($_SESSION['account_status']) . ". Please contact support.'); window.location.href = '../logout.php';</script>";
    exit();
}

$trucks = $con->prepare("SELECT truck_id, truck_no, truck_type FROM trucks WHERE status = 'Available'");
$trucks->execute();
$trucksResult = $trucks->get_result();
?>

<div class="container-fluid">
    <div class="card shadow mb-4 profile-card">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Available Trucks</h6>
        </div>
        <div class="card-body">
            <?php if ($trucksResult->num_rows > 0): ?>
                <div class="row">
                    <?php while ($truck = $trucksResult->fetch_assoc()): ?>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($truck['truck_no']) ?></h5>
                                    <p class="card-text">Type: <?= htmlspecialchars($truck['truck_type']) ?></p>
                                    <a href="booking.php?truck_id=<?= $truck['truck_id'] ?>" class="btn btn-primary">
                                        <i class="bi bi-truck"></i> Book Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="no-schedules">No trucks available at the moment.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$trucks->close();
$con->close();
$content = ob_get_clean();
include "../layout/client_layout.php";
?>