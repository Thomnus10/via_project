<?php 
$title = "Home | Joredane Trucking"; 
$activePage = "home"; 
ob_start(); 
?>

<!-- Hero Banner Section -->
<div class="container-fluid p-0">
    <div class="row g-0">
        <div class="col-12">
            <div class="bg-primary text-white py-5 text-center" style="background: linear-gradient(rgba(54, 76, 132, 0.9), rgba(54, 76, 132, 0.8)), url('https://via.placeholder.com/1920x600') no-repeat center center; background-size: cover;">
                <div class="container py-4">
                    <h1 class="display-4 fw-bold mb-3 text-light"><i class="bi bi-truck-front-fill me-2"></i>JOREDANE TRUCKING</h1>
                    <p class="lead mb-4">Premium Delivery Solutions in Bulacan</p>
                    <div class="d-flex justify-content-center mb-4">
                        <span class="badge bg-primary px-3 py-2 me-2">Professional</span>
                        <span class="badge bg-success px-3 py-2 me-2">Reliable</span>
                        <span class="badge bg-warning px-3 py-2">On-time</span>
                    </div>
                    <div class="d-flex justify-content-center mt-4">
                        <a href="booking.php" class="btn btn-light btn-lg me-3 px-4">
                            <i class="bi bi-calendar-check me-2"></i>Book Now
                        </a>
                        <a href="available.php" class="btn btn-outline-light btn-lg px-4">
                            <i class="bi bi-calendar-date me-2"></i>Check Availability
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Key Information Section -->
<div class="container my-5">
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="text-center p-4 h-100 shadow-sm rounded" style="background-color: rgba(54, 76, 132, 0.05);">
                <i class="bi bi-cash-coin text-primary mb-3" style="font-size: 2.5rem;"></i>
                <h3 class="h5">Standard Rate</h3>
                <p class="mb-0">₱10,000 per delivery</p>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="text-center p-4 h-100 shadow-sm rounded" style="background-color: rgba(54, 76, 132, 0.05);">
                <i class="bi bi-alarm text-primary mb-3" style="font-size: 2.5rem;"></i>
                <h3 class="h5">Pickup Schedule</h3>
                <p class="mb-0">Starting from 6:00 AM daily</p>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="text-center p-4 h-100 shadow-sm rounded" style="background-color: rgba(54, 76, 132, 0.05);">
                <i class="bi bi-clock-history text-primary mb-3" style="font-size: 2.5rem;"></i>
                <h3 class="h5">Delivery Completion</h3>
                <p class="mb-0">Before 6:00 PM same day</p>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="text-center p-4 h-100 shadow-sm rounded" style="background-color: rgba(54, 76, 132, 0.05);">
                <i class="bi bi-geo-alt text-primary mb-3" style="font-size: 2.5rem;"></i>
                <h3 class="h5">Service Coverage</h3>
                <p class="mb-0">Throughout Bulacan Province</p>
            </div>
        </div>
    </div>
</div>

<!-- Services Section -->
<div class="container-fluid py-5" style="background-color: #f8f9fa;">
    <div class="container">
        <h2 class="text-center mb-5" style="color: #364C84;">Our Premium Services</h2>
        <div class="row g-4">
            <div class="col-lg-4 mb-4">
                <div class="bg-white p-4 rounded h-100 shadow-sm">
                    <div class="text-center mb-3">
                        <i class="bi bi-rocket-takeoff text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <h3 class="h4 text-center mb-3">Express Delivery</h3>
                    <p class="text-center">Same-day delivery guaranteed with real-time tracking options for your peace of mind.</p>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="bg-white p-4 rounded h-100 shadow-sm">
                    <div class="text-center mb-3">
                        <i class="bi bi-shield-check text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <h3 class="h4 text-center mb-3">Secure Transport</h3>
                    <p class="text-center">Advanced security measures ensure your goods arrive safely and in perfect condition.</p>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="bg-white p-4 rounded h-100 shadow-sm">
                    <div class="text-center mb-3">
                        <i class="bi bi-people text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <h3 class="h4 text-center mb-3">Professional Team</h3>
                    <p class="text-center">Our experienced staff handles your deliveries with the highest level of professionalism.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Features & Testimonial Section -->
<div class="container my-5">
    <div class="row g-4">
        <div class="col-md-6 mb-4">
            <div class="border-start border-primary border-5 px-4 h-100">
                <h3 class="mb-4" style="color: #364C84;"><i class="bi bi-star-fill me-2" style="color: #FFD700;"></i>Why Choose Us?</h3>
                <ul class="list-unstyled">
                    <li class="d-flex align-items-center mb-3">
                        <i class="bi bi-check-circle-fill me-3 text-primary"></i>
                        <span>Timely pickup and delivery</span>
                    </li>
                    <li class="d-flex align-items-center mb-3">
                        <i class="bi bi-check-circle-fill me-3 text-primary"></i>
                        <span>Competitive rates with no hidden fees</span>
                    </li>
                    <li class="d-flex align-items-center mb-3">
                        <i class="bi bi-check-circle-fill me-3 text-primary"></i>
                        <span>Reliable service across Bulacan</span>
                    </li>
                    <li class="d-flex align-items-center">
                        <i class="bi bi-check-circle-fill me-3 text-primary"></i>
                        <span>Dedicated customer support</span>
                    </li>
                </ul>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="bg-light p-4 rounded h-100">
                <h3 class="mb-4" style="color: #364C84;"><i class="bi bi-chat-quote-fill me-2" style="color: #FFD700;"></i>Customer Testimonial</h3>
                <div class="p-3 mb-3 bg-white rounded">
                    <p class="fst-italic mb-0">"Joredane Trucking has been our trusted delivery partner for over 2 years. Their reliable service and professional team make every delivery smooth and worry-free."</p>
                </div>
                <div class="d-flex align-items-center mt-3">
                    <i class="bi bi-person-circle text-primary me-3" style="font-size: 2.5rem;"></i>
                    <div>
                        <strong>Juan Dela Cruz</strong><br>
                        <small class="text-muted">Business Owner, Malolos</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CTA Section -->
<div class="container mb-5">
    <div class="row">
        <div class="col-12">
            <div class="text-white p-5 rounded" style="background: linear-gradient(rgba(54, 76, 132, 0.9), rgba(54, 76, 132, 0.8)), url('https://via.placeholder.com/1920x600') no-repeat center center; background-size: cover;">
                <div class="row align-items-center">
                    <div class="col-lg-8 mb-3 mb-lg-0">
                        <h2>Ready to Schedule Your Delivery?</h2>
                        <p class="lead mb-0">Experience premium trucking services with Joredane – where reliability meets excellence.</p>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <a href="booking.php" class="btn btn-light me-2 mb-2 mb-md-0">
                            <i class="bi bi-calendar-check me-2"></i>Book Now
                        </a>
                        <a href="available.php" class="btn btn-outline-light">
                            <i class="bi bi-calendar-date me-2"></i>Check Availability
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Service Areas Section -->
<div class="container mb-5">
    <div class="row">
        <div class="col-12 text-center mb-4">
            <p class="text-muted">
                <i class="bi bi-geo-alt me-1"></i>Serving all cities and municipalities in Bulacan
            </p>
            <div class="d-flex justify-content-center flex-wrap">
                <span class="badge bg-light text-dark m-1 p-2">Malolos</span>
                <span class="badge bg-light text-dark m-1 p-2">San Jose del Monte</span>
                <span class="badge bg-light text-dark m-1 p-2">Meycauayan</span>
                <span class="badge bg-light text-dark m-1 p-2">Obando</span>
                <span class="badge bg-light text-dark m-1 p-2">Marilao</span>
                <span class="badge bg-light text-dark m-1 p-2">Bocaue</span>
            </div>
        </div>
    </div>
</div>

<?php
// Add animation script
echo '<script>
$(document).ready(function() {
    // Add animation to elements using jQuery
    $(".bg-primary.text-white.py-5").addClass("animate__animated animate__fadeIn");
    $(".shadow-sm").addClass("animate__animated animate__fadeInUp");
    
    // Add hover effects for service boxes
    $(".shadow-sm").hover(
        function() {
            $(this).css("transform", "translateY(-10px)");
            $(this).css("transition", "transform 0.3s ease");
        },
        function() {
            $(this).css("transform", "translateY(0)");
        }
    );
});
</script>';

$content = ob_get_clean();
include "../layout/client_layout.php";
?>