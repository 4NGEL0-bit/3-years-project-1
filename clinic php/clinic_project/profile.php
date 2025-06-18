<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

if (!is_logged_in()) {
    redirect('index.php');
}

$user_id = $_SESSION['user_id'];
$user_role = get_user_role();
$page_title = "My Profile";

$user_details = [];
$error_message = '';
$success_message = '';

// Fetch user details
$stmt_fetch = $conn->prepare("SELECT nom, email, phone, address, gender, date_of_birth FROM users WHERE id = ?");
if ($stmt_fetch) {
    $stmt_fetch->bind_param("i", $user_id);
    $stmt_fetch->execute();
    $result = $stmt_fetch->get_result();
    if ($result->num_rows === 1) {
        $user_details = $result->fetch_assoc();
    } else {
        $error_message = "Could not retrieve user details.";
    }
    $stmt_fetch->close();
} else {
    $error_message = "Database error preparing to fetch details.";
}

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($error_message)) {
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $address = sanitize_input($_POST['address']);
    $gender = sanitize_input($_POST['gender']);
    $dob = sanitize_input($_POST['dob']);
    $password = $_POST['password']; // Get password fields
    $confirm_password = $_POST['confirm_password'];

    // Basic validation
    if (empty($name) || empty($email)) {
        $error_message = "Name and Email are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } elseif ($email !== $user_details['email']) {
        // Check if new email already exists for another user
        $stmt_check_email = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt_check_email->bind_param("si", $email, $user_id);
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
        $sql_update = "UPDATE users SET nom = ?, email = ?, phone = ?, address = ?, gender = ?, date_of_birth = ?" . $password_update_sql . " WHERE id = ?";
        $types = "ssssssi" . $password_types;
        $params = [$name, $email, $phone, $address, $gender, $dob, $user_id];
        $all_params = array_merge($params, $password_params);

        $stmt_update = $conn->prepare($sql_update);
        if ($stmt_update) {
            $stmt_update->bind_param($types, ...$all_params);
            if ($stmt_update->execute()) {
                $success_message = "Profile updated successfully!";
                // Update session name if changed
                if ($_SESSION['user_name'] !== $name) {
                    $_SESSION['user_name'] = $name;
                }
                // Re-fetch details to display updated info
                $stmt_fetch_again = $conn->prepare("SELECT nom, email, phone, address, gender, date_of_birth FROM users WHERE id = ?");
                $stmt_fetch_again->bind_param("i", $user_id);
                $stmt_fetch_again->execute();
                $user_details = $stmt_fetch_again->get_result()->fetch_assoc();
                $stmt_fetch_again->close();
                // Refresh header potentially?
                 echo "<meta http-equiv='refresh' content='2;url=profile.php'>"; // Refresh page to show changes

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
    <h1><?php echo $page_title; ?></h1>
    <p>View and update your personal information and account settings.</p>
</div>

<?php if (!empty($error_message)): ?>
    <div class="error-message-login" style="margin-bottom: 20px; max-width: 800px; margin-left:auto; margin-right:auto;"><?php echo $error_message; ?></div>
<?php endif; ?>
<?php if (!empty($success_message)): ?>
    <div class="success-message-login" style="margin-bottom: 20px; max-width: 800px; margin-left:auto; margin-right:auto;"><?php echo $success_message; ?></div>
<?php endif; ?>

<?php if (!empty($user_details)): ?>
<div class="form-container" style="max-width: 800px; margin: 20px auto;">
    <form action="profile.php" method="POST" class="dashboard-form animated-form">
        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user_details['nom']); ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_details['email']); ?>" required>
        </div>

        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user_details['phone'] ?? ''); ?>" placeholder="e.g., 555-1234">
        </div>

        <div class="form-group">
            <label for="address">Address</label>
            <textarea id="address" name="address" rows="3" placeholder="Enter your full address"><?php echo htmlspecialchars($user_details['address'] ?? ''); ?></textarea>
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

        <button type="submit" class="btn-login">Update Profile</button>
    </form>
</div>
<?php else: ?>
    <div class="info-message" style="background-color: var(--secondary-color); color: var(--text-color); padding: 20px; border-radius: 8px; text-align: center; margin-top: 20px;">
        <p>Could not load profile details.</p>
    </div>
<?php endif; ?>

<div style="text-align: center; margin-top: 20px;">
    <a href="dashboard.php" class="btn-secondary">Back to Dashboard</a>
</div>

<?php
require_once 'includes/footer.php';
?>

