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
$query = "SELECT DISTINCT s.student_id, s.first_name, s.last_name, s.matriculation_number, 
               d.department_name, ay.academic_year, sess.session_name
        FROM Students s
        JOIN Departments d ON s.department_id = d.department_id
        JOIN Results r ON s.student_id = r.student_id
        JOIN AcademicYears ay ON r.academic_year_id = ay.academic_year_id
        JOIN Sessions sess ON r.session_id = sess.session_id
        WHERE 1=1";

$params = [];

if (!empty($filters['department_id'])) {
    $query .= " AND s.department_id = ?";
    $params[] = $filters['department_id'];
}

if (!empty($filters['academic_year_id'])) {
    $query .= " AND r.academic_year_id = ?";
    $params[] = $filters['academic_year_id'];
}

if (!empty($filters['session_id'])) {
    $query .= " AND r.session_id = ?";
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
$results = fetchAll($query, $params);

// Count total results for pagination
$countQuery = "SELECT COUNT(DISTINCT s.student_id) as total FROM Students s
             JOIN Results r ON s.student_id = r.student_id
             WHERE 1=1";

if (!empty($filters['department_id'])) {
    $countQuery .= " AND s.department_id = ?";
}

if (!empty($filters['academic_year_id'])) {
    $countQuery .= " AND r.academic_year_id = ?";
}

if (!empty($filters['session_id'])) {
    $countQuery .= " AND r.session_id = ?";
}

if (!empty($filters['search'])) {
    $countQuery .= " AND (s.first_name LIKE ? OR s.last_name LIKE ? OR s.matriculation_number LIKE ?)";
}

$totalResults = fetchOne($countQuery, array_slice($params, 0, -2))['total'];
$totalPages = ceil($totalResults / $limit);

// Function to get student result details
function getStudentResultDetails($student_id, $academic_year, $session) {
    global $conn;
    
    // Fetch student details
    $studentQuery = "SELECT s.*, d.department_name 
                     FROM Students s
                     JOIN Departments d ON s.department_id = d.department_id
                     WHERE s.student_id = ?";
    $student = fetchOne($studentQuery, [$student_id]);

    if (!$student) {
        return "Student not found.";
    }

    // Fetch individual course results
    $resultsQuery = "SELECT r.*, c.course_code, c.course_name, g.grade_letter
                     FROM Results r
                     JOIN Courses c ON r.course_id = c.course_id
                     JOIN Grades g ON r.grade_id = g.grade_id
                     JOIN AcademicYears ay ON r.academic_year_id = ay.academic_year_id
                     JOIN Sessions s ON r.session_id = s.session_id
                     WHERE r.student_id = ? AND ay.academic_year = ? AND s.session_name = ?
                     ORDER BY c.course_code";
    $results = fetchAll($resultsQuery, [$student_id, $academic_year, $session]);

    // Calculate GPA
    $totalPoints = 0;
    $totalCourses = count($results);
    foreach ($results as $result) {
        $totalPoints += getGradePoint($result['grade_letter']);
    }
    $gpa = $totalCourses > 0 ? $totalPoints / $totalCourses : 0;

    // Build HTML response
    $html = "<h3>{$student['first_name']} {$student['last_name']} ({$student['matriculation_number']})</h3>";
    $html .= "<p><strong>Department:</strong> {$student['department_name']}</p>";
    $html .= "<p><strong>Academic Year:</strong> {$academic_year}</p>";
    $html .= "<p><strong>Session:</strong> {$session}</p>";
    $html .= "<p><strong>GPA:</strong> " . number_format($gpa, 2) . "</p>";

    $html .= "<table class='table table-striped'>";
    $html .= "<thead><tr><th>Course Code</th><th>Course Name</th><th>Score</th><th>Grade</th></tr></thead>";
    $html .= "<tbody>";

    foreach ($results as $result) {
        $html .= "<tr>";
        $html .= "<td>{$result['course_code']}</td>";
        $html .= "<td>{$result['course_name']}</td>";
        $html .= "<td>{$result['score']}</td>";
        $html .= "<td>{$result['grade_letter']}</td>";
        $html .= "</tr>";
    }

    $html .= "</tbody></table>";

    return $html;
}

// Function to get edit result form
function getEditResultForm($student_id, $academic_year, $session) {
    global $conn;
    
    // Fetch student details
    $studentQuery = "SELECT s.*, d.department_name 
                     FROM Students s
                     JOIN Departments d ON s.department_id = d.department_id
                     WHERE s.student_id = ?";
    $student = fetchOne($studentQuery, [$student_id]);

    if (!$student) {
        return "Student not found.";
    }

    // Fetch individual course results
    $resultsQuery = "SELECT r.*, c.course_code, c.course_name, g.grade_letter
                     FROM Results r
                     JOIN Courses c ON r.course_id = c.course_id
                     JOIN Grades g ON r.grade_id = g.grade_id
                     JOIN AcademicYears ay ON r.academic_year_id = ay.academic_year_id
                     JOIN Sessions s ON r.session_id = s.session_id
                     WHERE r.student_id = ? AND ay.academic_year = ? AND s.session_name = ?
                     ORDER BY c.course_code";
    $results = fetchAll($resultsQuery, [$student_id, $academic_year, $session]);

    // Fetch all possible grades
    $gradesQuery = "SELECT * FROM Grades ORDER BY grade_letter";
    $grades = fetchAll($gradesQuery);

    // Build HTML form
    $html = "<form id='editResultForm'>";
    $html .= "<input type='hidden' name='student_id' value='{$student_id}'>";
    $html .= "<input type='hidden' name='academic_year' value='{$academic_year}'>";
    $html .= "<input type='hidden' name='session' value='{$session}'>";
    $html .= "<h3>{$student['first_name']} {$student['last_name']} ({$student['matriculation_number']})</h3>";
    $html .= "<p><strong>Department:</strong> {$student['department_name']}</p>";
    $html .= "<p><strong>Academic Year:</strong> {$academic_year}</p>";
    $html .= "<p><strong>Session:</strong> {$session}</p>";

    $html .= "<table class='table table-striped'>";
    $html .= "<thead><tr><th>Course Code</th><th>Course Name</th><th>Score</th><th>Grade</th></tr></thead>";
    $html .= "<tbody>";

    foreach ($results as $result) {
        $html .= "<tr>";
        $html .= "<td>{$result['course_code']}</td>";
        $html .= "<td>{$result['course_name']}</td>";
        $html .= "<td><input type='number' name='scores[{$result['result_id']}]' value='{$result['score']}' min='0' max='100' step='0.01' required class='form-control score-input'></td>";
        $html .= "<td><select name='grades[{$result['result_id']}]' required class='form-select grade-select'>";
        foreach ($grades as $grade) {
            $selected = ($grade['grade_letter'] == $result['grade_letter']) ? 'selected' : '';
            $html .= "<option value='{$grade['grade_id']}' {$selected}>{$grade['grade_letter']}</option>";
        }
        $html .= "</select></td>";
        $html .= "</tr>";
    }

    $html .= "</tbody></table>";
    $html .= "<button type='submit' class='btn btn-primary'>Update Results</button>";
    $html .= "</form>";

    return $html;
}

// Function to update student result
function updateStudentResult($data) {
    global $conn;

    error_log("Starting updateStudentResult with data: " . json_encode($data));

    try {
        $conn->begin_transaction();

        if (!isset($data['scores']) || !isset($data['grades']) || !isset($data['student_id'])) {
            throw new Exception("Missing required data for update");
        }

        $academic_year_id = null;
        $session_id = null;

        foreach ($data['scores'] as $result_id => $score) {
            if (!isset($data['grades'][$result_id])) {
                throw new Exception("Missing grade for result ID: $result_id");
            }

            $grade_id = $data['grades'][$result_id];
            $updateQuery = "UPDATE Results SET score = ?, grade_id = ? WHERE result_id = ?";
            $stmt = $conn->prepare($updateQuery);
            if (!$stmt) {
                throw new Exception("Error preparing statement: " . $conn->error);
            }
            $stmt->bind_param("dii", $score, $grade_id, $result_id);
            if (!$stmt->execute()) {
                throw new Exception("Error executing statement: " . $stmt->error);
            }

            // Get academic_year_id and session_id from the first result
            if ($academic_year_id === null || $session_id === null) {
                $infoQuery = "SELECT academic_year_id, session_id FROM Results WHERE result_id = ?";
                $infoStmt = $conn->prepare($infoQuery);
                $infoStmt->bind_param("i", $result_id);
                $infoStmt->execute();
                $infoResult = $infoStmt->get_result();
                $infoRow = $infoResult->fetch_assoc();
                $academic_year_id = $infoRow['academic_year_id'];
                $session_id = $infoRow['session_id'];
                $infoStmt->close();
            }

            $stmt->close();
        }

        error_log("All results updated successfully. Proceeding to recalculation.");

        // Trigger recalculation of overall results
        require_once 'calculate_overall_results.php';
        $recalculationResult = calculateAndUpdateOverallResults($data['student_id'], $academic_year_id, $session_id);

        if ($recalculationResult['success']) {
            $conn->commit();
            error_log("Transaction committed successfully.");
            return ['success' => true, 'message' => 'Results updated successfully.'];
        } else {
            throw new Exception($recalculationResult['message']);
        }
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error in updateStudentResult: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}
// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['action']) {
        case 'view_result':
            if (isset($_GET['student_id']) && isset($_GET['academic_year']) && isset($_GET['session'])) {
                echo json_encode([
                    'success' => true,
                    'html' => getStudentResultDetails($_GET['student_id'], $_GET['academic_year'], $_GET['session'])
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Missing parameters']);
            }
            exit;
        
        case 'get_edit_form':
            if (isset($_GET['student_id']) && isset($_GET['academic_year']) && isset($_GET['session'])) {
                echo json_encode([
                    'success' => true,
                    'html' => getEditResultForm($_GET['student_id'], $_GET['academic_year'], $_GET['session'])
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Missing parameters']);
            }
            exit;
        
        case 'update_result':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                error_log("Received update_result request: " . json_encode($_POST));
                $result = updateStudentResult($_POST);
                echo json_encode($result);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            }
            exit;
    }
}

function getGradePoint($gradeLetter) {
    switch ($gradeLetter) {
        case 'A': return 4.0;
        case 'B': return 3.0;
        case 'C': return 2.0;
        case 'D': return 1.0;
        default: return 0.0;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Results - LUFEM School</title>
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
                <h2 class="mb-4">View Results</h2>

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
                                <a href="view_result.php" class="btn btn-secondary">Clear Filters</a>
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
                                        <th>Academic Year</th>
                                        <th>Session</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($results as $result): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($result['last_name'] . ', ' . $result['first_name']); ?></td>
                                            <td><?php echo htmlspecialchars($result['matriculation_number']); ?></td>
                                            <td><?php echo htmlspecialchars($result['department_name']); ?></td>
                                            <td><?php echo htmlspecialchars($result['academic_year']); ?></td>
                                            <td><?php echo htmlspecialchars($result['session_name']); ?></td>
                                            <td class="action-buttons">
                                                <button class="btn btn-sm btn-primary view-result" data-student-id="<?php echo $result['student_id']; ?>" data-academic-year="<?php echo $result['academic_year']; ?>" data-session="<?php echo $result['session_name']; ?>">View</button>
                                                <button class="btn btn-sm btn-secondary edit-result" data-student-id="<?php echo $result['student_id']; ?>" data-academic-year="<?php echo $result['academic_year']; ?>" data-session="<?php echo $result['session_name']; ?>">Edit</button>
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

    <!-- View Result Modal -->
    <div class="modal fade" id="viewResultModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Student Result Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="viewResultContent">
                    <!-- Result details will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Result Modal -->
    <div class="modal fade" id="editResultModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Student Result</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="editResultContent">
                    <!-- Edit form will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../js/dashboard.js"></script>
    <script>
        $(document).ready(function() {
            // View Result
            $('.view-result').on('click', function() {
                var studentId = $(this).data('student-id');
                var academicYear = $(this).data('academic-year');
                var session = $(this).data('session');
                $.ajax({
                    url: 'view_result.php',
                    type: 'GET',
                    data: { 
                        action: 'view_result',
                        student_id: studentId,
                        academic_year: academicYear,
                        session: session
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#viewResultContent').html(response.html);
                            $('#viewResultModal').modal('show');
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Error fetching result details.');
                    }
                });
            });

            // Edit Result
            $('.edit-result').on('click', function() {
                var studentId = $(this).data('student-id');
                var academicYear = $(this).data('academic-year');
                var session = $(this).data('session');
                $.ajax({
                    url: 'view_result.php',
                    type: 'GET',
                    data: { 
                        action: 'get_edit_form',
                        student_id: studentId,
                        academic_year: academicYear,
                        session: session
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#editResultContent').html(response.html);
                            $('#editResultModal').modal('show');
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Error fetching edit form.');
                    }
                });
            });

            // Handle edit result form submission
  // Handle edit result form submission
$(document).ready(function() {
  $(document).on('submit', '#editResultForm', function(e) {
    e.preventDefault();
    var formData = $(this).serialize();
    var submitButton = $(this).find('button[type="submit"]');

    // Disable submit button and show loading state
    submitButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...');

    $.ajax({
      url: 'view_result.php?action=update_result',
      type: 'POST',
      data: formData,
      dataType: 'json',
      success: function(response) {
        console.log('Server response:', response);
        if (response && response.success) {
          console.log('Update successful');
          showAlert('success', 'Result updated successfully');
          $('#editResultModal').modal('hide');
          // Refresh the page to show updated results
          setTimeout(function() {
            location.reload();
          }, 1500);
        } else {
          console.error('Update failed:', response);
          showAlert('danger', 'Error: ' + (response.message || 'Unknown error occurred'));
        }
      },
      error: function(xhr, status, error) {
        console.error('AJAX Error:', status, error);
        console.error('Response:', xhr.responseText);
        try {
          var errorResponse = JSON.parse(xhr.responseText);
          console.error('Parsed error response:', errorResponse);
          showAlert('danger', 'Error: ' + (errorResponse.message || 'Unknown error occurred'));
        } catch (e) {
          console.error('Could not parse error response');
          showAlert('danger', 'An error occurred while updating the result.');
        }
      },
      complete: function() {
        // Re-enable submit button and restore original text
        submitButton.prop('disabled', false).text('Update Results');
      }
    });
  });

  function showAlert(type, message) {
    var alertHtml = '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
      message +
      '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
      '</div>';

    // Remove any existing alerts
    $('.alert').remove();

    // Add the new alert before the form
    $('#editResultForm').before(alertHtml);

    // Automatically dismiss the alert after 5 seconds
    setTimeout(function() {
      $('.alert').alert('close');
    }, 5000);
  }
});  // Update grade when score changes
            $(document).on('input', '.score-input', function() {
                var score = parseFloat($(this).val());
                var gradeSelect = $(this).closest('tr').find('.grade-select');
                
                if (!isNaN(score)) {
                    var grade = calculateGrade(score);
                    gradeSelect.val(grade);
                }
            });

            function calculateGrade(score) {
  if (score >= 70) return 'A';
  if (score >= 60) return 'B';
  if (score >= 50) return 'C';
  if (score >= 45) return 'D';
  if (score >= 40) return 'E';
  return 'F';
}
});
    </script>
</body>
</html>