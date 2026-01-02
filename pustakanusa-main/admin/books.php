<?php
// File manajemen buku
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

// Fungsi untuk sanitasi input
function sanitize($input) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($input));
}

// Status tabel
$tables_status = [
    'books' => table_exists($conn, 'books'),
    'categories' => table_exists($conn, 'categories'),
    'publishers' => table_exists($conn, 'publishers'),
    'authors' => table_exists($conn, 'authors')
];

// Cek semua tabel yang diperlukan
$all_required_tables = !in_array(false, $tables_status);

// Inisialisasi variabel
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$book_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$success_message = '';
$error_message = '';

// Title halaman
$page_title = "Manajemen Buku";

// Menentukan halaman aktif untuk sidebar
$active_page = 'books';

// Ambil daftar buku jika tabel sudah ada
$books = [];

if ($tables_status['books']) {
    // Default filter dan pencarian
    $search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
    $category_filter = isset($_GET['category']) ? intval($_GET['category']) : 0;
    $status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : '';
    
    // Pagination
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;
    
    // Bangun query
    $query_conditions = [];
    $params = [];
    $types = '';
    
    if (!empty($search)) {
        $query_conditions[] = "(b.title LIKE ? OR b.isbn LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $types .= 'ss';
    }
    
    if ($category_filter > 0) {
        $query_conditions[] = "bc.category_id = ?";
        $params[] = $category_filter;
        $types .= 'i';
    }
    
    if (!empty($status_filter)) {
        switch ($status_filter) {
            case 'featured':
                $query_conditions[] = "b.is_featured = 1";
                break;
            case 'bestseller':
                $query_conditions[] = "b.is_bestseller = 1";
                break;
            case 'new':
                $query_conditions[] = "b.is_new = 1";
                break;
            case 'outofstock':
                $query_conditions[] = "b.stock = 0";
                break;
        }
    }
    
    $where_clause = !empty($query_conditions) ? "WHERE " . implode(" AND ", $query_conditions) : "";
    
    // Query untuk menghitung total
    $count_sql = "SELECT COUNT(DISTINCT b.id) as total 
                  FROM books b 
                  LEFT JOIN book_categories bc ON b.id = bc.book_id 
                  $where_clause";
    
    try {
        $count_stmt = $conn->prepare($count_sql);
        
        if (!empty($params)) {
            $count_stmt->bind_param($types, ...$params);
        }
        
        $count_stmt->execute();
        $count_result = $count_stmt->get_result();
        $total_records = $count_result->fetch_assoc()['total'];
        $total_pages = ceil($total_records / $limit);
        
        // Query untuk mengambil data buku
        $sql = "SELECT b.*, p.name as publisher_name,
                GROUP_CONCAT(DISTINCT c.name ORDER BY c.name ASC SEPARATOR ', ') as categories,
                GROUP_CONCAT(DISTINCT a.name ORDER BY a.name ASC SEPARATOR ', ') as authors
                FROM books b
                LEFT JOIN publishers p ON b.publisher_id = p.id
                LEFT JOIN book_categories bc ON b.id = bc.book_id
                LEFT JOIN categories c ON bc.category_id = c.id
                LEFT JOIN book_authors ba ON b.id = ba.book_id
                LEFT JOIN authors a ON ba.author_id = a.id
                $where_clause
                GROUP BY b.id
                ORDER BY b.created_at DESC
                LIMIT ?, ?";
        
        $stmt = $conn->prepare($sql);
        
        if (!empty($params)) {
            $params[] = $offset;
            $params[] = $limit;
            $types .= 'ii';
            $stmt->bind_param($types, ...$params);
        } else {
            $stmt->bind_param("ii", $offset, $limit);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $books[] = $row;
        }
    } catch (Exception $e) {
        $error_message = "Error: " . $e->getMessage();
    }
    
    // Ambil daftar kategori untuk filter
    $categories = [];
    if ($tables_status['categories']) {
        $categories_result = $conn->query("SELECT id, name FROM categories ORDER BY name");
        if ($categories_result && $categories_result->num_rows > 0) {
            while ($row = $categories_result->fetch_assoc()) {
                $categories[] = $row;
            }
        }
    }
}
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
        }
        
        .book-card {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }
        
        .book-card .badge {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 10;
        }
        
        .table th {
            font-weight: 600;
            color: #495057;
        }
        
        .pagination .page-link {
            color: var(--bs-primary);
            border-radius: 5px;
            margin: 0 3px;
        }
        
        .pagination .page-item.active .page-link {
            background-color: var(--bs-primary);
            border-color: var(--bs-primary);
        }
        
        .cover-preview {
            max-width: 80px;
            border-radius: 5px;
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
                    <a class="nav-link <?= $active_page == 'setup' ? 'active' : '' ?>" href="setup_database.php">
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
        <?php if (!$all_required_tables): ?>
            <div class="alert alert-warning bg-white border-0 shadow-sm mb-4">
                <div class="d-flex">
                    <div class="me-3">
                        <i class="fas fa-exclamation-triangle fs-3 text-warning"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold">Tabel Database Belum Lengkap</h5>
                        <p class="mb-0">Beberapa tabel yang diperlukan untuk manajemen buku belum dibuat. Silakan jalankan <a href="setup_database.php" class="alert-link">Setup Database</a> terlebih dahulu.</p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Konten utama sesuai action -->
            <?php if ($action == 'list'): ?>
                <!-- Header halaman -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3 class="mb-0 fw-bold">Manajemen Buku</h3>
                        <p class="text-muted mb-0">Kelola semua buku yang tersedia di toko</p>
                    </div>
                    <div>
                        <a href="books.php?action=add" class="btn btn-primary">
                            <i class="fas fa-plus-circle me-2"></i>
                            Tambah Buku Baru
                        </a>
                    </div>
                </div>
                
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success bg-white border-0 shadow-sm mb-4">
                        <?= $success_message ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger bg-white border-0 shadow-sm mb-4">
                        <?= $error_message ?>
                    </div>
                <?php endif; ?>
                
                <!-- Filter dan pencarian -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form action="" method="get" class="row g-3">
                            <div class="col-lg-4">
                                <label for="search" class="form-label">Cari Buku</label>
                                <input type="text" class="form-control" id="search" name="search" placeholder="Judul atau ISBN" value="<?= htmlspecialchars($search ?? '') ?>">
                            </div>
                            
                            <div class="col-lg-3">
                                <label for="category" class="form-label">Kategori</label>
                                <select class="form-select" id="category" name="category">
                                    <option value="">Semua Kategori</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category['id'] ?>" <?= ($category_filter == $category['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($category['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-lg-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">Semua Status</option>
                                    <option value="featured" <?= ($status_filter == 'featured') ? 'selected' : '' ?>>Featured</option>
                                    <option value="bestseller" <?= ($status_filter == 'bestseller') ? 'selected' : '' ?>>Bestseller</option>
                                    <option value="new" <?= ($status_filter == 'new') ? 'selected' : '' ?>>Baru</option>
                                    <option value="outofstock" <?= ($status_filter == 'outofstock') ? 'selected' : '' ?>>Stok Habis</option>
                                </select>
                            </div>
                            
                            <div class="col-lg-2 d-flex align-items-end">
                                <div class="d-flex gap-2 w-100">
                                    <button type="submit" class="btn btn-primary flex-grow-1">
                                        <i class="fas fa-search me-2"></i>Filter
                                    </button>
                                    <a href="books.php" class="btn btn-light">
                                        <i class="fas fa-redo"></i>
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Daftar buku dalam tabel -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th width="80">Sampul</th>
                                        <th>Judul</th>
                                        <th>Penulis</th>
                                        <th>Kategori</th>
                                        <th>Harga</th>
                                        <th>Stok</th>
                                        <th>Status</th>
                                        <th width="120">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($books)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-4">
                                                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                                                <p class="mb-0">Tidak ada buku yang ditemukan</p>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($books as $book): ?>
                                            <tr>
                                                <td>
                                                    <img src="<?= htmlspecialchars($book['cover_image']) ?>" alt="Cover" class="cover-preview">
                                                </td>
                                                <td>
                                                    <strong><?= htmlspecialchars($book['title']) ?></strong>
                                                    <?php if (!empty($book['isbn'])): ?>
                                                        <div class="small text-muted">ISBN: <?= htmlspecialchars($book['isbn']) ?></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($book['authors'] ?? 'Tidak ada') ?></td>
                                                <td><?= htmlspecialchars($book['categories'] ?? 'Tidak ada') ?></td>
                                                <td>
                                                    <div>Rp <?= number_format($book['price'], 0, ',', '.') ?></div>
                                                    <?php if (!empty($book['discount_price'])): ?>
                                                        <div class="text-danger small">Diskon: Rp <?= number_format($book['discount_price'], 0, ',', '.') ?></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge <?= $book['stock'] > 0 ? 'bg-success' : 'bg-danger' ?>">
                                                        <?= $book['stock'] ?> unit
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($book['is_featured']): ?>
                                                        <span class="badge bg-primary">Featured</span>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($book['is_bestseller']): ?>
                                                        <span class="badge bg-warning">Bestseller</span>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($book['is_new']): ?>
                                                        <span class="badge bg-info">Baru</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-1">
                                                        <a href="books.php?action=edit&id=<?= $book['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="books.php?action=view&id=<?= $book['id'] ?>" class="btn btn-sm btn-outline-info" title="Lihat Detail">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="books.php?action=delete&id=<?= $book['id'] ?>" class="btn btn-sm btn-outline-danger" title="Hapus" onclick="return confirm('Yakin ingin menghapus buku ini?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if (isset($total_pages) && $total_pages > 1): ?>
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <div class="text-muted small">
                                    Menampilkan <?= count($books) ?> dari <?= $total_records ?> buku
                                </div>
                                <nav>
                                    <ul class="pagination mb-0">
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&category=<?= $category_filter ?>&status=<?= $status_filter ?>">
                                                    <i class="fas fa-chevron-left"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = max(1, $page - 2); $i <= min($page + 2, $total_pages); $i++): ?>
                                            <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                                <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= $category_filter ?>&status=<?= $status_filter ?>">
                                                    <?= $i ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $total_pages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&category=<?= $category_filter ?>&status=<?= $status_filter ?>">
                                                    <i class="fas fa-chevron-right"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
            <?php elseif ($action == 'add' || $action == 'edit'): ?>
                <!-- Form tambah/edit buku -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3 class="mb-0 fw-bold"><?= $action == 'add' ? 'Tambah Buku Baru' : 'Edit Buku' ?></h3>
                        <p class="text-muted mb-0">Masukkan informasi lengkap tentang buku</p>
                    </div>
                    <div>
                        <a href="books.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>
                            Kembali ke Daftar
                        </a>
                    </div>
                </div>
                
                <?php
                // Inisialisasi data buku jika mode edit
                $book_data = [
                    'title' => '',
                    'isbn' => '',
                    'description' => '',
                    'price' => '',
                    'discount_price' => '',
                    'stock' => '0',
                    'publisher_id' => '',
                    'cover_image' => '',
                    'publication_date' => '',
                    'page_count' => '',
                    'weight' => '',
                    'dimensions' => '',
                    'language' => 'Indonesia',
                    'is_featured' => 0,
                    'is_bestseller' => 0,
                    'is_new' => 0
                ];
                
                $book_categories = [];
                $book_authors = [];
                
                // Jika mode edit, ambil data buku yang akan diedit
                if ($action == 'edit' && $book_id > 0) {
                    $query = "SELECT * FROM books WHERE id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("i", $book_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        $book_data = $result->fetch_assoc();
                        
                        // Ambil kategori buku
                        $cat_query = "SELECT category_id FROM book_categories WHERE book_id = ?";
                        $cat_stmt = $conn->prepare($cat_query);
                        $cat_stmt->bind_param("i", $book_id);
                        $cat_stmt->execute();
                        $cat_result = $cat_stmt->get_result();
                        
                        while ($cat_row = $cat_result->fetch_assoc()) {
                            $book_categories[] = $cat_row['category_id'];
                        }
                        
                        // Ambil penulis buku
                        $author_query = "SELECT author_id FROM book_authors WHERE book_id = ?";
                        $author_stmt = $conn->prepare($author_query);
                        $author_stmt->bind_param("i", $book_id);
                        $author_stmt->execute();
                        $author_result = $author_stmt->get_result();
                        
                        while ($author_row = $author_result->fetch_assoc()) {
                            $book_authors[] = $author_row['author_id'];
                        }
                    } else {
                        $error_message = "Buku dengan ID tersebut tidak ditemukan.";
                    }
                }
                
                // Ambil daftar publisher
                $publishers = [];
                if ($tables_status['publishers']) {
                    $pub_result = $conn->query("SELECT id, name FROM publishers ORDER BY name");
                    if ($pub_result && $pub_result->num_rows > 0) {
                        while ($row = $pub_result->fetch_assoc()) {
                            $publishers[] = $row;
                        }
                    }
                }
                
                // Ambil daftar penulis
                $authors = [];
                if ($tables_status['authors']) {
                    $authors_result = $conn->query("SELECT id, name FROM authors ORDER BY name");
                    if ($authors_result && $authors_result->num_rows > 0) {
                        while ($row = $authors_result->fetch_assoc()) {
                            $authors[] = $row;
                        }
                    }
                }
                
                // Proses form jika di-submit
                if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_book'])) {
                    $title = sanitize($_POST['title']);
                    $isbn = sanitize($_POST['isbn']);
                    $description = sanitize($_POST['description']);
                    $price = floatval($_POST['price']);
                    $discount_price = !empty($_POST['discount_price']) ? floatval($_POST['discount_price']) : null;
                    $stock = intval($_POST['stock']);
                    $publisher_id = !empty($_POST['publisher_id']) ? intval($_POST['publisher_id']) : null;
                    $publication_date = sanitize($_POST['publication_date']);
                    $page_count = !empty($_POST['page_count']) ? intval($_POST['page_count']) : null;
                    $weight = !empty($_POST['weight']) ? floatval($_POST['weight']) : null;
                    $dimensions = sanitize($_POST['dimensions']);
                    $language = sanitize($_POST['language']);
                    
                    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
                    $is_bestseller = isset($_POST['is_bestseller']) ? 1 : 0;
                    $is_new = isset($_POST['is_new']) ? 1 : 0;
                    
                    $selected_categories = isset($_POST['categories']) ? $_POST['categories'] : [];
                    $selected_authors = isset($_POST['authors']) ? $_POST['authors'] : [];
                    
                    // Validasi data
                    $errors = [];
                    
                    if (empty($title)) {
                        $errors[] = "Judul buku harus diisi";
                    }
                    
                    if (empty($price) || $price <= 0) {
                        $errors[] = "Harga buku harus diisi dengan nilai yang valid";
                    }
                    
                    if (empty($stock) || $stock < 0) {
                        $errors[] = "Stok buku harus diisi dengan nilai yang valid";
                    }
                    
                    if (empty($selected_authors)) {
                        $errors[] = "Minimal satu penulis harus dipilih";
                    }
                    
                    if (empty($selected_categories)) {
                        $errors[] = "Minimal satu kategori harus dipilih";
                    }
                    
                    // Proses upload gambar sampul jika ada
                    $cover_image = $book_data['cover_image']; // Default menggunakan gambar yang sudah ada
                    
                    if (!empty($_FILES['cover_image']['name'])) {
                        $upload_dir = "../uploads/covers/";
                        
                        // Buat direktori jika belum ada
                        if (!file_exists($upload_dir)) {
                            mkdir($upload_dir, 0755, true);
                        }
                        
                        $file_ext = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
                        $file_name = 'book_' . time() . '.' . $file_ext;
                        $target_file = $upload_dir . $file_name;
                        
                        // Cek apakah file adalah gambar
                        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
                        
                        if (!in_array(strtolower($file_ext), $allowed_types)) {
                            $errors[] = "Format file tidak didukung. Gunakan JPG, JPEG, PNG, atau GIF.";
                        } elseif ($_FILES['cover_image']['size'] > 2000000) { // 2MB limit
                            $errors[] = "Ukuran file terlalu besar. Maksimal 2MB.";
                        } elseif (move_uploaded_file($_FILES['cover_image']['tmp_name'], $target_file)) {
                            $cover_image = '/uploads/covers/' . $file_name;
                        } else {
                            $errors[] = "Gagal mengupload file gambar.";
                        }
                    }
                    
                    // Jika tidak ada error, simpan data
                    if (empty($errors)) {
                        try {
                            $conn->begin_transaction();
                            
                            if ($action == 'add') {
                                // Query untuk insert buku baru
                                $query = "INSERT INTO books (title, isbn, description, price, discount_price, stock, 
                                         publisher_id, cover_image, publication_date, page_count, weight, dimensions, 
                                         language, is_featured, is_bestseller, is_new, created_at, updated_at) 
                                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
                                
                                $stmt = $conn->prepare($query);
                                $stmt->bind_param("sssddisssissiii", 
                                    $title, $isbn, $description, $price, $discount_price, $stock, 
                                    $publisher_id, $cover_image, $publication_date, $page_count, 
                                    $weight, $dimensions, $language, $is_featured, $is_bestseller, $is_new
                                );
                                
                                $stmt->execute();
                                $book_id = $conn->insert_id;
                                
                                $success_message = "Buku baru berhasil ditambahkan!";
                                
                            } else {
                                // Query untuk update buku
                                $query = "UPDATE books SET title = ?, isbn = ?, description = ?, price = ?, 
                                         discount_price = ?, stock = ?, publisher_id = ?, cover_image = ?, 
                                         publication_date = ?, page_count = ?, weight = ?, dimensions = ?, 
                                         language = ?, is_featured = ?, is_bestseller = ?, is_new = ?, 
                                         updated_at = NOW() WHERE id = ?";
                                
                                $stmt = $conn->prepare($query);
                                $stmt->bind_param("sssddissisissiii", 
                                    $title, $isbn, $description, $price, $discount_price, $stock, 
                                    $publisher_id, $cover_image, $publication_date, $page_count, 
                                    $weight, $dimensions, $language, $is_featured, $is_bestseller, $is_new, $book_id
                                );
                                
                                $stmt->execute();
                                
                                $success_message = "Buku berhasil diperbarui!";
                            }
                            
                            // Hapus kategori lama dan tambahkan yang baru (untuk mode edit)
                            if ($action == 'edit') {
                                $conn->query("DELETE FROM book_categories WHERE book_id = $book_id");
                                $conn->query("DELETE FROM book_authors WHERE book_id = $book_id");
                            }
                            
                            // Tambahkan kategori buku
                            foreach ($selected_categories as $category_id) {
                                $cat_query = "INSERT INTO book_categories (book_id, category_id) VALUES (?, ?)";
                                $cat_stmt = $conn->prepare($cat_query);
                                $cat_stmt->bind_param("ii", $book_id, $category_id);
                                $cat_stmt->execute();
                            }
                            
                            // Tambahkan penulis buku
                            foreach ($selected_authors as $author_id) {
                                $author_query = "INSERT INTO book_authors (book_id, author_id) VALUES (?, ?)";
                                $author_stmt = $conn->prepare($author_query);
                                $author_stmt->bind_param("ii", $book_id, $author_id);
                                $author_stmt->execute();
                            }
                            
                            $conn->commit();
                            
                            // Redirect ke halaman daftar buku
                            header("Location: books.php?success=" . urlencode($success_message));
                            exit;
                            
                        } catch (Exception $e) {
                            $conn->rollback();
                            $error_message = "Error: " . $e->getMessage();
                        }
                    } else {
                        $error_message = implode("<br>", $errors);
                    }
                }
                ?>
                
                <div class="card">
                    <div class="card-body">
                        <?php if (!empty($error_message)): ?>
                            <div class="alert alert-danger">
                                <?= $error_message ?>
                            </div>
                        <?php endif; ?>
                        
                        <form action="" method="post" enctype="multipart/form-data" class="row g-3">
                            <!-- Informasi Dasar -->
                            <div class="col-md-8">
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0">Informasi Dasar</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="title" class="form-label">Judul Buku <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="title" name="title" required value="<?= htmlspecialchars($book_data['title']) ?>">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="isbn" class="form-label">ISBN</label>
                                            <input type="text" class="form-control" id="isbn" name="isbn" value="<?= htmlspecialchars($book_data['isbn']) ?>">
                                            <div class="form-text">Format: 978-3-16-148410-0</div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="description" class="form-label">Deskripsi</label>
                                            <textarea class="form-control" id="description" name="description" rows="5"><?= htmlspecialchars($book_data['description']) ?></textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0">Detail Harga & Stok</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="price" class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control" id="price" name="price" required min="0" value="<?= htmlspecialchars($book_data['price']) ?>">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="discount_price" class="form-label">Harga Diskon (Rp)</label>
                                                <input type="number" class="form-control" id="discount_price" name="discount_price" min="0" value="<?= htmlspecialchars($book_data['discount_price']) ?>">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="stock" class="form-label">Stok <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="stock" name="stock" required min="0" value="<?= htmlspecialchars($book_data['stock']) ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0">Kategori & Penulis</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label">Kategori <span class="text-danger">*</span></label>
                                            <div class="border rounded p-3" style="max-height: 150px; overflow-y: auto;">
                                                <?php foreach ($categories as $category): ?>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="categories[]" value="<?= $category['id'] ?>" id="category_<?= $category['id'] ?>" 
                                                            <?= in_array($category['id'], $book_categories) ? 'checked' : '' ?>>
                                                        <label class="form-check-label" for="category_<?= $category['id'] ?>">
                                                            <?= htmlspecialchars($category['name']) ?>
                                                        </label>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Penulis <span class="text-danger">*</span></label>
                                            <div class="border rounded p-3" style="max-height: 150px; overflow-y: auto;">
                                                <?php foreach ($authors as $author): ?>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="authors[]" value="<?= $author['id'] ?>" id="author_<?= $author['id'] ?>" 
                                                            <?= in_array($author['id'], $book_authors) ? 'checked' : '' ?>>
                                                        <label class="form-check-label" for="author_<?= $author['id'] ?>">
                                                            <?= htmlspecialchars($author['name']) ?>
                                                        </label>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Sampul dan Detail Tambahan -->
                            <div class="col-md-4">
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0">Sampul Buku</h5>
                                    </div>
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <?php if (!empty($book_data['cover_image'])): ?>
                                                <img src="<?= htmlspecialchars($book_data['cover_image']) ?>" alt="Cover" class="img-fluid mb-3" style="max-height: 200px;">
                                            <?php else: ?>
                                                <div class="border rounded p-3 mb-3 bg-light">
                                                    <i class="fas fa-book fa-4x text-muted"></i>
                                                    <p class="text-muted mt-2">Belum ada sampul</p>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <input type="file" class="form-control" id="cover_image" name="cover_image" accept="image/*">
                                            <div class="form-text">Format: JPG, JPEG, PNG, GIF. Ukuran max. 2MB</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0">Status Buku</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" <?= $book_data['is_featured'] ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="is_featured">Featured</label>
                                        </div>
                                        <div class="form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" id="is_bestseller" name="is_bestseller" value="1" <?= $book_data['is_bestseller'] ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="is_bestseller">Bestseller</label>
                                        </div>
                                        <div class="form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" id="is_new" name="is_new" value="1" <?= $book_data['is_new'] ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="is_new">Baru</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0">Detail Tambahan</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="publisher_id" class="form-label">Penerbit</label>
                                            <select class="form-select" id="publisher_id" name="publisher_id">
                                                <option value="">-- Pilih Penerbit --</option>
                                                <?php foreach ($publishers as $publisher): ?>
                                                    <option value="<?= $publisher['id'] ?>" <?= ($book_data['publisher_id'] == $publisher['id']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($publisher['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="publication_date" class="form-label">Tanggal Terbit</label>
                                            <input type="date" class="form-control" id="publication_date" name="publication_date" value="<?= htmlspecialchars($book_data['publication_date']) ?>">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="page_count" class="form-label">Jumlah Halaman</label>
                                            <input type="number" class="form-control" id="page_count" name="page_count" min="1" value="<?= htmlspecialchars($book_data['page_count']) ?>">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="weight" class="form-label">Berat (gram)</label>
                                            <input type="number" class="form-control" id="weight" name="weight" min="0" step="0.01" value="<?= htmlspecialchars($book_data['weight']) ?>">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="dimensions" class="form-label">Dimensi (pxlxt cm)</label>
                                            <input type="text" class="form-control" id="dimensions" name="dimensions" placeholder="contoh: 15x21x1.5" value="<?= htmlspecialchars($book_data['dimensions']) ?>">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="language" class="form-label">Bahasa</label>
                                            <input type="text" class="form-control" id="language" name="language" value="<?= htmlspecialchars($book_data['language']) ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-12 text-end">
                                <hr>
                                <a href="books.php" class="btn btn-outline-secondary me-2">Batal</a>
                                <button type="submit" name="save_book" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Simpan Buku
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
            <?php elseif ($action == 'view'): ?>
                <!-- Detail buku -->
                <?php
                // Ambil data buku yang akan ditampilkan
                $book = null;
                
                if ($book_id > 0) {
                    $query = "SELECT b.*, p.name as publisher_name,
                            GROUP_CONCAT(DISTINCT c.name ORDER BY c.name ASC SEPARATOR ', ') as categories,
                            GROUP_CONCAT(DISTINCT a.name ORDER BY a.name ASC SEPARATOR ', ') as authors
                            FROM books b
                            LEFT JOIN publishers p ON b.publisher_id = p.id
                            LEFT JOIN book_categories bc ON b.id = bc.book_id
                            LEFT JOIN categories c ON bc.category_id = c.id
                            LEFT JOIN book_authors ba ON b.id = ba.book_id
                            LEFT JOIN authors a ON ba.author_id = a.id
                            WHERE b.id = ?
                            GROUP BY b.id";
                    
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("i", $book_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        $book = $result->fetch_assoc();
                    } else {
                        $error_message = "Buku dengan ID tersebut tidak ditemukan.";
                    }
                } else {
                    $error_message = "ID buku tidak valid.";
                }
                ?>
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3 class="mb-0 fw-bold">Detail Buku</h3>
                        <p class="text-muted mb-0">Informasi lengkap tentang buku</p>
                    </div>
                    <div>
                        <a href="books.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>
                            Kembali ke Daftar
                        </a>
                    </div>
                </div>
                
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger">
                        <?= $error_message ?>
                    </div>
                <?php elseif ($book): ?>
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <!-- Sampul buku -->
                                <div class="col-md-3 text-center mb-4 mb-md-0">
                                    <?php if (!empty($book['cover_image'])): ?>
                                        <img src="<?= htmlspecialchars($book['cover_image']) ?>" alt="Cover" class="img-fluid rounded shadow" style="max-height: 300px;">
                                    <?php else: ?>
                                        <div class="border rounded p-5 bg-light">
                                            <i class="fas fa-book fa-5x text-muted"></i>
                                            <p class="text-muted mt-3">Sampul tidak tersedia</p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="mt-3">
                                        <a href="books.php?action=edit&id=<?= $book['id'] ?>" class="btn btn-primary">
                                            <i class="fas fa-edit me-2"></i>Edit Buku
                                        </a>
                                    </div>
                                </div>
                                
                                <!-- Informasi buku -->
                                <div class="col-md-9">
                                    <h4 class="mb-3"><?= htmlspecialchars($book['title']) ?></h4>
                                    
                                    <div class="mb-4">
                                        <?php if ($book['is_featured']): ?>
                                            <span class="badge bg-primary me-2">Featured</span>
                                        <?php endif; ?>
                                        
                                        <?php if ($book['is_bestseller']): ?>
                                            <span class="badge bg-warning me-2">Bestseller</span>
                                        <?php endif; ?>
                                        
                                        <?php if ($book['is_new']): ?>
                                            <span class="badge bg-info me-2">Baru</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="row mb-4">
                                        <div class="col-md-4 mb-3">
                                            <h6 class="text-muted">Penulis</h6>
                                            <p><?= htmlspecialchars($book['authors'] ?: 'Tidak ada') ?></p>
                                        </div>
                                        
                                        <div class="col-md-4 mb-3">
                                            <h6 class="text-muted">Kategori</h6>
                                            <p><?= htmlspecialchars($book['categories'] ?: 'Tidak ada') ?></p>
                                        </div>
                                        
                                        <div class="col-md-4 mb-3">
                                            <h6 class="text-muted">Penerbit</h6>
                                            <p><?= htmlspecialchars($book['publisher_name'] ?: 'Tidak ada') ?></p>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-4">
                                        <div class="col-md-4 mb-3">
                                            <h6 class="text-muted">Harga</h6>
                                            <h5 class="text-primary">Rp <?= number_format($book['price'], 0, ',', '.') ?></h5>
                                            <?php if (!empty($book['discount_price'])): ?>
                                                <p class="text-danger">Diskon: Rp <?= number_format($book['discount_price'], 0, ',', '.') ?></p>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="col-md-4 mb-3">
                                            <h6 class="text-muted">ISBN</h6>
                                            <p><?= htmlspecialchars($book['isbn'] ?: 'Tidak ada') ?></p>
                                        </div>
                                        
                                        <div class="col-md-4 mb-3">
                                            <h6 class="text-muted">Stok</h6>
                                            <span class="badge <?= $book['stock'] > 0 ? 'bg-success' : 'bg-danger' ?> fs-6">
                                                <?= $book['stock'] ?> unit
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($book['description'])): ?>
                                        <div class="mb-4">
                                            <h5 class="border-bottom pb-2">Deskripsi</h5>
                                            <p class="text-muted"><?= nl2br(htmlspecialchars($book['description'])) ?></p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h5 class="border-bottom pb-2">Spesifikasi</h5>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <table class="table table-sm">
                                                <tr>
                                                    <td>Tanggal Terbit</td>
                                                    <td><?= !empty($book['publication_date']) ? date('d/m/Y', strtotime($book['publication_date'])) : 'Tidak ada' ?></td>
                                                </tr>
                                                <tr>
                                                    <td>Jumlah Halaman</td>
                                                    <td><?= !empty($book['page_count']) ? $book['page_count'] . ' halaman' : 'Tidak ada' ?></td>
                                                </tr>
                                                <tr>
                                                    <td>Bahasa</td>
                                                    <td><?= htmlspecialchars($book['language'] ?: 'Tidak ada') ?></td>
                                                </tr>
                                            </table>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <table class="table table-sm">
                                                <tr>
                                                    <td>Berat</td>
                                                    <td><?= !empty($book['weight']) ? $book['weight'] . ' gram' : 'Tidak ada' ?></td>
                                                </tr>
                                                <tr>
                                                    <td>Dimensi</td>
                                                    <td><?= htmlspecialchars($book['dimensions'] ?: 'Tidak ada') ?></td>
                                                </tr>
                                                <tr>
                                                    <td>Terakhir Diperbarui</td>
                                                    <td><?= date('d/m/Y H:i', strtotime($book['updated_at'])) ?></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
            <?php endif; ?>
        <?php endif; ?>
        
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