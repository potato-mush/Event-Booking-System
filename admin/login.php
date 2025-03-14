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
            </form>
        </div>
    </div>
</body>

</html>
