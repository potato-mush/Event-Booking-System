<?php
require_once 'db_connection.php';

try {
    $query = "SELECT DATE_FORMAT(transaction_date, '%Y-%m') as month, SUM(total_amount) as total 
              FROM transactions 
              WHERE status = 'PAID'
              GROUP BY month 
              ORDER BY month ASC 
              LIMIT 12";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
