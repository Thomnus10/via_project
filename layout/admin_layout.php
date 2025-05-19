<?php

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? htmlspecialchars($title) : "Admin Panel" ?> - Joredane Trucking Services</title>
    <link href="https://fonts.googleapis.com/css2?family=Bruno+Ace&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../../css/admin.css">

</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">JOREDANE TRUCKING SERVICES</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTop" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarTop">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link text-white" href="#"><i class="bi bi-bell"></i> Notifications</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="profile.php"><i class="bi bi-person-circle"></i> Profile</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="../logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <button class="toggle-btn" onclick="toggleSidebar()" aria-label="Toggle sidebar"><i class="bi bi-chevron-left"></i></button>
            <span>Menu</span>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link <?= $activePage === 'dashboard' ? 'active' : '' ?>">
                    <i class="bi bi-speedometer2"></i> <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="schedules.php" class="nav-link <?= $activePage === 'schedules' ? 'active' : '' ?>">
                    <i class="bi bi-calendar-event"></i> <span>Schedules</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="reports.php" class="nav-link <?= $activePage === 'reports' ? 'active' : '' ?>">
                    <i class="bi bi-graph-up-arrow"></i> <span>Reports</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="clients.php" class="nav-link <?= $activePage === 'clients' ? 'active' : '' ?>">
                    <i class="bi bi-person-lines-fill"></i> <span>Clients</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="user_management.php" class="nav-link <?= $activePage === 'users' ? 'active' : '' ?>">
                    <i class="bi bi-person-lines-fill"></i> <span>User Info</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="employees.php" class="nav-link <?= $activePage === 'employees' ? 'active' : '' ?>">
                    <i class="bi bi-people"></i> <span>Employees</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="payroll_view.php" class="nav-link <?= $activePage === 'payroll' ? 'active' : '' ?>">
                    <i class="bi bi-wallet2"></i> <span>Payrolls</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="trucks.php" class="nav-link <?= $activePage === 'trucks' ? 'active' : '' ?>">
                    <i class="bi bi-truck-front"></i> <span>Trucks</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="admin_request.php" class="nav-link <?= $activePage === 'request' ? 'active' : '' ?>">
                    <i class="bi bi-inbox"></i> <span>Requests</span>
                </a>
            </li>
        </ul>
    </div>

    <div class="main-content">
        <?php if (isset($content)) echo $content; ?>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById("sidebar").classList.toggle("collapsed");
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>