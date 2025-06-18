<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

if (!is_logged_in() || get_user_role() !== 'admin') {
    redirect('index.php');
}

$page_title = "Add New User";

$error_message = '';
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = sanitize_input($_POST['role']);
    $phone = sanitize_input($_POST['phone']);
    $address = sanitize_input($_POST['address']);
    $gender = sanitize_input($_POST['gender']);
    $dob = sanitize_input($_POST['dob']);

    // Basic Validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password) || empty($role)) {
        $error_message = "Name, Email, Password, and Role are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error_message = "Password must be at least 6 characters long.";
    } elseif (!in_array($role, ['patient', 'doctor', 'nurse', 'admin'])) {
        $error_message = "Invalid user role selected.";
    } else {
        // Check if email already exists
        $stmt_check_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt_check_email->bind_param("s", $email);
        $stmt_check_email->execute();
        if ($stmt_check_email->get_result()->num_rows > 0) {
            $error_message = "This email address is already registered.";
        }
        $stmt_check_email->close();
    }

    if (empty($error_message)) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt_insert = $conn->prepare("INSERT INTO users (nom, email, mot_de_passe, role, phone, address, gender, date_of_birth) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt_insert) {
            $stmt_insert->bind_param("ssssssss", $name, $email, $hashed_password, $role, $phone, $address, $gender, $dob);
            if ($stmt_insert->execute()) {
                $success_message = "User added successfully! Redirecting to user list...";
                echo "<meta http-equiv='refresh' content='2;url=manage_users.php'>";
            } else {
                $error_message = "Error adding user: " . $stmt_insert->error;
            }
            $stmt_insert->close();
        } else {
            $error_message = "Database error preparing insert: " . $conn->error;
        }
    }
}

?>
<script>
    document.title = "<?php echo $page_title; ?> - ClinicSys";
</script>

<div class="dashboard-header">
    <h1><?php echo $page_title; ?></h1>
    <p>Create a new account for a patient, doctor, or nurse.</p>
</div>

<?php if (!empty($error_message)): ?>
    <div class="error-message-login" style="margin-bottom: 20px; max-width: 800px; margin-left:auto; margin-right:auto;"><?php echo $error_message; ?></div>
<?php endif; ?>
<?php if (!empty($success_message)): ?>
    <div class="success-message-login" style="margin-bottom: 20px; max-width: 800px; margin-left:auto; margin-right:auto;"><?php echo $success_message; ?></div>
<?php endif; ?>

<?php if (empty($success_message)): // Hide form after success ?>
<div class="form-container" style="max-width: 800px; margin: 20px auto;">
    <form action="add_user.php" method="POST" class="dashboard-form animated-form">
        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>

        <div class="form-group">
            <label for="role">User Role</label>
            <select id="role" name="role" required>
                <option value="">-- Select Role --</option>
                <option value="patient" <?php echo (isset($_POST['role']) && $_POST['role'] === 'patient') ? 'selected' : ''; ?>>Patient</option>
                <option value="doctor" <?php echo (isset($_POST['role']) && $_POST['role'] === 'doctor') ? 'selected' : ''; ?>>Doctor</option>
                <option value="nurse" <?php echo (isset($_POST['role']) && $_POST['role'] === 'nurse') ? 'selected' : ''; ?>>Nurse</option>
                <option value="admin" <?php echo (isset($_POST['role']) && $_POST['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
            </select>
        </div>

        <hr style="border-color: var(--primary-color-dark); margin: 30px 0;">
        <h4>Optional Information</h4>

        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" placeholder="e.g., 555-1234">
        </div>

        <div class="form-group">
            <label for="address">Address</label>
            <textarea id="address" name="address" rows="3" placeholder="Enter full address"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
        </div>

        <div class="form-group">
            <label for="gender">Gender</label>
            <select id="gender" name="gender">
                <option value="">-- Select Gender --</option>
                <option value="Male" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                <option value="Female" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                <option value="Other" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                <option value="Prefer not to say" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'Prefer not to say') ? 'selected' : ''; ?>>Prefer not to say</option>
            </select>
        </div>

        <div class="form-group">
            <label for="dob">Date of Birth</label>
            <input type="date" id="dob" name="dob" value="<?php echo isset($_POST['dob']) ? htmlspecialchars($_POST['dob']) : ''; ?>">
        </div>

        <button type="submit" class="btn-login">Add User</button>
    </form>
</div>
<?php endif; ?>

<div style="text-align: center; margin-top: 20px;">
    <a href="manage_users.php" class="btn-secondary">Back to User List</a>
</div>

<?php
require_once 'includes/footer.php';
?>

