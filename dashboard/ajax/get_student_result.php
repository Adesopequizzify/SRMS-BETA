<?php
session_start();
require_once '../../db.php';

if (!isset($_SESSION['admin_id']) || !isset($_GET['student_id'])) {
    echo "Unauthorized access or missing student ID.";
    exit();
}

$student_id = $_GET['student_id'];

// Fetch student details and overall result
$studentQuery = "SELECT s.*, d.department_name, sor.* 
                 FROM Students s
                 JOIN Departments d ON s.department_id = d.department_id
                 LEFT JOIN StudentOverallResults sor ON s.student_id = sor.student_id
                 WHERE s.student_id = ?";
$student = fetchOne($studentQuery, [$student_id]);

if (!$student) {
    echo "Student not found.";
    exit();
}

// Fetch individual course results
$resultsQuery = "SELECT r.*, c.course_code, c.course_name, g.grade_letter
                 FROM Results r
                 JOIN Courses c ON r.course_id = c.course_id
                 JOIN Grades g ON r.grade_id = g.grade_id
                 WHERE r.student_id = ?
                 ORDER BY c.course_code";
$results = fetchAll($resultsQuery, [$student_id]);

// Start building the HTML response
$html = "<h3>{$student['first_name']} {$student['last_name']} ({$student['matriculation_number']})</h3>";
$html .= "<p><strong>Department:</strong> {$student['department_name']}</p>";
$html .= "<p><strong>Class:</strong> {$student['class']}</p>";

if ($student['cumulative_gpa']) {
    $html .= "<p><strong>Cumulative GPA:</strong> " . number_format($student['cumulative_gpa'], 2) . "</p>";
    $html .= "<p><strong>Total Credits Earned:</strong> {$student['total_credits_earned']}</p>";
    $html .= "<p><strong>Overall Grade:</strong> {$student['overall_grade_letter']}</p>";
    $html .= "<p><strong>Final Remark:</strong> {$student['final_remark']}</p>";
} else {
    $html .= "<p><em>No overall result calculated yet.</em></p>";
}

$html .= "<h4 class='mt-4'>Individual Course Results</h4>";
$html .= "<table class='table table-striped'>";
$html .= "<thead><tr><th>Course Code</th><th>Course Name</th><th>Score</th><th>Grade</th></tr></thead>";
$html .= "<tbody>";

foreach ($results as $result) {
    $html .= "<tr>";
    $html .= "<td>{$result['course_code']}</td>";
    $html .= "<td>{$result['course_name']}</td>";
    $html .= "<td>{$result['score']}</td>";
    $html .= "<td>{$result['grade_letter']}</td>";
    $html .= "</tr>";
}

$html .= "</tbody></table>";

echo $html;