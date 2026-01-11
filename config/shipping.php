<?php
// Konfigurasi Pengiriman - alternatif untuk RajaOngkir
require_once 'database.php';

/**
 * Mengambil daftar provinsi dari database
 * 
 * @return array Daftar provinsi
 */
function getProvinces() {
    global $conn;
    
    $provinces = [];
    $sql = "SELECT id, name FROM provinces ORDER BY name";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $provinces[] = [
                'province_id' => $row['id'],
                'province' => $row['name']
            ];
        }
        return $provinces;
    }
    
    return ["error" => "Gagal mengambil data provinsi"];
}

/**
 * Mengambil daftar kota berdasarkan ID provinsi
 * 
 * @param int $province_id ID provinsi
 * @return array Daftar kota
 */
function getCities($province_id) {
    global $conn;
    
    $cities = [];
    $sql = "SELECT id, name FROM cities WHERE province_id = ? ORDER BY name";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $province_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $cities[] = [
                'city_id' => $row['id'],
                'city_name' => $row['name']
            ];
        }
        return $cities;
    }
    
    return ["error" => "Gagal mengambil data kota"];
}

/**
 * Menghitung ongkos kirim berdasarkan kota tujuan, berat, dan kurir
 * 
 * @param int $destination_city ID kota tujuan
 * @param int $weight Berat paket dalam gram
 * @param string $courier Kurir pengiriman (jne, tiki, pos)
 * @return array Daftar biaya pengiriman
 */
function calculateShipping($destination_city, $weight, $courier = 'jne') {
    global $conn;
    
    // Konversi berat ke kilogram (pembulatan ke atas)
    $weight_in_kg = ceil($weight / 1000);
    
    $shipping_costs = [];
    $sql = "SELECT courier, service_name, cost_per_kg, etd 
            FROM shipping_costs 
            WHERE city_id = ? AND courier = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $destination_city, $courier);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $total_cost = $row['cost_per_kg'] * $weight_in_kg;
            
            $shipping_costs[] = [
                'service' => $row['service_name'],
                'description' => $row['courier'] . ' ' . $row['service_name'],
                'cost' => $total_cost,
                'etd' => $row['etd'] . ' hari',
            ];
        }
        return $shipping_costs;
    }
    
    return ["error" => "Tidak ada layanan pengiriman yang tersedia"];
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

/**
 * Mendapatkan informasi kota berdasarkan ID
 * 
 * @param int $city_id ID kota
 * @return array Informasi kota
 */
function getCityInfo($city_id) {
    global $conn;
    
    $sql = "SELECT c.id, c.name as city_name, p.name as province_name 
            FROM cities c
            JOIN provinces p ON c.province_id = p.id
            WHERE c.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $city_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

/**
 * Mendapatkan daftar kurir pengiriman
 * 
 * @return array Daftar kurir
 */
function getCouriers() {
    return [
        ['id' => 'jne', 'name' => 'JNE'],
        ['id' => 'tiki', 'name' => 'TIKI'],
        ['id' => 'pos', 'name' => 'POS Indonesia']
    ];
}
?> 