<?php
session_start();
require_once '../../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if (empty($_GET['student_id']) || empty($_GET['academic_year_id']) || empty($_GET['session_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$student_id = $_GET['student_id'];
$academic_year_id = $_GET['academic_year_id'];
$session_id = $_GET['session_id'];

try {
    $query = "SELECT c.course_id, c.course_code, c.course_name 
              FROM StudentCourses sc
              JOIN Courses c ON sc.course_id = c.course_id
              WHERE sc.student_id = ? 
              AND sc.academic_year_id = ?
              AND sc.session_id = ?";

    $courses = fetchAll($query, [$student_id, $academic_year_id, $session_id]);

    if (empty($courses)) {
        echo json_encode(['success' => false, 'message' => 'No registered courses found']);
        exit;
    }

    echo json_encode(['success' => true, 'data' => $courses]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

