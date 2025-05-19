<?php
$title = "Profile";
$activePage = "profile";
session_start();
ob_start();
include "../dbcon.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: ../login.php");
    exit();
}

if ($_SESSION['account_status'] !== 'Active') {
    echo "<script>alert('Your account is " . strtolower($_SESSION['account_status']) . ". Please contact support.'); window.location.href = '../logout.php';</script>";
    exit();
}

$userId = $_SESSION['user_id'];
$profileQuery = $con->prepare("SELECT u.username, c.full_name, c.email, c.contact_no, u.account_status FROM users u JOIN customers c ON u.user_id = c.user_id WHERE u.user_id = ?");
$profileQuery->bind_param("i", $userId);
$profileQuery->execute();
$profileResult = $profileQuery->get_result()->fetch_assoc();
$profileQuery->close();

if (!$profileResult) {
    echo "<div class='alert alert-danger'>No profile found.</div>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error_message'] = "Invalid CSRF token.";
        header("Location: profile.php");
        exit();
    }

    $username = trim($_POST['username'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $contact_no = trim($_POST['contact_no'] ?? '');

    if (empty($username) || empty($full_name) || empty($email) || empty($contact_no)) {
        $_SESSION['error_message'] = "All fields are required.";
        header("Location: profile.php");
        exit();
    }

    $con->begin_transaction();
    try {
        $updateUser = $con->prepare("UPDATE users SET username = ? WHERE user_id = ?");
        $updateUser->bind_param("si", $username, $userId);
        if (!$updateUser->execute()) {
            throw new Exception("Failed to update username.");
        }

        $updateCustomer = $con->prepare("UPDATE customers SET full_name = ?, email = ?, contact_no = ? WHERE user_id = ?");
        $updateCustomer->bind_param("sssi", $full_name, $email, $contact_no, $userId);
        if (!$updateCustomer->execute()) {
            throw new Exception("Failed to update customer details.");
        }

        $updateActivity = $con->prepare("UPDATE users SET last_activity = NOW() WHERE user_id = ?");
        $updateActivity->bind_param("i", $userId);
        if (!$updateActivity->execute()) {
            throw new Exception("Failed to update last activity.");
        }

        $con->commit();
        $_SESSION['success_message'] = "Profile updated successfully.";
        header("Location: profile.php");
        exit();
    } catch (Exception $e) {
        $con->rollback();
        error_log("Profile update error for user $userId: " . $e->getMessage());
        $_SESSION['error_message'] = "Error updating profile: " . $e->getMessage();
        header("Location: profile.php");
        exit();
    }
}
?>

<div class="container-fluid">
    <div class="card shadow mb-4 profile-card">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Your Profile</h6>
        </div>
        <div class="card-body">
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
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <div class="row">
                    <div class="col-md-6">
                        <div class="profile-info-item">
                            <div class="info-label">Username</div>
                            <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($profileResult['username']) ?>" required>
                        </div>
                        <div class="profile-info-item">
                            <div class="info-label">Full Name</div>
                            <input type="text" class="form-control" name="full_name" value="<?= htmlspecialchars($profileResult['full_name']) ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="profile-info-item">
                            <div class="info-label">Email</div>
                            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($profileResult['email']) ?>" required>
                        </div>
                        <div class="profile-info-item">
                            <div class="info-label">Contact Number</div>
                            <input type="text" class="form-control" name="contact_no" value="<?= htmlspecialchars($profileResult['contact_no']) ?>" required>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Changes</button>
                    <a href="home.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$con->close();
$content = ob_get_clean();
include "../layout/client_layout.php";
?>