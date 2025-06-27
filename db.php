<?php
require_once 'config.php';

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Create tables if they don't exist (for initial setup)
function createTables($pdo) {
    $sql = [
        "CREATE TABLE IF NOT EXISTS users (
            user_id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            role ENUM('applicant', 'admin', 'reviewer') DEFAULT 'applicant',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_login DATETIME
        )",
        
        "CREATE TABLE IF NOT EXISTS applicant_profiles (
            profile_id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNIQUE NOT NULL,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            date_of_birth DATE NOT NULL,
            gender ENUM('male', 'female', 'other') NOT NULL,
            nationality VARCHAR(50) NOT NULL,
            address TEXT NOT NULL,
            phone VARCHAR(20) NOT NULL,
            FOREIGN KEY (user_id) REFERENCES users(user_id)
        )",
        
        "CREATE TABLE IF NOT EXISTS programs (
            program_id INT AUTO_INCREMENT PRIMARY KEY,
            program_name VARCHAR(100) NOT NULL,
            description TEXT,
            duration VARCHAR(50),
            requirements TEXT,
            is_international BOOLEAN DEFAULT FALSE,
            is_active BOOLEAN DEFAULT TRUE
        )",
        
        "CREATE TABLE IF NOT EXISTS applications (
            application_id INT AUTO_INCREMENT PRIMARY KEY,
            applicant_id INT NOT NULL,
            program_id INT NOT NULL,
            application_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            status ENUM('draft', 'submitted', 'under_review', 'accepted', 'rejected', 'waitlisted') DEFAULT 'draft',
            comments TEXT,
            FOREIGN KEY (applicant_id) REFERENCES users(user_id),
            FOREIGN KEY (program_id) REFERENCES programs(program_id)
        )",
        
        "CREATE TABLE IF NOT EXISTS documents (
            document_id INT AUTO_INCREMENT PRIMARY KEY,
            application_id INT NOT NULL,
            document_type ENUM('transcript', 'certificate', 'passport', 'photo', 'recommendation', 'other') NOT NULL,
            file_path VARCHAR(255) NOT NULL,
            upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            is_verified BOOLEAN DEFAULT FALSE,
            FOREIGN KEY (application_id) REFERENCES applications(application_id)
        )",
        
        "CREATE TABLE IF NOT EXISTS international_qualifications (
            qualification_id INT AUTO_INCREMENT PRIMARY KEY,
            application_id INT NOT NULL,
            country VARCHAR(50) NOT NULL,
            qualification_name VARCHAR(100) NOT NULL,
            equivalency TEXT,
            is_translated BOOLEAN DEFAULT FALSE,
            translator_details TEXT,
            FOREIGN KEY (application_id) REFERENCES applications(application_id)
        )"
    ];
    
    foreach ($sql as $query) {
        $pdo->exec($query);
    }
    
    // Create admin user if not exists
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
    if ($stmt->fetchColumn() == 0) {
        $username = 'admin';
        $email = 'admin@school.edu';
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, 'admin')")
            ->execute([$username, $password, $email]);
    }
}

// Uncomment this line for initial setup
// createTables($pdo);
?>