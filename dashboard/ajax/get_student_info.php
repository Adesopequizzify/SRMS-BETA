<?php
session_start();
require_once '../../db.php';

header('Content-Type: application/json');

// Enable error reporting
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Log function
function logError($message) {
    error_log("get_student_info.php: " . $message);
}

if (!isset($_SESSION['admin_id'])) {
    logError("Unauthorized access attempt");
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if (empty($_GET['matriculation_number'])) {
    logError("Matriculation number is empty");
    echo json_encode(['success' => false, 'message' => 'Matriculation number is required']);
    exit;
}

$matriculation_number = $_GET['matriculation_number'];

logError("Searching for student with matriculation number: " . $matriculation_number);

try {
    $query = "SELECT s.student_id, s.first_name, s.last_name, s.class, s.department_id, s.academic_year_id, d.department_name
            FROM Students s
            JOIN Departments d ON s.department_id = d.department_id
            WHERE s.matriculation_number = ?";

    $student = fetchOne($query, [$matriculation_number]);

    if (!$student) {
        logError("No student found with matriculation number: " . $matriculation_number);
        echo json_encode(['success' => false, 'message' => 'Student not found']);
        exit;
    }

    logError("Student found: " . json_encode($student));
    echo json_encode(['success' => true, 'data' => $student]);
} catch (Exception $e) {
    logError("Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while fetching student information']);
}

