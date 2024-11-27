<?php
session_start();
require_once __DIR__ . '/../../db.php';  // Correct path to db.php from ajax folder

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'];
    $academic_year_id = $_POST['academic_year_id'];
    $session_id = $_POST['session_id'];
    $results = $_POST['results'];

    // Start transaction
    $conn->begin_transaction();

    try {
        foreach ($results as $result) {
            $result_id = $result['result_id'];
            $score = $result['score'];
            $grade_id = $result['grade_id'];

            $update_query = "UPDATE Results SET score = ?, grade_id = ? WHERE result_id = ? AND student_id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("diii", $score, $grade_id, $result_id, $student_id);
            $stmt->execute();

            if ($stmt->affected_rows === 0) {
                throw new Exception("No changes were made to result ID: $result_id");
            }
        }

        // Recalculate overall results
        require_once __DIR__ . '/../calculate_overall_results.php';  // Correct path to calculate_overall_results.php
        $recalculation_result = calculateAndUpdateOverallResults($student_id, $academic_year_id, $session_id);

        if (!$recalculation_result['success']) {
            throw new Exception($recalculation_result['message']);
        }

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Results updated successfully']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Error updating results: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

