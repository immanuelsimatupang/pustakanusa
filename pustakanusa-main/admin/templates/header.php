<?php
// Header untuk tampilan admin
// Pastikan session sudah dimulai di file yang memanggil header ini
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit;
}

// Set title jika tidak ada
if (!isset($page_title)) {
    $page_title = 'Admin Dashboard - Pustakanusa';
}

// Menentukan halaman aktif jika belum diset
if (!isset($active_page)) {
    $active_page = basename($_SERVER['PHP_SELF'], '.php');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Pustakanusa</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Admin custom styles -->
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
                    <a class="nav-link <?= $active_page == 'books' ? 'active' : '' ?>" href="books.php">
                        <i class="fas fa-book"></i>
                        Buku
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $active_page == 'orders' ? 'active' : '' ?>" href="orders.php">
                        <i class="fas fa-shopping-cart"></i>
                        Pesanan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $active_page == 'users' ? 'active' : '' ?>" href="users.php">
                        <i class="fas fa-users"></i>
                        Pengguna
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $active_page == 'categories' ? 'active' : '' ?>" href="categories.php">
                        <i class="fas fa-tags"></i>
                        Kategori
                    </a>
                </li>
                <li class="nav-item mt-3">
                    <a class="nav-link <?= $active_page == 'setup_database' ? 'active' : '' ?>" href="setup_database.php">
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
</body>
</html> 