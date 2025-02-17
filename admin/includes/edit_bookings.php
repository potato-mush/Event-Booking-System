<?php
require_once 'db_connection.php'; // Adjust the path as necessary

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $event_id = $_POST['eventId'];
    $event_name = $_POST['eventTitle'];
    $event_type = $_POST['eventType'];
    $event_date = $_POST['eventDate'];
    $event_time_start = $_POST['eventStartTime'];
    $event_time_end = $_POST['eventEndTime'];
    $event_theme = $_POST['eventTheme'] ?? '';
    $menu_type = $_POST['menuType'] ?? '';
    $guest_no = $_POST['guestNo'] ?? 0;
    $seating_arrangement = $_POST['seatingArrangement'] ?? '';
    $preferred_entertainment = $_POST['entertainment'] ?? '';
    $decoration_preferences = $_POST['decoration'] ?? '';
    $additional_services = $_POST['additionalServices'] ?? '';

    // Check for existing events on the same date and time
    $checkQuery = "SELECT COUNT(*) as event_count FROM booking WHERE event_date = :event_date AND ((event_time_start < :event_time_end AND event_time_end > :event_time_start) AND id != :event_id)";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bindParam(':event_date', $event_date);
    $checkStmt->bindParam(':event_time_start', $event_time_start);
    $checkStmt->bindParam(':event_time_end', $event_time_end);
    $checkStmt->bindParam(':event_id', $event_id);
    $checkStmt->execute();
    $result = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($result['event_count'] > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Time slot is already booked']);
        exit;
    }

    $query = "UPDATE booking SET event_name = :event_name, event_type = :event_type, event_date = :event_date, event_time_start = :event_time_start, event_time_end = :event_time_end, event_theme = :event_theme, menu_type = :menu_type, guest_no = :guest_no, seating_arrangement = :seating_arrangement, preferred_entertainment = :preferred_entertainment, decoration_preferences = :decoration_preferences, additional_services = :additional_services WHERE id = :event_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':event_id', $event_id);
    $stmt->bindParam(':event_name', $event_name);
    $stmt->bindParam(':event_type', $event_type);
    $stmt->bindParam(':event_date', $event_date);
    $stmt->bindParam(':event_time_start', $event_time_start);
    $stmt->bindParam(':event_time_end', $event_time_end);
    $stmt->bindParam(':event_theme', $event_theme);
    $stmt->bindParam(':menu_type', $menu_type);
    $stmt->bindParam(':guest_no', $guest_no);
    $stmt->bindParam(':seating_arrangement', $seating_arrangement);
    $stmt->bindParam(':preferred_entertainment', $preferred_entertainment);
    $stmt->bindParam(':decoration_preferences', $decoration_preferences);
    $stmt->bindParam(':additional_services', $additional_services);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        $errorInfo = $stmt->errorInfo();
        echo json_encode(['status' => 'error', 'message' => 'Failed to update event', 'errorInfo' => $errorInfo]);
    }
}
?>
