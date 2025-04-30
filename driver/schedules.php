<?php
$title = "Today's Schedules";
$activePage = "schedules";
session_start();
ob_start();
include "../dbcon.php";

// Check if logged in and role is driver
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if (strtolower($_SESSION['role']) !== "driver") {
    header("Location: ../unauthorized.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch driver_id if not already in session
if (!isset($_SESSION['driver_id'])) {
    $driverQuery = $con->prepare("SELECT driver_id FROM drivers WHERE user_id = ?");
    $driverQuery->bind_param("i", $user_id);
    $driverQuery->execute();
    $driverResult = $driverQuery->get_result();

    if ($row = $driverResult->fetch_assoc()) {
        $_SESSION['driver_id'] = $row['driver_id'];
    } else {
        // Handle case where driver record doesn't exist
        header("Location: ../unauthorized.php");
        exit();
    }
}

$driver_id = $_SESSION['driver_id'];

// Initialize filter variables
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';

// Base SQL query
$sql = "
    SELECT 
        s.schedule_id, 
        s.start_time, 
        s.end_time, 
        s.pick_up, 
        s.destination,
        s.distance_km,
        u.username,
        t.truck_no,
        h.full_name AS helper_name,
        (SELECT delivery_status FROM deliveries 
         WHERE schedule_id = s.schedule_id 
         ORDER BY delivery_datetime DESC LIMIT 1) as delivery_status
    FROM schedules s 
    JOIN users u ON s.customer_id = u.user_id
    LEFT JOIN trucks t ON s.truck_id = t.truck_id
    LEFT JOIN helpers h ON s.helper_id = h.helper_id
    WHERE s.driver_id = ?
";

// Add filters if set
if (!empty($filter_status)) {
    $sql .= " AND (SELECT delivery_status FROM deliveries 
              WHERE schedule_id = s.schedule_id 
              ORDER BY delivery_datetime DESC LIMIT 1) = ?";
}

if (!empty($date_filter)) {
    $sql .= " AND DATE(s.start_time) = ?";
}

$sql .= " ORDER BY s.start_time DESC";

// Prepare and bind parameters
$stmt = $con->prepare($sql);

if (!empty($filter_status) && !empty($date_filter)) {
    $stmt->bind_param("iss", $driver_id, $filter_status, $date_filter);
} elseif (!empty($filter_status)) {
    $stmt->bind_param("is", $driver_id, $filter_status);
} elseif (!empty($date_filter)) {
    $stmt->bind_param("is", $driver_id, $date_filter);
} else {
    $stmt->bind_param("i", $driver_id);
}

$stmt->execute();
$schedulesResult = $stmt->get_result();

// Get all distinct dates for the filter dropdown
$dateQuery = "
    SELECT DISTINCT DATE(start_time) as schedule_date 
    FROM schedules 
    WHERE driver_id = ? 
    ORDER BY start_time DESC
";
$dateStmt = $con->prepare($dateQuery);
$dateStmt->bind_param("i", $driver_id);
$dateStmt->execute();
$datesResult = $dateStmt->get_result();

// Get delivery status options
$statusOptions = ['Pending', 'In Transit', 'Delivered', 'Completed', 'Cancelled'];
?>

<h1>Driver's Delivery Schedules</h1>

<!-- Filter Form -->
<div class="filter-section">
    <form method="GET" action="">
        <div class="filter-row">
            <div class="filter-column">
                <label for="date">Filter by Date:</label>
                <select name="date" id="date">
                    <option value="">All Dates</option>
                    <?php while ($date = $datesResult->fetch_assoc()): ?>
                        <option value="<?= $date['schedule_date'] ?>" <?= ($date_filter == $date['schedule_date']) ? 'selected' : '' ?>>
                            <?= date('F d, Y', strtotime($date['schedule_date'])) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="filter-column">
                <label for="status">Filter by Status:</label>
                <select name="status" id="status">
                    <option value="">All Statuses</option>
                    <?php foreach ($statusOptions as $status): ?>
                        <option value="<?= $status ?>" <?= ($filter_status == $status) ? 'selected' : '' ?>>
                            <?= $status ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-column">
                <button type="submit" class="filter-btn ">Apply Filters</button>
                <a href="schedules.php" class="reset-btn">Reset</a>
            </div>
        </div>
    </form>
</div>

<div class="fixed-header">
    <table border="1" cellpadding="5">
        <tr>
            <th>Schedule ID</th>
            <th>Date</th>
            <th>Customer</th>
            <th>Pick-Up</th>
            <th>Destination</th>
            <th>Distance (km)</th>
            <th>Truck No</th>
            <th>Helper</th>
            <th>Start Time</th>
            <th>End Time</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php if ($schedulesResult->num_rows > 0): ?>
            <?php while ($row = $schedulesResult->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['schedule_id'] ?></td>
                    <td><?= date('M d, Y', strtotime($row['start_time'])) ?></td>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td><?= htmlspecialchars($row['pick_up']) ?></td>
                    <td><?= htmlspecialchars($row['destination']) ?></td>
                    <td><?= $row['distance_km'] ?></td>
                    <td><?= htmlspecialchars($row['truck_no']) ?></td>
                    <td><?= htmlspecialchars($row['helper_name'] ?? 'N/A') ?></td>
                    <td><?= date('h:i A', strtotime($row['start_time'])) ?></td>
                    <td><?= date('h:i A', strtotime($row['end_time'])) ?></td>
                    <td class="status-<?= strtolower(str_replace(' ', '-', $row['delivery_status'] ?? 'Scheduled')) ?>">
                        <?= htmlspecialchars($row['delivery_status'] ?? 'Scheduled') ?>
                    </td>
                    <td>
                        <?php
                        $status = $row['delivery_status'] ?? 'Scheduled';
                        if ($status === 'Scheduled' || $status === 'Pending'): ?>
                            <form method="POST" action="schedules/update_delivery_status.php" style="display:inline;">
                                <input type="hidden" name="schedule_id" value="<?= $row['schedule_id'] ?>">
                                <input type="hidden" name="new_status" value="In Transit">
                                <button type="submit" class="btn-start">Start Delivery</button>
                            </form>
                        <?php elseif ($status === 'In Transit'): ?>
                            <form method="POST" action="schedules/update_delivery_status.php" style="display:inline;">
                                <input type="hidden" name="schedule_id" value="<?= $row['schedule_id'] ?>">
                                <input type="hidden" name="new_status" value="Delivered">
                                <button type="submit" class="btn-complete">Mark as Delivered</button>
                            </form>
                        <?php elseif ($status === 'Delivered'): ?>
                            <span class="completed">Completed</span>
                        <?php else: ?>
                            <span class="completed"><?= $status ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="12" style="text-align: center;">No schedules found</td>
            </tr>
        <?php endif; ?>
    </table>
</div>

<!-- Delivery History Modal -->
<div id="deliveryHistoryModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Delivery Status History</h2>
        <div id="deliveryHistoryContent"></div>
    </div>
</div>



<script>
// JavaScript to fetch and display delivery history
function viewDeliveryHistory(scheduleId) {
    const modal = document.getElementById('deliveryHistoryModal');
    const contentDiv = document.getElementById('deliveryHistoryContent');
    
    // Fetch delivery history using AJAX
    fetch(`schedules/get_delivery_history.php?schedule_id=${scheduleId}`)
        .then(response => response.json())
        .then(data => {
            let historyHtml = '<table border="1" cellpadding="5" style="width:100%"><tr><th>Status</th><th>Date/Time</th></tr>';
            
            if (data.length > 0) {
                data.forEach(item => {
                    historyHtml += `<tr>
                        <td>${item.delivery_status}</td>
                        <td>${item.delivery_datetime}</td>
                    </tr>`;
                });
            } else {
                historyHtml += '<tr><td colspan="2">No history available</td></tr>';
            }
            
            historyHtml += '</table>';
            contentDiv.innerHTML = historyHtml;
            modal.style.display = 'block';
        })
        .catch(error => {
            contentDiv.innerHTML = '<p>Error loading delivery history.</p>';
            modal.style.display = 'block';
        });
}

// Close modal when clicking the X
document.querySelector('.close').addEventListener('click', function() {
    document.getElementById('deliveryHistoryModal').style.display = 'none';
});

// Close modal when clicking outside of it
window.addEventListener('click', function(event) {
    const modal = document.getElementById('deliveryHistoryModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
});
</script>

<?php
$content = ob_get_clean();
include "../layout/driver_layout.php";
?>