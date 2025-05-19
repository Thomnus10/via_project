<?php
$title = "View User";
$activePage = "users";
session_start();
ob_start();
include "../../dbcon.php";

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== "admin") {
    header("Location: ../../login.php");
    exit();
}

$user_id = intval($_GET['id'] ?? 0);
if ($user_id <= 0) {
    $_SESSION['error_message'] = "Invalid user ID.";
    header("Location: ../users.php");
    exit();
}

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
    WHERE u.user_id = ?
";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    $_SESSION['error_message'] = "User not found.";
    header("Location: ../users.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_status'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error_message'] = "Invalid CSRF token.";
        header("Location: view_user.php?id=$user_id");
        exit();
    }

    if ($user['role'] !== 'customer') {
        $_SESSION['error_message'] = "Only customer accounts can have their status updated.";
        header("Location: view_user.php?id=$user_id");
        exit();
    }

    $new_status = in_array($_POST['new_status'], ['Active', 'Inactive', 'Disabled']) ? $_POST['new_status'] : 'Active';
    $updateQuery = $con->prepare("UPDATE users SET account_status = ? WHERE user_id = ?");
    $updateQuery->bind_param("si", $new_status, $user_id);
    if ($updateQuery->execute()) {
        $_SESSION['success_message'] = "User status updated to $new_status.";
        header("Location: view_user.php?id=$user_id");
        exit();
    } else {
        error_log("Failed to update user $user_id status: " . $updateQuery->error);
        $_SESSION['error_message'] = "Failed to update user status.";
    }
    $updateQuery->close();
}
?>

<div class="card">
    <div class="card-header">
        <h1>User Details</h1>
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
        <div class="row">
            <div class="col-md-6">
                <div class="profile-info-item">
                    <div class="info-label">User ID</div>
                    <div class="info-value"><?= $user['user_id'] ?></div>
                </div>
                <div class="profile-info-item">
                    <div class="info-label">Username</div>
                    <div class="info-value"><?= htmlspecialchars($user['username']) ?></div>
                </div>
                <div class="profile-info-item">
                    <div class="info-label">Full Name</div>
                    <div class="info-value"><?= htmlspecialchars($user['full_name']) ?></div>
                </div>
                <div class="profile-info-item">
                    <div class="info-label">Email</div>
                    <div class="info-value"><?= htmlspecialchars($user['email']) ?></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="profile-info-item">
                    <div class="info-label">Contact Number</div>
                    <div class="info-value"><?= htmlspecialchars($user['contact_no']) ?></div>
                </div>
                <div class="profile-info-item">
                    <div class="info-label">Role</div>
                    <div class="info-value">
                        <span class="status-badge status-<?= strtolower($user['role']) ?>">
                            <?= ucfirst($user['role']) ?>
                        </span>
                    </div>
                </div>
                <div class="profile-info-item">
                    <div class="info-label">Account Status</div>
                    <div class="info-value">
                        <?php if ($user['role'] === 'customer'): ?>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <select name="new_status" onchange="this.form.submit()" class="form-select form-select-sm">
                                    <option value="Active" <?= $user['account_status'] === 'Active' ? 'selected' : '' ?>>Active</option>
                                    <option value="Inactive" <?= $user['account_status'] === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                                    <option value="Disabled" <?= $user['account_status'] === 'Disabled' ? 'selected' : '' ?>>Disabled</option>
                                </select>
                            </form>
                        <?php else: ?>
                            <span class="status-badge status-<?= strtolower($user['account_status'] ?? 'active') ?>">
                                <?= htmlspecialchars($user['account_status'] ?? 'Active') ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="profile-info-item">
                    <div class="info-label">Last Activity</div>
                    <div class="info-value">
                        <?= $user['last_activity'] ? date('M j, Y H:i', strtotime($user['last_activity'])) : 'No activity recorded' ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-3">
            <a href="../users.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back to Users</a>
            <a href="edit_user.php?id=<?= $user['user_id'] ?>" class="btn btn-primary"><i class="bi bi-pencil"></i> Edit User</a>
            <a href="delete_user.php?id=<?= $user['user_id'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this user?')"><i class="bi bi-trash"></i> Delete User</a>
        </div>
    </div>
</div>

<?php
$con->close();
$content = ob_get_clean();
include "../../layout/admin_layout.php";
?>