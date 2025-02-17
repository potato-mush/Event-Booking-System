<?php
require_once 'db_connection.php';

$id = $_POST['id'];
$status = $_POST['status'];

$query = "UPDATE booking SET status = :status WHERE id = :id";
$stmt = $conn->prepare($query);
$stmt->bindValue(':status', $status);
$stmt->bindValue(':id', $id);
$stmt->execute();
?>
