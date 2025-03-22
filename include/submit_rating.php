<?php
session_start();
require '../vendor/autoload.php';
require_once 'db_connection.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_rating') {
    try {
        $rating = $_POST['rating'];
        $feedback = $_POST['feedback'];
        $username = $_SESSION['user_username'];

        // Get user's email from database
        $stmt = $conn->prepare("SELECT email FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            throw new Exception('User not found');
        }

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'kevinsresort.restaurant@gmail.com';
        $mail->Password = 'ymso radd rgij dvsp';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom($user['email'], $username);
        $mail->addAddress('kevinsresort.restaurant@gmail.com');
        
        $mail->isHTML(true);
        $mail->Subject = "New Rating ($rating stars) from $username";
        
        $messageBody = "
            <h2>Rating Submission</h2>
            <p><strong>From:</strong> $username ({$user['email']})</p>
            <p><strong>Rating:</strong> $rating stars</p>
            <p><strong>Feedback:</strong></p>
            <p>" . nl2br(htmlspecialchars($feedback)) . "</p>
        ";
        
        $mail->Body = $messageBody;
        $mail->send();
        
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        error_log($e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Failed to send rating']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
