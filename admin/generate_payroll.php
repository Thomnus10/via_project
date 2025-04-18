<?php
$title = "Generate Payroll";
$activePage = "payroll";
ob_start();
require '../dbcon.php';
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== "admin") {
    header("Location: ../login.php");
    exit();
}

// Initialize variables
$message = '';
$alert_class = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['month'], $_POST['year'])) {
    $month = $_POST['month'];
    $year = $_POST['year'];

    // Calculate exact date range for the selected month/year
    $start_date = date('Y-m-01', strtotime("$year-$month-01"));
    $end_date = date('Y-m-t', strtotime("$year-$month-01")); // 't' gives last day of month

    // Enable error reporting
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $con->begin_transaction();

        // 1. Check for existing payroll for this period
        $check = $con->prepare("SELECT payroll_id FROM payroll 
                               WHERE pay_period_start = ? AND pay_period_end = ?");
        $check->bind_param("ss", $start_date, $end_date);
        $check->execute();

        if ($check->get_result()->num_rows > 0) {
            throw new Exception("Payroll already exists for " . date('F Y', strtotime($start_date)));
        }

        // 2. Get all drivers with their completed deliveries and total revenue
        $driver_query = $con->prepare("
            SELECT 
                d.driver_id, 
                d.full_name,
                COUNT(del.delivery_id) as completed_deliveries,
                COALESCE(SUM(py.total_amount), 0) as delivery_revenue
            FROM drivers d
            LEFT JOIN schedules s ON s.driver_id = d.driver_id
            LEFT JOIN deliveries del ON del.schedule_id = s.schedule_id
                AND del.delivery_status = 'Completed'
                AND del.delivery_datetime BETWEEN ? AND ?
            LEFT JOIN payments py ON py.schedule_id = s.schedule_id
            GROUP BY d.driver_id
        ");
        $driver_query->bind_param("ss", $start_date, $end_date);
        $driver_query->execute();
        $drivers = $driver_query->get_result();

        // 3. Base salary configuration
        $base_salary = 8000.00; // Increased base salary
        $commission_rate = 0.10; // 10% commission

        // 4. Prepare payroll insert statement
        $insert = $con->prepare("
            INSERT INTO payroll (
                driver_id, pay_period_start, pay_period_end, total_deliveries,
                base_salary, bonuses, sss_deduction, philhealth_deduction,
                pagibig_deduction, truck_maintenance, tax_deduction, deductions,
                net_pay, date_generated, payment_status, delivery_revenue, commission_rate
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'Pending', ?, ?)
        ");

        $processed_drivers = 0;
        // 5. Process each driver's payroll
        while ($driver = $drivers->fetch_assoc()) {
            $deliveries = $driver['completed_deliveries'];
            $revenue = $driver['delivery_revenue'];
            
            // Calculate commission (10% of delivery revenue)
            $commission = $revenue * $commission_rate;
            
            // Fixed deductions (Philippine rates)
            $sss = 581.30;        // Fixed SSS contribution
            $philhealth = 450.00; // Fixed PhilHealth
            $pagibig = 100.00;    // Fixed Pag-IBIG
            
            // Maintenance fee (₱500 per delivery)
            $truck_fee = $deliveries * 500;
            
            // Tax calculation (progressive)
            $taxable_income = $base_salary + $commission;
            $tax = calculateTax($taxable_income);
            
            // Total deductions
            $total_deductions = $sss + $philhealth + $pagibig + $truck_fee + $tax;
            
            // Calculate net pay
            $net_pay = ($base_salary + $commission) - $total_deductions;

            // Insert payroll record
            $insert->bind_param(
                "issiddddddddddd",
                $driver['driver_id'],
                $start_date,
                $end_date,
                $deliveries,
                $base_salary,
                $commission,
                $sss,
                $philhealth,
                $pagibig,
                $truck_fee,
                $tax,
                $total_deductions,
                $net_pay,
                $revenue,
                $commission_rate
            );
            $insert->execute();
            $processed_drivers++;
        }

        $con->commit();
        $message = "Successfully generated payroll for " . date('F Y', strtotime($start_date)) .
            " ($processed_drivers drivers processed)";
        $alert_class = 'alert-success';
    } catch (Exception $e) {
        $con->rollback();
        $message = "Error: " . $e->getMessage();
        $alert_class = 'alert-danger';
    }
}

// Philippine tax calculation function
function calculateTax($monthly_income) {
    $annual = $monthly_income * 12;

    if ($annual <= 250000) return 0;
    elseif ($annual <= 400000) return ($annual - 250000) * 0.15 / 12;
    elseif ($annual <= 800000) return (22500 + ($annual - 400000) * 0.20) / 12;
    elseif ($annual <= 2000000) return (102500 + ($annual - 800000) * 0.25) / 12;
    elseif ($annual <= 8000000) return (402500 + ($annual - 2000000) * 0.30) / 12;
    else return (2202500 + ($annual - 8000000) * 0.35) / 12;
}
?>

<!-- Rest of your HTML remains the same -->
<div class="container-fluid">
    <h1 class="mb-4">Generate Payroll</h1>

    <?php if ($message): ?>
        <div class="alert <?= $alert_class ?>"><?= $message ?></div>
    <?php endif; ?>

    <div class="card shadow">
        <div class="card-header py-3">
            <h5 class="m-0 font-weight-bold text-primary">Select Payroll Period</h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <!-- Your existing form fields -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="monthSelect">Select Month:</label>
                            <select class="form-control" id="monthSelect" name="month" required>
                                <!-- Month options -->
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="yearSelect">Select Year:</label>
                            <select class="form-control" id="yearSelect" name="year" required>
                                <!-- Year options -->
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-calculator"></i> Generate Payroll
                    </button>
                    <a href="payroll_view.php" class="btn btn-secondary ml-2">
                        <i class="bi bi-list-ul"></i> View Existing Payrolls
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include "../layout/admin_layout.php";
?>