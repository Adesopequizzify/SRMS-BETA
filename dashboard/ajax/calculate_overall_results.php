<?php
session_start();
require_once '../../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if (empty($_POST['student_id']) || empty($_POST['academic_year_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$student_id = $_POST['student_id'];
$academic_year_id = $_POST['academic_year_id'];

try {
    $conn->begin_transaction();

    // Calculate overall results
    $query = "SELECT AVG(r.score) as average_score, COUNT(*) as total_courses,
              SUM(CASE 
                  WHEN g.grade_letter = 'A' THEN 4
                  WHEN g.grade_letter = 'B' THEN 3
                  WHEN g.grade_letter = 'C' THEN 2
                  WHEN g.grade_letter = 'D' THEN 1
                  ELSE 0
              END) as total_grade_points
              FROM Results r
              JOIN Grades g ON r.grade_id = g.grade_id
              WHERE r.student_id = ? AND r.academic_year_id = ?";
    
    $result = fetchOne($query, [$student_id, $academic_year_id]);
    
    if ($result) {
        $average_score = $result['average_score'];
        $total_courses = $result['total_courses'];
        $total_grade_points = $result['total_grade_points'];
        
        $cumulative_gpa = $total_grade_points / $total_courses;
        $overall_grade_letter = getOverallGradeLetter($cumulative_gpa);
        $final_remark = getFinalRemark($cumulative_gpa);
        
        $insert_query = "INSERT INTO StudentOverallResults 
                         (student_id, academic_year_id, cumulative_gpa, total_credits_earned, 
                          overall_grade_letter, final_remark)
                         VALUES (?, ?, ?, ?, ?, ?)
                         ON DUPLICATE KEY UPDATE
                         cumulative_gpa = VALUES(cumulative_gpa),
                         total_credits_earned = VALUES(total_credits_earned),
                         overall_grade_letter = VALUES(overall_grade_letter),
                         final_remark = VALUES(final_remark)";
        
        executeQuery($insert_query, [
            $student_id, $academic_year_id, $cumulative_gpa, $total_courses,
            $overall_grade_letter, $final_remark
        ]);

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Overall results calculated and saved successfully']);
    } else {
        throw new Exception("No results found for the given student and academic year");
    }
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function getOverallGradeLetter($gpa) {
    if ($gpa >= 3.5) return 'A';
    if ($gpa >= 3.0) return 'B';
    if ($gpa >= 2.5) return 'C';
    if ($gpa >= 2.0) return 'D';
    return 'F';
}

function getFinalRemark($gpa) {
    if ($gpa >= 3.5) return 'Distinction';
    if ($gpa >= 3.0) return 'Upper Credit';
    if ($gpa >= 2.5) return 'Lower Credit';
    if ($gpa >= 2.0) return 'Pass';
    return 'Fail';
}

