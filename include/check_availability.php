<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['user_username'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

require 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventDate = $_POST['event-date'];
    $eventTimeStart = $_POST['event-time-start'];
    $eventTimeEnd = $_POST['event-time-end'];

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

    // Add 3 hours to the end time of each booking for buffer
    $stmt = $conn->prepare("SELECT 
        event_time_start,
        ADDTIME(event_time_end, '03:00:00') as buffered_end_time 
        FROM booking 
        WHERE event_date = ?");
    $stmt->execute([$eventDate]);
    $existingBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check if the requested time slot overlaps with any existing booking (including buffer)
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

    echo json_encode(['status' => 'success']);
    exit();
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
exit();
