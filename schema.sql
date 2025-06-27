CREATE DATABASE school_application_system;
USE school_application_system;

-- Users table
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('applicant', 'admin', 'reviewer') DEFAULT 'applicant',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME
);

-- Applicant profiles
CREATE TABLE applicant_profiles (
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
);

-- Programs table
CREATE TABLE programs (
    program_id INT AUTO_INCREMENT PRIMARY KEY,
    program_name VARCHAR(100) NOT NULL,
    description TEXT,
    duration VARCHAR(50),
    requirements TEXT,
    is_international BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE
);

-- Applications table
CREATE TABLE applications (
    application_id INT AUTO_INCREMENT PRIMARY KEY,
    applicant_id INT NOT NULL,
    program_id INT NOT NULL,
    application_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('draft', 'submitted', 'under_review', 'accepted', 'rejected', 'waitlisted') DEFAULT 'draft',
    comments TEXT,
    FOREIGN KEY (applicant_id) REFERENCES users(user_id),
    FOREIGN KEY (program_id) REFERENCES programs(program_id)
);

-- Documents table
CREATE TABLE documents (
    document_id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    document_type ENUM('transcript', 'certificate', 'passport', 'photo', 'recommendation', 'other') NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_verified BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (application_id) REFERENCES applications(application_id)
);

-- International qualifications
CREATE TABLE international_qualifications (
    qualification_id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    country VARCHAR(50) NOT NULL,
    qualification_name VARCHAR(100) NOT NULL,
    equivalency TEXT,
    is_translated BOOLEAN DEFAULT FALSE,
    translator_details TEXT,
    FOREIGN KEY (application_id) REFERENCES applications(application_id)
);

ALTER TABLE programs ADD COLUMN program_category ENUM('diploma', 'undergraduate', 'postgraduate') NOT NULL;
UPDATE programs SET program_category = 'diploma' WHERE program_name IN ('Accounting', 'Public Administration');
UPDATE programs SET program_category = 'undergraduate' WHERE program_name IN ('Arts', 'Sciences');
UPDATE programs SET program_category = 'postgraduate' WHERE program_name IN ('Agriculture', 'Arts');