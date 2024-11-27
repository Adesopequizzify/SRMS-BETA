$(document).ready(function() {
    let studentId = null;
    let departmentId = null;
    let previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
    
    // Grade point mapping
    const gradePoints = {
        'A': 4.0,
        'B': 3.5,
        'C': 3.0,
        'D': 2.5,
        'E': 2.0,
        'F': 0.0
    };

    // Final remarks mapping
    const finalRemarks = [
        { min: 3.50, max: 4.00, remark: 'Distinction' },
        { min: 3.00, max: 3.49, remark: 'Upper Credit' },
        { min: 2.50, max: 2.99, remark: 'Lower Credit' },
        { min: 2.00, max: 2.49, remark: 'Pass' },
        { min: 0.00, max: 1.99, remark: 'Fail' }
    ];

    // Prevent form submission on Enter key for matriculation number input
    $('#matricNumber').on('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            $(this).blur(); // Trigger the blur event
            return false;
        }
    });

    $('#matricNumber').on('blur', function() {
        let matricNumber = $(this).val();
        if (matricNumber) {
            $.ajax({
                url: 'ajax/get_student_info.php',
                type: 'GET',
                data: { matriculation_number: matricNumber },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        studentId = response.data.student_id;
                        departmentId = response.data.department_id;
                        $('#studentId').val(studentId);
                        $('#studentName').val(`${response.data.first_name} ${response.data.last_name}`);
                        $('#department').val(response.data.department_name);
                        $('#academicYear').val(response.data.academic_year_id);
                        $('#studentInfo').show();
                        loadStudentCourses();
                    } else {
                        showAlert(response.message);
                        $('#studentInfo').hide();
                    }
                },
                error: function(xhr, status, error) {
                    showAlert('An error occurred while fetching student information: ' + error);
                    $('#studentInfo').hide();
                }
            });
        }
    });

    function loadStudentCourses() {
        $.ajax({
            url: 'ajax/get_student_courses.php',
            type: 'GET',
            data: {
                student_id: studentId,
                academic_year_id: $('#academicYear').val(),
                session_id: $('#session').val()
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    let coursesHtml = '<h4 class="mb-3">Course Results:</h4>';
                    if (response.data.length > 0) {
                        response.data.forEach(function(course) {
                            coursesHtml += `
                                <div class="course-result">
                                    <input type="hidden" name="courses[]" value="${course.course_id}">
                                    <div class="row align-items-center">
                                        <div class="col-md-6 mb-2 mb-md-0">
                                            <label class="form-label">${course.course_code} - ${course.course_name}</label>
                                        </div>
                                        <div class="col-md-4 mb-2 mb-md-0">
                                            <div class="input-group">
                                                <input type="number" 
                                                       class="form-control score-input" 
                                                       name="scores[]" 
                                                       min="0" 
                                                       max="100" 
                                                       step="0.01" 
                                                       required 
                                                       data-course-id="${course.course_id}">
                                                <span class="input-group-text grade-display"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                    } else {
                        coursesHtml += '<p>No registered courses found for this student.</p>';
                    }
                    $('#courseResults').html(coursesHtml);
                } else {
                    showAlert('Failed to load courses: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                showAlert('An error occurred while loading courses: ' + error);
            }
        });
    }

    $(document).on('input', '.score-input', function() {
        let score = parseFloat($(this).val());
        let gradeDisplay = $(this).siblings('.grade-display');
        
        if (!isNaN(score)) {
            let grade = calculateGrade(score);
            gradeDisplay.html(`${grade}`);
            gradeDisplay.removeClass().addClass('input-group-text grade-display');
            
            if (grade === 'F') {
                gradeDisplay.addClass('bg-danger text-white');
            } else {
                gradeDisplay.addClass('bg-success text-white');
            }
        } else {
            gradeDisplay.html('');
            gradeDisplay.removeClass().addClass('input-group-text grade-display');
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

    function calculateGPA(scores) {
        let totalPoints = 0;
        let totalCourses = scores.length;
        
        scores.forEach(score => {
            totalPoints += gradePoints[calculateGrade(score)];
        });
        
        return totalCourses > 0 ? (totalPoints / totalCourses).toFixed(2) : 0;
    }

    function getFinalRemark(gpa) {
        for (let item of finalRemarks) {
            if (gpa >= item.min && gpa <= item.max) {
                return item.remark;
            }
        }
        return 'Fail';
    }

    $('#resultEntryForm').on('submit', function(e) {
        e.preventDefault();
        let scores = [];
        let courses = [];
        let isValid = true;

        $('.score-input').each(function() {
            let score = parseFloat($(this).val());
            if (isNaN(score) || score < 0 || score > 100) {
                showAlert('Please enter valid scores for all courses (0-100)');
                isValid = false;
                return false;
            }
            scores.push(score);
            courses.push({
                code: $(this).closest('.course-result').find('label').text().split(' - ')[0],
                name: $(this).closest('.course-result').find('label').text().split(' - ')[1],
                score: score,
                grade: calculateGrade(score)
            });
        });

        if (!isValid) return;

        let gpa = calculateGPA(scores);
        let finalRemark = getFinalRemark(gpa);

        let previewHtml = `
            <div class="result-preview p-4">
                <h4 class="mb-3">Student Result Summary</h4>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>Student:</strong> ${$('#studentName').val()}</p>
                        <p><strong>Matric Number:</strong> ${$('#matricNumber').val()}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Academic Year:</strong> ${$('#academicYear option:selected').text()}</p>
                        <p><strong>Session:</strong> ${$('#session option:selected').text()}</p>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover preview-table">
                        <thead class="table-primary">
                            <tr>
                                <th>Course Code</th>
                                <th>Course Name</th>
                                <th>Score</th>
                                <th>Grade</th>
                                <th>Grade Point</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${courses.map(course => `
                                <tr>
                                    <td>${course.code}</td>
                                    <td>${course.name}</td>
                                    <td>${course.score}</td>
                                    <td>${course.grade}</td>
                                    <td>${gradePoints[course.grade]}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4">
                    <h5>GPA: <span class="badge bg-primary">${gpa}</span></h5>
                    <h5>Final Remark: <span class="badge bg-${finalRemark === 'Fail' ? 'danger' : 'success'}">${finalRemark}</span></h5>
                </div>
            </div>
        `;

        $('#previewContent').html(previewHtml);
        previewModal.show();
    });

    $('#confirmSubmission').click(function() {
        $('#resultEntryForm')[0].submit();
    });

    function showAlert(message, type = 'danger') {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        $('#alertPlaceholder').html(alertHtml);
    }

    // Update courses when academic year or session changes
    $('#academicYear, #session').change(function() {
        if (studentId) {
            loadStudentCourses();
        }
    });
});

