<?php
$title = "Generate Payroll";
$activePage = "payroll";
ob_start();
require '../dbcon.php';
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

// Initialize variables
$message = '';
$alert_class = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['month'], $_POST['year'])) {
    $month = $_POST['month'];
    $year = $_POST['year'];
    $pay_period = $year . '-' . $month;

    // Calculate exact date range
    $start_date = date('Y-m-01', strtotime("$year-$month-01"));
    $end_date = date('Y-m-t', strtotime("$year-$month-01"));

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $con->begin_transaction();

        // Get payroll settings
        $settings = null;
        $settings_query = $con->prepare("SELECT * FROM payroll_settings WHERE pay_period = ?");
        $settings_query->bind_param("s", $pay_period);
        $settings_query->execute();
        $settings_result = $settings_query->get_result();
        
        if ($settings_result->num_rows > 0) {
            $settings = $settings_result->fetch_assoc();
        } else {
            $most_recent_query = $con->prepare("
                SELECT * FROM payroll_settings 
                WHERE pay_period < ? 
                ORDER BY pay_period DESC LIMIT 1
            ");
            $most_recent_query->bind_param("s", $pay_period);
            $most_recent_query->execute();
            $most_recent_result = $most_recent_query->get_result();
            
            if ($most_recent_result->num_rows > 0) {
                $settings = $most_recent_result->fetch_assoc();
            } else {
                throw new Exception("No payroll settings found. Please configure settings first.");
            }
        }

        // Check for existing payroll
        $check_driver = $con->prepare("SELECT payroll_id FROM payroll WHERE pay_period_start = ? AND pay_period_end = ?");
        $check_driver->bind_param("ss", $start_date, $end_date);
        $check_driver->execute();
        $check_driver_result = $check_driver->get_result();
        $check_driver->close();

        $check_helper = $con->prepare("SELECT payroll_id FROM helper_payroll WHERE pay_period_start = ? AND pay_period_end = ?");
        $check_helper->bind_param("ss", $start_date, $end_date);
        $check_helper->execute();
        $check_helper_result = $check_helper->get_result();
        $check_helper->close();

        if ($check_driver_result->num_rows > 0 || $check_helper_result->num_rows > 0) {
            throw new Exception("Payroll already exists for " . date('F Y', strtotime($start_date)));
        }

        // Get settings with proper column names
        $driver_base_salary = $settings['driver_base_salary'] ?? 0;
        $helper_base_salary = $settings['helper_base_salary'] ?? 0;
        $helper_commission_rate = 0.05;
        
        $sss_deduction = $settings['sss_rate'] ?? 0;
        $philhealth_deduction = $settings['philhealth_rate'] ?? 0;
        $pagibig_deduction = $settings['pagibig_rate'] ?? 0;
        $maintenance_per_delivery = $settings['truck_maintenance_deduction'] ?? 0;
        
        $wheeler_rates = [
            6 => $settings['rate_6w'] ?? 0,
            8 => $settings['rate_8w'] ?? 0,
            10 => $settings['rate_10w'] ?? 0,
            12 => $settings['rate_12w'] ?? 0
        ];

        // Get drivers with deliveries
        $driver_query = $con->prepare("
            SELECT d.driver_id, d.full_name, t.truck_type,
                   COUNT(del.delivery_id) as completed_deliveries,
                   COALESCE(SUM(s.distance_km), 0) as total_distance_km
            FROM drivers d
            LEFT JOIN schedules s ON s.driver_id = d.driver_id
            LEFT JOIN trucks t ON t.truck_id = s.truck_id
            LEFT JOIN deliveries del ON del.schedule_id = s.schedule_id
                AND del.delivery_status = 'Received'
                AND del.delivery_datetime BETWEEN ? AND ?
            GROUP BY d.driver_id
        ");
        $driver_query->bind_param("ss", $start_date, $end_date);
        $driver_query->execute();
        $drivers = $driver_query->get_result();

        // Get helpers with deliveries
        $helper_query = $con->prepare("
            SELECT h.helper_id, h.full_name,
                   COUNT(del.delivery_id) as completed_deliveries,
                   COALESCE(SUM(py.total_amount), 0) as delivery_revenue
            FROM helpers h
            LEFT JOIN schedules s ON s.helper_id = h.helper_id
            LEFT JOIN deliveries del ON del.schedule_id = s.schedule_id
                AND del.delivery_status = 'Received'
                AND del.delivery_datetime BETWEEN ? AND ?
            LEFT JOIN payments py ON py.schedule_id = s.schedule_id
            GROUP BY h.helper_id
        ");
        $helper_query->bind_param("ss", $start_date, $end_date);
        $helper_query->execute();
        $helpers = $helper_query->get_result();

        // Prepare payroll insert statements
        $insert_driver = $con->prepare("
            INSERT INTO payroll (
                driver_id, pay_period_start, pay_period_end, total_deliveries,
                base_salary, bonuses, sss_deduction, philhealth_deduction,
                pagibig_deduction, truck_maintenance, tax_deduction, deductions,
                net_pay, date_generated, payment_status, delivery_revenue
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'Pending', ?)
        ");

        $insert_helper = $con->prepare("
            INSERT INTO helper_payroll (
                helper_id, pay_period_start, pay_period_end, total_deliveries,
                base_salary, bonuses, sss_deduction, philhealth_deduction,
                pagibig_deduction, deductions, net_pay, payment_status, date_generated
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())
        ");
        
        $processed_drivers = 0;
        $processed_helpers = 0;

        // Process each driver
        while ($driver = $drivers->fetch_assoc()) {
            $deliveries = $driver['completed_deliveries'];
            $distance = $driver['total_distance_km'];
            
            $wheeler_count = 6;
            if (preg_match('/(\d+)\s*wheelers?/i', $driver['truck_type'], $matches)) {
                $wheeler_count = (int)$matches[1];
            }
            
            $rate_per_km = $wheeler_rates[$wheeler_count] ?? $wheeler_rates[6];
            $distance_earnings = $distance * $rate_per_km;
        
            $taxable_income = $driver_base_salary + $distance_earnings;
            $tax = calculateTax($taxable_income);
        
            // Calculate maintenance fee first
            $maintenance_fee = $deliveries * $maintenance_per_delivery;
            $total_deductions = $sss_deduction + $philhealth_deduction + $pagibig_deduction + $maintenance_fee + $tax;
        
            $net_pay = ($driver_base_salary + $distance_earnings) - $total_deductions;
        
            $insert_driver->bind_param(
                "issidddddddddd",
                $driver['driver_id'],
                $start_date,
                $end_date,
                $deliveries,
                $driver_base_salary,
                $distance_earnings,
                $sss_deduction,
                $philhealth_deduction,
                $pagibig_deduction,
                $maintenance_fee,  // Now using the variable
                $tax,
                $total_deductions,
                $net_pay,
                $distance_earnings
            );
            $insert_driver->execute();
            $processed_drivers++;
        }

        // Process each helper
        while ($helper = $helpers->fetch_assoc()) {
            $deliveries = $helper['completed_deliveries'];
            $revenue = $helper['delivery_revenue'];
            $commission = $revenue * $helper_commission_rate;

            $total_deductions = $sss_deduction + $philhealth_deduction + $pagibig_deduction;
            $net_pay = ($helper_base_salary + $commission) - $total_deductions;

            $insert_helper->bind_param(
                "issiddddddd",
                $helper['helper_id'],
                $start_date,
                $end_date,
                $deliveries,
                $helper_base_salary,
                $commission,
                $sss_deduction,
                $philhealth_deduction,
                $pagibig_deduction,
                $total_deductions,
                $net_pay
            );
            $insert_helper->execute();
            $processed_helpers++;
        }

        $driver_query->close();
        $helper_query->close();
        $insert_driver->close();
        $insert_helper->close();

        $con->commit();
        $message = "Successfully generated payroll for " . date('F Y', strtotime($start_date)) .
            " ($processed_drivers drivers and $processed_helpers helpers processed)";
        $alert_class = 'alert-success';
    } catch (Exception $e) {
        if (isset($con) && $con instanceof mysqli) {
            $con->rollback();
        }
        $message = "Error: " . $e->getMessage();
        $alert_class = 'alert-danger';
    }
}

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

<div class="container-fluid">
    <h1 class="mb-4">Generate Payroll</h1>

    <?php if ($message): ?>
        <div class="alert <?= $alert_class ?>"><?= $message ?></div>
    <?php endif; ?>

    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h5 class="m-0 font-weight-bold">Select Payroll Period</h5>
            <a href="payroll_config.php" class="btn btn-sm btn-outline-light">
                <i class="bi bi-gear"></i> Configure Settings
            </a>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="monthSelect">Select Month:</label>
                            <select class="form-control" id="monthSelect" name="month" required>
                                <?php
                                $months = [
                                    '01' => 'January', '02' => 'February', '03' => 'March',
                                    '04' => 'April', '05' => 'May', '06' => 'June',
                                    '07' => 'July', '08' => 'August', '09' => 'September',
                                    '10' => 'October', '11' => 'November', '12' => 'December'
                                ];
                                foreach ($months as $num => $name) {
                                    $selected = (date('m') == $num) ? 'selected' : '';
                                    echo "<option value='$num' $selected>$name</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="yearSelect">Select Year:</label>
                            <select class="form-control" id="yearSelect" name="year" required>
                                <?php
                                $current_year = date('Y');
                                for ($year = $current_year - 2; $year <= $current_year + 2; $year++) {
                                    $selected = ($year == $current_year) ? 'selected' : '';
                                    echo "<option value='$year' $selected>$year</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-calculator"></i> Generate Payroll
                    </button>
                    <a href="payroll_view.php" class="btn btn-secondary ml-2">
                        <i class="bi bi-list-ul"></i> View Driver Payrolls
                    </a>
                    <a href="helper_payroll_view.php" class="btn btn-secondary ml-2">
                        <i class="bi bi-list-ul"></i> View Helper Payrolls
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