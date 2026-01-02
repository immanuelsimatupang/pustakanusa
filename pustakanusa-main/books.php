<?php
$title = "Katalog Buku - PustakaNusa";
include 'templates/header.php';

// Demo books data - In a real implementation, this would come from a database
$categories = [
    1 => 'Fiksi',
    2 => 'Non-Fiksi',
    3 => 'Sejarah',
    4 => 'Pendidikan',
    5 => 'Sains dan Teknologi',
];

$books = [
    [
        'id' => 1,
        'title' => 'Bumi Manusia',
        'author' => 'Pramoedya Ananta Toer',
        'category_id' => 1,
        'cover' => 'assets/img/book1.jpg',
        'price' => 85000,
        'is_free' => false,
        'description' => 'Novel pertama dari Tetralogi Buru yang menceritakan kisah Minke, seorang pemuda pribumi yang berpendidikan Belanda pada masa kolonial.'
    ],
    [
        'id' => 2,
        'title' => 'Filosofi Teras',
        'author' => 'Henry Manampiring',
        'category_id' => 2,
        'cover' => 'assets/img/book2.jpg',
        'price' => 98000,
        'is_free' => false,
        'description' => 'Buku yang menjelaskan filosofi Stoa dan bagaimana menerapkannya dalam kehidupan sehari-hari untuk menjalani hidup yang lebih tenang dan bijaksana.'
    ],
    [
        'id' => 3,
        'title' => 'Laskar Pelangi',
        'author' => 'Andrea Hirata',
        'category_id' => 1,
        'cover' => 'assets/img/book3.jpg',
        'price' => 0,
        'is_free' => true,
        'description' => 'Novel yang menceritakan kisah perjuangan 10 anak dari keluarga miskin yang bersekolah di sebuah sekolah Muhammadiyah di Belitung yang penuh dengan keterbatasan.'
    ],
    [
        'id' => 4,
        'title' => 'Indonesia Etc.',
        'author' => 'Elizabeth Pisani',
        'category_id' => 3,
        'cover' => 'assets/img/book4.jpg',
        'price' => 110000,
        'is_free' => false,
        'description' => 'Sebuah catatan perjalanan seorang jurnalis dan peneliti tentang Indonesia, menjelajahi keragaman budaya, politik, dan kehidupan sehari-hari di negara kepulauan ini.'
    ],
    [
        'id' => 5,
        'title' => 'Sapiens: Riwayat Singkat Umat Manusia',
        'author' => 'Yuval Noah Harari',
        'category_id' => 2,
        'cover' => 'assets/img/book5.jpg',
        'price' => 125000,
        'is_free' => false,
        'description' => 'Buku yang mengeksplorasi sejarah manusia dari kemunculan Homo sapiens di Era Batu hingga revolusi kognitif, pertanian, dan ilmiah yang membentuk dunia modern.'
    ],
    [
        'id' => 6,
        'title' => 'Sejarah Dunia yang Disembunyikan',
        'author' => 'Jonathan Black',
        'category_id' => 3,
        'cover' => 'assets/img/book6.jpg',
        'price' => 115000,
        'is_free' => false,
        'description' => 'Buku yang menyingkap konspirasi dan rahasia tersembunyi di balik sejarah dunia yang telah dibentuk oleh berbagai kelompok rahasia dan tokoh berpengaruh.'
    ],
    [
        'id' => 7,
        'title' => 'Atomic Habits',
        'author' => 'James Clear',
        'category_id' => 2,
        'cover' => 'assets/img/book7.jpg',
        'price' => 105000,
        'is_free' => false,
        'description' => 'Buku yang menjelaskan bagaimana perubahan kecil dalam perilaku sehari-hari dapat membawa dampak besar pada kehidupan dan produktivitas Anda.'
    ],
    [
        'id' => 8,
        'title' => 'Matematika Diskrit dan Aplikasinya',
        'author' => 'Kenneth H. Rosen',
        'category_id' => 4,
        'cover' => 'assets/img/book8.jpg',
        'price' => 0,
        'is_free' => true,
        'description' => 'Buku teks yang komprehensif tentang konsep matematika diskrit dan aplikasinya dalam ilmu komputer dan bidang teknik lainnya.'
    ],
];

// Filter books by category if specified
$current_category = isset($_GET['category']) ? intval($_GET['category']) : null;
$filtered_books = $current_category ? array_filter($books, function($book) use ($current_category) {
    return $book['category_id'] == $current_category;
}) : $books;

// Search functionality
$search_term = isset($_GET['search']) ? $_GET['search'] : '';
if ($search_term) {
    $filtered_books = array_filter($filtered_books, function($book) use ($search_term) {
        return stripos($book['title'], $search_term) !== false || 
               stripos($book['author'], $search_term) !== false ||
               stripos($book['description'], $search_term) !== false;
    });
}

// Sort functionality
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'title';
$sort_dir = isset($_GET['dir']) && $_GET['dir'] == 'desc' ? SORT_DESC : SORT_ASC;

$sort_keys = array_column($filtered_books, $sort_by);
array_multisort($sort_keys, $sort_dir, $filtered_books);
?>

<!-- Books Catalog Section -->
<section id="books-header" class="bg-primary bg-opacity-10 py-4">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="display-5 fw-bold mb-0">Katalog Buku</h1>
                <p class="lead">Jelajahi koleksi terlengkap buku kami</p>
            </div>
            <div class="col-md-6">
                <form action="books.php" method="GET" class="d-flex">
                    <input type="text" name="search" class="form-control me-2" placeholder="Cari judul, penulis..." value="<?php echo htmlspecialchars($search_term); ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<section id="books-catalog" class="py-5">
    <div class="container">
        <div class="row">
            <!-- Sidebar Filters -->
            <div class="col-lg-3 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Filter Buku</h5>
                    </div>
                    <div class="card-body">
                        <h6 class="mb-3">Kategori</h6>
                        <ul class="list-group list-group-flush mb-4">
                            <li class="list-group-item <?php echo $current_category === null ? 'active bg-primary text-white' : ''; ?>">
                                <a href="books.php" class="text-decoration-none <?php echo $current_category === null ? 'text-white' : 'text-dark'; ?>">Semua Kategori</a>
                            </li>
                            <?php foreach ($categories as $id => $name): ?>
                            <li class="list-group-item <?php echo $current_category === $id ? 'active bg-primary text-white' : ''; ?>">
                                <a href="books.php?category=<?php echo $id; ?>" class="text-decoration-none <?php echo $current_category === $id ? 'text-white' : 'text-dark'; ?>">
                                    <?php echo $name; ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>

                        <h6 class="mb-3">Urutkan</h6>
                        <div class="mb-3">
                            <select class="form-select" id="sort-select" onchange="window.location.href=this.value">
                                <option value="books.php?sort=title&dir=asc<?php echo $current_category ? '&category='.$current_category : ''; ?><?php echo $search_term ? '&search='.urlencode($search_term) : ''; ?>" <?php echo $sort_by === 'title' && $sort_dir === SORT_ASC ? 'selected' : ''; ?>>
                                    Judul (A-Z)
                                </option>
                                <option value="books.php?sort=title&dir=desc<?php echo $current_category ? '&category='.$current_category : ''; ?><?php echo $search_term ? '&search='.urlencode($search_term) : ''; ?>" <?php echo $sort_by === 'title' && $sort_dir === SORT_DESC ? 'selected' : ''; ?>>
                                    Judul (Z-A)
                                </option>
                                <option value="books.php?sort=author&dir=asc<?php echo $current_category ? '&category='.$current_category : ''; ?><?php echo $search_term ? '&search='.urlencode($search_term) : ''; ?>" <?php echo $sort_by === 'author' && $sort_dir === SORT_ASC ? 'selected' : ''; ?>>
                                    Penulis (A-Z)
                                </option>
                                <option value="books.php?sort=price&dir=asc<?php echo $current_category ? '&category='.$current_category : ''; ?><?php echo $search_term ? '&search='.urlencode($search_term) : ''; ?>" <?php echo $sort_by === 'price' && $sort_dir === SORT_ASC ? 'selected' : ''; ?>>
                                    Harga (Rendah-Tinggi)
                                </option>
                                <option value="books.php?sort=price&dir=desc<?php echo $current_category ? '&category='.$current_category : ''; ?><?php echo $search_term ? '&search='.urlencode($search_term) : ''; ?>" <?php echo $sort_by === 'price' && $sort_dir === SORT_DESC ? 'selected' : ''; ?>>
                                    Harga (Tinggi-Rendah)
                                </option>
                            </select>
                        </div>

                        <h6 class="mb-3">Aksesibilitas</h6>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="free-books" onchange="window.location.href='books.php?free=' + (this.checked ? '1' : '0') + '<?php echo $current_category ? '&category='.$current_category : ''; ?><?php echo $search_term ? '&search='.urlencode($search_term) : ''; ?>'" <?php echo isset($_GET['free']) && $_GET['free'] == '1' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="free-books">
                                Hanya tampilkan buku gratis
                            </label>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-body">
                        <h5 class="card-title">Bergabunglah dengan Premium</h5>
                        <p class="card-text">Akses ratusan buku premium dan fitur canggih mulai dari Rp59.000/bulan</p>
                        <a href="subscription.php" class="btn btn-primary w-100">Lihat Paket</a>
                    </div>
                </div>
            </div>

            <!-- Books Grid -->
            <div class="col-lg-9">
                <?php if ($search_term): ?>
                <div class="alert alert-info mb-4">
                    <i class="fas fa-search me-2"></i> Hasil pencarian untuk: <strong><?php echo htmlspecialchars($search_term); ?></strong> (<?php echo count($filtered_books); ?> buku ditemukan)
                    <a href="books.php<?php echo $current_category ? '?category='.$current_category : ''; ?>" class="float-end">Reset</a>
                </div>
                <?php endif; ?>

                <?php if (isset($_GET['free']) && $_GET['free'] == '1'): ?>
                <div class="alert alert-info mb-4">
                    <i class="fas fa-filter me-2"></i> Menampilkan hanya buku gratis
                    <a href="books.php<?php echo $current_category ? '?category='.$current_category : ''; ?><?php echo $search_term ? '&search='.urlencode($search_term) : ''; ?>" class="float-end">Reset</a>
                </div>
                <?php endif; ?>

                <?php if (empty($filtered_books)): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i> Maaf, tidak ada buku yang sesuai dengan kriteria Anda. Silakan coba filter berbeda.
                </div>
                <?php else: ?>
                <div class="row">
                    <?php foreach ($filtered_books as $book): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 border-0 shadow-sm book-card">
                            <img src="<?php echo $book['cover']; ?>" class="card-img-top" alt="<?php echo $book['title']; ?>">
                            <div class="card-body">
                                <span class="badge <?php echo $book['is_free'] ? 'bg-success' : 'bg-primary'; ?> mb-2">
                                    <?php echo $categories[$book['category_id']]; ?>
                                </span>
                                <h5 class="card-title"><?php echo $book['title']; ?></h5>
                                <p class="card-text small text-muted"><?php echo $book['author']; ?></p>
                                <p class="card-text small book-description"><?php echo $book['description']; ?></p>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <span class="<?php echo $book['is_free'] ? 'text-success' : 'text-primary'; ?> fw-bold">
                                        <?php echo $book['is_free'] ? 'Gratis' : 'Rp ' . number_format($book['price'], 0, ',', '.'); ?>
                                    </span>
                                    <div>
                                        <a href="reader.php?book=<?php echo $book['id']; ?>" class="btn btn-sm btn-outline-primary me-1">
                                            <i class="fas fa-book-reader"></i>
                                        </a>
                                        <?php if (!$book['is_free']): ?>
                                        <a href="#" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-shopping-cart"></i>
                                        </a>
                                        <?php else: ?>
                                        <a href="#" class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item disabled">
                            <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Sebelumnya</a>
                        </li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#">Selanjutnya</a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Featured Collections -->
<section id="featured-collections" class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">Koleksi Pilihan</h2>
        
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-award text-primary fa-3x mb-3"></i>
                        <h4>Pemenang Penghargaan</h4>
                        <p class="text-muted mb-3">Kumpulan karya sastra terbaik yang telah meraih berbagai penghargaan bergengsi di Indonesia dan dunia.</p>
                        <a href="#" class="btn btn-outline-primary">Lihat Koleksi</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-book-open text-primary fa-3x mb-3"></i>
                        <h4>Domain Publik</h4>
                        <p class="text-muted mb-3">Akses gratis ke ribuan karya klasik dari penulis terkenal yang telah memasuki domain publik.</p>
                        <a href="#" class="btn btn-outline-primary">Lihat Koleksi</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-user-edit text-primary fa-3x mb-3"></i>
                        <h4>Penulis Lokal</h4>
                        <p class="text-muted mb-3">Dukung penulis Indonesia dengan menjelajahi koleksi karya terbaik dari penulis lokal berbakat.</p>
                        <a href="#" class="btn btn-outline-primary">Lihat Koleksi</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Book Box Promotion -->
<section id="bookbox-promo" class="py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 mb-4 mb-md-0">
                <img src="assets/img/bookbox.jpg" alt="BookBox" class="img-fluid rounded shadow">
            </div>
            <div class="col-md-6">
                <h2 class="display-5 fw-bold mb-3">BookBox</h2>
                <p class="lead mb-4">Terima 2-3 buku pilihan setiap bulan langsung di depan pintu Anda</p>
                <ul class="list-unstyled mb-4">
                    <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> Buku dipilih khusus sesuai preferensi Anda</li>
                    <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> Diskon hingga 30% dari harga retail</li>
                    <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> Pengiriman gratis ke seluruh Indonesia</li>
                    <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> Merchandise eksklusif setiap bulan</li>
                </ul>
                <a href="bookbox.php" class="btn btn-primary">Mulai dari Rp199.000/bulan</a>
            </div>
        </div>
    </div>
</section>

<style>
.book-card {
    transition: transform 0.3s;
}
.book-card:hover {
    transform: translateY(-5px);
}
.book-description {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>

<?php include 'templates/footer.php'; ?> 