<?php
require_once 'db_connection.php';

$data = json_decode(file_get_contents('php://input'), true);
$dateFrom = !empty($data['dateFrom']) ? $data['dateFrom'] : null;
$dateTo = !empty($data['dateTo']) ? $data['dateTo'] : null;
$search = !empty($data['search']) ? $data['search'] : null;
$page = isset($data['page']) ? (int)$data['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

$sql = "SELECT t.id, b.event_name, b.guest_no, b.event_date, b.event_time_start, b.event_time_end, 
               u.first_name, u.last_name, u.email, t.transaction_date, t.transaction_number, 
               t.total_amount, t.status 
        FROM transactions t
        JOIN booking b ON t.booking_id = b.id
        JOIN users u ON t.user_id = u.id";

$countSql = "SELECT COUNT(*) FROM transactions t 
             JOIN booking b ON t.booking_id = b.id 
             JOIN users u ON t.user_id = u.id";

$conditions = [];
$params = [];

if ($dateFrom && $dateTo) {
    $conditions[] = "t.transaction_date BETWEEN :dateFrom AND :dateTo";
    $params[':dateFrom'] = $dateFrom;
    $params[':dateTo'] = $dateTo;
}

if ($search) {
    $conditions[] = "(b.event_name LIKE :search 
                     OR u.first_name LIKE :search 
                     OR u.last_name LIKE :search 
                     OR t.transaction_number LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

if (!empty($conditions)) {
    $whereClause = " WHERE " . implode(' AND ', $conditions);
    $sql .= $whereClause;
    $countSql .= $whereClause;
}

$sql .= " LIMIT :limit OFFSET :offset";

// Get total count
$countStmt = $conn->prepare($countSql);
foreach ($params as $key => $value) {
    $countStmt->bindValue($key, $value);
}
$countStmt->execute();
$totalRows = $countStmt->fetchColumn();
$totalPages = ceil($totalRows / $limit);

// Get paginated data
$stmt = $conn->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode([
    'transactions' => $transactions,
    'totalPages' => $totalPages,
    'currentPage' => $page
]);
?>
