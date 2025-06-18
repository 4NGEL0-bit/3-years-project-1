<?php
require_once 'includes/db.php'; // For database operations
require_once 'includes/header.php'; // Includes session_start, functions.php, and HTML head

if (!is_logged_in() || get_user_role() !== 'doctor') {
    redirect('index.php'); // Redirect if not logged in or not a doctor
}

$doctor_id = $_SESSION['user_id'];
$page_title = "Add Diagnosis";

$appointment_id = isset($_GET['appointment_id']) ? (int)$_GET['appointment_id'] : 0;
$patient_id_from_url = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : 0; // Renamed to avoid conflict

$error_message = '';
$success_message = '';
$patient_name = 'N/A';
$appointment_details_display = 'N/A';

// Verify appointment and patient details
if ($appointment_id > 0) {
    $stmt_check = $conn->prepare("SELECT a.appointment_date, a.appointment_time, p.nom as patient_name, p.id as actual_patient_id
                                 FROM appointments a 
                                 JOIN users p ON a.patient_id = p.id 
                                 WHERE a.id = ? AND a.doctor_id = ?");
    if ($stmt_check) {
        $stmt_check->bind_param("ii", $appointment_id, $doctor_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        if ($appointment_data = $result_check->fetch_assoc()) {
            $patient_name = htmlspecialchars($appointment_data['patient_name']);
            $appointment_details_display = htmlspecialchars($appointment_data['appointment_date']) . " at " . htmlspecialchars(date("g:i A", strtotime($appointment_data['appointment_time'])));
            $actual_patient_id = $appointment_data['actual_patient_id']; // Use this for saving
        } else {
            $error_message = "Invalid appointment details or not authorized.";
        }
        $stmt_check->close();
    } else {
        $error_message = "Database error preparing to check appointment.";
    }
} else {
    $error_message = "Appointment ID not provided.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($error_message)) {
    $diagnosis_text = sanitize_input($_POST['diagnosis_text']);
    $recommendations = sanitize_input($_POST['recommendations']);
    $next_visit_date = !empty($_POST['next_visit_date']) ? sanitize_input($_POST['next_visit_date']) : NULL;
    $referral_details = sanitize_input($_POST['referral_details']);

    if (empty($diagnosis_text)) {
        $error_message = "Diagnosis text cannot be empty.";
    } else {
        // Check if a note already exists, if so, update, otherwise insert
        $stmt_find_note = $conn->prepare("SELECT id FROM medical_notes WHERE appointment_id = ?");
        $stmt_find_note->bind_param("i", $appointment_id);
        $stmt_find_note->execute();
        $note_result = $stmt_find_note->get_result();
        $existing_note = $note_result->fetch_assoc();
        $stmt_find_note->close();

        if ($existing_note) {
            $stmt_update_note = $conn->prepare("UPDATE medical_notes SET diagnosis_text = ?, recommendations = ?, next_visit_date = ?, referral_details = ?, doctor_id = ? WHERE appointment_id = ?");
            if ($stmt_update_note) {
                $stmt_update_note->bind_param("ssssii", $diagnosis_text, $recommendations, $next_visit_date, $referral_details, $doctor_id, $appointment_id);
                if ($stmt_update_note->execute()) {
                    $success_message = "Diagnosis updated successfully!";
                } else {
                    $error_message = "Error updating diagnosis: " . $stmt_update_note->error;
                }
                $stmt_update_note->close();
            }
        } else {
            $stmt_insert_note = $conn->prepare("INSERT INTO medical_notes (appointment_id, patient_id, doctor_id, diagnosis_text, recommendations, next_visit_date, referral_details) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if ($stmt_insert_note) {
                $stmt_insert_note->bind_param("iiissss", $appointment_id, $actual_patient_id, $doctor_id, $diagnosis_text, $recommendations, $next_visit_date, $referral_details);
                if ($stmt_insert_note->execute()) {
                    $success_message = "Diagnosis saved successfully!";
                } else {
                    $error_message = "Error saving diagnosis: " . $stmt_insert_note->error;
                }
                $stmt_insert_note->close();
            }
        }
         if (empty($error_message) && !empty($success_message)) {
             echo "<meta http-equiv='refresh' content='2;url=doctor_dashboard.php'>";
         }
    }
}

// Fetch existing diagnosis if any, to pre-fill form
$existing_diagnosis_data = ['diagnosis_text' => '', 'recommendations' => '', 'next_visit_date' => '', 'referral_details' => ''];
if ($appointment_id > 0 && empty($error_message)) {
    $stmt_fetch_existing = $conn->prepare("SELECT diagnosis_text, recommendations, next_visit_date, referral_details FROM medical_notes WHERE appointment_id = ? AND doctor_id = ?");
    if ($stmt_fetch_existing) {
        $stmt_fetch_existing->bind_param("ii", $appointment_id, $doctor_id);
        $stmt_fetch_existing->execute();
        $result_existing = $stmt_fetch_existing->get_result();
        if ($data = $result_existing->fetch_assoc()) {
            $existing_diagnosis_data = $data;
        }
        $stmt_fetch_existing->close();
    }
}

?>
<script>
    document.title = "<?php echo $page_title; ?> - ClinicSys";
</script>

<div class="dashboard-header">
    <h1>Add/Edit Medical Diagnosis</h1>
    <p>Record your findings and recommendations for the patient.</p>
</div>

<?php if (!empty($error_message)): ?>
    <div class="error-message-login" style="margin-bottom: 20px; max-width: 800px; margin-left:auto; margin-right:auto;"><?php echo $error_message; ?></div>
<?php endif; ?>
<?php if (!empty($success_message)): ?>
    <div class="success-message-login" style="margin-bottom: 20px; max-width: 800px; margin-left:auto; margin-right:auto;"><?php echo $success_message; ?></div>
<?php endif; ?>

<?php if (empty($error_message) || (!empty($error_message) && $error_message !== "Invalid appointment details or not authorized." && $error_message !== "Appointment ID or Patient ID not provided.")): // Show form if no critical error ?>
<div class="form-container" style="max-width: 800px; margin: 20px auto;">
    <div class="patient-appointment-info" style="background-color: var(--secondary-color); padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <p><strong>Patient:</strong> <?php echo $patient_name; ?></p>
        <p><strong>Appointment:</strong> <?php echo $appointment_details_display; ?></p>
    </div>

    <form action="add_diagnosis.php?appointment_id=<?php echo $appointment_id; ?>" method="POST" class="dashboard-form animated-form">
        <div class="form-group">
            <label for="diagnosis_text">Diagnosis</label>
            <textarea id="diagnosis_text" name="diagnosis_text" rows="6" placeholder="Enter detailed diagnosis..." required><?php echo htmlspecialchars($existing_diagnosis_data['diagnosis_text']); ?></textarea>
        </div>

        <div class="form-group">
            <label for="recommendations">Recommendations & Prescriptions</label>
            <textarea id="recommendations" name="recommendations" rows="4" placeholder="Enter recommendations, prescriptions, lifestyle advice..."><?php echo htmlspecialchars($existing_diagnosis_data['recommendations']); ?></textarea>
        </div>

        <div class="form-group">
            <label for="next_visit_date">Next Visit Date (Optional)</label>
            <input type="date" id="next_visit_date" name="next_visit_date" value="<?php echo htmlspecialchars($existing_diagnosis_data['next_visit_date']); ?>">
        </div>

        <div class="form-group">
            <label for="referral_details">Referral Details (Optional)</label>
            <input type="text" id="referral_details" name="referral_details" placeholder="e.g., Referred to Dr. Smith (Cardiologist)" value="<?php echo htmlspecialchars($existing_diagnosis_data['referral_details']); ?>">
        </div>

        <button type="submit" class="btn-login">Save Diagnosis</button>
    </form>
</div>
<?php endif; ?>

<div style="text-align: center; margin-top: 20px;">
    <a href="doctor_dashboard.php" class="btn-secondary">Back to Dashboard</a>
</div>

<?php
require_once 'includes/footer.php';
?>

