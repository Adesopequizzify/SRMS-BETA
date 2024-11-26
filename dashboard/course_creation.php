<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit();
}

$departments = fetchAll("SELECT * FROM Departments ORDER BY department_name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process form submission
    $course_code = $_POST['course_code'];
    $course_name = $_POST['course_name'];
    $department_id = $_POST['department_id'];
    $grade_thresholds = $_POST['grade_thresholds'];

    try {
        $conn->begin_transaction();

        // Insert course
        $stmt = $conn->prepare("INSERT INTO Courses (course_code, course_name, department_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $course_code, $course_name, $department_id);
        $stmt->execute();
        $course_id = $conn->insert_id;

        // Insert grade thresholds
        $stmt = $conn->prepare("INSERT INTO Grades (grade_letter, min_percentage, max_percentage) VALUES (?, ?, ?)");
        foreach ($grade_thresholds as $grade => $threshold) {
            $stmt->bind_param("sdd", $grade, $threshold['min'], $threshold['max']);
            $stmt->execute();
        }

        $conn->commit();
        $success_message = "Course created successfully!";
    } catch (Exception $e) {
        $conn->rollback();
        $error_message = "Error creating course: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Creation - LUFEM School</title>
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
                <h2 class="mb-4">Course Creation</h2>

                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <form id="courseCreationForm" method="POST">
                            <div class="mb-3">
                                <label for="course_code" class="form-label">Course Code</label>
                                <input type="text" class="form-control" id="course_code" name="course_code" required>
                            </div>
                            <div class="mb-3">
                                <label for="course_name" class="form-label">Course Name</label>
                                <input type="text" class="form-control" id="course_name" name="course_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="department_id" class="form-label">Department</label>
                                <select class="form-select" id="department_id" name="department_id" required>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?php echo $dept['department_id']; ?>">
                                            <?php echo htmlspecialchars($dept['department_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <h4 class="mt-4">Grade Thresholds</h4>
                            <div id="gradeThresholds">
                                <?php
                                $grades = ['A', 'B', 'C', 'D', 'E', 'F'];
                                foreach ($grades as $grade):
                                ?>
                                <div class="row mb-3">
                                    <div class="col-md-2">
                                        <label class="form-label">Grade</label>
                                        <input type="text" class="form-control" name="grade_thresholds[<?php echo $grade; ?>][grade]" value="<?php echo $grade; ?>" readonly>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label">Min Score</label>
                                        <input type="number" class="form-control" name="grade_thresholds[<?php echo $grade; ?>][min]" min="0" max="100" required>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label">Max Score</label>
                                        <input type="number" class="form-control" name="grade_thresholds[<?php echo $grade; ?>][max]" min="0" max="100" required>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="submit" class="btn btn-primary">Create Course</button>
                        </form>
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