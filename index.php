<?php
session_start(); // Start the session to access session data

// Check if the user is logged in
$loggedIn = isset($_SESSION['user_username']);  // Assuming you store the username in session when logged in

// Define the $page variable
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/catering-packages.css">
    <link rel="stylesheet" href="assets/css/customize-event.css">
    <link rel="stylesheet" href="assets/css/contact-us.css">
    <link rel="stylesheet" href="assets/css/receipt-styles.css">
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
</head>

<body>
    <header class="header">
        <!-- Logo as an image -->
        <img src="assets/images/logo.png" style="width: auto; height: 90px;" alt="EventPlanner Logo" class="logo">

        <!-- Show login button if not logged in or welcome message if logged in -->
        <?php if ($loggedIn): ?>
            <p class="username">Welcome back, <?php echo htmlspecialchars($_SESSION['user_username']); ?></p>
        <?php else: ?>
            <button class="login-btn" onclick="window.location.href='login.php';">Login</button>
        <?php endif; ?>
    </header>

    <div class="main">
        <nav class="sidebar">
            <ul>
                <li><a href="index.php?page=dashboard" class="<?php echo $page === 'dashboard' ? 'active' : ''; ?>">Dashboard</a></li>
                <li><a href="index.php?page=catering-packages" class="<?php echo $page === 'catering-packages' ? 'active' : ''; ?>">Catering Packages</a></li>
                <li><a href="index.php?page=customize-events" class="<?php echo $page === 'customize-events' ? 'active' : ''; ?>">Customize Events</a></li>
                <li><a href="index.php?page=contact-us" class="<?php echo $page === 'contact-us' ? 'active' : ''; ?>">Contact Us</a></li>
            </ul>

            <?php if ($loggedIn): ?>
                <a href="logout.php" class="logout-btn">Logout</a> <!-- Logout button at the bottom -->
            <?php endif; ?>
        </nav>

        <div class="content">
            <?php
            // index.php
            $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
            $allowedPages = ['dashboard', 'catering-packages', 'customize-events', 'contact-us', 'package-detail', 'receipt'];
            if (in_array($page, $allowedPages)) {
                if ($page === 'receipt' && !$loggedIn) {
                    header('Location: login.php');
                    exit();
                }
                include $page . '.php';
            } else {
                echo "<p>Page not found.</p>";
            }
            ?>
        </div>
    </div>
</body>

</html>