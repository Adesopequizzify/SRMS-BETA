<?php
session_start();
require_once '../../db.php';

if (!isset($_SESSION['admin_id']) || !isset($_GET['student_id'])) {
    echo "Unauthorized access or missing student ID.";
    exit();
}

$student_id = $_GET['student_id'];

// Fetch student details
$studentQuery = "SELECT s.*, d.department_name 
                 FROM Students s
                 JOIN Departments d ON s.department_id = d.department_id
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

// Fetch all possible grades
$gradesQuery = "SELECT * FROM Grades ORDER BY grade_letter";
$grades = fetchAll($gradesQuery);

// Start building the HTML form
$html = "<form id='editResultForm'>";
$html .= "<input type='hidden' name='student_id' value='{$student_id}'>";
$html .= "<h3>{$student['first_name']} {$student['last_name']} ({$student['matriculation_number']})</h3>";
$html .= "<p><strong>Department:</strong> {$student['department_name']}</p>";
$html .= "<p><strong>Class:</strong> {$student['class']}</p>";

$html .= "<table class='table table-striped'>";
$html .= "<thead><tr><th>Course Code</th><th>Course Name</th><th>Score</th><th>Grade</th></tr></thead>";
$html .= "<tbody>";

foreach ($results as $result) {
    $html .= "<tr>";
    $html .= "<td>{$result['course_code']}</td>";
    $html .= "<td>{$result['course_name']}</td>";
    $html .= "<td><input type='number' name='scores[{$result['result_id']}]' value='{$result['score']}' min='0' max='100' step='0.01' required></td>";
    $html .= "<td><select name='grades[{$result['result_id']}]' required>";
    foreach ($grades as $grade) {
        $selected = ($grade['grade_id'] == $result['grade_id']) ? 'selected' : '';
        $html .= "<option value='{$grade['grade_id']}' {$selected}>{$grade['grade_letter']}</option>";
    }
    $html .= "</select></td>";
    $html .= "</tr>";
}

$html .= "</tbody></table>";
$html .= "<button type='submit' class='btn btn-primary'>Update Results</button>";
$html .= "</form>";

echo $html;