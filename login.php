<?php
session_start();
include 'include/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_user_id'] = $user['id'];
        $_SESSION['user_username'] = $user['username'];
        header('Location: index.php');
        exit();
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
            transition: background-color 0.3s;
        }
        .modal-content button:hover {
            background-color: #45a049;
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
            transition: all 0.3s;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .close-modal:hover {
            background: #d32f2f;
            transform: scale(1.1);
        }
        .modal-content .error {
            color: #f44336;
            font-size: 14px;
            margin-top: 5px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <!-- Octagon on the left with logo -->
        <div class="octagon-container">
            <img src="assets/images/logo.png" alt="Logo" class="logo">
        </div>

        <!-- Login form on the right -->
        <div class="login-form-container">
            <div class="form-header">
                <i class="fas fa-user-circle"></i> <!-- Icon at the top -->
                <h2>User Login</h2>
            </div>

            <!-- Login Form -->
            <form action="login.php" method="POST" class="login-form">
                <?php if (isset($error)) : ?>
                    <div class="error-message" style="color: red; text-align: center; margin-bottom: 10px;">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                <!-- Username -->
                <input type="text" name="username" placeholder="Username" required>
                <!-- Password -->
                <input type="password" name="password" placeholder="Password" required>

                <!-- Login button -->
                <button type="submit" class="btn-login">Login</button>

                <!-- Forgot password link -->
                <a href="#" class="forgot-password">Forgot Password?</a>

                <!-- Sign up link -->
                <p>Don't have an account? <a href="signup.php">Click here to sign up</a></p>
            </form>
        </div>
    </div>

    <!-- Add these modals before the closing body tag -->
    <div id="resetRequestModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('resetRequestModal')">&times;</span>
            <h3>Reset Password</h3>
            <p>Enter your email address to receive a reset token.</p>
            <input type="email" id="resetEmail" placeholder="Enter your email">
            <button onclick="requestReset()">Send Reset Token</button>
        </div>
    </div>

    <div id="tokenModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('tokenModal')">&times;</span>
            <h3>Enter Reset Token</h3>
            <p>Please enter the token sent to your email.</p>
            <input type="text" id="resetToken" placeholder="Enter token">
            <button onclick="verifyToken()">Verify Token</button>
        </div>
    </div>

    <div id="newPasswordModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('newPasswordModal')">&times;</span>
            <h3>Set New Password</h3>
            <input type="password" id="newPassword" placeholder="New password">
            <input type="password" id="confirmPassword" placeholder="Confirm password">
            <button onclick="resetPassword()">Reset Password</button>
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
            fetch('include/reset_password.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=request_reset&email=${encodeURIComponent(email)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('resetRequestModal').style.display = 'none';
                    document.getElementById('tokenModal').style.display = 'block';
                }
                alert(data.message);
            });
        }

        function verifyToken() {
            const token = document.getElementById('resetToken').value;
            fetch('include/reset_password.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=verify_token&token=${encodeURIComponent(token)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('tokenModal').style.display = 'none';
                    document.getElementById('newPasswordModal').style.display = 'block';
                }
                alert(data.message);
            });
        }

        function resetPassword() {
            const token = document.getElementById('resetToken').value;
            const password = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (password !== confirmPassword) {
                alert('Passwords do not match');
                return;
            }

            fetch('include/reset_password.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=reset_password&token=${encodeURIComponent(token)}&password=${encodeURIComponent(password)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('newPasswordModal').style.display = 'none';
                }
                alert(data.message);
            });
        }
    </script>
</body>

</html>