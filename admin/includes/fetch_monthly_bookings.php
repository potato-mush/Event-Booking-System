<?php
require_once 'db_connection.php';

$query = "SELECT DATE_FORMAT(event_date, '%Y-%m') as month, COUNT(*) as total 
          FROM booking 
          GROUP BY month 
          ORDER BY month ASC 
          LIMIT 12";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($result);
?>
