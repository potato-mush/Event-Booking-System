<?php
require_once 'db_connection.php';

$id = $_POST['id'];
$status = $_POST['status'];

try {
    $conn->beginTransaction();

    // Update booking status
    $query = "UPDATE booking SET status = :status WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':status', $status);
    $stmt->bindValue(':id', $id);
    $stmt->execute();

    // Get user_id for the booking
    $query = "SELECT user_id FROM booking WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':id', $id);
    $stmt->execute();
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($status === 'CONFIRMED') {
        // Add notification
        $message = "Your booking (ID: $id) has been confirmed!";
        $query = "INSERT INTO notifications (user_id, message, created_at) 
                 VALUES (:user_id, :message, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':user_id', $booking['user_id']);
        $stmt->bindValue(':message', $message);
        $stmt->execute();
    }

    $conn->commit();
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
