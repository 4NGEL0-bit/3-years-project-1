<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

if (!is_logged_in() || get_user_role() !== 'nurse') { // Or maybe admin too?
    redirect('index.php');
}

$page_title = "Manage Inventory";

// TODO: Implement inventory item listing (from a new 'inventory' table?)
// TODO: Implement adding/editing/deleting inventory items
// TODO: Implement low stock alerts

?>
<script>
    document.title = "<?php echo $page_title; ?> - ClinicSys";
</script>

<div class="dashboard-header">
    <h1><?php echo $page_title; ?></h1>
    <p>Track and manage medical equipment and supplies.</p>
</div>

<div class="info-message" style="background-color: var(--secondary-color); color: var(--text-color); padding: 20px; border-radius: 8px; text-align: center; margin-top: 20px; max-width: 90%; margin-left:auto; margin-right:auto;">
    <p>Inventory management functionality is under development.</p>
    <!-- Inventory list, add/edit forms will go here -->
</div>

<div style="text-align: center; margin-top: 30px;">
    <a href="nurse_dashboard.php" class="btn-secondary">Back to Dashboard</a>
</div>

<?php
require_once 'includes/footer.php';
?>

