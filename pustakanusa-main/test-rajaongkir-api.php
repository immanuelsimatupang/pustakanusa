<?php
// File untuk menguji konfigurasi RajaOngkir API tanpa database

// Tampilkan semua error untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Konfigurasi RajaOngkir API - disalin dari config/rajaongkir.php
define('RAJAONGKIR_API_KEY_COST', 'vRnucRXe7b460970b3498166c0tL09ZL'); // API key untuk biaya pengiriman
define('RAJAONGKIR_API_KEY_DELIVERY', 'H86A0gyl7b460970b3498166G4qI254l'); // API key untuk pengiriman
define('RAJAONGKIR_BASE_URL', 'https://api.rajaongkir.com/starter');
define('RAJAONGKIR_ORIGIN', '152'); // ID Kota asal (Jakarta Pusat)

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

/**
 * Mengambil daftar provinsi dari RajaOngkir API
 */
function testGetProvinces() {
    $curl = curl_init();
    
    curl_setopt_array($curl, [
        CURLOPT_URL => RAJAONGKIR_BASE_URL . "/province",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "key: " . RAJAONGKIR_API_KEY_DELIVERY
        ],
    ]);
    
    $response = curl_exec($curl);
    $err = curl_error($curl);
    
    curl_close($curl);
    
    if ($err) {
        return ["error" => "cURL Error #:" . $err];
    } else {
        $result = json_decode($response, true);
        if (isset($result['rajaongkir']['results'])) {
            return $result['rajaongkir']['results'];
        } else {
            return ["error" => "Failed to get provinces", "response" => $result];
        }
    }
}

/**
 * Menghitung ongkos kirim menggunakan RajaOngkir API
 */
function testCalculateShipping($destination = 501, $weight = 1000, $courier = 'jne') {
    $curl = curl_init();
    
    $postData = [
        'origin' => RAJAONGKIR_ORIGIN,
        'destination' => $destination,
        'weight' => $weight,
        'courier' => $courier
    ];
    
    curl_setopt_array($curl, [
        CURLOPT_URL => RAJAONGKIR_BASE_URL . "/cost",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => http_build_query($postData),
        CURLOPT_HTTPHEADER => [
            "content-type: application/x-www-form-urlencoded",
            "key: " . RAJAONGKIR_API_KEY_COST
        ],
    ]);
    
    $response = curl_exec($curl);
    $err = curl_error($curl);
    
    curl_close($curl);
    
    if ($err) {
        return ["error" => "cURL Error #:" . $err];
    } else {
        $result = json_decode($response, true);
        if (isset($result['rajaongkir']['results'][0]['costs'])) {
            return $result['rajaongkir']['results'][0]['costs'];
        } else {
            return ["error" => "Failed to calculate shipping cost", "response" => $result];
        }
    }
}

// Tentukan aksi yang akan dijalankan
$action = isset($_GET['action']) ? $_GET['action'] : 'test-all';

try {
    switch ($action) {
        case 'test-provinces':
            // Tes mendapatkan daftar provinsi dengan API DELIVERY
            $provinces = testGetProvinces();
            responseJson($provinces);
            break;
            
        case 'test-shipping':
            // Tes menghitung ongkos kirim dengan API COST
            $destination = isset($_GET['destination']) ? $_GET['destination'] : 501; // Default: Yogyakarta
            $weight = isset($_GET['weight']) ? $_GET['weight'] : 1000; // Default: 1kg
            $courier = isset($_GET['courier']) ? $_GET['courier'] : 'jne'; // Default: JNE
            
            $shipping_cost = testCalculateShipping($destination, $weight, $courier);
            responseJson($shipping_cost);
            break;
            
        default:
            // Jalankan semua tes
            $tests = [
                'test-provinces' => testGetProvinces(),
                'test-shipping' => testCalculateShipping(501, 1000, 'jne') // Yogyakarta, 1kg, JNE
            ];
            
            responseJson($tests);
            break;
    }
} catch (Exception $e) {
    responseJson(['error' => $e->getMessage()], false);
}
?> 