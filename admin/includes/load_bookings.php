<?php
require_once 'db_connection.php'; // Adjust the path as necessary

$query = "SELECT id, event_name, event_type, event_date, event_time_start, event_time_end, event_theme, menu_type, guest_no, seating_arrangement, preferred_entertainment, decoration_preferences, additional_services, status FROM booking";
$stmt = $conn->prepare($query);
$stmt->execute();

$bookings = array();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $backgroundColor = '';
    $borderColor = '';
    if ($row['status'] === 'pending') {
        $backgroundColor = '#f39c12';
        $borderColor = '#f39c12';
    } elseif ($row['status'] === 'confirmed') {
        $backgroundColor = '#2ecc71';
        $borderColor = '#2ecc71';
    } elseif ($row['status'] === 'cancelled') {
        $backgroundColor = '#e74c3c';
        $borderColor = '#e74c3c';
    }

    $bookings[] = array(
        'id' => $row['id'],
        'title' => $row['event_name'],
        'start' => $row['event_date'] . 'T' . $row['event_time_start'],
        'end' => $row['event_date'] . 'T' . $row['event_time_end'],
        'backgroundColor' => $backgroundColor,
        'borderColor' => $borderColor,
        'extendedProps' => array(
            'event_type' => $row['event_type'],
            'event_theme' => $row['event_theme'],
            'menu_type' => $row['menu_type'],
            'guest_no' => $row['guest_no'],
            'seating_arrangement' => $row['seating_arrangement'],
            'entertainment' => $row['preferred_entertainment'],
            'decoration' => $row['decoration_preferences'],
            'additional_services' => $row['additional_services'],
            'status' => $row['status']
        ),
        'classNames' => [$row['status']]
    );
}

echo json_encode($bookings);
?>
