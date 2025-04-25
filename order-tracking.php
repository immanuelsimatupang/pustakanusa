<?php
// Inisialisasi session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once 'config/database.php';

// Inisialisasi variable
$order = null;
$error = null;
$success = false;

// Fungsi format Rupiah
function formatRupiah($price)
{
    return 'Rp ' . number_format($price, 0, ',', '.');
}

// Fungsi untuk mendapatkan label status
function getStatusLabel($status)
{
    switch ($status) {
        case 'pending':
            return '<span class="badge bg-warning">Menunggu Pembayaran</span>';
        case 'processing':
            return '<span class="badge bg-info">Diproses</span>';
        case 'shipped':
            return '<span class="badge bg-primary">Dikirim</span>';
        case 'delivered':
            return '<span class="badge bg-success">Diterima</span>';
        case 'cancelled':
            return '<span class="badge bg-danger">Dibatalkan</span>';
        default:
            return '<span class="badge bg-secondary">Unknown</span>';
    }
}

// Fungsi untuk mendapatkan label status pembayaran
function getPaymentStatusLabel($status)
{
    switch ($status) {
        case 'pending':
            return '<span class="badge bg-warning">Menunggu</span>';
        case 'paid':
            return '<span class="badge bg-success">Dibayar</span>';
        case 'failed':
            return '<span class="badge bg-danger">Gagal</span>';
        case 'cancelled':
            return '<span class="badge bg-danger">Dibatalkan</span>';
        default:
            return '<span class="badge bg-secondary">Unknown</span>';
    }
}

// Proses form pencarian pesanan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['order_number']) && !empty($_POST['order_number']) && isset($_POST['email']) && !empty($_POST['email'])) {
        $order_number = trim($_POST['order_number']);
        $email = trim($_POST['email']);
        
        // Query untuk mencari pesanan
        $sql = "SELECT o.*, COUNT(oi.id) as total_items 
                FROM orders o 
                LEFT JOIN order_items oi ON o.id = oi.order_id 
                WHERE o.order_number = ? AND o.customer_email = ? 
                GROUP BY o.id";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $order_number, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $order = $result->fetch_assoc();
            $success = true;
            
            // Ambil detail item pesanan
            $items_sql = "SELECT * FROM order_items WHERE order_id = ?";
            $items_stmt = $conn->prepare($items_sql);
            $items_stmt->bind_param("i", $order['id']);
            $items_stmt->execute();
            $items_result = $items_stmt->get_result();
            
            $order_items = [];
            while ($item = $items_result->fetch_assoc()) {
                $order_items[] = $item;
            }
            
            $order['items'] = $order_items;
        } else {
            $error = "Pesanan tidak ditemukan. Pastikan nomor pesanan dan email benar.";
        }
    } else {
        $error = "Nomor pesanan dan email wajib diisi.";
    }
}

// Include header
include_once 'templates/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h1 class="mb-4 text-center">Lacak Pesanan</h1>
            
            <?php if (!$success): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <p class="text-center mb-4">Masukkan informasi pesanan Anda untuk melacak status pengiriman</p>
                    
                    <?php if ($error): ?>
                    <div class="alert alert-danger mb-4" role="alert">
                        <?= $error ?>
                    </div>
                    <?php endif; ?>
                    
                    <form method="post" action="" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="order_number" class="form-label">Nomor Pesanan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="order_number" name="order_number" value="<?= isset($_POST['order_number']) ? htmlspecialchars($_POST['order_number']) : '' ?>" required>
                            <div class="invalid-feedback">
                                Nomor pesanan wajib diisi
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
                            <div class="invalid-feedback">
                                Email wajib diisi
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Lacak Pesanan</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="text-center">
                <p class="mb-1">Sudah memiliki akun?</p>
                <a href="profile.php?tab=orders" class="text-decoration-none">Lihat semua pesanan Anda</a>
            </div>
            <?php else: ?>
            <!-- Detail Pesanan -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Detail Pesanan #<?= htmlspecialchars($order['order_number']) ?></h5>
                        <div>
                            <?= getStatusLabel($order['order_status']) ?>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3">Informasi Pesanan</h6>
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <span class="fw-medium">Tanggal Pesanan:</span><br>
                                    <?= date('d F Y, H:i', strtotime($order['order_date'])) ?>
                                </li>
                                <li class="mb-2">
                                    <span class="fw-medium">Status Pembayaran:</span><br>
                                    <?= getPaymentStatusLabel($order['payment_status']) ?>
                                </li>
                                <li class="mb-2">
                                    <span class="fw-medium">Status Pesanan:</span><br>
                                    <?= getStatusLabel($order['order_status']) ?>
                                </li>
                                <li class="mb-2">
                                    <span class="fw-medium">Metode Pembayaran:</span><br>
                                    <?= ucfirst(str_replace('_', ' ', $order['payment_method'])) ?>
                                </li>
                                <?php if (!empty($order['tracking_number'])): ?>
                                <li class="mb-2">
                                    <span class="fw-medium">Nomor Resi:</span><br>
                                    <?= htmlspecialchars($order['tracking_number']) ?>
                                </li>
                                <?php endif; ?>
                                <li>
                                    <span class="fw-medium">Total Harga:</span><br>
                                    <span class="fs-5 text-primary"><?= formatRupiah($order['total_price']) ?></span>
                                </li>
                            </ul>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3">Informasi Pengiriman</h6>
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <span class="fw-medium">Nama Penerima:</span><br>
                                    <?= htmlspecialchars($order['customer_name']) ?>
                                </li>
                                <li class="mb-2">
                                    <span class="fw-medium">Email:</span><br>
                                    <?= htmlspecialchars($order['customer_email']) ?>
                                </li>
                                <li class="mb-2">
                                    <span class="fw-medium">Telepon:</span><br>
                                    <?= htmlspecialchars($order['customer_phone']) ?>
                                </li>
                                <li>
                                    <span class="fw-medium">Alamat Pengiriman:</span><br>
                                    <?= htmlspecialchars($order['shipping_address']) ?>, 
                                    <?= htmlspecialchars($order['shipping_city']) ?>, 
                                    <?= htmlspecialchars($order['shipping_postal_code']) ?>
                                    <?= !empty($order['shipping_province']) ? ', ' . htmlspecialchars($order['shipping_province']) : '' ?>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <!-- Status Pengiriman Timeline -->
                    <h6 class="fw-bold mb-3">Status Pengiriman</h6>
                    <div class="position-relative pb-3 mb-4" style="max-width: 700px; margin: 0 auto;">
                        <!-- Line -->
                        <div class="position-absolute bg-light" style="height: 5px; top: 30px; left: 10%; right: 10%;"></div>
                        
                        <!-- Steps -->
                        <div class="d-flex justify-content-between">
                            <div class="text-center" style="width: 20%;">
                                <div class="position-relative mb-2">
                                    <span class="<?= in_array($order['order_status'], ['pending', 'processing', 'shipped', 'delivered']) ? 'bg-success' : 'bg-light' ?> text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px; border: 3px solid #fff; box-shadow: 0 2px 4px rgba(0,0,0,.1);">
                                        <i class="fas fa-check"></i>
                                    </span>
                                </div>
                                <p class="small mb-0">Pesanan<br>Dibuat</p>
                            </div>
                            
                            <div class="text-center" style="width: 20%;">
                                <div class="position-relative mb-2">
                                    <span class="<?= in_array($order['order_status'], ['processing', 'shipped', 'delivered']) ? 'bg-success' : 'bg-light' ?> text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px; border: 3px solid #fff; box-shadow: 0 2px 4px rgba(0,0,0,.1);">
                                        <?php if (in_array($order['order_status'], ['processing', 'shipped', 'delivered'])): ?>
                                        <i class="fas fa-check"></i>
                                        <?php else: ?>
                                        <i class="fas fa-credit-card text-muted"></i>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <p class="small mb-0">Pembayaran<br>Dikonfirmasi</p>
                            </div>
                            
                            <div class="text-center" style="width: 20%;">
                                <div class="position-relative mb-2">
                                    <span class="<?= in_array($order['order_status'], ['processing', 'shipped', 'delivered']) ? 'bg-success' : 'bg-light' ?> text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px; border: 3px solid #fff; box-shadow: 0 2px 4px rgba(0,0,0,.1);">
                                        <?php if (in_array($order['order_status'], ['processing', 'shipped', 'delivered'])): ?>
                                        <i class="fas fa-check"></i>
                                        <?php else: ?>
                                        <i class="fas fa-box text-muted"></i>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <p class="small mb-0">Pesanan<br>Diproses</p>
                            </div>
                            
                            <div class="text-center" style="width: 20%;">
                                <div class="position-relative mb-2">
                                    <span class="<?= in_array($order['order_status'], ['shipped', 'delivered']) ? 'bg-success' : 'bg-light' ?> text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px; border: 3px solid #fff; box-shadow: 0 2px 4px rgba(0,0,0,.1);">
                                        <?php if (in_array($order['order_status'], ['shipped', 'delivered'])): ?>
                                        <i class="fas fa-check"></i>
                                        <?php else: ?>
                                        <i class="fas fa-shipping-fast text-muted"></i>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <p class="small mb-0">Pesanan<br>Dikirim</p>
                            </div>
                            
                            <div class="text-center" style="width: 20%;">
                                <div class="position-relative mb-2">
                                    <span class="<?= $order['order_status'] === 'delivered' ? 'bg-success' : 'bg-light' ?> text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px; border: 3px solid #fff; box-shadow: 0 2px 4px rgba(0,0,0,.1);">
                                        <?php if ($order['order_status'] === 'delivered'): ?>
                                        <i class="fas fa-check"></i>
                                        <?php else: ?>
                                        <i class="fas fa-home text-muted"></i>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <p class="small mb-0">Pesanan<br>Diterima</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Item Pesanan -->
                    <h6 class="fw-bold mb-3">Item Pesanan (<?= count($order['items']) ?>)</h6>
                    <div class="table-responsive">
                        <table class="table">
                            <thead class="table-light">
                                <tr>
                                    <th>Buku</th>
                                    <th class="text-center">Harga</th>
                                    <th class="text-center">Jumlah</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order['items'] as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['title']) ?></td>
                                    <td class="text-center"><?= formatRupiah($item['price']) ?></td>
                                    <td class="text-center"><?= $item['quantity'] ?></td>
                                    <td class="text-end"><?= formatRupiah($item['subtotal']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Subtotal</td>
                                    <td class="text-end"><?= formatRupiah($order['total_amount']) ?></td>
                                </tr>
                                <?php if ($order['shipping_cost'] > 0): ?>
                                <tr>
                                    <td colspan="3" class="text-end">Biaya Pengiriman</td>
                                    <td class="text-end"><?= formatRupiah($order['shipping_cost']) ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if ($order['discount_amount'] > 0): ?>
                                <tr>
                                    <td colspan="3" class="text-end">Diskon</td>
                                    <td class="text-end text-success">-<?= formatRupiah($order['discount_amount']) ?></td>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Total</td>
                                    <td class="text-end fw-bold"><?= formatRupiah($order['total_price']) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <div class="mt-4 text-center">
                        <?php if ($order['payment_status'] === 'pending'): ?>
                        <a href="payment.php?order=<?= $order['order_number'] ?>" class="btn btn-primary me-2">Bayar Sekarang</a>
                        <?php endif; ?>
                        <a href="order-tracking.php" class="btn btn-outline-secondary">Lacak Pesanan Lain</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Form validation
(function() {
    'use strict'
    
    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    var forms = document.querySelectorAll('.needs-validation')
    
    // Loop over them and prevent submission
    Array.prototype.slice.call(forms)
        .forEach(function(form) {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                
                form.classList.add('was-validated')
            }, false)
        })
})()
</script>

<?php include_once 'templates/footer.php'; ?> 