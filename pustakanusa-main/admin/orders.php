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

// Fungsi format Rupiah
function formatRupiah($price)
{
    return 'Rp ' . number_format($price, 0, ',', '.');
}

// Ambil parameter dari URL
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$order_id = isset($_GET['id']) ? $_GET['id'] : 0;

// Proses update status pesanan dan nomor resi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $update_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    $tracking_number = isset($_POST['tracking_number']) ? $_POST['tracking_number'] : '';
    
    $update_query = "UPDATE orders SET status = ?, tracking_number = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("ssi", $new_status, $tracking_number, $update_id);
    
    if ($update_stmt->execute()) {
        $status_message = "Status pesanan berhasil diperbarui.";
        $status_type = "success";
    } else {
        $status_message = "Gagal memperbarui status pesanan!";
        $status_type = "danger";
    }
}

// Proses pencarian pesanan
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$date_start = isset($_GET['date_start']) ? $_GET['date_start'] : '';
$date_end = isset($_GET['date_end']) ? $_GET['date_end'] : '';
$report_type = isset($_GET['report_type']) ? $_GET['report_type'] : '';

// Tampilkan laporan penjualan
if ($action === 'report') {
    $report_title = 'Laporan Penjualan';
    $report_period = '';
    $group_by = '';
    $date_format = '';
    
    // Set rentang waktu laporan berdasarkan jenis laporan
    if ($report_type === 'daily') {
        $report_period = date('d F Y', strtotime($date_start));
        $report_title .= ' Harian - ' . $report_period;
        $group_by = "DATE(created_at)";
        $date_format = "%d %b %Y";
    } elseif ($report_type === 'monthly') {
        if (!empty($date_start)) {
            $start_month = date('F Y', strtotime($date_start));
            $report_period = $start_month;
            if (!empty($date_end) && $date_start != $date_end) {
                $end_month = date('F Y', strtotime($date_end));
                $report_period .= ' - ' . $end_month;
            }
            $report_title .= ' Bulanan - ' . $report_period;
        }
        $group_by = "MONTH(created_at), YEAR(created_at)";
        $date_format = "%b %Y";
    } elseif ($report_type === 'yearly') {
        if (!empty($date_start)) {
            $start_year = date('Y', strtotime($date_start));
            $report_period = $start_year;
            if (!empty($date_end) && $date_start != $date_end) {
                $end_year = date('Y', strtotime($date_end));
                $report_period .= ' - ' . $end_year;
            }
            $report_title .= ' Tahunan - ' . $report_period;
        }
        $group_by = "YEAR(created_at)";
        $date_format = "%Y";
    }
    
    // Bangun query untuk laporan
    $report_query = "SELECT 
                    DATE_FORMAT(created_at, '$date_format') as period,
                    COUNT(*) as total_orders,
                    SUM(total_amount) as total_sales,
                    SUM(CASE WHEN status = 'completed' THEN total_amount ELSE 0 END) as completed_sales
                FROM orders
                WHERE 1=1";
    
    $params = [];
    $types = '';
    
    // Tambahkan filter tanggal jika ada
    if (!empty($date_start)) {
        $report_query .= " AND DATE(created_at) >= ?";
        $params[] = $date_start;
        $types .= 's';
    }
    
    if (!empty($date_end)) {
        $report_query .= " AND DATE(created_at) <= ?";
        $params[] = $date_end;
        $types .= 's';
    }
    
    // Tambahkan filter status jika ada
    if (!empty($status_filter)) {
        $report_query .= " AND status = ?";
        $params[] = $status_filter;
        $types .= 's';
    }
    
    $report_query .= " GROUP BY $group_by ORDER BY created_at ASC";
    
    $report_stmt = $conn->prepare($report_query);
    
    if (!empty($params)) {
        $report_stmt->bind_param($types, ...$params);
    }
    
    $report_stmt->execute();
    $report_result = $report_stmt->get_result();
    
    $report_data = [];
    while ($row = $report_result->fetch_assoc()) {
        $report_data[] = $row;
    }
    
    // Menghitung total keseluruhan
    $total_orders = 0;
    $total_sales = 0;
    $total_completed = 0;
    
    foreach ($report_data as $item) {
        $total_orders += $item['total_orders'];
        $total_sales += $item['total_sales'];
        $total_completed += $item['completed_sales'];
    }
} 
// Tampilkan detail pesanan
elseif ($action === 'view' && $order_id > 0) {
    // Ambil detail pesanan
    $order_query = "SELECT * FROM orders WHERE id = ?";
    $order_stmt = $conn->prepare($order_query);
    $order_stmt->bind_param("i", $order_id);
    $order_stmt->execute();
    $order_result = $order_stmt->get_result();
    
    if ($order_result->num_rows === 0) {
        // Pesanan tidak ditemukan
        header('Location: orders.php');
        exit;
    }
    
    $order = $order_result->fetch_assoc();
    
    // Ambil item pesanan
    $items_query = "SELECT oi.*, b.title, b.author, b.cover_image 
                    FROM order_items oi 
                    LEFT JOIN books b ON oi.book_id = b.id 
                    WHERE oi.order_id = ?";
    $items_stmt = $conn->prepare($items_query);
    $items_stmt->bind_param("i", $order_id);
    $items_stmt->execute();
    $items_result = $items_stmt->get_result();
    
    $order_items = [];
    while ($item = $items_result->fetch_assoc()) {
        $order_items[] = $item;
    }
} 
// Tampilkan daftar pesanan
else {
    // Hitung total halaman untuk pagination
    $search_condition = '';
    $params = [];
    $types = '';
    
    if (!empty($search)) {
        $search_condition = "AND (order_number LIKE ? OR name LIKE ? OR email LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
        $types .= 'sss';
    }
    
    if (!empty($status_filter)) {
        $search_condition .= " AND status = ?";
        $params[] = $status_filter;
        $types .= 's';
    }
    
    if (!empty($date_start)) {
        $search_condition .= " AND DATE(created_at) >= ?";
        $params[] = $date_start;
        $types .= 's';
    }
    
    if (!empty($date_end)) {
        $search_condition .= " AND DATE(created_at) <= ?";
        $params[] = $date_end;
        $types .= 's';
    }
    
    $count_query = "SELECT COUNT(*) as total FROM orders WHERE 1=1 $search_condition";
    $count_stmt = $conn->prepare($count_query);
    
    if (!empty($params)) {
        $count_stmt->bind_param($types, ...$params);
    }
    
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $count_row = $count_result->fetch_assoc();
    $total_records = $count_row['total'];
    
    // Pagination
    $records_per_page = 10;
    $total_pages = ceil($total_records / $records_per_page);
    $current_page = isset($_GET['page']) ? $_GET['page'] : 1;
    $offset = ($current_page - 1) * $records_per_page;
    
    // Ambil daftar pesanan
    $orders_query = "SELECT o.*, u.email as user_email
                    FROM orders o 
                    LEFT JOIN users u ON o.user_id = u.id 
                    WHERE 1=1 $search_condition 
                    ORDER BY o.created_at DESC 
                    LIMIT ?, ?";
    
    $params[] = $offset;
    $params[] = $records_per_page;
    $types .= 'ii';
    
    $orders_stmt = $conn->prepare($orders_query);
    $orders_stmt->bind_param($types, ...$params);
    $orders_stmt->execute();
    $orders_result = $orders_stmt->get_result();
    
    $orders = [];
    while ($row = $orders_result->fetch_assoc()) {
        $orders[] = $row;
    }
}

// Judul halaman dan halaman aktif untuk sidebar
$page_title = 'Kelola Pesanan';
$active_page = 'orders';

// Include header
include_once 'templates/header.php';
?>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-0 fw-bold">Kelola Pesanan</h3>
        <p class="text-muted mb-0">Manajemen pesanan pelanggan</p>
    </div>
    <div>
        <a href="../index.php" class="btn btn-light me-2" target="_blank">
            <i class="fas fa-external-link-alt me-2"></i>
            Lihat Website
        </a>
    </div>
</div>

<?php if (isset($status_message)): ?>
    <div class="alert alert-<?= $status_type ?> alert-dismissible fade show" role="alert">
        <?= $status_message ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if ($action === 'view'): ?>
    <!-- Detail Pesanan -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Detail Pesanan #<?= htmlspecialchars($order['order_number']) ?></h5>
            <a href="orders.php" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6 class="mb-2">Informasi Pesanan</h6>
                    <table class="table table-borderless">
                        <tr>
                            <td width="200">Nomor Pesanan</td>
                            <td><strong><?= htmlspecialchars($order['order_number']) ?></strong></td>
                        </tr>
                        <tr>
                            <td>Tanggal Pesanan</td>
                            <td><?= date('d M Y H:i', strtotime($order['created_at'])) ?></td>
                        </tr>
                        <tr>
                            <td>Status</td>
                            <td>
                                <form action="" method="POST" class="d-flex align-items-center">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <select name="status" class="form-select form-select-sm me-2" style="width: 140px;">
                                        <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                                        <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                        <option value="completed" <?= $order['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                        <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn btn-sm btn-primary">Update</button>
                                </form>
                            </td>
                        </tr>
                        <tr>
                            <td>Nomor Resi</td>
                            <td>
                                <form action="" method="POST" class="d-flex align-items-center">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <input type="hidden" name="status" value="<?= $order['status'] ?>">
                                    <input type="text" name="tracking_number" class="form-control form-control-sm me-2" value="<?= htmlspecialchars($order['tracking_number'] ?? '') ?>" placeholder="Masukkan nomor resi">
                                    <button type="submit" name="update_status" class="btn btn-sm btn-primary">Simpan</button>
                                </form>
                            </td>
                        </tr>
                        <tr>
                            <td>Metode Pembayaran</td>
                            <td><?= htmlspecialchars($order['payment_method']) ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6 class="mb-2">Informasi Pelanggan</h6>
                    <table class="table table-borderless">
                        <tr>
                            <td width="200">Nama</td>
                            <td><strong><?= htmlspecialchars($order['name']) ?></strong></td>
                        </tr>
                        <tr>
                            <td>Email</td>
                            <td><?= htmlspecialchars($order['email']) ?></td>
                        </tr>
                        <tr>
                            <td>Telepon</td>
                            <td><?= htmlspecialchars($order['phone'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <td>Alamat</td>
                            <td>
                                <?= htmlspecialchars($order['shipping_address']) ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <h6 class="mb-3">Item Pesanan</h6>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th width="70">Gambar</th>
                            <th>Buku</th>
                            <th class="text-end" width="130">Harga</th>
                            <th class="text-center" width="100">Jumlah</th>
                            <th class="text-end" width="150">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_items as $item): ?>
                            <tr>
                                <td>
                                    <img src="../<?= htmlspecialchars($item['cover_image'] ?? 'assets/images/books/default.jpg') ?>" alt="<?= htmlspecialchars($item['title'] ?? 'Buku') ?>" class="img-thumbnail" width="60">
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($item['title'] ?? 'Buku tidak tersedia') ?></strong>
                                    <?php if (!empty($item['author'])): ?>
                                        <br><small>Penulis: <?= htmlspecialchars($item['author']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end"><?= formatRupiah($item['price']) ?></td>
                                <td class="text-center"><?= $item['quantity'] ?></td>
                                <td class="text-end"><?= formatRupiah($item['price'] * $item['quantity']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="4" class="text-end"><strong>Subtotal</strong></td>
                            <td class="text-end"><?= formatRupiah($order['total_amount'] - $order['shipping_cost']) ?></td>
                        </tr>
                        <tr>
                            <td colspan="4" class="text-end"><strong>Biaya Pengiriman</strong></td>
                            <td class="text-end"><?= formatRupiah($order['shipping_cost']) ?></td>
                        </tr>
                        <tr>
                            <td colspan="4" class="text-end"><strong>Total</strong></td>
                            <td class="text-end"><?= formatRupiah($order['total_amount']) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
<?php elseif ($action === 'report'): ?>
    <!-- Laporan Penjualan -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><?= $report_title ?></h5>
            <a href="orders.php" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
        <div class="card-body">
            <form action="" method="GET" class="row g-3 mb-4">
                <input type="hidden" name="action" value="report">
                <div class="col-md-3">
                    <label for="report_type" class="form-label">Jenis Laporan</label>
                    <select name="report_type" id="report_type" class="form-select" onchange="this.form.submit()">
                        <option value="daily" <?= $report_type === 'daily' ? 'selected' : '' ?>>Harian</option>
                        <option value="monthly" <?= $report_type === 'monthly' ? 'selected' : '' ?>>Bulanan</option>
                        <option value="yearly" <?= $report_type === 'yearly' ? 'selected' : '' ?>>Tahunan</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="date_start" class="form-label">Tanggal Mulai</label>
                    <input type="date" name="date_start" id="date_start" class="form-control" value="<?= $date_start ?>">
                </div>
                <div class="col-md-3">
                    <label for="date_end" class="form-label">Tanggal Akhir</label>
                    <input type="date" name="date_end" id="date_end" class="form-control" value="<?= $date_end ?>">
                </div>
                <div class="col-md-3">
                    <label for="status_filter" class="form-label">Status</label>
                    <select name="status_filter" id="status_filter" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="processing" <?= $status_filter === 'processing' ? 'selected' : '' ?>>Processing</option>
                        <option value="shipped" <?= $status_filter === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                        <option value="completed" <?= $status_filter === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Tampilkan Laporan</button>
                    <a href="orders.php?action=report_export&report_type=<?= $report_type ?>&date_start=<?= $date_start ?>&date_end=<?= $date_end ?>&status=<?= $status_filter ?>" class="btn btn-success ms-2" target="_blank">
                        <i class="fas fa-file-excel me-1"></i> Export Excel
                    </a>
                    <a href="orders.php?action=report_print&report_type=<?= $report_type ?>&date_start=<?= $date_start ?>&date_end=<?= $date_end ?>&status=<?= $status_filter ?>" class="btn btn-secondary ms-2" target="_blank">
                        <i class="fas fa-print me-1"></i> Print
                    </a>
                </div>
            </form>
            
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Periode</th>
                            <th class="text-center">Jumlah Pesanan</th>
                            <th class="text-end">Total Penjualan</th>
                            <th class="text-end">Penjualan Selesai</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($report_data)): ?>
                            <tr>
                                <td colspan="4" class="text-center">Tidak ada data untuk periode yang dipilih</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($report_data as $item): ?>
                                <tr>
                                    <td><?= $item['period'] ?></td>
                                    <td class="text-center"><?= $item['total_orders'] ?></td>
                                    <td class="text-end"><?= formatRupiah($item['total_sales']) ?></td>
                                    <td class="text-end"><?= formatRupiah($item['completed_sales']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th>Total</th>
                            <th class="text-center"><?= $total_orders ?></th>
                            <th class="text-end"><?= formatRupiah($total_sales) ?></th>
                            <th class="text-end"><?= formatRupiah($total_completed) ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
<?php else: ?>
    <!-- Daftar Pesanan -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daftar Pesanan</h5>
            <div>
                <a href="orders.php?action=report" class="btn btn-success btn-sm">
                    <i class="fas fa-chart-bar me-1"></i> Laporan Penjualan
                </a>
            </div>
        </div>
        <div class="card-body">
            <form action="" method="GET" class="row g-3 mb-4">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Cari pesanan..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="processing" <?= $status_filter === 'processing' ? 'selected' : '' ?>>Processing</option>
                        <option value="shipped" <?= $status_filter === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                        <option value="completed" <?= $status_filter === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_start" class="form-control" placeholder="Dari tanggal" value="<?= $date_start ?>">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_end" class="form-control" placeholder="Sampai tanggal" value="<?= $date_end ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th width="80">ID</th>
                            <th>Nomor Pesanan</th>
                            <th>Tanggal</th>
                            <th>Pelanggan</th>
                            <th class="text-end">Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="7" class="text-center">Tidak ada pesanan ditemukan</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><?= $order['id'] ?></td>
                                    <td><?= htmlspecialchars($order['order_number']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($order['created_at'])) ?></td>
                                    <td>
                                        <?= htmlspecialchars($order['name']) ?>
                                        <br>
                                        <small><?= htmlspecialchars($order['email']) ?></small>
                                    </td>
                                    <td class="text-end"><?= formatRupiah($order['total_amount']) ?></td>
                                    <td>
                                        <?php
                                        $badge_class = 'bg-secondary';
                                        switch ($order['status']) {
                                            case 'pending':
                                                $badge_class = 'bg-warning text-dark';
                                                break;
                                            case 'processing':
                                                $badge_class = 'bg-info text-dark';
                                                break;
                                            case 'shipped':
                                                $badge_class = 'bg-primary';
                                                break;
                                            case 'completed':
                                                $badge_class = 'bg-success';
                                                break;
                                            case 'cancelled':
                                                $badge_class = 'bg-danger';
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?= $badge_class ?>"><?= ucfirst($order['status']) ?></span>
                                        <?php if (!empty($order['tracking_number'])): ?>
                                            <br>
                                            <small>Resi: <?= htmlspecialchars($order['tracking_number']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="orders.php?action=view&id=<?= $order['id'] ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye me-1"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($current_page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="orders.php?page=<?= $current_page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= $status_filter ?>&date_start=<?= $date_start ?>&date_end=<?= $date_end ?>">
                                    Sebelumnya
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= $i === $current_page ? 'active' : '' ?>">
                                <a class="page-link" href="orders.php?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= $status_filter ?>&date_start=<?= $date_start ?>&date_end=<?= $date_end ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($current_page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="orders.php?page=<?= $current_page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= $status_filter ?>&date_start=<?= $date_start ?>&date_end=<?= $date_end ?>">
                                    Selanjutnya
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php include_once 'templates/footer.php'; ?> 