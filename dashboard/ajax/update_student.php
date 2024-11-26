<?php
session_start();
require_once '../../db.php';

if (!isset($_SESSION['admin_id']) || !isset($_POST['student_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access or missing student ID.']);
    exit();
}

$student_id = $_POST['student_id'];
$first_name = $_POST['first_name'];
$gender = $_POST['gender'];
$department_id = $_POST['department_id'];
$class = $_POST['class'];

try {
    $updateQuery = "UPDATE Students 
                    SET first_name = ?, gender = ?, department_id = ?, class = ?
                    WHERE student_id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("ssisi", $first_name, $gender, $department_id, $class, $student_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Student information updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes were made or student not found.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error updating student information: ' . $e->getMessage()]);
}