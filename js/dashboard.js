$(document).ready(function() {
  $('#sidebarCollapse').on('click', function() {
    $('#sidebar').toggleClass('active');
  });

  // Handle sidebar navigation
  $('#sidebar a[data-page]').on('click', function(e) {
    e.preventDefault();
    var page = $(this).data('page');
    $('.content-page').removeClass('active');
    $('#' + page + '-page').addClass('active');
    $('#sidebar li').removeClass('active');
    $(this).parent('li').addClass('active');
  });

  // Custom popup close button
  $('.custom-popup-close').on('click', function() {
  $('#customPopup').hide();
  });
  

  function showCustomPopup(message, type = 'info') {
    $('.custom-popup-message').text(message);
    $('#customPopup').removeClass().addClass('custom-popup ' + type).show();
    setTimeout(function() {
      $('#customPopup').hide();
    }, 5000);
  }

  // Fetch dashboard statistics
  function fetchDashboardStats() {
    $.ajax({
      url: 'ajax/get_dashboard_stats.php',
      method: 'GET',
      dataType: 'json',
      success: function(response) {
        $('#totalStudents').text(response.totalStudents);
        $('#totalCourses').text(response.totalCourses);
        $('#totalDepartments').text(response.totalDepartments);
        $('#totalResults').text(response.totalResults);
      },
      error: function() {
        console.error('Failed to fetch dashboard statistics');
      }
    });
  }



  // Initial fetch
  fetchDashboardStats();
  

  // Refresh stats every 30 seconds
  setInterval(fetchDashboardStats, 30000);
  
});