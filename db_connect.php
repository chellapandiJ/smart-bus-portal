<?php
$host = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "bus_portal";

// Set Timezone
date_default_timezone_set('Asia/Kolkata');

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: Please ensure XAMPP MySQL is running. (Error: " . $conn->connect_error . ")");
}

// Set MySQL session timezone
$conn->query("SET time_zone = '+05:30'");

// Auto-reset expired wallet balances to 0
$conn->query("UPDATE wallet SET balance = 0 WHERE expiry_date IS NOT NULL AND expiry_date < NOW()");
?>
