<?php
include('include/db_connection.php'); // Ensure the database connection is correct

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the form data
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $address = $_POST['address'];
    $phone_number = $_POST['phone_number'];
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
            $sql = "INSERT INTO users (first_name, last_name, address, phone_number, username, email, password) VALUES (:first_name, :last_name, :address, :phone_number, :username, :email, :password)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':first_name', $first_name, PDO::PARAM_STR);
            $stmt->bindParam(':last_name', $last_name, PDO::PARAM_STR);
            $stmt->bindParam(':address', $address, PDO::PARAM_STR);
            $stmt->bindParam(':phone_number', $phone_number, PDO::PARAM_STR);
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
                <!-- First Name and Last Name in one row -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" name="first_name" id="first_name" placeholder="Enter your first name" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" name="last_name" id="last_name" placeholder="Enter your last name" required>
                    </div>
                </div>

                <!-- Address -->
                <label for="address">Address</label>
                <input type="text" name="address" id="address" placeholder="Enter your address" required>

                <!-- Phone Number -->
                <label for="phone_number">Phone Number</label>
                <input type="text" name="phone_number" id="phone_number" placeholder="Enter your phone number" required>

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