<?php
session_start();
require_once '../../db.php';

header('Content-Type: application/json');

// Enable error reporting
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

function logError($message) {
    error_log(date('[Y-m-d H:i:s] ') . "register_courses.php: " . $message . "\n", 3, "../../logs/debug.log");
}

if (!isset($_SESSION['admin_id'])) {
    logError("Unauthorized access attempt");
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Log the incoming POST data
logError("Received POST data: " . json_encode($_POST));

// Validate required fields
if (empty($_POST['student_id']) || !isset($_POST['academic_year_id']) || 
    !isset($_POST['session_id']) || empty($_POST['courses'])) {
    logError("Missing required fields. POST data: " . json_encode($_POST));
    echo json_encode([
        'success' => false, 
        'message' => 'Missing required fields',
        'debug' => [
            'student_id' => isset($_POST['student_id']),
            'academic_year_id' => isset($_POST['academic_year_id']),
            'session_id' => isset($_POST['session_id']),
            'courses' => isset($_POST['courses'])
        ]
    ]);
    exit;
}

try {
    $student_id = (int)$_POST['student_id'];
    $academic_year_id = (int)$_POST['academic_year_id'];
    $session_id = (int)$_POST['session_id'];
    $courses = is_array($_POST['courses']) ? $_POST['courses'] : [$_POST['courses']];

    logError("Processing registration - Student ID: $student_id, Year: $academic_year_id, Session: $session_id");
    logError("Courses to register: " . json_encode($courses));

    // Validate the student exists
    $student_check = fetchOne("SELECT student_id FROM Students WHERE student_id = ?", [$student_id]);
    if (!$student_check) {
        throw new Exception("Invalid student ID");
    }

    $conn->begin_transaction();

    // Delete existing registrations for this combination if any
    $delete_query = "DELETE FROM StudentCourses 
                    WHERE student_id = ? 
                    AND academic_year_id = ? 
                    AND session_id = ?";
    
    executeQuery($delete_query, [$student_id, $academic_year_id, $session_id]);
    logError("Deleted existing registrations if any");

    // Insert new course registrations
    $insert_query = "INSERT INTO StudentCourses 
                    (student_id, course_id, academic_year_id, session_id) 
                    VALUES (?, ?, ?, ?)";
    
    $registered_courses = 0;
    foreach ($courses as $course_id) {
        $course_id = (int)$course_id;
        
        // Validate the course exists
        $course_check = fetchOne("SELECT course_id FROM Courses WHERE course_id = ?", [$course_id]);
        if (!$course_check) {
            throw new Exception("Invalid course ID: " . $course_id);
        }

        try {
            insertData($insert_query, [$student_id, $course_id, $academic_year_id, $session_id]);
            $registered_courses++;
            logError("Registered course_id: $course_id for student_id: $student_id");
        } catch (Exception $e) {
            throw new Exception("Failed to register course ID: $course_id. Error: " . $e->getMessage());
        }
    }

    $conn->commit();
    logError("Successfully registered $registered_courses courses for student_id: $student_id");
    echo json_encode([
        'success' => true, 
        'message' => 'Successfully registered ' . $registered_courses . ' course(s)',
        'registered_courses' => $registered_courses
    ]);

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollback();
    }
    logError("Error registering courses: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Error registering courses: ' . $e->getMessage(),
        'debug' => [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
}