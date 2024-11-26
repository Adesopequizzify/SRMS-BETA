$(document).ready(function() {
  let studentId = null;
  let departmentId = null;

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
            $('#firstName').val(response.data.first_name);
            $('#lastName').val(response.data.last_name);
            $('#department').val(response.data.department_name);
            $('#class').val(response.data.class);
            $('#academicYear').val(response.data.academic_year_id);
            $('#studentInfo').show();
            loadCourses(departmentId);
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

  $('#courseRegistrationForm').on('submit', function(e) {
    if (!studentId) {
      e.preventDefault();
      showAlert('Please enter a valid matriculation number first');
      return;
    }

    let selectedCourses = $('input[name="courses[]"]:checked');
    if (selectedCourses.length === 0) {
      e.preventDefault();
      showAlert('Please select at least one course');
      return;
    }

    // Log the form data for debugging
    console.log('Submitting form with data:', {
      student_id: studentId,
      academic_year_id: $('#academicYear').val(),
      session_id: $('#session').val(),
      courses: Array.from(selectedCourses).map(c => c.value)
    });

    // The form will now submit normally to the PHP file
  });

  function loadCourses(departmentId) {
    if (!departmentId) {
      showAlert('Invalid department ID');
      return;
    }

    $.ajax({
      url: 'ajax/get_department_courses.php',
      type: 'GET',
      data: { department_id: departmentId },
      dataType: 'json',
      success: function(response) {
        if (response.success) {
          let courseListHtml = '<h4>Available Courses:</h4>';
          if (response.data.length > 0) {
            response.data.forEach(function(course) {
              courseListHtml += `
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="courses[]" value="${course.course_id}" id="course${course.course_id}">
                  <label class="form-check-label" for="course${course.course_id}">
                    ${course.course_code} - ${course.course_name}
                  </label>
                </div>
              `;
            });
          } else {
            courseListHtml += '<p>No courses available for this department.</p>';
          }
          $('#courseList').html(courseListHtml);
        } else {
          showAlert('Failed to load courses: ' + response.message);
        }
      },
      error: function(xhr, status, error) {
        showAlert('An error occurred while loading courses: ' + error);
      }
    });
  }

  function showAlert(message, type = 'danger') {
    const alertHtml = `
      <div class="alert alert-${type} alert-dismissible fade show" role="alert">
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    `;
    $('#alertPlaceholder').html(alertHtml);
  }
});