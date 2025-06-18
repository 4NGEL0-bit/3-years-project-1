<?php
// Common functions for the application
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Ensure session is started for all files that include this
}

// Function to sanitize input data
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to redirect to a different page
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION["user_id"]);
}

// Function to check user role
function get_user_role() {
    return isset($_SESSION["user_role"]) ? $_SESSION["user_role"] : null;
}

// More functions will be added here as needed (e.g., for password hashing, date formatting, etc.)

?>
