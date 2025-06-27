<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Check if user is logged in and is an applicant
if (!isLoggedIn() || $_SESSION['user']['role'] != 'applicant') {
    header('Location: ../login.php');
    exit;
}

// Get applicant data
$user_id = $_SESSION['user']['user_id'];
$stmt = $pdo->prepare("SELECT * FROM applicant_profiles WHERE user_id = ?");
$stmt->execute([$user_id]);
$profile = $stmt->fetch();

// Get applications
$stmt = $pdo->prepare("SELECT a.*, p.program_name FROM applications a 
                      JOIN programs p ON a.program_id = p.program_id 
                      WHERE a.applicant_id = ?");
$stmt->execute([$user_id]);
$applications = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applicant Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main class="container">
        <div class="dashboard">
            <aside class="sidebar">
                <div class="profile-summary">
                    <div class="avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <h3><?= htmlspecialchars($profile['first_name'] . ' ' . $profile['last_name']) ?></h3>
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
                <h2>Application Dashboard</h2>
                
                <div class="stats-cards">
                    <div class="stat-card">
                        <h3>Active Applications</h3>
                        <p><?= count(array_filter($applications, fn($app) => $app['status'] != 'draft')) ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Drafts</h3>
                        <p><?= count(array_filter($applications, fn($app) => $app['status'] == 'draft')) ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Completed</h3>
                        <p><?= count(array_filter($applications, fn($app) => $app['status'] == 'submitted')) ?></p>
                    </div>
                </div>
                
                <div class="applications-list">
                    <h3>Your Applications</h3>
                    <?php if (empty($applications)): ?>
                        <p>You haven't started any applications yet.</p>
                        <a href="apply.php" class="btn btn-primary">Start New Application</a>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Program</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($applications as $application): ?>
                                <tr>
                                    <td><?= htmlspecialchars($application['program_name']) ?></td>
                                    <td><?= date('M d, Y', strtotime($application['application_date'])) ?></td>
                                    <td><span class="status-badge <?= $application['status'] ?>"><?= ucfirst($application['status']) ?></span></td>
                                    <td>
                                        <a href="application.php?id=<?= $application['application_id'] ?>" class="btn btn-sm btn-primary">View</a>
                                        <?php if ($application['status'] == 'draft'): ?>
                                            <a href="apply.php?id=<?= $application['application_id'] ?>" class="btn btn-sm btn-secondary">Continue</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/script.js"></script>
</body>
</html>