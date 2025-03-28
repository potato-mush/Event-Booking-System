<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Force JSON response
header('Content-Type: application/json');

// Check if the user is logged in
if (!isset($_SESSION['user_username'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

// Fetch user's personal info from the database
require 'db_connection.php';

// Only process if we have a reference number
if (!isset($_POST['reference-number'])) {
    echo json_encode(['status' => 'error', 'message' => 'Reference number is required']);
    exit();
}

// Check for duplicate reference number first
$stmt = $conn->prepare("SELECT COUNT(*) FROM transactions WHERE reference_number = ?");
$stmt->execute([$_POST['reference-number']]);
if ($stmt->fetchColumn() > 0) {
    echo json_encode(['status' => 'error', 'message' => 'This reference number has already been used']);
    exit();
}

// Get user info
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$_SESSION['user_username']]);
$userInfo = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventName = $_POST['event-name'];
    $eventDate = $_POST['event-date'];
    $eventTimeStart = $_POST['event-time-start'];
    $eventTimeEnd = $_POST['event-time-end'];
    $numberOfGuests = intval($_POST['number-of-guests']);

    // Check number of events for the selected date
    $stmt = $conn->prepare("SELECT COUNT(*) FROM booking WHERE event_date = ?");
    $stmt->execute([$eventDate]);
    $eventCount = $stmt->fetchColumn();

    if ($eventCount >= 3) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Maximum number of events (3) already reached for this date.'
        ]);
        exit();
    }

    // Update the time conflict check to include the 3-hour buffer
    $stmt = $conn->prepare("SELECT 
        event_time_start,
        ADDTIME(event_time_end, '03:00:00') as buffered_end_time 
        FROM booking 
        WHERE event_date = ?");
    $stmt->execute([$eventDate]);
    $existingBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($existingBookings as $booking) {
        $existingStart = strtotime($booking['event_time_start']);
        $existingEndWithBuffer = strtotime($booking['buffered_end_time']);
        $requestedStart = strtotime($eventTimeStart);
        $requestedEnd = strtotime($eventTimeEnd);

        if (
            ($requestedStart >= $existingStart && $requestedStart < $existingEndWithBuffer) ||
            ($requestedEnd > $existingStart && $requestedEnd <= $existingEndWithBuffer) ||
            ($requestedStart <= $existingStart && $requestedEnd >= $existingEndWithBuffer)
        ) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Selected time slot conflicts with an existing event. Please allow at least 3 hours between events.'
            ]);
            exit();
        }
    }

    // For package bookings
    if (isset($_POST['package-price'])) {
        // Use package price directly
        $packagePrice = floatval($_POST['package-price']);
        $totalPrice = $packagePrice; // Direct package price without formatting
        
        // Set default values for package bookings
        $eventTheme = isset($_POST['event-theme']) ? $_POST['event-theme'] : $eventName;
        $menuTitle = $eventName; 
        $menuPrice = $packagePrice;
        $totalMenuPrice = $packagePrice; // For packages, menu price is package price
        $seatingArrangement = '-';
        $menuType = 'package';
        $additionalServices = '-';
        $preferredEntertainment = '-';
        $eventType = 'package';
        $decoration = '-';
        $referenceNumber = $_POST['reference-number'];
        
        // Skip additional price calculations for package bookings
    } else {
        // Original price calculation for custom bookings
        $eventTheme = isset($_POST['event-theme']) ? $_POST['event-theme'] : $eventName;
        $menuTitle = isset($_POST['menu-title']) ? $_POST['menu-title'] : '-';
        $menuPrice = isset($_POST['full-course-menu']) ? floatval($_POST['full-course-menu']) : 0;
        $seatingArrangement = isset($_POST['seating-arrangement']) ? $_POST['seating-arrangement'] : '-';
        $menuType = isset($_POST['menu-type']) ? $_POST['menu-type'] : 'package';
        $additionalServices = isset($_POST['additional-services']) ? $_POST['additional-services'] : '-';
        $preferredEntertainment = isset($_POST['preferred-entertainment']) ? $_POST['preferred-entertainment'] : '-';
        $eventType = isset($_POST['event-type']) ? $_POST['event-type'] : '-';
        $decoration = isset($_POST['decoration']) ? $_POST['decoration'] : '-';
        $referenceNumber = isset($_POST['reference-number']) ? $_POST['reference-number'] : '';

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
        $menuTypePrice = getPrice($menuType);  // Changed from menuPrice
        $servicesPrice = getPrice($additionalServices);
        $entertainmentPrice = getPrice($preferredEntertainment);
        $eventTypePrice = getPrice($eventType);
        $decorationPrice = getPrice($decoration);
        
        // Calculate total menu price based on number of guests
        $menuPrice = isset($_POST['full-course-menu']) ? floatval($_POST['full-course-menu']) : 0;
        $totalMenuPrice = $menuPrice * $numberOfGuests;

        $totalPrice += $seatingPrice;
        $totalPrice += $menuTypePrice;  // Changed from menuPrice
        $totalPrice += $servicesPrice;
        $totalPrice += $entertainmentPrice;
        $totalPrice += $eventTypePrice;
        $totalPrice += $decorationPrice;
        $totalPrice += $totalMenuPrice;  // Add menu price to total
    }

    // Generate a unique transaction number
    do {
        $transactionNumber = rand(100000, 999999);
        $stmt = $conn->prepare("SELECT COUNT(*) FROM transactions WHERE transaction_number = ?");
        $stmt->execute([$transactionNumber]);
        $count = $stmt->fetchColumn();
    } while ($count > 0);

    $currentDate = date("Y-m-d");

    try {
        // Start transaction to ensure data consistency
        $conn->beginTransaction();
        
        // Save booking and transaction details to the database
        $stmt = $conn->prepare("INSERT INTO booking (event_name, event_type, event_date, event_time_start, event_time_end, event_theme, menu_type, guest_no, seating_arrangement, preferred_entertainment, decoration_preferences, additional_services, status, user_id, menu_title, menu_price) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'PENDING', ?, ?, ?)");
        $stmt->execute([$eventName, $eventType, $eventDate, $eventTimeStart, $eventTimeEnd, $eventTheme, $menuType, $numberOfGuests, $seatingArrangement, $preferredEntertainment, $decoration, $additionalServices, $userInfo['id'], $menuTitle, $totalMenuPrice]);
        $bookingId = $conn->lastInsertId();

        $stmt = $conn->prepare("INSERT INTO transactions (booking_id, user_id, transaction_date, transaction_number, total_amount, status, reference_number) VALUES (?, ?, ?, ?, ?, 'PENDING', ?)");
        $stmt->execute([$bookingId, $userInfo['id'], $currentDate, $transactionNumber, $totalPrice, $referenceNumber]);

        // Commit the transaction
        $conn->commit();
        
        // Store booking ID in session
        $_SESSION['temp_booking_id'] = $bookingId;
        echo json_encode(['status' => 'success']);
        
    } catch (Exception $e) {
        // Rollback the transaction if any error occurs
        $conn->rollBack();
        error_log($e->getMessage()); // Log the error
        echo json_encode(['status' => 'error', 'message' => 'An error occurred while processing your booking']);
    }
    exit();
}
?>
