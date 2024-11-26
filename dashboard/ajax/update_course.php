<?php
session_start();
require_once '../../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if (empty($_POST['course_id']) || empty($_POST['course_code']) || empty($_POST['course_name']) || empty($_POST['department_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$course_id = $_POST['course_id'];
$course_code = $_POST['course_code'];
$course_name = $_POST['course_name'];
$department_id = $_POST['department_id'];

try {
    $query = "UPDATE Courses SET course_code = ?, course_name = ?, department_id = ? WHERE course_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssii', $course_code, $course_name, $department_id, $course_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Course updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes were made or course not found']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error updating course: ' . $e->getMessage()]);
}

