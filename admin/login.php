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
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
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
    <style>
        .login-form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .form-group {
            width: 100%;
            max-width: 300px;
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
        }
        .login-btn {
            width: 100%;
            max-width: 300px;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }
        .signup-link {
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-box">
            <img src="assets/images/logo.png" alt="Logo" class="logo">
            <h2>Admin Login</h2>

            <?php if (isset($error)) : ?>
                <div class="error-message" style="color: red; text-align: center; margin-bottom: 10px;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="login-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="login-btn">Login</button>
                <p class="signup-link">Don't have an account? <a href="signup.php">Sign up here</a></p>
            </form>
        </div>
    </div>
</body>

</html>
