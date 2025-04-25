<?php
// File login khusus admin
session_start();

// Cek jika user sudah login sebagai admin
if (isset($_SESSION['user_id']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
    header("Location: dashboard.php");
    exit;
}

// Koneksi ke database
require_once '../config/database.php';

// Inisialisasi variabel pesan error
$error_message = "";
$table_exists = true;

// Cek apakah tabel users ada
try {
    $check_table = $conn->query("SHOW TABLES LIKE 'users'");
    if ($check_table->num_rows == 0) {
        // Tabel tidak ditemukan, set flag
        $table_exists = false;
        $error_message = "Tabel users belum dibuat. Silakan jalankan setup database terlebih dahulu.";
    }
} catch (Exception $e) {
    $table_exists = false;
    $error_message = "Terjadi kesalahan dengan database: " . $e->getMessage();
}

// Proses form login jika ada request POST dan tabel users ada
if ($_SERVER["REQUEST_METHOD"] == "POST" && $table_exists) {
    // Validasi input
    if (empty($_POST['username']) || empty($_POST['password'])) {
        $error_message = "Username dan password harus diisi.";
    } else {
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $password = $_POST['password'];
        
        try {
            // Cek di database
            $query = "SELECT * FROM users WHERE email = ? AND is_admin = 1 LIMIT 1";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                
                // Verifikasi password
                if (password_verify($password, $user['password'])) {
                    // Login berhasil
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['name'];
                    $_SESSION['is_admin'] = 1;
                    
                    // Redirect ke dashboard admin
                    header("Location: dashboard.php");
                    exit;
                } else {
                    $error_message = "Username atau password salah.";
                }
            } else {
                $error_message = "Username atau password salah.";
            }
        } catch (Exception $e) {
            $error_message = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Pustakanusa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h2 class="text-center mb-4">Login Admin</h2>
                        
                        <?php if (!empty($error_message)): ?>
                            <div class="alert alert-danger"><?php echo $error_message; ?></div>
                            
                            <?php if (!$table_exists): ?>
                                <div class="alert alert-info">
                                    <p>Database belum siap. Anda harus menjalankan setup database terlebih dahulu.</p>
                                    <a href="setup_database.php" class="btn btn-primary mt-2">Jalankan Setup Database</a>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php if ($table_exists): ?>
                        <form method="post">
                            <div class="mb-3">
                                <label for="username" class="form-label">Email Admin</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Login</button>
                            </div>
                            <p class="text-center mt-3">
                                <a href="../index.php">Kembali ke Website</a>
                            </p>
                        </form>
                        <?php else: ?>
                        <div class="text-center">
                            <a href="setup_database.php" class="btn btn-lg btn-primary">Setup Database</a>
                            <p class="mt-3">
                                <a href="../index.php">Kembali ke Website</a>
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 