<?php
session_start();
require_once '../../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

try {
    $stats = [
        'totalStudents' => fetchOne("SELECT COUNT(*) as count FROM Students")['count'],
        'totalCourses' => fetchOne("SELECT COUNT(*) as count FROM Courses")['count'],
        'totalDepartments' => fetchOne("SELECT COUNT(*) as count FROM Departments")['count'],
        'totalResults' => fetchOne("SELECT COUNT(*) as count FROM Results")['count']
    ];

    echo json_encode($stats);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}