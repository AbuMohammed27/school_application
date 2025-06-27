<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Application System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .hero {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            padding: 5rem 0;
            border-radius: 15px;
            margin-bottom: 3rem;
            text-align: center;
        }
        
        .feature-card {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            height: 100%;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
        }
        
        .feature-card i {
            font-size: 3rem;
            margin-bottom: 1.5rem;
            color: #2575fc;
        }
        
        .cta-buttons .btn {
            padding: 0.75rem 2rem;
            font-size: 1.1rem;
            margin: 0 0.5rem;
            border-radius: 50px;
        }
        
        .btn-primary {
            background-color: #2575fc;
            border-color: #2575fc;
        }
        
        .btn-secondary {
            background-color: transparent;
            border: 2px solid white;
        }
        
        .btn-secondary:hover {
            background-color: rgba(255,255,255,0.1);
        }
    </style>
</head>
<body class="d-flex flex-column">
    <div class="container my-auto py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <section class="hero">
                    <div class="container">
                        <h1 class="display-4 fw-bold mb-4">Welcome to Our School Application Portal</h1>
                        <p class="lead mb-5">Start your educational journey with us today</p>
                        <div class="cta-buttons">
                            <a href="login.php" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt me-2"></i>Login
                            </a>
                            <a href="register.php" class="btn btn-secondary">
                                <i class="fas fa-user-plus me-2"></i>Register
                            </a>
                        </div>
                    </div>
                </section>
                
                <section class="features mb-5">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="feature-card">
                                <i class="fas fa-user-graduate"></i>
                                <h3>Easy Application</h3>
                                <p class="text-muted">Complete your application in one centralized portal</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="feature-card">
                                <i class="fas fa-file-upload"></i>
                                <h3>Document Upload</h3>
                                <p class="text-muted">Upload all required documents securely</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="feature-card">
                                <i class="fas fa-globe"></i>
                                <h3>International Support</h3>
                                <p class="text-muted">Special support for international applicants</p>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Your custom JS -->
    <script src="assets/js/script.js"></script>
</body>
</html>