<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clinic Appointment System - Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&family=Orbitron:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-art-panel">
            <!-- Placeholder for futuristic medical animation or graphic -->
            <div class="art-content">
                <h2>Future of Health, Today.</h2>
                <p>Seamlessly manage appointments and patient care with our next-generation platform.</p>
                <div class="animated-bg-shapes">
                    <span></span><span></span><span></span><span></span><span></span>
                    <span></span><span></span><span></span><span></span><span></span>
                </div>
            </div>
        </div>
        <div class="login-form-panel">
            <div class="form-wrapper">
                <div class="logo-header">
                    <!-- You can replace text with an actual logo image -->
                    <h1>ClinicSys</h1>
                    <p>Welcome Back! Please login to your account.</p>
                </div>
                <?php
                // Start session if not already started
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }
                require_once 'includes/db.php';
                require_once 'includes/functions.php';

                $error_message = '';

                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    $email = sanitize_input($_POST["email"]);
                    $password = sanitize_input($_POST["password"]);

                    if (empty($email) || empty($password)) {
                        $error_message = "Email and password are required.";
                    } else {
                        // Prepare statement to prevent SQL injection
                        $stmt = $conn->prepare("SELECT id, nom, mot_de_passe, role FROM users WHERE email = ?");
                        if ($stmt === false) {
                            $error_message = "Database error: Could not prepare statement.";
                        } else {
                            $stmt->bind_param("s", $email);
                            $stmt->execute();
                            $result = $stmt->get_result();

                            if ($result->num_rows == 1) {
                                $user = $result->fetch_assoc();
                                // Verify password (assuming plain text for now, but HASHING IS CRITICAL)
                                // For a real system, use password_verify($password, $user["mot_de_passe"])
                                if ($password === $user["mot_de_passe"]) { // REPLACE with HASHED password check
                                    $_SESSION["user_id"] = $user["id"];
                                    $_SESSION["user_name"] = $user["nom"];
                                    $_SESSION["user_role"] = $user["role"];
                                    redirect("dashboard.php");
                                } else {
                                    $error_message = "Invalid email or password.";
                                }
                            } else {
                                $error_message = "Invalid email or password.";
                            }
                            $stmt->close();
                        }
                    }
                }
                ?>
                <form action="index.php" method="POST" class="login-form">
                    <?php if (!empty($error_message)): ?>
                        <div class="error-message-login"><?php echo $error_message; ?></div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    </div>
                    <div class="form-options">
                        <label class="remember-me">
                            <input type="checkbox" name="remember">
                            Remember me
                        </label>
                        <a href="#" class="forgot-password">Forgot Password?</a>
                    </div>
                    <button type="submit" class="btn-login">Login</button>
                </form>
                <div class="register-link">
                    <p>Don't have an account? <a href="register.php">Sign Up</a></p>
                </div>
            </div>
        </div>
    </div>
    <script src="js/animations.js"></script> <!-- Placeholder for JS animations -->
</body>
</html>

