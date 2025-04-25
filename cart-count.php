<?php
// Mulai session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inisialisasi keranjang belanja jika belum ada
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Hitung total item di keranjang
$total_items = 0;
foreach ($_SESSION['cart'] as $qty) {
    $total_items += $qty;
}

// Return jumlah dalam format JSON
header('Content-Type: application/json');
echo json_encode(['count' => $total_items]);
?> 