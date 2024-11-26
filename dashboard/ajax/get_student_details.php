<?php
session_start();
require_once '../../db.php';

if (!isset($_SESSION['admin_id']) || !isset($_GET['student_id'])) {
    echo "Unauthorized access or missing student ID.";
    exit();
}

$student_id = $_GET['student_id'];
$editable = isset($_GET['editable']) && $_GET['editable'] === 'true';

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

// Fetch all departments for the dropdown
$departments = fetchAll("SELECT * FROM Departments ORDER BY department_name");

// Start building the HTML response
$html = $editable ? "<form id='editStudentForm'>" : "<div class='student-details'>";
$html .= "<input type='hidden' name='student_id' value='{$student_id}'>";

// Personal Information
$html .= "<h4>Personal Information</h4>";
$html .= "<div class='row mb-3'>";
$html .= "<div class='col-md-6'>";
$html .= "<label class='form-label'>First Name</label>";
$html .= $editable ? "<input type='text' class='form-control' name='first_name' value='{$student['first_name']}' required>" : "<p>{$student['first_name']}</p>";
$html .= "</div>";
$html .= "<div class='col-md-6'>";
$html .= "<label class='form-label'>Last Name</label>";
$html .= "<p>{$student['last_name']}</p>"; // Last name is not editable
$html .= "</div>";
$html .= "</div>";

$html .= "<div class='row mb-3'>";
$html .= "<div class='col-md-6'>";
$html .= "<label class='form-label'>Gender</label>";
if ($editable) {
    $html .= "<select class='form-select' name='gender' required>";
    $html .= "<option value='Male'" . ($student['gender'] == 'Male' ? ' selected' : '') . ">Male</option>";
    $html .= "<option value='Female'" . ($student['gender'] == 'Female' ? ' selected' : '') . ">Female</option>";
    $html .= "<option value='Other'" . ($student['gender'] == 'Other' ? ' selected' : '') . ">Other</option>";
    $html .= "</select>";
} else {
    $html .= "<p>{$student['gender']}</p>";
}
$html .= "</div>";
$html .= "<div class='col-md-6'>";
$html .= "<label class='form-label'>Matriculation Number</label>";
$html .= "<p>{$student['matriculation_number']}</p>"; // Matriculation number is not editable
$html .= "</div>";
$html .= "</div>";

// Academic Information
$html .= "<h4 class='mt-4'>Academic Information</h4>";
$html .= "<div class='row mb-3'>";
$html .= "<div class='col-md-6'>";
$html .= "<label class='form-label'>Department</label>";
if ($editable) {
    $html .= "<select class='form-select' name='department_id' required>";
    foreach ($departments as $dept) {
        $selected = ($dept['department_id'] == $student['department_id']) ? 'selected' : '';
        $html .= "<option value='{$dept['department_id']}' {$selected}>{$dept['department_name']}</option>";
    }
    $html .= "</select>";
} else {
    $html .= "<p>{$student['department_name']}</p>";
}
$html .= "</div>";
$html .= "<div class='col-md-6'>";
$html .= "<label class='form-label'>Class</label>";
if ($editable) {
    $html .= "<select class='form-select' name='class' required>";
    $classes = ['ND1', 'ND2', 'HND1', 'HND2'];
    foreach ($classes as $class) {
        $selected = ($class == $student['class']) ? 'selected' : '';
        $html .= "<option value='{$class}' {$selected}>{$class}</option>";
    }
    $html .= "</select>";
} else {
    $html .= "<p>{$student['class']}</p>";
}
$html .= "</div>";
$html .= "</div>";

// Add more fields as needed

if ($editable) {
    $html .= "<button type='submit' class='btn btn-primary mt-3'>Update Student Information</button>";
    $html .= "</form>";
} else {
    $html .= "</div>";
}

echo $html;