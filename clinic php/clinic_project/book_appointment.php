<?php
require_once 'includes/db.php'; // For database operations
require_once 'includes/header.php'; // Includes session_start, functions.php, and HTML head

if (!is_logged_in() || get_user_role() !== 'patient') {
    redirect('index.php'); // Redirect if not logged in or not a patient
}

$patient_id = $_SESSION['user_id'];
$page_title = "Book Appointment";

$error_message = '';
$success_message = '';

// Fetch available doctors
$doctors = [];
$sql_doctors = "SELECT id, nom FROM users WHERE role = 'doctor' ORDER BY nom ASC";
$result_doctors = $conn->query($sql_doctors);
if ($result_doctors && $result_doctors->num_rows > 0) {
    while ($row = $result_doctors->fetch_assoc()) {
        $doctors[] = $row;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $doctor_id_selected = sanitize_input($_POST['doctor_id']);
    $appointment_date = sanitize_input($_POST['appointment_date']);
    $appointment_time = sanitize_input($_POST['appointment_time']);
    $reason_for_visit = sanitize_input($_POST['reason_for_visit']);

    if (empty($doctor_id_selected) || empty($appointment_date) || empty($appointment_time)) {
        $error_message = "Please select a doctor, date, and time.";
    } else {
        // TODO: Add more sophisticated validation for date/time (e.g., not in past, within clinic hours, slot availability)
        $current_datetime = new DateTime();
        $selected_datetime = new DateTime($appointment_date . ' ' . $appointment_time);

        if ($selected_datetime < $current_datetime) {
            $error_message = "Cannot book an appointment in the past.";
        } else {
            // Check if slot is free (basic check, can be more complex)
            $stmt_check_slot = $conn->prepare("SELECT id FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ? AND status != 'cancelled'");
            $stmt_check_slot->bind_param("iss", $doctor_id_selected, $appointment_date, $appointment_time);
            $stmt_check_slot->execute();
            $result_check_slot = $stmt_check_slot->get_result();

            if ($result_check_slot->num_rows > 0) {
                $error_message = "The selected time slot is already booked with this doctor. Please choose another time.";
            } else {
                $stmt_insert = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, reason_for_visit, status) VALUES (?, ?, ?, ?, ?, 'scheduled')");
                if ($stmt_insert) {
                    $stmt_insert->bind_param("iisss", $patient_id, $doctor_id_selected, $appointment_date, $appointment_time, $reason_for_visit);
                    if ($stmt_insert->execute()) {
                        $success_message = "Appointment booked successfully! You will be redirected shortly.";
                        // Optionally, redirect after a few seconds
                        echo "<meta http-equiv='refresh' content='3;url=appointments.php'>";
                    } else {
                        $error_message = "Error booking appointment: " . $stmt_insert->error;
                    }
                    $stmt_insert->close();
                } else {
                    $error_message = "Database error: Could not prepare statement.";
                }
            }
            $stmt_check_slot->close();
        }
    }
}

?>
<script>
    document.title = "<?php echo $page_title; ?> - ClinicSys";
</script>

<div class="dashboard-header">
    <h1>Schedule Your Appointment</h1>
    <p>Choose your preferred doctor, date, and time for your consultation.</p>
</div>

<?php if (!empty($error_message)): ?>
    <div class="error-message-login" style="margin-bottom: 20px; width: 100%; max-width: 600px; margin-left:auto; margin-right:auto;"><?php echo $error_message; ?></div>
<?php endif; ?>
<?php if (!empty($success_message)): ?>
    <div class="success-message-login" style="margin-bottom: 20px; width: 100%; max-width: 600px; margin-left:auto; margin-right:auto;"><?php echo $success_message; ?></div>
<?php endif; ?>

<?php if (empty($success_message)): // Only show form if not successfully booked ?>
<form action="book_appointment.php" method="POST" class="dashboard-form animated-form" style="max-width: 700px; margin: 20px auto;">
    <div class="form-group">
        <label for="doctor_id">Select Doctor</label>
        <select id="doctor_id" name="doctor_id" required>
            <option value="">-- Choose a Doctor --</option>
            <?php foreach ($doctors as $doctor): ?>
                <option value="<?php echo htmlspecialchars($doctor['id']); ?>">
                    <?php echo htmlspecialchars($doctor['nom']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="appointment_date">Select Date</label>
        <input type="date" id="appointment_date" name="appointment_date" required min="<?php echo date('Y-m-d'); ?>">
    </div>

    <div class="form-group">
        <label for="appointment_time">Select Time</label>
        <input type="time" id="appointment_time" name="appointment_time" required>
        <small>Clinic hours: 9:00 AM - 6:00 PM. Please select a time within these hours.</small>
        <!-- TODO: Implement dynamic time slot generation based on doctor's availability and clinic hours -->
    </div>

    <div class="form-group">
        <label for="reason_for_visit">Reason for Visit (Optional)</label>
        <textarea id="reason_for_visit" name="reason_for_visit" rows="4" placeholder="Briefly describe the reason for your appointment..."></textarea>
    </div>

    <button type="submit" class="btn-login">Book Appointment</button>
</form>
<?php endif; ?>

<div style="text-align: center; margin-top: 20px;">
    <a href="patient_dashboard.php" class="btn-secondary">Back to Dashboard</a>
</div>

<?php
require_once 'includes/footer.php';
?>

