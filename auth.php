<?php
require_once 'config.php';
require_once 'db.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 * @return bool True if user is logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user']);
}

/**
 * Check if current user has admin privileges
 * @return bool True if user is admin, false otherwise
 */
function isAdmin() {
    return isLoggedIn() && $_SESSION['user']['role'] === 'admin';
}

/**
 * Check if current user is an applicant
 * @return bool True if user is applicant, false otherwise
 */
function isApplicant() {
    return isLoggedIn() && $_SESSION['user']['role'] === 'applicant';
}

/**
 * Redirect to login page if not logged in
 * @param string $role Required role (optional)
 */
function requireLogin($role = null) {
    if (!isLoggedIn()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header('Location: login.php');
        exit;
    }

    if ($role && $_SESSION['user']['role'] !== $role) {
        header('HTTP/1.0 403 Forbidden');
        echo 'You do not have permission to access this page.';
        exit;
    }
}

/**
 * Authenticate user credentials
 * @param string $username
 * @param string $password
 * @return bool|array Returns user data if successful, false otherwise
 */
function authenticate($username, $password) {
    global $pdo;
    
    // Debug
    error_log("Authenticating user: " . $username);
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if (!$user) {
        error_log("User not found: " . $username);
        return false;
    }

    function isSuperAdmin() {
    return (isLoggedIn() && $_SESSION['user']['role'] === 'admin');
}

function canEditApplications() {
    return (isLoggedIn() && in_array($_SESSION['user']['role'], ['admin', 'reviewer']));
}
    
    // Debug password verification
    error_log("Stored hash: " . $user['password']);
    error_log("Input password: " . $password);
    error_log("Verification result: " . (password_verify($password, $user['password']) ? 'Match' : 'No match'));
    
    return password_verify($password, $user['password']) ? $user : false;
}
/**
 * Set user session after successful login
 * @param array $user User data from database
 */
function setUserSession($user) {
    $_SESSION['user'] = [
        'user_id' => $user['user_id'],
        'username' => $user['username'],
        'email' => $user['email'],
        'role' => $user['role']
    ];
    
    // Update last login time
    global $pdo;
    $pdo->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?")
        ->execute([$user['user_id']]);
}

/**
 * Destroy user session (logout)
 */
function logout() {
    // Unset all session variables
    $_SESSION = array();
    
    // Destroy the session
    session_destroy();
    
    // Delete the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
}

/**
 * Check if password meets complexity requirements
 * @param string $password
 * @return bool True if password is valid, false otherwise
 */
function validatePassword($password) {
    // Minimum 8 characters, at least one letter and one number
    return preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/', $password);
}

/**
 * Hash password using bcrypt
 * @param string $password
 * @return string Hashed password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}