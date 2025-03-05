<?php
session_start();
require_once 'includes/db_connection.php';

// Check if the user is logged in
$loggedIn = isset($_SESSION['admin_username']);  // Assuming you store the username in session when logged in

// Redirect to login page if not logged in
if (!$loggedIn) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/dashboard-styles.css">
    <link rel="stylesheet" href="assets/css/bookings-styles.css">
    <link rel="stylesheet" href="assets/css/calendar-styles.css">
    <link rel="stylesheet" href="assets/css/customers-styles.css">
    <link rel="stylesheet" href="assets/css/reports-styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <header class="header">
        <!-- Logo as an image -->
        <img src="assets/images/logo.png" style="width: auto; height: 90px;" alt="EventPlanner Logo" class="logo">

        <!-- Show login button if not logged in or welcome message if logged in -->
        <?php if ($loggedIn): ?>
            <p class="username">Welcome back, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></p>
        <?php else: ?>
            <button class="login-btn" onclick="window.location.href='login.php';">Login</button>
        <?php endif; ?>
    </header>

    <div class="main">
        <nav class="sidebar">
            <ul>
                <li><a href="index.php?page=dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="index.php?page=bookings"><i class="fas fa-calendar-check"></i> Bookings</a></li>
                <li><a href="index.php?page=calendar"><i class="fas fa-calendar-alt"></i> Calendar</a></li>
                <li><a href="index.php?page=customer"><i class="fas fa-users"></i> Customers</a></li>
                <li><a href="index.php?page=reports"><i class="fas fa-chart-bar"></i> Reports</a></li>
            </ul>

            <?php if ($loggedIn): ?>
                <a href="logout.php" class="logout-btn">Logout</a> <!-- Logout button at the bottom -->
            <?php endif; ?>
        </nav>

        <div class="content">
            <?php
            // Handle page content
            $page = $_GET['page'] ?? 'dashboard';
            $page_file = "{$page}.php";

            if (file_exists($page_file)) {
                include $page_file;
            } else {
                include 'dashboard.php';
            }
            ?>
        </div>
    </div>
</body>

</html>