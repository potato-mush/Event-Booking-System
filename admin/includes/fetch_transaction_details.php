<?php
header('Content-Type: application/json');
require_once 'db_connection.php';

$data = json_decode(file_get_contents('php://input'), true);
$transactionId = $data['transactionId'];

try {
    $sql = "SELECT t.transaction_number, b.event_name, u.first_name, u.last_name, t.transaction_date, t.total_amount, t.status 
            FROM transactions t
            JOIN booking b ON t.booking_id = b.id
            JOIN users u ON t.user_id = u.id
            WHERE t.id = :transactionId";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':transactionId', $transactionId);
    $stmt->execute();
    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode($transaction);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
