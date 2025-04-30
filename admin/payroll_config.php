<?php
$title = "Payroll Settings";
$activePage = "payroll";
ob_start();
require '../dbcon.php';
session_start();

// Check admin login
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== "admin") {
    header("Location: ../login.php");
    exit();
}

// Initialize
$message = '';
$alert_class = '';
$form_data = []; // To store submitted form data in case of error

// Get current settings if they exist
$current_year = date('Y');
$current_month = date('m');
$current_period = "$current_year-$current_month";

$current_settings = null;
try {
    $stmt = $con->prepare("SELECT * FROM payroll_settings WHERE pay_period = ?");
    $stmt->bind_param("s", $current_period);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $current_settings = $result->fetch_assoc();
    }
    $stmt->close();
} catch (Exception $e) {
    // Just continue if there's an error
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capture the inputs
    $form_data = $_POST; // Store all submitted form data
    $pay_period = $_POST['year'] . '-' . $_POST['month']; // Example: 2025-04
    $driver_salary = (float)$_POST['driver_base_salary'];
    $helper_salary = (float)$_POST['helper_base_salary'];
    $sss = (float)$_POST['sss'];
    $philhealth = (float)$_POST['philhealth'];
    $pagibig = (float)$_POST['pagibig'];
    $truck_maintenance = (float)$_POST['truck_maintenance'];
    $rate_6 = (float)$_POST['rate_6'];
    $rate_8 = (float)$_POST['rate_8'];
    $rate_10 = (float)$_POST['rate_10'];
    $rate_12 = (float)$_POST['rate_12'];

    try {
        // Check if settings already exist
        $check = $con->prepare("SELECT * FROM payroll_settings WHERE pay_period = ?");
        $check->bind_param("s", $pay_period);
        $check->execute();
        $result = $check->get_result();
        $exists = $result->num_rows > 0;
        $check->close();

        if ($exists) {
            // Update existing (using SQL column names)
            $update = $con->prepare("UPDATE payroll_settings 
                SET driver_base_salary=?, helper_base_salary=?, sss_rate=?, philhealth_rate=?, pagibig_rate=?, 
                    truck_maintenance_deduction=?, rate_6w=?, rate_8w=?, rate_10w=?, rate_12w=?
                WHERE pay_period=?");
            $update->bind_param("dddddddddds", 
                $driver_salary, $helper_salary, $sss, $philhealth, $pagibig, 
                $truck_maintenance, $rate_6, $rate_8, $rate_10, $rate_12, $pay_period);
            $update->execute();
            $update->close();
            $message = "Payroll settings updated successfully.";
        } else {
            // Insert new (using SQL column names)
            $insert = $con->prepare("INSERT INTO payroll_settings 
                (pay_period, driver_base_salary, helper_base_salary, sss_rate, philhealth_rate, pagibig_rate, 
                 truck_maintenance_deduction, rate_6w, rate_8w, rate_10w, rate_12w) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $insert->bind_param("sdddddddddd", 
                $pay_period, $driver_salary, $helper_salary, $sss, $philhealth, $pagibig, 
                $truck_maintenance, $rate_6, $rate_8, $rate_10, $rate_12);
            $insert->execute();
            $insert->close();
            $message = "Payroll settings saved successfully.";
        }

        $alert_class = 'alert-success';
        $form_data = []; // Clear form data on success
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $alert_class = 'alert-danger';
        // Form data is already stored in $form_data, will be used to repopulate the form
    }
}

// Helper function to get the value for a form field
function getFormValue($field, $default = '') {
    global $form_data, $current_settings;
    
    // Map form field names to database field names where they differ
    $db_field_map = [
        'sss' => 'sss_rate',
        'philhealth' => 'philhealth_rate',
        'pagibig' => 'pagibig_rate',
        'truck_maintenance' => 'truck_maintenance_deduction',
        'rate_6' => 'rate_6w',
        'rate_8' => 'rate_8w',
        'rate_10' => 'rate_10w',
        'rate_12' => 'rate_12w'
    ];
    
    // Determine the database field name
    $db_field = isset($db_field_map[$field]) ? $db_field_map[$field] : $field;
    
    if (!empty($form_data) && isset($form_data[$field])) {
        return $form_data[$field]; // Return submitted value if available
    } elseif ($current_settings && isset($current_settings[$db_field])) {
        return $current_settings[$db_field]; // Fall back to database value
    }
    return $default; // Default value if neither is available
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-gear-fill me-2"></i>Payroll Settings</h1>
        <a href="generate_payroll.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Payroll
        </a>
    </div>

    <?php if ($message): ?>
        <div class="alert <?= $alert_class ?> alert-dismissible fade show" role="alert">
            <i class="bi <?= $alert_class == 'alert-success' ? 'bi-check-circle' : 'bi-exclamation-triangle' ?> me-2"></i>
            <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h5 class="m-0 font-weight-bold"><i class="bi bi-currency-exchange me-2"></i>Setup Payroll Configuration</h5>
            <span class="badge bg-primary">For <?= date('F Y') ?></span>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header ">
                                <h6 class="m-0"><i class="bi bi-calendar-month me-2"></i>Pay Period</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label">Month:</label>
                                            <select class="form-select" name="month" required>
                                                <?php
                                                $months = [
                                                    '01' => 'January', '02' => 'February', '03' => 'March',
                                                    '04' => 'April', '05' => 'May', '06' => 'June',
                                                    '07' => 'July', '08' => 'August', '09' => 'September',
                                                    '10' => 'October', '11' => 'November', '12' => 'December'
                                                ];
                                                
                                                foreach ($months as $num => $name) {
                                                    $selected = (isset($form_data['month']) && $form_data['month'] == $num) || 
                                                              (empty($form_data) && $num == $current_month) ? 'selected' : '';
                                                    echo "<option value='$num' $selected>$name</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Year:</label>
                                            <select class="form-select" name="year" required>
                                                <?php
                                                for ($y = $current_year - 2; $y <= $current_year + 2; $y++) {
                                                    $selected = (isset($form_data['year']) && $form_data['year'] == $y) || 
                                                              (empty($form_data) && $y == $current_year) ? 'selected' : '';
                                                    echo "<option value='$y' $selected>$y</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header ">
                                <h6 class="m-0"><i class="bi bi-cash-stack me-2"></i>Base Salary</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label">Driver Base Salary:</label>
                                            <div class="input-group">
                                                <span class="input-group-text">₱</span>
                                                <input type="number" step="0.01" class="form-control" name="driver_base_salary" 
                                                    value="<?= getFormValue('driver_base_salary') ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Helper Base Salary:</label>
                                            <div class="input-group">
                                                <span class="input-group-text">₱</span>
                                                <input type="number" step="0.01" class="form-control" name="helper_base_salary" 
                                                    value="<?= getFormValue('helper_base_salary') ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header ">
                                <h6 class="m-0"><i class="bi bi-dash-circle me-2"></i>Mandatory Deductions</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label class="form-label">SSS:</label>
                                            <div class="input-group">
                                                <span class="input-group-text">₱</span>
                                                <input type="number" step="0.01" class="form-control" name="sss" 
                                                    value="<?= getFormValue('sss') ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label class="form-label">PhilHealth:</label>
                                            <div class="input-group">
                                                <span class="input-group-text">₱</span>
                                                <input type="number" step="0.01" class="form-control" name="philhealth" 
                                                    value="<?= getFormValue('philhealth') ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label">Pag-IBIG:</label>
                                            <div class="input-group">
                                                <span class="input-group-text">₱</span>
                                                <input type="number" step="0.01" class="form-control" name="pagibig" 
                                                    value="<?= getFormValue('pagibig') ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header ">
                                <h6 class="m-0"><i class="bi bi-tools me-2"></i>Truck Maintenance</h6>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label class="form-label">Truck Maintenance Deduction:</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₱</span>
                                        <input type="number" step="0.01" class="form-control" name="truck_maintenance" 
                                            value="<?= getFormValue('truck_maintenance') ?>" required>
                                    </div>
                                    <small class="text-muted">Standard deduction for truck maintenance and repair expenses.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header ">
                                <h6 class="m-0"><i class="bi bi-truck me-2"></i>Distance Rate Per Wheeler Type</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group mb-3">
                                            <label class="form-label">6-Wheeler Rate:</label>
                                            <div class="input-group">
                                                <span class="input-group-text">₱</span>
                                                <input type="number" step="0.01" class="form-control" name="rate_6" 
                                                    value="<?= getFormValue('rate_6') ?>" required>
                                                <span class="input-group-text">/km</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-3">
                                            <label class="form-label">8-Wheeler Rate:</label>
                                            <div class="input-group">
                                                <span class="input-group-text">₱</span>
                                                <input type="number" step="0.01" class="form-control" name="rate_8" 
                                                    value="<?= getFormValue('rate_8') ?>" required>
                                                <span class="input-group-text">/km</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-3">
                                            <label class="form-label">10-Wheeler Rate:</label>
                                            <div class="input-group">
                                                <span class="input-group-text">₱</span>
                                                <input type="number" step="0.01" class="form-control" name="rate_10" 
                                                    value="<?= getFormValue('rate_10') ?>" required>
                                                <span class="input-group-text">/km</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="form-label">12-Wheeler Rate:</label>
                                            <div class="input-group">
                                                <span class="input-group-text">₱</span>
                                                <input type="number" step="0.01" class="form-control" name="rate_12" 
                                                    value="<?= getFormValue('rate_12') ?>" required>
                                                <span class="input-group-text">/km</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i> Save Settings
                    </button>
                    <a href="view_payroll_history.php" class="btn btn-outline-secondary">
                        <i class="bi bi-clock-history me-2"></i> View Previous Settings
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