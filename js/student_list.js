$(document).ready(function() {
  let currentStudentId = null;
  let isEditMode = false;

  // View Student
  $(document).on('click', '.view-student', function() {
    const $row = $(this).closest('tr');
    populateStudentModal($row, false);
  });

  // Edit Student
  $(document).on('click', '.edit-student', function() {
    const $row = $(this).closest('tr');
    populateStudentModal($row, true);
  });

  // Delete Student
  $(document).on('click', '.delete-student', function() {
    currentStudentId = $(this).closest('tr').data('student-id');
  });

  // Populate Student Modal
  function populateStudentModal($row, editable) {
    const studentData = $row.data();
    currentStudentId = studentData.studentId;
    isEditMode = editable;

    $('#studentId').val(studentData.studentId);
    $('#firstName').val(studentData.firstName).prop('readonly', !editable);
    $('#lastName').val(studentData.lastName);
    $('#gender').val(studentData.gender).prop('disabled', !editable);
    $('#matricNumber').val(studentData.matricNumber);
    $('#department').val(studentData.departmentId).prop('disabled', !editable);
    $('#class').val(studentData.class).prop('disabled', !editable);

    $('#studentModalTitle').text(editable ? 'Edit Student' : 'Student Details');
    $('#saveChanges').toggle(editable);
  }

  // Save Changes
  $('#saveChanges').on('click', function() {
    if (!isEditMode) return;

    const formData = $('#studentForm').serialize();

    $.ajax({
      url: 'ajax/update_student.php',
      type: 'POST',
      data: formData,
      dataType: 'json',
      success: function(response) {
        if (response.success) {
          alert('Student information updated successfully.');
          updateTableRow(response.student);
          $('#studentModal').modal('hide');
        } else {
          alert('Error: ' + response.message);
        }
      },
      error: function() {
        alert('An error occurred while updating student information.');
      }
    });
  });

  // Confirm Delete
  $('#confirmDelete').on('click', function() {
    if (!currentStudentId) return;

    $.ajax({
      url: 'ajax/delete_student.php',
      type: 'POST',
      data: { student_id: currentStudentId },
      dataType: 'json',
      success: function(response) {
        if (response.success) {
          alert('Student deleted successfully.');
          $(`tr[data-student-id="${currentStudentId}"]`).remove();
          $('#deleteConfirmModal').modal('hide');
        } else {
          alert('Error: ' + response.message);
        }
      },
      error: function() {
        alert('An error occurred while deleting the student.');
      }
    });
  });

  // Update Table Row
  function updateTableRow(studentData) {
    const $row = $(`tr[data-student-id="${studentData.student_id}"]`);
    $row.data(studentData);
    $row.find('td:eq(0)').text(studentData.matriculation_number);
    $row.find('td:eq(1)').text(`${studentData.last_name}, ${studentData.first_name}`);
    $row.find('td:eq(2)').text(studentData.department_name);
    $row.find('td:eq(3)').text(studentData.class);
    $row.find('td:eq(4)').text(studentData.gender);
  }

  // Filter form submission
  $('#filterForm').on('submit', function(e) {
    e.preventDefault();
    const formData = $(this).serialize();
    window.location.href = 'student_list.php?' + formData;
  });
});