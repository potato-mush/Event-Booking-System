<?php
// Prevent any unwanted output
@ini_set('display_errors', 0);
error_reporting(0);

// Start clean output
ob_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Set timezone
date_default_timezone_set('Asia/Manila');

try {
    require '../vendor/autoload.php';
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
            $stmt = $conn->prepare("SELECT id, username FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $token = bin2hex(random_bytes(32));
                $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Add debug logging
                error_log("Token created at: " . date('Y-m-d H:i:s'));
                error_log("Token expires at: " . $expiry);

                $stmt = $conn->prepare("INSERT INTO password_reset_tokens (user_id, token, expiry_timestamp) VALUES (?, ?, ?)");
                $stmt->execute([$user['id'], $token, $expiry]);

                // Send email using PHPMailer
                $mail = new PHPMailer(true);
                try {
                    // Server settings
                    $mail->SMTPDebug = 0;                      // Disable debug output
                    $mail->isSMTP();                          
                    $mail->Host       = 'smtp.gmail.com';     
                    $mail->SMTPAuth   = true;                 
                    $mail->Username   = 'kevinsresort.restaurant@gmail.com'; // Your Gmail address
                    $mail->Password   = 'ymso radd rgij dvsp'; // Your Gmail App Password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Use SMTP Secure (SSL/TLS)
                    $mail->Port       = 465;                   // TCP port to connect to (465 for SSL)

                    // Improved error handling
                    $mail->SMTPOptions = array(
                        'ssl' => array(
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        )
                    );

                    // Recipients
                    $mail->setFrom('kevinsresort.restaurant@gmail.com', 'Kevin\'s Restaurant');
                    $mail->addAddress($email);

                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = 'Password Reset Request';
                    $mail->Body    = "
                        <h2>Password Reset Request</h2>
                        <p>You have requested to reset your password.</p>
                        <p>Your password reset token is: <strong>$token</strong></p>
                        <p>This token will expire in 1 hour.</p>
                        <p>If you did not request this reset, please ignore this email.</p>
                    ";

                    $mail->send();
                    $response['success'] = true;
                    $response['message'] = 'Reset token has been sent to your email. (Check your Spam folder if not found)';
                } catch (Exception $e) {
                    throw new Exception("Email sending failed: " . $e->getMessage());
                }
            } else {
                $response['message'] = 'Email not found.';
            }
            break;

        case 'verify_token':
            if (empty($_POST['token'])) {
                throw new Exception('Token is required');
            }

            $token = trim($_POST['token']);
            $current_time = date('Y-m-d H:i:s');
            
            try {
                $stmt = $conn->prepare("SELECT user_id, expiry_timestamp FROM password_reset_tokens 
                                      WHERE token = :token 
                                      AND expiry_timestamp > :current_time 
                                      AND (used = 0 OR used IS NULL)");
                $stmt->bindParam(':token', $token);
                $stmt->bindParam(':current_time', $current_time);
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    // Add debug logging
                    error_log("Token check - Current time: " . $current_time);
                    error_log("Token check - Expiry time: " . $result['expiry_timestamp']);
                    
                    $response['success'] = true;
                    $response['message'] = 'Token valid';
                } else {
                    $response['message'] = 'Invalid or expired token';
                }
            } catch (PDOException $e) {
                throw new Exception('Database error: ' . $e->getMessage());
            }
            break;

        case 'reset_password':
            // Update the expiry check here too
            $token = $_POST['token'];
            $password = $_POST['password'];
            $current_time = date('Y-m-d H:i:s');

            $stmt = $conn->prepare("SELECT user_id FROM password_reset_tokens 
                                  WHERE token = ? 
                                  AND expiry_timestamp > ? 
                                  AND (used = 0 OR used IS NULL)");
            $stmt->execute([$token, $current_time]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $user_id = $result['user_id'];
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $user_id]);

                $stmt = $conn->prepare("UPDATE password_reset_tokens SET used = 1 WHERE token = ?");
                $stmt->execute([$token]);

                $response['success'] = true;
                $response['message'] = 'Password successfully reset';
            } else {
                $response['message'] = 'Invalid or expired token';
            }
            break;
    }
} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
} finally {
    // Clean any output before sending JSON
    if (ob_get_length()) ob_clean();
    
    // Ensure we have a valid response array
    if (!isset($response) || !is_array($response)) {
        $response = ['success' => false, 'message' => 'Unknown error occurred'];
    }
    
    // Send JSON response
    echo json_encode($response);
    exit;
}
