<?php
require_once 'includes/db.php'; // For database operations
require_once 'includes/header.php'; // Includes session_start, functions.php, and HTML head

if (!is_logged_in() || get_user_role() !== 'doctor') {
    redirect('index.php'); // Redirect if not logged in or not a doctor
}

$doctor_id = $_SESSION['user_id'];
$page_title = "Doctor Dashboard";

// Fetch data for dashboard cards
$today_appointments_count = 0;
$pending_diagnoses_count = 0;

$today_date = date("Y-m-d");
$stmt_today_app = $conn->prepare("SELECT COUNT(*) as count FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND status IN ('scheduled', 'checked-in', 'ready_for_doctor')");
if ($stmt_today_app) {
    $stmt_today_app->bind_param("is", $doctor_id, $today_date);
    $stmt_today_app->execute();
    $res_today_app = $stmt_today_app->get_result();
    $today_appointments_count = $res_today_app->fetch_assoc()['count'];
    $stmt_today_app->close();
}

// Example: Count appointments that are completed but might need diagnosis notes (simplified)
$stmt_pending_diag = $conn->prepare("SELECT COUNT(a.id) as count FROM appointments a LEFT JOIN medical_notes mn ON a.id = mn.appointment_id WHERE a.doctor_id = ? AND a.status = 'completed' AND mn.id IS NULL");
if ($stmt_pending_diag) {
    $stmt_pending_diag->bind_param("i", $doctor_id);
    $stmt_pending_diag->execute();
    $res_pending_diag = $stmt_pending_diag->get_result();
    $pending_diagnoses_count = $res_pending_diag->fetch_assoc()['count'];
    $stmt_pending_diag->close();
}

?>
<script>
    document.title = "<?php echo $page_title; ?> - ClinicSys";
</script>

<div class="dashboard-header">
    <h1>Doctor's Command Center</h1>
    <p>Manage your patient schedule, record diagnoses, and access patient histories efficiently.</p>
</div>

<div class="dashboard-grid">
    <div class="dashboard-card animated-card">
        <h3>Today's Patients</h3>
        <p>You have <strong class="text-accent"><?php echo $today_appointments_count; ?></strong> patient(s) scheduled for today.</p>
        <a href="appointments.php?filter=today" class="card-link">View Today's Schedule</a>
    </div>

    <div class="dashboard-card animated-card">
        <h3>Pending Diagnoses</h3>
        <p><strong class="text-accent"><?php echo $pending_diagnoses_count; ?></strong> appointment(s) may require diagnosis notes.</p>
        <a href="appointments.php?filter=pending_diagnosis" class="card-link">Add/Update Notes</a>
    </div>

    <div class="dashboard-card animated-card">
        <h3>Full Appointment List</h3>
        <p>Access all your past and upcoming appointments.</p>
        <a href="appointments.php" class="card-link">View All Appointments</a>
    </div>

    <div class="dashboard-card animated-card">
        <h3>Patient History</h3>
        <p>Search and view detailed medical histories of your patients.</p>
        <a href="patient_records.php" class="card-link">Access Patient Records</a> <!-- Placeholder -->
    </div>

    <div class="dashboard-card animated-card">
        <h3>Referrals</h3>
        <p>Manage patient referrals to other specialists or clinics.</p>
        <a href="referrals.php" class="card-link">Manage Referrals</a> <!-- Placeholder -->
    </div>
    
    <div class="dashboard-card animated-card">
        <h3>My Profile & Settings</h3>
        <p>Update your professional profile and notification preferences.</p>
        <a href="profile.php" class="card-link">Edit Profile</a> <!-- Placeholder -->
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>

