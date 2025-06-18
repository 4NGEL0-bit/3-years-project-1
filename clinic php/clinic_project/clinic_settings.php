<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

if (!is_logged_in() || get_user_role() !== 'admin') {
    redirect('index.php');
}

$page_title = "Clinic Settings";

$settings = [];
$error_message = '';
$success_message = '';

// Fetch current settings (assuming a simple key-value table 'clinic_settings')
// Let's create a dummy structure for now if the table doesn't exist
$sql_fetch_settings = "SELECT setting_key, setting_value FROM clinic_settings";
$result_settings = $conn->query($sql_fetch_settings);
if ($result_settings) {
    while ($row = $result_settings->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} else {
    // Handle case where table might not exist yet - provide defaults
    // $error_message = "Error fetching settings: " . $conn->error;
    $settings = [
        'clinic_name' => 'ClinicSys Default Name',
        'clinic_address' => '123 Health St, Medville',
        'clinic_phone' => '555-123-4567',
        'working_hours_start' => '09:00',
        'working_hours_end' => '18:00',
        'appointment_fee' => '100.00'
    ];
    $error_message = "Note: Settings table might not exist. Displaying default values.";
}

// Handle settings update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $clinic_name = sanitize_input($_POST['clinic_name']);
    $clinic_address = sanitize_input($_POST['clinic_address']);
    $clinic_phone = sanitize_input($_POST['clinic_phone']);
    $working_hours_start = sanitize_input($_POST['working_hours_start']);
    $working_hours_end = sanitize_input($_POST['working_hours_end']);
    $appointment_fee = sanitize_input($_POST['appointment_fee']);

    // Basic validation
    if (empty($clinic_name) || empty($working_hours_start) || empty($working_hours_end)) {
        $error_message = "Clinic Name and Working Hours are required.";
    } else {
        // Prepare to update/insert settings
        // This assumes a simple key-value store. A more robust approach might be needed.
        $settings_to_update = [
            'clinic_name' => $clinic_name,
            'clinic_address' => $clinic_address,
            'clinic_phone' => $clinic_phone,
            'working_hours_start' => $working_hours_start,
            'working_hours_end' => $working_hours_end,
            'appointment_fee' => $appointment_fee
        ];

        $all_updates_successful = true;
        foreach ($settings_to_update as $key => $value) {
            $stmt_upsert = $conn->prepare("INSERT INTO clinic_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            if ($stmt_upsert) {
                $stmt_upsert->bind_param("sss", $key, $value, $value);
                if (!$stmt_upsert->execute()) {
                    $error_message .= "Error updating setting '$key': " . $stmt_upsert->error . "<br>";
                    $all_updates_successful = false;
                }
                $stmt_upsert->close();
            } else {
                $error_message .= "Error preparing update for setting '$key': " . $conn->error . "<br>";
                $all_updates_successful = false;
            }
        }

        if ($all_updates_successful) {
            $success_message = "Clinic settings updated successfully!";
            // Re-fetch settings to display updated values
            $settings = $settings_to_update; // Update local array immediately
        } else {
             $error_message = "Note: Settings table might need to be created first. " . $error_message;
        }
    }
}

?>
<script>
    document.title = "<?php echo $page_title; ?> - ClinicSys";
</script>

<div class="dashboard-header">
    <h1><?php echo $page_title; ?></h1>
    <p>Configure general clinic information, working hours, and fees.</p>
</div>

<?php if (!empty($error_message)): ?>
    <div class="error-message-login" style="margin-bottom: 20px; max-width: 800px; margin-left:auto; margin-right:auto;"><?php echo $error_message; ?></div>
<?php endif; ?>
<?php if (!empty($success_message)): ?>
    <div class="success-message-login" style="margin-bottom: 20px; max-width: 800px; margin-left:auto; margin-right:auto;"><?php echo $success_message; ?></div>
<?php endif; ?>

<div class="form-container" style="max-width: 800px; margin: 20px auto;">
    <form action="clinic_settings.php" method="POST" class="dashboard-form animated-form">
        <div class="form-group">
            <label for="clinic_name">Clinic Name</label>
            <input type="text" id="clinic_name" name="clinic_name" value="<?php echo htmlspecialchars($settings['clinic_name'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="clinic_address">Clinic Address</label>
            <textarea id="clinic_address" name="clinic_address" rows="3"><?php echo htmlspecialchars($settings['clinic_address'] ?? ''); ?></textarea>
        </div>

        <div class="form-group">
            <label for="clinic_phone">Clinic Phone</label>
            <input type="tel" id="clinic_phone" name="clinic_phone" value="<?php echo htmlspecialchars($settings['clinic_phone'] ?? ''); ?>">
        </div>

        <hr style="border-color: var(--primary-color-dark); margin: 30px 0;">

        <div class="form-group">
            <label for="working_hours_start">Working Hours Start</label>
            <input type="time" id="working_hours_start" name="working_hours_start" value="<?php echo htmlspecialchars($settings['working_hours_start'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="working_hours_end">Working Hours End</label>
            <input type="time" id="working_hours_end" name="working_hours_end" value="<?php echo htmlspecialchars($settings['working_hours_end'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="appointment_fee">Default Appointment Fee ($)</label>
            <input type="number" step="0.01" id="appointment_fee" name="appointment_fee" value="<?php echo htmlspecialchars($settings['appointment_fee'] ?? ''); ?>" placeholder="e.g., 150.00">
        </div>

        <button type="submit" class="btn-login">Save Settings</button>
    </form>
</div>

<div style="text-align: center; margin-top: 30px;">
    <a href="admin_dashboard.php" class="btn-secondary">Back to Dashboard</a>
</div>

<?php
require_once 'includes/footer.php';
?>

