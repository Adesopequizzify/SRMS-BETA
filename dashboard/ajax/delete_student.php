<?php
session_start();
require_once '../../db.php';

if (!isset($_SESSION['admin_id']) || !isset($_POST['student_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access or missing student ID.']);
    exit();
}

$student_id = $_POST['student_id'];

$conn->begin_transaction();

try {
    // Delete from StudentCourses
    $stmt = $conn->prepare("DELETE FROM StudentCourses WHERE student_id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();

    // Delete from Results
    $stmt = $conn->prepare("DELETE FROM Results WHERE student_id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();

    // Delete from StudentOverallResults (if exists)
    $stmt = $conn->prepare("DELETE FROM StudentOverallResults WHERE student_id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();

    // Finally, delete the student
    $stmt = $conn->prepare("DELETE FROM Students WHERE student_id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Student deleted successfully.']);
    } else {
        throw new Exception('Student not found or already deleted.');
    }
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error deleting student: ' . $e->getMessage()]);
}