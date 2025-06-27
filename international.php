<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!isLoggedIn() || $_SESSION['user']['role'] != 'applicant') {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user']['user_id'];
$application_id = isset($_GET['application_id']) ? (int)$_GET['application_id'] : 0;

// Verify application belongs to user
if ($application_id) {
    $stmt = $pdo->prepare("SELECT * FROM applications WHERE application_id = ? AND applicant_id = ?");
    $stmt->execute([$application_id, $user_id]);
    $application = $stmt->fetch();
    
    if (!$application) {
        header('Location: dashboard.php');
        exit;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $country = $_POST['country'];
    $qualification_name = $_POST['qualification_name'];
    $is_translated = isset($_POST['is_translated']) ? 1 : 0;
    $translator_details = $_POST['translator_details'] ?? '';
    
    $stmt = $pdo->prepare("INSERT INTO international_qualifications 
                          (application_id, country, qualification_name, is_translated, translator_details)
                          VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$application_id, $country, $qualification_name, $is_translated, $translator_details]);
    
    $_SESSION['message'] = 'International qualification added successfully!';
    header("Location: international.php?application_id=$application_id");
    exit;
}

// Get existing qualifications
$qualifications = [];
if ($application_id) {
    $stmt = $pdo->prepare("SELECT * FROM international_qualifications WHERE application_id = ?");
    $stmt->execute([$application_id]);
    $qualifications = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>International Qualifications</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main class="container">
        <div class="dashboard">
            <aside class="sidebar">
                <!-- Same sidebar as dashboard -->
            </aside>
            
            <section class="main-content">
                <h2>International Qualifications</h2>
                
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>
                
                <?php if ($application_id): ?>
                    <div class="form-box">
                        <h3>Add International Qualification</h3>
                        <form method="POST">
                            <div class="form-group">
                                <label for="country">Country</label>
                                <select name="country" id="country" required>
                                    <option value="">-- Select country --</option>
                                    <?php
                                    $countries = $pdo->query("SELECT DISTINCT nationality FROM applicant_profiles")->fetchAll(PDO::FETCH_COLUMN);
                                    foreach ($countries as $country): ?>
                                        <option value="<?= htmlspecialchars($country) ?>"><?= htmlspecialchars($country) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="qualification_name">Qualification Name</label>
                                <input type="text" name="qualification_name" id="qualification_name" required>
                            </div>
                            
                            <div class="form-group checkbox-group">
                                <input type="checkbox" name="is_translated" id="is_translated">
                                <label for="is_translated">This document is translated to English</label>
                            </div>
                            
                            <div class="form-group" id="translatorDetails" style="display: none;">
                                <label for="translator_details">Translator Details</label>
                                <textarea name="translator_details" id="translator_details"></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Add Qualification</button>
                        </form>
                    </div>
                    
                    <div class="qualifications-list">
                        <h3>Your Qualifications</h3>
                        <?php if (empty($qualifications)): ?>
                            <p>No international qualifications added yet.</p>
                        <?php else: ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Country</th>
                                        <th>Qualification</th>
                                        <th>Translated</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($qualifications as $qual): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($qual['country']) ?></td>
                                        <td><?= htmlspecialchars($qual['qualification_name']) ?></td>
                                        <td><?= $qual['is_translated'] ? 'Yes' : 'No' ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-danger delete-qualification" data-id="<?= $qual['qualification_id'] ?>">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <p>Please select an application from your dashboard to add international qualifications.</p>
                        <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
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
        // Show/hide translator details
        $('#is_translated').change(function() {
            $('#translatorDetails').toggle(this.checked);
        });
        
        // Delete qualification
        $('.delete-qualification').click(function() {
            if (confirm('Are you sure you want to delete this qualification?')) {
                const qualId = $(this).data('id');
                $.post('../includes/delete_qualification.php', {qualification_id: qualId}, function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Failed to delete qualification: ' + response.message);
                    }
                });
            }
        });
    });
    </script>
</body>
</html>