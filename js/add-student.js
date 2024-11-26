$(document).ready(function() {
  function showAlert(message, type = 'success') {
    const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
    $('#alertPlaceholder').html(alertHtml);
  }

  $('#addStudentForm').on('submit', function(e) {
    e.preventDefault();

    const $form = $(this);
    const $submitBtn = $form.find('button[type="submit"]');
    const $buttonText = $submitBtn.find('.button-text');
    const $spinner = $submitBtn.find('.spinner-border');

    // Disable button and show spinner
    $submitBtn.prop('disabled', true);
    $buttonText.text('Adding Student...');
    $spinner.removeClass('d-none');

    $.ajax({
      url: 'ajax/add_student.php',
      method: 'POST',
      data: $form.serialize(),
      dataType: 'json',
      success: function(response) {
        if (response.success) {
          showAlert('Student added successfully!', 'success');
          $form[0].reset();
        } else {
          showAlert(response.message || 'Failed to add student. Please try again.', 'danger');
        }
      },
      error: function() {
        showAlert('An error occurred. Please try again later.', 'danger');
      },
      complete: function() {
        // Re-enable button and hide spinner
        $submitBtn.prop('disabled', false);
        $buttonText.text('Add Student');
        $spinner.addClass('d-none');
      }
    });
  });
});