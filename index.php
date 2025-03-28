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
    <style>
    .user-menu {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .notification-icon {
        position: relative;
        cursor: pointer;
    }

    .notification-count {
        position: absolute;
        top: -8px;
        right: -8px;
        background-color: #e74c3c;
        color: white;
        border-radius: 50%;
        padding: 2px 6px;
        font-size: 12px;
    }

    .notification-dropdown {
        display: none;
        position: absolute;
        right: 0;
        top: 100%;
        width: 300px;
        background: white;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        border-radius: 4px;
        z-index: 1000;
    }

    .notification-list {
        max-height: 300px;
        overflow-y: auto;
        padding: 10px;
    }

    .notification-item {
        color: #000;
        padding: 10px;
        border-bottom: 1px solid #eee;
    }

    .notification-item:last-child {
        border-bottom: none;
    }

    .notification-empty {
        text-align: center;
        padding: 20px;
        color: #666;
        font-style: italic;
    }
    </style>
</head>

<body>
    <header class="header">
        <!-- Logo as an image -->
        <img src="assets/images/logo.png" style="width: auto; height: 90px;" alt="EventPlanner Logo" class="logo">

        <!-- Show login button if not logged in or welcome message if logged in -->
        <?php if ($loggedIn): ?>
            <div class="user-menu">
                <p class="username">Welcome back, <?php echo htmlspecialchars($_SESSION['user_username']); ?></p>
                <div class="notification-icon">
                    <i class="fas fa-bell"></i>
                    <span class="notification-count" style="display: none;">0</span>
                    <div class="notification-dropdown">
                        <div class="notification-list"></div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <button class="login-btn" onclick="window.location.href='login.php';">Login</button>
        <?php endif; ?>
    </header>

    <div class="main">
        <nav class="sidebar">
            <ul>
                <li><a href="index.php?page=dashboard" class="<?php echo $page === 'dashboard' ? 'active' : ''; ?>">Dashboard</a></li>
                <li><a href="index.php?page=catering-packages" class="<?php echo $page === 'catering-packages' ? 'active' : ''; ?>">Catering Packages</a></li>
                <li><a href="index.php?page=customize-events" class="<?php echo $page === 'customize-events' ? 'active' : ''; ?>">Customize Package</a></li>
                <li><a href="index.php?page=contact-us" class="<?php echo $page === 'contact-us' ? 'active' : ''; ?>">Contact Us</a></li>
                <?php if ($loggedIn): ?>
                <li><a href="index.php?page=manage-bookings" class="<?php echo $page === 'manage-bookings' ? 'active' : ''; ?>">Manage Bookings</a></li>
                <?php endif; ?>
            </ul>

            <?php if ($loggedIn): ?>
                <a href="logout.php" class="logout-btn">Logout</a> <!-- Logout button at the bottom -->
            <?php endif; ?>
        </nav>

        <div class="content <?php echo $page === 'dashboard' ? 'dashboard-page' : ''; ?>">
            <?php
            // index.php
            $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
            $allowedPages = ['dashboard', 'catering-packages', 'customize-events', 'contact-us', 'package-detail', 'receipt', 'manage-bookings'];
            if (in_array($page, $allowedPages)) {
                if (($page === 'receipt' || $page === 'manage-bookings') && !$loggedIn) {
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

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const notificationIcon = document.querySelector('.notification-icon');
        const notificationDropdown = document.querySelector('.notification-dropdown');
        
        if (notificationIcon) {
            notificationIcon.addEventListener('click', function() {
                notificationDropdown.style.display = notificationDropdown.style.display === 'block' ? 'none' : 'block';
                if (notificationDropdown.style.display === 'block') {
                    fetchNotifications();
                }
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(event) {
                if (!notificationIcon.contains(event.target)) {
                    notificationDropdown.style.display = 'none';
                }
            });

            // Check for new notifications every minute
            setInterval(checkNotifications, 60000);
            checkNotifications();
        }
    });

    function checkNotifications() {
        fetch('include/get_notifications.php')
            .then(response => response.json())
            .then(data => {
                const count = document.querySelector('.notification-count');
                if (data.unread > 0) {
                    count.textContent = data.unread;
                    count.style.display = 'block';
                } else {
                    count.style.display = 'none';
                }
            });
    }

    function fetchNotifications() {
        fetch('include/get_notifications.php?full=1')
            .then(response => response.json())
            .then(data => {
                const list = document.querySelector('.notification-list');
                if (!data.notifications || data.notifications.length === 0) {
                    list.innerHTML = '<div class="notification-empty">No notifications</div>';
                    return;
                }
                list.innerHTML = data.notifications.map(notification => `
                    <div class="notification-item">
                        <p>${notification.message || 'No message content'}</p>
                        <small>${notification.created_at || 'No date'}</small>
                    </div>
                `).join('');
            });
    }
    </script>
</body>

</html>