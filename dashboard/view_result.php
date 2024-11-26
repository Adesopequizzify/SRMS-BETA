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
$query = "SELECT s.student_id, s.first_name, s.last_name, s.matriculation_number, 
               d.department_name, ay.academic_year, sess.session_name,
               sor.cumulative_gpa, sor.total_credits_earned, sor.overall_grade_letter, sor.final_remark
        FROM Students s
        JOIN Departments d ON s.department_id = d.department_id
        LEFT JOIN StudentOverallResults sor ON s.student_id = sor.student_id
        LEFT JOIN AcademicYears ay ON sor.academic_year_id = ay.academic_year_id
        LEFT JOIN Sessions sess ON sor.session_id = sess.session_id
        WHERE 1=1";

$params = [];

if (!empty($filters['department_id'])) {
    $query .= " AND s.department_id = ?";
    $params[] = $filters['department_id'];
}

if (!empty($filters['academic_year_id'])) {
    $query .= " AND sor.academic_year_id = ?";
    $params[] = $filters['academic_year_id'];
}

if (!empty($filters['session_id'])) {
    $query .= " AND sor.session_id = ?";
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
             LEFT JOIN StudentOverallResults sor ON s.student_id = sor.student_id
             WHERE 1=1";

if (!empty($filters['department_id'])) {
    $countQuery .= " AND s.department_id = ?";
}

if (!empty($filters['academic_year_id'])) {
    $countQuery .= " AND sor.academic_year_id = ?";
}

if (!empty($filters['session_id'])) {
    $countQuery .= " AND sor.session_id = ?";
}

if (!empty($filters['search'])) {
    $countQuery .= " AND (s.first_name LIKE ? OR s.last_name LIKE ? OR s.matriculation_number LIKE ?)";
}

$totalResults = fetchOne($countQuery, array_slice($params, 0, -2))['total'];
$totalPages = ceil($totalResults / $limit);
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
                                        <th>GPA</th>
                                        <th>Credits Earned</th>
                                        <th>Grade</th>
                                        <th>Final Remark</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($results as $result): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($result['last_name'] . ', ' . $result['first_name']); ?></td>
                                            <td><?php echo htmlspecialchars($result['matriculation_number']); ?></td>
                                            <td><?php echo htmlspecialchars($result['department_name']); ?></td>
                                            <td><?php echo htmlspecialchars($result['academic_year'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($result['session_name'] ?? 'N/A'); ?></td>
                                            <td><?php echo $result['cumulative_gpa'] ? number_format($result['cumulative_gpa'], 2) : 'N/A'; ?></td>
                                            <td><?php echo $result['total_credits_earned'] ?? 'N/A'; ?></td>
                                            <td><?php echo htmlspecialchars($result['overall_grade_letter'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($result['final_remark'] ?? 'N/A'); ?></td>
                                            <td class="action-buttons">
                                                <button class="btn btn-sm btn-primary view-result" data-student-id="<?php echo $result['student_id']; ?>">View</button>
                                                <button class="btn btn-sm btn-secondary edit-result" data-student-id="<?php echo $result['student_id']; ?>">Edit</button>
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
    <script src="../js/view-result.js"></script>
</body>
</html>