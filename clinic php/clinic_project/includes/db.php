<?php
// Database configuration
define("DB_HOST", "localhost");
define("DB_USER", "root"); // Replace with your database username
define("DB_PASS", ""); // Replace with your database password
define("DB_NAME", "clinic_db");

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set to utf8mb4 for better Unicode support
if (!$conn->set_charset("utf8mb4")) {
    // printf("Error loading character set utf8mb4: %s\n", $conn->error);
    // For now, we'll proceed, but this should be logged or handled in a production environment
}

// Function to close the database connection (optional, as PHP closes it automatically at script end)
function close_connection($connection) {
    $connection->close();
}
?>
