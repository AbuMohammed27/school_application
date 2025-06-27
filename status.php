<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!isLoggedIn() || $_SESSION['user']['role'] != 'applicant') {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user']['user_id'];
$application_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get all applications
$stmt = $pdo->prepare("SELECT a.*, p.program_name FROM applications a 
                      JOIN programs p ON a.program_id = p.program_id 
                      WHERE a.applicant_id = ? ORDER BY a.application_date DESC");
$stmt->execute([$user_id]);
$applications = $stmt->fetchAll();

// Get specific application details if ID is provided
$application = null;
$documents = [];
$qualifications = [];

if ($application_id) {
    // Verify application belongs to user
    foreach ($applications as $app) {
        if ($app['application_id'] == $application_id) {
            $application = $app;
            break;
        }
    }
    
    if ($application) {
        // Get documents
        $stmt = $pdo->prepare("SELECT * FROM documents WHERE application_id = ?");
        $stmt->execute([$application_id]);
        $documents = $stmt->fetchAll();
        
        // Get international qualifications
        $stmt = $pdo->prepare("SELECT * FROM international_qualifications WHERE application_id = ?");
        $stmt->execute([$application_id]);
        $qualifications = $stmt->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Status</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main class="container">
        <div class="dashboard">
            <aside class="sidebar">
                <!-- Same sidebar as dashboard -->
                  <div class="profile-summary">
                    <div class="avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                   
                </div>
                <nav>
                    <ul>
                        <li class="active"><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                        <li><a href="profile.php"><i class="fas fa-user"></i> My Profile</a></li>
                        <li><a href="apply.php"><i class="fas fa-file-alt"></i> New Application</a></li>
                        <li><a href="documents.php"><i class="fas fa-file-upload"></i> My Documents</a></li>
                        <li><a href="status.php"><i class="fas fa-spinner"></i> Application Status</a></li>
                        <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </nav>
            </aside>
            
            <section class="main-content">
                <h2>Application Status</h2>
                
                <div class="status-container">
                    <div class="applications-selector">
                        <h3>Your Applications</h3>
                        <select id="applicationSelector" class="form-control">
                            <option value="">-- Select an application --</option>
                            <?php foreach ($applications as $app): ?>
                                <option value="<?= $app['application_id'] ?>" 
                                    <?= ($application_id == $app['application_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($app['program_name']) ?> - 
                                    <?= date('M d, Y', strtotime($app['application_date'])) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <?php if ($application): ?>
                        <div class="status-details">
                            <h3>Application Details</h3>
                            <div class="detail-card">
                                <h4>Program: <?= htmlspecialchars($application['program_name']) ?></h4>
                                <p>Application Date: <?= date('F j, Y', strtotime($application['application_date'])) ?></p>
                                <p>Status: <span class="status-badge <?= $application['status'] ?>"><?= ucfirst($application['status']) ?></span></p>
                                
                                <?php if ($application['status'] != 'draft' && !empty($application['comments'])): ?>
                                    <div class="comments">
                                        <h5>Admissions Comments:</h5>
                                        <p><?= nl2br(htmlspecialchars($application['comments'])) ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="progress-tracker">
                                <h4>Application Progress</h4>
                                <div class="steps">
                                    <div class="step <?= $application['status'] != 'draft' ? 'completed' : '' ?>">
                                        <div class="step-number">1</div>
                                        <div class="step-info">
                                            <h5>Application Submitted</h5>
                                            <p><?= date('M d, Y', strtotime($application['application_date'])) ?></p>
                                        </div>
                                    </div>
                                    
                                    <div class="step <?= in_array($application['status'], ['under_review', 'accepted', 'rejected', 'waitlisted']) ? 'completed' : '' ?>">
                                        <div class="step-number">2</div>
                                        <div class="step-info">
                                            <h5>Under Review</h5>
                                            <?php if (in_array($application['status'], ['under_review', 'accepted', 'rejected', 'waitlisted'])): ?>
                                                <p>Review started</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="step <?= in_array($application['status'], ['accepted', 'rejected', 'waitlisted']) ? 'completed' : '' ?>">
                                        <div class="step-number">3</div>
                                        <div class="step-info">
                                            <h5>Decision Made</h5>
                                            <?php if (in_array($application['status'], ['accepted', 'rejected', 'waitlisted'])): ?>
                                                <p>Decision: <?= ucfirst($application['status']) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="requirements-check">
                                <h4>Requirements Checklist</h4>
                                <ul>
                                    <li class="<?= !empty($documents) ? 'completed' : '' ?>">
                                        Documents Uploaded
                                        <span><?= count($documents) ?> files</span>
                                    </li>
                                    <li class="<?= $application['status'] != 'draft' ? 'completed' : '' ?>">
                                        Application Submitted
                                    </li>
                                    <li class="<?= $application['status'] == 'under_review' ? 'completed' : '' ?>">
                                        Under Review
                                    </li>
                                    <li class="<?= in_array($application['status'], ['accepted', 'rejected', 'waitlisted']) ? 'completed' : '' ?>">
                                        Decision Made
                                    </li>
                                </ul>
                            </div>
                            
                            <?php if (!empty($qualifications)): ?>
                                <div class="international-info">
                                    <h4>International Qualifications</h4>
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Country</th>
                                                <th>Qualification</th>
                                                <th>Translated</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($qualifications as $qual): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($qual['country']) ?></td>
                                                <td><?= htmlspecialchars($qual['qualification_name']) ?></td>
                                                <td><?= $qual['is_translated'] ? 'Yes' : 'No' ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <p>Select an application to view its status and progress.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/script.js"></script>
    <script>
    $(document).ready(function() {
        // Application selector change
        $('#applicationSelector').change(function() {
            const appId = $(this).val();
            if (appId) {
                window.location.href = 'status.php?id=' + appId;
            }
        });
    });
    </script>
</body>
</html>