<?php
// Start the session
session_start();

// Unset the user-specific session variables
unset($_SESSION['user_user_id']);
unset($_SESSION['user_username']);

// Redirect the user to the login page
header('Location: login.php');
exit();
