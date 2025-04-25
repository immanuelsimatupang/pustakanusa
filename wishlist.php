<?php
// Inisialisasi session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=wishlist.php");
    exit;
}

// Include database connection
require_once 'config/database.php';

// Fungsi format Rupiah
function formatRupiah($price)
{
    return 'Rp ' . number_format($price, 0, ',', '.');
}

// Proses AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];
    
    // Add to wishlist
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        if (isset($_POST['book_id']) && is_numeric($_POST['book_id'])) {
            $book_id = (int) $_POST['book_id'];
            $user_id = $_SESSION['user_id'];
            
            // Cek apakah buku sudah ada di wishlist
            $check_sql = "SELECT id FROM wishlists WHERE user_id = ? AND book_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("ii", $user_id, $book_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $response['message'] = 'Buku sudah ada di daftar keinginan Anda.';
            } else {
                // Tambahkan ke wishlist
                $insert_sql = "INSERT INTO wishlists (user_id, book_id) VALUES (?, ?)";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->bind_param("ii", $user_id, $book_id);
                
                if ($insert_stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Buku berhasil ditambahkan ke daftar keinginan.';
                    
                    // Get wishlist count
                    $count_sql = "SELECT COUNT(*) as count FROM wishlists WHERE user_id = ?";
                    $count_stmt = $conn->prepare($count_sql);
                    $count_stmt->bind_param("i", $user_id);
                    $count_stmt->execute();
                    $count_result = $count_stmt->get_result();
                    $count_row = $count_result->fetch_assoc();
                    
                    $response['wishlist_count'] = $count_row['count'];
                } else {
                    $response['message'] = 'Gagal menambahkan buku ke daftar keinginan.';
                }
            }
        } else {
            $response['message'] = 'ID Buku tidak valid.';
        }
    }
    
    // Remove from wishlist
    if (isset($_POST['action']) && $_POST['action'] === 'remove') {
        if (isset($_POST['book_id']) && is_numeric($_POST['book_id'])) {
            $book_id = (int) $_POST['book_id'];
            $user_id = $_SESSION['user_id'];
            
            $delete_sql = "DELETE FROM wishlists WHERE user_id = ? AND book_id = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("ii", $user_id, $book_id);
            
            if ($delete_stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Buku berhasil dihapus dari daftar keinginan.';
                
                // Get wishlist count
                $count_sql = "SELECT COUNT(*) as count FROM wishlists WHERE user_id = ?";
                $count_stmt = $conn->prepare($count_sql);
                $count_stmt->bind_param("i", $user_id);
                $count_stmt->execute();
                $count_result = $count_stmt->get_result();
                $count_row = $count_result->fetch_assoc();
                
                $response['wishlist_count'] = $count_row['count'];
            } else {
                $response['message'] = 'Gagal menghapus buku dari daftar keinginan.';
            }
        } else {
            $response['message'] = 'ID Buku tidak valid.';
        }
    }
    
    // Move to cart
    if (isset($_POST['action']) && $_POST['action'] === 'move_to_cart') {
        if (isset($_POST['book_id']) && is_numeric($_POST['book_id'])) {
            $book_id = (int) $_POST['book_id'];
            $user_id = $_SESSION['user_id'];
            
            // Dapatkan data buku
            $book_sql = "SELECT id, title, price, discount_price, cover_image, stock FROM books WHERE id = ?";
            $book_stmt = $conn->prepare($book_sql);
            $book_stmt->bind_param("i", $book_id);
            $book_stmt->execute();
            $book_result = $book_stmt->get_result();
            
            if ($book_result->num_rows > 0) {
                $book = $book_result->fetch_assoc();
                
                // Cek stok
                if ($book['stock'] > 0) {
                    // Inisialisasi keranjang jika belum ada
                    if (!isset($_SESSION['cart'])) {
                        $_SESSION['cart'] = [];
                    }
                    
                    // Set harga
                    $price = $book['discount_price'] > 0 ? $book['discount_price'] : $book['price'];
                    
                    // Tambahkan atau update item di keranjang
                    if (isset($_SESSION['cart'][$book_id])) {
                        $_SESSION['cart'][$book_id]['qty'] += 1;
                    } else {
                        $_SESSION['cart'][$book_id] = [
                            'title' => $book['title'],
                            'price' => $price,
                            'qty' => 1,
                            'cover_image' => $book['cover_image']
                        ];
                    }
                    
                    // Hapus dari wishlist
                    $delete_sql = "DELETE FROM wishlists WHERE user_id = ? AND book_id = ?";
                    $delete_stmt = $conn->prepare($delete_sql);
                    $delete_stmt->bind_param("ii", $user_id, $book_id);
                    $delete_stmt->execute();
                    
                    $response['success'] = true;
                    $response['message'] = 'Buku berhasil dipindahkan ke keranjang.';
                    
                    // Get wishlist count
                    $count_sql = "SELECT COUNT(*) as count FROM wishlists WHERE user_id = ?";
                    $count_stmt = $conn->prepare($count_sql);
                    $count_stmt->bind_param("i", $user_id);
                    $count_stmt->execute();
                    $count_result = $count_stmt->get_result();
                    $count_row = $count_result->fetch_assoc();
                    
                    $response['wishlist_count'] = $count_row['count'];
                    
                    // Get cart count
                    $cart_count = 0;
                    foreach ($_SESSION['cart'] as $item) {
                        $cart_count += $item['qty'];
                    }
                    $response['cart_count'] = $cart_count;
                } else {
                    $response['message'] = 'Maaf, stok buku tidak tersedia.';
                }
            } else {
                $response['message'] = 'Buku tidak ditemukan.';
            }
        } else {
            $response['message'] = 'ID Buku tidak valid.';
        }
    }
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Get wishlist items
$user_id = $_SESSION['user_id'];
$wishlist_items = [];

$sql = "SELECT w.*, b.title, b.price, b.discount_price, b.cover_image, b.stock, b.slug 
        FROM wishlists w 
        JOIN books b ON w.book_id = b.id 
        WHERE w.user_id = ? 
        ORDER BY w.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $row['price_display'] = $row['discount_price'] > 0 ? $row['discount_price'] : $row['price'];
    $wishlist_items[] = $row;
}

// Include header
include_once 'templates/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <h1 class="mb-4">Daftar Keinginan</h1>
            
            <?php if (empty($wishlist_items)): ?>
            <div class="card shadow-sm">
                <div class="card-body py-5 text-center">
                    <div class="mb-4">
                        <i class="fas fa-heart text-muted" style="font-size: 4rem;"></i>
                    </div>
                    <h4>Daftar Keinginan Anda Kosong</h4>
                    <p class="text-muted mb-4">Jelajahi buku-buku kami dan tambahkan ke daftar keinginan Anda</p>
                    <a href="books.php" class="btn btn-primary">Jelajahi Buku</a>
                </div>
            </div>
            <?php else: ?>
            <div class="card shadow-sm mb-4">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Buku</th>
                                    <th class="text-center">Harga</th>
                                    <th class="text-center">Stok</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($wishlist_items as $item): ?>
                                <tr class="wishlist-item" data-book-id="<?= $item['book_id'] ?>">
                                    <td>
                                        <div class="d-flex align-items-center py-2">
                                            <img src="<?= $item['cover_image'] ?>" class="rounded me-3" style="width: 60px; height: 80px; object-fit: cover;" alt="<?= htmlspecialchars($item['title']) ?>">
                                            <div>
                                                <h6 class="mb-1"><?= htmlspecialchars($item['title']) ?></h6>
                                                <a href="book-detail.php?slug=<?= $item['slug'] ?>" class="text-decoration-none small">Lihat Detail</a>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center align-middle">
                                        <?php if ($item['discount_price'] > 0): ?>
                                        <p class="mb-0 text-primary"><?= formatRupiah($item['discount_price']) ?></p>
                                        <small class="text-decoration-line-through text-muted"><?= formatRupiah($item['price']) ?></small>
                                        <?php else: ?>
                                        <p class="mb-0 text-primary"><?= formatRupiah($item['price']) ?></p>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center align-middle">
                                        <?php if ($item['stock'] > 0): ?>
                                        <span class="badge bg-success">Tersedia</span>
                                        <?php else: ?>
                                        <span class="badge bg-danger">Habis</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center align-middle">
                                        <div class="btn-group btn-group-sm">
                                            <?php if ($item['stock'] > 0): ?>
                                            <button type="button" class="btn btn-outline-primary move-to-cart-btn" title="Tambahkan ke Keranjang">
                                                <i class="fas fa-shopping-cart"></i>
                                            </button>
                                            <?php else: ?>
                                            <button type="button" class="btn btn-outline-primary" title="Stok Habis" disabled>
                                                <i class="fas fa-shopping-cart"></i>
                                            </button>
                                            <?php endif; ?>
                                            <button type="button" class="btn btn-outline-danger remove-wishlist-btn" title="Hapus dari Daftar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-between">
                <a href="books.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i> Lanjut Belanja
                </a>
                <a href="cart.php" class="btn btn-primary">
                    Lihat Keranjang <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Menangani klik tombol hapus dari wishlist
    const removeButtons = document.querySelectorAll('.remove-wishlist-btn');
    removeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const wishlistItem = this.closest('.wishlist-item');
            const bookId = wishlistItem.dataset.bookId;
            
            if (confirm('Apakah Anda yakin ingin menghapus buku ini dari daftar keinginan?')) {
                removeFromWishlist(bookId, wishlistItem);
            }
        });
    });
    
    // Menangani klik tombol pindahkan ke keranjang
    const moveToCartButtons = document.querySelectorAll('.move-to-cart-btn');
    moveToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            const wishlistItem = this.closest('.wishlist-item');
            const bookId = wishlistItem.dataset.bookId;
            moveToCart(bookId, wishlistItem);
        });
    });
    
    // Fungsi untuk menghapus item dari wishlist
    function removeFromWishlist(bookId, wishlistItem) {
        fetch('wishlist.php', {
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
                wishlistItem.remove();
                
                // Update counter wishlist di header
                const wishlistCounter = document.getElementById('wishlist-counter');
                if (wishlistCounter) {
                    wishlistCounter.textContent = data.wishlist_count;
                    
                    if (data.wishlist_count > 0) {
                        wishlistCounter.classList.remove('d-none');
                    } else {
                        wishlistCounter.classList.add('d-none');
                        // Reload halaman jika wishlist kosong
                        location.reload();
                    }
                }
                
                showNotification(data.message, 'success');
            } else {
                showNotification(data.message || 'Gagal menghapus item dari daftar keinginan', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Terjadi kesalahan saat menghapus item', 'danger');
        });
    }
    
    // Fungsi untuk memindahkan item ke keranjang
    function moveToCart(bookId, wishlistItem) {
        fetch('wishlist.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=move_to_cart&book_id=${bookId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Hapus item dari UI
                wishlistItem.remove();
                
                // Update counter wishlist di header
                const wishlistCounter = document.getElementById('wishlist-counter');
                if (wishlistCounter) {
                    wishlistCounter.textContent = data.wishlist_count;
                    
                    if (data.wishlist_count > 0) {
                        wishlistCounter.classList.remove('d-none');
                    } else {
                        wishlistCounter.classList.add('d-none');
                        // Reload halaman jika wishlist kosong
                        location.reload();
                    }
                }
                
                // Update counter keranjang di header
                const cartCounter = document.getElementById('cart-counter');
                if (cartCounter) {
                    cartCounter.textContent = data.cart_count;
                    
                    if (data.cart_count > 0) {
                        cartCounter.classList.remove('d-none');
                    }
                }
                
                showNotification(data.message, 'success');
            } else {
                showNotification(data.message || 'Gagal memindahkan item ke keranjang', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Terjadi kesalahan saat memindahkan item ke keranjang', 'danger');
        });
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