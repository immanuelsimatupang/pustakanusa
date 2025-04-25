<?php
// Inisialisasi session
session_start();

// Cek apakah admin sudah login
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: login.php');
    exit;
}

// Koneksi ke database
require_once '../config/database.php';

// Ambil parameter dari URL
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Inisialisasi variabel pesan
$status_message = '';
$status_type = '';
$errors = [];

// Fungsi untuk validasi input
function validate($data) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($data));
}

// Proses form jika ada
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Reset password
    if (isset($_POST['reset_password'])) {
        $reset_user_id = intval($_POST['user_id']);
        $new_password = password_hash('password123', PASSWORD_DEFAULT);
        
        $reset_query = "UPDATE users SET password = ? WHERE id = ?";
        $reset_stmt = $conn->prepare($reset_query);
        $reset_stmt->bind_param("si", $new_password, $reset_user_id);
        
        if ($reset_stmt->execute()) {
            $status_message = "Password berhasil direset menjadi 'password123'.";
            $status_type = "success";
        } else {
            $status_message = "Gagal mereset password.";
            $status_type = "danger";
        }
    }
    
    // Blokir/Aktifkan pengguna
    if (isset($_POST['toggle_status'])) {
        $toggle_user_id = intval($_POST['user_id']);
        $new_status = ($_POST['current_status'] == 1) ? 0 : 1;
        
        $status_query = "UPDATE users SET is_active = ? WHERE id = ?";
        $status_stmt = $conn->prepare($status_query);
        $status_stmt->bind_param("ii", $new_status, $toggle_user_id);
        
        if ($status_stmt->execute()) {
            $status_message = $new_status == 1 ? "Pengguna berhasil diaktifkan." : "Pengguna berhasil diblokir.";
            $status_type = "success";
        } else {
            $status_message = "Gagal mengubah status pengguna.";
            $status_type = "danger";
        }
    }
    
    // Ubah hak akses admin
    if (isset($_POST['toggle_admin'])) {
        $toggle_admin_id = intval($_POST['user_id']);
        $new_admin_status = ($_POST['current_admin_status'] == 1) ? 0 : 1;
        
        $admin_query = "UPDATE users SET is_admin = ? WHERE id = ?";
        $admin_stmt = $conn->prepare($admin_query);
        $admin_stmt->bind_param("ii", $new_admin_status, $toggle_admin_id);
        
        if ($admin_stmt->execute()) {
            $status_message = $new_admin_status == 1 ? "Pengguna berhasil dijadikan admin." : "Hak akses admin berhasil dicabut.";
            $status_type = "success";
        } else {
            $status_message = "Gagal mengubah hak akses admin.";
            $status_type = "danger";
        }
    }
    
    // Edit profil pengguna
    if (isset($_POST['update_profile'])) {
        $edit_user_id = intval($_POST['user_id']);
        $name = validate($_POST['name']);
        $email = validate($_POST['email']);
        $phone = validate($_POST['phone']);
        $address = validate($_POST['address']);
        
        // Validasi
        if (empty($name)) $errors[] = "Nama tidak boleh kosong";
        if (empty($email)) $errors[] = "Email tidak boleh kosong";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Format email tidak valid";
        
        // Cek apakah email sudah digunakan (kecuali oleh pengguna yang sedang diedit)
        $check_email = "SELECT id FROM users WHERE email = ? AND id != ?";
        $check_stmt = $conn->prepare($check_email);
        $check_stmt->bind_param("si", $email, $edit_user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $errors[] = "Email sudah digunakan oleh pengguna lain";
        }
        
        // Jika tidak ada error, update profil
        if (empty($errors)) {
            $update_query = "UPDATE users SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("ssssi", $name, $email, $phone, $address, $edit_user_id);
            
            if ($update_stmt->execute()) {
                $status_message = "Profil pengguna berhasil diperbarui.";
                $status_type = "success";
                
                // Redirect ke halaman daftar pengguna
                header("Location: users.php?success=" . urlencode($status_message));
                exit;
            } else {
                $status_message = "Gagal memperbarui profil pengguna.";
                $status_type = "danger";
            }
        } else {
            $status_message = implode("<br>", $errors);
            $status_type = "danger";
        }
    }
}

// Ambil pesan sukses dari URL (redirect)
if (isset($_GET['success'])) {
    $status_message = $_GET['success'];
    $status_type = "success";
}

// Fungsi untuk mendapatkan daftar pengguna
function getUsers($search = '', $filter = '') {
    global $conn;
    
    $where_clauses = [];
    $params = [];
    $types = '';
    
    // Filter pencarian
    if (!empty($search)) {
        $where_clauses[] = "(name LIKE ? OR email LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $types .= 'ss';
    }
    
    // Filter berdasarkan tipe pengguna
    if ($filter === 'admin') {
        $where_clauses[] = "is_admin = 1";
    } elseif ($filter === 'customer') {
        $where_clauses[] = "is_admin = 0";
    } elseif ($filter === 'active') {
        $where_clauses[] = "is_active = 1";
    } elseif ($filter === 'blocked') {
        $where_clauses[] = "is_active = 0";
    }
    
    // Buat WHERE clause
    $where_sql = "";
    if (!empty($where_clauses)) {
        $where_sql = "WHERE " . implode(" AND ", $where_clauses);
    }
    
    // Query untuk mendapatkan daftar pengguna
    $query = "SELECT * FROM users $where_sql ORDER BY created_at DESC";
    
    $stmt = $conn->prepare($query);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    
    return $users;
}

// Mendapatkan detail pengguna berdasarkan ID
function getUserById($id) {
    global $conn;
    
    $query = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return null;
    }
    
    return $result->fetch_assoc();
}

// Judul halaman dan halaman aktif untuk sidebar
$page_title = 'Manajemen Pengguna';
$active_page = 'users';

// Tampilkan view berdasarkan action
if ($action === 'view' && $user_id > 0) {
    // Detail pengguna
    $user = getUserById($user_id);
    
    if (!$user) {
        header("Location: users.php");
        exit;
    }
} elseif ($action === 'edit' && $user_id > 0) {
    // Edit pengguna
    $user = getUserById($user_id);
    
    if (!$user) {
        header("Location: users.php");
        exit;
    }
} else {
    // Daftar pengguna
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $filter = isset($_GET['filter']) ? $_GET['filter'] : '';
    
    $users = getUsers($search, $filter);
}

// Include header
include_once 'templates/header.php';
?>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-0 fw-bold">Manajemen Pengguna</h3>
        <p class="text-muted mb-0">Kelola pengguna dan hak akses</p>
    </div>
    <div>
        <a href="../index.php" class="btn btn-light me-2" target="_blank">
            <i class="fas fa-external-link-alt me-2"></i>
            Lihat Website
        </a>
    </div>
</div>

<?php if (!empty($status_message)): ?>
    <div class="alert alert-<?= $status_type ?> alert-dismissible fade show" role="alert">
        <?= $status_message ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if ($action === 'view'): ?>
    <!-- Detail Pengguna -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Detail Pengguna</h5>
            <a href="users.php" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="mb-3">Informasi Pengguna</h6>
                    <table class="table table-borderless">
                        <tr>
                            <td width="180">ID</td>
                            <td><strong><?= $user['id'] ?></strong></td>
                        </tr>
                        <tr>
                            <td>Nama</td>
                            <td><?= htmlspecialchars($user['name']) ?></td>
                        </tr>
                        <tr>
                            <td>Email</td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                        </tr>
                        <tr>
                            <td>Telepon</td>
                            <td><?= htmlspecialchars($user['phone'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <td>Alamat</td>
                            <td><?= htmlspecialchars($user['address'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <td>Tanggal Registrasi</td>
                            <td><?= date('d M Y H:i', strtotime($user['created_at'])) ?></td>
                        </tr>
                        <tr>
                            <td>Status</td>
                            <td>
                                <?php if ($user['is_active'] == 1): ?>
                                    <span class="badge bg-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Diblokir</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Peran</td>
                            <td>
                                <?php if ($user['is_admin'] == 1): ?>
                                    <span class="badge bg-primary">Administrator</span>
                                <?php else: ?>
                                    <span class="badge bg-info">Pelanggan</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div class="col-md-6">
                    <h6 class="mb-3">Aksi</h6>
                    <div class="d-grid gap-2">
                        <a href="users.php?action=edit&id=<?= $user['id'] ?>" class="btn btn-primary">
                            <i class="fas fa-user-edit me-2"></i> Edit Profil
                        </a>
                        
                        <form action="" method="POST" onsubmit="return confirm('Yakin ingin mereset password pengguna ini?');">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <button type="submit" name="reset_password" class="btn btn-warning w-100">
                                <i class="fas fa-key me-2"></i> Reset Password
                            </button>
                        </form>
                        
                        <form action="" method="POST" onsubmit="return confirm('Yakin ingin mengubah status pengguna ini?');">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <input type="hidden" name="current_status" value="<?= $user['is_active'] ?>">
                            <button type="submit" name="toggle_status" class="btn <?= $user['is_active'] == 1 ? 'btn-danger' : 'btn-success' ?> w-100">
                                <?php if ($user['is_active'] == 1): ?>
                                    <i class="fas fa-ban me-2"></i> Blokir Pengguna
                                <?php else: ?>
                                    <i class="fas fa-check-circle me-2"></i> Aktifkan Pengguna
                                <?php endif; ?>
                            </button>
                        </form>
                        
                        <form action="" method="POST" onsubmit="return confirm('Yakin ingin mengubah hak akses pengguna ini?');">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <input type="hidden" name="current_admin_status" value="<?= $user['is_admin'] ?>">
                            <button type="submit" name="toggle_admin" class="btn <?= $user['is_admin'] == 1 ? 'btn-secondary' : 'btn-info' ?> w-100">
                                <?php if ($user['is_admin'] == 1): ?>
                                    <i class="fas fa-user me-2"></i> Cabut Hak Admin
                                <?php else: ?>
                                    <i class="fas fa-user-shield me-2"></i> Jadikan Admin
                                <?php endif; ?>
                            </button>
                        </form>
                    </div>
                    
                    <?php
                    // Tambahkan informasi pesanan jika pengguna adalah pelanggan
                    if ($user['is_admin'] == 0) {
                        $order_query = "SELECT COUNT(*) as total_orders, SUM(total_amount) as total_spent FROM orders WHERE user_id = ?";
                        $order_stmt = $conn->prepare($order_query);
                        $order_stmt->bind_param("i", $user['id']);
                        $order_stmt->execute();
                        $order_result = $order_stmt->get_result();
                        $order_data = $order_result->fetch_assoc();
                        
                        if ($order_data['total_orders'] > 0) {
                            echo '<div class="mt-4">';
                            echo '<h6 class="mb-3">Riwayat Pesanan</h6>';
                            echo '<div class="card bg-light">';
                            echo '<div class="card-body">';
                            echo '<p class="mb-1">Total Pesanan: <strong>' . $order_data['total_orders'] . '</strong></p>';
                            echo '<p class="mb-0">Total Belanja: <strong>Rp ' . number_format($order_data['total_spent'], 0, ',', '.') . '</strong></p>';
                            echo '</div>';
                            echo '</div>';
                            echo '<div class="d-grid mt-2">';
                            echo '<a href="orders.php?search=' . urlencode($user['email']) . '" class="btn btn-outline-primary">';
                            echo '<i class="fas fa-shopping-cart me-2"></i> Lihat Pesanan';
                            echo '</a>';
                            echo '</div>';
                            echo '</div>';
                        } else {
                            echo '<div class="mt-4">';
                            echo '<h6 class="mb-3">Riwayat Pesanan</h6>';
                            echo '<div class="alert alert-light">';
                            echo 'Pengguna ini belum memiliki pesanan.';
                            echo '</div>';
                            echo '</div>';
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
<?php elseif ($action === 'edit'): ?>
    <!-- Edit Pengguna -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Edit Profil Pengguna</h5>
            <a href="users.php?action=view&id=<?= $user['id'] ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
        <div class="card-body">
            <form action="" method="POST">
                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                
                <div class="row mb-3">
                    <label for="name" class="col-sm-2 col-form-label">Nama</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <label for="email" class="col-sm-2 col-form-label">Email</label>
                    <div class="col-sm-10">
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <label for="phone" class="col-sm-2 col-form-label">Telepon</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                    </div>
                </div>
                
                <div class="row mb-3">
                    <label for="address" class="col-sm-2 col-form-label">Alamat</label>
                    <div class="col-sm-10">
                        <textarea class="form-control" id="address" name="address" rows="3"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-sm-10 offset-sm-2">
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Simpan Perubahan
                        </button>
                        <a href="users.php?action=view&id=<?= $user['id'] ?>" class="btn btn-outline-secondary ms-2">Batal</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php else: ?>
    <!-- Daftar Pengguna -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Daftar Pengguna</h5>
        </div>
        <div class="card-body">
            <!-- Form filter -->
            <form action="" method="GET" class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" placeholder="Cari nama atau email..." value="<?= htmlspecialchars($search) ?>">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="filter" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Pengguna</option>
                        <option value="admin" <?= $filter === 'admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="customer" <?= $filter === 'customer' ? 'selected' : '' ?>>Pelanggan</option>
                        <option value="active" <?= $filter === 'active' ? 'selected' : '' ?>>Aktif</option>
                        <option value="blocked" <?= $filter === 'blocked' ? 'selected' : '' ?>>Diblokir</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <a href="users.php" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
            </form>
            
            <!-- Tabel pengguna -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Peran</th>
                            <th>Status</th>
                            <th>Tanggal Registrasi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">Tidak ada pengguna yang ditemukan</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= $user['id'] ?></td>
                                    <td><?= htmlspecialchars($user['name']) ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td>
                                        <?php if ($user['is_admin'] == 1): ?>
                                            <span class="badge bg-primary">Admin</span>
                                        <?php else: ?>
                                            <span class="badge bg-info">Pelanggan</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($user['is_active'] == 1): ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Diblokir</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="users.php?action=view&id=<?= $user['id'] ?>" class="btn btn-primary" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="users.php?action=edit&id=<?= $user['id'] ?>" class="btn btn-warning" title="Edit Pengguna">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn <?= $user['is_active'] == 1 ? 'btn-danger' : 'btn-success' ?>" 
                                                    title="<?= $user['is_active'] == 1 ? 'Blokir Pengguna' : 'Aktifkan Pengguna' ?>"
                                                    onclick="toggleStatus(<?= $user['id'] ?>, <?= $user['is_active'] ?>)">
                                                <i class="fas <?= $user['is_active'] == 1 ? 'fa-ban' : 'fa-check-circle' ?>"></i>
                                            </button>
                                        </div>
                                        
                                        <!-- Form untuk toggle status (akan disubmit via JavaScript) -->
                                        <form id="toggle-form-<?= $user['id'] ?>" action="" method="POST" style="display: none;">
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <input type="hidden" name="current_status" value="<?= $user['is_active'] ?>">
                                            <input type="hidden" name="toggle_status" value="1">
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- JavaScript untuk toggle status -->
<script>
function toggleStatus(userId, currentStatus) {
    const statusText = currentStatus == 1 ? "blokir" : "aktifkan";
    if (confirm(`Apakah Anda yakin ingin ${statusText} pengguna ini?`)) {
        document.getElementById(`toggle-form-${userId}`).submit();
    }
}
</script>

<?php include_once 'templates/footer.php'; ?> 