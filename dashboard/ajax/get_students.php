<?php
session_start();
require_once '../../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Prepare the base query
$query = "SELECT s.student_id, s.matriculation_number, s.first_name, s.last_name, 
               d.department_name, s.class, ay.academic_year
        FROM Students s
        JOIN Departments d ON s.department_id = d.department_id
        JOIN AcademicYears ay ON s.academic_year_id = ay.academic_year_id";

$countQuery = "SELECT COUNT(*) as total FROM Students s";

// Apply filters
$where = [];
$params = [];

if (!empty($_POST['class'])) {
    $where[] = "s.class = ?";
    $params[] = $_POST['class'];
}

if (!empty($_POST['department'])) {
    $where[] = "s.department_id = ?";
    $params[] = $_POST['department'];
}

if (!empty($_POST['academicYear'])) {
    $where[] = "s.academic_year_id = ?";
    $params[] = $_POST['academicYear'];
}

if (!empty($_POST['search']['value'])) {
    $searchValue = '%' . $_POST['search']['value'] . '%';
    $where[] = "(s.matriculation_number LIKE ? OR s.first_name LIKE ? OR s.last_name LIKE ?)";
    $params = array_merge($params, [$searchValue, $searchValue, $searchValue]);
}

if (!empty($where)) {
    $whereClause = " WHERE " . implode(" AND
", $where);
    $query .= $whereClause;
    $countQuery .= $whereClause;
}

// Ordering
$orderColumn = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : 1;
$orderDir = isset($_POST['order'][0]['dir']) ? $_POST['order'][0]['dir'] : 'asc';
$columns = ['matriculation_number', 'first_name', 'last_name', 'department_name', 'class', 'academic_year'];
$orderBy = " ORDER BY " . $columns[$orderColumn] . " " . $orderDir;

$query .= $orderBy;

// Pagination
$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$query .= " LIMIT ?, ?";
$params[] = $start;
$params[] = $length;

// Execute queries
$stmt = $conn->prepare($countQuery);
if (!empty($params)) {
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
}
$stmt->execute();
$countResult = $stmt->get_result()->fetch_assoc();
$totalRecords = $countResult['total'];

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
}

try {
    if (!$stmt->execute()) {
        throw new Exception("Query failed: " . $stmt->error);
    }
    $result = $stmt->get_result();
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    echo json_encode([
        "draw" => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
        "recordsTotal" => $totalRecords,
        "recordsFiltered" => $totalRecords,
        "data" => $data
    ]);
} catch (Exception $e) {
    echo json_encode([
        'error' => true,
        'message' => 'Failed to fetch students: ' . $e->getMessage()
    ]);
}

