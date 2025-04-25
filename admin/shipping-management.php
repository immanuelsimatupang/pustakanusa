<?php
// File untuk mengelola data pengiriman (admin)
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

// Proses form tambah/edit/hapus
$success_message = '';
$error_message = '';

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

// Include header
include_once '../templates/header.php';
?>

<div class="container my-5">
    <h1 class="mb-4">Manajemen Data Pengiriman</h1>
    
    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success"><?= $success_message ?></div>
    <?php endif; ?>
    
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger"><?= $error_message ?></div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-3">
            <div class="nav flex-column nav-pills" id="shipping-tab" role="tablist">
                <a class="nav-link active" id="province-tab" data-bs-toggle="pill" href="#province" role="tab">Provinsi</a>
                <a class="nav-link" id="city-tab" data-bs-toggle="pill" href="#city" role="tab">Kota</a>
                <a class="nav-link" id="shipping-cost-tab" data-bs-toggle="pill" href="#shipping-cost" role="tab">Tarif Pengiriman</a>
                <a class="nav-link" id="import-export-tab" data-bs-toggle="pill" href="#import-export" role="tab">Import/Export</a>
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="tab-content" id="shipping-tabContent">
                <!-- Tab Provinsi -->
                <div class="tab-pane fade show active" id="province" role="tabpanel">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Daftar Provinsi</h5>
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addProvinceModal">
                                <i class="fas fa-plus"></i> Tambah Provinsi
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
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
                    </div>
                </div>
                
                <!-- Tab Kota -->
                <div class="tab-pane fade" id="city" role="tabpanel">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Daftar Kota</h5>
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCityModal">
                                <i class="fas fa-plus"></i> Tambah Kota
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
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
                    </div>
                </div>
                
                <!-- Tab Tarif Pengiriman -->
                <div class="tab-pane fade" id="shipping-cost" role="tabpanel">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Daftar Tarif Pengiriman</h5>
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addShippingCostModal">
                                <i class="fas fa-plus"></i> Tambah Tarif
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
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
                    </div>
                </div>
                
                <!-- Tab Import/Export -->
                <div class="tab-pane fade" id="import-export" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Import/Export Data Pengiriman</h5>
                        </div>
                        <div class="card-body">
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