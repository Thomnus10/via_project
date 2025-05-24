<?php
include "../dbcon.php";
$title = "Truck Management";
$activePage = "trucks";
ob_start();
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== "admin") {
    header("Location: ../login.php");
    exit();
}

// Define standard truck types
$standard_truck_types = [
    "6 wheelers",
    "8 wheelers", 
    "10 wheelers",
    "12 wheelers"
];

// Handle truck operations
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Add/Update Truck
    if (isset($_POST['add_truck'])) {
        $truck_no = $_POST['truck_no'];
        $truck_type = $_POST['truck_type'];
        $status = $_POST['status'];
        $driver_id = !empty($_POST['driver_id']) ? $_POST['driver_id'] : null;
        $helper_id = !empty($_POST['helper_id']) ? $_POST['helper_id'] : null;
        
        $stmt = $con->prepare("INSERT INTO trucks (truck_no, truck_type, status, driver_id, helper_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssii", $truck_no, $truck_type, $status, $driver_id, $helper_id);
        $stmt->execute();
        $_SESSION['message'] = "Truck added successfully!";
        $_SESSION['message_type'] = "success";
    } 
    elseif (isset($_POST['update_truck'])) {
        $truck_id = $_POST['truck_id'];
        $truck_no = $_POST['truck_no'];
        $truck_type = $_POST['truck_type'];
        $status = $_POST['status'];
        $driver_id = !empty($_POST['driver_id']) ? $_POST['driver_id'] : null;
        $helper_id = !empty($_POST['helper_id']) ? $_POST['helper_id'] : null;
        
        $stmt = $con->prepare("UPDATE trucks SET truck_no = ?, truck_type = ?, status = ?, driver_id = ?, helper_id = ? WHERE truck_id = ?");
        $stmt->bind_param("sssiii", $truck_no, $truck_type, $status, $driver_id, $helper_id, $truck_id);
        $stmt->execute();
        $_SESSION['message'] = "Truck updated successfully!";
        $_SESSION['message_type'] = "success";
    }
    // Add Truck Type
    elseif (isset($_POST['add_truck_type'])) {
        $new_type = trim($_POST['new_type']);
        
        if (!empty($new_type)) {
            if (!in_array($new_type, $standard_truck_types)) {
                $standard_truck_types[] = $new_type;
                $_SESSION['message'] = "Truck type added successfully!";
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "Truck type already exists!";
                $_SESSION['message_type'] = "danger";
            }
        }
    }
    // Update Truck Type
    elseif (isset($_POST['update_truck_type'])) {
        $old_type = $_POST['old_type'];
        $new_type = trim($_POST['new_type']);
        
        if (!empty($new_type) && $new_type != $old_type) {
            // First update all trucks with this type
            $stmt = $con->prepare("UPDATE trucks SET truck_type = ? WHERE truck_type = ?");
            $stmt->bind_param("ss", $new_type, $old_type);
            $stmt->execute();
            
            // Update our standard types array
            if (($key = array_search($old_type, $standard_truck_types)) !== false) {
                $standard_truck_types[$key] = $new_type;
            }
            
            $_SESSION['message'] = "Truck type updated successfully!";
            $_SESSION['message_type'] = "success";
        }
    }
}

// Handle delete operations
if (isset($_GET['delete'])) {
    $truck_id = $_GET['delete'];
    $stmt = $con->prepare("DELETE FROM trucks WHERE truck_id = ?");
    $stmt->bind_param("i", $truck_id);
    $stmt->execute();
    $_SESSION['message'] = "Truck deleted successfully!";
    $_SESSION['message_type'] = "danger";
    header("Location: trucks.php");
    exit();
}

if (isset($_GET['delete_type'])) {
    $type_to_delete = $_GET['delete_type'];
    
    // Check if any trucks are using this type
    $stmt = $con->prepare("SELECT COUNT(*) FROM trucks WHERE truck_type = ?");
    $stmt->bind_param("s", $type_to_delete);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_row()[0];
    
    if ($count == 0) {
        // Remove from our standard types array
        if (($key = array_search($type_to_delete, $standard_truck_types)) !== false) {
            unset($standard_truck_types[$key]);
        }
        $_SESSION['message'] = "Truck type deleted successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Cannot delete type - $count truck(s) are using it!";
        $_SESSION['message_type'] = "danger";
    }
    header("Location: trucks.php");
    exit();
}

// Fetch data
$trucks = $con->query("
    SELECT t.truck_id, t.truck_no, t.truck_type, t.status, t.driver_id, t.helper_id,
           d.full_name AS driver_name, h.full_name AS helper_name
    FROM trucks t
    LEFT JOIN drivers d ON t.driver_id = d.driver_id
    LEFT JOIN helpers h ON t.helper_id = h.helper_id
    ORDER BY t.truck_no
") or die($con->error);

$drivers = $con->query("SELECT driver_id, full_name FROM drivers ORDER BY full_name") or die($con->error);
$helpers = $con->query("SELECT helper_id, full_name FROM helpers ORDER BY full_name") or die($con->error);

// Get all unique truck types currently in use
$existing_types = $con->query("SELECT DISTINCT truck_type FROM trucks") or die($con->error);
$all_truck_types = $standard_truck_types;
while ($row = $existing_types->fetch_assoc()) {
    if (!in_array($row['truck_type'], $all_truck_types)) {
        $all_truck_types[] = $row['truck_type'];
    }
}
sort($all_truck_types);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Truck Management</h1>
        <div>
            <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#addTruckModal">
                <i class="bi bi-plus-circle"></i> Add Truck
            </button>
            <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#manageTruckTypesModal">
                <i class="bi bi-gear"></i> Manage Types
            </button>
        </div>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?= $_SESSION['message_type'] ?> alert-dismissible fade show" role="alert">
            <?= $_SESSION['message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
    <?php endif; ?>

    <!-- Trucks Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold">All Trucks</h6>
            <div class="search-box">
                <input type="text" class="form-control form-control-sm" id="truckSearch" placeholder="Search trucks...">
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="truckTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>Truck No</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Driver</th>
                            <th>Helper</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $trucks->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['truck_no']) ?></td>
                                <td><?= htmlspecialchars($row['truck_type']) ?></td>
                                <td>
                                    <span class="badge 
                                        <?= $row['status'] == 'Available' ? 'bg-success' : 
                                           ($row['status'] == 'Booked' ? 'bg-warning text-dark' : 'bg-danger') ?>">
                                        <?= htmlspecialchars($row['status']) ?>
                                    </span>
                                </td>
                                <td><?= $row['driver_name'] ?? '<span class="text-muted">Not assigned</span>' ?></td>
                                <td><?= $row['helper_name'] ?? '<span class="text-muted">Not assigned</span>' ?></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-warning" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editTruckModal" 
                                                data-id="<?= $row['truck_id'] ?>"
                                                data-truck_no="<?= htmlspecialchars($row['truck_no']) ?>"
                                                data-truck_type="<?= htmlspecialchars($row['truck_type']) ?>"
                                                data-status="<?= htmlspecialchars($row['status']) ?>"
                                                data-driver_id="<?= $row['driver_id'] ?>"
                                                data-helper_id="<?= $row['helper_id'] ?>">
                                            <i class="bi bi-pencil"></i> 
                                        </button>
                                        <a href="?delete=<?= $row['truck_id'] ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Are you sure you want to delete this truck?')">
                                            <i class="bi bi-trash"></i> 
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

<!-- Add Truck Modal -->
<div class="modal fade" id="addTruckModal" tabindex="-1" aria-labelledby="addTruckModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title" id="addTruckModalLabel">Add New Truck</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="truck_no" class="form-label">Truck Number</label>
                        <input type="text" class="form-control" id="truck_no" name="truck_no" required>
                    </div>
                    <div class="mb-3">
                        <label for="truck_type" class="form-label">Truck Type</label>
                        <select class="form-select" id="truck_type" name="truck_type" required>
                            <?php foreach ($all_truck_types as $type): ?>
                                <option value="<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($type) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="Available">Available</option>
                            <option value="Booked">Booked</option>
                            <option value="Maintenance">Maintenance</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="driver_id" class="form-label">Driver</label>
                        <select class="form-select" id="driver_id" name="driver_id">
                            <option value="">Select Driver</option>
                            <?php 
                            $drivers->data_seek(0);
                            while($driver = $drivers->fetch_assoc()): ?>
                                <option value="<?= $driver['driver_id'] ?>">
                                    <?= htmlspecialchars($driver['full_name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="helper_id" class="form-label">Helper</label>
                        <select class="form-select" id="helper_id" name="helper_id">
                            <option value="">Select Helper</option>
                            <?php 
                            $helpers->data_seek(0);
                            while($helper = $helpers->fetch_assoc()): ?>
                                <option value="<?= $helper['helper_id'] ?>">
                                    <?= htmlspecialchars($helper['full_name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_truck" class="btn btn-primary">Add Truck</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Truck Modal -->
<div class="modal fade" id="editTruckModal" tabindex="-1" aria-labelledby="editTruckModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTruckModalLabel">Edit Truck</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="truck_id" id="edit_truck_id">
                    <div class="mb-3">
                        <label for="edit_truck_no" class="form-label">Truck Number</label>
                        <input type="text" class="form-control" id="edit_truck_no" name="truck_no" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_truck_type" class="form-label">Truck Type</label>
                        <select class="form-select" id="edit_truck_type" name="truck_type" required>
                            <?php foreach ($all_truck_types as $type): ?>
                                <option value="<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($type) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_status" class="form-label">Status</label>
                        <select class="form-select" id="edit_status" name="status" required>
                            <option value="Available">Available</option>
                            <option value="Booked">Booked</option>
                            <option value="Maintenance">Maintenance</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_driver_id" class="form-label">Driver</label>
                        <select class="form-select" id="edit_driver_id" name="driver_id">
                            <option value="">Select Driver</option>
                            <?php 
                            $drivers->data_seek(0);
                            while($driver = $drivers->fetch_assoc()): ?>
                                <option value="<?= $driver['driver_id'] ?>">
                                    <?= htmlspecialchars($driver['full_name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_helper_id" class="form-label">Helper</label>
                        <select class="form-select" id="edit_helper_id" name="helper_id">
                            <option value="">Select Helper</option>
                            <?php 
                            $helpers->data_seek(0);
                            while($helper = $helpers->fetch_assoc()): ?>
                                <option value="<?= $helper['helper_id'] ?>">
                                    <?= htmlspecialchars($helper['full_name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="update_truck" class="btn btn-primary">Update Truck</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Manage Truck Types Modal -->
<div class="modal fade" id="manageTruckTypesModal" tabindex="-1" aria-labelledby="manageTruckTypesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="manageTruckTypesModalLabel">Manage Truck Types</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTruckTypeModal">
                        <i class="bi bi-plus-circle"></i> Add New Type
                    </button>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Type Name</th>
                                <th>Usage Count</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_truck_types as $type): 
                                // Count how many trucks use this type
                                $stmt = $con->prepare("SELECT COUNT(*) FROM trucks WHERE truck_type = ?");
                                $stmt->bind_param("s", $type);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                $count = $result->fetch_row()[0];
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($type) ?></td>
                                    <td><?= $count ?></td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-sm btn-warning" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editTruckTypeModal"
                                                    data-old_type="<?= htmlspecialchars($type) ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <?php if ($count == 0 && !in_array($type, $standard_truck_types)): ?>
                                                <a href="?delete_type=<?= urlencode($type) ?>" 
                                                   class="btn btn-sm btn-danger"
                                                   onclick="return confirm('Are you sure you want to delete this truck type?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-danger" disabled title="Cannot delete standard or in-use types">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Truck Type Modal -->
<div class="modal fade" id="addTruckTypeModal" tabindex="-1" aria-labelledby="addTruckTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title" id="addTruckTypeModalLabel">Add New Truck Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="new_type" class="form-label">Type Name</label>
                        <input type="text" class="form-control" id="new_type" name="new_type" required>
                        <small class="text-muted">Example: "14 wheelers", "Refrigerated", "Flatbed"</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_truck_type" class="btn btn-primary">Add Type</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Truck Type Modal -->
<div class="modal fade" id="editTruckTypeModal" tabindex="-1" aria-labelledby="editTruckTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <input type="hidden" name="old_type" id="old_type">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTruckTypeModalLabel">Edit Truck Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="new_type" class="form-label">New Type Name</label>
                        <input type="text" class="form-control" id="new_type" name="new_type" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="update_truck_type" class="btn btn-primary">Update Type</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTable for trucks
    $('#truckTable').DataTable({
        "order": [[0, "asc"]],
        "responsive": true,
        "dom": '<"top"f>rt<"bottom"lip><"clear">',
        "language": {
            "search": "_INPUT_",
            "searchPlaceholder": "Search trucks...",
            "lengthMenu": "Show _MENU_ entries",
            "info": "Showing _START_ to _END_ of _TOTAL_ trucks",
            "paginate": {
                "previous": "<i class='bi bi-chevron-left'></i>",
                "next": "<i class='bi bi-chevron-right'></i>"
            }
        }
    });

    // Edit truck modal handler
    $('#editTruckModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        modal.find('#edit_truck_id').val(button.data('id'));
        modal.find('#edit_truck_no').val(button.data('truck_no'));
        modal.find('#edit_truck_type').val(button.data('truck_type'));
        modal.find('#edit_status').val(button.data('status'));
        modal.find('#edit_driver_id').val(button.data('driver_id'));
        modal.find('#edit_helper_id').val(button.data('helper_id'));
    });

    // Edit truck type modal handler
    $('#editTruckTypeModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        modal.find('#old_type').val(button.data('old_type'));
        modal.find('#new_type').val(button.data('old_type'));
    });
});
</script>

<?php
$content = ob_get_clean();
include "../layout/admin_layout.php";
?>