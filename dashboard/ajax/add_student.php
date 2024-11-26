<?php
session_start();
require_once '../../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $required_fields = ['first_name', 'last_name', 'matriculation_number', 'department_id', 
                       'gender', 'class', 'academic_year_id', 'session_id'];
    
    // Check for missing fields
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            exit;
        }
    }

    try {
        // Check if matriculation number already exists
        $stmt = $conn->prepare("SELECT student_id FROM Students WHERE matriculation_number = ?");
        $stmt->bind_param("s", $_POST['matriculation_number']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'This matriculation number is already registered']);
            exit;
        }

        // Generate password hash from last name
        $password = password_hash($_POST['last_name'], PASSWORD_DEFAULT);

        // Insert new student
        $stmt = $conn->prepare("INSERT INTO Students (first_name, last_name, matriculation_number, 
                              department_id, gender, class, academic_year_id, session_id, password) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->bind_param("ssssssiis", 
            $_POST['first_name'],
            $_POST['last_name'],
            $_POST['matriculation_number'],
            $_POST['department_id'],
            $_POST['gender'],
            $_POST['class'],
            $_POST['academic_year_id'],
            $_POST['session_id'],
            $password
        );

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception("Failed to add student");
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}