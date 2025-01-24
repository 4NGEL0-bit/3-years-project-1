<?php
require_once 'config.php';
requireRole('teacher');

try {
    // Get teacher's classes
    $stmt = $pdo->prepare("
        SELECT c.*, 
               COUNT(e.student_id) as student_count
        FROM courses c
        LEFT JOIN enrollments e ON c.id = e.course_id
        WHERE c.teacher_id = ?
        GROUP BY c.id
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $classes = $stmt->fetchAll();

    // Get pending actions (assignments to grade)
    $stmt = $pdo->prepare("
        SELECT a.*, c.name as course_name,
               COUNT(s.id) as submission_count
        FROM assignments a
        JOIN courses c ON a.course_id = c.id
        LEFT JOIN submissions s ON a.id = s.assignment_id
        WHERE c.teacher_id = ?
        AND s.grade IS NULL
        GROUP BY a.id
        ORDER BY a.due_date ASC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $pendingActions = $stmt->fetchAll();

    // Pass data to template
    $data = [
        'classes' => $classes,
        'pendingActions' => $pendingActions
    ];
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Include the HTML template
include 'templates/teacher_cyber.html';
