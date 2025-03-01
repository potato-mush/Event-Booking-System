<?php
session_start();

// Unset the admin-specific session variables
unset($_SESSION['admin_user_id']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_role']);

// Redirect the admin to the login page
header('Location: login.php');
exit();
