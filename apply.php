<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Check if user is logged in and is an applicant
if (!isLoggedIn() || $_SESSION['user']['role'] != 'applicant') {
    header('Location: ../login.php');
    exit;
}

// Define program categories and their respective programs
$programCategories = [
    'diploma' => [
        'Accounting',
        'Public Administration',
        'Librarianship',
        'Adult Education',
        'Youth & Development',
        'Health Communication',
        'Information Technology',
        'Medical Laboratory Science',
        'French for Professions',
        'Business Administration',
        'Computerized Accounting',
        'Public Relations',
        'Marketing',
        'Interior Design'
    ],
    'undergraduate' => [
        'Arts',
        'Sciences',
        'Law',
        'Business',
        'Engineering',
        'Health Sciences',
        'Education',
        'Marine Sciences',
        'Architecture',
        'Applied Sciences'
    ],
    'postgraduate' => [
        'Agriculture',
        'Arts',
        'Business',
        'Engineering',
        'Health Sciences',
        'Law',
        'Social Sciences',
        'Health Informatics',
        'Biotechnology',
        'Project Management',
        'Strategic Leadership'
    ]
];

$user_id = $_SESSION['user']['user_id'];
$application_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$application = null;

// If editing existing application
if ($application_id) {
    $stmt = $pdo->prepare("SELECT * FROM applications WHERE application_id = ? AND applicant_id = ?");
    $stmt->execute([$application_id, $user_id]);
    $application = $stmt->fetch();
    
    if (!$application) {
        header('Location: apply.php');
        exit;
    }
}

// Process form submission
// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $program_name = $_POST['program_name'];
    $program_category = $_POST['program_type'];
    $status = isset($_POST['submit']) ? 'submitted' : 'draft';
    
    // First get the program_id from the programs table
    $stmt = $pdo->prepare("SELECT program_id FROM programs WHERE program_name = ? AND program_category = ?");
    $stmt->execute([$program_name, $program_category]);
    $program = $stmt->fetch();
    
    if (!$program) {
        $_SESSION['error'] = "Selected program not found in database";
        header("Location: apply.php" . ($application_id ? "?id=$application_id" : ""));
        exit;
    }
    
    $program_id = $program['program_id'];
    
    if ($application_id) {
        // Update existing application
        $stmt = $pdo->prepare("UPDATE applications SET program_id = ?, status = ? WHERE application_id = ?");
        $stmt->execute([$program_id, $status, $application_id]);
    } else {
        // Create new application
        $stmt = $pdo->prepare("INSERT INTO applications (applicant_id, program_id, status) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $program_id, $status]);
        $application_id = $pdo->lastInsertId();
    }
    
    $_SESSION['message'] = $status == 'submitted' ? 
        'Application submitted successfully!' : 'Application saved as draft.';
    header("Location: apply.php?id=$application_id");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $application_id ? 'Edit' : 'New' ?> Application</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .program-type-container {
            margin-bottom: 20px;
        }
        .program-options {
            display: none;
            margin-top: 10px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .program-options.active {
            display: block;
        }
        .program-option {
            margin-bottom: 10px;
        }
        .program-option input[type="radio"] {
            margin-right: 10px;
        }
        .program-option label {
            cursor: pointer;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main class="container">
        <div class="dashboard">
            <aside class="sidebar">
                <!-- Same sidebar as dashboard -->
            </aside>
            
            <section class="main-content">
                <h2><?= $application_id ? 'Edit' : 'New' ?> Application</h2>
                
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>
                
                <form id="applicationForm" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Select Program Type</label>
                        <div class="program-type-container">
                            <select name="program_type" id="program_type" class="form-control" required>
                                <option value="">-- Select Program Type --</option>
                                <option value="diploma">Diploma Programs</option>
                                <option value="undergraduate">Undergraduate Programs</option>
                                <option value="postgraduate">Postgraduate Programs</option>
                            </select>
                        </div>
                        
                        <!-- Diploma Programs -->
                        <div id="diploma-programs" class="program-options">
                            <h4>Diploma Programs</h4>
                            <?php foreach ($programCategories['diploma'] as $program): ?>
                                <div class="program-option">
                                    <input type="radio" name="program_name" id="diploma_<?= str_replace(' ', '_', strtolower($program)) ?>" value="<?= htmlspecialchars($program) ?>" data-category="diploma">
                                    <label for="diploma_<?= str_replace(' ', '_', strtolower($program)) ?>"><?= htmlspecialchars($program) ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Undergraduate Programs -->
                        <div id="undergraduate-programs" class="program-options">
                            <h4>Undergraduate Programs</h4>
                            <?php foreach ($programCategories['undergraduate'] as $program): ?>
                                <div class="program-option">
                                    <input type="radio" name="program_name" id="undergrad_<?= str_replace(' ', '_', strtolower($program)) ?>" value="<?= htmlspecialchars($program) ?>" data-category="undergraduate">
                                    <label for="undergrad_<?= str_replace(' ', '_', strtolower($program)) ?>"><?= htmlspecialchars($program) ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Postgraduate Programs -->
                        <div id="postgraduate-programs" class="program-options">
                            <h4>Postgraduate Programs</h4>
                            <?php foreach ($programCategories['postgraduate'] as $program): ?>
                                <div class="program-option">
                                    <input type="radio" name="program_name" id="postgrad_<?= str_replace(' ', '_', strtolower($program)) ?>" value="<?= htmlspecialchars($program) ?>" data-category="postgraduate">
                                    <label for="postgrad_<?= str_replace(' ', '_', strtolower($program)) ?>"><?= htmlspecialchars($program) ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Hidden field to store program_id -->
                        <input type="hidden" name="program_id" id="program_id" value="">
                    </div>
                    
                    <div id="programRequirements" class="requirements-box">
                        <!-- Program requirements will be loaded here via AJAX -->
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="save" class="btn btn-secondary">Save Draft</button>
                        <button type="submit" name="submit" class="btn btn-primary">Submit Application</button>
                    </div>
                </form>
                
                <?php if ($application_id): ?>
                    <div class="application-sections">
                        <div class="section-card" onclick="location.href='documents.php?application_id=<?= $application_id ?>'">
                            <i class="fas fa-file-upload"></i>
                            <h3>Documents</h3>
                            <p>Upload required documents</p>
                        </div>
                        
                        <div class="section-card" onclick="location.href='international.php?application_id=<?= $application_id ?>'">
                            <i class="fas fa-globe"></i>
                            <h3>International</h3>
                            <p>Provide international qualifications</p>
                        </div>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/script.js"></script>
    <script>
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
            $('#program_id').val('');
            $('#programRequirements').html('');
        });
        
        // When a program is selected
        $('input[name="program_name"]').change(function() {
            const programName = $(this).val();
            const programCategory = $(this).data('category');
            
            // In a real application, you would likely make an AJAX call here
            // to get the program_id from your database based on the program name
            // For this example, we'll just use a placeholder
            $('#program_id').val(programCategory + '_' + programName.replace(/\s+/g, '_').toLowerCase());
            
            // Load program requirements (simulated)
            $('#programRequirements').html('<div class="alert alert-info">Loading requirements for ' + programName + '...</div>');
            
            // In your actual implementation, you would use:
            // $.get('../includes/get_requirements.php', {program_name: programName}, function(data) {
            //     $('#programRequirements').html(data);
            // });
        });
        
        // Initialize form if editing existing application
        <?php if ($application): ?>
            // You would need to fetch the program details from your database
            // and set the appropriate values here
            // This is just a placeholder implementation
            // $('#program_type').val('diploma').trigger('change');
            // $('input[name="program_name"][value="Accounting"]').prop('checked', true);
        <?php endif; ?>
    });
    </script>
</body>
</html>