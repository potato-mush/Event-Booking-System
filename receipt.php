<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Start the session to access session data
}

// Check if the user is logged in
if (!isset($_SESSION['user_username'])) {
    header('Location: login.php');
    exit();
}

// Fetch user's personal info from the database
require 'include/db_connection.php';

$userInfo = [];
if (isset($_SESSION['user_username'])) {
    $stmt = $conn->prepare("SELECT id, email, created_at, first_name, last_name, address, phone_number FROM users WHERE username = ?");
    $stmt->execute([$_SESSION['user_username']]);
    $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
}

$transactionNumber = rand(100000, 999999);
$currentDate = date("Y-m-d");

function normalizeOptionName($option) {
    return ucwords(str_replace('-', ' ', $option));
}

function formatTime($time) {
    return date("g:i A", strtotime($time));
}

$totalPrice = 0;
$eventName = $eventDate = $eventTimeStart = $eventTimeEnd = $eventTheme = $numberOfGuests = 
$seatingArrangement = $menuType = $additionalServices = $preferredEntertainment = 
$eventType = $decoration = $menuTitle = '';  // Added $menuTitle
$totalMenuPrice = 0;  // Added $totalMenuPrice initialization
$referenceNumber = '';  // Add this line

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event-name'])) {
    $eventName = $_POST['event-name'];
    $eventDate = $_POST['event-date'];
    $eventTimeStart = $_POST['event-time-start'];
    $eventTimeEnd = $_POST['event-time-end'];
    $eventTheme = $_POST['event-theme'];
    $numberOfGuests = $_POST['number-of-guests'];
    $seatingArrangement = $_POST['seating-arrangement'] === 'custom' ? $_POST['custom-seating-arrangement'] : $_POST['seating-arrangement'];
    $menuType = $_POST['menu-type'] === 'custom' ? $_POST['custom-menu-type'] : $_POST['menu-type'];
    $additionalServices = $_POST['additional-services'] === 'custom' ? $_POST['custom-additional-services'] : $_POST['additional-services'];
    $preferredEntertainment = $_POST['preferred-entertainment'] === 'custom' ? $_POST['custom-preferred-entertainment'] : $_POST['preferred-entertainment'];
    $eventType = $_POST['event-type'] === 'custom' ? $_POST['custom-event-type'] : $_POST['event-type'];
    $decoration = $_POST['decoration'] === 'custom' ? $_POST['custom-decoration'] : $_POST['decoration'];
    $menuTitle = isset($_POST['menu-title']) ? $_POST['menu-title'] : '';
    $menuPrice = isset($_POST['full-course-menu']) ? floatval($_POST['full-course-menu']) : 0;
    $numberOfGuests = intval($_POST['number-of-guests']);
    $referenceNumber = $_POST['reference-number'];

    // Calculate total menu price based on number of guests
    $totalMenuPrice = $menuPrice * $numberOfGuests;

    // Fetch prices from the database
    function getPrice($option)
    {
        global $conn;
        $normalizedOption = normalizeOptionName($option);
        $stmt = $conn->prepare("SELECT price FROM event_options WHERE option_name = ?");
        $stmt->execute([$normalizedOption]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['price'] : 0;
    }

    $seatingPrice = getPrice($seatingArrangement);
    $menuTypePrice = getPrice($menuType);  // Changed from menuPrice
    $servicesPrice = getPrice($additionalServices);
    $entertainmentPrice = getPrice($preferredEntertainment);
    $eventTypePrice = getPrice($eventType);
    $decorationPrice = getPrice($decoration);

    $totalPrice += $seatingPrice;
    $totalPrice += $menuTypePrice;  // Changed from menuPrice
    $totalPrice += $servicesPrice;
    $totalPrice += $entertainmentPrice;
    $totalPrice += $eventTypePrice;
    $totalPrice += $decorationPrice;
    $totalPrice += $totalMenuPrice;

    // Save booking and transaction details to the database
    if (isset($_POST['confirm'])) {
        $stmt = $conn->prepare("INSERT INTO booking (event_name, event_type, event_date, event_time_start, event_time_end, event_theme, menu_type, guest_no, seating_arrangement, preferred_entertainment, decoration_preferences, additional_services, status, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'UNPAID', ?)");
        $stmt->execute([$eventName, $eventType, $eventDate, $eventTimeStart, $eventTimeEnd, $eventTheme, $menuType, $numberOfGuests, $seatingArrangement, $preferredEntertainment, $decoration, $additionalServices, $userInfo['id']]);
        $bookingId = $conn->lastInsertId();

        $stmt = $conn->prepare("INSERT INTO transactions (booking_id, user_id, transaction_date, transaction_number, status) VALUES (?, ?, ?, ?, 'UNPAID')");
        $stmt->execute([$bookingId, $userInfo['id'], $currentDate, $transactionNumber]);

        $_SESSION['booking_success'] = true;
        header('Location: index.php?page=receipt');
        exit();
    }
}

// After database connection and before displaying receipt
if (isset($_SESSION['temp_booking_id'])) {
    $bookingId = $_SESSION['temp_booking_id'];
    
    // Fetch booking details
    $stmt = $conn->prepare("SELECT b.*, t.transaction_number, t.total_amount, t.reference_number 
                           FROM booking b 
                           JOIN transactions t ON b.id = t.booking_id 
                           WHERE b.id = ?");
    $stmt->execute([$bookingId]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($booking) {
        $eventName = $booking['event_name'];
        $eventDate = $booking['event_date'];
        $eventTimeStart = $booking['event_time_start'];
        $eventTimeEnd = $booking['event_time_end'];
        $eventTheme = $booking['event_theme'];
        $numberOfGuests = $booking['guest_no'];
        $seatingArrangement = $booking['seating_arrangement'];
        $menuType = $booking['menu_type'];
        $additionalServices = $booking['additional_services'];
        $preferredEntertainment = $booking['preferred_entertainment'];
        $eventType = $booking['event_type'];
        $decoration = $booking['decoration_preferences'];
        $menuTitle = $booking['menu_title'];
        $totalMenuPrice = $booking['menu_price'];
        $totalPrice = $booking['total_amount'];
        $referenceNumber = $booking['reference_number'];
        $transactionNumber = $booking['transaction_number'];
    }
}
?>

<div class="print-preview-content">
    <div id="print-content">
        <div id="receipt-logo">
            <img src="assets/images/logo.png" alt="Logo" style="width: 150px; display: block; margin: 0 auto;">
        </div>
        <h3>Event Payment Receipt</h3>
        <div id="receipt-header">
            <p style="text-align: left; float: left;"><strong>Date:</strong> <?= htmlspecialchars($currentDate) ?></p>
            <p style="text-align: right; float: right;"><strong>Transaction Number:</strong> <?= htmlspecialchars($transactionNumber) ?></p>
        </div>
        <div style="clear: both;"></div>
        <div id="receipt-details">
            <h3>Guest Attendees Information</h3>
            <div class="receipt-row">
                <span class="receipt-label">Guest/Attendee Name:</span>
                <span class="receipt-value"><?= htmlspecialchars($userInfo['first_name'] . ' ' . $userInfo['last_name']) ?></span>
            </div>
            <div class="receipt-row">
                <span class="receipt-label">Email:</span>
                <span class="receipt-value"><?= htmlspecialchars($userInfo['email']) ?></span>
            </div>
            <div class="receipt-row">
                <span class="receipt-label">Number of Guests:</span>
                <span class="receipt-value"><?= htmlspecialchars($numberOfGuests) ?></span>
            </div>
            <div class="receipt-row">
                <span class="receipt-label">Phone Number:</span>
                <span class="receipt-value"><?= htmlspecialchars($userInfo['phone_number']) ?></span>
            </div>
            <div class="receipt-row">
                <span class="receipt-label">Address:</span>
                <span class="receipt-value"><?= htmlspecialchars($userInfo['address']) ?></span>
            </div>

            <h3>Event Information</h3>
            <div class="receipt-row">
                <span class="receipt-label">Event Name:</span>
                <span class="receipt-value"><?= htmlspecialchars($eventName) ?></span>
            </div>
            <div class="receipt-row">
                <span class="receipt-label">Event Date:</span>
                <span class="receipt-value"><?= htmlspecialchars($eventDate) ?></span>
            </div>
            <div class="receipt-row">
                <span class="receipt-label">Event Time:</span>
                <span class="receipt-value"><?= formatTime($eventTimeStart) ?> to <?= formatTime($eventTimeEnd) ?></span>
            </div>
            <div class="receipt-row">
                <span class="receipt-label">Event Theme:</span>
                <span class="receipt-value"><?= htmlspecialchars($eventTheme) ?></span>
            </div>
            <div class="receipt-row">
                <span class="receipt-label">Seating Arrangement:</span>
                <span class="receipt-value"><?= htmlspecialchars(normalizeOptionName($seatingArrangement)) ?></span>
            </div>
            <div class="receipt-row">
                <span class="receipt-label">Menu Type:</span>
                <span class="receipt-value"><?= htmlspecialchars(normalizeOptionName($menuType)) ?><?= $menuTitle ? ' (' . htmlspecialchars($menuTitle) . ')' : '' ?></span>
            </div>
            <?php if ($totalMenuPrice > 0): ?>
            <div class="receipt-row">
                <span class="receipt-label">Menu Price:</span>
                <span class="receipt-value">₱<?= number_format($totalMenuPrice, 2) ?></span>
            </div>
            <?php endif; ?>
            <div class="receipt-row">
                <span class="receipt-label">Additional Services:</span>
                <span class="receipt-value"><?= htmlspecialchars(normalizeOptionName($additionalServices)) ?></span>
            </div>
            <div class="receipt-row">
                <span class="receipt-label">Preferred Entertainment:</span>
                <span class="receipt-value"><?= htmlspecialchars(normalizeOptionName($preferredEntertainment)) ?></span>
            </div>
            <div class="receipt-row">
                <span class="receipt-label">Event Type:</span>
                <span class="receipt-value"><?= htmlspecialchars(normalizeOptionName($eventType)) ?></span>
            </div>
            <div class="receipt-row">
                <span class="receipt-label">Decoration:</span>
                <span class="receipt-value"><?= htmlspecialchars(normalizeOptionName($decoration)) ?></span>
            </div>
            <div class="receipt-row">
                <span class="receipt-label">Reference Number:</span>
                <span class="receipt-value"><?= htmlspecialchars($referenceNumber) ?></span>
            </div>
            <div class="receipt-row">
                <span class="receipt-label">Payment Status:</span>
                <span class="receipt-value">50% Down Payment</span>
            </div>
            <div class="receipt-row">
                <span class="receipt-label">Total Amount:</span>
                <span class="receipt-value">₱<?= number_format($totalPrice, 2) ?></span>
            </div>
        </div>
    </div>
    <form method="POST">
        <input type="hidden" name="event-name" value="<?= htmlspecialchars($eventName) ?>">
        <input type="hidden" name="event-date" value="<?= htmlspecialchars($eventDate) ?>">
        <input type="hidden" name="event-time-start" value="<?= htmlspecialchars($eventTimeStart) ?>">
        <input type="hidden" name="event-time-end" value="<?= htmlspecialchars($eventTimeEnd) ?>">
        <input type="hidden" name="event-theme" value="<?= htmlspecialchars($eventTheme) ?>">
        <input type="hidden" name="number-of-guests" value="<?= htmlspecialchars($numberOfGuests) ?>">
        <input type="hidden" name="seating-arrangement" value="<?= htmlspecialchars($seatingArrangement) ?>">
        <input type="hidden" name="menu-type" value="<?= htmlspecialchars($menuType) ?>">
        <input type="hidden" name="additional-services" value="<?= htmlspecialchars($additionalServices) ?>">
        <input type="hidden" name="preferred-entertainment" value="<?= htmlspecialchars($preferredEntertainment) ?>">
        <input type="hidden" name="event-type" value="<?= htmlspecialchars($eventType) ?>">
        <input type="hidden" name="decoration" value="<?= htmlspecialchars($decoration) ?>">
        <input type="hidden" name="full-course-menu" value="<?= htmlspecialchars($menuPrice) ?>">
        <input type="hidden" name="menu-title" value="<?= htmlspecialchars($menuTitle) ?>">
        <input type="hidden" name="booking_id" value="<?= $_SESSION['temp_booking_id'] ?? '' ?>">
        <button type="submit" name="confirm_booking">Confirm</button>
    </form>
</div>

<style>
/* Update the existing styles with the new receipt styles */
.print-preview-content {
    background-color: #fefefe;
    margin: 20px auto;
    padding: 20px;
    width: 80%;
    max-width: 800px;
    text-align: center;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

#receipt-logo {
    margin-bottom: 20px;
}

#receipt-header {
    margin-bottom: 30px;
    overflow: hidden;
}

.receipt-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
    padding: 5px 0;
    border-bottom: 1px dotted #ddd;
}

.receipt-label {
    font-weight: bold;
    color: #555;
}

.receipt-value {
    text-align: right;
}

#receipt-details h3 {
    margin: 20px 0 15px;
    padding-bottom: 5px;
    border-bottom: 2px solid #007bff;
    color: #333;
}

@media print {
    .print-preview-content {
        margin: 0 auto;
        box-shadow: none;
    }

    form {
        display: none;
    }
}
</style>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_booking'])) {
    $bookingId = $_POST['booking_id'];
    
    // Update booking and transaction status
    $stmt = $conn->prepare("UPDATE booking SET status = 'PENDING' WHERE id = ?");
    $stmt->execute([$bookingId]);
    
    $stmt = $conn->prepare("UPDATE transactions SET status = 'PARTIALLY PAID' WHERE booking_id = ?");
    $stmt->execute([$bookingId]);
    
    // Set success flag and clear temp booking ID
    $_SESSION['booking_success'] = true;
    unset($_SESSION['temp_booking_id']);
    
    echo "<script>
        document.getElementById('success-modal').style.display = 'block';
        document.getElementById('success-modal').addEventListener('click', function() {
            window.location.href = 'index.php';
        });
    </script>";
}
?>

<!-- Success Modal -->
<div id="success-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <h2>Success!</h2>
        <p>Your booking has been confirmed.</p>
        <button onclick="window.location.href='index.php'">OK</button>
    </div>
</div>

<style>
/* Receipt styles */
.receipt {
    background-color: #fff;
    padding: 20px;
    border: 1px solid #ccc;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    width: 80%;
    max-width: 600px;
    margin: 20px auto;
    font-family: Arial, sans-serif;
    border-radius: 10px;
    animation: fadeIn 0.5s ease-in-out;
}

.receipt h2 {
    margin-top: 0;
    border-bottom: 2px solid #4CAF50;
    padding-bottom: 10px;
    color: #333;
}

.receipt h3 {
    margin-top: 20px;
    color: #555;
}

.receipt p {
    margin: 10px 0;
    color: #666;
}

.receipt p strong {
    color: #333;
}

.receipt .user-info {
    margin-top: 20px;
    padding-top: 10px;
    border-top: 1px solid #ccc;
}

/* Modal styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgb(0,0,0);
    background-color: rgba(0,0,0,0.4);
    animation: fadeIn 0.5s ease-in-out;
}

.modal-content {
    background-color: #fefefe;
    margin: 15% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 400px;
    text-align: center;
    border-radius: 10px;
    animation: slideIn 0.5s ease-in-out;
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideIn {
    from { transform: translateY(-50px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
</style>

<script>
window.onload = function() {
    <?php if (isset($_SESSION['booking_success']) && $_SESSION['booking_success']): ?>
        document.getElementById('success-modal').style.display = 'block';
        <?php unset($_SESSION['booking_success']); ?>
    <?php endif; ?>
};
</script>