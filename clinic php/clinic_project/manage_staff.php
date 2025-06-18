<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

if (!is_logged_in() || get_user_role() !== 'admin') {
    redirect('index.php');
}

$page_title = "Manage Staff";

$staff_members = [];
$error_message = '';
$success_message = '';

// Fetch staff users (doctors and nurses)
$sql_fetch_staff = "SELECT id, nom, email, role, phone, created_at FROM users WHERE role IN ('doctor', 'nurse') ORDER BY role, nom ASC";
$result_staff = $conn->query($sql_fetch_staff);
if ($result_staff) {
    while ($row = $result_staff->fetch_assoc()) {
        $staff_members[] = $row;
    }
} else {
    $error_message = "Error fetching staff members: " . $conn->error;
}

// Handle staff deletion (similar to manage_users.php, but maybe add checks related to appointments?)
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['user_id'])) {
    $user_id_to_delete = (int)$_GET['user_id'];
    // Basic check: Don't allow deleting the current admin user (though they shouldn't be listed here)
    if ($user_id_to_delete === $_SESSION['user_id']) {
        $error_message = "Action not allowed.";
    } else {
        // TODO: Add checks - e.g., reassign patients/appointments before deleting a doctor?
        $stmt_delete = $conn->prepare("DELETE FROM users WHERE id = ? AND role IN ('doctor', 'nurse')");
        if ($stmt_delete) {
            $stmt_delete->bind_param("i", $user_id_to_delete);
            if ($stmt_delete->execute()) {
                $success_message = "Staff member deleted successfully.";
                // Refresh staff list
                $staff_members = []; // Clear old list
                $result_staff = $conn->query($sql_fetch_staff);
                if ($result_staff) {
                    while ($row = $result_staff->fetch_assoc()) {
                        $staff_members[] = $row;
                    }
                }
            } else {
                $error_message = "Error deleting staff member: " . $stmt_delete->error;
            }
            $stmt_delete->close();
        } else {
            $error_message = "Error preparing delete statement: " . $conn->error;
        }
    }
}

?>
<script>
    document.title = "<?php echo $page_title; ?> - ClinicSys";
</script>

<div class="dashboard-header">
    <h1><?php echo $page_title; ?></h1>
    <p>View, add, edit, and remove Doctor and Nurse accounts.</p>
</div>

<?php if (!empty($error_message)): ?>
    <div class="error-message-login" style="margin-bottom: 20px; max-width: 90%; margin-left:auto; margin-right:auto;"><?php echo $error_message; ?></div>
<?php endif; ?>
<?php if (!empty($success_message)): ?>
    <div class="success-message-login" style="margin-bottom: 20px; max-width: 90%; margin-left:auto; margin-right:auto;"><?php echo $success_message; ?></div>
<?php endif; ?>

<div style="margin-bottom: 20px; text-align: right; max-width: 90%; margin-left:auto; margin-right:auto;">
    <a href="add_user.php?role_filter=staff" class="btn-login" style="padding: 10px 20px;">Add New Staff Member</a> <!-- Link to add user page, maybe pre-select role -->
</div>

<?php if (empty($staff_members) && empty($error_message)): ?>
    <div class="info-message" style="background-color: var(--secondary-color); color: var(--text-color); padding: 20px; border-radius: 8px; text-align: center; margin-top: 20px; max-width: 90%; margin-left:auto; margin-right:auto;">
        <p>No staff members found.</p>
    </div>
<?php elseif (!empty($staff_members)): ?>
    <div style="max-width: 95%; margin: 0 auto;">
        <table class="content-table animated-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Phone</th>
                    <th>Registered On</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($staff_members as $staff): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($staff['id']); ?></td>
                        <td><?php echo htmlspecialchars($staff['nom']); ?></td>
                        <td><?php echo htmlspecialchars($staff['email']); ?></td>
                        <td><span class="status-<?php echo strtolower(htmlspecialchars($staff['role'])); ?>"><?php echo ucfirst(htmlspecialchars($staff['role'])); ?></span></td>
                        <td><?php echo htmlspecialchars($staff['phone'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars(date("Y-m-d", strtotime($staff['created_at']))); ?></td>
                        <td class="action-links">
                            <a href="edit_user.php?user_id=<?php echo $staff['id']; ?>">Edit</a>
                            <a href="manage_staff.php?action=delete&user_id=<?php echo $staff['id']; ?>" onclick="return confirm('Are you sure you want to delete this staff member? Consider reassigning their tasks/patients first.');" style="color: #dc3545;">Delete</a>
                            <!-- Add more actions like 'View Schedule' etc. -->
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<div style="text-align: center; margin-top: 30px;">
    <a href="admin_dashboard.php" class="btn-secondary">Back to Dashboard</a>
</div>

<?php
require_once 'includes/footer.php';
?>

