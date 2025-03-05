<?php
require_once 'db_connection.php';

$query = "SELECT SUM(total_amount) as totalPaid 
          FROM transactions 
          WHERE status = 'PAID'";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($result);
?>
