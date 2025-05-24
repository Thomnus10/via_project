<?php
$title = "Book";
$activePage = "book";
session_start();
ob_start();
include "../dbcon.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
  echo "<script>alert('You must log in as a customer.'); window.location.href = '../login.php';</script>";
  exit();
}

if ($_SESSION['account_status'] !== 'Active') {
  echo "<script>alert('Your account is " . strtolower($_SESSION['account_status']) . ". You cannot book until reactivated. Please contact support.'); window.location.href = '../logout.php';</script>";
  exit();
}

if (isset($_SESSION['confirmation_message'])) {
  echo "<script>alert('" . addslashes($_SESSION['confirmation_message']) . "');</script>";
  unset($_SESSION['confirmation_message']);
}
if (isset($_SESSION['error_message'])) {
  echo "<script>alert('" . addslashes($_SESSION['error_message']) . "');</script>";
  unset($_SESSION['error_message']);
}
if (isset($_GET['success']) && $_GET['success'] == 1) {
  echo "<script>alert('Booking submitted successfully!');</script>";
}
if (isset($_GET['error'])) {
  $error_message = $_GET['message'] ?? 'An error occurred. Please try again.';
  echo "<script>alert('" . addslashes($error_message) . "');</script>";
}

$userId = $_SESSION['user_id'];

$profileQuery = $con->prepare("SELECT u.username, c.full_name, c.contact_no, u.account_status FROM users u JOIN customers c ON u.user_id = c.user_id WHERE u.user_id = ?");
$profileQuery->bind_param("i", $userId);
$profileQuery->execute();
$profileResult = $profileQuery->get_result()->fetch_assoc();
if (!$profileResult) {
  echo "<div class='alert alert-danger'>No profile found.</div>";
  exit();
}

$getCustomerId = $con->prepare("SELECT customer_id FROM customers WHERE user_id = ?");
$getCustomerId->bind_param("i", $userId);
$getCustomerId->execute();
$customerId = $getCustomerId->get_result()->fetch_assoc()['customer_id'];
if (!$customerId) {
  echo "<div class='alert alert-danger'>Customer ID not found.</div>";
  exit();
}

$bookingsQuery = $con->prepare("
    SELECT s.schedule_id, s.start_time, s.end_time, s.destination, s.pick_up, s.distance_km, 
           t.truck_no, t.truck_id,
           (SELECT delivery_status FROM deliveries WHERE schedule_id = s.schedule_id ORDER BY delivery_id DESC LIMIT 1) AS delivery_status,
           (SELECT delivery_id FROM deliveries WHERE schedule_id = s.schedule_id ORDER BY delivery_id DESC LIMIT 1) AS delivery_id
    FROM schedules s
    JOIN trucks t ON s.truck_id = t.truck_id
    WHERE s.customer_id = ?
    ORDER BY s.start_time DESC
");
$bookingsQuery->bind_param("i", $customerId);
$bookingsQuery->execute();
$bookingsResult = $bookingsQuery->get_result();

$trucks = $con->query("SELECT * FROM trucks WHERE status = 'Available'");
$prefill_date = $_GET['date'] ?? '';
?>

<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Welcome, <?= htmlspecialchars($profileResult['full_name']) ?>!</h1>
  </div>

  <!-- Confirmation Modal for Delivery -->
  <div class="modal fade" id="confirmationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content modal-content-profile">
        <div class="modal-header">
          <h5 class="modal-title">Confirm Delivery Received</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="POST" action="booking/mark_received.php">
          <div class="modal-body">
            <p>Please confirm that you have received your delivery in good condition.</p>
            <p class="text-muted">This will also mark the truck as available for new bookings.</p>
            <input type="hidden" name="schedule_id" id="confirm_schedule_id">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Confirm Receipt</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="card shadow mb-4 profile-card">
    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold text-primary">Your Profile</h6>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-6">
          <div class="profile-info-item">
            <div class="info-label">Username</div>
            <div class="info-value"><?= htmlspecialchars($profileResult['username']) ?></div>
          </div>
          <div class="profile-info-item">
            <div class="info-label">Account Status</div>
            <div class="info-value">
              <span class="badge <?= $profileResult['account_status'] === 'Active' ? 'bg-success' : ($profileResult['account_status'] === 'Inactive' ? 'bg-warning text-dark' : 'bg-danger') ?>">
                <?= htmlspecialchars($profileResult['account_status']) ?>
              </span>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="profile-info-item">
            <div class="info-label">Contact No</div>
            <div class="info-value"><?= htmlspecialchars($profileResult['contact_no']) ?></div>
          </div>
        </div>
      </div>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bookingModal">
        <i class="bi bi-truck"></i> Book a Truck Delivery
      </button>
    </div>
  </div>

  <!-- Add this modal HTML -->
  <div class="modal fade" id="orderConfirmationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Confirm Booking</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>Please confirm your booking details:</p>
          <div class="card mb-3">
            <div class="card-body">
              <p><strong>Pick-Up:</strong> <span id="confirm_pick_up"></span></p>
              <p><strong>Destination:</strong> <span id="confirm_destination"></span></p>
              <p><strong>Distance:</strong> <span id="confirm_distance"></span> km</p>
              <p><strong>Date:</strong> <span id="confirm_date"></span></p>
              <p><strong>Truck:</strong> <span id="confirm_truck"></span></p>
              <hr>
              <p><strong>Total Cost:</strong> <span id="confirm_total_cost"></span></p>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" id="confirmOrderBtn" class="btn btn-primary">Confirm Booking</button>
        </div>
      </div>
    </div>
  </div>


  <!-- Booking Modal -->
  <div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content modal-content-profile">
        <div class="modal-header">
          <h5 class="modal-title" id="bookingModalLabel">Book a Truck Delivery</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="bookingForm" method="POST" action="booking/process_booking.php">
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Pick-up Location</label>
              <input type="text" class="form-control" name="pick_up" id="pick_up" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Destination</label>
              <input type="text" class="form-control" name="destination" id="destination" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Distance (km)</label>
              <input type="number" class="form-control" name="distance_km" id="distance_km" min="1" step="0.1" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Booking Date</label>
              <input type="date" class="form-control" name="booking_date" id="booking_date" value="<?= htmlspecialchars($prefill_date) ?>" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Truck</label>
              <select class="form-select" name="truck_id" id="truck_id" required>
                <?php while ($truck = $trucks->fetch_assoc()): ?>
                  <option value="<?= $truck['truck_id'] ?>"><?= htmlspecialchars($truck['truck_no']) ?> (<?= htmlspecialchars($truck['truck_type']) ?>)</option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Cost Breakdown</label>
              <div class="card">
                <div class="card-body">
                  <p class="mb-1"><strong>Base Rate:</strong> <span id="base_rate">$10,000</span></p>
                  <p class="mb-1"><strong>Extra Distance Charge:</strong> <span id="extra_charge">$0</span></p>
                  <p class="mb-0"><strong>Total Cost:</strong> <span id="total_cost">$10,000</span></p>
                </div>
              </div>
            </div>
            <input type="hidden" name="start_time" value="06:00">
            <input type="hidden" name="end_time" value="18:00">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" id="bookNowBtn">Book Now</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="card shadow mb-4 profile-card">
    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold text-primary">Your Bookings</h6>
    </div>
    <div class="card-body">
      <div class="table-responsive fixed-header">
        <table class="table table-bordered table-hover" id="bookingsTable" width="100%" cellspacing="0">
          <thead>
            <tr>
              <th>Schedule</th>
              <th>Pick-Up</th>
              <th>Destination</th>
              <th>Distance</th>
              <th>Truck</th>
              <th>Delivery Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $bookingsResult->fetch_assoc()): ?>
              <tr>
                <td>
                  <?= date('M j, Y', strtotime($row['start_time'])) ?><br>
                  <?= date('h:i A', strtotime($row['start_time'])) ?>–<?= date('h:i A', strtotime($row['end_time'])) ?>
                </td>
                <td><?= htmlspecialchars($row['pick_up']) ?></td>
                <td><?= htmlspecialchars($row['destination']) ?></td>
                <td><?= htmlspecialchars($row['distance_km']) ?> km</td>
                <td><?= htmlspecialchars($row['truck_no']) ?></td>
                <td>
                  <span class="badge <?php
                                      $status = $row['delivery_status'] ?? 'Pending';
                                      echo ($status == 'Received') ? 'bg-success' : (($status == 'Delivered') ? 'bg-info' : (($status == 'In Transit') ? 'bg-primary' : (($status == 'Cancelled') ? 'bg-danger' : 'bg-warning text-dark')));
                                      ?>">
                    <?= htmlspecialchars($status) ?>
                  </span>
                </td>
                <td>
                  <div class="d-flex gap-2 flex-wrap">
                    <?php
                    $status = $row['delivery_status'] ?? 'Pending';
                    if ($status === 'Pending'): ?>
                      <form method="POST" action="booking/cancel_booking.php" class="mb-0" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                        <input type="hidden" name="schedule_id" value="<?= $row['schedule_id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <button type="submit" class="btn btn-sm cancel-btn">
                          <i class="bi bi-x-circle"></i> Cancel
                        </button>
                      </form>
                    <?php endif; ?>
                    <?php if ($status === 'Delivered'): ?>
                      <button class="btn btn-sm btn-primary confirm-delivery"
                        data-schedule-id="<?= $row['schedule_id'] ?>">
                        <i class="bi bi-check-circle"></i> Confirm Receipt
                      </button>
                    <?php elseif ($status === 'Received'): ?>
                      <span class="text-success"><i class="bi bi-check2-circle"></i> Received</span>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
            <?php if ($bookingsResult->num_rows === 0): ?>
              <tr>
                <td colspan="7" class="no-schedules">No bookings found.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<style>
  #bookingsTable th:nth-child(1) {
    width: 20%;
  }

  /* Schedule */
  #bookingsTable th:nth-child(2) {
    width: 20%;
  }

  /* Pick-Up */
  #bookingsTable th:nth-child(3) {
    width: 20%;
  }

  /* Destination */
  #bookingsTable th:nth-child(4) {
    width: 10%;
  }

  /* Distance */
  #bookingsTable th:nth-child(5) {
    width: 15%;
  }

  /* Truck */
  #bookingsTable th:nth-child(6) {
    width: 10%;
  }

  /* Delivery Status */
  #bookingsTable th:nth-child(7) {
    width: 15%;
  }

  /* Actions */
</style>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
  $(document).ready(function() {
    $('#bookingsTable').DataTable({
      "order": [
        [0, "desc"]
      ],
      "responsive": true,
      "dom": '<"top"f>rt<"bottom"lip><"clear">',
      "language": {
        "search": "_INPUT_",
        "searchPlaceholder": "Search bookings...",
        "lengthMenu": "Show _MENU_ entries",
        "info": "Showing _START_ to _END_ of _TOTAL_ bookings",
        "paginate": {
          "previous": "<i class='bi bi-chevron-left'></i>",
          "next": "<i class='bi bi-chevron-right'></i>"
        }
      }
    });

    $(document).on('click', '.confirm-delivery', function() {
      var scheduleId = $(this).data('schedule-id');
      $('#confirm_schedule_id').val(scheduleId);
      $('#confirmationModal').modal('show');
    });

    $('#confirmationModal form').on('submit', function(e) {
      e.preventDefault();
      var form = $(this);

      $.ajax({
        url: form.attr('action'),
        type: 'POST',
        data: form.serialize(),
        success: function(response) {
          $('#confirmationModal').modal('hide');
          location.reload(); // Refresh to show updated status
        },
        error: function() {
          alert('Error confirming receipt. Please try again.');
        }
      });
    });

    function updateCostBreakdown() {
      const distance = parseFloat($('#distance_km').val()) || 0;
      const baseRate = 10000;
      const extraCharge = distance > 30 ? Math.ceil((distance - 30) / 10) * 5000 : 0;
      const totalCost = baseRate + extraCharge;

      $('#base_rate').text('Php ' + baseRate.toLocaleString());
      $('#extra_charge').text('Php  ' + extraCharge.toLocaleString());
      $('#total_cost').text('Php  ' + totalCost.toLocaleString());

      return {
        baseRate,
        extraCharge,
        totalCost
      };
    }

    $('#distance_km').on('input', updateCostBreakdown);

    $('#bookingModal').on('shown.bs.modal', function() {
      updateCostBreakdown();
    });

    $('#bookNowBtn').click(function(e) {
      e.preventDefault();
      if ($('#bookingForm')[0].checkValidity()) {
        const costs = updateCostBreakdown();
        $('#confirm_pick_up').text($('#pick_up').val());
        $('#confirm_destination').text($('#destination').val());
        $('#confirm_distance').text($('#distance_km').val());
        $('#confirm_date').text($('#booking_date').val());
        $('#confirm_truck').text($('#truck_id option:selected').text());
        $('#confirm_base_rate').text('Php ' + costs.baseRate.toLocaleString());
        $('#confirm_extra_charge').text('Php ' + costs.extraCharge.toLocaleString());
        $('#confirm_total_cost').text('Php ' + costs.totalCost.toLocaleString());
        $('#bookingModal').modal('hide');
        $('#orderConfirmationModal').modal('show');
      } else {
        $('#bookingForm')[0].reportValidity();
      }
    });

    $('#confirmOrderBtn').click(function() {
      $('#bookingForm').submit();
    });
  });
</script>

<?php
$profileQuery->close();
$getCustomerId->close();
$bookingsQuery->close();
$con->close();
$content = ob_get_clean();
include "../layout/client_layout.php";
?>