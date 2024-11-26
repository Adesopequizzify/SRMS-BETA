<!-- Add this div right after the wrapper div -->
<div class="sidebar-overlay"></div>

<!-- Update the JavaScript section -->
<script>
$(document).ready(function () {
    $('#sidebarCollapse').on('click', function () {
        $('#sidebar, #content').toggleClass('active');
        $('.sidebar-overlay').toggleClass('active');
    });

    // Close sidebar when clicking overlay
    $('.sidebar-overlay').on('click', function () {
        $('#sidebar, #content').removeClass('active');
        $('.sidebar-overlay').removeClass('active');
    });

    // Close sidebar when clicking outside on mobile
    $(document).on('click touchstart', function (e) {
        if ($('#sidebar').hasClass('active') && 
            !$(e.target).closest('#sidebar').length && 
            !$(e.target).closest('#sidebarCollapse').length) {
            $('#sidebar, #content').removeClass('active');
            $('.sidebar-overlay').removeClass('active');
        }
    });

    // Prevent sidebar close when clicking inside it
    $('#sidebar').on('click touchstart', function (e) {
        e.stopPropagation();
    });
});
</script>

