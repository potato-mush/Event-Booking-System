<?php
// package-detail.php
include 'include/db_connection.php'; // Include the database connection

// Check if the package ID is provided in the URL
if (!isset($_GET['id'])) {
    die("Package ID not provided.");
}

$packageId = $_GET['id'];

// Fetch the package details from the database
$stmt = $conn->prepare("SELECT * FROM catering_packages WHERE id = :id");
$stmt->bindParam(':id', $packageId);
$stmt->execute();
$package = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$package) {
    die("Package not found.");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $package['title']; ?></title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/packages.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
</head>

<body>
    <div class="page-container">
        <!-- Back button -->
        <div class="back-button">
            <a href="index.php?page=catering-packages"><i class="fas fa-arrow-left"></i> Back</a>
        </div>

        <!-- Title at the top-center -->
        <div class="package-heading"><?php echo $package['title']; ?></div>

        <!-- Content container with image on the left and octagon on the right -->
        <div class="content-container">
            <!-- Image container (left side) -->
            <div class="image-container">
                <img src="<?php echo $package['image_url']; ?>" alt="<?php echo $package['title']; ?>">
            </div>

            <!-- Octagon container (right side) -->
            <div class="octagon-container">
                <div class="package-description">
                    <p><?php echo nl2br($package['description']); ?></p>
                    <p>This Package Starts at <strong>₱<?php echo number_format($package['price'], 2, '.', ','); ?></strong></p>
                </div>
            </div>
        </div>

        <!-- Book now button at the bottom-right -->
        <div class="book-now-button">
            <a href="#" class="btn" onclick="openBookingModal(); return false;">Book Now!</a>
        </div>

        <!-- Booking Modal -->
        <div id="booking-modal" class="modal">
            <div class="modal-content">
                <h2>Book <?php echo $package['title']; ?></h2>
                <form id="quick-booking-form">
                    <input type="hidden" name="event-name" value="<?php echo $package['title']; ?>">
                    <input type="hidden" name="event-type" value="package">
                    <input type="hidden" name="event-theme" value="<?php echo $package['title']; ?>">
                    <input type="hidden" name="package-price" value="<?php echo number_format($package['price'], 2, '.', ''); ?>">

                    <div class="form-group">
                        <label for="event-date">Event Date:</label>
                        <input type="date" id="event-date" name="event-date" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="event-time-start">Start Time:</label>
                        <input type="time" id="event-time-start" name="event-time-start" required>
                    </div>

                    <div class="form-group">
                        <label for="event-time-end">End Time:</label>
                        <input type="time" id="event-time-end" name="event-time-end" required>
                    </div>

                    <div class="form-group">
                        <label for="number-of-guests">Number of Guests:</label>
                        <input type="number" id="number-of-guests" name="number-of-guests" min="1" max="100" required>
                    </div>

                    <div class="button-group">
                        <button type="submit">Proceed to Payment</button>
                        <button type="button" onclick="closeBookingModal()">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Payment Modal -->
        <div id="payment-modal" class="modal">
            <div class="modal-content">
                <h2>Down Payment Required</h2>
                <p>Total Amount: ₱<span id="totalAmount">0.00</span></p>
                <p>Required Down Payment (50%): ₱<span id="downPayment">0.00</span></p>
                <p>Please scan the QR code below to pay the down payment</p>
                <img src="assets/images/qrCode.jpg" alt="Payment QR Code" style="width: 200px; height: 200px;">
                <form id="payment-form" action="include/confirm_booking.php" method="POST">
                    <!-- Hidden fields to carry over booking details -->
                    <input type="hidden" name="package-price" id="payment-package-price">
                    <input type="hidden" name="event-name" id="payment-event-name">
                    <input type="hidden" name="event-date" id="payment-event-date">
                    <input type="hidden" name="event-time-start" id="payment-event-time-start">
                    <input type="hidden" name="event-time-end" id="payment-event-time-end">
                    <input type="hidden" name="event-type" id="payment-event-type">
                    <input type="hidden" name="event-theme" id="payment-event-theme">
                    <input type="hidden" name="number-of-guests" id="payment-number-of-guests">

                    <div class="form-group">
                        <label for="reference-number">Reference Number:</label>
                        <input type="text" id="reference-number" name="reference-number" pattern=".{13,13}" maxlength="13" required>
                    </div>
                    <div class="button-group">
                        <button type="submit">Confirm Payment</button>
                        <button type="button" onclick="closePaymentModal()">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openBookingModal() {
            document.getElementById('booking-modal').style.display = 'block';
        }

        function closeBookingModal() {
            document.getElementById('booking-modal').style.display = 'none';
        }

        document.getElementById('quick-booking-form').addEventListener('submit', function(e) {
            e.preventDefault();

            if (validateBookingForm()) {
                // Get package price directly without multiplying by guests
                const packagePrice = parseFloat(document.querySelector('input[name="package-price"]').value);
                const totalAmount = packagePrice;
                const downPayment = totalAmount * 0.5;

                // Update payment modal with proper number formatting
                document.getElementById('totalAmount').textContent = totalAmount.toLocaleString('en-PH', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                document.getElementById('downPayment').textContent = downPayment.toLocaleString('en-PH', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });

                // Transfer form data to payment form
                document.getElementById('payment-package-price').value = packagePrice;
                document.getElementById('payment-event-name').value = document.querySelector('input[name="event-name"]').value;
                document.getElementById('payment-event-date').value = document.getElementById('event-date').value;
                document.getElementById('payment-event-time-start').value = document.getElementById('event-time-start').value;
                document.getElementById('payment-event-time-end').value = document.getElementById('event-time-end').value;
                document.getElementById('payment-event-type').value = document.querySelector('input[name="event-type"]').value;
                document.getElementById('payment-event-theme').value = document.querySelector('input[name="event-theme"]').value;
                document.getElementById('payment-number-of-guests').value = document.getElementById('number-of-guests').value;

                // Hide booking modal and show payment modal
                closeBookingModal();
                document.getElementById('payment-modal').style.display = 'block';
            }
        });

        function validateBookingForm() {
            const startTime = document.getElementById('event-time-start').value;
            const endTime = document.getElementById('event-time-end').value;

            if (startTime && endTime) {
                const start = new Date(`1970-01-01T${startTime}`);
                const end = new Date(`1970-01-01T${endTime}`);
                const diff = (end - start) / (1000 * 60 * 60); // Difference in hours

                if (diff < 1) {
                    alert('The event duration must be at least 1 hour.');
                    return false;
                }

                if (diff > 3) {
                    alert('The event duration cannot exceed 3 hours.');
                    return false;
                }
            }

            return true;
        }

        function closePaymentModal() {
            document.getElementById('payment-modal').style.display = 'none';
        }

        // Reference number validation
        document.getElementById('reference-number').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^\d]/g, '');
            if (this.value.length > 13) {
                this.value = this.value.slice(0, 13);
            }
        });
    </script>
</body>

</html>