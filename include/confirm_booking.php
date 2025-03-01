<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Start the session to access session data
}

// Check if the user is logged in
if (!isset($_SESSION['user_username'])) {
    header('Location: ../login.php');
    exit();
}

// Fetch user's personal info from the database
require 'db_connection.php';

$userInfo = [];
if (isset($_SESSION['user_username'])) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$_SESSION['user_username']]);
    $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    // Compute the total price
    $totalPrice = 0;

    // Fetch prices from the database
    function normalizeOptionName($option) {
        return ucwords(str_replace('-', ' ', $option));
    }

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

    // Generate a unique transaction number
    do {
        $transactionNumber = rand(100000, 999999);
        $stmt = $conn->prepare("SELECT COUNT(*) FROM transactions WHERE transaction_number = ?");
        $stmt->execute([$transactionNumber]);
        $count = $stmt->fetchColumn();
    } while ($count > 0);

    $currentDate = date("Y-m-d");

    // Save booking and transaction details to the database
    $stmt = $conn->prepare("INSERT INTO booking (event_name, event_type, event_date, event_time_start, event_time_end, event_theme, menu_type, guest_no, seating_arrangement, preferred_entertainment, decoration_preferences, additional_services, status, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'PENDING', ?)");
    $stmt->execute([$eventName, $eventType, $eventDate, $eventTimeStart, $eventTimeEnd, $eventTheme, $menuType, $numberOfGuests, $seatingArrangement, $preferredEntertainment, $decoration, $additionalServices, $userInfo['id']]);
    $bookingId = $conn->lastInsertId();

    $stmt = $conn->prepare("INSERT INTO transactions (booking_id, user_id, transaction_date, transaction_number, total_amount, status) VALUES (?, ?, ?, ?, ?, 'UNPAID')");
    $stmt->execute([$bookingId, $userInfo['id'], $currentDate, $transactionNumber, $totalPrice]);

    $_SESSION['booking_success'] = true;
    header('Location: ../index.php?page=receipt');
    exit();
}
?>
