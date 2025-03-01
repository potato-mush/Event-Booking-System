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
$eventName = $eventDate = $eventTimeStart = $eventTimeEnd = $eventTheme = $numberOfGuests = $seatingArrangement = $menuType = $additionalServices = $preferredEntertainment = $eventType = $decoration = '';

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
    $menuPrice = getPrice($menuType);
    $servicesPrice = getPrice($additionalServices);
    $entertainmentPrice = getPrice($preferredEntertainment);
    $eventTypePrice = getPrice($eventType);
    $decorationPrice = getPrice($decoration);

    $totalPrice += $seatingPrice;
    $totalPrice += $menuPrice;
    $totalPrice += $servicesPrice;
    $totalPrice += $entertainmentPrice;
    $totalPrice += $eventTypePrice;
    $totalPrice += $decorationPrice;

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
?>

<div class="receipt">
    <h2>Event Receipt</h2>
    <p><strong>Total Price:</strong> ₱<?= number_format($totalPrice, 2) ?></p>
    <p><strong>Event Name:</strong> <?= htmlspecialchars($eventName) ?></p>
    <p><strong>Event Date:</strong> <?= htmlspecialchars($eventDate) ?></p>
    <p><strong>Event Time:</strong> <?= formatTime($eventTimeStart) ?> to <?= formatTime($eventTimeEnd) ?></p>
    <p><strong>Event Theme:</strong> <?= htmlspecialchars($eventTheme) ?></p>
    <p><strong>Number of Guests:</strong> <?= htmlspecialchars($numberOfGuests) ?></p>
    <p><strong>Seating Arrangement:</strong> <?= htmlspecialchars(normalizeOptionName($seatingArrangement)) ?></p>
    <p><strong>Menu Type:</strong> <?= htmlspecialchars(normalizeOptionName($menuType)) ?></p>
    <p><strong>Additional Services:</strong> <?= htmlspecialchars(normalizeOptionName($additionalServices)) ?></p>
    <p><strong>Preferred Entertainment:</strong> <?= htmlspecialchars(normalizeOptionName($preferredEntertainment)) ?></p>
    <p><strong>Event Type:</strong> <?= htmlspecialchars(normalizeOptionName($eventType)) ?></p>
    <p><strong>Decoration:</strong> <?= htmlspecialchars(normalizeOptionName($decoration)) ?></p>
    <div class="user-info">
        <h3>User Information</h3>
        <p><strong>Name:</strong> <?= htmlspecialchars($userInfo['first_name'] . ' ' . $userInfo['last_name']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($userInfo['email']) ?></p>
        <p><strong>Account Created:</strong> <?= htmlspecialchars($userInfo['created_at']) ?></p>
        <p><strong>Address:</strong> <?= htmlspecialchars($userInfo['address']) ?></p>
        <p><strong>Phone Number:</strong> <?= htmlspecialchars($userInfo['phone_number']) ?></p>
    </div>
    <p><strong>Transaction Number:</strong> <?= htmlspecialchars($transactionNumber) ?></p>
    <p><strong>Date:</strong> <?= htmlspecialchars($currentDate) ?></p>
    <form method="POST" action="include/confirm_booking.php">
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
        <button type="submit" name="confirm">Confirm</button>
    </form>
</div>

<!-- Success Modal -->
<div id="success-modal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('success-modal').style.display='none'">&times;</span>
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