<?php
session_start();

// Cek jika user sudah login
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

// Inisialisasi variabel pesan error
$error_message = "";

// Proses form login jika ada request POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validasi input
    if (empty($_POST['username']) || empty($_POST['password'])) {
        $error_message = "Username dan password harus diisi.";
    } else {
        // Pada implementasi sebenarnya, ini akan terhubung ke database
        // Untuk contoh ini, kita gunakan hardcoded credentials
        $demo_username = "demo";
        $demo_password = "password123";
        
        if ($_POST['username'] === $demo_username && $_POST['password'] === $demo_password) {
            // Login berhasil
            $_SESSION['user_id'] = 1;
            $_SESSION['username'] = $_POST['username'];
            $_SESSION['user_role'] = 'student';
            
            // Redirect ke dashboard
            header("Location: dashboard.php");
            exit;
        } else {
            $error_message = "Username atau password salah.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Kahfi Education</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="assets/img/kahfi-logo.svg" alt="Kahfi Education" height="40">
                <span class="ms-2 fw-bold">Kahfi Education</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">Daftar</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <img src="assets/img/islamic-pattern-divider.svg" alt="Islamic Pattern" class="mb-3" style="height: 40px;">
                            <h2 class="fw-bold">Masuk ke Akun Anda</h2>
                            <p class="text-muted">Bismillah, mulai perjalanan belajar Anda</p>
                        </div>
                        
                        <?php if (!empty($error_message)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $error_message; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fas fa-user text-success"></i></span>
                                    <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username Anda">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fas fa-lock text-success"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password Anda">
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
                                <a href="forgot-password.php" class="text-decoration-none text-success">Lupa password?</a>
                            </div>
                            
                            <div class="d-grid mb-4">
                                <button type="submit" class="btn btn-success py-2">Masuk</button>
                            </div>
                            
                            <div class="islamic-quote p-3 mb-4">
                                <p class="arabic-text mb-2 text-center">مَنْ سَلَكَ طَرِيقًا يَلْتَمِسُ فِيهِ عِلْمًا سَهَّلَ اللَّهُ لَهُ بِهِ طَرِيقًا إِلَى الْجَنَّةِ</p>
                                <p class="small text-center mb-0"><em>"Barangsiapa menempuh jalan untuk mencari ilmu, maka Allah akan memudahkan baginya jalan menuju surga."</em> <br>- HR. Muslim</p>
                            </div>
                            
                            <p class="text-center mb-0">Belum memiliki akun? <a href="register.php" class="text-decoration-none text-success">Daftar sekarang</a></p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Simple -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">© 2023 Kahfi Education. Semua hak dilindungi.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="index.php" class="text-white me-3">Beranda</a>
                    <a href="#" class="text-white me-3">Kontak</a>
                    <a href="#" class="text-white">Kebijakan Privasi</a>
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