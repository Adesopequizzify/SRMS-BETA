<?php
session_start();
require_once '../../db.php';

// Check if the student is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: ../../index.php');
    exit();
}

$student_id = $_SESSION['student_id'];

// Fetch student information
$stmt = $conn->prepare("SELECT s.*, d.department_name 
                        FROM Students s
                        JOIN Departments d ON s.department_id = d.department_id
                        WHERE s.student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

// Fetch all academic years and sessions for the student
$stmt = $conn->prepare("SELECT DISTINCT r.academic_year_id, r.session_id, ay.academic_year, s.session_name
                        FROM Results r
                        JOIN AcademicYears ay ON r.academic_year_id = ay.academic_year_id
                        JOIN Sessions s ON r.session_id = s.session_id
                        WHERE r.student_id = ?
                        ORDER BY ay.academic_year DESC,
CASE 
                            WHEN s.session_name = 'First Semester' THEN 1
                            WHEN s.session_name = 'Second Semester' THEN 2
                            ELSE 3
                        END");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$academic_sessions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get selected academic year and session
$selected_academic_year_id = $_GET['academic_year_id'] ?? $academic_sessions[0]['academic_year_id'];
$selected_session_id = $_GET['session_id'] ?? $academic_sessions[0]['session_id'];

// Fetch results for the selected academic year and session
$stmt = $conn->prepare("SELECT r.*, c.course_code, c.course_name, g.grade_letter
                        FROM Results r
                        JOIN Courses c ON r.course_id = c.course_id
                        JOIN Grades g ON r.grade_id = g.grade_id
                        WHERE r.student_id = ? AND r.academic_year_id = ? AND r.session_id = ?
                        ORDER BY c.course_code");
$stmt->bind_param("iii", $student_id, $selected_academic_year_id, $selected_session_id);
$stmt->execute();
$results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch overall result for the selected academic year and session
$stmt = $conn->prepare("SELECT * FROM StudentOverallResults
                        WHERE student_id = ? AND academic_year_id = ? AND session_id = ?");
$stmt->bind_param("iii", $student_id, $selected_academic_year_id, $selected_session_id);
$stmt->execute();
$overall_result = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Results - LUFEM School</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../css/student_dashboard.css">
</head>
<body>
   <div class="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar" class="active">
            <div class="sidebar-header">
                <h3>LUFEM School</h3>
            </div>
            <ul class="list-unstyled components">
                <li class="">
                    <a href="student_dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
                </li>
                <li>
                    <a href="profile.php"><i class="bi bi-person"></i> Profile</a>
                </li>
                <li class="active">
                    <a href="results.php"><i class="bi bi-file-earmark-text"></i> Results</a>
                </li>
                <li>
                    <a href="../../logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
                </li>
            </ul>
        </nav>
        <!-- Page Content -->
        <div id="content">
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn btn-info">
                        <i class="bi bi-list"></i>
                    </button>
                    <span class="navbar-text ms-3">
                        Welcome, <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                    </span>
                </div>
            </nav>

            <div class="container-fluid mt-4">
                <h2 class="mb-4">Student Results</h2>
                
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Select Academic Year and Session</h5>
                        <form method="GET" action="" class="row g-3">
                            <div class="col-md-6">
                                <select name="academic_year_id" class="form-select" onchange="this.form.submit()">
                                    <?php foreach ($academic_sessions as $session): ?>
                                        <option value="<?php echo $session['academic_year_id']; ?>" 
                                                <?php echo $session['academic_year_id'] == $selected_academic_year_id ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($session['academic_year']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <select name="session_id" class="form-select" onchange="this.form.submit()">
                                    <?php foreach ($academic_sessions as $session): ?>
                                        <?php if ($session['academic_year_id'] == $selected_academic_year_id): ?>
                                            <option value="<?php echo $session['session_id']; ?>"
                                                    <?php echo $session['session_id'] == $selected_session_id ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($session['session_name']); ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </form>
                    </div>
                </div>

                <?php if (!empty($results)): ?>
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Course Results</h5>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Course Code</th>
                                            <th>Course Name</th>
                                            <th>Score</th>
                                            <th>Grade</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($results as $result): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($result['course_code']); ?></td>
                                                <td><?php echo htmlspecialchars($result['course_name']); ?></td>
                                                <td><?php echo number_format($result['score'], 2); ?></td>
                                                <td><?php echo htmlspecialchars($result['grade_letter']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <?php if ($overall_result): ?>
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Overall Result</h5>
                                <p><strong>GPA:</strong> <?php echo number_format($overall_result['cumulative_gpa'], 2); ?></p>
                                <p><strong>Credits Earned:</strong> <?php echo $overall_result['total_credits_earned']; ?></p>
                                <p><strong>Overall Grade:</strong> <?php echo $overall_result['overall_grade_letter']; ?></p>
                                <p><strong>Remark:</strong> <?php echo $overall_result['final_remark']; ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-info">No results available for the selected academic year and session.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/student_dashboard.js"></script>
    <script src="../../js/sidebar.js"></script>
</body>
</html>
