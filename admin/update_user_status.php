<?php
include 'dbcon.php';

try {
    $con->begin_transaction();

    // Mark customers inactive if no activity for 30 days
    $inactiveStmt = $con->prepare("
        UPDATE users 
        SET account_status = 'Inactive'
        WHERE role = 'customer'
        AND account_status = 'Active'
        AND last_activity IS NOT NULL
        AND last_activity < NOW() - INTERVAL 30 DAY
    ");
    $inactiveStmt->execute();
    $inactiveCount = $inactiveStmt->affected_rows;
    $inactiveStmt->close();

    // Mark customers disabled if no activity for 365 days
    $disabledStmt = $con->prepare("
        UPDATE users 
        SET account_status = 'Disabled'
        WHERE role = 'customer'
        AND account_status = 'Inactive'
        AND last_activity IS NOT NULL
        AND last_activity < NOW() - INTERVAL 365 DAY
    ");
    $disabledStmt->execute();
    $disabledCount = $disabledStmt->affected_rows;
    $disabledStmt->close();

    // Mark customers with no activity for 30 days as inactive
    $noActivityStmt = $con->prepare("
        UPDATE users 
        SET account_status = 'Inactive'
        WHERE role = 'customer'
        AND account_status = 'Active'
        AND last_activity IS NULL
    ");
    $noActivityStmt->execute();
    $noActivityCount = $noActivityStmt->affected_rows;
    $noActivityStmt->close();

    $con->commit();
    error_log("User status update: $inactiveCount marked Inactive, $disabledCount marked Disabled, $noActivityCount marked Inactive (no activity).");
} catch (Exception $e) {
    $con->rollback();
    error_log("Error updating user statuses: " . $e->getMessage());
}

$con->close();
?>