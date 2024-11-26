$(document).ready(function() {
  $('#sidebarCollapse').on('click', function() {
    $('#sidebar').toggleClass('active');
    $('#content').toggleClass('active');
  });

  // Rest of your existing JavaScript code...


    // Add smooth scrolling to all links
    $("a").on('click', function(event) {
        if (this.hash !== "") {
            event.preventDefault();
            var hash = this.hash;
            $('html, body').animate({
                scrollTop: $(hash).offset().top
            }, 800, function(){
                window.location.hash = hash;
            });
        }
    });

    // Add fade-in animation to cards
    $('.card').each(function(i) {
        $(this).delay(100 * i).fadeIn(500);
    });

    // Add hover effect to sidebar items
    $('#sidebar ul li a').hover(
        function() {
            $(this).addClass('animated pulse');
        },
        function() {
            $(this).removeClass('animated pulse');
        }
    );

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Add custom validation styles
    (function() {
        'use strict';
        window.addEventListener('load', function() {
            var forms = document.getElementsByClassName('needs-validation');
            var validation = Array.prototype.filter.call(forms, function(form) {
                form.addEventListener('submit', function(event) {
                    if (form.checkValidity() === false) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        }, false);
    })();
});
