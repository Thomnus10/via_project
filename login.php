<?php
session_start();
include 'dbcon.php';

// Generate CSRF token for forms
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $stmt = $con->prepare("SELECT user_id, password, role, account_status FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($userID, $hashed_password, $role, $account_status);
        $stmt->fetch();

        if ($account_status !== 'Active') {
            echo "<script>
                    alert('Your account is " . strtolower($account_status) . ". Please contact support.');
                    window.history.back();
                  </script>";
            $stmt->close();
            $con->close();
            exit();
        }

        if (password_verify($password, $hashed_password)) {
            $updateActivity = $con->prepare("UPDATE users SET last_activity = NOW() WHERE user_id = ?");
            $updateActivity->bind_param("i", $userID);
            $updateActivity->execute();
            $updateActivity->close();

            session_regenerate_id(true);
            $_SESSION["user_id"] = $userID;
            $_SESSION["role"] = $role;
            $_SESSION["account_status"] = $account_status;

            switch ($role) {
                case 'admin':
                    $redirect = 'admin/dashboard.php';
                    break;
                case 'driver':
                    $redirect = 'driver/schedules.php';
                    break;
                case 'customer':
                    $redirect = 'client/home.php';
                    break;
                case 'helper':
                    $redirect = 'helper/schedules.php';
                    break;
                default:
                    echo "<script>
                            alert('Unknown role. Contact admin.');
                            window.history.back();
                          </script>";
                    $stmt->close();
                    $con->close();
                    exit();
            }

            echo "<script>
                    alert('Log-in success!');
                    window.location.href = '$redirect';
                  </script>";
            exit();
        } else {
            echo "<script>
                    alert('Invalid password. Please try again.');
                    window.history.back();
                  </script>";
        }
    } else {
        echo "<script>
                alert('User not found. Please check your username.');
                window.history.back();
              </script>";
    }

    $stmt->close();
    $con->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Joredane Trucking Services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Bruno+Ace&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Bruno Ace', sans-serif;
            background-color: #f8f9fa;
            color: #364C84;
        }
    </style>
</head>
<body>
    <div class="container vh-100 d-flex justify-content-center align-items-center">
        <div class="row w-100 justify-content-center">
            <div class="col-md-8 col-lg-6">
                <form method="POST" action="" class="p-4 shadow rounded-4 bg-light">
                    <h2 class="text-center mb-4" style="text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.5); color: #364C84;">
                        JOREDANE TRUCKING SERVICES
                    </h2>
                    <h3 class="text-center mb-4">LOG-IN</h3>
                    <div class="mb-3 row align-items-center">
                        <label for="username" class="col-sm-4 col-form-label text-end fs-5">Username:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control rounded-pill input-shadow" id="username" name="username" required>
                        </div>
                    </div>
                    <div class="mb-4 row align-items-center">
                        <label for="password" class="col-sm-4 col-form-label text-end fs-5">Password:</label>
                        <div class="col-sm-8">
                            <input type="password" class="form-control rounded-pill input-shadow" id="password" name="password" required>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-outline-primary rounded-pill mx-2 input-shadow">Log-in</button>
                    </div>
                    <p class="text-center mt-3">
                        Don't have an account? <a href="request_account.php">Request here</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>