<?php
require_once 'db_connection.php';

$data = json_decode(file_get_contents('php://input'), true);
$dateFrom = $data['dateFrom'];
$dateTo = $data['dateTo'];

$sql = "SELECT t.id, b.event_name, b.guest_no, b.event_date, b.event_time_start, b.event_time_end, u.first_name, u.last_name, u.email, t.transaction_date, t.transaction_number, t.total_amount, t.status 
        FROM transactions t
        JOIN booking b ON t.booking_id = b.id
        JOIN users u ON t.user_id = u.id
        WHERE t.transaction_date BETWEEN :dateFrom AND :dateTo";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':dateFrom', $dateFrom);
$stmt->bindParam(':dateTo', $dateTo);
$stmt->execute();

$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($transactions);
?>
