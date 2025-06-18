<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

if (!is_logged_in() || get_user_role() !== 'admin') {
    redirect('index.php');
}

$page_title = "Manage Users";

$users = [];
$error_message = '';
$success_message = '';

// Fetch all users (can add pagination later)
$sql_fetch_users = "SELECT id, nom, email, role, phone, created_at FROM users ORDER BY role, nom ASC";
$result_users = $conn->query($sql_fetch_users);
if ($result_users) {
    while ($row = $result_users->fetch_assoc()) {
        $users[] = $row;
    }
} else {
    $error_message = "Error fetching users: " . $conn->error;
}

// Handle user deletion (example)
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['user_id'])) {
    $user_id_to_delete = (int)$_GET['user_id'];
    // Basic check: Don't allow deleting the current admin user
    if ($user_id_to_delete === $_SESSION['user_id']) {
        $error_message = "You cannot delete your own account.";
    } else {
        // Add more checks if needed (e.g., check for related appointments/records)
        $stmt_delete = $conn->prepare("DELETE FROM users WHERE id = ?");
        if ($stmt_delete) {
            $stmt_delete->bind_param("i", $user_id_to_delete);
            if ($stmt_delete->execute()) {
                $success_message = "User deleted successfully.";
                // Refresh user list
                $users = []; // Clear old list
                $result_users = $conn->query($sql_fetch_users);
                if ($result_users) {
                    while ($row = $result_users->fetch_assoc()) {
                        $users[] = $row;
                    }
                }
            } else {
                $error_message = "Error deleting user: " . $stmt_delete->error;
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
    <p>View, add, edit, and remove user accounts (Patients, Doctors, Nurses).</p>
</div>

<?php if (!empty($error_message)): ?>
    <div class="error-message-login" style="margin-bottom: 20px; max-width: 90%; margin-left:auto; margin-right:auto;"><?php echo $error_message; ?></div>
<?php endif; ?>
<?php if (!empty($success_message)): ?>
    <div class="success-message-login" style="margin-bottom: 20px; max-width: 90%; margin-left:auto; margin-right:auto;"><?php echo $success_message; ?></div>
<?php endif; ?>

<div style="margin-bottom: 20px; text-align: right; max-width: 90%; margin-left:auto; margin-right:auto;">
    <a href="add_user.php" class="btn-login" style="padding: 10px 20px;">Add New User</a> <!-- Link to add user page -->
</div>

<?php if (empty($users) && empty($error_message)): ?>
    <div class="info-message" style="background-color: var(--secondary-color); color: var(--text-color); padding: 20px; border-radius: 8px; text-align: center; margin-top: 20px; max-width: 90%; margin-left:auto; margin-right:auto;">
        <p>No users found.</p>
    </div>
<?php elseif (!empty($users)): ?>
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
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                        <td><?php echo htmlspecialchars($user['nom']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><span class="status-<?php echo strtolower(htmlspecialchars($user['role'])); ?>"><?php echo ucfirst(htmlspecialchars($user['role'])); ?></span></td>
                        <td><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars(date("Y-m-d", strtotime($user['created_at']))); ?></td>
                        <td class="action-links">
                            <a href="edit_user.php?user_id=<?php echo $user['id']; ?>">Edit</a>
                            <?php if ($user['id'] !== $_SESSION['user_id']): // Don't show delete for self ?>
                            <a href="manage_users.php?action=delete&user_id=<?php echo $user['id']; ?>" onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.');" style="color: #dc3545;">Delete</a>
                            <?php endif; ?>
                            <!-- Add more actions like 'View Profile', 'Reset Password' etc. -->
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

