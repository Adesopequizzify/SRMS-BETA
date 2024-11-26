<nav id="sidebar">
    <div class="sidebar-header">
        <h3>LUFEM School</h3>
    </div>

    <ul class="list-unstyled components">
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' ? 'active' : ''; ?>">
            <a href="admin_dashboard.php">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
        </li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'add_student.php' ? 'active' : ''; ?>">
            <a href="add_student.php">
                <i class="bi bi-person-plus"></i> Add New Student
            </a>
        </li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'student_list.php' ? 'active' : ''; ?>">
            <a href="student_list.php">
                <i class="bi bi-people"></i> Registered Students
            </a>
        </li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'course_creation.php' ? 'active' : ''; ?>">
            <a href="course_creation.php">
                <i class="bi bi-book"></i> Course Registration
            </a>
        </li>
         <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'course_list.php' ? 'active' : ''; ?>">
            <a href="course_list.php">
                <i class="bi bi-people"></i> Course List
            </a>
        </li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'student_course_registration.php' ? 'active' : ''; ?>">
            <a href="student_course_registration.php">
                <i class="bi bi-pencil-square"></i> Student Course Registration
            </a>
        </li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'result_entry.php' ? 'active' : ''; ?>">
            <a href="result_entry.php">
                <i class="bi bi-pencil-square"></i> Insert New Result
            </a>
        </li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'view_results.php' ? 'active' : ''; ?>">
            <a href="view_result.php">
                <i class="bi bi-table"></i> View Results
            </a>
        </li>
        <li>
            <a href="../logout.php">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </li>
    </ul>
</nav>