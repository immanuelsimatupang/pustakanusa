<?php
// File untuk menguji konfigurasi RajaOngkir API

// Tampilkan semua error untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include konfigurasi dan koneksi database
require_once 'config/database.php';
require_once 'config/rajaongkir.php';

// Set header sebagai JSON
header('Content-Type: application/json');

// Fungsi untuk menangani respons
function responseJson($data, $status = true) {
    echo json_encode([
        'success' => $status,
        'data' => $data
    ], JSON_PRETTY_PRINT);
    exit;
}

// Tentukan aksi yang akan dijalankan
$action = isset($_GET['action']) ? $_GET['action'] : 'provinces';

try {
    switch ($action) {
        case 'provinces':
            // Tes mendapatkan daftar provinsi
            $provinces = getProvinces();
            responseJson($provinces);
            break;
            
        case 'cities':
            // Tes mendapatkan daftar kota berdasarkan provinsi
            $province_id = isset($_GET['province_id']) ? $_GET['province_id'] : 10; // Default: Jawa Tengah
            $cities = getCities($province_id);
            responseJson($cities);
            break;
            
        case 'shipping':
            // Tes menghitung ongkos kirim
            $destination = isset($_GET['destination']) ? $_GET['destination'] : 501; // Default: Yogyakarta
            $weight = isset($_GET['weight']) ? $_GET['weight'] : 1000; // Default: 1kg
            $courier = isset($_GET['courier']) ? $_GET['courier'] : 'jne'; // Default: JNE
            
            $shipping_cost = calculateShipping($destination, $weight, $courier);
            responseJson($shipping_cost);
            break;
            
        default:
            // Jalankan semua tes
            $tests = [
                'provinces' => getProvinces(),
                'cities' => getCities(10), // Jawa Tengah
                'shipping' => calculateShipping(501, 1000, 'jne') // Yogyakarta, 1kg, JNE
            ];
            
            responseJson($tests);
            break;
    }
} catch (Exception $e) {
    responseJson(['error' => $e->getMessage()], false);
}
?>