<?php
require '../dbcon.php';
$title = "Payroll Management";
$activePage = "payroll";
ob_start();
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if (strtolower($_SESSION['role']) !== "admin") {
    header("Location: ../unauthorized.php");
    exit();
}

// Get all available pay periods - added limit to improve efficiency
$pay_periods = [];
$period_query = "SELECT DISTINCT pay_period FROM payroll_settings ORDER BY pay_period DESC LIMIT 12";
$period_result = mysqli_query($con, $period_query);
while ($row = mysqli_fetch_assoc($period_result)) {
    $pay_periods[] = $row['pay_period'];
}

// Get selected period from URL or use most recent
$selected_period = isset($_GET['period']) && in_array($_GET['period'], $pay_periods) 
    ? $_GET['period'] 
    : ($pay_periods[0] ?? date('Y-m'));
$start_date = date('Y-m-01', strtotime($selected_period));
$end_date = date('Y-m-t', strtotime($selected_period));

// Pre-fetch delivery data to avoid multiple queries in the loop
$driver_delivery_data = [];
$helper_delivery_data = [];

// Get driver delivery data in a single query
$driver_delivery_sql = "SELECT 
    s.driver_id,
    COUNT(d.delivery_id) AS delivery_count,
    COALESCE(SUM(s.distance_km), 0) AS total_distance
    FROM deliveries d
    JOIN schedules s ON d.schedule_id = s.schedule_id
    WHERE d.delivery_status = 'Received'
    AND d.delivery_datetime BETWEEN '$start_date' AND '$end_date'
    GROUP BY s.driver_id";
$driver_delivery_result = mysqli_query($con, $driver_delivery_sql);
while ($row = mysqli_fetch_assoc($driver_delivery_result)) {
    $driver_delivery_data[$row['driver_id']] = [
        'delivery_count' => $row['delivery_count'],
        'total_distance' => $row['total_distance']
    ];
}

// Get helper delivery data in a single query
$helper_delivery_sql = "SELECT 
    s.helper_id,
    COUNT(d.delivery_id) AS delivery_count
    FROM deliveries d
    JOIN schedules s ON d.schedule_id = s.schedule_id
    WHERE d.delivery_status = 'Received'
    AND d.delivery_datetime BETWEEN '$start_date' AND '$end_date'
    GROUP BY s.helper_id";
$helper_delivery_result = mysqli_query($con, $helper_delivery_sql);
while ($row = mysqli_fetch_assoc($helper_delivery_result)) {
    $helper_delivery_data[$row['helper_id']] = $row['delivery_count'];
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Payroll Management</h1>
        <div>
            <a href="generate_payroll.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Generate Payroll
            </a>
            <a href="payroll_report.php" class="btn btn-secondary">
                <i class="bi bi-file-earmark-text"></i> View Reports
            </a>
            <a href="payroll_config.php" class="btn btn-warning">
                <i class="bi bi-gear"></i> Payroll Settings
            </a>
        </div>
    </div>

    <!-- Period Selection -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold">Select Pay Period</h6>
        </div>
        <div class="card-body">
            <form method="get" class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Pay Period:</label>
                        <select class="form-control" name="period" onchange="this.form.submit()">
                            <?php foreach ($pay_periods as $period): ?>
                                <option value="<?= htmlspecialchars($period) ?>" <?= $period == $selected_period ? 'selected' : '' ?>>
                                    <?= date('F Y', strtotime($period)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Driver Payroll Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold">Driver Payroll Records</h6>
            <span class="badge bg-primary">Drivers</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="driverPayrollTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>Driver</th>
                            <th>Pay Period</th>
                            <th>Base Salary</th>
                            <th>Deliveries</th>
                            <th>Distance Earnings</th>
                            <th>Distance (km)</th>
                            <th>Deductions</th>
                            <th>Net Pay</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Get driver payroll with proper delivery counts
                        $driver_sql = "SELECT p.*, d.full_name 
                                     FROM payroll p
                                     JOIN drivers d ON p.driver_id = d.driver_id
                                     WHERE p.pay_period_start = '$start_date' 
                                     AND p.pay_period_end = '$end_date'
                                     ORDER BY p.payment_status, d.full_name";
                        $driver_result = mysqli_query($con, $driver_sql);

                        while($row = mysqli_fetch_assoc($driver_result)):
                            $driver_id = $row['driver_id'];
                            $delivery_count = $driver_delivery_data[$driver_id]['delivery_count'] ?? 0;
                            $total_distance = $driver_delivery_data[$driver_id]['total_distance'] ?? 0;
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($row['full_name']) ?></td>
                            <td>
                                <?= date('M j, Y', strtotime($row['pay_period_start'])) ?><br>
                                <small>to <?= date('M j, Y', strtotime($row['pay_period_end'])) ?></small>
                            </td>
                            <td class="text-end">₱<?= number_format($row['base_salary'], 2) ?></td>
                            <td class="text-center"><?= $delivery_count ?></td>
                            <td class="text-end">₱<?= number_format($row['bonuses'], 2) ?></td>
                            <td class="text-end"><?= number_format($total_distance, 2) ?> km</td>
                            <td class="text-end">₱<?= number_format($row['deductions'], 2) ?></td>
                            <td class="text-end font-weight-bold">₱<?= number_format($row['net_pay'], 2) ?></td>
                            <td>
                                <span class="badge <?= $row['payment_status'] == 'Paid' ? 'bg-success' : 'bg-warning' ?>">
                                    <?= $row['payment_status'] ?>
                                    <?php if($row['payment_status'] == 'Paid'): ?>
                                        <br><small><?= date('M j, Y', strtotime($row['payment_date'])) ?></small>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <?php if($row['payment_status'] == 'Pending'): ?>
                                        <a href="payroll/mark_driver_paid.php?id=<?= $row['payroll_id'] ?>" 
                                           class="btn btn-sm btn-success" 
                                           title="Mark as Paid">
                                            <i class="bi bi-cash-coin"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="payroll_details.php?id=<?= $row['payroll_id'] ?>" 
                                       class="btn btn-sm btn-info" 
                                       title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="payroll/print_driver_payslip.php?id=<?= $row['payroll_id'] ?>" 
                                       class="btn btn-sm btn-primary" 
                                       title="Print Payslip" 
                                       target="_blank">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Helper Payroll Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold">Helper Payroll Records</h6>
            <span class="badge bg-secondary">Helpers</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="helperPayrollTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>Helper</th>
                            <th>Pay Period</th>
                            <th>Base Salary</th>
                            <th>Deliveries</th>
                            <th>Commission (10%)</th>
                            <th>Deductions</th>
                            <th>Net Pay</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Get helper payroll with proper delivery counts
                        $helper_sql = "SELECT p.*, h.full_name 
                                     FROM helper_payroll p
                                     JOIN helpers h ON p.helper_id = h.helper_id
                                     WHERE p.pay_period_start = '$start_date' 
                                     AND p.pay_period_end = '$end_date'
                                     ORDER BY p.payment_status, h.full_name";
                        $helper_result = mysqli_query($con, $helper_sql);

                        while($row = mysqli_fetch_assoc($helper_result)):
                            $helper_id = $row['helper_id'];
                            $delivery_count = $helper_delivery_data[$helper_id] ?? 0;
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($row['full_name']) ?></td>
                            <td>
                                <?= date('M j, Y', strtotime($row['pay_period_start'])) ?><br>
                                <small>to <?= date('M j, Y', strtotime($row['pay_period_end'])) ?></small>
                            </td>
                            <td class="text-end">₱<?= number_format($row['base_salary'], 2) ?></td>
                            <td class="text-center"><?= $delivery_count ?></td>
                            <td class="text-end">₱<?= number_format($row['bonuses'], 2) ?></td>
                            <td class="text-end">₱<?= number_format($row['deductions'], 2) ?></td>
                            <td class="text-end font-weight-bold">₱<?= number_format($row['net_pay'], 2) ?></td>
                            <td>
                                <span class="badge <?= $row['payment_status'] == 'Paid' ? 'bg-success' : 'bg-warning' ?>">
                                    <?= $row['payment_status'] ?>
                                    <?php if($row['payment_status'] == 'Paid'): ?>
                                        <br><small><?= date('M j, Y', strtotime($row['payment_date'])) ?></small>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <?php if($row['payment_status'] == 'Pending'): ?>
                                        <a href="payroll/mark_helper_paid.php?id=<?= $row['payroll_id'] ?>" 
                                           class="btn btn-sm btn-success" 
                                           title="Mark as Paid">
                                            <i class="bi bi-cash-coin"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="helper_payroll_details.php?id=<?= $row['payroll_id'] ?>" 
                                       class="btn btn-sm btn-info" 
                                       title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="payroll/print_helper_payslip.php?id=<?= $row['payroll_id'] ?>" 
                                       class="btn btn-sm btn-primary" 
                                       title="Print Payslip" 
                                       target="_blank">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        $('#driverPayrollTable, #helperPayrollTable').DataTable({
            "order": [[1, "desc"]],
            "responsive": true,
            "dom": '<"top"f>rt<"bottom"lip><"clear">',
            "language": {
                "search": "_INPUT_",
                "searchPlaceholder": "Search payroll...",
                "lengthMenu": "Show _MENU_ entries",
                "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                "paginate": {
                    "previous": "<i class='bi bi-chevron-left'></i>",
                    "next": "<i class='bi bi-chevron-right'></i>"
                }
            }
        });
    });
</script>

<?php
$content = ob_get_clean();
include "../layout/admin_layout.php";
?>