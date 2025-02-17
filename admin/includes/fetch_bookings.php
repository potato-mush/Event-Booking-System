<?php
require_once 'db_connection.php';

$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? 'all';
$page = $_GET['page'] ?? 1;
$limit = 5;
$offset = ($page - 1) * $limit;

$query = "SELECT booking.id, users.first_name, users.last_name, booking.event_date, booking.event_type, booking.event_name, booking.event_time_start, booking.event_time_end, booking.status 
          FROM booking 
          JOIN users ON booking.user_id = users.id 
          WHERE (users.first_name LIKE :search OR users.last_name LIKE :search OR booking.event_type LIKE :search OR booking.event_name LIKE :search)";

if ($filter !== 'all') {
    $query .= " AND booking.status = :filter";
}

$query .= " LIMIT :limit OFFSET :offset";

$stmt = $conn->prepare($query);
$stmt->bindValue(':search', '%' . $search . '%');
if ($filter !== 'all') {
    $stmt->bindValue(':filter', $filter);
}
$stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($result)) {
    echo "<tr><td colspan='8'>No matches found</td></tr>";
} else {
    foreach ($result as $row) {
        $customerName = $row['first_name'] . ' ' . $row['last_name'];
        $startTime = new DateTime($row['event_time_start']);
        $endTime = new DateTime($row['event_time_end']);
        $duration = $startTime->diff($endTime)->format('%h hour(s)');
        echo "<tr>
                <td>{$row['id']}</td>
                <td>{$customerName}</td>
                <td>{$row['event_name']}</td>
                <td>{$row['event_date']}</td>
                <td>{$row['event_type']}</td>
                <td>{$duration}</td>
                <td><span class='status-badge status-{$row['status']}'>{$row['status']}</span></td>
                <td>";
        if ($row['status'] == 'PENDING') {
            echo "<button class='confirm-btn' data-id='{$row['id']}'><i class='fas fa-check-circle'></i></button>
                  <button class='cancel-btn' data-id='{$row['id']}'><i class='fas fa-times-circle'></i></button>";
        } else {
            echo "<button class='pending-btn' data-id='{$row['id']}'><i class='fas fa-minus-circle'></i></button>";
        }
        echo "</td>
              </tr>";
    }
}
?>
