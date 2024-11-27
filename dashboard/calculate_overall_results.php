<?php
session_start();
require_once __DIR__ . '/../db.php';  // Using __DIR__ to ensure correct path resolution

// Function to calculate GPA
function calculateGPA($totalPoints, $totalCourses) {
    return $totalCourses > 0 ? $totalPoints / $totalCourses : 0;
}

// Function to get overall grade letter
function getOverallGradeLetter($gpa) {
    if ($gpa >= 3.5) return 'A';
    if ($gpa >= 3.0) return 'B';
    if ($gpa >= 2.5) return 'C';
    if ($gpa >= 2.0) return 'D';
    return 'F';
}

// Function to get final remark
function getFinalRemark($gpa) {
    if ($gpa >= 3.5) return 'Distinction';
    if ($gpa >= 3.0) return 'Upper Credit';
    if ($gpa >= 2.5) return 'Lower Credit';
    if ($gpa >= 2.0) return 'Pass';
    return 'Fail';
}

// Main calculation function
function calculateAndUpdateOverallResults($student_id = null, $academic_year_id = null, $session_id = null) {
    global $conn;
    
    error_log("Starting calculateAndUpdateOverallResults - Student ID: " . ($student_id ?? 'All') . 
              ", Academic Year ID: " . ($academic_year_id ?? 'All') . 
              ", Session ID: " . ($session_id ?? 'All'));
    
    $conn->begin_transaction();
    
    try {
        // Get all distinct student, academic year, and session combinations
        $query = "SELECT DISTINCT student_id, academic_year_id, session_id FROM Results";
        $params = [];
        $types = "";

        // Add conditions if specific parameters are provided
        if ($student_id !== null) {
            $query .= " WHERE student_id = ?";
            $params[] = $student_id;
            $types .= "i";
        }
        if ($academic_year_id !== null) {
            $query .= ($student_id === null ? " WHERE" : " AND") . " academic_year_id = ?";
            $params[] = $academic_year_id;
            $types .= "i";
        }
        if ($session_id !== null) {
            $query .= (($student_id === null && $academic_year_id === null) ? " WHERE" : " AND") . " session_id = ?";
            $params[] = $session_id;
            $types .= "i";
        }

        error_log("Executing query: " . $query);
        error_log("Query parameters: " . json_encode($params));

        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . $conn->error);
        }
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        if (!$stmt->execute()) {
            throw new Exception("Error executing statement: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        
        $updatedCount = 0;
        
        while ($row = $result->fetch_assoc()) {
            $current_student_id = $row['student_id'];
            $current_academic_year_id = $row['academic_year_id'];
            $current_session_id = $row['session_id'];
            
            error_log("Processing - Student ID: $current_student_id, Academic Year ID: $current_academic_year_id, Session ID: $current_session_id");
            
            // Calculate overall results for this student, year, and session
            $calcQuery = "SELECT 
                            COUNT(*) as total_courses,
                            SUM(CASE 
                                WHEN g.grade_letter = 'A' THEN 4
                                WHEN g.grade_letter = 'B' THEN 3
                                WHEN g.grade_letter = 'C' THEN 2
                                WHEN g.grade_letter = 'D' THEN 1
                                ELSE 0
                            END) as total_points
                          FROM Results r
                          JOIN Grades g ON r.grade_id = g.grade_id
                          WHERE r.student_id = ? AND r.academic_year_id = ? AND r.session_id = ?";
            
            $stmt = $conn->prepare($calcQuery);
            if (!$stmt) {
                throw new Exception("Error preparing calculation statement: " . $conn->error);
            }
            
            $stmt->bind_param("iii", $current_student_id, $current_academic_year_id, $current_session_id);
            
            if (!$stmt->execute()) {
                throw new Exception("Error executing calculation statement: " . $stmt->error);
            }
            
            $calcResult = $stmt->get_result()->fetch_assoc();
            
            $totalCourses = $calcResult['total_courses'];
            $totalPoints = $calcResult['total_points'];
            
            $gpa = calculateGPA($totalPoints, $totalCourses);
            $overallGradeLetter = getOverallGradeLetter($gpa);
            $finalRemark = getFinalRemark($gpa);
            
            error_log("Calculated - Total Courses: $totalCourses, Total Points: $totalPoints, GPA: $gpa, Grade: $overallGradeLetter, Remark: $finalRemark");
            
            // Insert or update the StudentOverallResults table
            $updateQuery = "INSERT INTO StudentOverallResults 
                            (student_id, academic_year_id, session_id, cumulative_gpa, total_credits_earned, overall_grade_letter, final_remark)
                            VALUES (?, ?, ?, ?, ?, ?, ?)
                            ON DUPLICATE KEY UPDATE
                            cumulative_gpa = VALUES(cumulative_gpa),
                            total_credits_earned = VALUES(total_credits_earned),
                            overall_grade_letter = VALUES(overall_grade_letter),
                            final_remark = VALUES(final_remark)";
            
            $stmt = $conn->prepare($updateQuery);
            if (!$stmt) {
                throw new Exception("Error preparing update statement: " . $conn->error);
            }
            
            $stmt->bind_param("iiidiss", $current_student_id, $current_academic_year_id, $current_session_id, $gpa, $totalCourses, $overallGradeLetter, $finalRemark);
            
            if (!$stmt->execute()) {
                throw new Exception("Error executing update statement: " . $stmt->error);
            }
            
            $updatedCount++;
            error_log("Updated overall results for Student ID: $current_student_id");
        }
        
        $conn->commit();
        error_log("Transaction committed successfully. Updated $updatedCount student(s).");
        return ["success" => true, "message" => "Updated overall results for $updatedCount student(s)."];
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error in calculateAndUpdateOverallResults: " . $e->getMessage());
        return ["success" => false, "message" => "Error: " . $e->getMessage()];
    }
}

// Check if the script is being run directly or included
if ($_SERVER['REQUEST_METHOD'] === 'POST' || php_sapi_name() === 'cli') {
    $result = calculateAndUpdateOverallResults();
    echo json_encode($result);
}