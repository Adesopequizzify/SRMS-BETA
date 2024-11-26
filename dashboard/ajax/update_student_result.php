<?php
session_start();
require_once '../../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id']) || !isset($_POST['student_id']) || !isset($_POST['scores'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access or missing data']);
    exit;
}

$student_id = $_POST['student_id'];
$scores = $_POST['scores'];

try {
    $conn->begin_transaction();

    foreach ($scores as $result_id => $score) {
        $score = floatval($score);
        
        // Get grade based on score
        $grade_query = "SELECT grade_id FROM Grades 
                        WHERE ? BETWEEN min_percentage AND max_percentage";
        $grade_result = fetchOne($grade_query, [$score]);
        
        if (!$grade_result) {
            throw new Exception("Invalid score for result ID: $result_id");
        }

        $grade_id = $grade_result['grade_id'];

        // Update result
        $update_query = "UPDATE Results 
                         SET score = ?, grade_id = ?
                         WHERE result_id = ? AND student_id = ?";
        executeQuery($update_query, [$score, $grade_id, $result_id, $student_id]);
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Results updated successfully']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

