<?php
// File manajemen kategori
session_start();

// Cek apakah pengguna sudah login sebagai admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit;
}

// Include konfigurasi
require_once '../config/database.php';

// Fungsi untuk sanitasi input
function sanitize($input) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($input));
}

// Fungsi untuk mendapatkan kategori dalam format hierarki
function getCategoriesHierarchy($parent_id = 0, $indent = '') {
    global $conn;
    $result = [];
    
    $sql = "SELECT * FROM categories WHERE parent_id = ? ORDER BY display_order ASC, name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $parent_id);
    $stmt->execute();
    $query_result = $stmt->get_result();
    
    while ($row = $query_result->fetch_assoc()) {
        $row['indent'] = $indent;
        $result[] = $row;
        
        // Ambil sub-kategori secara rekursif
        $children = getCategoriesHierarchy($row['id'], $indent . '-- ');
        $result = array_merge($result, $children);
    }
    
    return $result;
}

// Fungsi untuk mendapatkan daftar kategori induk untuk dropdown
function getParentCategories($exclude_id = 0) {
    global $conn;
    $result = [];
    
    $sql = "SELECT id, name FROM categories WHERE parent_id = 0";
    if ($exclude_id > 0) {
        $sql .= " AND id != ?";
    }
    $sql .= " ORDER BY name ASC";
    
    $stmt = $conn->prepare($sql);
    if ($exclude_id > 0) {
        $stmt->bind_param("i", $exclude_id);
    }
    $stmt->execute();
    $query_result = $stmt->get_result();
    
    while ($row = $query_result->fetch_assoc()) {
        $result[] = $row;
    }
    
    return $result;
}

// Inisialisasi variabel
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$category_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$success_message = isset($_GET['success']) ? $_GET['success'] : '';
$error_message = '';

// Title halaman
$page_title = "Manajemen Kategori";

// Menentukan halaman aktif untuk sidebar
$active_page = 'categories';

// Cek apakah tabel kategori sudah ada
$table_exists = $conn->query("SHOW TABLES LIKE 'categories'")->num_rows > 0;

// Jika tabel belum ada, tampilkan pesan
if (!$table_exists) {
    $error_message = "Tabel kategori belum dibuat. Silakan jalankan setup database terlebih dahulu.";
} else {
    // Proses berdasarkan action
    if ($action == 'add' || $action == 'edit') {
        // Inisialisasi data kategori
        $category_data = [
            'name' => '',
            'description' => '',
            'slug' => '',
            'parent_id' => 0,
            'display_order' => 0,
            'is_active' => 1
        ];
        
        // Jika mode edit, ambil data kategori yang akan diedit
        if ($action == 'edit' && $category_id > 0) {
            $query = "SELECT * FROM categories WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $category_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $category_data = $result->fetch_assoc();
            } else {
                $error_message = "Kategori dengan ID tersebut tidak ditemukan.";
            }
        }
        
        // Proses form jika di-submit
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_category'])) {
            $name = sanitize($_POST['name']);
            $description = sanitize($_POST['description']);
            $slug = sanitize($_POST['slug']);
            $parent_id = intval($_POST['parent_id']);
            $display_order = intval($_POST['display_order']);
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            // Validasi data
            $errors = [];
            
            if (empty($name)) {
                $errors[] = "Nama kategori harus diisi";
            }
            
            // Generate slug jika kosong
            if (empty($slug)) {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
            }
            
            // Cek apakah slug sudah ada
            $check_slug_sql = "SELECT id FROM categories WHERE slug = ? AND id != ?";
            $check_stmt = $conn->prepare($check_slug_sql);
            $check_stmt->bind_param("si", $slug, $category_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $errors[] = "Slug '$slug' sudah digunakan. Gunakan slug yang lain.";
            }
            
            // Jika tidak ada error, simpan data
            if (empty($errors)) {
                try {
                    if ($action == 'add') {
                        // Query untuk insert kategori baru
                        $query = "INSERT INTO categories (name, description, slug, parent_id, display_order, is_active, created_at, updated_at) 
                                  VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
                        
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("sssiii", $name, $description, $slug, $parent_id, $display_order, $is_active);
                        
                        $stmt->execute();
                        $success_message = "Kategori baru berhasil ditambahkan!";
                        
                    } else {
                        // Query untuk update kategori
                        $query = "UPDATE categories SET name = ?, description = ?, slug = ?, parent_id = ?, 
                                 display_order = ?, is_active = ?, updated_at = NOW() WHERE id = ?";
                        
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("sssiiii", $name, $description, $slug, $parent_id, $display_order, $is_active, $category_id);
                        
                        $stmt->execute();
                        $success_message = "Kategori berhasil diperbarui!";
                    }
                    
                    // Redirect ke halaman daftar kategori
                    header("Location: categories.php?success=" . urlencode($success_message));
                    exit;
                    
                } catch (Exception $e) {
                    $error_message = "Error: " . $e->getMessage();
                }
            } else {
                $error_message = implode("<br>", $errors);
            }
        }
    } elseif ($action == 'delete') {
        // Proses hapus kategori
        if ($category_id > 0) {
            // Cek apakah kategori memiliki sub-kategori
            $check_children = "SELECT COUNT(*) as count FROM categories WHERE parent_id = ?";
            $check_stmt = $conn->prepare($check_children);
            $check_stmt->bind_param("i", $category_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            $children_count = $result->fetch_assoc()['count'];
            
            if ($children_count > 0) {
                $error_message = "Kategori ini memiliki sub-kategori. Hapus atau pindahkan sub-kategori terlebih dahulu.";
            } else {
                try {
                    // Cek apakah kategori digunakan dalam tabel book_categories
                    $check_usage = "SELECT COUNT(*) as count FROM book_categories WHERE category_id = ?";
                    $usage_stmt = $conn->prepare($check_usage);
                    $usage_stmt->bind_param("i", $category_id);
                    $usage_stmt->execute();
                    $usage_result = $usage_stmt->get_result();
                    $usage_count = $usage_result->fetch_assoc()['count'];
                    
                    if ($usage_count > 0) {
                        $error_message = "Kategori ini sedang digunakan oleh $usage_count buku. Hapus dari buku terlebih dahulu.";
                    } else {
                        // Hapus kategori
                        $delete_sql = "DELETE FROM categories WHERE id = ?";
                        $delete_stmt = $conn->prepare($delete_sql);
                        $delete_stmt->bind_param("i", $category_id);
                        $delete_stmt->execute();
                        
                        $success_message = "Kategori berhasil dihapus!";
                        header("Location: categories.php?success=" . urlencode($success_message));
                        exit;
                    }
                } catch (Exception $e) {
                    $error_message = "Error: " . $e->getMessage();
                }
            }
        } else {
            $error_message = "ID kategori tidak valid.";
        }
    } elseif ($action == 'reorder') {
        // Proses pengaturan ulang urutan kategori
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['category_order'])) {
            try {
                $orders = $_POST['category_order'];
                
                foreach ($orders as $id => $order) {
                    $id = intval($id);
                    $order = intval($order);
                    
                    $update_sql = "UPDATE categories SET display_order = ? WHERE id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("ii", $order, $id);
                    $update_stmt->execute();
                }
                
                $success_message = "Urutan kategori berhasil diperbarui!";
                header("Location: categories.php?success=" . urlencode($success_message));
                exit;
            } catch (Exception $e) {
                $error_message = "Error: " . $e->getMessage();
            }
        }
    }
}

// Ambil daftar kategori jika tabel sudah ada
$categories = [];
if ($table_exists) {
    $categories = getCategoriesHierarchy();
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
        
        .table th {
            font-weight: 600;
            color: #495057;
        }
        
        .category-item {
            transition: background-color 0.2s;
        }
        
        .category-item:hover {
            background-color: rgba(67, 97, 238, 0.05);
        }
        
        .category-actions {
            opacity: 0.2;
            transition: opacity 0.2s;
        }
        
        .category-item:hover .category-actions {
            opacity: 1;
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
        <?php if (!$table_exists): ?>
            <div class="alert alert-warning bg-white border-0 shadow-sm mb-4">
                <div class="d-flex">
                    <div class="me-3">
                        <i class="fas fa-exclamation-triangle fs-3 text-warning"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold">Tabel Database Belum Ada</h5>
                        <p class="mb-0">Tabel kategori belum dibuat. Silakan jalankan <a href="setup_database.php" class="alert-link">Setup Database</a> terlebih dahulu.</p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Konten utama sesuai action -->
            <?php if ($action == 'list'): ?>
                <!-- Header halaman -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3 class="mb-0 fw-bold">Manajemen Kategori</h3>
                        <p class="text-muted mb-0">Kelola kategori dan sub-kategori untuk buku</p>
                    </div>
                    <div class="d-flex">
                        <a href="categories.php?action=reorder" class="btn btn-outline-primary me-2">
                            <i class="fas fa-sort me-2"></i>
                            Atur Urutan
                        </a>
                        <a href="categories.php?action=add" class="btn btn-primary">
                            <i class="fas fa-plus-circle me-2"></i>
                            Tambah Kategori Baru
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
                
                <!-- Daftar kategori -->
                <div class="card">
                    <div class="card-body">
                        <?php if (empty($categories)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                                <p class="mb-0">Belum ada kategori. Klik "Tambah Kategori Baru" untuk membuat kategori pertama.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                        <tr>
                                            <th>Nama Kategori</th>
                                            <th>Slug</th>
                                            <th>Urutan</th>
                                            <th>Status</th>
                                            <th>Jumlah Buku</th>
                                            <th width="100">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($categories as $category): ?>
                                            <tr class="category-item">
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <span><?= $category['indent'] ?></span>
                                                        <strong><?= htmlspecialchars($category['name']) ?></strong>
                                                    </div>
                                                    <?php if (!empty($category['description'])): ?>
                                                        <small class="text-muted"><?= htmlspecialchars($category['description']) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($category['slug']) ?></td>
                                                <td><?= $category['display_order'] ?></td>
                                                <td>
                                                    <?php if ($category['is_active']): ?>
                                                        <span class="badge bg-success">Aktif</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Non-aktif</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    // Hitung jumlah buku dalam kategori
                                                    $count_sql = "SELECT COUNT(*) as total FROM book_categories WHERE category_id = ?";
                                                    $count_stmt = $conn->prepare($count_sql);
                                                    $count_stmt->bind_param("i", $category['id']);
                                                    $count_stmt->execute();
                                                    $count_result = $count_stmt->get_result();
                                                    $book_count = $count_result->fetch_assoc()['total'];
                                                    ?>
                                                    <span class="badge bg-info"><?= $book_count ?> buku</span>
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-1 category-actions">
                                                        <a href="categories.php?action=edit&id=<?= $category['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="categories.php?action=delete&id=<?= $category['id'] ?>" class="btn btn-sm btn-outline-danger" title="Hapus" onclick="return confirm('Yakin ingin menghapus kategori ini?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
            <?php elseif ($action == 'add' || $action == 'edit'): ?>
                <!-- Form tambah/edit kategori -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3 class="mb-0 fw-bold"><?= $action == 'add' ? 'Tambah Kategori Baru' : 'Edit Kategori' ?></h3>
                        <p class="text-muted mb-0">Masukkan informasi kategori</p>
                    </div>
                    <div>
                        <a href="categories.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>
                            Kembali ke Daftar
                        </a>
                    </div>
                </div>
                
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger bg-white border-0 shadow-sm mb-4">
                        <?= $error_message ?>
                    </div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-body">
                        <form action="" method="post" class="row g-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" required value="<?= htmlspecialchars($category_data['name']) ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="slug" class="form-label">Slug</label>
                                    <input type="text" class="form-control" id="slug" name="slug" value="<?= htmlspecialchars($category_data['slug']) ?>">
                                    <div class="form-text">Digunakan untuk URL. Jika kosong, akan dibuat otomatis dari nama.</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="description" class="form-label">Deskripsi</label>
                                    <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($category_data['description']) ?></textarea>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="parent_id" class="form-label">Kategori Induk</label>
                                    <select class="form-select" id="parent_id" name="parent_id">
                                        <option value="0">-- Tidak Ada Induk (Kategori Utama) --</option>
                                        <?php 
                                        $parent_categories = getParentCategories($action == 'edit' ? $category_id : 0);
                                        foreach ($parent_categories as $parent): 
                                        ?>
                                            <option value="<?= $parent['id'] ?>" <?= ($category_data['parent_id'] == $parent['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($parent['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text">Untuk membuat sub-kategori, pilih kategori induknya.</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="display_order" class="form-label">Urutan Tampilan</label>
                                    <input type="number" class="form-control" id="display_order" name="display_order" min="0" value="<?= intval($category_data['display_order']) ?>">
                                    <div class="form-text">Menentukan urutan kategori. Angka lebih kecil ditampilkan lebih dahulu.</div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?= $category_data['is_active'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="is_active">Kategori Aktif</label>
                                    </div>
                                    <div class="form-text">Kategori tidak aktif tidak akan ditampilkan di halaman publik.</div>
                                </div>
                            </div>
                            
                            <div class="col-12 text-end">
                                <hr>
                                <a href="categories.php" class="btn btn-outline-secondary me-2">Batal</a>
                                <button type="submit" name="save_category" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Simpan Kategori
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
            <?php elseif ($action == 'reorder'): ?>
                <!-- Pengaturan urutan kategori -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3 class="mb-0 fw-bold">Atur Urutan Kategori</h3>
                        <p class="text-muted mb-0">Atur urutan tampilan kategori dengan mengubah angka urutan</p>
                    </div>
                    <div>
                        <a href="categories.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>
                            Kembali ke Daftar
                        </a>
                    </div>
                </div>
                
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger bg-white border-0 shadow-sm mb-4">
                        <?= $error_message ?>
                    </div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-body">
                        <?php if (empty($categories)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                                <p class="mb-0">Belum ada kategori. Klik "Tambah Kategori Baru" untuk membuat kategori pertama.</p>
                            </div>
                        <?php else: ?>
                            <form action="categories.php?action=reorder" method="post">
                                <div class="table-responsive">
                                    <table class="table align-middle">
                                        <thead>
                                            <tr>
                                                <th>Nama Kategori</th>
                                                <th width="200">Urutan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($categories as $category): ?>
                                                <tr class="category-item">
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <span><?= $category['indent'] ?></span>
                                                            <strong><?= htmlspecialchars($category['name']) ?></strong>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control" name="category_order[<?= $category['id'] ?>]" value="<?= $category['display_order'] ?>" min="0">
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="text-end mt-3">
                                    <a href="categories.php" class="btn btn-outline-secondary me-2">Batal</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Simpan Urutan
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
                
            <?php endif; ?>
        <?php endif; ?>
        
        <footer class="mt-5 text-muted">
            <p class="small mb-0">&copy; 2023 PustakaNusa Admin Panel. All rights reserved.</p>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-generate slug from name
        document.addEventListener('DOMContentLoaded', function() {
            const nameInput = document.getElementById('name');
            const slugInput = document.getElementById('slug');
            
            if (nameInput && slugInput) {
                nameInput.addEventListener('input', function() {
                    // Generate slug only if the slug field is empty or hasn't been manually changed
                    if (slugInput.dataset.manuallyChanged !== 'true') {
                        const slug = nameInput.value
                            .toLowerCase()
                            .replace(/[^\w\s-]/g, '')  // Remove special chars except spaces, hyphens and underscores
                            .replace(/\s+/g, '-')      // Replace spaces with hyphens
                            .replace(/--+/g, '-')      // Replace multiple hyphens with a single hyphen
                            .trim();
                        
                        slugInput.value = slug;
                    }
                });
                
                // Set a flag when user manually edits the slug
                slugInput.addEventListener('input', function() {
                    slugInput.dataset.manuallyChanged = 'true';
                });
            }
        });
        
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