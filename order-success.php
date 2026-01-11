<?php
// Inisialisasi session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fungsi format Rupiah
function formatRupiah($price)
{
    return 'Rp ' . number_format($price, 0, ',', '.');
}

// Redirect jika tidak ada data pesanan
if (!isset($_SESSION['order_number']) || !isset($_SESSION['order_total'])) {
    header('Location: index.php');
    exit;
}

// Ambil data pesanan dari session
$order_number = $_SESSION['order_number'];
$order_total = $_SESSION['order_total'];

// Include header
include_once 'templates/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
                    </div>
                    <h1 class="mb-3">Terima Kasih!</h1>
                    <p class="fs-5 mb-4">Pesanan Anda berhasil dibuat</p>
                    <div class="row justify-content-center mb-4">
                        <div class="col-md-8">
                            <div class="card bg-light">
                                <div class="card-body p-4">
                                    <p class="mb-2"><strong>Nomor Pesanan:</strong></p>
                                    <p class="fs-5 mb-3"><?= htmlspecialchars($order_number) ?></p>
                                    <p class="mb-2"><strong>Total Pembayaran:</strong></p>
                                    <p class="fs-5 mb-0 text-primary"><?= formatRupiah($order_total) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <p class="mb-4">Kami telah mengirimkan rincian pesanan ke email Anda.<br>
                    Silakan lakukan pembayaran sesuai dengan metode yang dipilih.</p>
                    
                    <div class="d-flex flex-column flex-md-row justify-content-center gap-3">
                        <a href="index.php" class="btn btn-outline-secondary px-4">
                            <i class="fas fa-home me-2"></i> Kembali ke Beranda
                        </a>
                        <a href="profile.php?tab=orders" class="btn btn-primary px-4">
                            <i class="fas fa-list-ul me-2"></i> Lihat Pesanan Saya
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body">
                    <h4 class="mb-3">Langkah Selanjutnya</h4>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="d-flex">
                                <div class="me-3">
                                    <span class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">1</span>
                                </div>
                                <div>
                                    <h5>Lakukan Pembayaran</h5>
                                    <p class="text-muted">Selesaikan pembayaran sesuai metode yang dipilih dalam waktu 24 jam.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex">
                                <div class="me-3">
                                    <span class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">2</span>
                                </div>
                                <div>
                                    <h5>Konfirmasi Pembayaran</h5>
                                    <p class="text-muted">Kirimkan bukti pembayaran melalui WhatsApp atau email kami.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex">
                                <div class="me-3">
                                    <span class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">3</span>
                                </div>
                                <div>
                                    <h5>Pesanan Diproses</h5>
                                    <p class="text-muted">Kami akan memproses pesanan Anda setelah pembayaran dikonfirmasi.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex">
                                <div class="me-3">
                                    <span class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">4</span>
                                </div>
                                <div>
                                    <h5>Pengiriman</h5>
                                    <p class="text-muted">Kami akan mengirimkan pesanan Anda dan memberikan nomor resi.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body">
                    <h4 class="mb-3">Butuh Bantuan?</h4>
                    <p>Jika Anda memiliki pertanyaan atau kendala tentang pesanan Anda, jangan ragu untuk menghubungi kami:</p>
                    <div class="d-flex flex-column flex-md-row gap-3 mt-3">
                        <a href="https://wa.me/628123456789" class="btn btn-outline-success" target="_blank">
                            <i class="fab fa-whatsapp me-2"></i> WhatsApp
                        </a>
                        <a href="mailto:cs@pustakanusa.com" class="btn btn-outline-primary">
                            <i class="fas fa-envelope me-2"></i> Email
                        </a>
                        <a href="tel:+628123456789" class="btn btn-outline-secondary">
                            <i class="fas fa-phone me-2"></i> Telepon
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Hapus data pesanan dari session setelah halaman terload
// Tetapi simpan untuk keperluan tampilan di halaman ini
if (isset($_SESSION['order_number'])) {
    unset($_SESSION['order_number']);
}
if (isset($_SESSION['order_total'])) {
    unset($_SESSION['order_total']);
}

include_once 'templates/footer.php';
?> 