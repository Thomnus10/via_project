<?php
session_start();
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error_message'] = "Invalid CSRF token.";
        header("Location: delete_user.php?id=$user_id");
        exit();
    }

    $verifyQuery = $con->prepare("SELECT role FROM users WHERE user_id = ?");
    $verifyQuery->bind_param("i", $user_id);
    $verifyQuery->execute();
    $user = $verifyQuery->get_result()->fetch_assoc();
    $verifyQuery->close();

    if (!$user) {
        $_SESSION['error_message'] = "User not found.";
        header("Location: ../users.php");
        exit();
    }

    $con->begin_transaction();
    try {
        $table = match ($user['role']) {
            'admin' => 'admins',
            'customer' => 'customers',
            'driver' => 'drivers',
            'helper' => 'helpers',
            default => throw new Exception("Invalid role.")
        };

        $deleteDetails = $con->prepare("DELETE FROM $table WHERE user_id = ?");
        $deleteDetails->bind_param("i", $user_id);
        if (!$deleteDetails->execute()) {
            throw new Exception("Failed to delete from $table.");
        }

        $deleteUser = $con->prepare("DELETE FROM users WHERE user_id = ?");
        $deleteUser->bind_param("i", $user_id);
        if (!$deleteUser->execute()) {
            throw new Exception("Failed to delete user.");
        }

        $con->commit();
        $_SESSION['success_message'] = "User deleted successfully.";
        header("Location: ../users.php");
        exit();
    } catch (Exception $e) {
        $con->rollback();
        error_log("Delete user error for user $user_id: " . $e->getMessage());
        $_SESSION['error_message'] = "Error deleting user: " . $e->getMessage();
        header("Location: delete_user.php?id=$user_id");
        exit();
    }
}

$title = "Delete User";
$activePage = "users";
ob_start();

$query = "SELECT username, role FROM users WHERE user_id = ?";
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
?>

<div class="card">
    <div class="card-header">
        <h1>Delete User</h1>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['error_message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <p>Are you sure you want to delete the user <strong><?= htmlspecialchars($user['username']) ?></strong> (Role: <?= ucfirst($user['role']) ?>)? This action cannot be undone.</p>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-danger"><i class="bi bi-trash"></i> Confirm Delete</button>
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