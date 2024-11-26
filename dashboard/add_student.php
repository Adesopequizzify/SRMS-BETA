<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit();
}

$departments = fetchAll("SELECT * FROM Departments ORDER BY department_name");
$academic_years = fetchAll("SELECT * FROM AcademicYears ORDER BY academic_year DESC");
$sessions = fetchAll("SELECT * FROM Sessions ORDER BY session_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Student - LUFEM School</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
    <div class="wrapper">
        <!-- Include Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Page Content -->
        <div id="content">
            <!-- Include Navbar -->
            <?php include 'navbar.php'; ?>

            <!-- Main Content Area -->
            <div class="container-fluid mt-4">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="card shadow-sm">
                            <div class="card-body p-4">
                                <h4 class="card-title mb-4">Add New Student</h4>
                                
                                <!-- Alert placeholder -->
                                <div id="alertPlaceholder"></div>

                                <form id="addStudentForm">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="firstName" class="form-label">First Name</label>
                                            <input type="text" class="form-control" id="firstName" name="first_name" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="lastName" class="form-label">Last Name</label>
                                            <input type="text" class="form-control" id="lastName" name="last_name" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="gender" class="form-label">Gender</label>
                                            <select class="form-select" id="gender" name="gender" required>
                                                <option value="">Select Gender</option>
                                                <option value="Male">Male</option>
                                                <option value="Female">Female</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="department" class="form-label">Department</label>
                                            <select class="form-select" id="department" name="department_id" required>
                                                <option value="">Select Department</option>
                                                <?php foreach ($departments as $dept): ?>
                                                    <option value="<?php echo $dept['department_id']; ?>">
                                                        <?php echo htmlspecialchars($dept['department_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label for="matricNumber" class="form-label">Matric Number</label>
                                            <input type="text" class="form-control" id="matricNumber" name="matriculation_number" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="class" class="form-label">Class</label>
                                            <select class="form-select" id="class" name="class" required>
                                                <option value="">Select Class</option>
                                                <option value="ND1">ND1</option>
                                                <option value="ND2">ND2</option>
                                                <option value="HND1">HND1</option>
                                                <option value="HND2">HND2</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="academicYear" class="form-label">Academic Year</label>
                                            <select class="form-select" id="academicYear" name="academic_year_id" required>
                                                <option value="">Select Academic Year</option>
                                                <?php foreach ($academic_years as $year): ?>
                                                    <option value="<?php echo $year['academic_year_id']; ?>">
                                                        <?php echo htmlspecialchars($year['academic_year']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="session" class="form-label">Session</label>
                                            <select class="form-select" id="session" name="session_id" required>
                                                <option value="">Select Session</option>
                                                <?php foreach ($sessions as $session): ?>
                                                    <option value="<?php echo $session['session_id']; ?>">
                                                        <?php echo htmlspecialchars($session['session_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-success px-4">
                                                <span class="button-text">Add Student</span>
                                                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                            </button>
                                        </div>
                                    </div>
                                </form>
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
    <script src="../js/add-student.js"></script>
</body>
</html>