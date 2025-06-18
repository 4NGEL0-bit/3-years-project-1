<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

if (!is_logged_in() || get_user_role() !== 'admin') {
    redirect('index.php');
}

$page_title = "Add New Payment Record";

$error_message = '';
$success_message = '';

// Fetch appointments that might need payment (e.g., completed but no payment yet? Or allow for any?)
// For simplicity, let's fetch recent completed appointments
$appointments_needing_payment = [];
$sql_appointments = "SELECT a.id as appointment_id, a.appointment_date, u.nom as patient_name, u.id as patient_id
                     FROM appointments a
                     JOIN users u ON a.patient_id = u.id
                     WHERE a.status = 'completed' 
                     ORDER BY a.appointment_date DESC, u.nom ASC LIMIT 50"; // Limit for dropdown
$result_app = $conn->query($sql_appointments);
if ($result_app) {
    while ($row = $result_app->fetch_assoc()) {
        $appointments_needing_payment[] = $row;
    }
} else {
    $error_message = "Error fetching appointments: " . $conn->error;
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $appointment_id = filter_input(INPUT_POST, 'appointment_id', FILTER_VALIDATE_INT);
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
    $payment_date = sanitize_input($_POST['payment_date']);
    $payment_method = sanitize_input($_POST['payment_method']);
    $payment_status = sanitize_input($_POST['payment_status']);

    // Basic Validation
    if (empty($appointment_id) || empty($amount) || empty($payment_date) || empty($payment_method) || empty($payment_status)) {
        $error_message = "All fields are required.";
    } elseif ($amount <= 0) {
        $error_message = "Amount must be positive.";
    } elseif (!in_array($payment_status, ['pending', 'completed', 'failed', 'refunded'])) {
        $error_message = "Invalid payment status.";
    } else {
        // Get patient_id from appointment_id
        $stmt_get_patient = $conn->prepare("SELECT patient_id FROM appointments WHERE id = ?");
        $patient_id = null;
        if ($stmt_get_patient) {
            $stmt_get_patient->bind_param("i", $appointment_id);
            $stmt_get_patient->execute();
            $res_patient = $stmt_get_patient->get_result();
            if ($row_patient = $res_patient->fetch_assoc()) {
                $patient_id = $row_patient['patient_id'];
            }
            $stmt_get_patient->close();
        }

        if (!$patient_id) {
            $error_message = "Could not find patient for the selected appointment.";
        } else {
            // Insert payment record
            $stmt_insert = $conn->prepare("INSERT INTO payments (appointment_id, patient_id, amount, payment_date, payment_method, status) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt_insert) {
                $stmt_insert->bind_param("iidsss", $appointment_id, $patient_id, $amount, $payment_date, $payment_method, $payment_status);
                if ($stmt_insert->execute()) {
                    $success_message = "Payment record added successfully! Redirecting...";
                    echo "<meta http-equiv='refresh' content='2;url=payments.php'>";
                } else {
                    $error_message = "Error adding payment record: " . $stmt_insert->error;
                }
                $stmt_insert->close();
            } else {
                $error_message = "Database error preparing insert: " . $conn->error;
            }
        }
    }
}

?>
<script>
    document.title = "<?php echo $page_title; ?> - ClinicSys";
</script>

<div class="dashboard-header">
    <h1><?php echo $page_title; ?></h1>
    <p>Manually record a payment received for an appointment.</p>
</div>

<?php if (!empty($error_message)): ?>
    <div class="error-message-login" style="margin-bottom: 20px; max-width: 800px; margin-left:auto; margin-right:auto;"><?php echo $error_message; ?></div>
<?php endif; ?>
<?php if (!empty($success_message)): ?>
    <div class="success-message-login" style="margin-bottom: 20px; max-width: 800px; margin-left:auto; margin-right:auto;"><?php echo $success_message; ?></div>
<?php endif; ?>

<?php if (empty($success_message)): // Hide form after success ?>
<div class="form-container" style="max-width: 800px; margin: 20px auto;">
    <form action="add_payment.php" method="POST" class="dashboard-form animated-form">

        <div class="form-group">
            <label for="appointment_id">Select Appointment</label>
            <select id="appointment_id" name="appointment_id" required>
                <option value="">-- Select Appointment --</option>
                <?php foreach ($appointments_needing_payment as $app): ?>
                    <option value="<?php echo $app['appointment_id']; ?>"
                            <?php echo (isset($_POST['appointment_id']) && $_POST['appointment_id'] == $app['appointment_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($app['appointment_date']) . " - " . htmlspecialchars($app['patient_name']) . " (ID: " . $app['appointment_id'] . ")"; ?>
                    </option>
                <?php endforeach; ?>
                 <?php if (empty($appointments_needing_payment) && empty($error_message)) echo "<option value='' disabled>No recent completed appointments found</option>"; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="amount">Amount ($)</label>
            <input type="number" step="0.01" id="amount" name="amount" value="<?php echo isset($_POST['amount']) ? htmlspecialchars($_POST['amount']) : ''; ?>" required placeholder="e.g., 150.00">
        </div>

        <div class="form-group">
            <label for="payment_date">Payment Date</label>
            <input type="date" id="payment_date" name="payment_date" value="<?php echo isset($_POST['payment_date']) ? htmlspecialchars($_POST['payment_date']) : date('Y-m-d'); ?>" required>
        </div>

        <div class="form-group">
            <label for="payment_method">Payment Method</label>
            <select id="payment_method" name="payment_method" required>
                <option value="">-- Select Method --</option>
                <option value="Cash" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] === 'Cash') ? 'selected' : ''; ?>>Cash</option>
                <option value="Credit Card" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] === 'Credit Card') ? 'selected' : ''; ?>>Credit Card</option>
                <option value="Bank Transfer" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] === 'Bank Transfer') ? 'selected' : ''; ?>>Bank Transfer</option>
                <option value="Insurance" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] === 'Insurance') ? 'selected' : ''; ?>>Insurance</option>
                <option value="Other" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] === 'Other') ? 'selected' : ''; ?>>Other</option>
            </select>
        </div>

        <div class="form-group">
            <label for="payment_status">Payment Status</label>
            <select id="payment_status" name="payment_status" required>
                <option value="pending" <?php echo (isset($_POST['payment_status']) && $_POST['payment_status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                <option value="completed" <?php echo (isset($_POST['payment_status']) && $_POST['payment_status'] === 'completed') ? 'selected' : ''; ?>>Completed</option>
                <option value="failed" <?php echo (isset($_POST['payment_status']) && $_POST['payment_status'] === 'failed') ? 'selected' : ''; ?>>Failed</option>
                <option value="refunded" <?php echo (isset($_POST['payment_status']) && $_POST['payment_status'] === 'refunded') ? 'selected' : ''; ?>>Refunded</option>
            </select>
        </div>

        <button type="submit" class="btn-login">Add Payment</button>
    </form>
</div>
<?php endif; ?>

<div style="text-align: center; margin-top: 20px;">
    <a href="payments.php" class="btn-secondary">Back to Payment List</a>
</div>

<?php
require_once 'includes/footer.php';
?>

