<?php
require_once 'config.php';
requireRole('admin');

try {
    // Get system status
    $stmt = $pdo->prepare("SELECT * FROM system_status ORDER BY last_updated DESC");
    $stmt->execute();
    $systemStatus = $stmt->fetchAll();

    // Get user statistics
    $stats = [
        'total_users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
        'active_users' => $pdo->query("
            SELECT COUNT(*) FROM users 
            WHERE last_login > DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ")->fetchColumn(),
        'teachers_online' => $pdo->query("
            SELECT COUNT(*) FROM users 
            WHERE role = 'teacher' 
            AND last_login > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ")->fetchColumn(),
        'new_registrations' => $pdo->query("
            SELECT COUNT(*) FROM users 
            WHERE created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
        ")->fetchColumn()
    ];

    // Calculate system health percentage
    $totalComponents = count($systemStatus);
    $healthyComponents = 0;
    foreach ($systemStatus as $status) {
        if ($status['status'] === 'ONLINE') {
            $healthyComponents++;
        }
    }
    $systemHealth = $totalComponents > 0 ? 
        round(($healthyComponents / $totalComponents) * 100, 1) : 0;

    // Pass data to template
    $data = [
        'systemStatus' => $systemStatus,
        'stats' => $stats,
        'systemHealth' => $systemHealth
    ];
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Include the HTML template
include 'templates/admin_dashboard.html';
