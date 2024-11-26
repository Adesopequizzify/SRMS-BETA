$(document).ready(function() {
    let table = $('#coursesTable').DataTable({
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        dom: '<"row"<"col-md-6"l><"col-md-6"f>>rtip',
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search..."
        }
    });

    // Edit course
    $('#coursesTable').on('click', '.edit-course', function() {
        let courseId = $(this).data('id');
        let row = $(this).closest('tr');
        let course = {
            course_id: courseId,
            course_code: row.find('td:eq(0)').text(),
            course_name: row.find('td:eq(1)').text(),
            department_name: row.find('td:eq(2)').text()
        };

        $('#editCourseId').val(course.course_id);
        $('#editCourseCode').val(course.course_code);
        $('#editCourseName').val(course.course_name);
        $('#editDepartment').val($('#editDepartment option').filter(function() {
            return $(this).text() === course.department_name;
        }).val());

        $('#editCourseModal').modal('show');
    });

    // Save course changes
    $('#saveCourseChanges').click(function() {
        let formData = $('#editCourseForm').serialize();
        $.ajax({
            url: 'ajax/update_course.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#editCourseModal').modal('hide');
                    showAlert('Course information updated successfully', 'success');
                    // Reload the page to reflect changes
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showAlert('Failed to update course information: ' + response.message);
                }
            },
            error: function() {
                showAlert('An error occurred while updating course information');
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

