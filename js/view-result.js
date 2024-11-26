$(document).ready(function() {
    const viewResultModal = new bootstrap.Modal(document.getElementById('viewResultModal'));
    const editResultModal = new bootstrap.Modal(document.getElementById('editResultModal'));

    // View Result
    $('.view-result').on('click', function() {
        const studentId = $(this).data('student-id');
        $.ajax({
            url: 'ajax/get_student_result.php',
            type: 'GET',
            data: { student_id: studentId },
            success: function(response) {
                $('#viewResultContent').html(response);
                viewResultModal.show();
            },
            error: function() {
                alert('Error fetching student result. Please try again.');
            }
        });
    });

    // Edit Result
    $('.edit-result').on('click', function() {
        const studentId = $(this).data('student-id');
        $.ajax({
            url: 'ajax/get_edit_result_form.php',
            type: 'GET',
            data: { student_id: studentId },
            success: function(response) {
                $('#editResultContent').html(response);
                editResultModal.show();
            },
            error: function() {
                alert('Error fetching edit form. Please try again.');
            }
        });
    });

    // Handle edit form submission
    $(document).on('submit', '#editResultForm', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'ajax/update_student_result.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Result updated successfully!');
                    
editResultModal.hide();
                    location.reload(); // Reload the page to reflect changes
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Error updating result. Please try again.');
            }
        });
    });
});

