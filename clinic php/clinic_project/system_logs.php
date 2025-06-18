<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

if (!is_logged_in() || get_user_role() !== 'admin') {
    redirect('index.php');
}

$page_title = "System Logs";

// TODO: Implement log fetching and display logic
// This might involve reading from a log file or a database table

$logs = []; // Placeholder for log entries

?>
<script>
    document.title = "<?php echo $page_title; ?> - ClinicSys";
</script>

<div class="dashboard-header">
    <h1><?php echo $page_title; ?></h1>
    <p>Monitor system activity, user actions, and potential errors.</p>
</div>

<div class="info-message" style="background-color: var(--secondary-color); color: var(--text-color); padding: 20px; border-radius: 8px; text-align: center; margin-top: 20px; max-width: 90%; margin-left:auto; margin-right:auto;">
    <p>System logging functionality is under development.</p>
    <!-- Log entries will be displayed here, possibly with filtering options -->
</div>

<div style="text-align: center; margin-top: 30px;">
    <a href="admin_dashboard.php" class="btn-secondary">Back to Dashboard</a>
</div>

<?php
require_once 'includes/footer.php';
?>

