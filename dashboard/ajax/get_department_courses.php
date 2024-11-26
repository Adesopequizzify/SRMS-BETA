<?php
session_start();
require_once '../../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if (empty($_GET['department_id'])) {
    echo json_encode(['success' => false, 'message' => 'Department ID is required']);
    exit;
}

$department_id = $_GET['department_id'];

$query = "SELECT course_id, course_code, course_name FROM Courses WHERE department_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param('i', $department_id);
$stmt->execute();
$result = $stmt->get_result();

$courses = $result->fetch_all(MYSQLI_ASSOC);

if (empty($courses)) {
    echo json_encode(['success' => false, 'message' => 'No courses found for this department']);
    exit;
}

echo json_encode(['success' => true, 'data' => $courses]);

