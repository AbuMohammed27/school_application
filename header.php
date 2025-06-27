<?php
// Add these require statements at the very top
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

// Rest of your header.php content
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle : 'School Application System' ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <a href="<?= SITE_URL ?>">School Application System</a>
            </div>
            <nav>
                <ul>
                    <?php if (isLoggedIn()): ?>
                        <li><a href="<?= SITE_URL ?>"><i class="fas fa-home"></i> Home</a></li>
                        <?php if ($_SESSION['user']['role'] == 'admin'): ?>
                            <li><a href="<?= SITE_URL ?>/admin/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                        <?php else: ?>
                            <li><a href="<?= SITE_URL ?>/applicant/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                        <?php endif; ?>
                        <li><a href="<?= SITE_URL ?>/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    <?php else: ?>
                        <li><a href="<?= SITE_URL ?>/login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                        <li><a href="<?= SITE_URL ?>/register.php"><i class="fas fa-user-plus"></i> Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>