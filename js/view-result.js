$(document).ready(function() {
  // View Result
  $('.view-result').on('click', function() {
    var studentId = $(this).data('student-id');
    $.ajax({
      url: 'ajax/get_student_result.php',
      type: 'GET',
      data: { student_id: studentId },
      success: function(response) {
        $('#viewResultContent').html(response);
        $('#viewResultModal').modal('show');
      },
      error: function() {
        alert('Error fetching student result.');
      }
    });
  });

  // Edit Result
  $('.edit-result').on('click', function() {
    var studentId = $(this).data('student-id');
    $.ajax({
      url: 'ajax/get_edit_result_form.php',
      type: 'GET',
      data: { student_id: studentId },
      success: function(response) {
        $('#editResultContent').html(response);
        $('#editResultModal').modal('show');
      },
      error: function() {
        alert('Error fetching edit form.');
      }
    });
  });

  // Submit Edit Result Form
  $(document).on('submit', '#editResultForm', function(e) {
    e.preventDefault();
    $.ajax({
      url: 'ajax/update_student_result.php',
      type: 'POST',
      data: $(this).serialize(),
      success: function(response) {
        var result = JSON.parse(response);
        if (result.success) {
          alert('Result updated successfully.');
          $('#editResultModal').modal('hide');
          location.reload(); // Reload the page to show updated results
        } else {
          alert('Error: ' + result.message);
        }
      },
      error: function() {
        alert('Error updating result.');
      }
    });
  });

  // Filter form submission
  $('#filterForm').on('submit', function(e) {
    e.preventDefault();
    var formData = $(this).serialize();
    window.location.href = 'view_result.php?' + formData;
  });
});