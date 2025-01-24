<?php
require_once 'init.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'student';

    if (empty($username) || empty($password)) {
        $error = "Please fill in all fields";
    } else {
        try {
            $stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                if ($user['role'] === $role) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];

                    // Update last login
                    $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
                    $stmt->execute(['id' => $user['id']]);

                    // Redirect based on role
                    switch($role) {
                        case 'student':
                            header("Location: student_dashboard.php");
                            break;
                        case 'teacher':
                            header("Location: teacher_dashboard.php");
                            break;
                        case 'admin':
                            header("Location: admin_dashboard.php");
                            break;
                    }
                    exit();
                } else {
                    $error = "Invalid role selected for this user";
                }
            } else {
                $error = "Invalid username or password";
            }
        } catch(PDOException $e) {
            $error = "Login failed: " . $e->getMessage();
        }
    }
}

// Include the login template
include 'templates/login.html';
