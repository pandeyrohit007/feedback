<?php
session_start();

// Capture user type before destroying the session
$userType = $_SESSION['user_type'] ?? null;

// Clear the session
session_unset();
session_destroy();

// Redirect based on user type
if ($userType === 'admin') {
    header("Location: admin_login.php");
} else {
    header("Location: customer_login.php");
}
exit();
