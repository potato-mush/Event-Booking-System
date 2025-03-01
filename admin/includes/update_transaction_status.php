<?php
header('Content-Type: application/json');
require_once 'db_connection.php';

$data = json_decode(file_get_contents('php://input'), true);
$transactionId = $data['transactionId'];
$status = $data['status'];

try {
    $sql = "UPDATE transactions SET status = :status WHERE id = :transactionId";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':transactionId', $transactionId);
    $stmt->execute();

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
