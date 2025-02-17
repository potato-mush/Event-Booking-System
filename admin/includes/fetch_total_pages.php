<?php
require_once 'db_connection.php';

$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? 'all';
$limit = 5;

$query = "SELECT COUNT(*) as total 
          FROM booking 
          JOIN users ON booking.user_id = users.id 
          WHERE (users.first_name LIKE :search OR users.last_name LIKE :search OR booking.event_type LIKE :search)";

if ($filter !== 'all') {
    $query .= " AND booking.status = :filter";
}

$stmt = $conn->prepare($query);
$stmt->bindValue(':search', '%' . $search . '%');
if ($filter !== 'all') {
    $stmt->bindValue(':filter', $filter);
}
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$totalPages = ceil($result['total'] / $limit);

echo $totalPages;
?>
