<?php
session_start();
include('include/db_connection.php'); // Make sure your database connection is correct

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare and execute query to fetch user data
    $sql = "SELECT * FROM users WHERE username = :username";
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

            // Update last_login column
            $update_sql = "UPDATE users SET last_login = NOW() WHERE id = :id";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bindParam(':id', $user['id'], PDO::PARAM_INT);
            $update_stmt->execute();

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
    <title>Login</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
</body>

</html>