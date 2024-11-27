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
                alert('Error fetching result details.');
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

    // Handle edit result form submission
    $(document).on('submit', '#editResultForm', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            url: 'ajax/update_student_result.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Result updated successfully');
                    $('#editResultModal').modal('hide');
                    // Refresh the page to show updated results
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('An error occurred while updating the result.');
            }
        });
    });

    // Update grade when score changes
    $(document).on('input', '.score-input', function() {
        var score = parseFloat($(this).val());
        var gradeSelect = $(this).closest('.form-group').find('.grade-select');
        
        if (!isNaN(score)) {
            var grade = calculateGrade(score);
            gradeSelect.val(grade);
        }
    });

    function calculateGrade(score) {
        if (score >= 70) return 'A';
        if (score >= 60) return 'B';
        if (score >= 50) return 'C';
        if (score >= 45) return 'D';
        if (score >= 40) return 'E';
        return 'F';
    }
});

