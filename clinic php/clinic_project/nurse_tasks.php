<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

if (!is_logged_in() || get_user_role() !== 'nurse') {
    redirect('index.php');
}

$page_title = "My Tasks & Notifications";

// TODO: Implement task fetching (assigned by doctors/admin?)
// TODO: Implement notification system (e.g., low inventory, new tasks)

?>
<script>
    document.title = "<?php echo $page_title; ?> - ClinicSys";
</script>

<div class="dashboard-header">
    <h1><?php echo $page_title; ?></h1>
    <p>View assigned tasks, reminders, and system notifications.</p>
</div>

<div class="info-message" style="background-color: var(--secondary-color); color: var(--text-color); padding: 20px; border-radius: 8px; text-align: center; margin-top: 20px; max-width: 90%; margin-left:auto; margin-right:auto;">
    <p>Task and notification functionality is under development.</p>
    <!-- Task list and notification area will go here -->
</div>

<div style="text-align: center; margin-top: 30px;">
    <a href="nurse_dashboard.php" class="btn-secondary">Back to Dashboard</a>
</div>

<?php
require_once 'includes/footer.php';
?>

