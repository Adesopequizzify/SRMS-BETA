<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection details
$host = 'localhost';
$db   = 'lmf';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Fetch students
    $stmt = $pdo->query("SELECT s.student_id, s.matriculation_number, s.first_name, s.last_name, 
                                d.department_name, s.class, ay.academic_year
                         FROM Students s
                         JOIN Departments d ON s.department_id = d.department_id
                         JOIN AcademicYears ay ON s.academic_year_id = ay.academic_year_id
                         LIMIT 10");
    
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Display results
    echo "<h2>Student Data:</h2>";
    echo "<pre>";
    print_r($students);
    echo "</pre>";
    
    // Display as table
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Matric Number</th><th>First Name</th><th>Last Name</th><th>Department</th><th>Class</th><th>Academic Year</th></tr>";
    foreach ($students as $student) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($student['student_id']) . "</td>";
        echo "<td>" . htmlspecialchars($student['matriculation_number']) . "</td>";
        echo "<td>" . htmlspecialchars($student['first_name']) . "</td>";
        echo "<td>" . htmlspecialchars($student['last_name']) . "</td>";
        echo "<td>" . htmlspecialchars($student['department_name']) . "</td>";
        echo "<td>" . htmlspecialchars($student['class']) . "</td>";
        echo "<td>" . htmlspecialchars($student['academic_year']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>