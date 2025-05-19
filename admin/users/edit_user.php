<?php
$title = "Edit User";
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
    SELECT u.user_id, u.username, u.role, u.account_status,
           COALESCE(a.full_name, c.full_name, d.full_name, h.full_name, '') AS full_name,
           COALESCE(a.email, c.email, d.email, h.email, '') AS email,
           COALESCE(a.contact_no, c.contact_no, d.contact_no, h.contact_no, '') AS contact_no
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error_message'] = "Invalid CSRF token.";
        header("Location: edit_user.php?id=$user_id");
        exit();
    }

    $username = trim($_POST['username'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $contact_no = trim($_POST['contact_no'] ?? '');

    if (empty($username) || empty($full_name) || empty($email) || empty($contact_no)) {
        $_SESSION['error_message'] = "All fields are required.";
        header("Location: edit_user.php?id=$user_id");
        exit();
    }

    $con->begin_transaction();
    try {
        $updateUser = $con->prepare("UPDATE users SET username = ? WHERE user_id = ?");
        $updateUser->bind_param("si", $username, $user_id);
        if (!$updateUser->execute()) {
            throw new Exception("Failed to update username.");
        }

        $table = match ($user['role']) {
            'admin' => 'admins',
            'customer' => 'customers',
            'driver' => 'drivers',
            'helper' => 'helpers',
            default => throw new Exception("Invalid role.")
        };

        $updateDetails = $con->prepare("UPDATE $table SET full_name = ?, email = ?, contact_no = ? WHERE user_id = ?");
        $updateDetails->bind_param("sssi", $full_name, $email, $contact_no, $user_id);
        if (!$updateDetails->execute()) {
            throw new Exception("Failed to update user details.");
        }

        $updateActivity = $con->prepare("UPDATE users SET last_activity = NOW() WHERE user_id = ?");
        $updateActivity->bind_param("i", $_SESSION['user_id']);
        $updateActivity->execute();

        $con->commit();
        $_SESSION['success_message'] = "User details updated successfully.";
        header("Location: ../users.php");
        exit();
    } catch (Exception $e) {
        $con->rollback();
        error_log("Edit user error for user $user_id: " . $e->getMessage());
        $_SESSION['error_message'] = "Error updating user: " . $e->getMessage();
        header("Location: edit_user.php?id=$user_id");
        exit();
    }
}
?>

<div class="card">
    <div class="card-header">
        <h1>Edit User</h1>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['error_message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="full_name" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="full_name" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="contact_no" class="form-label">Contact Number</label>
                <input type="text" class="form-control" id="contact_no" name="contact_no" value="<?= htmlspecialchars($user['contact_no']) ?>" required>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Changes</button>
                <a href="../users.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php
$con->close();
$content = ob_get_clean();
include "../../layout/admin_layout.php";
?>