<?php
require_once 'config.php';
require_once 'db.php';

if (isset($_GET['program_name']) && isset($_GET['program_type'])) {
    $program_name = $_GET['program_name'];
    $program_type = $_GET['program_type'];
    
    $stmt = $pdo->prepare("SELECT requirements FROM programs WHERE program_name = ? AND program_category = ?");
    $stmt->execute([$program_name, $program_type]);
    $program = $stmt->fetch();
    
    if ($program && !empty($program['requirements'])) {
        echo '<div class="program-requirements">';
        echo '<h4>Program Requirements</h4>';
        echo nl2br(htmlspecialchars($program['requirements']));
        echo '</div>';
    } else {
        echo '<div class="alert alert-info">No specific requirements listed for this program.</div>';
    }
} else {
    echo '<div class="alert alert-warning">Program information not provided.</div>';
}
?>