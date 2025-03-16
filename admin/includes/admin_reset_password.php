<?php
@ini_set('display_errors', 0);
error_reporting(0);
ob_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

date_default_timezone_set('Asia/Manila');

try {
    require '../../vendor/autoload.php';
    require_once 'db_connection.php';

    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    if (!isset($_POST['action'])) {
        throw new Exception('No action specified');
    }

    $response = ['success' => false, 'message' => ''];

    switch ($_POST['action']) {
        case 'request_reset':
            $email = $_POST['email'];
            $stmt = $conn->prepare("SELECT id, username FROM admin WHERE email = ?");
            $stmt->execute([$email]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($admin) {
                // Generate a random password
                $new_password = bin2hex(random_bytes(4)); // 8 characters long
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                // Update the password immediately
                $stmt = $conn->prepare("UPDATE admin SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $admin['id']]);

                $mail = new PHPMailer(true);
                try {
                    $mail->SMTPDebug = 0;
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'kevinsresort.restaurant@gmail.com';
                    $mail->Password   = 'hjbz umns dzyb eqes';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    $mail->Port       = 465;

                    $mail->SMTPOptions = array(
                        'ssl' => array(
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        )
                    );

                    $mail->setFrom('kevinsresort.restaurant@gmail.com', 'Kevin\'s Restaurant Admin');
                    $mail->addAddress($email);

                    $mail->isHTML(true);
                    $mail->Subject = 'Admin Password Reset';
                    $mail->Body    = "
                        <h2>Admin Password Reset</h2>
                        <p>Your admin password has been reset.</p>
                        <p>Your new password is: <strong>{$new_password}</strong></p>
                        <p>Please login with this password and change it immediately.</p>
                    ";

                    $mail->send();
                    $response['success'] = true;
                    $response['message'] = 'A new password has been sent to your email.';
                } catch (Exception $e) {
                    throw new Exception("Email sending failed: " . $e->getMessage());
                }
            } else {
                $response['message'] = 'Email not found.';
            }
            break;
    }
} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
} finally {
    if (ob_get_length()) ob_clean();
    
    if (!isset($response) || !is_array($response)) {
        $response = ['success' => false, 'message' => 'Unknown error occurred'];
    }
    
    echo json_encode($response);
    exit;
}
