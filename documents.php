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

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['document'])) {
    $document_type = $_POST['document_type'];
    $file = $_FILES['document'];
    
    // Validate file
    $allowed_types = ['application/pdf', 'image/jpeg', 'image/png'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowed_types)) {
        $_SESSION['error'] = 'Only PDF, JPG, and PNG files are allowed.';
    } elseif ($file['size'] > $max_size) {
        $_SESSION['error'] = 'File size must be less than 5MB.';
    } else {
        // Generate unique filename
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'doc_' . $user_id . '_' . time() . '.' . $ext;
        $target_path = UPLOAD_DIR . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            // Save to database
            $stmt = $pdo->prepare("INSERT INTO documents (application_id, document_type, file_path) VALUES (?, ?, ?)");
            $stmt->execute([$application_id, $document_type, $filename]);
            
            $_SESSION['message'] = 'Document uploaded successfully!';
        } else {
            $_SESSION['error'] = 'Failed to upload document.';
        }
    }
    
    header("Location: documents.php?application_id=$application_id");
    exit;
}

// Get documents for this application
$documents = [];
if ($application_id) {
    $stmt = $pdo->prepare("SELECT * FROM documents WHERE application_id = ? ORDER BY upload_date DESC");
    $stmt->execute([$application_id]);
    $documents = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Upload</title>
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
                <h2>Document Upload</h2>
                
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
                
                <?php if ($application_id): ?>
                    <div class="upload-box">
                        <h3>Upload New Document</h3>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="document_type">Document Type</label>
                                <select name="document_type" id="document_type" required>
                                    <option value="">-- Select document type --</option>
                                    <option value="transcript">Transcript</option>
                                    <option value="certificate">Certificate</option>
                                    <option value="passport">Passport</option>
                                    <option value="photo">Passport Photo</option>
                                    <option value="recommendation">Recommendation Letter</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="document">Choose File</label>
                                <input type="file" name="document" id="document" required>
                                <small>Max file size: 5MB (PDF, JPG, PNG)</small>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Upload Document</button>
                        </form>
                    </div>
                    
                    <div class="documents-list">
                        <h3>Uploaded Documents</h3>
                        <?php if (empty($documents)): ?>
                            <p>No documents uploaded yet.</p>
                        <?php else: ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>File</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($documents as $doc): ?>
                                    <tr>
                                        <td><?= ucfirst($doc['document_type']) ?></td>
                                        <td>
                                            <a href="../uploads/documents/<?= htmlspecialchars($doc['file_path']) ?>" target="_blank">
                                                View Document
                                            </a>
                                        </td>
                                        <td><?= date('M d, Y', strtotime($doc['upload_date'])) ?></td>
                                        <td>
                                            <span class="status-badge <?= $doc['is_verified'] ? 'verified' : 'pending' ?>">
                                                <?= $doc['is_verified'] ? 'Verified' : 'Pending' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-danger delete-document" data-id="<?= $doc['document_id'] ?>">
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
                        <p>Please select an application from your dashboard to upload documents.</p>
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
        // Delete document
        $('.delete-document').click(function() {
            if (confirm('Are you sure you want to delete this document?')) {
                const docId = $(this).data('id');
                $.post('../includes/delete_document.php', {document_id: docId}, function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Failed to delete document: ' + response.message);
                    }
                });
            }
        });
    });
    </script>
</body>
</html>