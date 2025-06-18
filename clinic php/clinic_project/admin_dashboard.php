<?php
require_once 'includes/db.php'; // For database operations
require_once 'includes/header.php'; // Includes session_start, functions.php, and HTML head

if (!is_logged_in() || get_user_role() !== 'admin') {
    redirect('index.php'); // Redirect if not logged in or not an admin
}

$page_title = "Admin Dashboard";

// Example data - in a real app, fetch this from the DB
$total_users = 0;
$total_appointments_today = 0;
$total_doctors = 0;

$stmt_users = $conn->query("SELECT COUNT(*) as count FROM users");
if ($stmt_users) $total_users = $stmt_users->fetch_assoc()['count'];

$today_date = date("Y-m-d");
$stmt_app_today = $conn->prepare("SELECT COUNT(*) as count FROM appointments WHERE appointment_date = ?");
if ($stmt_app_today) {
    $stmt_app_today->bind_param("s", $today_date);
    $stmt_app_today->execute();
    $res_app_today = $stmt_app_today->get_result();
    $total_appointments_today = $res_app_today->fetch_assoc()['count'];
    $stmt_app_today->close();
}

$stmt_doctors = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'doctor'");
if ($stmt_doctors) {
    $stmt_doctors->execute();
    $res_doctors = $stmt_doctors->get_result();
    $total_doctors = $res_doctors->fetch_assoc()['count'];
    $stmt_doctors->close();
}

?>
<script>
    document.title = "<?php echo $page_title; ?> - ClinicSys";
</script>

<div class="dashboard-header">
    <h1>Administrator Control Panel</h1>
    <p>Oversee clinic operations, manage users, and view system analytics.</p>
</div>

<div class="dashboard-grid">
    <div class="dashboard-card animated-card">
        <h3>User Management</h3>
        <p>Total Users: <strong class="text-accent"><?php echo $total_users; ?></strong>. Add, edit, or remove users (patients, doctors, nurses).</p>
        <a href="manage_users.php" class="card-link">Manage Users</a> <!-- Placeholder -->
    </div>

    <div class="dashboard-card animated-card">
        <h3>Appointments Overview</h3>
        <p>Today's Appointments: <strong class="text-accent"><?php echo $total_appointments_today; ?></strong>. View and manage all scheduled appointments.</p>
        <a href="appointments.php" class="card-link">View All Appointments</a>
    </div>

    <div class="dashboard-card animated-card">
        <h3>Staff Management</h3>
        <p>Total Doctors: <strong class="text-accent"><?php echo $total_doctors; ?></strong>. Manage doctor and nurse profiles and schedules.</p>
        <a href="manage_staff.php" class="card-link">Manage Staff</a> <!-- Placeholder -->
    </div>

    <div class="dashboard-card animated-card">
        <h3>Payment Reports</h3>
        <p>Access financial records, view payment histories, and generate reports.</p>
        <a href="payments.php" class="card-link">View Payment Reports</a>
    </div>

    <div class="dashboard-card animated-card">
        <h3>Clinic Settings</h3>
        <p>Configure clinic working hours, consultation fees, and other system parameters.</p>
        <a href="clinic_settings.php" class="card-link">Adjust Settings</a> <!-- Placeholder -->
    </div>
    
    <div class="dashboard-card animated-card">
        <h3>System Logs</h3>
        <p>Monitor system activity and review logs for troubleshooting.</p>
        <a href="system_logs.php" class="card-link">View Logs</a> <!-- Placeholder -->
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>

