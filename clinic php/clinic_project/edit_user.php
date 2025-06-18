<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

if (!is_logged_in() || get_user_role() !== 'admin') {
    redirect('index.php');
}

$page_title = "Edit User";
$user_id_to_edit = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

$user_details = [];
$error_message = '';
$success_message = '';

// Fetch user details for editing
if ($user_id_to_edit > 0) {
    $stmt_fetch = $conn->prepare("SELECT nom, email, role, phone, address, gender, date_of_birth FROM users WHERE id = ?");
    if ($stmt_fetch) {
        $stmt_fetch->bind_param("i", $user_id_to_edit);
        $stmt_fetch->execute();
        $result = $stmt_fetch->get_result();
        if ($result->num_rows === 1) {
            $user_details = $result->fetch_assoc();
        } else {
            $error_message = "User not found.";
        }
        $stmt_fetch->close();
    } else {
        $error_message = "Database error preparing to fetch user details.";
    }
} else {
    $error_message = "No user ID provided.";
    // redirect('manage_users.php'); // Or show error
}

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($error_message) && isset($_POST['user_id']) && (int)$_POST['user_id'] === $user_id_to_edit) {
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $role = sanitize_input($_POST['role']);
    $phone = sanitize_input($_POST['phone']);
    $address = sanitize_input($_POST['address']);
    $gender = sanitize_input($_POST['gender']);
    $dob = sanitize_input($_POST['dob']);
    $password = $_POST['password']; // Get password fields
    $confirm_password = $_POST['confirm_password'];

    // Basic validation
    if (empty($name) || empty($email) || empty($role)) {
        $error_message = "Name, Email, and Role are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } elseif (!in_array($role, ['patient', 'doctor', 'nurse', 'admin'])) {
        $error_message = "Invalid user role selected.";
    } elseif ($email !== $user_details['email']) {
        // Check if new email already exists for another user
        $stmt_check_email = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt_check_email->bind_param("si", $email, $user_id_to_edit);
        $stmt_check_email->execute();
        if ($stmt_check_email->get_result()->num_rows > 0) {
            $error_message = "This email address is already registered by another user.";
        }
        $stmt_check_email->close();
    }

    // Password update logic
    $password_update_sql = "";
    $password_params = [];
    $password_types = "";
    if (!empty($password)) {
        if ($password !== $confirm_password) {
            $error_message = "Passwords do not match.";
        } elseif (strlen($password) < 6) { // Example minimum length
            $error_message = "Password must be at least 6 characters long.";
        } else {
            // HASH THE PASSWORD - IMPORTANT!
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $password_update_sql = ", mot_de_passe = ?";
            $password_params[] = $hashed_password;
            $password_types .= "s";
        }
    }

    if (empty($error_message)) {
        $sql_update = "UPDATE users SET nom = ?, email = ?, role = ?, phone = ?, address = ?, gender = ?, date_of_birth = ?" . $password_update_sql . " WHERE id = ?";
        $types = "sssssssi" . $password_types;
        $params = [$name, $email, $role, $phone, $address, $gender, $dob, $user_id_to_edit];
        $all_params = array_merge($params, $password_params);

        $stmt_update = $conn->prepare($sql_update);
        if ($stmt_update) {
            $stmt_update->bind_param($types, ...$all_params);
            if ($stmt_update->execute()) {
                $success_message = "User profile updated successfully! Redirecting...";
                // Re-fetch details to display updated info
                $stmt_fetch_again = $conn->prepare("SELECT nom, email, role, phone, address, gender, date_of_birth FROM users WHERE id = ?");
                $stmt_fetch_again->bind_param("i", $user_id_to_edit);
                $stmt_fetch_again->execute();
                $user_details = $stmt_fetch_again->get_result()->fetch_assoc();
                $stmt_fetch_again->close();
                echo "<meta http-equiv='refresh' content='2;url=manage_users.php'>"; // Redirect back to list
            } else {
                $error_message = "Error updating profile: " . $stmt_update->error;
            }
            $stmt_update->close();
        } else {
            $error_message = "Database error preparing update: " . $conn->error;
        }
    }
}

?>
<script>
    document.title = "<?php echo $page_title; ?> - ClinicSys";
</script>

<div class="dashboard-header">
    <h1><?php echo $page_title; ?> (ID: <?php echo $user_id_to_edit; ?>)</h1>
    <p>Modify the details for the selected user account.</p>
</div>

<?php if (!empty($error_message)): ?>
    <div class="error-message-login" style="margin-bottom: 20px; max-width: 800px; margin-left:auto; margin-right:auto;"><?php echo $error_message; ?></div>
<?php endif; ?>
<?php if (!empty($success_message)): ?>
    <div class="success-message-login" style="margin-bottom: 20px; max-width: 800px; margin-left:auto; margin-right:auto;"><?php echo $success_message; ?></div>
<?php endif; ?>

<?php if (!empty($user_details) && empty($success_message)): // Show form if user found and not just successfully updated ?>
<div class="form-container" style="max-width: 800px; margin: 20px auto;">
    <form action="edit_user.php?user_id=<?php echo $user_id_to_edit; ?>" method="POST" class="dashboard-form animated-form">
        <input type="hidden" name="user_id" value="<?php echo $user_id_to_edit; ?>">
        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user_details['nom']); ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_details['email']); ?>" required>
        </div>

        <div class="form-group">
            <label for="role">User Role</label>
            <select id="role" name="role" required>
                <option value="">-- Select Role --</option>
                <option value="patient" <?php echo ($user_details['role'] === 'patient') ? 'selected' : ''; ?>>Patient</option>
                <option value="doctor" <?php echo ($user_details['role'] === 'doctor') ? 'selected' : ''; ?>>Doctor</option>
                <option value="nurse" <?php echo ($user_details['role'] === 'nurse') ? 'selected' : ''; ?>>Nurse</option>
                <option value="admin" <?php echo ($user_details['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
            </select>
        </div>

        <hr style="border-color: var(--primary-color-dark); margin: 30px 0;">
        <h4>Optional Information</h4>

        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user_details['phone'] ?? ''); ?>" placeholder="e.g., 555-1234">
        </div>

        <div class="form-group">
            <label for="address">Address</label>
            <textarea id="address" name="address" rows="3" placeholder="Enter full address"><?php echo htmlspecialchars($user_details['address'] ?? ''); ?></textarea>
        </div>

        <div class="form-group">
            <label for="gender">Gender</label>
            <select id="gender" name="gender">
                <option value="">-- Select Gender --</option>
                <option value="Male" <?php echo ($user_details['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                <option value="Female" <?php echo ($user_details['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                <option value="Other" <?php echo ($user_details['gender'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                <option value="Prefer not to say" <?php echo ($user_details['gender'] ?? '') === 'Prefer not to say' ? 'selected' : ''; ?>>Prefer not to say</option>
            </select>
        </div>

        <div class="form-group">
            <label for="dob">Date of Birth</label>
            <input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($user_details['date_of_birth'] ?? ''); ?>">
        </div>

        <hr style="border-color: var(--primary-color-dark); margin: 30px 0;">
        <h4>Update Password (Optional)</h4>

        <div class="form-group">
            <label for="password">New Password</label>
            <input type="password" id="password" name="password" placeholder="Leave blank to keep current password">
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm New Password</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password">
        </div>

        <button type="submit" class="btn-login">Update User</button>
    </form>
</div>
<?php elseif (empty($error_message)): // If success message is shown, don't show the form ?>
    <!-- Optionally show a success message or just rely on the redirect -->
<?php endif; ?>

<div style="text-align: center; margin-top: 20px;">
    <a href="manage_users.php" class="btn-secondary">Back to User List</a>
</div>

<?php
require_once 'includes/footer.php';
?>

