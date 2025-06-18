<?php
require_once 'includes/db.php'; // For database operations
require_once 'includes/header.php'; // Includes session_start, functions.php, and HTML head

if (!is_logged_in() || get_user_role() !== 'nurse') {
    redirect('index.php'); // Redirect if not logged in or not a nurse
}

$nurse_id = $_SESSION['user_id'];
$page_title = "Nurse Dashboard";

// Fetch data for dashboard cards
$today_check_ins_pending = 0;
$patients_ready_for_doctor = 0;

$today_date = date("Y-m-d");
// Count appointments scheduled for today that are not yet 'checked-in' or 'ready_for_doctor' or 'completed'
$stmt_pending_checkin = $conn->prepare("SELECT COUNT(*) as count FROM appointments WHERE appointment_date = ? AND status = 'scheduled'");
if ($stmt_pending_checkin) {
    $stmt_pending_checkin->bind_param("s", $today_date);
    $stmt_pending_checkin->execute();
    $res_pending_checkin = $stmt_pending_checkin->get_result();
    $today_check_ins_pending = $res_pending_checkin->fetch_assoc()['count'];
    $stmt_pending_checkin->close();
}

// Count patients marked as 'checked-in' but not yet 'ready_for_doctor' or 'completed'
$stmt_ready_doctor = $conn->prepare("SELECT COUNT(*) as count FROM appointments WHERE appointment_date = ? AND status = 'checked-in'");
if ($stmt_ready_doctor) {
    $stmt_ready_doctor->bind_param("s", $today_date);
    $stmt_ready_doctor->execute();
    $res_ready_doctor = $stmt_ready_doctor->get_result();
    $patients_ready_for_doctor = $res_ready_doctor->fetch_assoc()['count']; // This actually shows patients who are checked-in, nurse might make them ready
    $stmt_ready_doctor->close();
}

?>
<script>
    document.title = "<?php echo $page_title; ?> - ClinicSys";
</script>

<div class="dashboard-header">
    <h1>Nurse Station Hub</h1>
    <p>Manage patient flow, update statuses, and assist with daily clinic operations.</p>
</div>

<div class="dashboard-grid">
    <div class="dashboard-card animated-card">
        <h3>Today's Appointments</h3>
        <p>View all appointments scheduled for today to manage patient arrivals.</p>
        <a href="appointments.php?filter=today_all_staff" class="card-link">View Full Schedule</a>
    </div>

    <div class="dashboard-card animated-card">
        <h3>Pending Check-ins</h3>
        <p><strong class="text-accent"><?php echo $today_check_ins_pending; ?></strong> patient(s) scheduled today are awaiting check-in.</p>
        <a href="appointments.php?filter=pending_checkin" class="card-link">Manage Check-ins</a>
    </div>

    <div class="dashboard-card animated-card">
        <h3>Patient Vitals & Intake</h3>
        <p>Record patient vitals (weight, blood pressure, etc.) and intake information.</p>
        <a href="#" class="card-link">Record Vitals</a> <!-- Placeholder: Link to a specific patient's intake form from appointment list -->
    </div>

    <div class="dashboard-card animated-card">
        <h3>Patients Ready for Doctor</h3>
        <p><strong class="text-accent"><?php echo $patients_ready_for_doctor; ?></strong> patient(s) are checked-in and can be marked ready for the doctor.</p>
        <a href="appointments.php?filter=checked_in" class="card-link">Update Status</a>
    </div>

    <div class="dashboard-card animated-card">
        <h3>Equipment & Supplies</h3>
        <p>Check and manage inventory of medical supplies and equipment status.</p>
        <a href="#" class="card-link">Manage Inventory</a> <!-- Placeholder -->
    </div>
    
    <div class="dashboard-card animated-card">
        <h3>My Tasks & Notifications</h3>
        <p>View assigned tasks and important notifications for the day.</p>
        <a href="#" class="card-link">View Tasks</a> <!-- Placeholder -->
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>

