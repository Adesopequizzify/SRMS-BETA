<?php
session_start();
require_once '../../db.php';

// Check if the student is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: ../../index.php');
    exit();
}

// Fetch student information
$student_id = $_SESSION['student_id'];
$stmt = $conn->prepare("SELECT s.*, d.department_name, ay.academic_year, sess.session_name 
                        FROM Students s
                        JOIN Departments d ON s.department_id = d.department_id
                        JOIN AcademicYears ay ON s.academic_year_id = ay.academic_year_id
                        JOIN Sessions sess ON s.session_id = sess.session_id
                        WHERE s.student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

// Fetch the latest result summary
$stmt = $conn->prepare("SELECT * FROM StudentOverallResults 
                        WHERE student_id = ? 
                        ORDER BY academic_year_id DESC, session_id DESC 
                        LIMIT 1");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$latestResult = $result->fetch_assoc();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - LUFEM School</title>
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
                <li class="active">
                    <a href="student_dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
                </li>
                <li>
                    <a href="profile.php"><i class="bi bi-person"></i> Profile</a>
                </li>
                <li>
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
                <h2 class="mb-4">Student Dashboard</h2>
                
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Student Information</h5>
                                <p><strong>Matric Number:</strong> <?php echo htmlspecialchars($student['matriculation_number']); ?></p>
                                <p><strong>Department:</strong> <?php echo htmlspecialchars($student['department_name']); ?></p>
                                <p><strong>Class:</strong> <?php echo htmlspecialchars($student['class']); ?></p>
                                <p><strong>Current Session:</strong> <?php echo htmlspecialchars($student['academic_year'] . ' - ' . $student['session_name']); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Latest Result Summary</h5>
                                <?php if ($latestResult): ?>
                                    <p><strong>GPA:</strong> <?php echo number_format($latestResult['cumulative_gpa'], 2); ?></p>
                                    <p><strong>Credits Earned:</strong> <?php echo $latestResult['total_credits_earned']; ?></p>
                                    <p><strong>Overall Grade:</strong> <?php echo $latestResult['overall_grade_letter']; ?></p>
                                    <p><strong>Remark:</strong> <?php echo $latestResult['final_remark']; ?></p>
                                <?php else: ?>
                                    <p>No results available yet.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/sidebar.js"></script>
</body>
</html>

