<?php
session_start();

// Cek jika user sudah login
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

// Inisialisasi variabel pesan error dan sukses
$error_message = "";
$success_message = "";

// Proses form pendaftaran jika ada request POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validasi input
    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $agree_terms = isset($_POST['agree_terms']) ? true : false;
    
    // Validasi field tidak boleh kosong
    if (empty($fullname) || empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = "Semua field harus diisi.";
    }
    // Validasi email
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Format email tidak valid.";
    }
    // Validasi password match
    elseif ($password !== $confirm_password) {
        $error_message = "Password dan konfirmasi password tidak cocok.";
    }
    // Validasi terms and conditions
    elseif (!$agree_terms) {
        $error_message = "Anda harus menyetujui syarat dan ketentuan.";
    }
    // Proses pendaftaran
    else {
        // Pada implementasi sebenarnya, data akan disimpan ke database
        // Untuk contoh ini, kita anggap pendaftaran berhasil
        $success_message = "Pendaftaran berhasil! Silahkan cek email Anda untuk aktivasi akun.";
        
        // Reset form setelah berhasil
        $fullname = $username = $email = $password = $confirm_password = "";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Kahfi Education</title>
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
                        <a class="nav-link" href="login.php">Masuk</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <img src="assets/img/islamic-pattern-divider.svg" alt="Islamic Pattern" class="mb-3" style="height: 40px;">
                            <h2 class="fw-bold">Daftar Akun Baru</h2>
                            <p class="text-muted">Bismillah, bergabunglah dengan komunitas belajar Kahfi Education</p>
                        </div>
                        
                        <?php if (!empty($error_message)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $error_message; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($success_message)): ?>
                            <div class="alert alert-success" role="alert">
                                <?php echo $success_message; ?>
                                <div class="mt-3">
                                    <a href="login.php" class="btn btn-success">Masuk ke Akun</a>
                                </div>
                            </div>
                        <?php else: ?>
                            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="row g-3">
                                <div class="col-12">
                                    <label for="fullname" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fas fa-user text-success"></i></span>
                                        <input type="text" class="form-control" id="fullname" name="fullname" placeholder="Masukkan nama lengkap Anda" value="<?php echo isset($fullname) ? $fullname : ''; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fas fa-at text-success"></i></span>
                                        <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username" value="<?php echo isset($username) ? $username : ''; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fas fa-envelope text-success"></i></span>
                                        <input type="email" class="form-control" id="email" name="email" placeholder="Masukkan alamat email" value="<?php echo isset($email) ? $email : ''; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fas fa-lock text-success"></i></span>
                                        <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password" required>
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="password-strength mt-2" id="passwordStrength">
                                        <div class="progress" style="height: 5px;">
                                            <div class="progress-bar bg-danger" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <small class="text-muted">Gunakan minimal 8 karakter dengan huruf dan angka</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="confirm_password" class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fas fa-lock text-success"></i></span>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Masukkan konfirmasi password" required>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <label class="form-label">Pilih Kategori <span class="text-danger">*</span></label>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-check custom-radio">
                                                <input class="form-check-input" type="radio" name="role" id="role_student" value="student" checked>
                                                <label class="form-check-label" for="role_student">Siswa</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check custom-radio">
                                                <input class="form-check-input" type="radio" name="role" id="role_parent" value="parent">
                                                <label class="form-check-label" for="role_parent">Orang Tua</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check custom-radio">
                                                <input class="form-check-input" type="radio" name="role" id="role_teacher" value="teacher">
                                                <label class="form-check-label" for="role_teacher">Pengajar</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="agree_terms" name="agree_terms" required>
                                        <label class="form-check-label" for="agree_terms">
                                            Saya setuju dengan <a href="#" class="text-decoration-none text-success">Syarat dan Ketentuan</a> serta <a href="#" class="text-decoration-none text-success">Kebijakan Privasi</a>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-success py-2">Daftar Sekarang</button>
                                    </div>
                                </div>
                                
                                <div class="col-12 text-center">
                                    <div class="islamic-quote p-3 mb-2">
                                        <p class="arabic-text mb-2 text-center">إِنَّمَا الْأَعْمَالُ بِالنِّيَّاتِ</p>
                                        <p class="small text-center mb-0"><em>"Sesungguhnya setiap amalan tergantung pada niatnya."</em> <br>- HR. Bukhari & Muslim</p>
                                    </div>
                                    <p class="mt-3 mb-0">Sudah memiliki akun? <a href="login.php" class="text-decoration-none text-success">Masuk sekarang</a></p>
                                </div>
                            </form>
                        <?php endif; ?>
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
        
        // Password strength meter
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const progressBar = document.querySelector('#passwordStrength .progress-bar');
            let strength = 0;
            
            // Hitung kekuatan password
            if (password.length >= 8) strength += 25;
            if (password.match(/[A-Z]/)) strength += 25;
            if (password.match(/[0-9]/)) strength += 25;
            if (password.match(/[^A-Za-z0-9]/)) strength += 25;
            
            // Atur warna berdasarkan kekuatan
            if (strength < 50) {
                progressBar.className = 'progress-bar bg-danger';
            } else if (strength < 75) {
                progressBar.className = 'progress-bar bg-warning';
            } else {
                progressBar.className = 'progress-bar bg-success';
            }
            
            // Update progress bar
            progressBar.style.width = strength + '%';
            progressBar.setAttribute('aria-valuenow', strength);
        });
    </script>
</body>
</html> 