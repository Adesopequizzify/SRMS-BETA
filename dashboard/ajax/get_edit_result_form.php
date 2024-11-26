<?php
session_start();
require_once '../../db.php';

if (!isset($_SESSION['admin_id']) || !isset($_GET['student_id'])) {
    echo "Unauthorized access or missing student ID";
    exit;
}

$student_id = $_GET['student_id'];

// Fetch student information
$student_query = "SELECT s.first_name, s.last_name, s.matriculation_number, d.department_name
                  FROM Students s
                  JOIN Departments d ON s.department_id = d.department_id
                  WHERE s.student_id = ?";
$student = fetchOne($student_query, [$student_id]);

if (!$student) {
    echo "Student not found";
    exit;
}

// Fetch student results
$results_query = "SELECT r.result_id, c.course_code, c.course_name, r.score, g.grade, 
                         ay.academic_year_id, ay.academic_year, sess.session_id, sess.session_name
                  FROM Results r
                  JOIN Courses c ON r.course_id = c.course_id
                  JOIN Grades g ON r.grade_id = g.grade_id
                  JOIN AcademicYears ay ON r.academic_year_id = ay.academic_year_id
                  JOIN Sessions sess ON r.session_id = sess.session_id
                  WHERE r.student_id = ?
                  ORDER BY ay.academic_year DESC, sess.session_name, c.course_code";
$results = fetchAll($results_query, [$student_id]);

// Output HTML
?>
<form id="editResultForm">
    <input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
    <h4><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h4>
    <p><strong>Matric No:</strong> <?php echo htmlspecialchars($student['matriculation_number']); ?></p>
    <p><strong>Department:</strong> <?php echo htmlspecialchars($student['department_name']); ?></p>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Academic Year</th>
                <th>Session</th>
                <th>Course Code</th>
                <th>Course Name</th>
                <th>Score</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($results as $result): ?>
                <tr>
                    <td><?php echo htmlspecialchars($result['academic_year']); ?></td>
                    <td><?php echo htmlspecialchars($result['session_name']); ?></td>
                    <td><?php echo htmlspecialchars($result['course_code']); ?></td>
                    <td><?php echo htmlspecialchars($result['course_name']); ?></td>
                    <td>
                        <input type="number" name="scores[<?php echo $result['result_id']; ?>]" 
                               value="<?php echo htmlspecialchars($result['score']); ?>"
                               min="0" max="100" step="0.01" required class="form-control">
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="text-end mt-3">
        <button type="submit" class="btn btn-primary">Update Results</button>
    </div>
</form>

