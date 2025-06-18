<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

if (!is_logged_in() || get_user_role() !== 'nurse') {
    redirect('index.php');
}

$page_title = "Record Vitals & Check-in";

// TODO: Implement patient search for check-in
// TODO: Implement form to record vitals (height, weight, temp, bp)
// TODO: Update appointment status to 'checked-in' or 'ready_for_doctor'

?>
<script>
    document.title = "<?php echo $page_title; ?> - ClinicSys";
</script>

<div class="dashboard-header">
    <h1><?php echo $page_title; ?></h1>
    <p>Check in patients for their appointments and record their vital signs.</p>
</div>

<div class="info-message" style="background-color: var(--secondary-color); color: var(--text-color); padding: 20px; border-radius: 8px; text-align: center; margin-top: 20px; max-width: 90%; margin-left:auto; margin-right:auto;">
    <p>Patient check-in and vital recording functionality is under development.</p>
    <!-- Search, check-in button, and vitals form will go here -->
</div>

<div style="text-align: center; margin-top: 30px;">
    <a href="nurse_dashboard.php" class="btn-secondary">Back to Dashboard</a>
</div>

<?php
require_once 'includes/footer.php';
?>

