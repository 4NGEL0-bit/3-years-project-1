<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Testing Database Connection</h2>";

try {
    $db = new PDO("mysql:host=localhost;dbname=cyber_education", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Database connection successful!<br>";

    // Test if users table exists
    $stmt = $db->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "Users table exists!<br>";
        
        // Test if we can read users
        $stmt = $db->query("SELECT * FROM users");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Found " . count($users) . " users in database.<br>";
    } else {
        echo "Users table does not exist! Please run setup.sql first.<br>";
    }

} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "<br>";
}

echo "<h2>Testing File Structure</h2>";
$required_files = [
    'init.php',
    'login.php',
    'templates/login.html',
    'templates/student_matrix.html',
    'templates/teacher_cyber.html',
    'templates/admin_dashboard.html'
];

foreach ($required_files as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "✅ Found $file<br>";
    } else {
        echo "❌ Missing $file<br>";
    }
}

echo "<h2>Testing PHP Configuration</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Display Errors: " . ini_get('display_errors') . "<br>";
echo "Error Reporting: " . ini_get('error_reporting') . "<br>";
echo "Session Support: " . (session_status() === PHP_SESSION_ACTIVE ? "Active" : "Inactive") . "<br>";
