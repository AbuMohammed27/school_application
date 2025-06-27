<?php
require_once 'config.php';
require_once 'db.php';

header('Content-Type: application/json');

if (isset($_GET['program_id'])) {
    $program_id = (int)$_GET['program_id'];
    $stmt = $pdo->prepare("SELECT program_id, program_name, program_category FROM programs WHERE program_id = ?");
    $stmt->execute([$program_id]);
    $program = $stmt->fetch();
    
    if ($program) {
        echo json_encode($program);
    } else {
        echo json_encode(['error' => 'Program not found']);
    }
} else {
    echo json_encode(['error' => 'No program_id provided']);
}
?>