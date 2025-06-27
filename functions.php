<?php
require_once 'config.php';
require_once 'db.php';

/**
 * Redirect to specified URL
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user']);
}

/**
 * Sanitize input data
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Generate random password
 */
function generatePassword($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $password;
}

/**
 * Get program requirements (AJAX endpoint)
 */
if (isset($_GET['action']) && $_GET['action'] == 'get_requirements') {
    $program_id = (int)$_GET['program_id'];
    $stmt = $pdo->prepare("SELECT requirements FROM programs WHERE program_id = ?");
    $stmt->execute([$program_id]);
    $program = $stmt->fetch();
    
    if ($program) {
        echo '<div class="requirements-content">';
        echo '<h4>Program Requirements</h4>';
        echo nl2br(htmlspecialchars($program['requirements']));
        echo '</div>';
    } else {
        echo '<div class="alert alert-info">No requirements found for this program.</div>';
    }
    exit;
}

/**
 * Delete document (AJAX endpoint)
 */
if (isset($_POST['action']) && $_POST['action'] == 'delete_document') {
    $response = ['success' => false];
    
    if (isLoggedIn()) {
        $document_id = (int)$_POST['document_id'];
        $user_id = $_SESSION['user']['user_id'];
        
        // Verify document belongs to user
        $stmt = $pdo->prepare("SELECT d.* FROM documents d 
                              JOIN applications a ON d.application_id = a.application_id
                              WHERE d.document_id = ? AND a.applicant_id = ?");
        $stmt->execute([$document_id, $user_id]);
        $document = $stmt->fetch();
        
        if ($document) {
            // Delete file
            $file_path = UPLOAD_DIR . $document['file_path'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            
            // Delete from database
            $stmt = $pdo->prepare("DELETE FROM documents WHERE document_id = ?");
            $stmt->execute([$document_id]);
            
            $response['success'] = true;
        } else {
            $response['message'] = 'Document not found or access denied.';
        }
    } else {
        $response['message'] = 'Authentication required.';
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

/**
 * Delete qualification (AJAX endpoint)
 */
if (isset($_POST['action']) && $_POST['action'] == 'delete_qualification') {
    $response = ['success' => false];
    
    if (isLoggedIn()) {
        $qualification_id = (int)$_POST['qualification_id'];
        $user_id = $_SESSION['user']['user_id'];
        
        // Verify qualification belongs to user
        $stmt = $pdo->prepare("SELECT iq.* FROM international_qualifications iq 
                              JOIN applications a ON iq.application_id = a.application_id
                              WHERE iq.qualification_id = ? AND a.applicant_id = ?");
        $stmt->execute([$qualification_id, $user_id]);
        $qualification = $stmt->fetch();
        
        if ($qualification) {
            // Delete from database
            $stmt = $pdo->prepare("DELETE FROM international_qualifications WHERE qualification_id = ?");
            $stmt->execute([$qualification_id]);
            
            $response['success'] = true;
        } else {
            $response['message'] = 'Qualification not found or access denied.';
        }
    } else {
        $response['message'] = 'Authentication required.';
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>