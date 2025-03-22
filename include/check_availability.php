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

    // Check for time slot conflicts
    $stmt = $conn->prepare("SELECT COUNT(*) FROM booking WHERE event_date = ? AND (
        (? BETWEEN event_time_start AND event_time_end) OR
        (? BETWEEN event_time_start AND event_time_end) OR
        (event_time_start BETWEEN ? AND ?) OR
        (event_time_end BETWEEN ? AND ?)
    )");
    $stmt->execute([$eventDate, $eventTimeStart, $eventTimeEnd, $eventTimeStart, $eventTimeEnd, $eventTimeStart, $eventTimeEnd]);
    $timeConflicts = $stmt->fetchColumn();

    if ($timeConflicts > 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Selected time slot conflicts with an existing event.'
        ]);
        exit();
    }

    echo json_encode(['status' => 'success']);
    exit();
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
exit();
