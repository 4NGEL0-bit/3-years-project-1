<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'functions.php'; // Ensures functions like is_logged_in() are available

$user_name = isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Guest';
$user_role = isset($_SESSION['user_role']) ? htmlspecialchars($_SESSION['user_role']) : '';

// Basic navigation based on role - can be expanded
$nav_links = [];
if (is_logged_in()) {
    $nav_links['Dashboard'] = 'dashboard.php'; // This will redirect to specific dashboard
    if ($user_role === 'patient') {
        $nav_links['Book Appointment'] = 'book_appointment.php';
        $nav_links['My Appointments'] = 'appointments.php';
    }
    if ($user_role === 'doctor') {
        $nav_links['View Appointments'] = 'appointments.php';
        // Add more doctor specific links here
    }
    if ($user_role === 'admin') {
        // Add admin specific links here
        $nav_links['Manage Users'] = '#'; // Placeholder
        $nav_links['Clinic Settings'] = '#'; // Placeholder
    }
    $nav_links['Logout'] = 'logout.php';
} else {
    $nav_links['Login'] = 'index.php';
    $nav_links['Register'] = 'register.php';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- The title will be set by each individual page -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&family=Orbitron:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard_style.css"> <!-- New stylesheet for dashboard specific styles -->
</head>
<body>
    <header class="main-header">
        <div class="header-container">
            <div class="logo">
                <a href="<?php echo is_logged_in() ? 'dashboard.php' : 'index.php'; ?>">ClinicSys</a>
            </div>
            <nav class="main-nav">
                <ul>
                    <?php foreach ($nav_links as $title => $url): ?>
                        <li><a href="<?php echo $url; ?>"><?php echo $title; ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </nav>
            <?php if (is_logged_in()): ?>
            <div class="user-info">
                <span>Welcome, <?php echo $user_name; ?> (<?php echo ucfirst($user_role); ?>)</span>
            </div>
            <?php endif; ?>
        </div>
    </header>
    <main class="main-content">
        <!-- Page specific content will go here -->

