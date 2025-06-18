<?php
require_once 'includes/db.php'; // For database operations
require_once 'includes/header.php'; // Includes session_start, functions.php, and HTML head

if (!is_logged_in() || get_user_role() !== 'patient') {
    redirect('index.php'); // Redirect if not logged in or not a patient
}

$patient_id = $_SESSION['user_id'];
$page_title = "Patient Dashboard"; // Set page title for header

// Fetch patient name for a personalized welcome - already handled in header.php
// $stmt_user = $conn->prepare("SELECT nom FROM utilisateurs WHERE id = ?");
// $stmt_user->bind_param("i", $patient_id);
// $stmt_user->execute();
// $user_result = $stmt_user->get_result();
// $user_details = $user_result->fetch_assoc();
// $patient_name = $user_details ? htmlspecialchars($user_details['nom']) : 'Patient';
// $stmt_user->close();

// Placeholder data for upcoming appointments and recent activity
// In a real application, these would be fetched from the database
$upcoming_appointments_count = 0; // Example: fetch count from appointments table
$stmt_app_count = $conn->prepare("SELECT COUNT(*) as count FROM appointments WHERE patient_id = ? AND appointment_date >= CURDATE() AND status = 'scheduled'");
if ($stmt_app_count) {
    $stmt_app_count->bind_param("i", $patient_id);
    $stmt_app_count->execute();
    $count_result = $stmt_app_count->get_result();
    if ($count_row = $count_result->fetch_assoc()) {
        $upcoming_appointments_count = $count_row['count'];
    }
    $stmt_app_count->close();
}

$recent_diagnosis = "No recent diagnosis available."; // Example: fetch last diagnosis
$stmt_diag = $conn->prepare("SELECT diagnosis_text, recommendations, DATE(mn.created_at) as diagnosis_date FROM medical_notes mn JOIN appointments a ON mn.appointment_id = a.id WHERE mn.patient_id = ? ORDER BY mn.created_at DESC LIMIT 1");
if ($stmt_diag) {
    $stmt_diag->bind_param("i", $patient_id);
    $stmt_diag->execute();
    $diag_result = $stmt_diag->get_result();
    if ($diag_row = $diag_result->fetch_assoc()) {
        $recent_diagnosis = "On " . htmlspecialchars($diag_row['diagnosis_date']) . ": " . htmlspecialchars(substr($diag_row['diagnosis_text'], 0, 100)) . "...";
    }
    $stmt_diag->close();
}

?>
<script>
    document.title = "<?php echo $page_title; ?> - ClinicSys";
</script>

<div class="dashboard-header">
    <h1>Welcome to Your Health Portal</h1>
    <p>Manage your appointments, view your medical history, and connect with your healthcare providers.</p>
</div>

<div class="dashboard-grid">
    <div class="dashboard-card animated-card">
        <h3>View Profile</h3>
        <p>Review and update your personal information and contact details.</p>
        <a href="profile.php" class="card-link">Go to Profile</a> <!-- Placeholder link -->
    </div>

    <div class="dashboard-card animated-card">
        <h3>Book Appointment</h3>
        <p>Schedule a new appointment with our available doctors at your convenience.</p>
        <a href="book_appointment.php" class="card-link">Book Now</a>
    </div>

    <div class="dashboard-card animated-card">
        <h3>Upcoming Appointments</h3>
        <p>You have <strong class="text-accent"><?php echo $upcoming_appointments_count; ?></strong> upcoming appointment(s).</p>
        <a href="appointments.php" class="card-link">View All Appointments</a>
    </div>

    <div class="dashboard-card animated-card">
        <h3>Medical History</h3>
        <p>Access your past diagnoses, doctor's notes, and treatment plans.</p>
        <p><em>Latest: <?php echo htmlspecialchars($recent_diagnosis); ?></em></p>
        <a href="medical_history.php" class="card-link">View Full History</a> <!-- Placeholder link -->
    </div>

    <div class="dashboard-card animated-card">
        <h3>Invoices & Payments</h3>
        <p>View your billing statements and manage payments securely.</p>
        <a href="payments.php" class="card-link">Manage Payments</a>
    </div>

    <div class="dashboard-card animated-card">
        <h3>Download Summary</h3>
        <p>Get a printable summary of your visits, diagnoses, and prescriptions.</p>
        <a href="download_summary.php" class="card-link">Download Visit Summary</a> <!-- Placeholder link -->
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>

