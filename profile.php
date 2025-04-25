<?php
// Inisialisasi session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect ke halaman login jika belum login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Tambahkan koneksi database
require_once 'config/database.php';

// Fungsi format Rupiah
function formatRupiah($price)
{
    return 'Rp ' . number_format($price, 0, ',', '.');
}

// Ambil data user
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Ambil tab yang aktif
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'profile';

// Update profil jika ada request POST
$success_message = "";
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $postal_code = trim($_POST['postal_code']);
    
    // Validasi input
    if (empty($name) || empty($email) || empty($phone)) {
        $error_message = "Nama, email dan nomor telepon harus diisi!";
    } else {
        // Update data user
        $update_query = "UPDATE users SET name = ?, email = ?, phone = ?, address = ?, city = ?, postal_code = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ssssssi", $name, $email, $phone, $address, $city, $postal_code, $user_id);
        
        if ($update_stmt->execute()) {
            $success_message = "Profil berhasil diperbarui!";
            // Refresh data user
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
        } else {
            $error_message = "Terjadi kesalahan saat memperbarui profil!";
        }
    }
}

// Update password jika ada request POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validasi input
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_message = "Semua field password harus diisi!";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "Password baru dan konfirmasi password tidak cocok!";
    } else {
        // Verifikasi password saat ini
        $check_query = "SELECT password FROM users WHERE id = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("i", $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $user_data = $check_result->fetch_assoc();
        
        if (password_verify($current_password, $user_data['password'])) {
            // Hash password baru
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update password
            $update_query = "UPDATE users SET password = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($update_stmt->execute()) {
                $success_message = "Password berhasil diperbarui!";
            } else {
                $error_message = "Terjadi kesalahan saat memperbarui password!";
            }
        } else {
            $error_message = "Password saat ini tidak valid!";
        }
    }
}

// Ambil data pesanan jika tab orders aktif
if ($active_tab === 'orders') {
    $orders_query = "SELECT o.*, COUNT(oi.id) as total_items 
                    FROM orders o 
                    JOIN order_items oi ON o.id = oi.order_id 
                    WHERE o.user_id = ? 
                    GROUP BY o.id 
                    ORDER BY o.created_at DESC";
    $orders_stmt = $conn->prepare($orders_query);
    $orders_stmt->bind_param("i", $user_id);
    $orders_stmt->execute();
    $orders_result = $orders_stmt->get_result();
    $orders = [];
    
    while ($row = $orders_result->fetch_assoc()) {
        $orders[] = $row;
    }
}

// Ambil detail pesanan jika ada request detail
$order_detail = null;
$order_items = [];

if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];
    
    // Ambil detail pesanan
    $detail_query = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
    $detail_stmt = $conn->prepare($detail_query);
    $detail_stmt->bind_param("ii", $order_id, $user_id);
    $detail_stmt->execute();
    $detail_result = $detail_stmt->get_result();
    
    if ($detail_result->num_rows > 0) {
        $order_detail = $detail_result->fetch_assoc();
        
        // Ambil item pesanan
        $items_query = "SELECT oi.*, b.title, b.author, b.cover_image 
                        FROM order_items oi 
                        JOIN books b ON oi.book_id = b.id 
                        WHERE oi.order_id = ?";
        $items_stmt = $conn->prepare($items_query);
        $items_stmt->bind_param("i", $order_id);
        $items_stmt->execute();
        $items_result = $items_stmt->get_result();
        
        while ($item = $items_result->fetch_assoc()) {
            $order_items[] = $item;
        }
    } else {
        // Pesanan tidak ditemukan atau bukan milik user
        header('Location: profile.php?tab=orders');
        exit;
    }
}

// Include header
include_once 'templates/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="profile-avatar mb-3">
                            <i class="fas fa-user-circle" style="font-size: 5rem; color: #6c757d;"></i>
                        </div>
                        <h5 class="mb-1"><?= htmlspecialchars($user['name']) ?></h5>
                        <p class="text-muted"><?= htmlspecialchars($user['email']) ?></p>
                    </div>
                    
                    <div class="list-group list-group-flush">
                        <a href="profile.php?tab=profile" class="list-group-item list-group-item-action <?= $active_tab === 'profile' ? 'active' : '' ?>">
                            <i class="fas fa-user me-2"></i> Profil Saya
                        </a>
                        <a href="profile.php?tab=orders" class="list-group-item list-group-item-action <?= $active_tab === 'orders' ? 'active' : '' ?>">
                            <i class="fas fa-shopping-bag me-2"></i> Pesanan Saya
                        </a>
                        <a href="profile.php?tab=password" class="list-group-item list-group-item-action <?= $active_tab === 'password' ? 'active' : '' ?>">
                            <i class="fas fa-lock me-2"></i> Ubah Password
                        </a>
                        <a href="logout.php" class="list-group-item list-group-item-action text-danger">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    <?= $success_message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <?= $error_message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($active_tab === 'profile'): ?>
                <!-- Profil -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">Informasi Profil</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Nama Lengkap</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Nomor Telepon</label>
                                    <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Alamat</label>
                                <textarea class="form-control" id="address" name="address" rows="3"><?= htmlspecialchars($user['address']) ?></textarea>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="city" class="form-label">Kota</label>
                                    <input type="text" class="form-control" id="city" name="city" value="<?= htmlspecialchars($user['city']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="postal_code" class="form-label">Kode Pos</label>
                                    <input type="text" class="form-control" id="postal_code" name="postal_code" value="<?= htmlspecialchars($user['postal_code']) ?>">
                                </div>
                            </div>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="submit" name="update_profile" class="btn btn-primary px-4">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php elseif ($active_tab === 'password'): ?>
                <!-- Ubah Password -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">Ubah Password</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Password Saat Ini</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>
                            <div class="mb-3">
                                <label for="new_password" class="form-label">Password Baru</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="submit" name="update_password" class="btn btn-primary px-4">Simpan Password Baru</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php elseif ($active_tab === 'orders'): ?>
                <!-- Pesanan -->
                <?php if (isset($order_detail)): ?>
                    <!-- Detail Pesanan -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Detail Pesanan</h5>
                            <a href="profile.php?tab=orders" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Kembali
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6 class="mb-2">Informasi Pesanan</h6>
                                    <p class="mb-1"><strong>Nomor Pesanan:</strong> <?= htmlspecialchars($order_detail['order_number']) ?></p>
                                    <p class="mb-1"><strong>Tanggal Pesanan:</strong> <?= date('d M Y H:i', strtotime($order_detail['created_at'])) ?></p>
                                    <p class="mb-1"><strong>Status:</strong> 
                                        <span class="badge bg-<?= $order_detail['status'] === 'pending' ? 'warning' : ($order_detail['status'] === 'processing' ? 'info' : ($order_detail['status'] === 'shipped' ? 'primary' : ($order_detail['status'] === 'completed' ? 'success' : 'secondary'))) ?>">
                                            <?= ucfirst($order_detail['status']) ?>
                                        </span>
                                    </p>
                                    <p class="mb-1"><strong>Metode Pembayaran:</strong> <?= htmlspecialchars($order_detail['payment_method']) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="mb-2">Alamat Pengiriman</h6>
                                    <p class="mb-1"><strong>Nama:</strong> <?= htmlspecialchars($order_detail['name']) ?></p>
                                    <p class="mb-1"><strong>Alamat:</strong> <?= htmlspecialchars($order_detail['address']) ?></p>
                                    <p class="mb-1"><strong>Kota:</strong> <?= htmlspecialchars($order_detail['city']) ?></p>
                                    <p class="mb-1"><strong>Kode Pos:</strong> <?= htmlspecialchars($order_detail['postal_code']) ?></p>
                                    <p class="mb-1"><strong>Telepon:</strong> <?= htmlspecialchars($order_detail['phone']) ?></p>
                                </div>
                            </div>
                            
                            <h6 class="mb-3">Item Pesanan</h6>
                            <div class="table-responsive">
                                <table class="table table-borderless">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col" width="60">Gambar</th>
                                            <th scope="col">Buku</th>
                                            <th scope="col" class="text-center">Harga</th>
                                            <th scope="col" class="text-center">Jumlah</th>
                                            <th scope="col" class="text-end">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($order_items as $item): ?>
                                            <tr>
                                                <td>
                                                    <img src="<?= htmlspecialchars($item['cover_image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>" class="img-thumbnail" width="50">
                                                </td>
                                                <td>
                                                    <h6 class="mb-0"><?= htmlspecialchars($item['title']) ?></h6>
                                                    <small class="text-muted"><?= htmlspecialchars($item['author']) ?></small>
                                                </td>
                                                <td class="text-center"><?= formatRupiah($item['price']) ?></td>
                                                <td class="text-center"><?= $item['quantity'] ?></td>
                                                <td class="text-end"><?= formatRupiah($item['price'] * $item['quantity']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <td colspan="4" class="text-end"><strong>Subtotal:</strong></td>
                                            <td class="text-end"><?= formatRupiah($order_detail['subtotal']) ?></td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="text-end"><strong>Ongkos Kirim:</strong></td>
                                            <td class="text-end"><?= formatRupiah($order_detail['shipping_cost']) ?></td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                            <td class="text-end"><?= formatRupiah($order_detail['total_amount']) ?></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Daftar Pesanan -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">Pesanan Saya</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($orders)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-shopping-bag text-muted mb-3" style="font-size: 4rem;"></i>
                                    <h5>Anda belum memiliki pesanan</h5>
                                    <p class="text-muted">Jelajahi katalog buku kami dan temukan bacaan yang Anda suka.</p>
                                    <a href="index.php" class="btn btn-primary mt-2">Mulai Belanja</a>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Nomor Pesanan</th>
                                                <th>Tanggal</th>
                                                <th>Total</th>
                                                <th>Item</th>
                                                <th>Status</th>
                                                <th class="text-end">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($orders as $order): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($order['order_number']) ?></td>
                                                    <td><?= date('d/m/Y', strtotime($order['created_at'])) ?></td>
                                                    <td><?= formatRupiah($order['total_amount']) ?></td>
                                                    <td><?= $order['total_items'] ?> item</td>
                                                    <td>
                                                        <span class="badge bg-<?= $order['status'] === 'pending' ? 'warning' : ($order['status'] === 'processing' ? 'info' : ($order['status'] === 'shipped' ? 'primary' : ($order['status'] === 'completed' ? 'success' : 'secondary'))) ?>">
                                                            <?= ucfirst($order['status']) ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-end">
                                                        <a href="profile.php?tab=orders&order_id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                            Detail
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once 'templates/footer.php'; ?> 