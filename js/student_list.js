$(document).ready(function() {
  let table = $('#studentsTable').DataTable({
    pageLength: 10,
    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
    dom: '<"row"<"col-md-6"l><"col-md-6"f>>rtip',
    language: {
      search: "_INPUT_",
      searchPlaceholder: "Search..."
    }
  });

  // Edit student
  $('#studentsTable').on('click', '.edit-student', function() {
    let studentId = $(this).data('id');
    let row = $(this).closest('tr');
    let student = {
      student_id: studentId,
      first_name: row.find('td:eq(1)').text(),
      last_name: row.find('td:eq(2)').text(),
      matriculation_number: row.find('td:eq(0)').text(),
      department_name: row.find('td:eq(3)').text(),
      class: row.find('td:eq(4)').text(),
      academic_year: row.find('td:eq(5)').text()
    };

    $('#editStudentId').val(student.student_id);
    $('#editFirstName').val(student.first_name);
    $('#editLastName').val(student.last_name);
    $('#editMatricNumber').val(student.matriculation_number);
    $('#editDepartment').val($('#editDepartment option').filter(function() {
      return $(this).text() === student.department_name;
    }).val());
    $('#editClass').val(student.class);
    $('#editAcademicYear').val($('#editAcademicYear option').filter(function() {
      return $(this).text() === student.academic_year;
    }).val());

    $('#editStudentModal').modal('show');
  });

  // Save student changes
  $('#saveStudentChanges').click(function() {
    let formData = $('#editStudentForm').serialize();
    $.ajax({
      url: 'ajax/update_student.php',
      type: 'POST',
      data: formData,
      dataType: 'json',
      success: function(response) {
        if (response.success) {
          $('#editStudentModal').modal('hide');
          showAlert('Student information updated successfully', 'success');
          // Reload the page to reflect changes
          setTimeout(function() {
            location.reload();
          }, 1500);
        } else {
          showAlert('Failed to update student information: ' + response.message);
        }
      },
      error: function() {
        showAlert('An error occurred while updating student information');
      }
    });
  });

  // Apply filters
  $('#filterForm').on('submit', function(e) {
    e.preventDefault();
    let url = window.location.pathname + '?' + $(this).serialize();
    window.location.href = url;
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
});