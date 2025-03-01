<?php
header('Content-Type: application/json');
require_once 'db_connection.php';

$dateFrom = $_POST['dateFrom'] ?? null;
$dateTo = $_POST['dateTo'] ?? null;
$search = $_POST['search'] ?? '';
$page = $_POST['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

try {
    $sql = "SELECT t.id, b.event_name, u.first_name, u.last_name, t.transaction_date, t.transaction_number, t.total_amount, t.status 
            FROM transactions t
            JOIN booking b ON t.booking_id = b.id
            JOIN users u ON t.user_id = u.id
            WHERE (t.transaction_number LIKE :search OR b.event_name LIKE :search OR CONCAT(u.first_name, ' ', u.last_name) LIKE :search)";

    if ($dateFrom && $dateTo) {
        $sql .= " AND t.transaction_date BETWEEN :dateFrom AND :dateTo";
    }

    $sql .= " LIMIT :limit OFFSET :offset";

    $stmt = $conn->prepare($sql);
    $searchParam = '%' . $search . '%';
    $stmt->bindParam(':search', $searchParam);

    if ($dateFrom && $dateTo) {
        $stmt->bindParam(':dateFrom', $dateFrom);
        $stmt->bindParam(':dateTo', $dateTo);
    }

    $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);

    $stmt->execute();
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($transactions);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
