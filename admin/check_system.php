<?php
// File untuk memeriksa apakah sistem admin berjalan dengan baik
session_start();

// Include konfigurasi database
require_once '../config/database.php';

// Fungsi untuk mengecek file
function check_file($file_path) {
    if (file_exists($file_path)) {
        echo "<div class='text-success'><i class='fas fa-check-circle'></i> File $file_path ada</div>";
        return true;
    } else {
        echo "<div class='text-danger'><i class='fas fa-times-circle'></i> File $file_path tidak ditemukan</div>";
        return false;
    }
}

// Fungsi untuk mengecek tabel
function check_table($table_name) {
    global $conn;
    $result = $conn->query("SHOW TABLES LIKE '$table_name'");
    if ($result->num_rows > 0) {
        echo "<div class='text-success'><i class='fas fa-check-circle'></i> Tabel $table_name ada</div>";
        return true;
    } else {
        echo "<div class='text-danger'><i class='fas fa-times-circle'></i> Tabel $table_name tidak ditemukan</div>";
        return false;
    }
}

// Cek koneksi database
$db_status = "Database <strong>terhubung</strong>";
if ($conn->connect_error) {
    $db_status = "Database <strong>tidak terhubung</strong>: " . $conn->connect_error;
}

// Cek folder templates jika diperlukan
$templates_folder = file_exists('../templates') ? "Folder templates <strong>ada</strong>" : "Folder templates <strong>tidak ditemukan</strong>";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Sistem Admin - Pustakanusa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Pemeriksaan Sistem Admin</h4>
                    </div>
                    <div class="card-body">
                        <h5>Status Koneksi Database</h5>
                        <p><?php echo $db_status; ?></p>
                        
                        <h5 class="mt-4">Pemeriksaan File Admin</h5>
                        <div class="ms-4">
                            <?php
                            check_file('index.php');
                            check_file('login.php');
                            check_file('orders.php');
                            check_file('create_admin.php');
                            ?>
                        </div>
                        
                        <h5 class="mt-4">Pemeriksaan Tabel Database</h5>
                        <div class="ms-4">
                            <?php
                            check_table('users');
                            check_table('books');
                            check_table('orders');
                            check_table('categories');
                            ?>
                        </div>
                        
                        <h5 class="mt-4">Pengecekan Session</h5>
                        <div class="ms-4">
                            <?php
                            if (session_status() === PHP_SESSION_ACTIVE) {
                                echo "<div class='text-success'><i class='fas fa-check-circle'></i> Session aktif</div>";
                            } else {
                                echo "<div class='text-danger'><i class='fas fa-times-circle'></i> Session tidak aktif</div>";
                            }
                            ?>
                        </div>
                        
                        <div class="mt-4">
                            <h5>Langkah selanjutnya:</h5>
                            <ol>
                                <li>Jika semua pemeriksaan berhasil, buat akun admin di halaman <a href="create_admin.php">Create Admin</a>.</li>
                                <li>Login ke halaman admin di <a href="login.php">Login Admin</a>.</li>
                                <li>Akses dashboard admin di <a href="index.php">Dashboard Admin</a>.</li>
                            </ol>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="../index.php" class="btn btn-secondary">Kembali ke Website</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 