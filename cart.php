<?php
// Inisialisasi session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inisialisasi keranjang
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Fungsi untuk ambil data buku berdasarkan ID
function getBookById($id)
{
    // Koneksi ke database
    require_once 'config/database.php';
    
    // Query untuk mengambil data buku
    $sql = "SELECT id, title, author, price, cover_image, stock FROM books WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $book = $result->fetch_assoc();
        // Tambahkan path lengkap ke gambar cover
        $book['cover_image'] = 'uploads/covers/' . $book['cover_image'];
        return $book;
    }
    
    return null;
}

// Handle request AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'add') {
        // Validasi input
        if (!isset($_POST['book_id']) || !is_numeric($_POST['book_id'])) {
            echo json_encode([
                'success' => false,
                'message' => 'ID Buku tidak valid'
            ]);
            exit;
        }
        
        $bookId = (int)$_POST['book_id'];
        $qty = isset($_POST['qty']) && is_numeric($_POST['qty']) ? (int)$_POST['qty'] : 1;
        
        // Validasi qty
        if ($qty <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Jumlah harus lebih dari 0'
            ]);
            exit;
        }
        
        // Dapatkan informasi buku
        $book = getBookById($bookId);
        
        if (!$book) {
            echo json_encode([
                'success' => false,
                'message' => 'Buku tidak ditemukan'
            ]);
            exit;
        }
        
        // Cek stok buku
        if ($book['stock'] <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Maaf, stok buku habis'
            ]);
            exit;
        }
        
        // Cek apakah buku sudah ada di keranjang
        if (isset($_SESSION['cart'][$bookId])) {
            // Update jumlah
            $_SESSION['cart'][$bookId]['qty'] += $qty;
            
            // Batasi dengan stok yang tersedia
            if ($_SESSION['cart'][$bookId]['qty'] > $book['stock']) {
                $_SESSION['cart'][$bookId]['qty'] = $book['stock'];
            }
        } else {
            // Tambahkan buku baru ke keranjang
            $_SESSION['cart'][$bookId] = [
                'id' => $book['id'],
                'title' => $book['title'],
                'author' => $book['author'],
                'price' => $book['price'],
                'cover_image' => $book['cover_image'],
                'qty' => $qty
            ];
        }
        
        // Hitung total item di keranjang
        $cartCount = 0;
        foreach ($_SESSION['cart'] as $item) {
            $cartCount += $item['qty'];
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Buku telah ditambahkan ke keranjang',
            'cart_count' => $cartCount
        ]);
        exit;
    }
    
    if ($_POST['action'] === 'update') {
        if (!isset($_POST['book_id']) || !isset($_POST['qty']) || !is_numeric($_POST['book_id']) || !is_numeric($_POST['qty'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Parameter tidak valid'
            ]);
            exit;
        }
        
        $bookId = (int)$_POST['book_id'];
        $qty = (int)$_POST['qty'];
        
        // Validasi qty
        if ($qty <= 0) {
            // Jika qty 0 atau negatif, hapus dari keranjang
            if (isset($_SESSION['cart'][$bookId])) {
                unset($_SESSION['cart'][$bookId]);
            }
        } else {
            // Update qty jika buku ada di keranjang
            if (isset($_SESSION['cart'][$bookId])) {
                // Dapatkan informasi stok buku
                $book = getBookById($bookId);
                
                if ($book && $book['stock'] > 0) {
                    // Batasi dengan stok yang tersedia
                    $_SESSION['cart'][$bookId]['qty'] = min($qty, $book['stock']);
                } else {
                    $_SESSION['cart'][$bookId]['qty'] = $qty;
                }
            }
        }
        
        // Hitung total item dan total harga
        $cartCount = 0;
        $cartTotal = 0;
        
        foreach ($_SESSION['cart'] as $item) {
            $cartCount += $item['qty'];
            $cartTotal += $item['price'] * $item['qty'];
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Keranjang berhasil diperbarui',
            'cart_count' => $cartCount,
            'cart_total' => formatRupiah($cartTotal),
            'raw_total' => $cartTotal
        ]);
        exit;
    }
    
    if ($_POST['action'] === 'remove') {
        if (!isset($_POST['book_id']) || !is_numeric($_POST['book_id'])) {
            echo json_encode([
                'success' => false,
                'message' => 'ID Buku tidak valid'
            ]);
            exit;
        }
        
        $bookId = (int)$_POST['book_id'];
        
        // Hapus buku dari keranjang
        if (isset($_SESSION['cart'][$bookId])) {
            unset($_SESSION['cart'][$bookId]);
        }
        
        // Hitung total item dan total harga
        $cartCount = 0;
        $cartTotal = 0;
        
        foreach ($_SESSION['cart'] as $item) {
            $cartCount += $item['qty'];
            $cartTotal += $item['price'] * $item['qty'];
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Buku telah dihapus dari keranjang',
            'cart_count' => $cartCount,
            'cart_total' => formatRupiah($cartTotal),
            'raw_total' => $cartTotal
        ]);
        exit;
    }
    
    if ($_POST['action'] === 'clear') {
        // Kosongkan keranjang
        $_SESSION['cart'] = [];
        
        echo json_encode([
            'success' => true,
            'message' => 'Keranjang telah dikosongkan',
            'cart_count' => 0,
            'cart_total' => formatRupiah(0),
            'raw_total' => 0
        ]);
        exit;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    if ($_GET['action'] === 'count') {
        // Hitung total item di keranjang
        $cartCount = 0;
        foreach ($_SESSION['cart'] as $item) {
            $cartCount += $item['qty'];
        }
        
        echo json_encode([
            'success' => true,
            'cart_count' => $cartCount
        ]);
        exit;
    }
}

// Fungsi untuk menghitung total biaya keranjang
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

// Fungsi untuk memformat harga ke format Rupiah
function formatRupiah($price)
{
    return 'Rp ' . number_format($price, 0, ',', '.');
}

// Jika bukan request AJAX, tampilkan halaman keranjang
include_once 'templates/header.php';
?>

<div class="container my-5">
    <h1 class="mb-4">Keranjang Belanja</h1>
    
    <?php if (empty($_SESSION['cart'])): ?>
    
    <div class="alert alert-info">
        <p class="mb-0">Keranjang belanja Anda masih kosong.</p>
    </div>
    
    <div class="text-center my-5">
        <img src="assets/images/empty-cart.svg" alt="Keranjang Kosong" class="img-fluid mb-4" style="max-width: 200px;">
        <h4>Keranjang Belanja Anda Kosong</h4>
        <p class="text-muted">Silakan tambahkan buku yang ingin Anda beli ke keranjang</p>
        <a href="index.php" class="btn btn-primary mt-3">
            <i class="fas fa-arrow-left me-2"></i> Lanjutkan Belanja
        </a>
    </div>
    
    <?php else: ?>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Item Keranjang</h5>
                        <button id="clear-cart" class="btn btn-sm btn-outline-danger">
                            <i class="fas fa-trash-alt me-1"></i> Kosongkan Keranjang
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <?php foreach ($_SESSION['cart'] as $bookId => $item): ?>
                    <div class="cart-item mb-3 pb-3 border-bottom" data-book-id="<?= $bookId ?>">
                        <div class="row align-items-center">
                            <div class="col-md-2 col-4 mb-2 mb-md-0">
                                <img src="<?= $item['cover_image'] ?>" class="img-fluid rounded" alt="<?= htmlspecialchars($item['title']) ?>">
                            </div>
                            <div class="col-md-5 col-8 mb-2 mb-md-0">
                                <h6 class="mb-1"><?= htmlspecialchars($item['title']) ?></h6>
                                <p class="text-muted small mb-1"><?= htmlspecialchars($item['author']) ?></p>
                                <p class="text-primary mb-0 fw-bold"><?= formatRupiah($item['price']) ?></p>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="input-group input-group-sm quantity-control">
                                    <button class="btn btn-outline-secondary decrease-qty" type="button">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" class="form-control text-center qty-input" value="<?= $item['qty'] ?>" min="1" max="99">
                                    <button class="btn btn-outline-secondary increase-qty" type="button">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-2 col-6 text-end">
                                <div class="d-flex flex-column align-items-end">
                                    <span class="fw-bold mb-2"><?= formatRupiah($item['price'] * $item['qty']) ?></span>
                                    <button class="btn btn-sm btn-link text-danger p-0 remove-item">
                                        <i class="fas fa-trash-alt me-1"></i> Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="d-flex justify-content-between">
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i> Lanjutkan Belanja
                </a>
                <a href="checkout.php" class="btn btn-primary">
                    Lanjut ke Pembayaran <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
        
        <div class="col-lg-4 mt-4 mt-lg-0">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Ringkasan Pesanan</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal</span>
                        <span id="cart-subtotal" class="fw-bold"><?= formatRupiah(calculateCartTotal()) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Biaya Pengiriman</span>
                        <span>-</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Pajak</span>
                        <span>-</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="fw-bold">Total</span>
                        <span id="cart-total" class="fw-bold"><?= formatRupiah(calculateCartTotal()) ?></span>
                    </div>
                    <a href="checkout.php" class="btn btn-primary w-100">Lanjut ke Pembayaran</a>
                </div>
            </div>
            
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Kode Promo</h5>
                </div>
                <div class="card-body">
                    <form id="promo-form">
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" placeholder="Masukkan kode promo">
                            <button class="btn btn-outline-secondary" type="submit">Terapkan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <?php endif; ?>
    
    <!-- Rekomendasi Buku Lainnya -->
    <div class="my-5">
        <h2 class="mb-4">Rekomendasi Untuk Anda</h2>
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 g-4">
            <?php
            // Ambil rekomendasi buku
            require_once 'config/database.php';
            $sql = "SELECT id, title, author, price, cover_image FROM books WHERE stock > 0 ORDER BY RAND() LIMIT 5";
            $result = $conn->query($sql);
            
            if ($result && $result->num_rows > 0) {
                while ($book = $result->fetch_assoc()) {
                    ?>
                    <div class="col">
                        <div class="card h-100 book-card">
                            <a href="book-detail.php?id=<?= $book['id'] ?>">
                                <img src="uploads/covers/<?= $book['cover_image'] ?>" class="card-img-top" alt="<?= htmlspecialchars($book['title']) ?>">
                            </a>
                            <div class="card-body">
                                <h6 class="card-title mb-1">
                                    <a href="book-detail.php?id=<?= $book['id'] ?>" class="text-decoration-none text-dark"><?= htmlspecialchars($book['title']) ?></a>
                                </h6>
                                <p class="card-text small text-muted mb-2"><?= htmlspecialchars($book['author']) ?></p>
                                <p class="card-text fw-bold text-primary mb-2"><?= formatRupiah($book['price']) ?></p>
                                <button class="btn btn-sm btn-outline-primary w-100 add-to-cart-btn" data-book-id="<?= $book['id'] ?>">
                                    <i class="fas fa-cart-plus me-1"></i> Tambah ke Keranjang
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            }
            ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Menghandle tombol hapus item
    const removeButtons = document.querySelectorAll('.remove-item');
    if (removeButtons.length > 0) {
        removeButtons.forEach(button => {
            button.addEventListener('click', function() {
                const cartItem = this.closest('.cart-item');
                const bookId = cartItem.dataset.bookId;
                
                if (confirm('Apakah Anda yakin ingin menghapus buku ini dari keranjang?')) {
                    removeFromCart(bookId, cartItem);
                }
            });
        });
    }
    
    // Menghandle tombol kosongkan keranjang
    const clearCartButton = document.getElementById('clear-cart');
    if (clearCartButton) {
        clearCartButton.addEventListener('click', function() {
            if (confirm('Apakah Anda yakin ingin mengosongkan keranjang belanja?')) {
                clearCart();
            }
        });
    }
    
    // Menghandle perubahan jumlah
    const qtyInputs = document.querySelectorAll('.qty-input');
    if (qtyInputs.length > 0) {
        qtyInputs.forEach(input => {
            // Update saat nilai berubah
            input.addEventListener('change', function() {
                const cartItem = this.closest('.cart-item');
                const bookId = cartItem.dataset.bookId;
                const qty = parseInt(this.value);
                
                if (qty <= 0) {
                    if (confirm('Apakah Anda yakin ingin menghapus buku ini dari keranjang?')) {
                        removeFromCart(bookId, cartItem);
                    } else {
                        this.value = 1;
                        updateCartItem(bookId, 1);
                    }
                } else {
                    updateCartItem(bookId, qty);
                }
            });
            
            // Batasi input hanya angka dan maksimal 2 digit
            input.addEventListener('input', function() {
                if (this.value.length > 2) {
                    this.value = this.value.slice(0, 2);
                }
            });
        });
        
        // Menghandle tombol kurangi jumlah
        const decreaseButtons = document.querySelectorAll('.decrease-qty');
        decreaseButtons.forEach(button => {
            button.addEventListener('click', function() {
                const input = this.closest('.quantity-control').querySelector('.qty-input');
                const cartItem = this.closest('.cart-item');
                const bookId = cartItem.dataset.bookId;
                let qty = parseInt(input.value) - 1;
                
                if (qty <= 0) {
                    if (confirm('Apakah Anda yakin ingin menghapus buku ini dari keranjang?')) {
                        removeFromCart(bookId, cartItem);
                    } else {
                        qty = 1;
                    }
                }
                
                input.value = qty;
                updateCartItem(bookId, qty);
            });
        });
        
        // Menghandle tombol tambah jumlah
        const increaseButtons = document.querySelectorAll('.increase-qty');
        increaseButtons.forEach(button => {
            button.addEventListener('click', function() {
                const input = this.closest('.quantity-control').querySelector('.qty-input');
                const cartItem = this.closest('.cart-item');
                const bookId = cartItem.dataset.bookId;
                let qty = parseInt(input.value) + 1;
                
                if (qty > 99) {
                    qty = 99;
                }
                
                input.value = qty;
                updateCartItem(bookId, qty);
            });
        });
    }
    
    // Fungsi untuk update item keranjang
    function updateCartItem(bookId, qty) {
        fetch('cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=update&book_id=${bookId}&qty=${qty}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update UI
                updateCartUI(data);
            } else {
                showNotification(data.message || 'Gagal memperbarui keranjang', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Terjadi kesalahan saat memperbarui keranjang', 'danger');
        });
    }
    
    // Fungsi untuk menghapus item dari keranjang
    function removeFromCart(bookId, cartItem) {
        fetch('cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=remove&book_id=${bookId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Hapus item dari UI
                cartItem.remove();
                
                // Update UI
                updateCartUI(data);
                
                // Jika keranjang kosong, reload halaman
                if (data.cart_count === 0) {
                    location.reload();
                }
                
                showNotification(data.message, 'success');
            } else {
                showNotification(data.message || 'Gagal menghapus item dari keranjang', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Terjadi kesalahan saat menghapus item', 'danger');
        });
    }
    
    // Fungsi untuk mengosongkan keranjang
    function clearCart() {
        fetch('cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=clear'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload halaman
                location.reload();
            } else {
                showNotification(data.message || 'Gagal mengosongkan keranjang', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Terjadi kesalahan saat mengosongkan keranjang', 'danger');
        });
    }
    
    // Fungsi untuk update UI keranjang
    function updateCartUI(data) {
        // Update harga subtotal dan total
        const subtotalElement = document.getElementById('cart-subtotal');
        const totalElement = document.getElementById('cart-total');
        
        if (subtotalElement && totalElement) {
            subtotalElement.textContent = data.cart_total;
            totalElement.textContent = data.cart_total;
        }
        
        // Update counter keranjang di header
        const cartCounter = document.getElementById('cart-counter');
        if (cartCounter) {
            cartCounter.textContent = data.cart_count;
            
            if (data.cart_count > 0) {
                cartCounter.classList.remove('d-none');
            } else {
                cartCounter.classList.add('d-none');
            }
        }
    }
    
    // Fungsi untuk menampilkan notifikasi
    function showNotification(message, type = 'success') {
        // Cek apakah sudah ada container untuk notifikasi
        let notifContainer = document.getElementById('notification-container');
        
        if (!notifContainer) {
            notifContainer = document.createElement('div');
            notifContainer.id = 'notification-container';
            notifContainer.className = 'position-fixed bottom-0 end-0 p-3';
            notifContainer.style.zIndex = '1080';
            document.body.appendChild(notifContainer);
        }
        
        // Buat toast notification
        const toastId = 'toast-' + Date.now();
        const toast = document.createElement('div');
        toast.id = toastId;
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;
        
        notifContainer.appendChild(toast);
        
        // Inisialisasi dan tampilkan toast
        const bsToast = new bootstrap.Toast(toast, {
            autohide: true,
            delay: 3000
        });
        bsToast.show();
        
        // Hapus toast dari DOM setelah tertutup
        toast.addEventListener('hidden.bs.toast', function() {
            notifContainer.removeChild(toast);
        });
    }
});
</script>

<?php include_once 'templates/footer.php'; ?> 