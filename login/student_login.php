<?php
session_start();
require_once '../db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matricNumber = $_POST['matricNumber'] ?? '';
    $password = $_POST['studentPassword'] ?? '';

    if (empty($matricNumber) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Please enter both matric number and password.']);
        exit;
    }

    $stmt = $conn->prepare("SELECT student_id, matriculation_number, password, first_name, last_name FROM Students WHERE matriculation_number = ?");
    $stmt->bind_param("s", $matricNumber);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $student = $result->fetch_assoc();
        if (password_verify($password, $student['password'])) {
            $_SESSION['student_id'] = $student['student_id'];
            $_SESSION['student_name'] = $student['first_name'] . ' ' . $student['last_name'];
            $_SESSION['matriculation_number'] = $student['matriculation_number'];
            echo json_encode(['success' => true, 'redirect' => 'dashboard/student/student_dashboard.php']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid matric number or password.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid matric number or password.']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

$conn->close();