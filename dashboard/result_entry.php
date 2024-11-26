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
    try {
        $conn->begin_transaction();

        $student_id = $_POST['student_id'];
        $academic_year_id = $_POST['academic_year_id'];
        $session_id = $_POST['session_id'];
        $courses = $_POST['courses'];
        $scores = $_POST['scores'];

        // Validate input
        if (empty($student_id) || empty($academic_year_id) || empty($session_id) || 
            empty($courses) || empty($scores) || count($courses) !== count($scores)) {
            throw new Exception("Invalid form submission");
        }

        // Process each course result
        foreach ($courses as $index => $course_id) {
            $score = floatval($scores[$index]);
            
            // Get grade based on score
            $grade_query = "SELECT grade_id FROM Grades 
                          WHERE ? BETWEEN min_percentage AND max_percentage";
            $grade_result = fetchOne($grade_query, [$score]);
            
            if (!$grade_result) {
                throw new Exception("Invalid score for course ID: $course_id");
            }

            $grade_id = $grade_result['grade_id'];

            // Insert or update result
            $result_query = "INSERT INTO Results 
                           (student_id, course_id, academic_year_id, session_id, score, grade_id)
                           VALUES (?, ?, ?, ?, ?, ?)
                           ON DUPLICATE KEY UPDATE 
                           score = VALUES(score), 
                           grade_id = VALUES(grade_id)";

            executeQuery($result_query, [
                $student_id, $course_id, $academic_year_id, 
                $session_id, $score, $grade_id
            ]);
        }

        $conn->commit();
        $message = "Results submitted successfully";
    } catch (Exception $e) {
        $conn->rollback();
        $message = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Result Entry - LUFEM School</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/dashboard.css">
    <style>
        .grade-badge {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            margin-left: 0.5rem;
        }
        .preview-modal .modal-dialog {
            max-width: 800px;
        }
        .preview-table th, .preview-table td {
            padding: 0.5rem;
        }
        .result-preview {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .course-result {
            background-color: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .course-result .form-label {
            font-weight: 600;
        }
        .grade-display {
            font-weight: 600;
            text-align: center;
            min-width: 60px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <?php include 'sidebar.php'; ?>

        <div id="content">
            <?php include 'navbar.php'; ?>

            <div class="container-fluid mt-4">
                <h2 class="mb-4">Result Entry</h2>

                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo strpos($message, 'Error') !== false ? 'danger' : 'success'; ?> alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <form id="resultEntryForm" method="POST">
                            <input type="hidden" id="studentId" name="student_id">
                            
                            <div class="mb-3">
                                <label for="matricNumber" class="form-label">Matriculation Number</label>
                                <input type="text" class="form-control" id="matricNumber" required>
                            </div>

                            <div id="studentInfo" style="display: none;">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="studentName" class="form-label">Student Name</label>
                                        <input type="text" class="form-control" id="studentName" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="department" class="form-label">Department</label>
                                        <input type="text" class="form-control" id="department" readonly>
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

                                <div id="courseResults" class="mb-3">
                                    <!-- Course results will be dynamically added here -->
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">Submit Results</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Preview Modal -->
            <div class="modal fade" id="previewModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">Result Preview</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body" id="previewContent">
                            <!-- Preview content will be dynamically added here -->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" id="confirmSubmission">
                                Confirm Submission
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../js/dashboard.js"></script>
    <script src="../js/result-entry.js"></script>
</body>
</html>

