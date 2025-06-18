<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

if (!is_logged_in() || get_user_role() !== 'patient') {
    redirect('index.php');
}

$patient_id = $_SESSION['user_id'];
$page_title = "Download Visit Summary";

// TODO: Implement logic to generate a summary (e.g., PDF) of recent visits/diagnoses
// This would involve fetching data and using a PDF generation library (like FPDF or TCPDF)
// For now, it's just a placeholder page.

?>
<script>
    document.title = "<?php echo $page_title; ?> - ClinicSys";
</script>

<div class="dashboard-header">
    <h1><?php echo $page_title; ?></h1>
    <p>Generate a printable summary of your recent clinic visits.</p>
</div>

<div class="info-message" style="background-color: var(--secondary-color); color: var(--text-color); padding: 20px; border-radius: 8px; text-align: center; margin-top: 20px; max-width: 90%; margin-left:auto; margin-right:auto;">
    <p>Visit summary generation functionality is under development.</p>
    <!-- A button to trigger PDF generation will go here -->
</div>

<div style="text-align: center; margin-top: 30px;">
    <a href="patient_dashboard.php" class="btn-secondary">Back to Dashboard</a>
</div>

<?php
require_once 'includes/footer.php';
?>

