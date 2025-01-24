<?php
require_once 'includes/init.php';
checkRole(['student']);

try {
    // Get student's courses
    $query = "SELECT c.*, e.progress
             FROM courses c
             JOIN enrollments e ON c.id = e.course_id
             WHERE e.student_id = :student_id";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':student_id', $_SESSION['user_id']);
    $stmt->execute();
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get upcoming assignments
    $query = "SELECT a.*, c.name as course_name
             FROM assignments a
             JOIN courses c ON a.course_id = c.id
             JOIN enrollments e ON c.id = e.course_id
             WHERE e.student_id = :student_id
             AND a.due_date > NOW()
             ORDER BY a.due_date ASC
             LIMIT 3";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':student_id', $_SESSION['user_id']);
    $stmt->execute();
    $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate overall progress
    $totalProgress = 0;
    foreach ($courses as $course) {
        $totalProgress += $course['progress'];
    }
    $overallProgress = count($courses) > 0 ? round($totalProgress / count($courses)) : 0;

    // Pass data to template
    $data = [
        'courses' => $courses,
        'assignments' => $assignments,
        'progress' => $overallProgress
    ];
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Include the HTML template
include 'templates/student_matrix.html';
