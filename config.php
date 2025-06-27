<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'school_application_system');

// Site configuration
define('SITE_URL', 'http://localhost/school-application');
define('UPLOAD_DIR', __DIR__ . '/../uploads/documents/');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();
?>