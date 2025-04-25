<?php
// Mendapatkan daftar kota berdasarkan provinsi (untuk AJAX)

// Set header JSON
header('Content-Type: application/json');

// Include konfigurasi shipping
require_once 'config/shipping.php';

// Cek parameter
if (!isset($_GET['province_id']) || empty($_GET['province_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID Provinsi tidak valid',
        'data' => []
    ]);
    exit;
}

// Ambil data kota
$province_id = intval($_GET['province_id']);
$cities = getCities($province_id);

// Cek hasil
if (isset($cities['error'])) {
    echo json_encode([
        'success' => false,
        'message' => $cities['error'],
        'data' => []
    ]);
} else {
    echo json_encode([
        'success' => true,
        'message' => 'Berhasil mendapatkan daftar kota',
        'data' => $cities
    ]);
}
?> 