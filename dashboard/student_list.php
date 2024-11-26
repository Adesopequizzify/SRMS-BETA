<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit();
}

// Fetch all departments for the filter and form
$departments = fetchAll("SELECT * FROM Departments ORDER BY department_name");

// Initialize filter variables
$filters = [
    'department_id' => $_GET['department_id'] ?? '',
    'class' => $_GET['class'] ?? '',
    'search' => $_GET['search'] ?? '',
];

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build the query
$query = "SELECT s.student_id, s.matriculation_number, s.first_name, s.last_name, 
                 s.gender, s.class, d.department_name, d.department_id
          FROM Students s
          JOIN Departments d ON s.department_id = d.department_id
          WHERE 1=1";

$params = [];

if (!empty($filters['department_id'])) {
    $query .= " AND s.department_id = ?";
    $params[] = $filters['department_id'];
}

if (!empty($filters['class'])) {
    $query .= " AND s.class = ?";
    $params[] = $filters['class'];
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
$countQuery = "SELECT COUNT(*) as total FROM Students s WHERE 1=1";

if (!empty($filters['department_id'])) {
    $countQuery .= " AND s.department_id = ?";
}

if (!empty($filters['class'])) {
    $countQuery .= " AND s.class = ?";
}

if (!empty($filters['search'])) {
    $countQuery .= " AND (s.first_name LIKE ? OR s.last_name LIKE ? OR s.matriculation_number LIKE ?)";
}

$totalStudents = fetchOne($countQuery, array_slice($params, 0, -2))['total'];
$totalPages = ceil($totalStudents / $limit);
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
                                <label for="class" class="form-label">Class</label>
                                <select class="form-select" id="class" name="class">
                                    <option value="">All Classes</option>
                                    <option value="ND1" <?php echo $filters['class'] == 'ND1' ? 'selected' : ''; ?>>ND1</option>
                                    <option value="ND2" <?php echo $filters['class'] == 'ND2' ? 'selected' : ''; ?>>ND2</option>
                                    <option value="HND1" <?php echo $filters['class'] == 'HND1' ? 'selected' : ''; ?>>HND1</option>
                                    <option value="HND2" <?php echo $filters['class'] == 'HND2' ? 'selected' : ''; ?>>HND2</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" class="form-control" id="search" name="search" value="<?php echo htmlspecialchars($filters['search']); ?>" placeholder="Name or Matric No.">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
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
                                        <th>Matric No.</th>
                                        <th>Name</th>
                                        <th>Department</th>
                                        <th>Class</th>
                                        <th>Gender</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $student): ?>
                                        <tr data-student-id="<?php echo $student['student_id']; ?>"
                                            data-matric-number="<?php echo htmlspecialchars($student['matriculation_number']); ?>"
                                            data-first-name="<?php echo htmlspecialchars($student['first_name']); ?>"
                                            data-last-name="<?php echo htmlspecialchars($student['last_name']); ?>"
                                            data-department-id="<?php echo $student['department_id']; ?>"
                                            data-department-name="<?php echo htmlspecialchars($student['department_name']); ?>"
                                            data-class="<?php echo htmlspecialchars($student['class']); ?>"
                                            data-gender="<?php echo htmlspecialchars($student['gender']); ?>">
                                            <td><?php echo htmlspecialchars($student['matriculation_number']); ?></td>
                                            <td><?php echo htmlspecialchars($student['last_name'] . ', ' . $student['first_name']); ?></td>
                                            <td><?php echo htmlspecialchars($student['department_name']); ?></td>
                                            <td><?php echo htmlspecialchars($student['class']); ?></td>
                                            <td><?php echo htmlspecialchars($student['gender']); ?></td>
                                            <td class="action-buttons">
                                                <button class="btn btn-sm btn-primary view-student" data-bs-toggle="modal" data-bs-target="#studentModal">View</button>
                                                <button class="btn btn-sm btn-secondary edit-student" data-bs-toggle="modal" data-bs-target="#studentModal">Edit</button>
                                                <button class="btn btn-sm btn-danger delete-student" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal">Delete</button>
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

    <!-- View/Edit Student Modal -->
    <div class="modal fade" id="studentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="studentModalTitle">Student Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="studentForm">
                        <input type="hidden" id="studentId" name="student_id">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="firstName" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="firstName" name="first_name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="lastName" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="lastName" name="last_name" readonly>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="gender" class="form-label">Gender</label>
                                <select class="form-select" id="gender" name="gender" required>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="matricNumber" class="form-label">Matriculation Number</label>
                                <input type="text" class="form-control" id="matricNumber" name="matriculation_number" readonly>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="department" class="form-label">Department</label>
                                <select class="form-select" id="department" name="department_id" required>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?php echo $dept['department_id']; ?>"><?php echo htmlspecialchars($dept['department_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="class" class="form-label">Class</label>
                                <select class="form-select" id="class" name="class" required>
                                    <option value="ND1">ND1</option>
                                    <option value="ND2">ND2</option>
                                    <option value="HND1">HND1</option>
                                    <option value="HND2">HND2</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveChanges">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this student? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/dashboard.js"></script>
    <script src="../js/student-list.js"></script>
</body>
</html>