<?php
$title = "PustakaNusa - Platform Literasi Digital Indonesia";
include 'templates/header.php';
?>

<!-- Hero Carousel Banner -->
<section id="hero-carousel" class="mb-4">
    <div id="mainCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active">
                <div class="container-fluid px-0">
                    <div class="row g-0">
                        <div class="col-12">
                            <div class="position-relative">
                                <img src="assets/img/banner/banner1.jpg" class="d-block w-100" alt="Banner Promo">
                                <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center">
                                    <div class="container">
                                        <div class="row">
                                            <div class="col-lg-6 col-md-8">
                                                <div class="bg-white bg-opacity-90 p-4 rounded shadow-sm">
                                                    <h2 class="fw-bold">Nikmati Akses Tanpa Batas</h2>
                                                    <p class="mb-3">Jelajahi ribuan buku premium dalam format digital dengan langganan PustakaNusa Premium</p>
                                                    <a href="subscription.php" class="btn btn-primary rounded-pill">
                                                        <i class="fas fa-crown me-2"></i> Langganan Sekarang
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="carousel-item">
                <div class="container-fluid px-0">
                    <div class="row g-0">
                        <div class="col-12">
                            <div class="position-relative">
                                <img src="assets/img/banner/banner2.jpg" class="d-block w-100" alt="Flash Sale">
                                <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center">
                                    <div class="container">
                                        <div class="row">
                                            <div class="col-lg-6 col-md-8">
                                                <div class="bg-dark bg-opacity-90 p-4 rounded shadow-sm text-white">
                                                    <span class="badge bg-danger mb-2">Hanya 24 Jam!</span>
                                                    <h2 class="fw-bold">Flash Sale!</h2>
                                                    <p class="mb-3">Diskon hingga 70% untuk ratusan judul buku pilihan. Jangan sampai kehabisan!</p>
                                                    <a href="flash-sale.php" class="btn btn-danger rounded-pill">
                                                        <i class="fas fa-bolt me-2"></i> Beli Sekarang
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="carousel-item">
                <div class="container-fluid px-0">
                    <div class="row g-0">
                        <div class="col-12">
                            <div class="position-relative">
                                <img src="assets/img/banner/banner3.jpg" class="d-block w-100" alt="Klub Buku">
                                <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center">
                                    <div class="container">
                                        <div class="row">
                                            <div class="col-lg-6 col-md-8">
                                                <div class="bg-success bg-opacity-90 p-4 rounded shadow-sm text-white">
                                                    <h2 class="fw-bold">Klub Buku Online</h2>
                                                    <p class="mb-3">Bergabunglah dengan ribuan pembaca dalam diskusi buku mingguan yang seru dan inspiratif</p>
                                                    <a href="book-clubs.php" class="btn btn-light text-success rounded-pill">
                                                        <i class="fas fa-users me-2"></i> Gabung Sekarang
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#mainCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#mainCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
</section>

<!-- Category Icons Section -->
<section id="category-icons" class="mb-5">
    <div class="container">
        <div class="row g-4 justify-content-center">
            <div class="col-lg-2 col-md-3 col-4">
                <a href="category.php?id=1" class="text-decoration-none">
                    <div class="text-center">
                        <div class="bg-light rounded-circle mx-auto d-flex align-items-center justify-content-center mb-2" style="width: 80px; height: 80px;">
                            <i class="fas fa-book text-primary fa-2x"></i>
                        </div>
                        <p class="mb-0 small fw-medium text-dark">Fiksi</p>
                    </div>
                </a>
            </div>
            <div class="col-lg-2 col-md-3 col-4">
                <a href="category.php?id=2" class="text-decoration-none">
                    <div class="text-center">
                        <div class="bg-light rounded-circle mx-auto d-flex align-items-center justify-content-center mb-2" style="width: 80px; height: 80px;">
                            <i class="fas fa-lightbulb text-warning fa-2x"></i>
                        </div>
                        <p class="mb-0 small fw-medium text-dark">Non-Fiksi</p>
                    </div>
                </a>
            </div>
            <div class="col-lg-2 col-md-3 col-4">
                <a href="category.php?id=3" class="text-decoration-none">
                    <div class="text-center">
                        <div class="bg-light rounded-circle mx-auto d-flex align-items-center justify-content-center mb-2" style="width: 80px; height: 80px;">
                            <i class="fas fa-graduation-cap text-info fa-2x"></i>
                        </div>
                        <p class="mb-0 small fw-medium text-dark">Pendidikan</p>
                    </div>
                </a>
            </div>
            <div class="col-lg-2 col-md-3 col-4">
                <a href="category.php?id=4" class="text-decoration-none">
                    <div class="text-center">
                        <div class="bg-light rounded-circle mx-auto d-flex align-items-center justify-content-center mb-2" style="width: 80px; height: 80px;">
                            <i class="fas fa-child text-danger fa-2x"></i>
                        </div>
                        <p class="mb-0 small fw-medium text-dark">Anak-anak</p>
                    </div>
                </a>
            </div>
            <div class="col-lg-2 col-md-3 col-4">
                <a href="category.php?id=5" class="text-decoration-none">
                    <div class="text-center">
                        <div class="bg-light rounded-circle mx-auto d-flex align-items-center justify-content-center mb-2" style="width: 80px; height: 80px;">
                            <i class="fas fa-landmark text-success fa-2x"></i>
                        </div>
                        <p class="mb-0 small fw-medium text-dark">Sejarah</p>
                    </div>
                </a>
            </div>
            <div class="col-lg-2 col-md-3 col-4">
                <a href="category.php?id=6" class="text-decoration-none">
                    <div class="text-center">
                        <div class="bg-light rounded-circle mx-auto d-flex align-items-center justify-content-center mb-2" style="width: 80px; height: 80px;">
                            <i class="fas fa-globe text-secondary fa-2x"></i>
                        </div>
                        <p class="mb-0 small fw-medium text-dark">Lainnya</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Flash Sale / Promo Section -->
<section id="flash-sale" class="mb-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h4 mb-0">
                <i class="fas fa-bolt text-danger me-2"></i> Flash Sale
            </h2>
            <a href="flash-sale.php" class="btn btn-sm btn-link text-decoration-none">Lihat Semua</a>
        </div>
        
        <div class="position-relative">
            <div class="row g-3">
                <div class="col-lg-2 col-md-3 col-6">
                    <div class="card h-100 border-0 shadow-sm product-card">
                        <div class="position-relative">
                            <a href="book-detail.php?id=1">
                            <img src="assets/img/books/book1.jpg" class="card-img-top" alt="Buku 1">
                            </a>
                            <div class="position-absolute top-0 start-0 bg-danger text-white py-1 px-2 small">-40%</div>
                        </div>
                        <div class="card-body p-3">
                            <h6 class="card-title mb-1 text-truncate">
                                <a href="book-detail.php?id=1" class="text-decoration-none text-dark">Bumi Manusia</a>
                            </h6>
                            <p class="small text-muted mb-2 text-truncate">Pramoedya Ananta Toer</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-danger fw-bold">Rp 59.000</span>
                                    <span class="small text-decoration-line-through text-muted">Rp 98.000</span>
                                </div>
                                <a href="javascript:void(0)" class="btn btn-sm btn-outline-primary add-to-cart-btn" data-book-id="1"><i class="fas fa-cart-plus"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-3 col-6">
                    <div class="card h-100 border-0 shadow-sm product-card">
                        <div class="position-relative">
                            <a href="book-detail.php?id=2">
                            <img src="assets/img/books/book2.jpg" class="card-img-top" alt="Buku 2">
                            </a>
                            <div class="position-absolute top-0 start-0 bg-danger text-white py-1 px-2 small">-30%</div>
                        </div>
                        <div class="card-body p-3">
                            <h6 class="card-title mb-1 text-truncate">
                                <a href="book-detail.php?id=2" class="text-decoration-none text-dark">Filosofi Teras</a>
                            </h6>
                            <p class="small text-muted mb-2 text-truncate">Henry Manampiring</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-danger fw-bold">Rp 68.000</span>
                                    <span class="small text-decoration-line-through text-muted">Rp 97.000</span>
                                </div>
                                <a href="javascript:void(0)" class="btn btn-sm btn-outline-primary add-to-cart-btn" data-book-id="2"><i class="fas fa-cart-plus"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-3 col-6">
                    <div class="card h-100 border-0 shadow-sm product-card">
                        <div class="position-relative">
                            <img src="assets/img/books/book3.jpg" class="card-img-top" alt="Buku 3">
                            <div class="position-absolute top-0 start-0 bg-danger text-white py-1 px-2 small">-50%</div>
                        </div>
                        <div class="card-body p-3">
                            <h6 class="card-title mb-1 text-truncate">Laut Bercerita</h6>
                            <p class="small text-muted mb-2 text-truncate">Leila S. Chudori</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-danger fw-bold">Rp 49.500</span>
                                    <span class="small text-decoration-line-through text-muted">Rp 99.000</span>
                                </div>
                                <a href="javascript:void(0)" class="btn btn-sm btn-outline-primary add-to-cart-btn" data-book-id="3"><i class="fas fa-cart-plus"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-3 col-6">
                    <div class="card h-100 border-0 shadow-sm product-card">
                        <div class="position-relative">
                            <img src="assets/img/books/book4.jpg" class="card-img-top" alt="Buku 4">
                            <div class="position-absolute top-0 start-0 bg-danger text-white py-1 px-2 small">-35%</div>
                        </div>
                        <div class="card-body p-3">
                            <h6 class="card-title mb-1 text-truncate">Pulang</h6>
                            <p class="small text-muted mb-2 text-truncate">Tere Liye</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-danger fw-bold">Rp 58.000</span>
                                    <span class="small text-decoration-line-through text-muted">Rp 89.000</span>
                                </div>
                                <a href="javascript:void(0)" class="btn btn-sm btn-outline-primary add-to-cart-btn" data-book-id="4"><i class="fas fa-cart-plus"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-3 col-6">
                    <div class="card h-100 border-0 shadow-sm product-card">
                        <div class="position-relative">
                            <img src="assets/img/books/book5.jpg" class="card-img-top" alt="Buku 5">
                            <div class="position-absolute top-0 start-0 bg-danger text-white py-1 px-2 small">-25%</div>
                        </div>
                        <div class="card-body p-3">
                            <h6 class="card-title mb-1 text-truncate">Perahu Kertas</h6>
                            <p class="small text-muted mb-2 text-truncate">Dee Lestari</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-danger fw-bold">Rp 74.250</span>
                                    <span class="small text-decoration-line-through text-muted">Rp 99.000</span>
                                </div>
                                <a href="javascript:void(0)" class="btn btn-sm btn-outline-primary add-to-cart-btn" data-book-id="5"><i class="fas fa-cart-plus"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-3 col-6">
                    <div class="card h-100 border-0 shadow-sm product-card">
                        <div class="position-relative">
                            <img src="assets/img/books/book6.jpg" class="card-img-top" alt="Buku 6">
                            <div class="position-absolute top-0 start-0 bg-danger text-white py-1 px-2 small">-45%</div>
                        </div>
                        <div class="card-body p-3">
                            <h6 class="card-title mb-1 text-truncate">Rentang Kisah</h6>
                            <p class="small text-muted mb-2 text-truncate">Gita Savitri</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-danger fw-bold">Rp 54.450</span>
                                    <span class="small text-decoration-line-through text-muted">Rp 99.000</span>
                                </div>
                                <a href="javascript:void(0)" class="btn btn-sm btn-outline-primary add-to-cart-btn" data-book-id="6"><i class="fas fa-cart-plus"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Controls -->
            <div class="flash-sale-controls d-none d-md-block">
                <button class="btn btn-light rounded-circle shadow-sm position-absolute top-50 start-0 translate-middle-y" style="margin-left: -20px;">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="btn btn-light rounded-circle shadow-sm position-absolute top-50 end-0 translate-middle-y" style="margin-right: -20px;">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Featured Books Section -->
<section id="featured-books" class="py-5 bg-light">
        <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="text-center mb-3">Buku Unggulan</h2>
                <p class="text-center text-muted">Pilihan terbaik karya sastra dan non-fiksi berkualitas</p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <img src="assets/img/book1.jpg" class="card-img-top" alt="Buku 1">
                    <div class="card-body">
                        <span class="badge bg-primary mb-2">Fiksi</span>
                        <h5 class="card-title">Bumi Manusia</h5>
                        <p class="card-text small">Pramoedya Ananta Toer</p>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="text-primary fw-bold">Rp 85.000</span>
                            <a href="#" class="btn btn-sm btn-outline-primary"><i class="fas fa-shopping-cart"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <img src="assets/img/book2.jpg" class="card-img-top" alt="Buku 2">
                    <div class="card-body">
                        <span class="badge bg-primary mb-2">Non-Fiksi</span>
                        <h5 class="card-title">Filosofi Teras</h5>
                        <p class="card-text small">Henry Manampiring</p>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="text-primary fw-bold">Rp 98.000</span>
                            <a href="#" class="btn btn-sm btn-outline-primary"><i class="fas fa-shopping-cart"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <img src="assets/img/book3.jpg" class="card-img-top" alt="Buku 3">
                    <div class="card-body">
                        <span class="badge bg-success mb-2">Gratis</span>
                        <h5 class="card-title">Laskar Pelangi</h5>
                        <p class="card-text small">Andrea Hirata</p>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="text-success fw-bold">Domain Publik</span>
                            <a href="#" class="btn btn-sm btn-outline-success"><i class="fas fa-download"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <img src="assets/img/book4.jpg" class="card-img-top" alt="Buku 4">
                    <div class="card-body">
                        <span class="badge bg-primary mb-2">Sejarah</span>
                        <h5 class="card-title">Indonesia Etc.</h5>
                        <p class="card-text small">Elizabeth Pisani</p>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="text-primary fw-bold">Rp 110.000</span>
                            <a href="#" class="btn btn-sm btn-outline-primary"><i class="fas fa-shopping-cart"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-center mt-4">
            <a href="books.php" class="btn btn-outline-primary">Lihat Lebih Banyak Buku</a>
            </div>
        </div>
    </section>

<!-- Value Propositions Section -->
<section id="features" class="py-5">
        <div class="container">
        <div class="row mb-5">
            <div class="col-md-6 mx-auto text-center">
                <h2 class="mb-3">Kenapa PustakaNusa?</h2>
                <p class="text-muted">Platform literasi digital terlengkap yang menghubungkan pembaca, buku, dan komunitas dalam satu tempat</p>
            </div>
            </div>
            <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-0 h-100">
                    <div class="card-body text-center">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                            <i class="fas fa-book-open text-primary fa-2x"></i>
                        </div>
                        <h4>Akses Buku Digital</h4>
                        <p class="text-muted">Ribuan buku digital yang dapat diakses kapan saja dan di mana saja melalui perangkat Anda</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 h-100">
                    <div class="card-body text-center">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                            <i class="fas fa-sync-alt text-primary fa-2x"></i>
                        </div>
                        <h4>Sinkronisasi Multi-Perangkat</h4>
                        <p class="text-muted">Lanjutkan membaca dari perangkat mana pun dengan sinkronisasi bookmark dan catatan otomatis</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 h-100">
                    <div class="card-body text-center">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                            <i class="fas fa-users text-primary fa-2x"></i>
                        </div>
                        <h4>Komunitas Pembaca</h4>
                        <p class="text-muted">Bergabung dengan komunitas pecinta buku, diskusikan bacaan, dan dapatkan rekomendasi</p>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </section>

<?php include 'templates/footer.php'; ?> 