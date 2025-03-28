<?php
session_start();
require_once '../admin/includes/db_connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$userId = $_SESSION['user_id'];

if (isset($_GET['full'])) {
    // Get full notifications
    $query = "SELECT message, created_at FROM notifications 
              WHERE user_id = :user_id 
              ORDER BY created_at DESC 
              LIMIT 10";
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':user_id', $userId);
    $stmt->execute();
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($notifications)) {
        echo json_encode(['notifications' => []]);
        exit;
    }
    
    // Mark notifications as read
    $updateQuery = "UPDATE notifications SET is_read = 1 WHERE user_id = :user_id";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bindValue(':user_id', $userId);
    $updateStmt->execute();
    
    echo json_encode(['notifications' => $notifications]);
} else {
    // Get unread count only
    $query = "SELECT COUNT(*) as unread FROM notifications 
              WHERE user_id = :user_id AND is_read = 0";
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':user_id', $userId);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode(['unread' => (int)$result['unread']]);
}
