<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

if (!is_logged_in() || !in_array(get_user_role(), ['doctor', 'admin'])) { // Or just doctor?
    redirect('index.php');
}

$page_title = "Manage Referrals";

// TODO: Implement referral creation form
// TODO: Display list of referrals made by the doctor or all referrals for admin

?>
<script>
    document.title = "<?php echo $page_title; ?> - ClinicSys";
</script>

<div class="dashboard-header">
    <h1><?php echo $page_title; ?></h1>
    <p>Create and manage patient referrals to specialists or other clinics.</p>
</div>

<div class="info-message" style="background-color: var(--secondary-color); color: var(--text-color); padding: 20px; border-radius: 8px; text-align: center; margin-top: 20px; max-width: 90%; margin-left:auto; margin-right:auto;">
    <p>Referral management functionality is under development.</p>
    <!-- Referral list and creation form will be displayed here -->
</div>

<div style="text-align: center; margin-top: 30px;">
    <a href="dashboard.php" class="btn-secondary">Back to Dashboard</a>
</div>

<?php
require_once 'includes/footer.php';
?>

