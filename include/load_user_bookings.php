<?php
session_start();
require_once 'db_connection.php';

// Debug session data
error_log("Session data: " . print_r($_SESSION, true));

// Check both user_id and user_username
if (!isset($_SESSION['user_id']) && isset($_SESSION['user_username'])) {
    // Try to get user_id from username
    try {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$_SESSION['user_username']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
        }
    } catch (PDOException $e) {
        error_log("Error fetching user ID: " . $e->getMessage());
    }
}

if (!isset($_SESSION['user_id'])) {
    error_log("User not logged in. Session data: " . print_r($_SESSION, true));
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

try {
    // Add debug logging for booking statuses
    $statusQuery = "SELECT id, status FROM booking WHERE user_id = :user_id";
    $statusStmt = $conn->prepare($statusQuery);
    $statusStmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $statusStmt->execute();
    error_log("Current booking statuses for user " . $_SESSION['user_id'] . ":");
    while ($row = $statusStmt->fetch(PDO::FETCH_ASSOC)) {
        error_log("Booking ID: " . $row['id'] . ", Status: " . $row['status']);
    }

    $query = "SELECT b.id, b.event_name as title, b.event_date, 
              b.event_time_start, b.event_time_end, 
              b.status, b.event_type, b.event_theme, b.menu_type, b.guest_no, 
              b.seating_arrangement, b.preferred_entertainment as entertainment, 
              b.decoration_preferences as decoration, b.additional_services,
              t.transaction_number, t.total_amount, t.status as payment_status,
              t.reference_number 
              FROM booking b 
              LEFT JOIN transactions t ON b.id = t.booking_id 
              WHERE b.user_id = :user_id 
              ORDER BY b.event_date ASC, b.event_time_start ASC";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    
    if (!$stmt->execute()) {
        throw new PDOException('Failed to execute query');
    }
    
    $events = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Ensure status is correctly set from database
        $status = $row['status'] ?: 'PENDING'; // Default to PENDING if null
        error_log("Processing event ID: " . $row['id'] . " with status: " . $status);

        // Format the start and end datetime properly
        $startDateTime = date('Y-m-d\TH:i:s', strtotime($row['event_date'] . ' ' . $row['event_time_start']));
        $endDateTime = date('Y-m-d\TH:i:s', strtotime($row['event_date'] . ' ' . $row['event_time_end']));
        
        $events[] = [
            'id' => $row['id'],
            'title' => $row['title'] ?: 'Untitled Event',
            'start' => $startDateTime,
            'end' => $endDateTime,
            'status' => $status, // Use the validated status
            'event_type' => $row['event_type'],
            'event_theme' => $row['event_theme'],
            'menu_type' => $row['menu_type'],
            'guest_no' => $row['guest_no'],
            'seating_arrangement' => $row['seating_arrangement'],
            'entertainment' => $row['entertainment'],
            'decoration' => $row['decoration'],
            'additional_services' => $row['additional_services'],
            'transaction' => [
                'number' => $row['transaction_number'],
                'amount' => $row['total_amount'],
                'status' => $row['payment_status'],
                'reference' => $row['reference_number']
            ]
        ];
    }

    // Debug output
    error_log("Found " . count($events) . " events for user " . $_SESSION['user_id']);
    
    if (empty($events)) {
        echo json_encode(['events' => [], 'message' => 'No events found']);
    } else {
        echo json_encode($events);
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to load events']);
}
