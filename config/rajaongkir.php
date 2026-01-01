<?php
// Load environment variables
require_once __DIR__ . '/env.php';
loadEnv(__DIR__ . '/../.env');

// Konfigurasi RajaOngkir API
define('RAJAONGKIR_API_KEY_COST', env('RAJAONGKIR_API_KEY_COST', 'vRnucRXe7b460970b3498166c0tL09ZL')); // API key untuk biaya pengiriman
define('RAJAONGKIR_API_KEY_DELIVERY', env('RAJAONGKIR_API_KEY_DELIVERY', 'H86A0gyl7b460970b3498166G4qI254l')); // API key untuk pengiriman
define('RAJAONGKIR_BASE_URL', 'https://api.rajaongkir.com/starter');
define('RAJAONGKIR_ORIGIN', env('RAJAONGKIR_ORIGIN', '152')); // ID Kota asal (Jakarta Pusat)

/**
 * Mengambil daftar provinsi dari RajaOngkir API
 * 
 * @return array Daftar provinsi
 */
function getProvinces() {
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
            return ["error" => "Failed to get provinces"];
        }
    }
}

/**
 * Mengambil daftar kota berdasarkan ID provinsi
 * 
 * @param int $province_id ID provinsi
 * @return array Daftar kota
 */
function getCities($province_id) {
    $curl = curl_init();
    
    curl_setopt_array($curl, [
        CURLOPT_URL => RAJAONGKIR_BASE_URL . "/city?province=" . $province_id,
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
            return ["error" => "Failed to get cities"];
        }
    }
}

/**
 * Menghitung ongkos kirim menggunakan RajaOngkir API
 * 
 * @param int $destination_city ID kota tujuan
 * @param int $weight Berat paket dalam gram
 * @param string $courier Kurir pengiriman (jne, pos, tiki)
 * @return array Daftar biaya pengiriman
 */
function calculateShipping($destination_city, $weight, $courier = 'jne') {
    $curl = curl_init();
    
    $postData = [
        'origin' => RAJAONGKIR_ORIGIN,
        'destination' => $destination_city,
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
            return ["error" => "Failed to calculate shipping cost"];
        }
    }
}

/**
 * Menghitung total berat pesanan dari keranjang belanja
 * 
 * @param array $cart Array keranjang belanja
 * @return int Total berat dalam gram
 */
function calculateTotalWeight($cart) {
    global $conn;
    $total_weight = 0;
    
    foreach ($cart as $book_id => $item) {
        $sql = "SELECT weight FROM books WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $book_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $weight = $row['weight'] ?? 300; // Default 300 gram jika tidak ada data berat
            $total_weight += ($weight * $item['qty']);
        }
    }
    
    // Minimum 100 gram
    return max(100, $total_weight);
}
?> 