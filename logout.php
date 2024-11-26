<?php
// logout.php

// Start the session
session_start();

// Unset all session variables
session_unset();

// Destroy the session
session_destroy();

// Redirect to the login page or homepage
header("Location: index.php"); // Replace 'login.php' with the desired redirection URL
exit();
?>