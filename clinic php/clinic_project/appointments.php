<?php
require_once 'includes/db.php'; // For database operations
require_once 'includes/header.php'; // Includes session_start, functions.php, and HTML head

if (!is_logged_in()) {
    redirect('index.php');
}

$user_id = $_SESSION['user_id'];
$user_role = get_user_role();
$page_title = "Manage Appointments";

$appointments = [];
$filter_condition = "";
$params = [];
$param_types = "";

// Base SQL query
$sql = "SELECT a.id, a.appointment_date, a.appointment_time, p.nom as patient_name, d.nom as doctor_name, a.status, a.reason_for_visit 
        FROM appointments a 
        JOIN users p ON a.patient_id = p.id 
        JOIN users d ON a.doctor_id = d.id";

if ($user_role === 'patient') {
    $filter_condition .= " WHERE a.patient_id = ?";
    $params[] = $user_id;
    $param_types .= "i";
} elseif ($user_role === 'doctor') {
    $filter_condition .= " WHERE a.doctor_id = ?";
    $params[] = $user_id;
    $param_types .= "i";
    if (isset($_GET['filter']) && $_GET['filter'] === 'today') {
        $filter_condition .= " AND a.appointment_date = CURDATE() AND a.status IN ('scheduled', 'checked-in', 'ready_for_doctor')";
        $page_title = "Today's Appointments";
    } elseif (isset($_GET['filter']) && $_GET['filter'] === 'pending_diagnosis') {
        $filter_condition .= " AND a.status = 'completed' AND NOT EXISTS (SELECT 1 FROM medical_notes mn WHERE mn.appointment_id = a.id)";
        $page_title = "Appointments Pending Diagnosis";
    }
} elseif ($user_role === 'admin') {
    // Admin can see all, but can filter
    if (isset($_GET['filter_doctor']) && !empty($_GET['filter_doctor'])) {
        $filter_condition .= (empty($filter_condition) ? " WHERE" : " AND") . " a.doctor_id = ?";
        $params[] = $_GET['filter_doctor'];
        $param_types .= "i";
    }
    if (isset($_GET['filter_date']) && !empty($_GET['filter_date'])) {
        $filter_condition .= (empty($filter_condition) ? " WHERE" : " AND") . " a.appointment_date = ?";
        $params[] = $_GET['filter_date'];
        $param_types .= "s";
    }
} elseif ($user_role === 'nurse') {
    // Nurse sees today's appointments, can filter by status
    $filter_condition .= " WHERE a.appointment_date = CURDATE()";
    if (isset($_GET['filter'])) {
        if ($_GET['filter'] === 'pending_checkin') {
            $filter_condition .= " AND a.status = 'scheduled'";
            $page_title = "Pending Check-ins";
        } elseif ($_GET['filter'] === 'checked_in') {
            $filter_condition .= " AND a.status = 'checked-in'";
            $page_title = "Checked-in Patients";
        }
    }
}

$sql .= $filter_condition;
$sql .= " ORDER BY a.appointment_date DESC, a.appointment_time DESC";

$stmt = $conn->prepare($sql);
if ($stmt) {
    if (!empty($params)) {
        $stmt->bind_param($param_types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
    $stmt->close();
} else {
    // Handle error in SQL prepare
    echo "<p class='error-message-login'>Error preparing SQL statement: " . $conn->error . "</p>";
}

?>
<script>
    document.title = "<?php echo $page_title; ?> - ClinicSys";
</script>

<div class="dashboard-header">
    <h1><?php echo $page_title; ?></h1>
    <p>Review and manage scheduled appointments.</p>
</div>

<?php if ($user_role === 'admin'): ?>
    <!-- Admin Filters -->
    <form method="GET" action="appointments.php" class="dashboard-form filter-form" style="max-width: 800px; margin: 20px auto; display: flex; gap: 15px; align-items: flex-end;">
        <div class="form-group" style="flex-grow: 1;">
            <label for="filter_doctor">Filter by Doctor ID</label>
            <input type="text" name="filter_doctor" id="filter_doctor" placeholder="Enter Doctor ID" value="<?php echo isset($_GET['filter_doctor']) ? htmlspecialchars($_GET['filter_doctor']) : ''; ?>">
        </div>
        <div class="form-group" style="flex-grow: 1;">
            <label for="filter_date">Filter by Date</label>
            <input type="date" name="filter_date" id="filter_date" value="<?php echo isset($_GET['filter_date']) ? htmlspecialchars($_GET['filter_date']) : ''; ?>">
        </div>
        <button type="submit" class="btn-login" style="padding: 10px 20px; height: 46px; margin-bottom: 20px;">Filter</button>
        <a href="appointments.php" class="btn-secondary" style="padding: 10px 20px; height: 46px; margin-bottom: 20px; line-height: 26px;">Clear</a>

    </form>
<?php endif; ?>

<?php if (empty($appointments)): ?>
    <div class="info-message" style="background-color: var(--secondary-color); color: var(--text-color); padding: 20px; border-radius: 8px; text-align: center; margin-top: 20px;">
        <p>No appointments found matching your criteria.</p>
        <?php if ($user_role === 'patient'): ?>
            <a href="book_appointment.php" class="btn-login" style="display: inline-block; margin-top:15px;">Book a New Appointment</a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <table class="content-table animated-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Time</th>
                <?php if ($user_role !== 'patient') echo "<th>Patient</th>"; ?>
                <?php if ($user_role !== 'doctor') echo "<th>Doctor</th>"; ?>
                <th>Reason for Visit</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($appointments as $appointment): ?>
                <tr>
                    <td><?php echo htmlspecialchars($appointment['appointment_date']); ?></td>
                    <td><?php echo htmlspecialchars(date("g:i A", strtotime($appointment['appointment_time']))); ?></td>
                    <?php if ($user_role !== 'patient') echo "<td>" . htmlspecialchars($appointment['patient_name']) . "</td>"; ?>
                    <?php if ($user_role !== 'doctor') echo "<td>" . htmlspecialchars($appointment['doctor_name']) . "</td>"; ?>
                    <td><?php echo !empty($appointment['reason_for_visit']) ? htmlspecialchars($appointment['reason_for_visit']) : '-'; ?></td>
                    <td><span class="status-<?php echo strtolower(htmlspecialchars($appointment['status'])); ?>"><?php echo ucfirst(htmlspecialchars($appointment['status'])); ?></span></td>
                    <td class="action-links">
                        <?php if ($user_role === 'doctor' && $appointment['status'] === 'completed'): ?>
                            <a href="add_diagnosis.php?appointment_id=<?php echo $appointment['id']; ?>">Add/View Diagnosis</a>
                        <?php elseif ($user_role === 'nurse' && $appointment['status'] === 'scheduled'): ?>
                            <a href="#" onclick="updateAppointmentStatus(<?php echo $appointment['id']; ?>, 'checked-in')">Check-in</a>
                        <?php elseif ($user_role === 'nurse' && $appointment['status'] === 'checked-in'): ?>
                            <a href="#" onclick="updateAppointmentStatus(<?php echo $appointment['id']; ?>, 'ready_for_doctor')">Mark Ready</a>
                        <?php endif; ?>
                        <?php if ($appointment['status'] === 'scheduled' && ($user_role === 'patient' || $user_role === 'admin')) : ?>
                             <a href="#" onclick="updateAppointmentStatus(<?php echo $appointment['id']; ?>, 'cancelled')" style="color: #dc3545;">Cancel</a>
                        <?php endif; ?>
                         <a href="#">Details</a> <!-- Placeholder for a details modal/page -->
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<script>
// Basic JS for status updates (would need AJAX in a real app for seamless updates)
function updateAppointmentStatus(appointmentId, newStatus) {
    if (confirm(`Are you sure you want to change status to "${newStatus}"?`)) {
        // In a real app, this would be an AJAX call to a PHP script to update the DB
        // For now, we can simulate by redirecting with parameters (not ideal for UX)
        // window.location.href = `update_appointment_status.php?id=${appointmentId}&status=${newStatus}`;
        alert(`Simulating update for appointment ${appointmentId} to status ${newStatus}. AJAX call needed here.`);
        // Potentially reload or update UI part via JS after successful AJAX
    }
}
</script>

<?php
require_once 'includes/footer.php';
?>

