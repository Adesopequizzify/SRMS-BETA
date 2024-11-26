<?php
session_start();
require_once '../../db.php';

if (!isset($_SESSION['admin_id']) || !isset($_POST['student_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access or missing student ID.']);
    exit();
}

$student_id = $_POST['student_id'];
$scores = $_POST['scores'];
$grades = $_POST['grades'];

$conn->begin_transaction();

try {
    foreach ($scores as $result_id => $score) {
        $grade_id = $grades[$result_id];
        $updateQuery = "UPDATE Results SET score = ?, grade_id = ? WHERE result_id = ? AND student_id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("diii", $score, $grade_id, $result_id, $student_id);
        $stmt->execute();
    }

    // Recalculate overall results
    require_once 'calculate_overall_results.php';
    $recalculationResult = calculateAndUpdateOverallResults();

    if ($recalculationResult['success']) {
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Results updated successfully.']);
    } else {
        throw new Exception($recalculationResult['message']);
    }
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error updating results: ' . $e->getMessage()]);
}