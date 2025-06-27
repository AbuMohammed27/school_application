<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || $_SESSION['user']['role'] != 'admin') {
    header('Location: ../login.php');
    exit;
}

// Get all applications with applicant and program details
$query = "SELECT a.*, u.username, p.program_name, 
          CONCAT(ap.first_name, ' ', ap.last_name) AS applicant_name
          FROM applications a
          JOIN users u ON a.applicant_id = u.user_id
          JOIN applicant_profiles ap ON u.user_id = ap.user_id
          JOIN programs p ON a.program_id = p.program_id
          ORDER BY a.application_date DESC";
$applications = $pdo->query($query)->fetchAll();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $application_id = (int)$_POST['application_id'];
    $status = $_POST['status'];
    $comments = $_POST['comments'] ?? '';
    
    $stmt = $pdo->prepare("UPDATE applications SET status = ?, comments = ? WHERE application_id = ?");
    $stmt->execute([$status, $comments, $application_id]);
    
    $_SESSION['message'] = 'Application status updated successfully!';
    header("Location: applications.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Applications</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main class="container">
        <div class="dashboard">
            <?php include 'admin_sidebar.php'; ?>
            
            <section class="main-content">
                <h2>Applications Management</h2>
                
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>
                
                <div class="filter-section">
                    <form method="GET" class="filter-form">
                        <div class="form-group">
                            <label for="status">Filter by Status:</label>
                            <select name="status" id="status">
                                <option value="">All Applications</option>
                                <option value="submitted">Submitted</option>
                                <option value="under_review">Under Review</option>
                                <option value="accepted">Accepted</option>
                                <option value="rejected">Rejected</option>
                                <option value="waitlisted">Waitlisted</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </form>
                </div>
                
                <div class="applications-table">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Applicant</th>
                                <th>Program</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($applications as $app): ?>
                            <tr>
                                <td><?= $app['application_id'] ?></td>
                                <td><?= htmlspecialchars($app['applicant_name']) ?></td>
                                <td><?= htmlspecialchars($app['program_name']) ?></td>
                                <td><?= date('M d, Y', strtotime($app['application_date'])) ?></td>
                                <td>
                                    <span class="status-badge <?= $app['status'] ?>">
                                        <?= ucfirst($app['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary view-application" data-id="<?= $app['application_id'] ?>">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>
    
    <!-- Application Detail Modal -->
    <div id="applicationModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h3>Application Details</h3>
            <div id="applicationDetails"></div>
            
            <h4>Update Status</h4>
            <form id="statusForm" method="POST">
                <input type="hidden" name="application_id" id="modalApplicationId">
                <div class="form-group">
                    <label for="status">Status</label>
                    <select name="status" id="modalStatus" required>
                        <option value="submitted">Submitted</option>
                        <option value="under_review">Under Review</option>
                        <option value="accepted">Accepted</option>
                        <option value="rejected">Rejected</option>
                        <option value="waitlisted">Waitlisted</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="comments">Comments</label>
                    <textarea name="comments" id="comments" rows="3"></textarea>
                </div>
                <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
            </form>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/script.js"></script>
    <script>
    $(document).ready(function() {
        // View application details
        $('.view-application').click(function() {
            const appId = $(this).data('id');
            $.get('../includes/get_application.php', {id: appId}, function(data) {
                $('#applicationDetails').html(data);
                $('#modalApplicationId').val(appId);
                $('#modalStatus').val($('#currentStatus').val());
                $('#applicationModal').show();
            });
        });
        
        // Close modal
        $('.close-modal').click(function() {
            $('#applicationModal').hide();
        });
        
        // Close modal when clicking outside
        $(window).click(function(event) {
            if (event.target == $('#applicationModal')[0]) {
                $('#applicationModal').hide();
            }
        });
    });
    </script>
</body>
</html>