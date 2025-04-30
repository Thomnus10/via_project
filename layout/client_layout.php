<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= isset($title) ? $title : "Admin Panel" ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Bruno+Ace&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>


    <!-- Add this in the head section of your layout file -->
    <style>
        body {
            font-family: 'Bruno Ace', sans-serif !important;
            background-color: #f8f9fa;
            color: #364C84;
            transition: margin-left 0.3s ease-in-out;
            padding-top: 56px;
            /* navbar height */
        }

        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background-color: #364C84;
            z-index: 1040;
        }

        .sidebar {
            width: 250px;
            height: 100vh;
            background: linear-gradient(180deg, #364C84, #4A5C9B);
            position: fixed;
            top: 56px;
            /* below navbar */
            left: 0;
            padding: 20px;
            color: white;
            transition: width 0.3s ease-in-out;
            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            z-index: 1030;
        }

        .sidebar.collapsed {
            width: 70px;
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .toggle-btn {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            transition: transform 0.1s;
        }

        .sidebar.collapsed .toggle-btn {
            transform: rotate(180deg);
        }

        .sidebar-header span {
            transition: opacity 0.3s ease-in-out, margin-left 0.3s ease-in-out;
        }

        .sidebar.collapsed .sidebar-header span {
            opacity: 0;
            margin-left: -20px;
        }

        .nav-link {
            color: white;
            font-weight: 500;
            padding: 12px 15px;
            transition: 0.3s;
            border-radius: 5px;
            display: flex;
            align-items: center;
            gap: 15px;
            white-space: nowrap;
        }

        .nav-link i {
            font-size: 1.4rem;
            min-width: 10px;
            text-align: center;
        }

        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.2);
            color: #FFD700 !important;
        }

        .nav-link.active {
            background-color: #2F3E6E;
        }

        .sidebar.collapsed .nav-link span {
            opacity: 0;
            width: 0;
        }

        .sidebar.collapsed .nav-link {
            justify-content: center;
            padding: 12px 0;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: margin-left 0.3s;
        }

        .sidebar.collapsed+.main-content {
            margin-left: 70px;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
                padding: 10px;
            }

            .sidebar-header span {
                display: none;
            }

            .main-content {
                margin-left: 70px;
            }

            .nav-link {
                justify-content: center;
                padding: 10px;
            }

            .nav-link span {
                display: none;
            }
        }

        /* Fixed header table styles */
        .fixed-header {
            position: relative;
            width: 100%;
            overflow: auto;
            height: 100%;
            /* Set the height as needed */
        }

        .fixed-header table {
            width: 100%;
            border-collapse: collapse;
        }

        .fixed-header th {
            position: sticky;
            top: 0;
            background-color: #f8f9fa;
            /* Same as body background */
            z-index: 10;
        }

        th,
        td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: left;
        }

        .booking-form {
            max-width: 600px;
            margin: 20px auto;
            background: #f8f8f8;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .booking-form label {
            display: block;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .booking-form input[type="text"],
        .booking-form input[type="datetime-local"],
        .booking-form select {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .booking-form button {
            padding: 10px 20px;
            background-color: #007bff;
            border: none;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }

        .booking-form button:hover {
            background-color: #0056b3;
        }

        /* #bookingModal {
            display: none;
            
            position: fixed;
            z-index: 999;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        #bookingModal.show {
            display: flex;
        }

        .modal-content {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            width: 400px;
            max-width: 90%;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        } */

        #bookingModal form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        #bookingModal label {
            display: flex;
            flex-direction: column;
            font-weight: bold;
        }

        #bookingModal input,
        #bookingModal select {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        #bookingModal button {
            padding: 10px;
            background-color: #28a745;
            border: none;
            border-radius: 5px;
            color: white;
            cursor: pointer;
        }

        #bookingModal button#closeBookingModal {
            background-color: #dc3545;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-group {
            margin-bottom: 15px;
        }

        .time-display {
            display: flex;
            gap: 20px;
        }

        .time-display>div {
            flex: 1;
        }

        .time-value {
            padding: 8px;
            background: #f5f5f5;
            border-radius: 4px;
            margin-top: 5px;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        /* Aesthetic Delivery Info Styling */
        .info-section {
            height: calc(100vh - 56px);
            padding-left: 250px;
            background: linear-gradient(-45deg, #e3f2fd, #e0f7fa, #fff3e0, #f3e5f5);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        @keyframes gradientBG {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        .info-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 25px;
            padding: 40px;
            max-width: 720px;
            width: 100%;
            box-shadow: 0 20px 30px rgba(0, 0, 0, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.4);
            text-align: center;
            animation: fadeIn 1.2s ease-in-out;
            transition: all 0.3s ease;
        }

        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 40px rgba(0, 0, 0, 0.15);
        }

        .info-card h1 {
            color: #364C84;
            margin-bottom: 20px;
            font-size: 2.2rem;
        }

        .tagline {
            font-size: 1.1rem;
            margin-bottom: 30px;
            color: #5c5c5c;
            font-style: italic;
        }

        .info-item {
            font-size: 1.4rem;
            margin-bottom: 20px;
            color: #222;
        }

        .info-item i {
            color: #364C84;
            margin-right: 10px;
        }

        @media (max-width: 768px) {
            .info-section {
                padding-left: 70px;
            }

            .info-card {
                margin: 0 20px;
            }
        }

        .cancel-btn {
            padding: 5px 10px;
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .cancel-btn:hover {
            background-color: #c82333;
        }

        /* Profile-specific styles (taking precedence where duplicates exist) */
        .profile-card {
            border-radius: 15px;
            overflow: hidden;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .profile-info-item {
            padding: 15px;
            margin-bottom: 15px;
            background-color: #f8f9fa;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .profile-info-item:hover {
            background-color: #e9ecef;
            transform: translateY(-2px);
        }

        .info-label {
            font-size: 0.9rem;
            color: #6c757d;
            font-weight: 250;
        }

        .info-value {
            font-size: 1.1rem;
            color: #364C84;
            font-weight: 300;
            margin-top: 5px;
        }

        .address-value {
            white-space: pre-line;
        }

        .modal-content-profile {
            border-radius: 15px;
            overflow: hidden;
            border: none;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
        }

        .form-control {
            border-radius: 8px;
            padding: 10px 15px;
            border: 1px solid #ced4da;
        }

        .form-control:focus {
            border-color: #364C84;
            box-shadow: 0 0 0 0.25rem rgba(54, 76, 132, 0.25);
        }

        .btn-primary {
            background-color: #364C84;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
        }

        .btn-primary:hover {
            background-color: #2F3E6E;
        }

        .btn-secondary {
            border-radius: 8px;
            padding: 10px 20px;
        }
        #calendar {
            max-width: 1100px;
            margin: 30px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            margin-top: 20px;
            color: #333;
        }

        .legend {
            display: flex;
            justify-content: center;
            margin: 20px 0;
            gap: 20px;
            flex-wrap: wrap;
        }

        .legend-item {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }

        .legend-color {
            width: 20px;
            height: 20px;
            margin-right: 8px;
            border-radius: 3px;
        }

        .event-card {
            border: 1px solid #ddd;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            background-color: #f9f9f9;
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
        }
        
        .event-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .event-date {
            font-weight: bold;
            font-size: 1.2em;
            margin-bottom: 5px;
        }
        
        .event-trucks {
            color: #666;
            margin-top: 5px;
        }
        
        .event-truck-list {
            margin-top: 10px;
            padding-left: 15px;
        }
        
        .event-truck-type {
            font-weight: 600;
            margin-top: 5px;
        }
        
        .event-list {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .tab-buttons {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }
        
        .tab-button {
            padding: 10px 20px;
            margin: 0 5px;
            border: none;
            background-color: #f0f0f0;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.2s;
        }
        
        .tab-button.active {
            background-color: #4CAF50;
            color: white;
        }
        
        #list-view, #calendar-view {
            display: none;
        }
        
        .active-view {
            display: block !important;
        }
        
        .all-full-message {
            text-align: center;
            margin: 40px 0;
            color: #777;
            font-style: italic;
        }
        
        /* Tooltip styling for calendar */
        .fc-event-title {
            font-weight: bold;
        }
        
        .tooltip {
            position: absolute;
            z-index: 1000;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 300px;
            display: none;
        }
        
        .tooltip-title {
            font-weight: bold;
            margin-bottom: 5px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        
        .tooltip-content {
            font-size: 0.9em;
        }
        
        .truck-type-group {
            margin-top: 8px;
        }
        
        .truck-type-title {
            font-weight: 600;
            margin-bottom: 3px;
        }
        
        .filter-controls {
            max-width: 1100px;
            margin: 0 auto 20px auto;
            padding: 0 20px;
            display: flex;
            gap: 15px;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .filter-group {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        select.filter-select {
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        
        .filter-label {
            font-weight: 600;
        }
        
        /* Calendar day styling */
        .fc-day-future {
            background-color: #fafafa;
        }
        
        .fc-day-today {
            background-color: #e8f4ff !important;
        }
        
        .no-availability {
            background-color: #f5f5f5;
            color: #999;
            font-style: italic;
        }
        /* Add these styles to your CSS file */

/* Hero Section */
.hero-section {
    background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('../assets/images/delivery-banner.jpg');
    background-size: cover;
    background-position: center;
    color: white;
    padding: 100px 0;
    text-align: center;
    margin-bottom: 30px;
}

.hero-section h1 {
    font-weight: 700;
    margin-bottom: 20px;
}

.hero-section .btn {
    margin-top: 20px;
    padding: 10px 30px;
    font-weight: 600;
}

/* Glass Effect */
.glass {
    background: rgba(255, 255, 255, 0.25);
    backdrop-filter: blur(10px);
    border-radius: 10px;
    border: 1px solid rgba(255, 255, 255, 0.18);
    box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
    padding: 25px;
    transition: all 0.3s ease;
}

.glass:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
}

/* Info Card */
.info-card {
    margin-bottom: 30px;
}

.info-card .card-header {
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    margin-bottom: 20px;
    padding-bottom: 15px;
}

.info-card h2 {
    font-weight: 600;
    color: #343a40;
    margin-bottom: 10px;
}

.tagline {
    font-style: italic;
    color: #6c757d;
    margin-bottom: 20px;
}

.info-item {
    padding: 10px 0;
    font-size: 18px;
}

.info-item i {
    color: #0d6efd;
    margin-right: 10px;
}

/* Section Titles */
.section-title {
    position: relative;
    display: inline-block;
    padding-bottom: 10px;
    margin-bottom: 30px;
    font-weight: 700;
    color: #343a40;
}

.section-title:after {
    content: '';
    position: absolute;
    width: 50px;
    height: 3px;
    background: #0d6efd;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
}

/* Service Cards */
.service-card {
    text-align: center;
    padding: 30px 20px;
    height: 100%;
}

.service-card .icon-wrapper {
    width: 80px;
    height: 80px;
    line-height: 80px;
    font-size: 40px;
    background: rgba(13, 110, 253, 0.1);
    border-radius: 50%;
    margin: 0 auto 20px;
    color: #0d6efd;
}

.service-card h3 {
    margin-bottom: 15px;
    font-weight: 600;
}

/* Feature Cards */
.feature-card {
    padding: 25px;
    height: 100%;
}

.feature-card i {
    font-size: 40px;
    color: #0d6efd;
    margin-bottom: 15px;
}

.feature-card h3 {
    margin-bottom: 15px;
    font-weight: 600;
}

/* Testimonial Cards */
.testimonial-card {
    padding: 30px;
    height: 100%;
}

.testimonial-content {
    margin-bottom: 20px;
    position: relative;
}

.testimonial-content i {
    font-size: 30px;
    color: #0d6efd;
    opacity: 0.3;
    position: absolute;
    top: -10px;
    left: -10px;
}

.testimonial-content p {
    position: relative;
    padding-left: 20px;
    font-style: italic;
}

.testimonial-author h4 {
    margin-bottom: 5px;
    font-weight: 600;
    font-size: 18px;
}

.testimonial-author p {
    color: #6c757d;
    margin: 0;
}

/* CTA Section */
.cta-section {
    background: linear-gradient(45deg, #0d6efd, #0b5ed7);
    color: white;
    border-radius: 10px;
}

.cta-section h2 {
    font-weight: 700;
    margin-bottom: 15px;
}

.cta-buttons {
    margin-top: 25px;
}

.cta-buttons .btn-outline-primary {
    color: white;
    border-color: white;
}

.cta-buttons .btn-outline-primary:hover {
    background-color: white;
    color: #0d6efd;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .hero-section {
        padding: 60px 0;
    }
    
    .hero-section h1 {
        font-size: 2rem;
    }
    
    .info-item {
        font-size: 16px;
    }
    
    .cta-buttons .btn {
        display: block;
        width: 100%;
        margin: 10px 0;
    }
}
    </style>
</head>

<body>
    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">JOREDANE TRUCKING SERVICES | SCHEDULING </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTop">
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

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <button class="toggle-btn" onclick="toggleSidebar()"><i class="bi bi-chevron-left"></i></button>
            <span>Menu</span>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item"><a href="home.php" class="nav-link <?= $activePage === 'home' ? 'active' : '' ?>"><i class="bi bi-speedometer2"></i> <span>Home</span></a></li>
            <li class="nav-item"><a href="available.php" class="nav-link <?= $activePage === 'available' ? 'active' : '' ?>"><i class="bi bi-speedometer2"></i> <span>Available Dates</span></a></li>
            <li class="nav-item"><a href="booking.php" class="nav-link <?= $activePage === 'book' ? 'active' : '' ?>"><i class="bi bi-speedometer2"></i> <span>Start Booking</span></a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <?php if (isset($content)) echo $content; ?>
    </div>

    <!-- Scripts -->
    <script>
        function toggleSidebar() {
            document.getElementById("sidebar").classList.toggle("collapsed");
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>