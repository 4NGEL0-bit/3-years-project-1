<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

if (!is_logged_in() || !in_array(get_user_role(), ['doctor', 'admin'])) { // Allow admin too?
    redirect('index.php');
}

$page_title = "Patient Records";

// TODO: Implement patient search functionality
// TODO: Display list of patients or search results
// TODO: Link to individual patient history/details page

?>
<script>
    document.title = "<?php echo $page_title; ?> - ClinicSys";
</script>

<div class="dashboard-header">
    <h1><?php echo $page_title; ?></h1>
    <p>Search and access detailed patient medical histories.</p>
</div>

<div class="form-container" style="max-width: 800px; margin: 20px auto;">
    <form action="patient_records.php" method="GET" class="dashboard-form">
        <div class="form-group">
            <label for="search_patient">Search Patient (by Name or ID)</label>
            <input type="text" id="search_patient" name="search_query" placeholder="Enter patient name or ID...">
        </div>
        <button type="submit" class="btn-login">Search</button>
    </form>
</div>

<div class="info-message" style="background-color: var(--secondary-color); color: var(--text-color); padding: 20px; border-radius: 8px; text-align: center; margin-top: 20px; max-width: 90%; margin-left:auto; margin-right:auto;">
    <p>Patient records functionality is under development.</p>
    <!-- Search results will be displayed here -->
</div>

<div style="text-align: center; margin-top: 30px;">
    <a href="dashboard.php" class="btn-secondary">Back to Dashboard</a>
</div>

<?php
require_once 'includes/footer.php';
?>

