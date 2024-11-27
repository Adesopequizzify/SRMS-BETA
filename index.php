<?php
session_start();
include 'db.php';

if (isset($_SESSION['admin_id']) || isset($_SESSION['student_id'])) {
    header('Location: dashboard/admin_dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LUFEM School - Student Result Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container-fluid min-vh-100 d-flex align-items-center justify-content-center">
        <div class="row justify-content-center w-100">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-lg">
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <i class="bi bi-person-circle user-icon"></i>
                        </div>
                        <h2 class="text-center mb-2">LUFEM School</h2>
                        <h5 class="text-center mb-4">Student Result Management System Project 2024</h5>
                        <ul class="nav nav-tabs mb-3" id="loginTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="admin-tab" data-bs-toggle="tab" data-bs-target="#admin-login" type="button" role="tab">Admin Login</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="student-tab" data-bs-toggle="tab" data-bs-target="#student-login" type="button" role="tab">Student Login</button>
                            </li>
                        </ul>
                        <div class="tab-content" id="loginTabsContent">
                            <div class="tab-pane fade show active" id="admin-login" role="tabpanel">
                                <form id="adminLoginForm">
                                    <div class="mb-3">
                                        <label for="adminUsername" class="form-label">Username</label>
                                        <input type="text" class="form-control" id="adminUsername" name="adminUsername" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="adminPassword" class="form-label">Password</label>
                                        <input type="password" class="form-control" id="adminPassword" name="adminPassword" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">Login as Admin</button>
                                </form>
                            </div>
                            <div class="tab-pane fade" id="student-login" role="tabpanel">
                                <form id="studentLoginForm">
                                    <div class="mb-3">
                                        <label for="matricNumber" class="form-label">Matric Number</label>
                                        <input type="text" class="form-control" id="matricNumber" name="matricNumber" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="studentPassword" class="form-label">Password</label>
                                        <input type="password" class="form-control" id="studentPassword" name="studentPassword" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">Login as Student</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/script.js"></script>
    <script>
        function showNotification(type, message) {
            var alertClass = 'alert-' + type;
            var $alert = $('<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
                           message +
                           '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                           '</div>');
            
            $('.card-body').prepend($alert);
            
            setTimeout(function() {
                $alert.alert('close');
            }, 5000);
        }
    </script>
</body>
</html>