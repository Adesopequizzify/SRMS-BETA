<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit();
}

$academic_years = fetchAll("SELECT * FROM AcademicYears ORDER BY academic_year DESC");
$sessions = fetchAll("SELECT * FROM Sessions ORDER BY session_name");

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process form submission
    $student_id = $_POST['student_id'] ?? '';
    $academic_year_id = $_POST['academic_year_id'] ?? '';
    $session_id = $_POST['session_id'] ?? '';
    $courses = $_POST['courses'] ?? [];

    if (empty($student_id) || empty($academic_year_id) || empty($session_id) || empty($courses)) {
        $message = 'All fields are required.';
    } else {
        try {
            $conn->begin_transaction();

            // Delete existing registrations for this combination if any
            $delete_query = "DELETE FROM StudentCourses 
                            WHERE student_id = ? 
                            AND academic_year_id = ? 
                            AND session_id = ?";
            
            executeQuery($delete_query, [$student_id, $academic_year_id, $session_id]);

            // Insert new course registrations
            $insert_query = "INSERT INTO StudentCourses 
                            (student_id, course_id, academic_year_id, session_id) 
                            VALUES (?, ?, ?, ?)";
            
            $registered_courses = 0;
            foreach ($courses as $course_id) {
                insertData($insert_query, [$student_id, $course_id, $academic_year_id, $session_id]);
                $registered_courses++;
            }

            $conn->commit();
            $message = "Successfully registered $registered_courses course(s).";
        } catch (Exception $e) {
            $conn->rollback();
            $message = "Error registering courses: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Course Registration - LUFEM School</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
    <div class="wrapper">
        <?php include 'sidebar.php'; ?>

        <div id="content">
            <?php include 'navbar.php'; ?>

            <div class="container-fluid mt-4">
                <h2 class="mb-4">Student Course Registration</h2>

                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo strpos($message, 'Error') !== false ? 'danger' : 'success'; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <form id="courseRegistrationForm" method="POST">
                            <div class="mb-3">
                                <label for="matricNumber" class="form-label">Matriculation Number</label>
                                <input type="text" class="form-control" id="matricNumber" name="matriculation_number" required>
                            </div>
                            <div id="studentInfo" style="display: none;">
                                <input type="hidden" id="studentId" name="student_id">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="firstName" class="form-label">First Name</label>
                                        <input type="text" class="form-control" id="firstName" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="lastName" class="form-label">Last Name</label>
                                        <input type="text" class="form-control" id="lastName" readonly>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="department" class="form-label">Department</label>
                                        <input type="text" class="form-control" id="department" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="class" class="form-label">Class</label>
                                        <input type="text" class="form-control" id="class" readonly>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="academicYear" class="form-label">Academic Year</label>
                                        <select class="form-select" id="academicYear" name="academic_year_id" required>
                                            <?php foreach ($academic_years as $year): ?>
                                                <option value="<?php echo $year['academic_year_id']; ?>">
                                                    <?php echo htmlspecialchars($year['academic_year']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="session" class="form-label">Session</label>
                                        <select class="form-select" id="session" name="session_id" required>
                                            <?php foreach ($sessions as $session): ?>
                                                <option value="<?php echo $session['session_id']; ?>">
                                                    <?php echo htmlspecialchars($session['session_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div id="courseList" class="mb-3">
                                    <!-- Course checkboxes will be dynamically added here -->
                                </div>
                                <button type="submit" class="btn btn-primary">Register Courses</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../js/dashboard.js"></script>
    <script src="../js/student-course-registration.js"></script>
</body>
</html>
