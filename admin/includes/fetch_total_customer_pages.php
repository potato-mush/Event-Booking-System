<?php
require_once 'db_connection.php';

$search = $_GET['search'] ?? '';
$limit = 10;

$query = "SELECT COUNT(*) as total 
          FROM users 
          WHERE username LIKE :search OR email LIKE :search OR first_name LIKE :search OR last_name LIKE :search";
$stmt = $conn->prepare($query);
$stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
$stmt->execute();
$total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$totalPages = ceil($total / $limit);
echo $totalPages;
?>
