<?php
// File untuk memperbarui struktur tabel categories

// Cek apakah pengguna sudah login sebagai admin
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit;
}

// Include konfigurasi
require_once '../config/database.php';

// Fungsi untuk mengecek apakah kolom ada dalam tabel
function columnExists($conn, $table, $column) {
    $query = "SHOW COLUMNS FROM `$table` LIKE '$column'";
    $result = $conn->query($query);
    return $result->num_rows > 0;
}

// Variabel untuk menyimpan pesan
$messages = [];

// Cek apakah tabel categories ada
$table_exists = $conn->query("SHOW TABLES LIKE 'categories'")->num_rows > 0;

if (!$table_exists) {
    // Jika tabel belum ada, buat tabel baru
    $create_sql = "CREATE TABLE `categories` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(100) NOT NULL,
        `slug` VARCHAR(100) NOT NULL,
        `description` TEXT NULL,
        `parent_id` INT DEFAULT 0,
        `display_order` INT DEFAULT 0,
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `slug` (`slug`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($create_sql)) {
        $messages[] = "Tabel categories berhasil dibuat dengan struktur baru.";
    } else {
        $messages[] = "Error membuat tabel categories: " . $conn->error;
    }
} else {
    // Jika tabel sudah ada, cek dan tambahkan kolom yang diperlukan
    
    // Cek kolom parent_id
    if (!columnExists($conn, 'categories', 'parent_id')) {
        $alter_sql = "ALTER TABLE `categories` ADD COLUMN `parent_id` INT DEFAULT 0 AFTER `description`";
        if ($conn->query($alter_sql)) {
            $messages[] = "Kolom parent_id berhasil ditambahkan ke tabel categories.";
        } else {
            $messages[] = "Error menambahkan kolom parent_id: " . $conn->error;
        }
    } else {
        $messages[] = "Kolom parent_id sudah ada dalam tabel categories.";
    }
    
    // Cek kolom display_order
    if (!columnExists($conn, 'categories', 'display_order')) {
        $alter_sql = "ALTER TABLE `categories` ADD COLUMN `display_order` INT DEFAULT 0 AFTER `parent_id`";
        if ($conn->query($alter_sql)) {
            $messages[] = "Kolom display_order berhasil ditambahkan ke tabel categories.";
        } else {
            $messages[] = "Error menambahkan kolom display_order: " . $conn->error;
        }
    } else {
        $messages[] = "Kolom display_order sudah ada dalam tabel categories.";
    }
    
    // Cek kolom is_active
    if (!columnExists($conn, 'categories', 'is_active')) {
        $alter_sql = "ALTER TABLE `categories` ADD COLUMN `is_active` TINYINT(1) DEFAULT 1 AFTER `display_order`";
        if ($conn->query($alter_sql)) {
            $messages[] = "Kolom is_active berhasil ditambahkan ke tabel categories.";
        } else {
            $messages[] = "Error menambahkan kolom is_active: " . $conn->error;
        }
    } else {
        $messages[] = "Kolom is_active sudah ada dalam tabel categories.";
    }
}

// Tampilkan hasil
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Tabel Kategori - PustakaNusa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Update Struktur Tabel Kategori</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($messages)): ?>
                            <div class="alert alert-info">
                                <h5>Hasil:</h5>
                                <ul>
                                    <?php foreach ($messages as $message): ?>
                                        <li><?= $message ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <p class="mb-4">Proses update struktur tabel kategori selesai. Sekarang Anda dapat menggunakan fitur manajemen kategori.</p>
                        
                        <div class="d-flex justify-content-between">
                            <a href="categories.php" class="btn btn-primary">Ke Halaman Kategori</a>
                            <a href="dashboard.php" class="btn btn-outline-secondary">Kembali ke Dashboard</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 