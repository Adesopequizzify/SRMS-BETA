<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - LUFEM School</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar">
            <div class="sidebar-header">
                <h3>LUFEM School</h3>
            </div>

            <ul class="list-unstyled components">
                <li class="active">
                    <a href="admin_dashboard.php">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="add_student.php">
                        <i class="bi bi-person-plus"></i> Add New Student
                    </a>
                </li>
                <li>
                    <a href="student_list.php">
                        <i class="bi bi-people"></i> Registered Students
                    </a>
                </li>
                <li>
                    <a href="course_registration.php">
                        <i class="bi bi-book"></i> Course Registration
                    </a>
                </li>
                <li>
                    <a href="student_course_registration.php">
                        <i class="bi bi-pencil-square"></i> Student Course Registration
                    </a>
                </li>
                <li>
                    <a href="result_entry.php">
                        <i class="bi bi-pencil-square"></i> Insert New Result
                    </a>
                </li>
                <li>
                    <a href="view_results.php">
                        <i class="bi bi-table"></i> View Results
                    </a>
                </li>
                <li>
                    <a href="../logout.php">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Page Content -->
        <div id="content">
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn btn-success">
                        <i class="bi bi-list"></i>
                    </button>
                    <span class="navbar-text ms-auto">
                        Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>
                    </span>
                </div>
            </nav>

            <!-- Main Content Area -->
            <div class="container-fluid mt-4">
                <div class="row">
                    <div class="col-md-6 col-lg-3 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-2 text-muted">Total Students</h6>
                                <h2 class="card-title mb-0" id="totalStudents">Loading...</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-2 text-muted">Total Courses</h6>
                                <h2 class="card-title mb-0" id="totalCourses">Loading...</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-2 text-muted">Total Departments</h6>
                                <h2 class="card-title mb-0" id="totalDepartments">Loading...</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-2 text-muted">Total Results</h6>
                                <h2 class="card-title mb-0" id="totalResults">Loading...</h2>
                            </div>
                        </div>
                    </div>
                </div>

      
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../js/dashboard.js"></script>
</body>
</html>