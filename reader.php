<?php
$title = "Pembaca Buku Digital - PustakaNusa";
$extraStyles = '<link href="assets/css/reader.css" rel="stylesheet">';
$extraScripts = '<script src="assets/js/reader.js"></script>';
include 'templates/header.php';

// Ambil ID buku dan nomor halaman dari parameter URL
$book_id = isset($_GET['id']) ? intval($_GET['id']) : 1;
$current_page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// TODO: Implementasi query database untuk mengambil detail buku dan konten sampel

// Demo books data - Dalam implementasi nyata, ini akan diambil dari database
$books = [
    1 => [
        'id' => 1,
        'title' => 'Bumi Manusia',
        'author' => 'Pramoedya Ananta Toer',
        'cover' => 'assets/img/books/book1.jpg',
        'total_pages' => 150,
        'current_page' => $current_page,
        'sample_content' => [
            1 => "<p>Pengantar dari Penerbit</p><p>Novel <em>Bumi Manusia</em> karya Pramoedya Ananta Toer ini merupakan bagian pertama dari Tetralogi Buru. Novel ini mengambil latar belakang dan kritik terhadap kehidupan di Hindia Belanda (sekarang Indonesia) pada masa menjelang kebangkitan nasional.</p><p>Karya ini merupakan salah satu karya sastra terbaik yang pernah dihasilkan oleh sastrawan Indonesia. Novel ini, bersama dengan tiga novel lainnya dalam Tetralogi Buru, telah diterjemahkan ke dalam lebih dari 41 bahasa di dunia dan diakui sebagai mahakarya dunia.</p><p>Semoga pembaca dapat menikmati karya monumental ini.</p>",
            2 => "<h2>Bab 1</h2><p>Semua berawal ketika aku mula pertama masuk E.L.S., Sekolah Dasar Belanda, Tuan Herman Mellema, ayah Annelies, muncul di rumah orangtuaku. Ia muncul sebagai pria tua dengan janggut kasar yang tak terurus, membawa tongkat berlilit kulit ular hitam mengkilat, berpakaian Eropa semuanya, dan bersepatu.</p><p>Waktu itu aku belum punya nama kecuali panggilan sehari-hari: Minke. Orang memanggilku: Minke! Dan meski nama itu terdengar kurang menyenangkan di telinga Jawa – sebab ia bisa saja berarti: kekejian, atau: keaiban – aku tak pernah membantah. Nampaknya memang begitulah yang dikehendaki.</p>",
            3 => "<p>Kami singgah sebentar di warung kopi dan minum kopi susu dengan kue-kue manis. Aku tak tahu nama semua kue yang kubeli. Tapi dia tahu: pannekoeken-met-boter, taartnja, ik-weet-niet, spekkoek, dan segala macam lagi.</p><p>'Kau pandai sekali berbahasa Belanda, Nyai,' kataku memuji.</p><p>'Tidak, Tuan, bundaku seorang Nyai juga, aku banyak omong dengan berbagai Nyonya dan Tuan yang datang ke sini, terutama dengan Tuan Mellema, dan banyak lagi, Tuan. Aku bicara saja, tak mengerti tata bahasa. Tak bersekolah bukan?'</p>",
        ]
    ],
    2 => [
        'id' => 2,
        'title' => 'Laut Bercerita',
        'author' => 'Leila S. Chudori',
        'cover' => 'assets/img/books/book3.jpg',
        'total_pages' => 120,
        'current_page' => $current_page,
        'sample_content' => [
            1 => "<p>Pengantar dari Penerbit</p><p>Novel <em>Laut Bercerita</em> karya Leila S. Chudori merupakan novel yang mengangkat tema tentang penculikan aktivis di Indonesia pada tahun 1998.</p><p>Novel ini mengisahkan tentang seorang mahasiswa aktivis bernama Laut yang hilang setelah diculik oleh orang-orang tak dikenal pada tahun 1998. Selama 20 tahun berikutnya, keluarganya hidup dalam ketidakpastian tentang keberadaannya.</p>",
            2 => "<h2>Bab 1 - Biru</h2><p>Jakarta, Maret 1998</p><p>Seperti ketika mengalami demam tinggi, aku merasakan panas itu datang seketika. Wajahku, leherku, telingaku, seperti akan meledak hingga kepalaku berdentam-dentam seperti genderang. Kulihat wajah Daniel memerah menahan amarah, matanya berkaca-kaca tidak seperti biasanya. Aku bukan sedang mengalami demam tinggi. Ini jauh lebih parah, kami sedang marah. Kami, para mahasiswa yang dipanggil aktivis, sedang marah.</p>",
            3 => "<p>Sesungguhnya panas yang kami rasakan, dari dinding perut sampai kerongkongan, sudah terjadi sejak lama. Kemuakan atas rezim yang begitu pongah, menikmati buah dari penindasan, korupsi, dan kelicikan bisnis yang hanya menguntungkan keluarga dan kerabat, seperti terus menyalakan api kemarahan yang perlahan membesar tersulut oleh kelaparan dan kesengsaraan di berbagai wilayah Indonesia.</p><p>Tak ada yang bisa menghindar dari kegelisahan ini, bahkan Kinan yang biasanya tenang dan terukur pun perlahan mulai gusar. Mungkin karena puluhan karton mie instan di kantor Majalah Bernas menjadi kian menumpuk. Mungkin karena tulisan kami di Bernas kian tajam dan mengundang para pembaca mahasiswa untuk selalu datang.</p>",
        ]
    ]
];

// Pilih buku berdasarkan ID
$book = isset($books[$book_id]) ? $books[$book_id] : $books[1];

// Pastikan nomor halaman tidak melebihi total halaman
if ($book['current_page'] > $book['total_pages']) {
    $book['current_page'] = $book['total_pages'];
} elseif ($book['current_page'] < 1) {
    $book['current_page'] = 1;
}

// Siapkan konten halaman dari array sample_content
$page_content = isset($book['sample_content'][$book['current_page']]) 
    ? $book['sample_content'][$book['current_page']] 
    : "<p>Halaman tidak tersedia. Silakan pilih halaman lain.</p>";
?>

<!-- Book Reader Interface -->
<div class="reader-container">
    <!-- Left Sidebar -->
    <div class="sidebar-left">
        <div class="book-info mb-4">
            <img src="<?php echo $book['cover']; ?>" alt="<?php echo $book['title']; ?>" class="book-cover mb-3">
            <h5 class="book-title"><?php echo $book['title']; ?></h5>
            <p class="book-author text-muted mb-3"><?php echo $book['author']; ?></p>
            <div class="progress mb-2">
                <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo ($book['current_page'] / $book['total_pages']) * 100; ?>%" aria-valuenow="<?php echo $book['current_page']; ?>" aria-valuemin="0" aria-valuemax="<?php echo $book['total_pages']; ?>"></div>
            </div>
            <small class="text-muted">Halaman <?php echo $book['current_page']; ?> dari <?php echo $book['total_pages']; ?></small>
        </div>

        <div class="book-controls mb-4">
            <h6 class="text-uppercase fw-bold small mb-3">Navigasi</h6>
            <div class="d-grid gap-2">
                <button class="btn btn-outline-primary btn-sm" id="btn-table-of-contents">
                    <i class="fas fa-list me-2"></i> Daftar Isi
                </button>
                <button class="btn btn-outline-primary btn-sm" id="btn-bookmarks">
                    <i class="fas fa-bookmark me-2"></i> Penanda
                </button>
                <button class="btn btn-outline-primary btn-sm" id="btn-notes">
                    <i class="fas fa-sticky-note me-2"></i> Catatan
                </button>
                <button class="btn btn-outline-primary btn-sm" id="btn-search">
                    <i class="fas fa-search me-2"></i> Pencarian
                </button>
            </div>
        </div>

        <div class="book-settings">
            <h6 class="text-uppercase fw-bold small mb-3">Pengaturan</h6>
            <div class="mb-3">
                <label for="font-size" class="form-label d-flex justify-content-between">
                    <span>Ukuran Font</span>
                    <span id="font-size-value">16px</span>
                </label>
                <input type="range" class="form-range" min="12" max="24" value="16" id="font-size">
            </div>
            <div class="mb-3">
                <label for="line-height" class="form-label d-flex justify-content-between">
                    <span>Jarak Baris</span>
                    <span id="line-height-value">1.5</span>
                </label>
                <input type="range" class="form-range" min="1" max="3" step="0.1" value="1.5" id="line-height">
            </div>
            <div class="mb-3">
                <label class="form-label">Mode Tampilan</label>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-primary flex-grow-1 theme-btn" data-theme="light">
                        <i class="fas fa-sun me-1"></i> Terang
                    </button>
                    <button class="btn btn-sm btn-outline-primary flex-grow-1 theme-btn" data-theme="sepia">
                        <i class="fas fa-moon me-1"></i> Sepia
                    </button>
                    <button class="btn btn-sm btn-outline-primary flex-grow-1 theme-btn" data-theme="dark">
                        <i class="fas fa-moon me-1"></i> Gelap
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="reader-content" id="reader-content">
        <!-- Top Navigation Bar -->
        <div class="reader-navbar">
            <button class="btn btn-sm btn-outline-primary" id="toggle-sidebar-left">
                <i class="fas fa-bars"></i>
            </button>
            <div class="reader-title d-none d-md-block">
                <?php echo $book['title']; ?> - <?php echo $book['author']; ?>
            </div>
            <div class="reader-actions">
                <button class="btn btn-sm btn-outline-primary" id="btn-add-bookmark" title="Tambah Penanda">
                    <i class="fas fa-bookmark"></i>
                </button>
                <button class="btn btn-sm btn-outline-primary" id="btn-add-note" title="Tambah Catatan">
                    <i class="fas fa-sticky-note"></i>
                </button>
                <button class="btn btn-sm btn-outline-primary" id="toggle-sidebar-right" title="Komentar">
                    <i class="fas fa-comments"></i>
                </button>
            </div>
        </div>

        <!-- Book Content -->
        <div class="book-page-content" id="book-content">
            <?php echo $page_content; ?>
        </div>

        <!-- Bottom Navigation -->
        <div class="reader-bottom-nav">
            <a href="?id=<?php echo $book_id; ?>&page=<?php echo max(1, $book['current_page'] - 1); ?>" class="btn btn-primary <?php echo ($book['current_page'] <= 1) ? 'disabled' : ''; ?>">
                <i class="fas fa-chevron-left me-2"></i> Sebelumnya
            </a>
            <span class="current-page">
                Halaman <?php echo $book['current_page']; ?> dari <?php echo $book['total_pages']; ?>
            </span>
            <a href="?id=<?php echo $book_id; ?>&page=<?php echo min($book['total_pages'], $book['current_page'] + 1); ?>" class="btn btn-primary <?php echo ($book['current_page'] >= $book['total_pages']) ? 'disabled' : ''; ?>">
                Selanjutnya <i class="fas fa-chevron-right ms-2"></i>
            </a>
        </div>
    </div>

    <!-- Right Sidebar -->
    <div class="sidebar-right">
        <div class="sidebar-header">
            <h5 class="mb-0">Komentar & Diskusi</h5>
            <button class="btn btn-sm btn-close" id="close-sidebar-right"></button>
        </div>

        <div class="comments-container">
            <div class="comment-form mb-4">
                <textarea class="form-control mb-2" rows="3" placeholder="Tulis komentar tentang halaman ini..."></textarea>
                <button class="btn btn-primary w-100">Kirim Komentar</button>
            </div>

            <div class="comments-list">
                <div class="comment-item">
                    <div class="comment-header">
                        <img src="assets/img/avatars/avatar1.jpg" alt="User" class="comment-avatar">
                        <div>
                            <h6 class="mb-0">Dian Sastrowardoyo</h6>
                            <small class="text-muted">2 jam yang lalu</small>
                        </div>
                    </div>
                    <div class="comment-body">
                        <p>Bagian ini sangat menarik, memberikan gambaran yang jelas tentang situasi sosial pada masa itu. Pramoedya memang hebat dalam menggambarkan kondisi masyarakat.</p>
                    </div>
                    <div class="comment-actions">
                        <button class="btn btn-sm btn-link">Balas</button>
                        <button class="btn btn-sm btn-link">Suka (5)</button>
                    </div>
                </div>

                <div class="comment-item">
                    <div class="comment-header">
                        <img src="assets/img/avatars/avatar2.jpg" alt="User" class="comment-avatar">
                        <div>
                            <h6 class="mb-0">Reza Rahadian</h6>
                            <small class="text-muted">Kemarin</small>
                        </div>
                    </div>
                    <div class="comment-body">
                        <p>Bumi Manusia adalah salah satu karya sastra terbaik Indonesia. Saya sudah baca berkali-kali dan selalu menemukan hal baru setiap kali membacanya.</p>
                    </div>
                    <div class="comment-actions">
                        <button class="btn btn-sm btn-link">Balas</button>
                        <button class="btn btn-sm btn-link">Suka (12)</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<!-- Table of Contents Modal -->
<div class="modal fade" id="tocModal" tabindex="-1" aria-labelledby="tocModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tocModalLabel">Daftar Isi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="list-group list-group-flush">
                    <?php
                    // Buat daftar isi berdasarkan sampel konten
                    $toc_titles = [
                        1 => 'Pengantar',
                        2 => 'Bab 1',
                        3 => 'Bab 2'
                    ];
                    
                    foreach ($toc_titles as $page_num => $title) {
                        if (isset($book['sample_content'][$page_num])) {
                            echo '<li class="list-group-item' . ($page_num == $book['current_page'] ? ' active' : '') . '">';
                            echo '<a href="?id=' . $book_id . '&page=' . $page_num . '">' . $title . '</a>';
                            echo '</li>';
                        }
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Bookmarks Modal -->
<div class="modal fade" id="bookmarksModal" tabindex="-1" aria-labelledby="bookmarksModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bookmarksModalLabel">Penanda Halaman</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i> Anda belum memiliki penanda di buku ini.
                </div>
                <p class="text-center text-muted">
                    Tambahkan penanda dengan mengklik tombol <i class="fas fa-bookmark"></i> saat membaca.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Notes Modal -->
<div class="modal fade" id="notesModal" tabindex="-1" aria-labelledby="notesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="notesModalLabel">Catatan Buku</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i> Anda belum memiliki catatan di buku ini.
                </div>
                <p class="text-center text-muted">
                    Tambahkan catatan dengan memilih teks dan mengklik tombol <i class="fas fa-sticky-note"></i>.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Search Modal -->
<div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="searchModalLabel">Cari Dalam Buku</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="input-group mb-3">
                    <input type="text" class="form-control" placeholder="Ketik kata kunci..." aria-label="Kata kunci pencarian">
                    <button class="btn btn-primary" type="button" id="btn-search-book">Cari</button>
                </div>
                <div class="mt-3">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> Masukkan kata kunci untuk mencari dalam buku.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Kembali ke Detail Buku -->
<div class="position-fixed bottom-0 end-0 p-3">
    <a href="book-detail.php?id=<?php echo $book_id; ?>" class="btn btn-secondary btn-sm rounded-pill shadow">
        <i class="fas fa-arrow-left me-1"></i> Kembali ke Detail Buku
    </a>
</div>

<?php include 'templates/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all modals
    const tocButton = document.getElementById('btn-table-of-contents');
    const bookmarksButton = document.getElementById('btn-bookmarks');
    const notesButton = document.getElementById('btn-notes');
    const searchButton = document.getElementById('btn-search');
    
    tocButton.addEventListener('click', function() {
        new bootstrap.Modal(document.getElementById('tocModal')).show();
    });
    
    bookmarksButton.addEventListener('click', function() {
        new bootstrap.Modal(document.getElementById('bookmarksModal')).show();
    });
    
    notesButton.addEventListener('click', function() {
        new bootstrap.Modal(document.getElementById('notesModal')).show();
    });
    
    searchButton.addEventListener('click', function() {
        new bootstrap.Modal(document.getElementById('searchModal')).show();
    });
    
    // Toggle sidebars
    const toggleLeftSidebar = document.getElementById('toggle-sidebar-left');
    const toggleRightSidebar = document.getElementById('toggle-sidebar-right');
    const closeRightSidebar = document.getElementById('close-sidebar-right');
    const readerContainer = document.querySelector('.reader-container');
    
    toggleLeftSidebar.addEventListener('click', function() {
        readerContainer.classList.toggle('left-sidebar-collapsed');
    });
    
    toggleRightSidebar.addEventListener('click', function() {
        readerContainer.classList.toggle('right-sidebar-visible');
    });
    
    closeRightSidebar.addEventListener('click', function() {
        readerContainer.classList.remove('right-sidebar-visible');
    });
    
    // Font size and line height controls
    const fontSizeSlider = document.getElementById('font-size');
    const fontSizeValue = document.getElementById('font-size-value');
    const lineHeightSlider = document.getElementById('line-height');
    const lineHeightValue = document.getElementById('line-height-value');
    const bookContent = document.getElementById('book-content');
    
    fontSizeSlider.addEventListener('input', function() {
        const size = this.value;
        fontSizeValue.textContent = size + 'px';
        bookContent.style.fontSize = size + 'px';
    });
    
    lineHeightSlider.addEventListener('input', function() {
        const height = this.value;
        lineHeightValue.textContent = height;
        bookContent.style.lineHeight = height;
    });
    
    // Theme settings
    const themeButtons = document.querySelectorAll('.theme-btn');
    
    themeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const theme = this.getAttribute('data-theme');
            
            // Remove active class from all buttons
            themeButtons.forEach(btn => btn.classList.remove('active', 'btn-primary', 'text-white'));
            
            // Add active class to clicked button
            this.classList.add('active', 'btn-primary', 'text-white');
            
            // Apply theme
            if (theme === 'light') {
                bookContent.style.backgroundColor = '#ffffff';
                bookContent.style.color = '#212529';
            } else if (theme === 'sepia') {
                bookContent.style.backgroundColor = '#f8f1e3';
                bookContent.style.color = '#5b4636';
            } else if (theme === 'dark') {
                bookContent.style.backgroundColor = '#303030';
                bookContent.style.color = '#e0e0e0';
            }
        });
    });
    
    // Highlight the light theme by default
    themeButtons[0].click();
});
</script> 