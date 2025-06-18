<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clinic Appointment System - Register</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&family=Orbitron:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css"> <!-- Use the same stylesheet -->
</head>
<body>
    <div class="login-container register-container"> <!-- Added register-container for potential specific tweaks -->
        <div class="login-art-panel">
            <div class="art-content">
                <h2>Join Our Network of Care.</h2>
                <p>Register today for streamlined access to your health journey.</p>
                <div class="animated-bg-shapes">
                    <span></span><span></span><span></span><span></span><span></span>
                    <span></span><span></span><span></span><span></span><span></span>
                </div>
            </div>
        </div>
        <div class="login-form-panel">
            <div class="form-wrapper">
                <div class="logo-header">
                    <h1>ClinicSys</h1>
                    <p>Create Your Account</p>
                </div>
                <?php
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }
                require_once 'includes/db.php';
                require_once 'includes/functions.php';

                $error_message = '';
                $success_message = '';

                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    $name = sanitize_input($_POST["name"]);
                    $email = sanitize_input($_POST["email"]);
                    $phone = sanitize_input($_POST["phone"]);
                    $address = sanitize_input($_POST["address"]);
                    $gender = sanitize_input($_POST["gender"]);
                    $dob = sanitize_input($_POST["dob"]);
                    $password = sanitize_input($_POST["password"]);
                    $confirm_password = sanitize_input($_POST["confirm_password"]);

                    // Basic Validations
                    if (empty($name) || empty($email) || empty($password) || empty($confirm_password) || empty($dob)) {
                        $error_message = "Please fill in all required fields (Name, Email, DOB, Password, Confirm Password).";
                    } elseif ($password !== $confirm_password) {
                        $error_message = "Passwords do not match.";
                    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $error_message = "Invalid email format.";
                    } else {
                        // Check if email already exists
                        $stmt_check = $conn->prepare("SELECT id FROM users WHERE email = ?");
                        $stmt_check->bind_param("s", $email);
                        $stmt_check->execute();
                        $result_check = $stmt_check->get_result();

                        if ($result_check->num_rows > 0) {
                            $error_message = "An account with this email already exists.";
                        } else {
                            // HASH THE PASSWORD - IMPORTANT!
                            // $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                            // For now, using plain text as in login, but this MUST be changed
                            $hashed_password = $password; // REPLACE with actual hashing

                            $stmt_insert = $conn->prepare("INSERT INTO users (nom, email, phone, address, gender, date_of_birth, mot_de_passe, role) VALUES (?, ?, ?, ?, ?, ?, ?, 'patient')");
                            if ($stmt_insert) {
                                $stmt_insert->bind_param("sssssss", $name, $email, $phone, $address, $gender, $dob, $hashed_password);
                                if ($stmt_insert->execute()) {
                                    $success_message = "Registration successful! You can now <a href='index.php'>login</a>.";
                                } else {
                                    $error_message = "Error during registration. Please try again. " . $stmt_insert->error;
                                }
                                $stmt_insert->close();
                            } else {
                                $error_message = "Database error: Could not prepare statement for insertion.";
                            }
                        }
                        $stmt_check->close();
                    }
                }
                ?>

                <?php if (!empty($error_message)): ?>
                    <div class="error-message-login"><?php echo $error_message; ?></div>
                <?php endif; ?>
                <?php if (!empty($success_message)): ?>
                    <div class="success-message-login"><?php echo $success_message; ?></div> <!-- You might want a different class for success -->
                <?php endif; ?>

                <?php if (empty($success_message)): // Only show form if not successfully registered ?>
                <form action="register.php" method="POST" class="login-form register-form">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" placeholder="Enter your full name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number (Optional)</label>
                        <input type="tel" id="phone" name="phone" placeholder="Enter your phone number">
                    </div>
                    <div class="form-group">
                        <label for="address">Address (Optional)</label>
                        <input type="text" id="address" name="address" placeholder="Enter your address">
                    </div>
                    <div class="form-group">
                        <label for="gender">Gender (Optional)</label>
                        <select id="gender" name="gender">
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="dob">Date of Birth</label>
                        <input type="date" id="dob" name="dob" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Create a password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                    </div>
                    <button type="submit" class="btn-login">Register</button>
                </form>
                <?php endif; ?>
                <div class="register-link">
                    <p>Already have an account? <a href="index.php">Login Here</a></p>
                </div>
            </div>
        </div>
    </div>
    <script src="js/animations.js"></script>
</body>
</html>

