<?php
// Menghitung biaya pengiriman (untuk AJAX)

// Set header JSON
header('Content-Type: application/json');

// Include konfigurasi shipping
require_once 'config/shipping.php';

// Cek parameter
$errors = [];

if (!isset($_GET['city_id']) || empty($_GET['city_id'])) {
    $errors[] = 'ID Kota tidak valid';
}

if (!isset($_GET['weight']) || !is_numeric($_GET['weight']) || $_GET['weight'] < 100) {
    $errors[] = 'Berat tidak valid (minimum 100 gram)';
}

if (!isset($_GET['courier']) || empty($_GET['courier'])) {
    $errors[] = 'Kurir tidak valid';
}

// Jika ada error, kembalikan pesan
if (!empty($errors)) {
    echo json_encode([
        'success' => false,
        'message' => implode(', ', $errors),
        'data' => []
    ]);
    exit;
}

// Ambil parameter
$city_id = intval($_GET['city_id']);
$weight = intval($_GET['weight']);
$courier = $_GET['courier'];

// Hitung biaya pengiriman
$shipping_costs = calculateShipping($city_id, $weight, $courier);

// Cek hasil
if (isset($shipping_costs['error'])) {
    echo json_encode([
        'success' => false,
        'message' => $shipping_costs['error'],
        'data' => []
    ]);
} else {
    echo json_encode([
        'success' => true,
        'message' => 'Berhasil menghitung biaya pengiriman',
        'data' => $shipping_costs
    ]);
}
?> 