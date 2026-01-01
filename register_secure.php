<?php
session_start();

// Load security functions
require_once __DIR__ . '/includes/security.php';

// Cek jika user sudah login
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Inisialisasi variabel pesan error dan sukses
$error_message = "";
$success_message = "";

// Proses form pendaftaran jika ada request POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validasi dan sanitasi input
    $fullname = sanitizeInput($_POST['fullname'] ?? '');
    $username = sanitizeInput($_POST['username'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');
    $city = sanitizeInput($_POST['city'] ?? '');
    $postal_code = sanitizeInput($_POST['postal_code'] ?? '');
    $province = sanitizeInput($_POST['province'] ?? '');
    $agree_terms = isset($_POST['agree_terms']) ? true : false;

    // Validasi CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error_message = "Token keamanan tidak valid. Silakan coba lagi.";
    }
    // Validasi field tidak boleh kosong
    elseif (empty($fullname) || empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = "Semua field wajib diisi.";
    }
    // Validasi email
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Format email tidak valid.";
    }
    // Validasi username (hanya huruf, angka, dan underscore)
    elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        $error_message = "Username harus 3-20 karakter dan hanya boleh mengandung huruf, angka, dan underscore.";
    }
    // Validasi password strength
    elseif (!validatePasswordStrength($password)) {
        $error_message = "Password harus minimal 8 karakter dan mengandung setidaknya satu huruf besar, satu huruf kecil, satu angka, dan satu karakter spesial.";
    }
    // Validasi password match
    elseif ($password !== $confirm_password) {
        $error_message = "Password dan konfirmasi password tidak cocok.";
    }
    // Validasi terms and conditions
    elseif (!$agree_terms) {
        $error_message = "Anda harus menyetujui syarat dan ketentuan.";
    }
    // Cek apakah email sudah terdaftar
    else {
        global $conn;
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error_message = "Email sudah terdaftar. Silakan gunakan email lain.";
        } else {
            // Cek apakah username sudah terdaftar
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $error_message = "Username sudah digunakan. Silakan pilih username lain.";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert user ke database
                $stmt = $conn->prepare("INSERT INTO users (name, username, email, password, phone, address, city, postal_code, province) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssssss", $fullname, $username, $email, $hashed_password, $phone, $address, $city, $postal_code, $province);

                if ($stmt->execute()) {
                    $success_message = "Pendaftaran berhasil! Silakan login untuk melanjutkan.";
                    
                    // Log aktivitas pendaftaran
                    logSecurityEvent('registration_success', ['user_email' => $email]);
                    
                    // Reset form setelah berhasil
                    $fullname = $username = $email = $password = $confirm_password = $phone = $address = $city = $postal_code = $province = "";
                } else {
                    $error_message = "Terjadi kesalahan saat pendaftaran. Silakan coba lagi.";
                    logSecurityEvent('registration_error', ['error' => $conn->error, 'user_email' => $email]);
                }
            }
        }
    }
}

// Fungsi untuk validasi kekuatan password
function validatePasswordStrength($password) {
    // Minimal 8 karakter dengan huruf besar, huruf kecil, angka, dan karakter spesial
    $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
    return preg_match($pattern, $password);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - PustakaNusa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="assets/img/logo.png" alt="PustakaNusa" height="40">
                <span class="ms-2 fw-bold">PustakaNusa</span>
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
                            <p class="text-muted">Bergabunglah dengan komunitas PustakaNusa</p>
                        </div>
                        
                        <?php if (!empty($error_message)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo htmlspecialchars($error_message); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($success_message)): ?>
                            <div class="alert alert-success" role="alert">
                                <?php echo htmlspecialchars($success_message); ?>
                                <div class="mt-3">
                                    <a href="login.php" class="btn btn-success">Masuk ke Akun</a>
                                </div>
                            </div>
                        <?php else: ?>
                            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="row g-3">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                
                                <div class="col-12">
                                    <label for="fullname" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fas fa-user text-success"></i></span>
                                        <input type="text" class="form-control" id="fullname" name="fullname" placeholder="Masukkan nama lengkap Anda" value="<?php echo isset($fullname) ? htmlspecialchars($fullname) : ''; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fas fa-at text-success"></i></span>
                                        <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username (3-20 karakter)" value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" required>
                                    </div>
                                    <div class="form-text">Username hanya boleh mengandung huruf, angka, dan underscore.</div>
                                </div>
                                
                                <div class="col-12">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fas fa-envelope text-success"></i></span>
                                        <input type="email" class="form-control" id="email" name="email" placeholder="Masukkan alamat email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
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
                                        <small class="text-muted">Minimal 8 karakter dengan huruf besar, kecil, angka, dan simbol</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="confirm_password" class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fas fa-lock text-success"></i></span>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Masukkan konfirmasi password" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Nomor Telepon</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fas fa-phone text-success"></i></span>
                                        <input type="tel" class="form-control" id="phone" name="phone" placeholder="Masukkan nomor telepon" value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="postal_code" class="form-label">Kode Pos</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fas fa-map-marker-alt text-success"></i></span>
                                        <input type="text" class="form-control" id="postal_code" name="postal_code" placeholder="Kode pos" value="<?php echo isset($postal_code) ? htmlspecialchars($postal_code) : ''; ?>">
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <label for="province" class="form-label">Provinsi</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fas fa-map text-success"></i></span>
                                        <input type="text" class="form-control" id="province" name="province" placeholder="Nama provinsi" value="<?php echo isset($province) ? htmlspecialchars($province) : ''; ?>">
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <label for="city" class="form-label">Kota</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fas fa-city text-success"></i></span>
                                        <input type="text" class="form-control" id="city" name="city" placeholder="Nama kota" value="<?php echo isset($city) ? htmlspecialchars($city) : ''; ?>">
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <label for="address" class="form-label">Alamat Lengkap</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fas fa-home text-success"></i></span>
                                        <textarea class="form-control" id="address" name="address" placeholder="Alamat lengkap Anda" rows="2"><?php echo isset($address) ? htmlspecialchars($address) : ''; ?></textarea>
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
                    <p class="mb-0">© 2026 PustakaNusa. Semua hak dilindungi.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="index.php" class="text-white me-3">Beranda</a>
                    <a href="contact.php" class="text-white me-3">Kontak</a>
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
        
        // Toggle confirm password visibility
        const confirmPasswordToggle = document.createElement('button');
        confirmPasswordToggle.className = 'btn btn-outline-secondary';
        confirmPasswordToggle.type = 'button';
        confirmPasswordToggle.innerHTML = '<i class="fas fa-eye"></i>';
        confirmPasswordToggle.addEventListener('click', function() {
            const confirmPasswordInput = document.getElementById('confirm_password');
            const icon = this.querySelector('i');
            
            if (confirmPasswordInput.type === 'password') {
                confirmPasswordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                confirmPasswordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        // Add toggle button to confirm password field
        const confirmInputGroup = document.querySelector('#confirm_password').parentElement;
        confirmInputGroup.appendChild(confirmPasswordToggle);
        
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