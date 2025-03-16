<?php
session_start();
require_once 'includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare and execute query to fetch user data
    $sql = "SELECT * FROM admin WHERE username = :username";
    $stmt = $conn->prepare($sql);

    // Bind the username parameter using PDO
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);

    // Execute the statement
    $stmt->execute();

    // Check if a user exists with the given username
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify password
        if (password_verify($password, $user['password'])) {
            // Password is correct, start session and redirect
            $_SESSION['admin_user_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['admin_role'] = $user['role'];
            header('Location: index.php'); // Redirect to the dashboard or main page
            exit;
        } else {
            // Invalid password
            $error = "Invalid password.";
        }
    } else {
        // No user found with that username
        $error = "No user found with that username.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Kevin's Restaurant and Resort</title>
    <link rel="stylesheet" href="assets/css/login-styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <div class="login-container">
        <!-- Octagon on the left with logo -->
        <div class="octagon-container">
            <img src="assets/images/logo.png" alt="Logo" class="logo">
        </div>

        <!-- Login form on the right -->
        <div class="login-box">
            <div class="form-header">
                <i class="fas fa-user-circle"></i> <!-- Icon at the top -->
                <h2>Admin Login</h2>
            </div>

            <?php if (isset($error)) : ?>
                <div class="error-message" style="color: red; text-align: center; margin-bottom: 10px;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="login-form">
                <div class="form-group">
                    <input type="text" id="username" name="username" placeholder="Username" required>
                </div>

                <div class="form-group">
                    <input type="password" id="password" name="password" placeholder="Password" required>
                </div>

                <button type="submit" class="btn-login">Login</button>
                <a href="#" class="forgot-password">Forgot Password?</a>
            </form>
        </div>
    </div>

    <!-- Simplify to single reset modal -->
    <div id="resetRequestModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('resetRequestModal')">&times;</span>
            <h3>Reset Password</h3>
            <p>Enter your email address to receive a new password.</p>
            <input type="email" id="resetEmail" placeholder="Enter your email">
            <button onclick="requestReset()">Reset Password</button>
        </div>
    </div>

    <script>
    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }

    document.querySelector('.forgot-password').addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('resetRequestModal').style.display = 'block';
    });

    function requestReset() {
        const email = document.getElementById('resetEmail').value;
        fetch('includes/admin_reset_password.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `action=request_reset&email=${encodeURIComponent(email)}`
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                document.getElementById('resetRequestModal').style.display = 'none';
            }
        });
    }
    </script>

    <style>
    .modal {
        display: none;
        position: fixed;
        z-index: 1;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.6);
    }
    .modal-content {
        position: relative;
        background-color: #fefefe;
        margin: 10% auto;
        padding: 30px;
        border: none;
        width: 90%;
        max-width: 400px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    }
    .modal-content h3 {
        color: #333;
        margin-bottom: 20px;
        text-align: center;
        font-size: 1.5em;
    }
    .modal-content p {
        color: #666;
        margin-bottom: 20px;
        text-align: center;
    }
    .modal-content input {
        width: 100%;
        padding: 12px;
        margin: 8px 0;
        border: 1px solid #ddd;
        border-radius: 5px;
        box-sizing: border-box;
        font-size: 14px;
    }
    .modal-content button {
        width: 100%;
        padding: 12px;
        background-color: #4CAF50;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        margin-top: 15px;
        font-size: 16px;
    }
    .close-modal {
        position: absolute;
        right: -15px;
        top: -15px;
        width: 30px;
        height: 30px;
        background: #f44336;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 18px;
    }
    .forgot-password {
        display: block;
        text-align: center;
        margin-top: 15px;
        color: #666;
        text-decoration: none;
    }
    .forgot-password:hover {
        color: #4CAF50;
    }
    </style>
</body>

</html>
