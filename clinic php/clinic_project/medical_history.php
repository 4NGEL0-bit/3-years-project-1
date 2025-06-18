<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

if (!is_logged_in() || get_user_role() !== 'patient') {
    redirect('index.php');
}

$patient_id = $_SESSION['user_id'];
$page_title = "Medical History";

// TODO: Implement logic to fetch and display patient's full medical history
// This would involve querying appointments, medical_notes, payments, etc.

?>
<script>
    document.title = "<?php echo $page_title; ?> - ClinicSys";
</script>

<div class="dashboard-header">
    <h1><?php echo $page_title; ?></h1>
    <p>Review your past appointments, diagnoses, and treatment plans.</p>
</div>

<div class="info-message" style="background-color: var(--secondary-color); color: var(--text-color); padding: 20px; border-radius: 8px; text-align: center; margin-top: 20px; max-width: 90%; margin-left:auto; margin-right:auto;">
    <p>Medical history display functionality is under development.</p>
    <!-- Detailed history will be displayed here -->
</div>

<div style="text-align: center; margin-top: 30px;">
    <a href="patient_dashboard.php" class="btn-secondary">Back to Dashboard</a>
</div>

<?php
require_once 'includes/footer.php';
?>

