<?php
include('include/db_connection.php'); // Ensure the database connection is correct

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the form data
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if passwords match
    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if username or email already exists in the database
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username OR email = :email");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $error = "Username or Email already exists.";
        } else {
            // Hash the password before saving it
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert the new user into the database
            $sql = "INSERT INTO users (username, email, password) VALUES (:username, :email, :password)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);

            if ($stmt->execute()) {
                header('Location: login.php'); // Redirect to login page after successful registration
                exit;
            } else {
                $error = "An error occurred while registering.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/signup.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <div class="signup-container">
        <!-- Octagon on the left with smaller logo -->
        <div class="octagon-container">
            <img src="assets/images/logo.png" alt="Logo" class="logo">
        </div>

        <!-- Registration Form centered -->
        <div class="registration-form-container">
            <div class="form-header">
                <!-- Display error message -->
                <?php if (isset($error)) : ?>
                    <div class="error-message" style="color: red; text-align: center; margin-bottom: 10px;">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <h2>Registration Form</h2>
            </div>

            <form action="signup.php" method="POST" class="registration-form">
                <!-- Username -->
                <label for="username">Username</label>
                <input type="text" name="username" id="username" placeholder="Enter your username" required>

                <!-- Email -->
                <label for="email">Email</label>
                <input type="email" name="email" id="email" placeholder="Enter your email" required>

                <!-- Password -->
                <label for="password">Password</label>
                <input type="password" name="password" id="password" placeholder="Enter your password" required>

                <!-- Confirm Password -->
                <label for="confirm_password">Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm your password" required>

                <!-- Sign Up button -->
                <button type="submit" class="btn-signup">Sign Up</button>

                <!-- Redirect to login page -->
                <p>Already have an account? <a href="login.php">Login here</a></p>
            </form>
        </div>
    </div>
</body>

</html>