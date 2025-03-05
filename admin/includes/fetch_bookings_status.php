<?php
require_once 'db_connection.php';

$query = "SELECT status, COUNT(*) as total 
          FROM booking 
          GROUP BY status";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($result);
?>
