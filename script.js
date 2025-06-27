// Document ready function
$(document).ready(function() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Handle file input change
    $('input[type="file"]').change(function() {
        const fileName = $(this).val().split('\\').pop();
        if (fileName) {
            $(this).next('.custom-file-label').html(fileName);
        }
    });
    
    // Form validation
    $('form').submit(function(e) {
        let isValid = true;
        $(this).find('[required]').each(function() {
            if (!$(this).val()) {
                $(this).addClass('is-invalid');
                isValid = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            $(this).find('.is-invalid').first().focus();
        }
    });
    
    // Remove validation on input
    $('input, select, textarea').on('input change', function() {
        if ($(this).val()) {
            $(this).removeClass('is-invalid');
        }
    });
    
    // Toggle password visibility
    $('.toggle-password').click(function() {
        const input = $(this).siblings('input');
        const type = input.attr('type') === 'password' ? 'text' : 'password';
        input.attr('type', type);
        $(this).find('i').toggleClass('fa-eye fa-eye-slash');
    });
    
    // Handle AJAX loading
    $(document).on({
        ajaxStart: function() { 
            $('body').addClass('loading'); 
        },
        ajaxStop: function() { 
            $('body').removeClass('loading'); 
        }    
    });
});

// Loading overlay
$('body').append(`
    <div class="loading-overlay">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
`);

// CSS for loading overlay
$('head').append(`
    <style>
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255,255,255,0.7);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        
        body.loading .loading-overlay {
            display: flex;
        }
        
        .is-invalid {
            border-color: #dc3545 !important;
        }
        
        .invalid-feedback {
            display: none;
            color: #dc3545;
            font-size: 0.875rem;
        }
        
        .is-invalid + .invalid-feedback {
            display: block;
        }
    </style>
`);

$(document).ready(function() {
    // Ensure links work properly
    $('a.btn').on('click', function(e) {
        // Only prevent default if link is "#"
        if ($(this).attr('href') === '#') {
            e.preventDefault();
        }
    });
});

// Add this to your script.js
console.log("Script loaded");
$('a.btn').each(function() {
    console.log("Button found:", $(this).attr('href'));
});

// Check if clicks are being registered
$('a.btn').on('click', function() {
    console.log("Button clicked:", $(this).attr('href'));
});

$(document).ready(function() {
    // Show/hide program options based on selected type
    $('#program_type').change(function() {
        const programType = $(this).val();
        
        // Hide all program options
        $('.program-options').removeClass('active');
        
        // Show selected program type
        if (programType) {
            $('#' + programType + '-programs').addClass('active');
        }
        
        // Clear any selected program
        $('input[name="program_name"]').prop('checked', false);
        $('#programRequirements').html('');
    });
    
    // When a program is selected
    $('input[name="program_name"]').change(function() {
        const programName = $(this).val();
        
        // Load program requirements via AJAX
        $.get('../includes/get_requirements.php', {
            program_name: programName,
            program_type: $('#program_type').val()
        }, function(data) {
            $('#programRequirements').html(data);
        });
    });
    
    // Initialize form if editing existing application
    <?php if ($application): ?>
        // Get the program details for the existing application
        $.get('../includes/get_program_details.php', {
            program_id: <?= $application['program_id'] ?>
        }, function(data) {
            if (data) {
                $('#program_type').val(data.program_category).trigger('change');
                $('input[name="program_name"][value="' + data.program_name + '"]').prop('checked', true);
                
                // Load requirements
                $.get('../includes/get_requirements.php', {
                    program_name: data.program_name,
                    program_type: data.program_category
                }, function(requirements) {
                    $('#programRequirements').html(requirements);
                });
            }
        });
    <?php endif; ?>
});