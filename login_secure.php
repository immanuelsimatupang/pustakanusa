<?php
// Define base path for security
define('BASEPATH', 1);

// Include security middleware
require_once 'includes/security.php';

// Include database configuration
require_once 'config/database.php';

// Initialize variables
$error_message = "";
$success_message = "";

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Process login form if POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error_message = "CSRF token validation failed. Please try again.";
        logSecurityEvent('CSRF_ATTACK', 'Invalid token in login form');
    } else {
        // Validate and sanitize inputs
        $email = isset($_POST['email']) ? sanitizeInput($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        
        // Validate inputs
        if (empty($email) || empty($password)) {
            $error_message = "Email and password are required.";
        } elseif (!validateEmail($email)) {
            $error_message = "Invalid email format.";
        } elseif (isPotentialAttack($email)) {
            $error_message = "Invalid input detected.";
            logSecurityEvent('INPUT_ATTACK', "Potential attack in email: $email");
        } else {
            // Check rate limiting
            if (!checkRateLimit('login_' . $_SERVER['REMOTE_ADDR'], 5, 300)) { // 5 attempts per 5 minutes
                $error_message = "Too many login attempts. Please try again later.";
                logSecurityEvent('RATE_LIMIT_EXCEEDED', "IP: {$_SERVER['REMOTE_ADDR']} exceeded login attempts");
            } else {
                try {
                    // Prepare and execute query to prevent SQL injection
                    $stmt = $conn->prepare("SELECT id, name, email, password, is_admin FROM users WHERE email = ? LIMIT 1");
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        $user = $result->fetch_assoc();
                        
                        // Verify password
                        if (password_verify($password, $user['password'])) {
                            // Regenerate session ID to prevent session fixation
                            session_regenerate_id(true);
                            
                            // Set session variables
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['username'] = $user['name'];
                            $_SESSION['user_email'] = $user['email'];
                            $_SESSION['is_admin'] = $user['is_admin'];
                            
                            // Log successful login
                            logSecurityEvent('LOGIN_SUCCESS', "User ID: {$user['id']} logged in from {$_SERVER['REMOTE_ADDR']}");
                            
                            // Redirect based on user type
                            if ($user['is_admin']) {
                                header("Location: admin/dashboard.php");
                            } else {
                                header("Location: index.php");
                            }
                            exit;
                        } else {
                            $error_message = "Invalid email or password.";
                            logSecurityEvent('LOGIN_FAILED', "Failed login attempt for email: $email from {$_SERVER['REMOTE_ADDR']}");
                        }
                    } else {
                        $error_message = "Invalid email or password.";
                        logSecurityEvent('LOGIN_FAILED', "Failed login attempt for non-existent email: $email from {$_SERVER['REMOTE_ADDR']}");
                    }
                    
                    $stmt->close();
                } catch (Exception $e) {
                    $error_message = "An error occurred during login. Please try again.";
                    logSecurityEvent('LOGIN_ERROR', "Database error: " . $e->getMessage());
                }
            }
        }
    }
}

// Generate CSRF token for the form
$csrf_token = generateCSRFToken();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PustakaNusa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="assets/img/pustaka-logo.svg" alt="PustakaNusa" height="40">
                <span class="ms-2 fw-bold">PustakaNusa</span>
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <h2 class="fw-bold">Masuk ke Akun Anda</h2>
                            <p class="text-muted">Akses koleksi buku digital Anda</p>
                        </div>
                        
                        <?php if (!empty($error_message)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $error_message; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($success_message)): ?>
                            <div class="alert alert-success" role="alert">
                                <?php echo $success_message; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                            <!-- CSRF Token -->
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fas fa-envelope text-primary"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="Masukkan email Anda" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fas fa-lock text-primary"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password Anda" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                    <label class="form-check-label" for="remember">Ingat saya</label>
                                </div>
                                <a href="forgot-password.php" class="text-decoration-none text-primary">Lupa password?</a>
                            </div>
                            
                            <div class="d-grid mb-4">
                                <button type="submit" class="btn btn-primary py-2">Masuk</button>
                            </div>
                            
                            <p class="text-center mb-0">Belum memiliki akun? <a href="register.php" class="text-decoration-none text-primary">Daftar sekarang</a></p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">© 2023 PustakaNusa. Semua hak dilindungi.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="index.php" class="text-white me-3">Beranda</a>
                    <a href="about.php" class="text-white me-3">Tentang Kami</a>
                    <a href="contact.php" class="text-white">Kontak</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    </script>
</body>
</html>