$(document).ready(function() {
  function handleLogin(formId, loginUrl) {
    $(formId).submit(function(e) {
      e.preventDefault();
      var $form = $(this);
      var $submitBtn = $form.find('button[type="submit"]');
      var originalBtnText = $submitBtn.text();

      $submitBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Logging in...');
      $submitBtn.prop('disabled', true);

      $.ajax({
        url: loginUrl,
        type: 'POST',
        data: $form.serialize(),
        dataType: 'json',
        success: function(response) {
          if (response.success) {
            showNotification('success', 'Login successful! Redirecting...');
            setTimeout(function() {
              window.location.href = response.redirect;
            }, 1500);
          } else {
            showNotification('danger', response.message || 'Login failed. Please try again.');
            $submitBtn.html(originalBtnText);
            $submitBtn.prop('disabled', false);
          }
        },
        error: function() {
          showNotification('danger', 'An error occurred. Please try again later.');
          $submitBtn.html(originalBtnText);
          $submitBtn.prop('disabled', false);
        }
      });
    });
  }

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

  handleLogin('#adminLoginForm', 'login/admin_login.php');
  handleLogin('#studentLoginForm', 'login/student_login.php');
});