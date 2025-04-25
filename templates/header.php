<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : "PustakaNusa - Platform Literasi Digital Indonesia"; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" href="assets/img/favicon.ico" type="image/x-icon">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    
    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css' rel='stylesheet'>
    
    <!-- Remixicon -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@300;400;700&family=Open+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS Variables -->
    <link href="assets/css/variables.css" rel="stylesheet">
    
    <!-- Main CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .top-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #eaeaea;
            font-size: 12px;
        }
        .search-form {
            width: 100%;
            max-width: 550px;
        }
        .search-form .input-group {
            border-radius: 6px;
            overflow: hidden;
        }
        .navbar-category {
            background-color: #f8f9fa;
            border-bottom: 1px solid #eaeaea;
            padding: 0.5rem 0;
        }
        .cart-icon {
            position: relative;
        }
        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            font-size: 10px;
            padding: 0.25rem 0.5rem;
            border-radius: 50%;
        }
        .user-dropdown .dropdown-menu {
            width: 200px;
        }
        .category-nav .nav-link {
            padding: 0.5rem 1rem;
            font-weight: 500;
            color: #333;
            transition: all 0.2s;
        }
        .category-nav .nav-link:hover {
            color: var(--primary-color);
        }
        .navbar-brand img {
            height: 40px;
        }
    </style>
    
    <?php if(isset($extraStyles)) echo $extraStyles; ?>
</head>
<body>
    <?php
    // Inisialisasi session jika belum ada
    if (!isset($_SESSION)) {
        session_start();
    }
    
    // Hitung total item di keranjang untuk badge
    $cart_count = 0;
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $qty) {
            $cart_count += $qty;
        }
    }
    ?>

    <!-- Top Header -->
    <div class="top-header py-2 d-none d-md-block">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <a href="about.php" class="text-muted me-3"><i class="fas fa-info-circle me-1"></i> Tentang Kami</a>
                        <a href="contact.php" class="text-muted me-3"><i class="fas fa-envelope me-1"></i> Hubungi Kami</a>
                        <a href="faq.php" class="text-muted"><i class="fas fa-question-circle me-1"></i> Bantuan</a>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <div class="d-flex align-items-center justify-content-end">
                        <a href="#" class="text-muted me-3"><i class="fas fa-book-open me-1"></i> Blog</a>
                        <a href="#" class="text-muted me-3"><i class="fas fa-heart me-1"></i> Klub Buku</a>
                        <a href="#" class="text-muted"><i class="fas fa-lightbulb me-1"></i> Forum Diskusi</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <header class="bg-white py-3">
        <div class="container">
            <div class="row align-items-center">
                <!-- Logo -->
                <div class="col-lg-2 col-md-3 col-6 mb-2 mb-lg-0">
                    <a class="navbar-brand d-flex align-items-center" href="index.php">
                        <img src="assets/img/pustaka-logo.svg" alt="PustakaNusa" class="img-fluid">
                    </a>
                </div>
                
                <!-- Search Bar -->
                <div class="col-lg-6 col-md-5 mb-2 mb-lg-0">
                    <form class="search-form mx-auto">
                        <div class="input-group">
                            <select class="form-select flex-shrink-1 bg-light" style="max-width: 130px; border-radius: 0;">
                                <option value="semua">Semua Kategori</option>
                                <option value="fiksi">Fiksi</option>
                                <option value="nonfiksi">Non-Fiksi</option>
                                <option value="pendidikan">Pendidikan</option>
                                <option value="anak">Anak-anak</option>
                            </select>
                            <input type="text" class="form-control" placeholder="Cari judul buku, penulis, atau penerbit...">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- User Options -->
                <div class="col-lg-4 col-md-4 col-6 text-end">
                    <div class="d-flex align-items-center justify-content-end">
                        <!-- Wishlist -->
                        <a href="wishlist.php" class="btn btn-light me-2" data-bs-toggle="tooltip" title="Daftar Keinginan">
                            <i class="far fa-heart"></i>
                        </a>
                        
                        <!-- Shopping Cart -->
                        <a href="cart.php" class="btn btn-light me-3 cart-icon" data-bs-toggle="tooltip" title="Keranjang Belanja">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="cart-count badge bg-danger <?php echo ($cart_count == 0) ? 'd-none' : ''; ?>"><?php echo $cart_count; ?></span>
                        </a>
                        
                        <!-- Login/Register -->
                        <div class="dropdown user-dropdown">
                            <a href="#" class="dropdown-toggle text-decoration-none" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="btn btn-outline-primary rounded-pill px-3 d-inline-flex align-items-center">
                                    <i class="fas fa-user me-2"></i> Akun Saya
                                </span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 py-0" aria-labelledby="userDropdown">
                                <li class="px-3 py-2 bg-light border-bottom">
                                    <span class="fw-medium">Selamat Datang</span>
                                </li>
                                <li><a class="dropdown-item py-2" href="login.php"><i class="fas fa-sign-in-alt me-2 text-primary"></i> Masuk</a></li>
                                <li><a class="dropdown-item py-2" href="register.php"><i class="fas fa-user-plus me-2 text-primary"></i> Daftar</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item py-2" href="#"><i class="fas fa-book me-2 text-primary"></i> Koleksi Saya</a></li>
                                <li><a class="dropdown-item py-2" href="#"><i class="fas fa-history me-2 text-primary"></i> Riwayat Pesanan</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Category Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light navbar-category py-0 shadow-sm">
        <div class="container">
            <button class="navbar-toggler border-0 px-0" type="button" data-bs-toggle="collapse" data-bs-target="#categoryNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="categoryNav">
                <ul class="navbar-nav category-nav">
                    <li class="nav-item">
                        <a class="nav-link px-3 <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>" href="index.php">
                            <i class="fas fa-home text-primary me-1"></i> Beranda
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle px-3 <?php echo (basename($_SERVER['PHP_SELF']) == 'books.php') ? 'active' : ''; ?>" href="books.php" id="booksDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-book text-primary me-1"></i> Buku Fiksi
                        </a>
                        <ul class="dropdown-menu border-0 shadow rounded-3">
                            <li><a class="dropdown-item py-2" href="category.php?id=1">Novel</a></li>
                            <li><a class="dropdown-item py-2" href="category.php?id=2">Cerpen</a></li>
                            <li><a class="dropdown-item py-2" href="category.php?id=3">Puisi</a></li>
                            <li><a class="dropdown-item py-2" href="category.php?id=4">Roman</a></li>
                            <li><a class="dropdown-item py-2" href="category.php?id=5">Sastra Klasik</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle px-3" href="#" id="nonfiksiDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-lightbulb text-primary me-1"></i> Non-Fiksi
                        </a>
                        <ul class="dropdown-menu border-0 shadow rounded-3">
                            <li><a class="dropdown-item py-2" href="#">Biografi</a></li>
                            <li><a class="dropdown-item py-2" href="#">Sejarah</a></li>
                            <li><a class="dropdown-item py-2" href="#">Bisnis & Ekonomi</a></li>
                            <li><a class="dropdown-item py-2" href="#">Sains & Teknologi</a></li>
                            <li><a class="dropdown-item py-2" href="#">Pengembangan Diri</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle px-3" href="#" id="pendidikanDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-graduation-cap text-primary me-1"></i> Pendidikan
                        </a>
                        <ul class="dropdown-menu border-0 shadow rounded-3">
                            <li><a class="dropdown-item py-2" href="#">Buku Pelajaran</a></li>
                            <li><a class="dropdown-item py-2" href="#">Buku Anak</a></li>
                            <li><a class="dropdown-item py-2" href="#">Kamus & Referensi</a></li>
                            <li><a class="dropdown-item py-2" href="#">Buku Persiapan Ujian</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3 <?php echo (basename($_SERVER['PHP_SELF']) == 'cart.php') ? 'active' : ''; ?>" href="cart.php">
                            <i class="fas fa-shopping-cart text-primary me-1"></i> Keranjang
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3" href="promo.php">
                            <i class="fas fa-tags text-danger me-1"></i> Promo
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3" href="new-releases.php">
                            <i class="fas fa-star text-warning me-1"></i> Buku Baru
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3" href="bestseller.php">
                            <i class="fas fa-trophy text-success me-1"></i> Terlaris
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3 <?php echo (basename($_SERVER['PHP_SELF']) == 'reader.php') ? 'active' : ''; ?>" href="reader.php">
                            <i class="fas fa-book-reader text-primary me-1"></i> Baca Digital
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</body>
</html>