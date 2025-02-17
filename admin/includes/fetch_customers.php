<?php
require_once 'db_connection.php';

$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? 'all';
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$filterCondition = '';
if ($filter == 'active') {
    $filterCondition = "AND last_login >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
} elseif ($filter == 'inactive') {
    $filterCondition = "AND last_login < DATE_SUB(NOW(), INTERVAL 7 DAY)";
}

$query = "SELECT id, username, email, first_name, last_name, address, phone_number, last_login 
          FROM users 
          WHERE (username LIKE :search OR email LIKE :search OR first_name LIKE :search OR last_name LIKE :search) 
          $filterCondition 
          LIMIT :limit OFFSET :offset";
$stmt = $conn->prepare($query);
$stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
$stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($result)) {
    echo "<tr><td colspan='6'>No matches found</td></tr>";
} else {
    foreach ($result as $row) {
        $nameEmail = $row['first_name'] . ' ' . $row['last_name'] . '<br>' . $row['email'];
        $statusClass = (new DateTime($row['last_login']) >= (new DateTime())->modify('-7 days')) ? 'status-active' : 'status-inactive';
        $statusText = (new DateTime($row['last_login']) >= (new DateTime())->modify('-7 days')) ? 'Active' : 'Inactive';
        echo "<tr>
                <td>{$row['id']}</td>
                <td>{$nameEmail}</td>
                <td>{$row['username']}</td>
                <td>{$row['address']}</td>
                <td>{$row['phone_number']}</td>
                <td><span class='status-badge {$statusClass}'>{$statusText}</span></td>
              </tr>";
    }
}
?>
