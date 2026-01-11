/**
 * PustakaNusa - Cart JS
 * Script untuk menangani operasi keranjang belanja
 */

document.addEventListener('DOMContentLoaded', function() {
    // Cache elemen keranjang
    const cartCountEl = document.querySelector('.cart-count');
    
    // Inisialisasi jumlah item keranjang
    updateCartCount();
    
    // Tambahkan event listener untuk tombol "Tambah ke Keranjang"
    const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');
    
    if (addToCartButtons.length > 0) {
        addToCartButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                const bookId = this.dataset.bookId;
                const qty = 1; // Default jumlah
                
                // Tambahkan ke keranjang
                addToCart(bookId, qty);
            });
        });
    }
    
    // Tambahkan event listener untuk tombol "Beli Sekarang"
    const buyNowButtons = document.querySelectorAll('.buy-now-btn');
    
    if (buyNowButtons.length > 0) {
        buyNowButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                const bookId = this.dataset.bookId;
                const qty = 1; // Default jumlah
                
                // Tambahkan ke keranjang dan redirect ke checkout
                buyNow(bookId, qty);
            });
        });
    }
    
    // Fungsi untuk beli sekarang (tambah ke keranjang dan redirect ke checkout)
    function buyNow(bookId, qty = 1) {
        // Animasi tombol
        const button = document.querySelector(`.buy-now-btn[data-book-id="${bookId}"]`);
        if (button) {
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Memproses...';
            button.disabled = true;
        }
        
        // Kirim request AJAX ke server
        fetch('cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=add&book_id=${bookId}&qty=${qty}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update jumlah item di keranjang
                updateCartCountFromData(data.cart_count);
                
                // Simpan ke localStorage
                saveCartToLocalStorage(bookId, qty);
                
                // Redirect ke halaman checkout
                window.location.href = 'checkout.php';
            } else {
                // Tampilkan notifikasi error
                showNotification(data.message || 'Gagal menambahkan produk ke keranjang', 'danger');
                
                // Reset tombol
                if (button) {
                    button.innerHTML = '<i class="fas fa-shopping-cart me-2"></i> Beli Sekarang';
                    button.disabled = false;
                }
            }
        })
        .catch(error => {
            // Sembunyikan loading state
            hideLoadingState();
            
            // Tampilkan notifikasi error
            console.error('Error:', error);
            showNotification('Terjadi kesalahan saat menambahkan produk ke keranjang', 'danger');
            
            // Reset tombol
            if (button) {
                button.innerHTML = '<i class="fas fa-shopping-cart me-2"></i> Beli Sekarang';
                button.disabled = false;
            }
        });
    }
    
    // Fungsi untuk menambahkan produk ke keranjang
    function addToCart(bookId, qty = 1) {
        // Animasi tombol
        const button = document.querySelector(`.add-to-cart-btn[data-book-id="${bookId}"]`);
        if (button) {
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            button.disabled = true;
        }
        
        // Cek jumlah di localStorage untuk validasi stok
        const currentQty = getCartItemQtyFromLocalStorage(bookId);
        
        // Kirim request AJAX ke server
        fetch('cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=add&book_id=${bookId}&qty=${qty}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update jumlah item di keranjang
                updateCartCountFromData(data.cart_count);
                
                // Simpan ke localStorage
                saveCartToLocalStorage(bookId, qty);
                
                // Tampilkan notifikasi
                showNotification(data.message, 'success');
                
                // Reset tombol
                if (button) {
                    setTimeout(() => {
                        button.innerHTML = '<i class="fas fa-check"></i>';
                        
                        setTimeout(() => {
                            button.innerHTML = '<i class="fas fa-cart-plus"></i>';
                            button.disabled = false;
                        }, 1000);
                    }, 500);
                }
            } else {
                // Tampilkan notifikasi error
                showNotification(data.message || 'Gagal menambahkan produk ke keranjang', 'danger');
                
                // Reset tombol
                if (button) {
                    button.innerHTML = '<i class="fas fa-cart-plus"></i>';
                    button.disabled = false;
                }
            }
        })
        .catch(error => {
            // Sembunyikan loading state
            hideLoadingState();
            
            // Tampilkan notifikasi error
            console.error('Error:', error);
            showNotification('Terjadi kesalahan saat menambahkan produk ke keranjang', 'danger');
            
            // Reset tombol
            if (button) {
                button.innerHTML = '<i class="fas fa-cart-plus"></i>';
                button.disabled = false;
            }
        });
    }
    
    // Fungsi untuk update jumlah item di keranjang
    function updateCartCount() {
        fetch('cart.php?action=count', {
            method: 'GET'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateCartCountFromData(data.cart_count);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
    
    // Update tampilan jumlah item di keranjang
    function updateCartCountFromData(count) {
        if (cartCountEl) {
            cartCountEl.textContent = count;
            
            // Toggle class jika kosong
            if (count > 0) {
                cartCountEl.classList.remove('d-none');
            } else {
                cartCountEl.classList.add('d-none');
            }
        }
    }
    
    // Tampilkan status loading
    function showLoadingState() {
        // Implementasi loading indicator jika diperlukan
        // Misalnya dengan menambahkan overlay atau spinner
    }
    
    // Sembunyikan status loading
    function hideLoadingState() {
        // Implementasi menghilangkan loading indicator
    }
    
    // Fungsi untuk menyimpan keranjang ke localStorage
    function saveCartToLocalStorage(bookId, qty) {
        let cart = getCartFromLocalStorage();
        
        // Cek apakah buku sudah ada di keranjang
        if (cart[bookId]) {
            cart[bookId] += qty;
        } else {
            cart[bookId] = qty;
        }
        
        // Simpan ke localStorage
        localStorage.setItem('pustakanusa_cart', JSON.stringify(cart));
    }
    
    // Fungsi untuk mendapatkan keranjang dari localStorage
    function getCartFromLocalStorage() {
        const cartData = localStorage.getItem('pustakanusa_cart');
        return cartData ? JSON.parse(cartData) : {};
    }
    
    // Fungsi untuk mendapatkan jumlah item dari localStorage
    function getCartItemQtyFromLocalStorage(bookId) {
        const cart = getCartFromLocalStorage();
        return cart[bookId] || 0;
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
    
    // Sinkronkan keranjang dari localStorage ke session ketika halaman dimuat
    function syncCartWithServer() {
        const cart = getCartFromLocalStorage();
        
        // Jika ada item di localStorage, sinkronkan dengan server
        if (Object.keys(cart).length > 0) {
            fetch('cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=sync&cart=${JSON.stringify(cart)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update jumlah item di keranjang
                    updateCartCountFromData(data.cart_count);
                }
            })
            .catch(error => {
                console.error('Error syncing cart:', error);
            });
        }
    }
    
    // Jalankan sinkronisasi saat halaman dimuat
    syncCartWithServer();
}); 