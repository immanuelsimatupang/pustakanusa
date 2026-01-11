<?php
// Inisialisasi session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include konfigurasi
require_once 'config/database.php';
require_once 'config/midtrans.php';

// Inisialisasi variable
$order = null;
$error = null;
$snap_token = null;

// Fungsi format Rupiah
function formatRupiah($price)
{
    return 'Rp ' . number_format($price, 0, ',', '.');
}

// Cek apakah ada parameter order
if (isset($_GET['order']) && !empty($_GET['order'])) {
    $order_number = trim($_GET['order']);
    
    // Ambil data pesanan
    $sql = "SELECT o.*, COUNT(oi.id) as total_items 
            FROM orders o 
            LEFT JOIN order_items oi ON o.id = oi.order_id 
            WHERE o.order_number = ? 
            GROUP BY o.id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $order_number);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();
        
        // Cek status pembayaran
        if ($order['payment_status'] !== 'pending') {
            if ($order['payment_status'] === 'paid') {
                $error = "Pesanan ini sudah dibayar.";
            } else {
                $error = "Status pembayaran pesanan: " . ucfirst($order['payment_status']);
            }
        } else {
            // Ambil detail item pesanan
            $items_sql = "SELECT * FROM order_items WHERE order_id = ?";
            $items_stmt = $conn->prepare($items_sql);
            $items_stmt->bind_param("i", $order['id']);
            $items_stmt->execute();
            $items_result = $items_stmt->get_result();
            
            $order_items = [];
            while ($item = $items_result->fetch_assoc()) {
                $item['book_id'] = $item['book_id'] ?: 0;
                $order_items[] = $item;
            }
            
            $order['items'] = $order_items;
            
            // Persiapkan data untuk Midtrans
            $customer = [
                'name' => $order['customer_name'],
                'email' => $order['customer_email'],
                'phone' => $order['customer_phone'],
                'address' => $order['shipping_address'],
                'city' => $order['shipping_city'],
                'postal_code' => $order['shipping_postal_code']
            ];
            
            // Dapatkan Snap Token
            $snap_token = getMidtransSnapToken($order, $order['items'], $customer);
            
            if (!$snap_token) {
                $error = "Terjadi kesalahan saat memproses pembayaran. Silakan coba lagi nanti.";
            }
        }
    } else {
        $error = "Pesanan tidak ditemukan.";
    }
} else {
    $error = "Nomor pesanan tidak valid.";
}

// Include header
include_once 'templates/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h1 class="mb-4 text-center">Pembayaran</h1>
            
            <?php if ($error): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-body py-5 text-center">
                    <div class="mb-4">
                        <i class="fas fa-exclamation-circle text-warning" style="font-size: 4rem;"></i>
                    </div>
                    <h4 class="mb-4"><?= $error ?></h4>
                    <div>
                        <a href="order-tracking.php" class="btn btn-primary">Lacak Pesanan</a>
                        <a href="profile.php?tab=orders" class="btn btn-outline-secondary ms-2">Lihat Pesanan Saya</a>
                    </div>
                </div>
            </div>
            <?php elseif ($order): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Detail Pesanan #<?= htmlspecialchars($order['order_number']) ?></h5>
                </div>
                <div class="card-body p-4">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Nama:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
                            <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($order['customer_email']) ?></p>
                            <p class="mb-1"><strong>Telepon:</strong> <?= htmlspecialchars($order['customer_phone']) ?></p>
                            <p class="mb-0"><strong>Alamat:</strong> <?= htmlspecialchars($order['shipping_address']) ?>, <?= htmlspecialchars($order['shipping_city']) ?>, <?= htmlspecialchars($order['shipping_postal_code']) ?></p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <p class="mb-1"><strong>Tanggal Pesanan:</strong> <?= date('d F Y, H:i', strtotime($order['order_date'])) ?></p>
                            <p class="mb-1"><strong>Metode Pembayaran:</strong> <?= ucfirst(str_replace('_', ' ', $order['payment_method'])) ?></p>
                            <p class="mb-1"><strong>Status Pembayaran:</strong> <span class="badge bg-warning">Menunggu Pembayaran</span></p>
                            <p class="mb-0"><strong>Batas Pembayaran:</strong> <?= date('d F Y, H:i', strtotime($order['order_date'] . ' +1 day')) ?></p>
                        </div>
                    </div>
                    
                    <div class="table-responsive mb-4">
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
                    
                    <div class="text-center">
                        <h5 class="mb-4">Total yang harus dibayar: <span class="text-primary"><?= formatRupiah($order['total_price']) ?></span></h5>
                        
                        <?php if ($snap_token): ?>
                        <!-- Tombol Pembayaran Midtrans -->
                        <button id="pay-button" class="btn btn-primary btn-lg">Bayar Sekarang</button>
                        
                        <!-- Informasi tambahan -->
                        <div class="mt-4">
                            <p class="small text-muted mb-1">Dengan menekan tombol "Bayar Sekarang", Anda akan diarahkan ke halaman pembayaran yang aman.</p>
                            <p class="small text-muted mb-0">Kami menerima berbagai metode pembayaran termasuk kartu kredit, transfer bank, e-wallet dan lainnya.</p>
                        </div>
                        <?php else: ?>
                        <!-- Pesan error jika token tidak berhasil dibuat -->
                        <div class="alert alert-danger">
                            Terjadi kesalahan saat memproses pembayaran. Silakan coba lagi nanti.
                        </div>
                        <a href="order-tracking.php" class="btn btn-primary">Kembali</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Informasi Keamanan -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6 class="mb-2">Pembayaran Aman & Terjamin</h6>
                            <p class="small text-muted mb-0">Semua transaksi dilindungi dengan enkripsi SSL. Informasi pembayaran Anda tidak akan pernah disimpan di server kami.</p>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <img src="assets/images/payment/payment-security.png" alt="Secure Payment" class="img-fluid" style="max-height: 40px;">
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($snap_token): ?>
<!-- Midtrans JS -->
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="<?= MIDTRANS_CLIENT_KEY ?>"></script>
<script>
document.getElementById('pay-button').addEventListener('click', function() {
    // Panggil Snap dengan token
    snap.pay('<?= $snap_token ?>', {
        onSuccess: function(result) {
            // Redirect ke halaman sukses
            window.location.href = 'payment-success.php?order=<?= $order['order_number'] ?>&transaction_id=' + result.transaction_id;
        },
        onPending: function(result) {
            // Redirect ke halaman pending
            window.location.href = 'payment-pending.php?order=<?= $order['order_number'] ?>&transaction_id=' + result.transaction_id;
        },
        onError: function(result) {
            // Redirect ke halaman error
            window.location.href = 'payment-error.php?order=<?= $order['order_number'] ?>&message=' + result.status_message;
        },
        onClose: function() {
            // User menutup popup pembayaran tanpa menyelesaikan
            alert('Anda menutup popup pembayaran tanpa menyelesaikan transaksi.');
        }
    });
});
</script>
<?php endif; ?>

<?php include_once 'templates/footer.php'; ?> 