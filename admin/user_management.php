<?php
$title = "User Management";
$activePage = "users";
session_start();
ob_start();
include "../dbcon.php";

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== "admin") {
    header("Location: ../login.php");
    exit();
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && isset($_POST['new_status'])) {
    $user_id = intval($_POST['user_id']);
    $new_status = $_POST['new_status'];
    
    // Verify user is a customer
    $verifyQuery = $con->prepare("SELECT role FROM users WHERE user_id = ?");
    $verifyQuery->bind_param("i", $user_id);
    $verifyQuery->execute();
    $user = $verifyQuery->get_result()->fetch_assoc();
    
    if ($user && $user['role'] === 'customer') {
        $updateQuery = $con->prepare("UPDATE users SET account_status = ? WHERE user_id = ?");
        $updateQuery->bind_param("si", $new_status, $user_id);
        if ($updateQuery->execute()) {
            $_SESSION['success_message'] = "User status updated successfully.";
        } else {
            $_SESSION['error_message'] = "Failed to update user status.";
        }
        $updateQuery->close();
    } else {
        $_SESSION['error_message'] = "Only customer accounts can have their status updated.";
    }
    $verifyQuery->close();
    header("Location: users.php");
    exit();
}

// Build query with filters
$role_filter = $_GET['role_filter'] ?? '';
$status_filter = $_GET['status_filter'] ?? '';
$search = $_GET['search'] ?? '';

$query = "
    SELECT u.user_id, u.username, u.role, u.account_status, u.last_activity,
           COALESCE(a.full_name, c.full_name, d.full_name, h.full_name, 'N/A') AS full_name,
           COALESCE(a.email, c.email, d.email, h.email, 'N/A') AS email,
           COALESCE(a.contact_no, c.contact_no, d.contact_no, h.contact_no, 'N/A') AS contact_no
    FROM users u
    LEFT JOIN admins a ON u.user_id = a.user_id
    LEFT JOIN customers c ON u.user_id = c.user_id
    LEFT JOIN drivers d ON u.user_id = d.user_id
    LEFT JOIN helpers h ON u.user_id = h.user_id
    WHERE 1=1
";

$params = [];
$types = '';

if ($role_filter) {
    $query .= " AND u.role = ?";
    $params[] = $role_filter;
    $types .= 's';
}

if ($status_filter) {
    $query .= " AND u.account_status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if ($search) {
    $query .= " AND (COALESCE(a.full_name, c.full_name, d.full_name, h.full_name, '') LIKE ? OR 
                    COALESCE(a.email, c.email, d.email, h.email, '') LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= 'ss';
}

$usersQuery = $con->prepare($query);
if (!empty($params)) {
    $usersQuery->bind_param($types, ...$params);
}
$usersQuery->execute();
$usersResult = $usersQuery->get_result();
?>

<div class="card">
    <div class="card-header">
        <h1>User Management</h1>
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['success_message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['error_message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <!-- Filter Form -->
        <div class="filter-form">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <select name="role_filter" class="form-select">
                        <option value="">All Roles</option>
                        <option value="admin" <?= $role_filter === 'admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="customer" <?= $role_filter === 'customer' ? 'selected' : '' ?>>Customer</option>
                        <option value="driver" <?= $role_filter === 'driver' ? 'selected' : '' ?>>Driver</option>
                        <option value="helper" <?= $role_filter === 'helper' ? 'selected' : '' ?>>Helper</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status_filter" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="Active" <?= $status_filter === 'Active' ? 'selected' : '' ?>>Active</option>
                        <option value="Inactive" <?= $status_filter === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                        <option value="Disabled" <?= $status_filter === 'Disabled' ? 'selected' : '' ?>>Disabled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search by name or email" value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </form>
        </div>

        <!-- Users Table -->
        <div class="fixed-header">
            <table>
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Contact No</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Activity</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $usersResult->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['user_id'] ?></td>
                            <td><?= htmlspecialchars($row['username']) ?></td>
                            <td><?= htmlspecialchars($row['full_name']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['contact_no']) ?></td>
                            <td>
                                <span class="status-badge status-<?= strtolower($row['role']) ?>">
                                    <?= ucfirst($row['role']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge status-<?= strtolower($row['account_status'] ?? 'Active') ?>">
                                    <?= htmlspecialchars($row['account_status'] ?? 'Active') ?>
                                </span>
                            </td>
                            <td>
                                <?= $row['last_activity'] ? date('M j, Y H:i', strtotime($row['last_activity'])) : 'No activity recorded' ?>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="users/view_user.php?id=<?= $row['user_id'] ?>" 
                                       class="btn btn-sm btn-info" 
                                       title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="users/edit_user.php?id=<?= $row['user_id'] ?>" 
                                       class="btn btn-sm btn-warning" 
                                       title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="users/delete_user.php?id=<?= $row['user_id'] ?>" 
                                       class="btn btn-sm btn-danger" 
                                       title="Delete"
                                       onclick="return confirm('Are you sure you want to delete this user?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                    <?php if ($row['role'] === 'customer'): ?>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to change the status to <?= $row['account_status'] === 'Active' ? 'Inactive' : ($row['account_status'] === 'Inactive' ? 'Disabled' : 'Active') ?>?');">
                                            <input type="hidden" name="user_id" value="<?= $row['user_id'] ?>">
                                            <input type="hidden" name="new_status" value="<?= $row['account_status'] === 'Active' ? 'Inactive' : ($row['account_status'] === 'Inactive' ? 'Disabled' : 'Active') ?>">
                                            <button type="submit" class="btn btn-sm btn-<?= $row['account_status'] === 'Active' ? 'warning' : ($row['account_status'] === 'Inactive' ? 'danger' : 'success') ?>" title="<?= $row['account_status'] === 'Active' ? 'Mark Inactive' : ($row['account_status'] === 'Inactive' ? 'Mark Disabled' : 'Reactivate') ?>">
                                                <i class="bi bi-<?= $row['account_status'] === 'Active' ? 'pause-circle' : ($row['account_status'] === 'Inactive' ? 'x-circle' : 'check-circle') ?>"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    <?php if ($usersResult->num_rows === 0): ?>
                        <tr>
                            <td colspan="9" class="no-schedules">No users found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$usersQuery->close();
$con->close();
$content = ob_get_clean();
include "../layout/admin_layout.php";
?>