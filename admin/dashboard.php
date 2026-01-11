<?php
// File dashboard admin sederhana tanpa error
session_start();

// Cek apakah pengguna sudah login sebagai admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit;
}

// Include konfigurasi
require_once '../config/database.php';

// Fungsi untuk cek apakah tabel ada
function table_exists($conn, $table_name) {
    $result = $conn->query("SHOW TABLES LIKE '$table_name'");
    return $result->num_rows > 0;
}

// Status tabel
$tables_status = [
    'users' => table_exists($conn, 'users'),
    'books' => table_exists($conn, 'books'),
    'orders' => table_exists($conn, 'orders'),
    'categories' => table_exists($conn, 'categories'),
    'provinces' => table_exists($conn, 'provinces'),
    'cities' => table_exists($conn, 'cities')
];

// Hitung jumlah untuk statistik dashboard
$stats = [
    'books' => 0,
    'orders' => 0,
    'users' => 0
];

// Jika tabel ada, ambil statistik
if ($tables_status['books']) {
    $books_result = $conn->query("SELECT COUNT(*) as total FROM books");
    if ($books_result && $books_result->num_rows > 0) {
        $stats['books'] = $books_result->fetch_assoc()['total'];
    }
}

if ($tables_status['orders']) {
    $orders_result = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status = 'pending'");
    if ($orders_result && $orders_result->num_rows > 0) {
        $stats['orders'] = $orders_result->fetch_assoc()['total'];
    }
}

if ($tables_status['users']) {
    $users_result = $conn->query("SELECT COUNT(*) as total FROM users");
    if ($users_result && $users_result->num_rows > 0) {
        $stats['users'] = $users_result->fetch_assoc()['total'];
    }
}

// Cek semua tabel
$all_tables_exist = !in_array(false, $tables_status);

// Title halaman
$page_title = "Dashboard Admin";

// Menentukan halaman aktif
$active_page = 'dashboard';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Pustakanusa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bs-primary: #4361ee;
            --bs-primary-rgb: 67, 97, 238;
            --bs-secondary: #3f37c9;
            --bs-success: #4cc9f0;
            --bs-info: #4895ef;
            --bs-warning: #f72585;
            --bs-danger: #e63946;
            --sidebar-width: 250px;
        }
        
        body {
            font-family: 'Inter', 'Segoe UI', Roboto, sans-serif;
            background-color: #f8f9fc;
            color: #333;
        }
        
        .bg-primary {
            background-color: var(--bs-primary) !important;
        }
        
        .text-primary {
            color: var(--bs-primary) !important;
        }
        
        .btn-primary {
            background-color: var(--bs-primary);
            border-color: var(--bs-primary);
        }
        
        .btn-primary:hover {
            background-color: var(--bs-secondary);
            border-color: var(--bs-secondary);
        }
        
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #fff;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            z-index: 1000;
            transition: all 0.3s;
        }
        
        .sidebar-header {
            padding: 1.5rem 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .sidebar-menu {
            padding: 1rem 0;
        }
        
        .sidebar-menu .nav-link {
            padding: 0.75rem 1.5rem;
            color: #6c757d;
            display: flex;
            align-items: center;
            transition: all 0.3s;
        }
        
        .sidebar-menu .nav-link:hover {
            color: var(--bs-primary);
            background-color: rgba(67, 97, 238, 0.05);
        }
        
        .sidebar-menu .nav-link.active {
            color: var(--bs-primary);
            background-color: rgba(67, 97, 238, 0.1);
            font-weight: 500;
            border-left: 3px solid var(--bs-primary);
        }
        
        .sidebar-menu .nav-link i {
            margin-right: 0.75rem;
            width: 20px;
            text-align: center;
        }
        
        .sidebar-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            position: absolute;
            bottom: 0;
            width: 100%;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .stat-card .stat-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }
        
        .stat-card .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0;
        }
        
        .stat-card .stat-label {
            font-size: 0.9rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 0;
        }
        
        .menu-card {
            transition: all 0.3s;
            border-radius: 10px;
        }
        
        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .user-dropdown img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            margin-right: 0.5rem;
        }
        
        .alert-warning {
            border-left: 4px solid #ffc107;
        }
        
        .btn-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                margin-left: calc(-1 * var(--sidebar-width));
            }
            
            .sidebar.active {
                margin-left: 0;
            }
            
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
            
            .sidebar-toggler.active {
                margin-left: var(--sidebar-width);
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h5 class="mb-0 fw-bold text-primary">
                <i class="fas fa-book me-2"></i>
                PustakaNusa
            </h5>
        </div>
        
        <div class="sidebar-menu">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link <?= $active_page == 'dashboard' ? 'active' : '' ?>" href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="books.php">
                        <i class="fas fa-book"></i>
                        Buku
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="orders.php">
                        <i class="fas fa-shopping-cart"></i>
                        Pesanan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="users.php">
                        <i class="fas fa-users"></i>
                        Pengguna
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="categories.php">
                        <i class="fas fa-tags"></i>
                        Kategori
                    </a>
                </li>
                <li class="nav-item mt-3">
                    <a class="nav-link" href="setup_database.php">
                        <i class="fas fa-database"></i>
                        Setup Database
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="sidebar-footer">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 36px; height: 36px;">
                        <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-semibold"><?= $_SESSION['username'] ?></h6>
                        <small class="text-muted">Administrator</small>
                    </div>
                </div>
                <a href="logout.php" class="btn btn-icon btn-light" title="Logout">
                    <i class="fas fa-sign-out-alt text-danger"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="mb-0 fw-bold">Dashboard</h3>
                <p class="text-muted mb-0">Selamat datang di panel admin Pustakanusa</p>
            </div>
            <div>
                <a href="../index.php" class="btn btn-light me-2" target="_blank">
                    <i class="fas fa-external-link-alt me-2"></i>
                    Lihat Website
                </a>
                <a href="setup_database.php" class="btn btn-primary">
                    <i class="fas fa-database me-2"></i>
                    Setup Database
                </a>
            </div>
        </div>

        <?php if (!$all_tables_exist): ?>
        <div class="alert alert-warning bg-white border-0 shadow-sm mb-4">
            <div class="d-flex">
                <div class="me-3">
                    <i class="fas fa-exclamation-triangle fs-3 text-warning"></i>
                </div>
                <div>
                    <h5 class="fw-bold">Perhatian!</h5>
                    <p class="mb-0">Beberapa tabel database belum dibuat. Silakan jalankan <a href="setup_database.php" class="alert-link">Setup Database</a> terlebih dahulu untuk membuat semua tabel yang diperlukan.</p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Stats Row -->
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card h-100 bg-primary text-white">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="stat-value"><?= $stats['books'] ?></p>
                                <p class="stat-label">Total Buku</p>
                                
                                <?php if ($tables_status['books']): ?>
                                    <a href="books.php" class="btn btn-light btn-sm mt-2">
                                        <i class="fas fa-arrow-right me-1"></i>
                                        Kelola
                                    </a>
                                <?php else: ?>
                                    <span class="badge bg-light text-primary mt-2">Tabel belum dibuat</span>
                                <?php endif; ?>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-book"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card h-100 bg-info text-white">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="stat-value"><?= $stats['orders'] ?></p>
                                <p class="stat-label">Pesanan Pending</p>
                                
                                <?php if ($tables_status['orders']): ?>
                                    <a href="orders.php" class="btn btn-light btn-sm mt-2">
                                        <i class="fas fa-arrow-right me-1"></i>
                                        Kelola
                                    </a>
                                <?php else: ?>
                                    <span class="badge bg-light text-info mt-2">Tabel belum dibuat</span>
                                <?php endif; ?>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card h-100 bg-success text-white">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="stat-value"><?= $stats['users'] ?></p>
                                <p class="stat-label">Total Pengguna</p>
                                
                                <?php if ($tables_status['users']): ?>
                                    <a href="users.php" class="btn btn-light btn-sm mt-2">
                                        <i class="fas fa-arrow-right me-1"></i>
                                        Kelola
                                    </a>
                                <?php else: ?>
                                    <span class="badge bg-light text-success mt-2">Tabel belum dibuat</span>
                                <?php endif; ?>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card h-100 bg-warning text-white">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between">
                            <div>
                                <?php
                                $cat_count = 0;
                                if ($tables_status['categories']) {
                                    $cat_result = $conn->query("SELECT COUNT(*) as total FROM categories");
                                    if ($cat_result && $cat_result->num_rows > 0) {
                                        $cat_count = $cat_result->fetch_assoc()['total'];
                                    }
                                }
                                ?>
                                <p class="stat-value"><?= $cat_count ?></p>
                                <p class="stat-label">Total Kategori</p>
                                
                                <?php if ($tables_status['categories']): ?>
                                    <a href="categories.php" class="btn btn-light btn-sm mt-2">
                                        <i class="fas fa-arrow-right me-1"></i>
                                        Kelola
                                    </a>
                                <?php else: ?>
                                    <span class="badge bg-light text-warning mt-2">Tabel belum dibuat</span>
                                <?php endif; ?>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-tags"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Menu Cards -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="fw-bold mb-4">Menu Utama</h5>
                <div class="row g-4">
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <a href="books.php" class="text-decoration-none">
                            <div class="card menu-card h-100 border-0">
                                <div class="card-body text-center p-4">
                                    <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 70px; height: 70px;">
                                        <i class="fas fa-book fa-2x text-primary"></i>
                                    </div>
                                    <h5 class="card-title mb-0 text-dark">Manajemen Buku</h5>
                                </div>
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <a href="orders.php" class="text-decoration-none">
                            <div class="card menu-card h-100 border-0">
                                <div class="card-body text-center p-4">
                                    <div class="rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 70px; height: 70px;">
                                        <i class="fas fa-shopping-cart fa-2x text-info"></i>
                                    </div>
                                    <h5 class="card-title mb-0 text-dark">Manajemen Pesanan</h5>
                                </div>
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <a href="users.php" class="text-decoration-none">
                            <div class="card menu-card h-100 border-0">
                                <div class="card-body text-center p-4">
                                    <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 70px; height: 70px;">
                                        <i class="fas fa-users fa-2x text-success"></i>
                                    </div>
                                    <h5 class="card-title mb-0 text-dark">Manajemen Pengguna</h5>
                                </div>
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <a href="categories.php" class="text-decoration-none">
                            <div class="card menu-card h-100 border-0">
                                <div class="card-body text-center p-4">
                                    <div class="rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 70px; height: 70px;">
                                        <i class="fas fa-tags fa-2x text-warning"></i>
                                    </div>
                                    <h5 class="card-title mb-0 text-dark">Manajemen Kategori</h5>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card">
            <div class="card-body">
                <h5 class="fw-bold mb-4">Aksi Cepat</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="d-grid">
                            <a href="books.php?action=add" class="btn btn-primary">
                                <i class="fas fa-plus-circle me-2"></i>
                                Tambah Buku Baru
                            </a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-grid">
                            <a href="categories.php?action=add" class="btn btn-info text-white">
                                <i class="fas fa-plus-circle me-2"></i>
                                Tambah Kategori Baru
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <footer class="mt-5 text-muted">
            <p class="small mb-0">&copy; 2023 PustakaNusa Admin Panel. All rights reserved.</p>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle sidebar on mobile
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggler = document.querySelector('.sidebar-toggler');
            const sidebar = document.querySelector('.sidebar');
            
            if (sidebarToggler) {
                sidebarToggler.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                    this.classList.toggle('active');
                });
            }
        });
    </script>
</body>
</html> 