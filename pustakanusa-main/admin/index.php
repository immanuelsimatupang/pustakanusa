<?php
// Include header
session_start();

// Cek apakah pengguna sudah login sebagai admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit;
}

// Include konfigurasi
require_once '../config/database.php';
require_once '../config/shipping.php';

// Fungsi untuk sanitasi input
function sanitize($input) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($input));
}

// Proses form tambah/edit/hapus untuk pengiriman
$success_message = '';
$error_message = '';

// Cek pesan sukses atau error dari halaman lain
if (isset($_GET['success'])) {
    $success_message = $_GET['success'];
}
if (isset($_GET['error'])) {
    $error_message = $_GET['error'];
}

// Proses penambahan provinsi
if (isset($_POST['add_province'])) {
    $name = sanitize($_POST['province_name']);
    
    if (!empty($name)) {
        $sql = "INSERT INTO provinces (name) VALUES (?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $name);
        
        if ($stmt->execute()) {
            $success_message = "Provinsi berhasil ditambahkan.";
        } else {
            $error_message = "Gagal menambahkan provinsi: " . $conn->error;
        }
    } else {
        $error_message = "Nama provinsi tidak boleh kosong.";
    }
}

// Proses penambahan kota
if (isset($_POST['add_city'])) {
    $province_id = intval($_POST['province_id']);
    $name = sanitize($_POST['city_name']);
    
    if (!empty($name) && $province_id > 0) {
        $sql = "INSERT INTO cities (province_id, name) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $province_id, $name);
        
        if ($stmt->execute()) {
            $success_message = "Kota berhasil ditambahkan.";
        } else {
            $error_message = "Gagal menambahkan kota: " . $conn->error;
        }
    } else {
        $error_message = "Provinsi dan nama kota harus diisi.";
    }
}

// Proses penambahan tarif pengiriman
if (isset($_POST['add_shipping_cost'])) {
    $city_id = intval($_POST['city_id']);
    $courier = sanitize($_POST['courier']);
    $service_name = sanitize($_POST['service_name']);
    $cost_per_kg = intval($_POST['cost_per_kg']);
    $etd = sanitize($_POST['etd']);
    
    if ($city_id > 0 && !empty($courier) && !empty($service_name) && $cost_per_kg > 0 && !empty($etd)) {
        $sql = "INSERT INTO shipping_costs (city_id, courier, service_name, cost_per_kg, etd) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issss", $city_id, $courier, $service_name, $cost_per_kg, $etd);
        
        if ($stmt->execute()) {
            $success_message = "Tarif pengiriman berhasil ditambahkan.";
        } else {
            $error_message = "Gagal menambahkan tarif pengiriman: " . $conn->error;
        }
    } else {
        $error_message = "Semua field harus diisi dengan benar.";
    }
}

// Proses hapus data
if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $type = isset($_GET['type']) ? $_GET['type'] : '';
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($id > 0) {
        switch ($type) {
            case 'province':
                // Cek apakah ada kota yang terkait
                $check_sql = "SELECT COUNT(*) as count FROM cities WHERE province_id = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("i", $id);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                $check_row = $check_result->fetch_assoc();
                
                if ($check_row['count'] > 0) {
                    $error_message = "Tidak dapat menghapus provinsi karena masih ada kota yang terkait.";
                } else {
                    $sql = "DELETE FROM provinces WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $id);
                    
                    if ($stmt->execute()) {
                        $success_message = "Provinsi berhasil dihapus.";
                    } else {
                        $error_message = "Gagal menghapus provinsi: " . $conn->error;
                    }
                }
                break;
                
            case 'city':
                // Cek apakah ada tarif pengiriman yang terkait
                $check_sql = "SELECT COUNT(*) as count FROM shipping_costs WHERE city_id = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("i", $id);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                $check_row = $check_result->fetch_assoc();
                
                if ($check_row['count'] > 0) {
                    $error_message = "Tidak dapat menghapus kota karena masih ada tarif pengiriman yang terkait.";
                } else {
                    $sql = "DELETE FROM cities WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $id);
                    
                    if ($stmt->execute()) {
                        $success_message = "Kota berhasil dihapus.";
                    } else {
                        $error_message = "Gagal menghapus kota: " . $conn->error;
                    }
                }
                break;
                
            case 'shipping_cost':
                $sql = "DELETE FROM shipping_costs WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                
                if ($stmt->execute()) {
                    $success_message = "Tarif pengiriman berhasil dihapus.";
                } else {
                    $error_message = "Gagal menghapus tarif pengiriman: " . $conn->error;
                }
                break;
                
            default:
                $error_message = "Tipe data tidak valid.";
                break;
        }
    } else {
        $error_message = "ID tidak valid.";
    }
}

// Ambil data untuk ditampilkan
$provinces = getProvinces();
$cities = [];
$shipping_costs = [];

// Ambil data kota
$cities_sql = "SELECT c.id, c.name, c.province_id, p.name as province_name 
               FROM cities c
               JOIN provinces p ON c.province_id = p.id
               ORDER BY p.name, c.name";
$cities_result = $conn->query($cities_sql);

if ($cities_result && $cities_result->num_rows > 0) {
    while ($row = $cities_result->fetch_assoc()) {
        $cities[] = $row;
    }
}

// Ambil data tarif pengiriman
$costs_sql = "SELECT sc.id, sc.city_id, sc.courier, sc.service_name, sc.cost_per_kg, sc.etd,
              c.name as city_name, p.name as province_name
              FROM shipping_costs sc
              JOIN cities c ON sc.city_id = c.id
              JOIN provinces p ON c.province_id = p.id
              ORDER BY p.name, c.name, sc.courier, sc.service_name";
$costs_result = $conn->query($costs_sql);

if ($costs_result && $costs_result->num_rows > 0) {
    while ($row = $costs_result->fetch_assoc()) {
        $shipping_costs[] = $row;
    }
}

// Ambil data untuk dashboard
$books_count_sql = "SELECT COUNT(*) as total FROM books";
$books_result = $conn->query($books_count_sql);
$books_count = ($books_result && $books_result->num_rows > 0) ? $books_result->fetch_assoc()['total'] : 0;

$orders_count_sql = "SELECT COUNT(*) as total FROM orders WHERE status = 'pending'";
$orders_result = $conn->query($orders_count_sql);
$orders_count = ($orders_result && $orders_result->num_rows > 0) ? $orders_result->fetch_assoc()['total'] : 0;

$users_count_sql = "SELECT COUNT(*) as total FROM users";
$users_result = $conn->query($users_count_sql);
$users_count = ($users_result && $users_result->num_rows > 0) ? $users_result->fetch_assoc()['total'] : 0;

// Include header
include_once '../templates/header.php';
?>

<div class="container my-5">
    <h1 class="mb-4">Dashboard Admin</h1>
    
    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success"><?= $success_message ?></div>
    <?php endif; ?>
    
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger"><?= $error_message ?></div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-3">
            <div class="list-group mb-4">
                <a href="index.php" class="list-group-item list-group-item-action active">Dashboard</a>
                <a href="books.php" class="list-group-item list-group-item-action">Manajemen Buku</a>
                <a href="orders.php" class="list-group-item list-group-item-action">Manajemen Pesanan</a>
                <a href="users.php" class="list-group-item list-group-item-action">Manajemen Pengguna</a>
                <a href="categories.php" class="list-group-item list-group-item-action">Manajemen Kategori</a>
                <a href="settings.php" class="list-group-item list-group-item-action">Pengaturan</a>
            </div>
        </div>
        
        <div class="col-md-9">
            <!-- Dashboard Stats -->
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="card-title">Total Buku</h5>
                            <p class="display-4"><?= $books_count ?></p>
                            <a href="books.php" class="btn btn-primary btn-sm">Lihat Detail</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="card-title">Pesanan Baru</h5>
                            <p class="display-4"><?= $orders_count ?></p>
                            <a href="orders.php" class="btn btn-primary btn-sm">Lihat Detail</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="card-title">Total Pengguna</h5>
                            <p class="display-4"><?= $users_count ?></p>
                            <a href="users.php" class="btn btn-primary btn-sm">Lihat Detail</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Main Content Area -->
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0">Pesanan Terbaru</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>No. Pesanan</th>
                                            <th>Pelanggan</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Ambil data pesanan terbaru
                                        $recent_orders_sql = "SELECT o.id, o.order_number, o.created_at, u.name as customer_name, 
                                                             o.total_amount, o.status 
                                                      FROM orders o 
                                                      JOIN users u ON o.user_id = u.id 
                                                      ORDER BY o.created_at DESC LIMIT 5";
                                        $recent_orders_result = $conn->query($recent_orders_sql);
                                        
                                        if ($recent_orders_result && $recent_orders_result->num_rows > 0) {
                                            while ($order = $recent_orders_result->fetch_assoc()) {
                                                $status_class = '';
                                                switch ($order['status']) {
                                                    case 'pending':
                                                        $status_class = 'bg-warning';
                                                        $status_text = 'Pending';
                                                        break;
                                                    case 'processing':
                                                        $status_class = 'bg-info';
                                                        $status_text = 'Diproses';
                                                        break;
                                                    case 'shipped':
                                                        $status_class = 'bg-success';
                                                        $status_text = 'Dikirim';
                                                        break;
                                                    case 'completed':
                                                        $status_class = 'bg-primary';
                                                        $status_text = 'Selesai';
                                                        break;
                                                    case 'cancelled':
                                                        $status_class = 'bg-danger';
                                                        $status_text = 'Dibatalkan';
                                                        break;
                                                    default:
                                                        $status_class = 'bg-secondary';
                                                        $status_text = $order['status'];
                                                }
                                                ?>
                                                <tr>
                                                    <td><?= $order['order_number'] ?></td>
                                                    <td><?= $order['customer_name'] ?></td>
                                                    <td><span class="badge <?= $status_class ?>"><?= $status_text ?></span></td>
                                                    <td><a href="orders.php?action=view&id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary">Detail</a></td>
                                                </tr>
                                                <?php
                                            }
                                        } else {
                                            echo '<tr><td colspan="4" class="text-center">Tidak ada pesanan terbaru</td></tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-end">
                                <a href="orders.php" class="btn btn-outline-primary btn-sm">Lihat Semua Pesanan</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0">Buku Terlaris</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <?php
                                // Ambil data buku terlaris
                                $bestseller_sql = "SELECT b.id, b.title, COUNT(oi.book_id) as sold
                                              FROM books b
                                              LEFT JOIN order_items oi ON b.id = oi.book_id
                                              LEFT JOIN orders o ON oi.order_id = o.id
                                              WHERE o.status != 'cancelled' OR o.status IS NULL
                                              GROUP BY b.id
                                              ORDER BY sold DESC
                                              LIMIT 5";
                                $bestseller_result = $conn->query($bestseller_sql);
                                
                                if ($bestseller_result && $bestseller_result->num_rows > 0) {
                                    while ($book = $bestseller_result->fetch_assoc()) {
                                        ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <?= $book['title'] ?>
                                            <span class="badge bg-primary rounded-pill"><?= $book['sold'] ?></span>
                                        </li>
                                        <?php
                                    }
                                } else {
                                    echo '<li class="list-group-item">Tidak ada data penjualan buku</li>';
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Shipping Management Section -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Manajemen Data Pengiriman</h5>
                    <div>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addProvinceModal">
                            <i class="fas fa-plus"></i> Provinsi
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCityModal">
                            <i class="fas fa-plus"></i> Kota
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addShippingCostModal">
                            <i class="fas fa-plus"></i> Tarif
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs" id="shippingTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="province-tab" data-bs-toggle="tab" href="#province" role="tab">Provinsi</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="city-tab" data-bs-toggle="tab" href="#city" role="tab">Kota</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="shipping-cost-tab" data-bs-toggle="tab" href="#shipping-cost" role="tab">Tarif Pengiriman</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="import-export-tab" data-bs-toggle="tab" href="#import-export" role="tab">Import/Export</a>
                        </li>
                    </ul>
                    
                    <div class="tab-content mt-3" id="shippingTabContent">
                        <!-- Tab Provinsi -->
                        <div class="tab-pane fade show active" id="province" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nama Provinsi</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($provinces as $province): ?>
                                        <tr>
                                            <td><?= $province['province_id'] ?></td>
                                            <td><?= $province['province'] ?></td>
                                            <td>
                                                <a href="?action=delete&type=province&id=<?= $province['province_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus provinsi ini?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Tab Kota -->
                        <div class="tab-pane fade" id="city" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Provinsi</th>
                                            <th>Nama Kota</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($cities as $city): ?>
                                        <tr>
                                            <td><?= $city['id'] ?></td>
                                            <td><?= $city['province_name'] ?></td>
                                            <td><?= $city['name'] ?></td>
                                            <td>
                                                <a href="?action=delete&type=city&id=<?= $city['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus kota ini?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Tab Tarif Pengiriman -->
                        <div class="tab-pane fade" id="shipping-cost" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Kota</th>
                                            <th>Kurir</th>
                                            <th>Layanan</th>
                                            <th>Biaya/kg</th>
                                            <th>Estimasi</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($shipping_costs as $cost): ?>
                                        <tr>
                                            <td><?= $cost['id'] ?></td>
                                            <td><?= $cost['city_name'] ?> (<?= $cost['province_name'] ?>)</td>
                                            <td><?= strtoupper($cost['courier']) ?></td>
                                            <td><?= $cost['service_name'] ?></td>
                                            <td>Rp <?= number_format($cost['cost_per_kg'], 0, ',', '.') ?></td>
                                            <td><?= $cost['etd'] ?> hari</td>
                                            <td>
                                                <a href="?action=delete&type=shipping_cost&id=<?= $cost['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus tarif ini?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Tab Import/Export -->
                        <div class="tab-pane fade" id="import-export" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Export Data</h6>
                                    <p>Download data pengiriman dalam format CSV</p>
                                    <div class="d-grid gap-2">
                                        <a href="export-shipping.php?type=provinces" class="btn btn-outline-primary">Export Provinsi</a>
                                        <a href="export-shipping.php?type=cities" class="btn btn-outline-primary">Export Kota</a>
                                        <a href="export-shipping.php?type=shipping_costs" class="btn btn-outline-primary">Export Tarif Pengiriman</a>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6>Import Data</h6>
                                    <p>Upload file CSV untuk import data</p>
                                    <form action="import-shipping.php" method="post" enctype="multipart/form-data">
                                        <div class="mb-3">
                                            <label for="import_type" class="form-label">Jenis Data</label>
                                            <select class="form-select" id="import_type" name="import_type" required>
                                                <option value="">Pilih Jenis Data</option>
                                                <option value="provinces">Provinsi</option>
                                                <option value="cities">Kota</option>
                                                <option value="shipping_costs">Tarif Pengiriman</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="csv_file" class="form-label">File CSV</label>
                                            <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Import Data</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Menu Cepat -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Menu Cepat</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-2">
                                    <a href="books.php?action=add" class="btn btn-outline-primary w-100">
                                        <i class="fas fa-book"></i> Tambah Buku
                                    </a>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <a href="categories.php?action=add" class="btn btn-outline-primary w-100">
                                        <i class="fas fa-tags"></i> Tambah Kategori
                                    </a>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <a href="orders.php?status=pending" class="btn btn-outline-primary w-100">
                                        <i class="fas fa-clock"></i> Pesanan Pending
                                    </a>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <a href="../index.php" class="btn btn-outline-secondary w-100" target="_blank">
                                        <i class="fas fa-store"></i> Lihat Toko
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

<!-- Modal Tambah Provinsi -->
<div class="modal fade" id="addProvinceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="" method="post">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Provinsi Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="province_name" class="form-label">Nama Provinsi</label>
                        <input type="text" class="form-control" id="province_name" name="province_name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="add_province" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Tambah Kota -->
<div class="modal fade" id="addCityModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="" method="post">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Kota Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="province_id" class="form-label">Provinsi</label>
                        <select class="form-select" id="province_id" name="province_id" required>
                            <option value="">Pilih Provinsi</option>
                            <?php foreach ($provinces as $province): ?>
                                <option value="<?= $province['province_id'] ?>"><?= $province['province'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="city_name" class="form-label">Nama Kota</label>
                        <input type="text" class="form-control" id="city_name" name="city_name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="add_city" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Tambah Tarif Pengiriman -->
<div class="modal fade" id="addShippingCostModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="" method="post">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Tarif Pengiriman Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="city_id" class="form-label">Kota</label>
                        <select class="form-select" id="city_id" name="city_id" required>
                            <option value="">Pilih Kota</option>
                            <?php foreach ($cities as $city): ?>
                                <option value="<?= $city['id'] ?>"><?= $city['name'] ?> (<?= $city['province_name'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="courier" class="form-label">Kurir</label>
                        <select class="form-select" id="courier" name="courier" required>
                            <option value="">Pilih Kurir</option>
                            <?php foreach (getCouriers() as $courier): ?>
                                <option value="<?= $courier['id'] ?>"><?= $courier['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="service_name" class="form-label">Nama Layanan</label>
                        <input type="text" class="form-control" id="service_name" name="service_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="cost_per_kg" class="form-label">Biaya per Kg (Rp)</label>
                        <input type="number" class="form-control" id="cost_per_kg" name="cost_per_kg" min="1000" required>
                    </div>
                    <div class="mb-3">
                        <label for="etd" class="form-label">Estimasi Waktu Pengiriman (hari)</label>
                        <input type="text" class="form-control" id="etd" name="etd" placeholder="Contoh: 1-2" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="add_shipping_cost" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once '../templates/footer.php'; ?> 