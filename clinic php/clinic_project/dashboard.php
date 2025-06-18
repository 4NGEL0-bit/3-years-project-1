<?php
// Role-Based Dashboard - Redirects users after login
require_once 'includes/functions.php';

if (!is_logged_in()) {
    redirect('index.php'); // If not logged in, redirect to login page
}

$role = get_user_role();

switch ($role) {
    case 'admin':
        redirect('admin_dashboard.php');
        break;
    case 'doctor':
        redirect('doctor_dashboard.php');
        break;
    case 'nurse':
        redirect('nurse_dashboard.php');
        break;
    case 'patient':
        redirect('patient_dashboard.php');
        break;
    default:
        // If role is not set or invalid, redirect to login with an error message (optional)
        // For now, just redirect to login
        // TODO: Add error handling or logging for unknown roles
        redirect('logout.php'); // Or index.php with an error message
        break;
}
?>
