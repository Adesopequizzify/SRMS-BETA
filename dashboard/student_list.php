<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit();
}

// Fetch all departments, academic years, and sessions for filters
$departments = fetchAll("SELECT * FROM Departments ORDER BY department_name");
$academic_years = fetchAll("SELECT * FROM AcademicYears ORDER BY academic_year DESC");
$sessions = fetchAll("SELECT * FROM Sessions ORDER BY session_name");

// Initialize filter variables
$filters = [
    'department_id' => $_GET['department_id'] ?? '',
    'academic_year_id' => $_GET['academic_year_id'] ?? '',
    'session_id' => $_GET['session_id'] ?? '',
    'search' => $_GET['search'] ?? '',
];

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build the query
$query = "SELECT s.student_id, s.first_name, s.last_name, s.matriculation_number, s.gender, s.class,
               d.department_name, ay.academic_year, sess.session_name
        FROM Students s
        JOIN Departments d ON s.department_id = d.department_id
        JOIN AcademicYears ay ON s.academic_year_id = ay.academic_year_id
        JOIN Sessions sess ON s.session_id = sess.session_id
        WHERE 1=1";

$params = [];

if (!empty($filters['department_id'])) {
    $query .= " AND s.department_id = ?";
    $params[] = $filters['department_id'];
}

if (!empty($filters['academic_year_id'])) {
    $query .= " AND s.academic_year_id = ?";
    $params[] = $filters['academic_year_id'];
}

if (!empty($filters['session_id'])) {
    $query .= " AND s.session_id = ?";
    $params[] = $filters['session_id'];
}

if (!empty($filters['search'])) {
    $query .= " AND (s.first_name LIKE ? OR s.last_name LIKE ? OR s.matriculation_number LIKE ?)";
    $searchTerm = "%{$filters['search']}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$query .= " ORDER BY s.last_name, s.first_name";
$query .= " LIMIT ? OFFSET ?";

$params[] = $limit;
$params[] = $offset;

// Execute the query
$students = fetchAll($query, $params);

// Count total results for pagination
$countQuery = "SELECT COUNT(*) as total FROM Students s
             WHERE 1=1";

if (!empty($filters['department_id'])) {
    $countQuery .= " AND s.department_id = ?";
}

if (!empty($filters['academic_year_id'])) {
    $countQuery .= " AND s.academic_year_id = ?";
}

if (!empty($filters['session_id'])) {
    $countQuery .= " AND s.session_id = ?";
}

if (!empty($filters['search'])) {
    $countQuery .= " AND (s.first_name LIKE ? OR s.last_name LIKE ? OR s.matriculation_number LIKE ?)";
}

$totalStudents = fetchOne($countQuery, array_slice($params, 0, -2))['total'];
$totalPages = ceil($totalStudents / $limit);

// Function to get student details
function getStudentDetails($student_id) {
    $query = "SELECT s.*, d.department_name, ay.academic_year, sess.session_name
              FROM Students s
              JOIN Departments d ON s.department_id = d.department_id
              JOIN AcademicYears ay ON s.academic_year_id = ay.academic_year_id
              JOIN Sessions sess ON s.session_id = sess.session_id
              WHERE s.student_id = ?";
    
    $student = fetchOne($query, [$student_id]);

    if (!$student) {
        return "Student not found.";
    }

    $html = "<h3>{$student['first_name']} {$student['last_name']} ({$student['matriculation_number']})</h3>";
    $html .= "<p><strong>Department:</strong> {$student['department_name']}</p>";
    $html .= "<p><strong>Academic Year:</strong> {$student['academic_year']}</p>";
    $html .= "<p><strong>Session:</strong> {$student['session_name']}</p>";
    $html .= "<p><strong>Gender:</strong> {$student['gender']}</p>";
    $html .= "<p><strong>Class:</strong> {$student['class']}</p>";

    return $html;
}

// Function to get edit student form
function getEditStudentForm($student_id) {
    $query = "SELECT s.*, d.department_name, ay.academic_year, sess.session_name
              FROM Students s
              JOIN Departments d ON s.department_id = d.department_id
              JOIN AcademicYears ay ON s.academic_year_id = ay.academic_year_id
              JOIN Sessions sess ON s.session_id = sess.session_id
              WHERE s.student_id = ?";
    
    $student = fetchOne($query, [$student_id]);

    if (!$student) {
        return "Student not found.";
    }

    $departments = fetchAll("SELECT * FROM Departments ORDER BY department_name");
    $academic_years = fetchAll("SELECT * FROM AcademicYears ORDER BY academic_year DESC");
    $sessions = fetchAll("SELECT * FROM Sessions ORDER BY session_name");

    $html = "<form id='editStudentForm'>";
    $html .= "<input type='hidden' name='student_id' value='{$student['student_id']}'>";
    $html .= "<div class='mb-3'>";
    $html .= "<label for='first_name' class='form-label'>First Name</label>";
    $html .= "<input type='text' class='form-control' id='first_name' name='first_name' value='{$student['first_name']}' required>";
    $html .= "</div>";
    $html .= "<div class='mb-3'>";
    $html .= "<label for='last_name' class='form-label'>Last Name</label>";
    $html .= "<input type='text' class='form-control' id='last_name' name='last_name' value='{$student['last_name']}' required>";
    $html .= "</div>";
    $html .= "<div class='mb-3'>";
    $html .= "<label for='matriculation_number' class='form-label'>Matriculation Number</label>";
    $html .= "<input type='text' class='form-control' id='matriculation_number' name='matriculation_number' value='{$student['matriculation_number']}' required>";
    $html .= "</div>";
    $html .= "<div class='mb-3'>";
    $html .= "<label for='department_id' class='form-label'>Department</label>";
    $html .= "<select class='form-select' id='department_id' name='department_id' required>";
    foreach ($departments as $dept) {
        $selected = ($dept['department_id'] == $student['department_id']) ? 'selected' : '';
        $html .= "<option value='{$dept['department_id']}' {$selected}>{$dept['department_name']}</option>";
    }
    $html .= "</select>";
    $html .= "</div>";
    $html .= "<div class='mb-3'>";
    $html .= "<label for='gender' class='form-label'>Gender</label>";
    $html .= "<select class='form-select' id='gender' name='gender' required>";
    $genders = ['Male', 'Female', 'Other'];
    foreach ($genders as $gender) {
        $selected = ($gender == $student['gender']) ? 'selected' : '';
        $html .= "<option value='{$gender}' {$selected}>{$gender}</option>";
    }
    $html .= "</select>";
    $html .= "</div>";
    $html .= "<div class='mb-3'>";
    $html .= "<label for='class' class='form-label'>Class</label>";
    $html .= "<select class='form-select' id='class' name='class' required>";
    $classes = ['ND1', 'ND2', 'HND1', 'HND2'];
    foreach ($classes as $class) {
        $selected = ($class == $student['class']) ? 'selected' : '';
        $html .= "<option value='{$class}' {$selected}>{$class}</option>";
    }
    $html .= "</select>";
    $html .= "</div>";
    $html .= "<div class='mb-3'>";
    $html .= "<label for='academic_year_id' class='form-label'>Academic Year</label>";
    $html .= "<select class='form-select' id='academic_year_id' name='academic_year_id' required>";
    foreach ($academic_years as $year) {
        $selected = ($year['academic_year_id'] == $student['academic_year_id']) ? 'selected' : '';
        $html .= "<option value='{$year['academic_year_id']}' {$selected}>{$year['academic_year']}</option>";
    }
    $html .= "</select>";
    $html .= "</div>";
    $html .= "<div class='mb-3'>";
    $html .= "<label for='session_id' class='form-label'>Session</label>";
    $html .= "<select class='form-select' id='session_id' name='session_id' required>";
    foreach ($sessions as $session) {
        $selected = ($session['session_id'] == $student['session_id']) ? 'selected' : '';
        $html .= "<option value='{$session['session_id']}' {$selected}>{$session['session_name']}</option>";
    }
    $html .= "</select>";
    $html .= "</div>";
    $html .= "<button type='submit' class='btn btn-primary'>Update Student</button>";
    $html .= "</form>";

    return $html;
}

// Function to update student information
function updateStudent($data) {
    global $conn;

    try {
        $conn->begin_transaction();

        $updateQuery = "UPDATE Students SET 
                        first_name = ?, 
                        last_name = ?, 
                        matriculation_number = ?, 
                        department_id = ?, 
                        gender = ?, 
                        class = ?, 
                        academic_year_id = ?, 
                        session_id = ? 
                        WHERE student_id = ?";

        $stmt = $conn->prepare($updateQuery);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . $conn->error);
        }

        $stmt->bind_param("ssssssiis", 
            $data['first_name'], 
            $data['last_name'], 
            $data['matriculation_number'], 
            $data['department_id'], 
            $data['gender'], 
            $data['class'], 
            $data['academic_year_id'], 
            $data['session_id'], 
            $data['student_id']
        );

        if (!$stmt->execute()) {
            throw new Exception("Error executing statement: " . $stmt->error);
        }

        $conn->commit();
        return ['success' => true, 'message' => 'Student information updated successfully.'];
    } catch (Exception $e) {
        $conn->rollback();
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

// Function to delete student
function deleteStudent($student_id) {
    global $conn;

    try {
        $conn->begin_transaction();

        // Delete related records in Results table
        $deleteResultsQuery = "DELETE FROM Results WHERE student_id = ?";
        $stmt = $conn->prepare($deleteResultsQuery);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . $conn->error);
        }
        $stmt->bind_param("i", $student_id);
        if (!$stmt->execute()) {
            throw new Exception("Error executing statement: " . $stmt->error);
        }

        // Delete related records in StudentCourses table
        $deleteStudentCoursesQuery = "DELETE FROM StudentCourses WHERE student_id = ?";
        $stmt = $conn->prepare($deleteStudentCoursesQuery);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . $conn->error);
        }
        $stmt->bind_param("i", $student_id);
        if (!$stmt->execute()) {
            throw new Exception("Error executing statement: " . $stmt->error);
        }

        // Delete related records in StudentOverallResults table
        $deleteOverallResultsQuery = "DELETE FROM StudentOverallResults WHERE student_id = ?";
        $stmt = $conn->prepare($deleteOverallResultsQuery);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . $conn->error);
        }
        $stmt->bind_param("i", $student_id);
        if (!$stmt->execute()) {
            throw new Exception("Error executing statement: " . $stmt->error);
        }

        // Delete the student record
        $deleteStudentQuery = "DELETE FROM Students WHERE student_id = ?";
        $stmt = $conn->prepare($deleteStudentQuery);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . $conn->error);
        }
        $stmt->bind_param("i", $student_id);
        if (!$stmt->execute()) {
            throw new Exception("Error executing statement: " . $stmt->error);
        }

        $conn->commit();
        return ['success' => true, 'message' => 'Student deleted successfully.'];
    } catch (Exception $e) {
        $conn->rollback();
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['action']) {
        case 'view_student':
            if (isset($_GET['student_id'])) {
                $result = getStudentDetails($_GET['student_id']);
                echo json_encode([
                    'success' => true,
                    'html' => $result
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Missing student_id parameter']);
            }
            exit;
        
        case 'get_edit_form':
            if (isset($_GET['student_id'])) {
                $form = getEditStudentForm($_GET['student_id']);
                echo json_encode([
                    'success' => true,
                    'html' => $form
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Missing student_id parameter']);
            }
            exit;
        
        case 'update_student':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $result = updateStudent($_POST);
                echo json_encode($result);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            }
            exit;

        case 'delete_student':
            if (isset($_GET['student_id'])) {
                $result = deleteStudent($_GET['student_id']);
                echo json_encode($result);
            } else {
                echo json_encode(['success' => false, 'message' => 'Missing student_id parameter']);
            }
            exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student List - LUFEM School</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/dashboard.css">
    <style>
        .table-responsive {
            overflow-x: auto;
        }
        .action-buttons .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <?php include 'sidebar.php'; ?>

        <div id="content">
            <?php include 'navbar.php'; ?>

            <div class="container-fluid mt-4">
                <h2 class="mb-4">Student List</h2>

                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <form id="filterForm" method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label for="department" class="form-label">Department</label>
                                <select class="form-select" id="department" name="department_id">
                                    <option value="">All Departments</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?php echo $dept['department_id']; ?>" <?php echo $filters['department_id'] == $dept['department_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($dept['department_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="academicYear" class="form-label">Academic Year</label>
                                <select class="form-select" id="academicYear" name="academic_year_id">
                                    <option value="">All Years</option>
                                    <?php foreach ($academic_years as $year): ?>
                                        <option value="<?php echo $year['academic_year_id']; ?>" <?php echo $filters['academic_year_id'] == $year['academic_year_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($year['academic_year']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="session" class="form-label">Session</label>
                                <select class="form-select" id="session" name="session_id">
                                    <option value="">All Sessions</option>
                                    <?php foreach ($sessions as $session): ?>
                                        <option value="<?php echo $session['session_id']; ?>" <?php echo $filters['session_id'] == $session['session_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($session['session_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" class="form-control" id="search" name="search" value="<?php echo htmlspecialchars($filters['search']); ?>" placeholder="Name or Matric No.">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Apply Filters</button>
                                <a href="StudentList.php" class="btn btn-secondary">Clear Filters</a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Matric No.</th>
                                        <th>Department</th>
                                        <th>Class</th>
                                        <th>Academic Year</th>
                                        <th>Session</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $student): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($student['last_name'] . ', ' . $student['first_name']); ?></td>
                                            <td><?php echo htmlspecialchars($student['matriculation_number']); ?></td>
                                            <td><?php echo htmlspecialchars($student['department_name']); ?></td>
                                            <td><?php echo htmlspecialchars($student['class']); ?></td>
                                            <td><?php echo htmlspecialchars($student['academic_year']); ?></td>
                                            <td><?php echo htmlspecialchars($student['session_name']); ?></td>
                                            <td class="action-buttons">
                                                <button class="btn btn-sm btn-primary view-student" data-student-id="<?php echo $student['student_id']; ?>">View</button>
                                                <button class="btn btn-sm btn-secondary edit-student" data-student-id="<?php echo $student['student_id']; ?>">Edit</button>
                                                <button class="btn btn-sm btn-danger delete-student" data-student-id="<?php echo $student['student_id']; ?>">Delete</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if ($totalPages > 1): ?>
                            <nav aria-label="Page navigation" class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&<?php echo http_build_query(array_filter($filters)); ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Student Modal -->
    <div class="modal fade" id="viewStudentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Student Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="viewStudentContent">
                    <!-- Student details will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Student Modal -->
    <div class="modal fade" id="editStudentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="editStudentContent">
                    <!-- Edit form will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this student? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../js/dashboard.js"></script>
    <script>
    $(document).ready(function() {
        // View Student
        $('.view-student').on('click', function() {
            var studentId = $(this).data('student-id');
            $.ajax({
                url: 'student_list.php',
                type: 'GET',
                data: { 
                    action: 'view_student',
                    student_id: studentId
                },
                success: function(response) {
                    if (response.success) {
                        $('#viewStudentContent').html(response.html);
                        $('#viewStudentModal').modal('show');
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    alert('Error fetching student details: ' + error);
                }
            });
        });

        // Edit Student
        $('.edit-student').on('click', function() {
            var studentId = $(this).data('student-id');
            $.ajax({
                url: 'student_list.php',
                type: 'GET',
                data: { 
                    action: 'get_edit_form',
                    student_id: studentId
                },
                success: function(response) {
                    if (response.success) {
                        $('#editStudentContent').html(response.html);
                        $('#editStudentModal').modal('show');
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    alert('Error fetching edit form: ' + error);
                }
            });
        });

        // Handle edit student form submission
        $(document).on('submit', '#editStudentForm', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();
            $.ajax({
                url: 'student_list.php?action=update_student',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        $('#editStudentModal').modal('hide');
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    alert('Error updating student: ' + error);
                }
            });
        });

        // Delete Student
        $('.delete-student').on('click', function() {
            var studentId = $(this).data('student-id');
            $('#confirmDelete').data('student-id', studentId);
            $('#deleteConfirmationModal').modal('show');
        });

        $('#confirmDelete').on('click', function() {
            var studentId = $(this).data('student-id');
            $.ajax({
                url: 'student_list.php',
                type: 'GET',
                data: { 
                    action: 'delete_student',
                    student_id: studentId
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    alert('Error deleting student: ' + error);
                },
                complete: function() {
                    $('#deleteConfirmationModal').modal('hide');
                }
            });
        });
    });
    </script>
</body>
</html>