<?php
require_once 'db_connection.php'; // Adjust the path as necessary

$data = json_decode(file_get_contents('php://input'), true);
$event_date = $data['eventDate'];
$event_time_start = $data['eventStartTime'];
$event_time_end = $data['eventEndTime'];

$query = "SELECT COUNT(*) as event_count FROM booking WHERE event_date = :event_date AND ((event_time_start < :event_time_end AND event_time_end > :event_time_start))";
$stmt = $conn->prepare($query);
$stmt->bindParam(':event_date', $event_date);
$stmt->bindParam(':event_time_start', $event_time_start);
$stmt->bindParam(':event_time_end', $event_time_end);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($result['event_count'] == 0) {
    echo json_encode(['status' => 'available']);
} else {
    echo json_encode(['status' => 'unavailable']);
}
?>
