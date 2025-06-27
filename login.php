<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';


if (isLoggedIn()) {
    header('Location: ' . SITE_URL . '/dashboard.php');
    exit;
}


$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    // Debug output
    error_log("Attempting login for: " . $username);
    
    try {
        $user = authenticate($username, $password);
        
        if ($user) {
            $_SESSION['user'] = [
                'user_id' => $user['user_id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role']
            ];
            
            // Debug
            error_log("Login successful for: " . $username);
            error_log("Session data: " . print_r($_SESSION, true));
            
            // Update last login
            $pdo->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?")
                ->execute([$user['user_id']]);
            
            // Redirect
            $redirect = ($user['role'] === 'admin') ? '/admin/dashboard.php' : '/applicant/dashboard.php';
            header('Location: ' . SITE_URL . $redirect);
            exit;
        } else {
            $error = "Invalid username or password";
            error_log("Login failed for: " . $username);
        }
    } catch (PDOException $e) {
        $error = "Database error during login";
        error_log("Login error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - School Application System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <div class="auth-form">
            <h2>Login to Your Account</h2>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-input">
                        <input type="password" name="password" id="password" required>
                        <button type="button" class="toggle-password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Login</button>
                </div>
                
                <div class="auth-links">
                    <a href="forgot_password.php">Forgot Password?</a>
                    <span>|</span>
                    <a href="register.php">Create an Account</a>
                </div>
            </form>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>