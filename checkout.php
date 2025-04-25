<?php
// Inisialisasi session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect jika keranjang kosong
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

// Include database connection
require_once 'config/database.php';

// Fungsi format Rupiah
function formatRupiah($price)
{
    return 'Rp ' . number_format($price, 0, ',', '.');
}

// Hitung total belanja
function calculateCartTotal()
{
    $total = 0;
    
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'] * $item['qty'];
        }
    }
    
    return $total;
}

// Proses checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi input
    $errors = [];
    
    if (empty($_POST['name'])) {
        $errors['name'] = 'Nama lengkap wajib diisi';
    }
    
    if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Email tidak valid';
    }
    
    if (empty($_POST['phone']) || !preg_match('/^[0-9]{10,15}$/', $_POST['phone'])) {
        $errors['phone'] = 'Nomor telepon tidak valid';
    }
    
    if (empty($_POST['address'])) {
        $errors['address'] = 'Alamat wajib diisi';
    }
    
    if (empty($_POST['city'])) {
        $errors['city'] = 'Kota wajib diisi';
    }
    
    if (empty($_POST['postal_code']) || !preg_match('/^[0-9]{5}$/', $_POST['postal_code'])) {
        $errors['postal_code'] = 'Kode pos tidak valid';
    }
    
    if (empty($_POST['payment_method'])) {
        $errors['payment_method'] = 'Metode pembayaran wajib dipilih';
    }
    
    // Jika tidak ada error, proses pesanan
    if (empty($errors)) {
        // 1. Simpan data pesanan ke tabel orders
        $total_amount = calculateCartTotal();
        $shipping_cost = 0; // Untuk saat ini, ongkir = 0
        $total_price = $total_amount + $shipping_cost;
        $order_number = 'ORD-' . date('YmdHis') . '-' . rand(100, 999);
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : NULL;
        
        $sql = "INSERT INTO orders (order_number, user_id, customer_name, customer_email, customer_phone, 
                shipping_address, shipping_city, shipping_postal_code, payment_method, total_amount, 
                shipping_cost, total_price, order_status, order_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sissssssidds", 
            $order_number, 
            $user_id, 
            $_POST['name'], 
            $_POST['email'], 
            $_POST['phone'], 
            $_POST['address'], 
            $_POST['city'], 
            $_POST['postal_code'], 
            $_POST['payment_method'], 
            $total_amount, 
            $shipping_cost, 
            $total_price
        );
        
        if ($stmt->execute()) {
            $order_id = $conn->insert_id;
            
            // 2. Simpan detail pesanan ke tabel order_items
            $item_sql = "INSERT INTO order_items (order_id, book_id, title, price, quantity, subtotal) 
                          VALUES (?, ?, ?, ?, ?, ?)";
            $item_stmt = $conn->prepare($item_sql);
            
            foreach ($_SESSION['cart'] as $book_id => $item) {
                $subtotal = $item['price'] * $item['qty'];
                $item_stmt->bind_param("iisdid", 
                    $order_id, 
                    $book_id, 
                    $item['title'], 
                    $item['price'], 
                    $item['qty'], 
                    $subtotal
                );
                $item_stmt->execute();
                
                // 3. Update stok buku
                $update_stock = "UPDATE books SET stock = stock - ? WHERE id = ?";
                $stock_stmt = $conn->prepare($update_stock);
                $stock_stmt->bind_param("ii", $item['qty'], $book_id);
                $stock_stmt->execute();
            }
            
            // 4. Kosongkan keranjang belanja
            $_SESSION['cart'] = [];
            
            // 5. Redirect ke halaman terima kasih
            $_SESSION['order_number'] = $order_number;
            $_SESSION['order_total'] = $total_price;
            
            header('Location: order-success.php');
            exit;
        } else {
            $checkout_error = "Terjadi kesalahan saat memproses pesanan. Silakan coba lagi.";
        }
    }
}

// Include header
include_once 'templates/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8">
            <h1 class="mb-4">Checkout</h1>
            
            <?php if (isset($checkout_error)): ?>
            <div class="alert alert-danger mb-4">
                <?= $checkout_error ?>
            </div>
            <?php endif; ?>
            
            <form method="post" id="checkout-form">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">Informasi Pengiriman</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                                       id="name" name="name" value="<?= $_POST['name'] ?? '' ?>">
                                <?php if (isset($errors['name'])): ?>
                                <div class="invalid-feedback"><?= $errors['name'] ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                                       id="email" name="email" value="<?= $_POST['email'] ?? '' ?>">
                                <?php if (isset($errors['email'])): ?>
                                <div class="invalid-feedback"><?= $errors['email'] ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Nomor Telepon <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" 
                                       id="phone" name="phone" value="<?= $_POST['phone'] ?? '' ?>">
                                <?php if (isset($errors['phone'])): ?>
                                <div class="invalid-feedback"><?= $errors['phone'] ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="postal_code" class="form-label">Kode Pos <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?= isset($errors['postal_code']) ? 'is-invalid' : '' ?>" 
                                       id="postal_code" name="postal_code" maxlength="5" value="<?= $_POST['postal_code'] ?? '' ?>">
                                <?php if (isset($errors['postal_code'])): ?>
                                <div class="invalid-feedback"><?= $errors['postal_code'] ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="city" class="form-label">Kota <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?= isset($errors['city']) ? 'is-invalid' : '' ?>" 
                                       id="city" name="city" value="<?= $_POST['city'] ?? '' ?>">
                                <?php if (isset($errors['city'])): ?>
                                <div class="invalid-feedback"><?= $errors['city'] ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="province" class="form-label">Provinsi <span class="text-danger">*</span></label>
                                <select class="form-select" id="province" name="province">
                                    <option value="">Pilih Provinsi</option>
                                    <option value="Aceh">Aceh</option>
                                    <option value="Sumatera Utara">Sumatera Utara</option>
                                    <option value="Sumatera Barat">Sumatera Barat</option>
                                    <option value="Riau">Riau</option>
                                    <option value="Jambi">Jambi</option>
                                    <option value="Sumatera Selatan">Sumatera Selatan</option>
                                    <option value="Bengkulu">Bengkulu</option>
                                    <option value="Lampung">Lampung</option>
                                    <option value="Kepulauan Bangka Belitung">Kepulauan Bangka Belitung</option>
                                    <option value="Kepulauan Riau">Kepulauan Riau</option>
                                    <option value="DKI Jakarta">DKI Jakarta</option>
                                    <option value="Jawa Barat">Jawa Barat</option>
                                    <option value="Jawa Tengah">Jawa Tengah</option>
                                    <option value="DI Yogyakarta">DI Yogyakarta</option>
                                    <option value="Jawa Timur">Jawa Timur</option>
                                    <option value="Banten">Banten</option>
                                    <option value="Bali">Bali</option>
                                </select>
                            </div>
                            
                            <div class="col-12">
                                <label for="address" class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                                <textarea class="form-control <?= isset($errors['address']) ? 'is-invalid' : '' ?>" 
                                          id="address" name="address" rows="3"><?= $_POST['address'] ?? '' ?></textarea>
                                <?php if (isset($errors['address'])): ?>
                                <div class="invalid-feedback"><?= $errors['address'] ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-12">
                                <label for="order_notes" class="form-label">Catatan Pesanan (opsional)</label>
                                <textarea class="form-control" id="order_notes" name="order_notes" rows="2"><?= $_POST['order_notes'] ?? '' ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">Metode Pembayaran</h5>
                    </div>
                    <div class="card-body">
                        <div class="payment-methods">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="payment_method" id="bank_transfer" value="bank_transfer" 
                                       <?= (!isset($_POST['payment_method']) || $_POST['payment_method'] == 'bank_transfer') ? 'checked' : '' ?>>
                                <label class="form-check-label d-flex align-items-center" for="bank_transfer">
                                    <span class="me-3">Transfer Bank</span>
                                    <div class="payment-icons">
                                        <img src="assets/images/payment/bca.png" alt="BCA" height="24" class="me-2">
                                        <img src="assets/images/payment/mandiri.png" alt="Mandiri" height="24" class="me-2">
                                        <img src="assets/images/payment/bni.png" alt="BNI" height="24">
                                    </div>
                                </label>
                                <div class="ms-4 mt-2 mb-4 bank-details" id="bank_transfer_details">
                                    <p class="mb-2">Silakan transfer ke salah satu rekening berikut:</p>
                                    <div class="card mb-2">
                                        <div class="card-body p-3">
                                            <p class="mb-1"><strong>Bank BCA</strong></p>
                                            <p class="mb-1">No. Rekening: 1234567890</p>
                                            <p class="mb-0">Atas Nama: PT Pustaka Nusa Indonesia</p>
                                        </div>
                                    </div>
                                    <div class="card mb-2">
                                        <div class="card-body p-3">
                                            <p class="mb-1"><strong>Bank Mandiri</strong></p>
                                            <p class="mb-1">No. Rekening: 0987654321</p>
                                            <p class="mb-0">Atas Nama: PT Pustaka Nusa Indonesia</p>
                                        </div>
                                    </div>
                                    <div class="card">
                                        <div class="card-body p-3">
                                            <p class="mb-1"><strong>Bank BNI</strong></p>
                                            <p class="mb-1">No. Rekening: 1357924680</p>
                                            <p class="mb-0">Atas Nama: PT Pustaka Nusa Indonesia</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="payment_method" id="ewallet" value="ewallet" 
                                       <?= (isset($_POST['payment_method']) && $_POST['payment_method'] == 'ewallet') ? 'checked' : '' ?>>
                                <label class="form-check-label d-flex align-items-center" for="ewallet">
                                    <span class="me-3">E-Wallet</span>
                                    <div class="payment-icons">
                                        <img src="assets/images/payment/gopay.png" alt="GoPay" height="24" class="me-2">
                                        <img src="assets/images/payment/ovo.png" alt="OVO" height="24" class="me-2">
                                        <img src="assets/images/payment/dana.png" alt="DANA" height="24">
                                    </div>
                                </label>
                                <div class="ms-4 mt-2 mb-3 payment-details" id="ewallet_details" style="display:none">
                                    <p class="mb-2">Silakan pilih e-wallet yang ingin Anda gunakan:</p>
                                    <select class="form-select" name="ewallet_type">
                                        <option value="gopay">GoPay</option>
                                        <option value="ovo">OVO</option>
                                        <option value="dana">DANA</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="payment_method" id="cod" value="cod" 
                                       <?= (isset($_POST['payment_method']) && $_POST['payment_method'] == 'cod') ? 'checked' : '' ?>>
                                <label class="form-check-label d-flex align-items-center" for="cod">
                                    <span>Bayar di Tempat (COD)</span>
                                </label>
                                <div class="ms-4 mt-2 payment-details" id="cod_details" style="display:none">
                                    <p class="mb-0">Pembayaran dilakukan saat barang diterima. <br>Hanya tersedia untuk area tertentu.</p>
                                </div>
                            </div>
                            
                            <?php if (isset($errors['payment_method'])): ?>
                            <div class="text-danger mb-3"><?= $errors['payment_method'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between mt-4">
                    <a href="cart.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i> Kembali ke Keranjang
                    </a>
                    <button type="submit" class="btn btn-primary">
                        Buat Pesanan <i class="fas fa-arrow-right ms-2"></i>
                    </button>
                </div>
            </form>
        </div>
        
        <div class="col-lg-4 mt-4 mt-lg-0">
            <div class="card shadow-sm mb-4 sticky-top" style="top: 20px; z-index: 1000;">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Ringkasan Pesanan</h5>
                </div>
                <div class="card-body">
                    <div class="order-items mb-3">
                        <?php foreach ($_SESSION['cart'] as $bookId => $item): ?>
                        <div class="d-flex mb-3">
                            <img src="<?= $item['cover_image'] ?>" class="rounded" style="width: 50px; height: 70px; object-fit: cover;" alt="<?= htmlspecialchars($item['title']) ?>">
                            <div class="ms-3">
                                <h6 class="mb-0 fs-6"><?= htmlspecialchars($item['title']) ?></h6>
                                <p class="mb-0 small text-muted"><?= $item['qty'] ?> x <?= formatRupiah($item['price']) ?></p>
                                <p class="mb-0 text-primary"><?= formatRupiah($item['price'] * $item['qty']) ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal</span>
                        <span><?= formatRupiah(calculateCartTotal()) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Biaya Pengiriman</span>
                        <span>Gratis</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Diskon</span>
                        <span>-</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-0">
                        <span class="fw-bold">Total</span>
                        <span class="fw-bold"><?= formatRupiah(calculateCartTotal()) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Menampilkan detail pembayaran saat metode pembayaran dipilih
    const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
    const paymentDetails = document.querySelectorAll('.payment-details');
    
    paymentMethods.forEach(method => {
        method.addEventListener('change', function() {
            // Sembunyikan semua detail pembayaran
            paymentDetails.forEach(detail => {
                detail.style.display = 'none';
            });
            
            // Tampilkan detail pembayaran yang sesuai
            const selectedMethodDetails = document.getElementById(`${this.id}_details`);
            if (selectedMethodDetails && this.id !== 'bank_transfer') {
                selectedMethodDetails.style.display = 'block';
            }
        });
    });
    
    // Tampilkan detail metode pembayaran yang aktif saat halaman dimuat
    const activeMethod = document.querySelector('input[name="payment_method"]:checked');
    if (activeMethod && activeMethod.id !== 'bank_transfer') {
        const activeDetails = document.getElementById(`${activeMethod.id}_details`);
        if (activeDetails) {
            activeDetails.style.display = 'block';
        }
    }
    
    // Validasi form sebelum submit
    const checkoutForm = document.getElementById('checkout-form');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(event) {
            let isValid = true;
            
            // Validasi nama
            const nameInput = document.getElementById('name');
            if (!nameInput.value.trim()) {
                isValid = false;
                nameInput.classList.add('is-invalid');
            } else {
                nameInput.classList.remove('is-invalid');
            }
            
            // Validasi email
            const emailInput = document.getElementById('email');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailInput.value.trim() || !emailRegex.test(emailInput.value.trim())) {
                isValid = false;
                emailInput.classList.add('is-invalid');
            } else {
                emailInput.classList.remove('is-invalid');
            }
            
            // Validasi nomor telepon
            const phoneInput = document.getElementById('phone');
            const phoneRegex = /^[0-9]{10,15}$/;
            if (!phoneInput.value.trim() || !phoneRegex.test(phoneInput.value.trim())) {
                isValid = false;
                phoneInput.classList.add('is-invalid');
            } else {
                phoneInput.classList.remove('is-invalid');
            }
            
            // Validasi alamat
            const addressInput = document.getElementById('address');
            if (!addressInput.value.trim()) {
                isValid = false;
                addressInput.classList.add('is-invalid');
            } else {
                addressInput.classList.remove('is-invalid');
            }
            
            // Validasi kota
            const cityInput = document.getElementById('city');
            if (!cityInput.value.trim()) {
                isValid = false;
                cityInput.classList.add('is-invalid');
            } else {
                cityInput.classList.remove('is-invalid');
            }
            
            // Validasi kode pos
            const postalCodeInput = document.getElementById('postal_code');
            const postalCodeRegex = /^[0-9]{5}$/;
            if (!postalCodeInput.value.trim() || !postalCodeRegex.test(postalCodeInput.value.trim())) {
                isValid = false;
                postalCodeInput.classList.add('is-invalid');
            } else {
                postalCodeInput.classList.remove('is-invalid');
            }
            
            if (!isValid) {
                event.preventDefault();
                // Scroll ke elemen invalid pertama
                const firstInvalidElement = document.querySelector('.is-invalid');
                if (firstInvalidElement) {
                    firstInvalidElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            } else {
                // Tampilkan loading pada tombol submit
                const submitButton = this.querySelector('button[type="submit"]');
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Memproses...';
                submitButton.disabled = true;
            }
        });
    }
});
</script>

<?php include_once 'templates/footer.php'; ?> 