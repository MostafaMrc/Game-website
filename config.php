<?php
// GiantBomb API Key
define('API_KEY', value: 'd0857161568198fed9c0d896bf5cef0c3ec6bbbf'); 

// Database Credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Set your actual password if required
define('DB_NAME', 'giantbomb_db');

// Database Connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}
?>
