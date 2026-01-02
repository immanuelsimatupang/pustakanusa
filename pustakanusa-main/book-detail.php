<?php
$title = "Detail Buku - PustakaNusa";
include 'templates/header.php';

// Ambil ID buku dari parameter URL
$book_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// TODO: Implementasi query database untuk mengambil detail buku

// Untuk contoh, kita gunakan data dummy
$book = [
    'id' => $book_id,
    'title' => 'Bumi Manusia',
    'author' => 'Pramoedya Ananta Toer',
    'publisher' => 'Lentera Dipantara',
    'year' => '2019',
    'pages' => 535,
    'description' => 'Bumi Manusia adalah novel pertama dari Tetralogi Buru karya Pramoedya Ananta Toer yang pertama kali diterbitkan oleh Hasta Mitra pada tahun 1980. Novel ini bercerita tentang perjuangan Minke, seorang pribumi yang berusaha mendapatkan kesetaraan hak dengan bangsa Eropa pada masa kolonial Belanda.',
    'category' => 'Fiksi',
    'language' => 'Indonesia',
    'isbn' => '979-8083-74-5',
    'price' => 98000,
    'discount_price' => 59000,
    'discount_percent' => 40,
    'rating' => 4.8,
    'reviews_count' => 128,
    'is_bestseller' => true,
    'cover_image' => 'assets/img/books/book1.jpg',
    'is_available' => true,
    'stock' => 15,
    'has_sample' => true // Tambahan untuk menunjukkan bahwa buku memiliki sampel untuk dibaca
];
?>

<!-- Book Detail Content -->
<section class="py-5">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small">
                <li class="breadcrumb-item"><a href="index.php">Beranda</a></li>
                <li class="breadcrumb-item"><a href="books.php">Buku</a></li>
                <li class="breadcrumb-item"><a href="category.php?id=1"><?= $book['category'] ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= $book['title'] ?></li>
            </ol>
        </nav>

        <div class="row g-4">
            <!-- Book Cover -->
            <div class="col-md-4 mb-4 mb-md-0">
                <div class="position-relative">
                    <img src="<?= $book['cover_image'] ?>" class="img-fluid rounded shadow" alt="<?= $book['title'] ?>">
                    <?php if ($book['discount_percent'] > 0): ?>
                    <div class="position-absolute top-0 start-0 bg-danger text-white py-1 px-3 rounded-pill mt-3 ms-3">
                        -<?= $book['discount_percent'] ?>%
                    </div>
                    <?php endif; ?>
                    <?php if ($book['is_bestseller']): ?>
                    <div class="position-absolute top-0 end-0 bg-warning text-dark py-1 px-3 rounded-pill mt-3 me-3">
                        <i class="fas fa-award me-1"></i> Bestseller
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Add Sample Button -->
                <?php if ($book['has_sample']): ?>
                <div class="mt-3">
                    <a href="reader.php?id=<?= $book['id'] ?>&page=1" class="btn btn-outline-info w-100">
                        <i class="fas fa-book-reader me-2"></i> Baca Sampel
                    </a>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Book Details -->
            <div class="col-md-8">
                <h1 class="mb-2"><?= $book['title'] ?></h1>
                <p class="text-muted mb-3">Oleh <a href="author.php?name=<?= urlencode($book['author']) ?>" class="text-decoration-none"><?= $book['author'] ?></a></p>
                
                <!-- Rating -->
                <div class="d-flex align-items-center mb-3">
                    <div class="me-2">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <?php if ($i <= floor($book['rating'])): ?>
                                <i class="fas fa-star text-warning"></i>
                            <?php elseif ($i - 0.5 <= $book['rating']): ?>
                                <i class="fas fa-star-half-alt text-warning"></i>
                            <?php else: ?>
                                <i class="far fa-star text-warning"></i>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                    <span class="fw-bold"><?= $book['rating'] ?></span>
                    <span class="text-muted ms-2">(<?= $book['reviews_count'] ?> ulasan)</span>
                </div>
                
                <!-- Price -->
                <div class="mb-4">
                    <?php if ($book['discount_price'] < $book['price']): ?>
                        <h3 class="text-danger fw-bold mb-0">Rp <?= number_format($book['discount_price'], 0, ',', '.') ?></h3>
                        <p class="text-muted"><del>Rp <?= number_format($book['price'], 0, ',', '.') ?></del> (Hemat <?= $book['discount_percent'] ?>%)</p>
                    <?php else: ?>
                        <h3 class="fw-bold mb-0">Rp <?= number_format($book['price'], 0, ',', '.') ?></h3>
                    <?php endif; ?>
                </div>
                
                <!-- Availability -->
                <div class="mb-4">
                    <?php if ($book['is_available'] && $book['stock'] > 0): ?>
                        <div class="badge bg-success mb-2">Tersedia (<?= $book['stock'] ?> stok)</div>
                    <?php else: ?>
                        <div class="badge bg-danger mb-2">Stok Habis</div>
                    <?php endif; ?>
                </div>
                
                <!-- Action Buttons -->
                <div class="d-flex flex-wrap gap-2 mb-4">
                    <?php if ($book['is_available'] && $book['stock'] > 0): ?>
                        <button class="btn btn-primary btn-lg buy-now-btn" data-book-id="<?= $book['id'] ?>">
                            <i class="fas fa-shopping-cart me-2"></i> Beli Sekarang
                        </button>
                        <button class="btn btn-outline-primary btn-lg add-to-cart-btn" data-book-id="<?= $book['id'] ?>">
                            <i class="fas fa-cart-plus me-2"></i> Tambah ke Keranjang
                        </button>
                    <?php else: ?>
                        <button class="btn btn-secondary btn-lg" disabled>
                            <i class="fas fa-shopping-cart me-2"></i> Beli Sekarang
                        </button>
                        <button class="btn btn-outline-secondary btn-lg" disabled>
                            <i class="fas fa-cart-plus me-2"></i> Tambah ke Keranjang
                        </button>
                    <?php endif; ?>
                    <button class="btn btn-outline-danger btn-lg">
                        <i class="far fa-heart"></i>
                    </button>
                    <button class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-share-alt"></i>
                    </button>
                </div>
                
                <!-- Book Info -->
                <div class="mb-4">
                    <div class="row g-3">
                        <div class="col-lg-6">
                            <div class="d-flex">
                                <div class="text-muted" style="width: 120px;">Penerbit</div>
                                <div><?= $book['publisher'] ?></div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="d-flex">
                                <div class="text-muted" style="width: 120px;">Tahun Terbit</div>
                                <div><?= $book['year'] ?></div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="d-flex">
                                <div class="text-muted" style="width: 120px;">Jumlah Halaman</div>
                                <div><?= $book['pages'] ?></div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="d-flex">
                                <div class="text-muted" style="width: 120px;">Bahasa</div>
                                <div><?= $book['language'] ?></div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="d-flex">
                                <div class="text-muted" style="width: 120px;">ISBN</div>
                                <div><?= $book['isbn'] ?></div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="d-flex">
                                <div class="text-muted" style="width: 120px;">Kategori</div>
                                <div><a href="category.php?id=1" class="text-decoration-none"><?= $book['category'] ?></a></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tabs Content -->
        <div class="mt-5">
            <ul class="nav nav-tabs" id="bookDetailTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button" role="tab" aria-controls="description" aria-selected="true">Deskripsi</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab" aria-controls="reviews" aria-selected="false">Ulasan (<?= $book['reviews_count'] ?>)</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="shipping-tab" data-bs-toggle="tab" data-bs-target="#shipping" type="button" role="tab" aria-controls="shipping" aria-selected="false">Pengiriman & Pembayaran</button>
                </li>
            </ul>
            <div class="tab-content p-4 border border-top-0 rounded-bottom" id="bookDetailTabsContent">
                <div class="tab-pane fade show active" id="description" role="tabpanel" aria-labelledby="description-tab">
                    <h4>Deskripsi</h4>
                    <p><?= $book['description'] ?></p>
                </div>
                <div class="tab-pane fade" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">
                    <h4>Ulasan Pembaca</h4>
                    <div class="mb-4">
                        <div class="row align-items-center">
                            <div class="col-md-3 text-center">
                                <h2 class="display-4 fw-bold mb-0"><?= $book['rating'] ?></h2>
                                <div class="mb-2">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= floor($book['rating'])): ?>
                                            <i class="fas fa-star text-warning"></i>
                                        <?php elseif ($i - 0.5 <= $book['rating']): ?>
                                            <i class="fas fa-star-half-alt text-warning"></i>
                                        <?php else: ?>
                                            <i class="far fa-star text-warning"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                                <p class="text-muted"><?= $book['reviews_count'] ?> ulasan</p>
                            </div>
                            <div class="col-md-9">
                                <!-- Rating Bars -->
                                <div class="mb-2 d-flex align-items-center">
                                    <div class="text-muted me-2">5 <i class="fas fa-star text-warning"></i></div>
                                    <div class="progress flex-grow-1" style="height: 8px;">
                                        <div class="progress-bar bg-success" style="width: 70%"></div>
                                    </div>
                                    <div class="text-muted ms-2">70%</div>
                                </div>
                                <div class="mb-2 d-flex align-items-center">
                                    <div class="text-muted me-2">4 <i class="fas fa-star text-warning"></i></div>
                                    <div class="progress flex-grow-1" style="height: 8px;">
                                        <div class="progress-bar bg-success" style="width: 20%"></div>
                                    </div>
                                    <div class="text-muted ms-2">20%</div>
                                </div>
                                <div class="mb-2 d-flex align-items-center">
                                    <div class="text-muted me-2">3 <i class="fas fa-star text-warning"></i></div>
                                    <div class="progress flex-grow-1" style="height: 8px;">
                                        <div class="progress-bar bg-warning" style="width: 8%"></div>
                                    </div>
                                    <div class="text-muted ms-2">8%</div>
                                </div>
                                <div class="mb-2 d-flex align-items-center">
                                    <div class="text-muted me-2">2 <i class="fas fa-star text-warning"></i></div>
                                    <div class="progress flex-grow-1" style="height: 8px;">
                                        <div class="progress-bar bg-danger" style="width: 2%"></div>
                                    </div>
                                    <div class="text-muted ms-2">2%</div>
                                </div>
                                <div class="mb-2 d-flex align-items-center">
                                    <div class="text-muted me-2">1 <i class="fas fa-star text-warning"></i></div>
                                    <div class="progress flex-grow-1" style="height: 8px;">
                                        <div class="progress-bar bg-danger" style="width: 0%"></div>
                                    </div>
                                    <div class="text-muted ms-2">0%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Review List -->
                    <div class="border-top pt-4">
                        <div class="mb-4 pb-4 border-bottom">
                            <div class="d-flex mb-3">
                                <img src="assets/img/avatars/avatar1.jpg" class="rounded-circle me-3" width="48" height="48" alt="User Avatar">
                                <div>
                                    <h6 class="mb-0">Budi Santoso</h6>
                                    <div class="d-flex align-items-center">
                                        <div class="text-warning me-2">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                        </div>
                                        <span class="text-muted small">2 bulan yang lalu</span>
                                    </div>
                                </div>
                            </div>
                            <p>Novel luar biasa yang mengangkat cerita sejarah kolonialisme di Indonesia dengan sangat baik. Karakter-karakternya hidup dan dialog-dialognya tajam. Sangat direkomendasikan!</p>
                        </div>
                        
                        <div class="mb-4 pb-4 border-bottom">
                            <div class="d-flex mb-3">
                                <img src="assets/img/avatars/avatar2.jpg" class="rounded-circle me-3" width="48" height="48" alt="User Avatar">
                                <div>
                                    <h6 class="mb-0">Siti Rahmawati</h6>
                                    <div class="d-flex align-items-center">
                                        <div class="text-warning me-2">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="far fa-star"></i>
                                        </div>
                                        <span class="text-muted small">3 bulan yang lalu</span>
                                    </div>
                                </div>
                            </div>
                            <p>Saya sangat terkesan dengan penulisan Pramoedya yang detail dan menghidupkan sejarah. Buku ini membuat saya lebih memahami perjuangan bangsa Indonesia di masa kolonial. Hanya saja beberapa bagian terasa agak lambat.</p>
                        </div>
                        
                        <div class="text-center mt-4">
                            <a href="#" class="btn btn-outline-primary">Lihat Semua Ulasan</a>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="shipping" role="tabpanel" aria-labelledby="shipping-tab">
                    <h4>Informasi Pengiriman</h4>
                    <p>Pengiriman dilakukan dalam 1-3 hari kerja setelah pembayaran dikonfirmasi.</p>
                    
                    <h5 class="mt-4">Metode Pengiriman</h5>
                    <ul>
                        <li>JNE (Regular, YES)</li>
                        <li>J&T Express</li>
                        <li>SiCepat</li>
                        <li>AnterAja</li>
                        <li>Ninja Express</li>
                    </ul>
                    
                    <h5 class="mt-4">Metode Pembayaran</h5>
                    <ul>
                        <li>Transfer Bank (BCA, Mandiri, BNI, BRI)</li>
                        <li>Virtual Account</li>
                        <li>E-Wallet (GoPay, OVO, Dana, LinkAja)</li>
                        <li>Kartu Kredit</li>
                        <li>Cicilan 0%</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Related Books -->
        <section class="mt-5">
            <h3 class="mb-4">Buku Terkait</h3>
            <div class="row g-3">
                <div class="col-lg-2 col-md-3 col-6">
                    <div class="card h-100 border-0 shadow-sm product-card">
                        <img src="assets/img/books/book2.jpg" class="card-img-top" alt="Buku Terkait">
                        <div class="card-body p-3">
                            <h6 class="card-title mb-1 text-truncate">Anak Semua Bangsa</h6>
                            <p class="small text-muted mb-2 text-truncate">Pramoedya Ananta Toer</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold">Rp 89.000</span>
                                <button class="btn btn-sm btn-outline-primary"><i class="fas fa-shopping-cart"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-3 col-6">
                    <div class="card h-100 border-0 shadow-sm product-card">
                        <div class="position-relative">
                            <img src="assets/img/books/book3.jpg" class="card-img-top" alt="Buku Terkait">
                            <div class="position-absolute top-0 start-0 bg-danger text-white py-1 px-2 small">-30%</div>
                        </div>
                        <div class="card-body p-3">
                            <h6 class="card-title mb-1 text-truncate">Jejak Langkah</h6>
                            <p class="small text-muted mb-2 text-truncate">Pramoedya Ananta Toer</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-danger fw-bold">Rp 62.300</span>
                                    <span class="small text-decoration-line-through text-muted">Rp 89.000</span>
                                </div>
                                <button class="btn btn-sm btn-outline-primary"><i class="fas fa-shopping-cart"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-3 col-6">
                    <div class="card h-100 border-0 shadow-sm product-card">
                        <img src="assets/img/books/book4.jpg" class="card-img-top" alt="Buku Terkait">
                        <div class="card-body p-3">
                            <h6 class="card-title mb-1 text-truncate">Rumah Kaca</h6>
                            <p class="small text-muted mb-2 text-truncate">Pramoedya Ananta Toer</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold">Rp 89.000</span>
                                <button class="btn btn-sm btn-outline-primary"><i class="fas fa-shopping-cart"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-3 col-6">
                    <div class="card h-100 border-0 shadow-sm product-card">
                        <img src="assets/img/books/book5.jpg" class="card-img-top" alt="Buku Terkait">
                        <div class="card-body p-3">
                            <h6 class="card-title mb-1 text-truncate">Gadis Pantai</h6>
                            <p class="small text-muted mb-2 text-truncate">Pramoedya Ananta Toer</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold">Rp 75.000</span>
                                <button class="btn btn-sm btn-outline-primary"><i class="fas fa-shopping-cart"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-3 col-6">
                    <div class="card h-100 border-0 shadow-sm product-card">
                        <img src="assets/img/books/book6.jpg" class="card-img-top" alt="Buku Terkait">
                        <div class="card-body p-3">
                            <h6 class="card-title mb-1 text-truncate">Perburuan</h6>
                            <p class="small text-muted mb-2 text-truncate">Pramoedya Ananta Toer</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold">Rp 72.000</span>
                                <button class="btn btn-sm btn-outline-primary"><i class="fas fa-shopping-cart"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-3 col-6">
                    <div class="card h-100 border-0 shadow-sm product-card">
                        <img src="assets/img/books/book7.jpg" class="card-img-top" alt="Buku Terkait">
                        <div class="card-body p-3">
                            <h6 class="card-title mb-1 text-truncate">Arok Dedes</h6>
                            <p class="small text-muted mb-2 text-truncate">Pramoedya Ananta Toer</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold">Rp 80.000</span>
                                <button class="btn btn-sm btn-outline-primary"><i class="fas fa-shopping-cart"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</section>

<?php include 'templates/footer.php'; ?> 