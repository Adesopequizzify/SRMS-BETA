<?php
session_start();
require_once '../../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid student ID']);
    exit;
}

$studentId = intval($_GET['id']);

$query = "SELECT s.*, d.department_name 
          FROM Students s
          JOIN Departments d ON s.department_id = d.department_id
          WHERE s.student_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $studentId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Student not found']);
    exit;
}

$student = $result->fetch_assoc();

echo json_encode(['success' => true, 'data' => $student]);