$(document).ready(function() {
  // View Student
  $('.view-student').on('click', function() {
    var studentId = $(this).data('student-id');
    loadStudentDetails(studentId, false);
  });

  // Edit Student
  $('.edit-student').on('click', function() {
    var studentId = $(this).data('student-id');
    loadStudentDetails(studentId, true);
  });

  // Delete Student
  $('.delete-student').on('click', function() {
    var studentId = $(this).data('student-id');
    $('#deleteConfirmModal').modal('show');
    $('#confirmDelete').data('student-id', studentId);
  });

  // Confirm Delete
  $('#confirmDelete').on('click', function() {
    var studentId = $(this).data('student-id');
    $.ajax({
      url: 'ajax/delete_student.php',
      type: 'POST',
      data: { student_id: studentId },
      success: function(response) {
        var result = JSON.parse(response);
        if (result.success) {
          alert('Student deleted successfully.');
          location.reload();
        } else {
          alert('Error: ' + result.message);
        }
      },
      error: function() {
        alert('Error deleting student.');
      }
    });
    $('#deleteConfirmModal').modal('hide');
  });

  // Load Student Details
  function loadStudentDetails(studentId, editable) {
    $.ajax({
      url: 'ajax/get_student_details.php',
      type: 'GET',
      data: { student_id: studentId, editable: editable },
      success: function(response) {
        $('#studentModalContent').html(response);
        $('#studentModalTitle').text(editable ? 'Edit Student' : 'Student Details');
        $('#studentModal').modal('show');
      },
      error: function() {
        alert('Error fetching student details.');
      }
    });
  }

  // Submit Edit Student Form
  $(document).on('submit', '#editStudentForm', function(e) {
    e.preventDefault();
    $.ajax({
      url: 'ajax/update_student.php',
      type: 'POST',
      data: $(this).serialize(),
      success: function(response) {
        var result = JSON.parse(response);
        if (result.success) {
          alert('Student information updated successfully.');
          $('#studentModal').modal('hide');
          location.reload();
        } else {
          alert('Error: ' + result.message);
        }
      },
      error: function() {
        alert('Error updating student information.');
      }
    });
  });

  // Filter form submission
  $('#filterForm').on('submit', function(e) {
    e.preventDefault();
    var formData = $(this).serialize();
    window.location.href = 'student_list.php?' + formData;
  });
});