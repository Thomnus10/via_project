<?php
include '../dbcon.php';
$title = "Customer Account Request";
$activePage = "Requests";
ob_start();
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== "admin") {
    header("Location: ../login.php");
    exit();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require '../vendor/phpmailer/phpmailer/src/SMTP.php';
require '../vendor/phpmailer/phpmailer/src/Exception.php';

if (isset($_GET['approve_id'])) {
    $id = $_GET['approve_id'];

    // Secure SELECT query
    $stmt = $con->prepare("SELECT * FROM account_requests WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();

    if (!$data) {
        echo "Invalid request ID.";
        exit;
    }

    // Create user account (without email)
    $tempPass = password_hash("client123", PASSWORD_DEFAULT);
    $role = "customer";
    $stmt = $con->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $data['username'], $tempPass, $role);
    $stmt->execute();
    $user_id = $con->insert_id;
    $stmt->close();

    // Insert into customers table (with email)
    $stmt = $con->prepare("INSERT INTO customers (user_id, full_name, email) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $data['full_name'], $data['email']);
    $stmt->execute();
    $stmt->close();

    // Update account request status
    $stmt = $con->prepare("UPDATE account_requests SET status = 'approved' WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    // Send email
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'oalbacite@gmail.com'; // Your Gmail
        $mail->Password = 'bfcy tkae tgyy ntmt'; // Your App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('oalbacite@gmail.com', 'Jordane Trucking Services');
        $mail->addAddress($data['email'], $data['full_name']);

        $mail->isHTML(true);
        $mail->Subject = 'Account Approved';
        $mail->Body = "Hello " . htmlspecialchars($data['full_name']) . ",<br><br>Your customer account has been approved!<br><br>
            <strong>Username:</strong> " . htmlspecialchars($data['username']) . "<br>
            <strong>Password:</strong>client123<br><br>
            Please log in and change your password.";

        $mail->send();
        echo "Account approved and email sent!";
    } catch (Exception $e) {
        echo "Account approved but email failed. Error: {$mail->ErrorInfo}";
    }
}
?>

<h2>Customer Account Requests</h2>
<table border="1">
    <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Action</th>
    </tr>
    <?php
    $res = $con->query("SELECT * FROM account_requests WHERE status = 'pending'");
    while ($row = $res->fetch_assoc()) {
        echo "<tr>
            <td>{$row['full_name']}</td>
            <td>{$row['email']}</td>
            <td><a href='?approve_id={$row['id']}' class='btn btn-sm btn-outline-light'>Approve</a></td>
        </tr>";
    }
    ?>
</table>

<?php
$content = ob_get_clean();
include "../layout/admin_layout.php";
?>