<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Timeout duration: 10 minutes (600 seconds)
$timeout_duration = 600;

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    // Last request was more than 10 minutes ago
    session_unset();     // unset $_SESSION variable for the run-time 
    session_destroy();   // destroy session data in storage
    header("Location: ../login.php?msg=timeout");
    exit();
}

$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
?>
