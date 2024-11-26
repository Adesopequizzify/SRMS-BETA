document.addEventListener('DOMContentLoaded', function () {
    // Sidebar toggle
    const sidebarCollapse = document.getElementById('sidebarCollapse');
    const sidebar = document.getElementById('sidebar');
    const content = document.getElementById('content');

    if (sidebarCollapse && sidebar && content) {
        sidebarCollapse.addEventListener('click', function () {
            sidebar.classList.toggle('active');
            content.classList.toggle('active');
        });
    }

    // Add smooth scrolling to all links
    document.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', function (e) {
            if (this.hash !== "") {
                e.preventDefault();
                const hash = this.hash;
                document.querySelector(hash).scrollIntoView({
                    behavior: 'smooth'
                });
                history.pushState(null, null, hash);
            }
        });
    });

    // Add hover effect to sidebar items
    document.querySelectorAll('#sidebar ul li a').forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.classList.add('animated', 'pulse');
        });
        item.addEventListener('mouseleave', function() {
            this.classList.remove('animated', 'pulse');
        });
    });

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Add custom validation styles
    var forms = document.getElementsByClassName('needs-validation');
    Array.prototype.filter.call(forms, function(form) {
        form.addEventListener('submit', function(event) {
            if (form.checkValidity() === false) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
});

