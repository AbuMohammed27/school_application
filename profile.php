<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Check if user is logged in and is an applicant
if (!isLoggedIn() || $_SESSION['user']['role'] != 'applicant') {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user']['user_id'];

// Get applicant profile
$stmt = $pdo->prepare("SELECT * FROM applicant_profiles WHERE user_id = ?");
$stmt->execute([$user_id]);
$profile = $stmt->fetch();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $date_of_birth = $_POST['date_of_birth'];
    $gender = $_POST['gender'];
    $nationality = $_POST['nationality'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    
    if ($profile) {
        // Update existing profile
        $stmt = $pdo->prepare("UPDATE applicant_profiles SET 
                              first_name = ?, last_name = ?, date_of_birth = ?, gender = ?, 
                              nationality = ?, address = ?, phone = ?
                              WHERE user_id = ?");
        $stmt->execute([$first_name, $last_name, $date_of_birth, $gender, 
                       $nationality, $address, $phone, $user_id]);
    } else {
        // Create new profile
        $stmt = $pdo->prepare("INSERT INTO applicant_profiles 
                              (user_id, first_name, last_name, date_of_birth, gender, 
                              nationality, address, phone)
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $first_name, $last_name, $date_of_birth, $gender,
                       $nationality, $address, $phone]);
    }
    
    $_SESSION['message'] = 'Profile updated successfully!';
    header('Location: profile.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main class="container">
        <div class="dashboard">
            <aside class="sidebar">
                 <div class="profile-summary">
                    <div class="avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <h3><?= htmlspecialchars($profile['first_name'] . ' ' . $profile['last_name']) ?></h3>
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
                <h2>My Profile</h2>
                
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" name="first_name" id="first_name" 
                                   value="<?= htmlspecialchars($profile['first_name'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" name="last_name" id="last_name" 
                                   value="<?= htmlspecialchars($profile['last_name'] ?? '') ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="date_of_birth">Date of Birth</label>
                            <input type="date" name="date_of_birth" id="date_of_birth" 
                                   value="<?= htmlspecialchars($profile['date_of_birth'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="gender">Gender</label>
                            <select name="gender" id="gender" required>
                                <option value="">-- Select Gender --</option>
                                <option value="male" <?= ($profile['gender'] ?? '') == 'male' ? 'selected' : '' ?>>Male</option>
                                <option value="female" <?= ($profile['gender'] ?? '') == 'female' ? 'selected' : '' ?>>Female</option>
                                <option value="other" <?= ($profile['gender'] ?? '') == 'other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="nationality">Nationality</label>
                        <input type="text" name="nationality" id="nationality" 
                               value="<?= htmlspecialchars($profile['nationality'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea name="address" id="address" required><?= htmlspecialchars($profile['address'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" name="phone" id="phone" 
                               value="<?= htmlspecialchars($profile['phone'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Save Profile</button>
                    </div>
                </form>
                
                <div class="profile-actions">
                    <h3>Account Settings</h3>
                    <a href="../change_password.php" class="btn btn-secondary">
                        <i class="fas fa-key"></i> Change Password
                    </a>
                </div>
            </section>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/script.js"></script>
</body>
</html>