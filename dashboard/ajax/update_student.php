<?php
session_start();
require_once '../../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$required_fields = ['student_id', 'first_name', 'department_id', 'gender', 'class', 'academic_year_id'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }
}

$student_id = intval($_POST['student_id']);
$first_name = $_POST['first_name'];
$department_id = intval($_POST['department_id']);
$gender = $_POST['gender'];
$class = $_POST['class'];
$academic_year_id = intval($_POST['academic_year_id']);

try {
    $stmt = $conn->prepare("UPDATE Students SET first_name = ?, department_id = ?, gender = ?, class = ?, academic_year_id = ? WHERE student_id = ?");
    $stmt->bind_param("sissii", $first_name, $department_id, $gender, $class, $academic_year_id, $student_id);
    
    if ($stmt->execute()) {
        // Log the activity
        $admin_id = $_SESSION['admin_id'];
        $activity_type = 'update_student';
        $details = "Updated student information for student ID: $student_id";
        logActivity($conn, $admin_id, $activity_type, $details);

        echo json_encode(['success' => true]);
    } else {
        throw new Exception("Failed to update student information");
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function logActivity($conn, $admin_id, $activity_type, $details) {
    $stmt = $conn->prepare("INSERT INTO ActivityLog (admin_id, activity_type, details) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $admin_id, $activity_type, $details);
    $stmt->execute();
}