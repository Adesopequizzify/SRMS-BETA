<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit();
}

// Fetch departments and academic years for filters
$departments = fetchAll("SELECT * FROM Departments ORDER BY department_name");
$academic_years = fetchAll("SELECT * FROM AcademicYears ORDER BY academic_year DESC");

// Base query
$query = "SELECT s.student_id, s.matriculation_number, s.first_name, s.last_name, 
                 d.department_name, s.class, ay.academic_year, s.gender, s.department_id, s.academic_year_id
          FROM Students s
          JOIN Departments d ON s.department_id = d.department_id
          JOIN AcademicYears ay ON s.academic_year_id = ay.academic_year_id";

$where = [];
$params = [];

// Apply filters if set
if (!empty($_GET['class'])) {
    $where[] = "s.class = ?";
    $params[] = $_GET['class'];
}
if (!empty($_GET['department'])) {
    $where[] = "s.department_id = ?";
    $params[] = $_GET['department'];
}
if (!empty($_GET['academicYear'])) {
    $where[] = "s.academic_year_id = ?";
    $params[] = $_GET['academicYear'];
}

if (!empty($where)) {
    $query .= " WHERE " . implode(" AND ", $where);
}

$query .= " ORDER BY s.last_name, s.first_name";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$students = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registered Students - LUFEM School</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
    <div class="wrapper">
        <?php include 'sidebar.php'; ?>

        <div id="content">
            <?php include 'navbar.php'; ?>

            <div class="container-fluid mt-4">
                <h2 class="mb-4">Registered Students</h2>

                <div id="alertPlaceholder"></div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <form id="filterForm" class="row mb-3">
                            <div class="col-md-3">
                                <select name="class" id="filterClass" class="form-select">
                                    <option value="">All Classes</option>
                                    <option value="ND1" <?php echo ($_GET['class'] ?? '') == 'ND1' ? 'selected' : ''; ?>>ND1</option>
                                    <option value="ND2" <?php echo ($_GET['class'] ?? '') == 'ND2' ? 'selected' : ''; ?>>ND2</option>
                                    <option value="HND1" <?php echo ($_GET['class'] ?? '') == 'HND1' ? 'selected' : ''; ?>>HND1</option>
                                    <option value="HND2" <?php echo ($_GET['class'] ?? '') == 'HND2' ? 'selected' : ''; ?>>HND2</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="department" id="filterDepartment" class="form-select">
                                    <option value="">All Departments</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?php echo $dept['department_id']; ?>" <?php echo ($_GET['department'] ?? '') == $dept['department_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($dept['department_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="academicYear" id="filterAcademicYear" class="form-select">
                                    <option value="">All Academic Years</option>
                                    <?php foreach ($academic_years as $year): ?>
                                        <option value="<?php echo $year['academic_year_id']; ?>" <?php echo ($_GET['academicYear'] ?? '') == $year['academic_year_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($year['academic_year']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary">Apply Filters</button>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table id="studentsTable" class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Matric Number</th>
                                        <th>First Name</th>
                                        <th>Last Name</th>
                                        <th>Department</th>
                                        <th>Class</th>
                                        <th>Academic Year</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $student): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($student['matriculation_number']); ?></td>
                                        <td><?php echo htmlspecialchars($student['first_name']); ?></td>
                                        <td><?php echo htmlspecialchars($student['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($student['department_name']); ?></td>
                                        <td><?php echo htmlspecialchars($student['class']); ?></td>
                                        <td><?php echo htmlspecialchars($student['academic_year']); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary edit-student" data-id="<?php echo $student['student_id']; ?>">Edit</button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Student Modal -->
    <div class="modal fade" id="editStudentModal" tabindex="-1" aria-labelledby="editStudentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editStudentModalLabel">Edit Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editStudentForm">
                        <input type="hidden" id="editStudentId" name="student_id">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="editFirstName" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="editFirstName" name="first_name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="editLastName" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="editLastName" name="last_name" readonly>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="editMatricNumber" class="form-label">Matric Number</label>
                                <input type="text" class="form-control" id="editMatricNumber" name="matriculation_number" readonly>
                            </div>
                            <div class="col-md-6">
                                <label for="editDepartment" class="form-label">Department</label>
                                <select class="form-select" id="editDepartment" name="department_id" required>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?php echo $dept['department_id']; ?>">
                                            <?php echo htmlspecialchars($dept['department_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="editGender" class="form-label">Gender</label>
                                <select class="form-select" id="editGender" name="gender" required>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="editClass" class="form-label">Class</label>
                                <select class="form-select" id="editClass" name="class" required>
                                    <option value="ND1">ND1</option>
                                    <option value="ND2">ND2</option>
                                    <option value="HND1">HND1</option>
                                    <option value="HND2">HND2</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="editAcademicYear" class="form-label">Academic Year</label>
                                <select class="form-select" id="editAcademicYear" name="academic_year_id" required>
                                    <?php foreach ($academic_years as $year): ?>
                                        <option value="<?php echo $year['academic_year_id']; ?>">
                                            <?php echo htmlspecialchars($year['academic_year']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveStudentChanges">Save changes</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="../js/dashboard.js"></script>
    <script src="../js/student-list.js"></script>
</body>
</html>