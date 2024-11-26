<?php
session_start();
require_once '../../db.php';

// Check if the student is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: ../../index.php');
    exit();
}

// Fetch student information
$student_id = $_SESSION['student_id'];
$stmt = $conn->prepare("SELECT s.*, d.department_name, ay.academic_year, sess.session_name 
                        FROM Students s
                        JOIN Departments d ON s.department_id = d.department_id
                        JOIN AcademicYears ay ON s.academic_year_id = ay.academic_year_id
                        JOIN Sessions sess ON s.session_id = sess.session_id
                        WHERE s.student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

// Handle form submission for password change
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (password_verify($current_password, $student['password'])) {
        if ($new_password === $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE Students SET password = ? WHERE student_id = ?");
            $update_stmt->bind_param("si", $hashed_password, $student_id);
            if ($update_stmt->execute()) {
                $message = '<div class="alert alert-success">Password updated successfully.</div>';
            } else {
                $message = '<div class="alert alert-danger">Error updating password. Please try again.</div>';
            }
        } else {
            $message = '<div class="alert alert-danger">New passwords do not match.</div>';
        }
    } else {
        $message = '<div class="alert alert-danger">Current password is incorrect.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile - LUFEM School</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../css/student_dashboard.css">
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar" class="active">
            <div class="sidebar-header">
                <h3>LUFEM School</h3>
            </div>
            <ul class="list-unstyled components">
                <li class="">
                    <a href="student_dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
                </li>
                <li class="active">
                    <a href="profile.php"><i class="bi bi-person"></i> Profile</a>
                </li>
                <li>
                    <a href="results.php"><i class="bi bi-file-earmark-text"></i> Results</a>
                </li>
                <li>
                    <a href="../../logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
                </li>
            </ul>
        </nav>
        <!-- Page Content -->
        <div id="content">
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn btn-info">
                        <i class="bi bi-list"></i>
                    </button>
                    <span class="navbar-text ms-3">
                        Welcome, <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                    </span>
                </div>
            </nav>

            <div class="container-fluid mt-4">
                <h2 class="mb-4">Student Profile</h2>
                
                <?php echo $message; ?>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Personal Information</h5>
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></p>
                                <p><strong>Matric Number:</strong> <?php echo htmlspecialchars($student['matriculation_number']); ?></p>
                                <p><strong>Gender:</strong> <?php echo htmlspecialchars($student['gender']); ?></p>
                                <p><strong>Department:</strong> <?php echo htmlspecialchars($student['department_name']); ?></p>
                                <p><strong>Class:</strong> <?php echo htmlspecialchars($student['class']); ?></p>
                                <p><strong>Current Session:</strong> <?php echo htmlspecialchars($student['academic_year'] . ' - ' . $student['session_name']); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Change Password</h5>
                                <form method="POST" action="">
                                    <div class="mb-3">
                                        <label for="current_password" class="form-label">Current Password</label>
                                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="new_password" class="form-label">New Password</label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Change Password</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/student_dashboard.js"></script>
    <script src="../../js/sidebar.js"></script>
    
</body>
</html>
