<?php
// File untuk export data pengiriman ke CSV
session_start();

// Cek apakah pengguna sudah login sebagai admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit;
}

// Include konfigurasi
require_once '../config/database.php';

// Set header untuk download file
header('Content-Type: text/csv; charset=utf-8');

// Cek tipe data yang akan diexport
if (!isset($_GET['type']) || empty($_GET['type'])) {
    echo "Tipe data tidak valid.";
    exit;
}

$type = $_GET['type'];
$today = date('Y-m-d');

switch ($type) {
    case 'provinces':
        // Export provinsi
        header('Content-Disposition: attachment; filename="provinsi_' . $today . '.csv"');
        
        // Buat file CSV
        $output = fopen('php://output', 'w');
        
        // Tambahkan BOM untuk UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Kolom header
        fputcsv($output, ['id', 'name']);
        
        // Ambil data provinsi
        $sql = "SELECT id, name FROM provinces ORDER BY name";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                fputcsv($output, $row);
            }
        }
        break;
        
    case 'cities':
        // Export kota
        header('Content-Disposition: attachment; filename="kota_' . $today . '.csv"');
        
        // Buat file CSV
        $output = fopen('php://output', 'w');
        
        // Tambahkan BOM untuk UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Kolom header
        fputcsv($output, ['id', 'province_id', 'province_name', 'name']);
        
        // Ambil data kota
        $sql = "SELECT c.id, c.province_id, p.name as province_name, c.name 
                FROM cities c
                JOIN provinces p ON c.province_id = p.id
                ORDER BY p.name, c.name";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                fputcsv($output, $row);
            }
        }
        break;
        
    case 'shipping_costs':
        // Export tarif pengiriman
        header('Content-Disposition: attachment; filename="tarif_pengiriman_' . $today . '.csv"');
        
        // Buat file CSV
        $output = fopen('php://output', 'w');
        
        // Tambahkan BOM untuk UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Kolom header
        fputcsv($output, ['id', 'city_id', 'city_name', 'province_name', 'courier', 'service_name', 'cost_per_kg', 'etd']);
        
        // Ambil data tarif pengiriman
        $sql = "SELECT sc.id, sc.city_id, c.name as city_name, p.name as province_name, 
                       sc.courier, sc.service_name, sc.cost_per_kg, sc.etd
                FROM shipping_costs sc
                JOIN cities c ON sc.city_id = c.id
                JOIN provinces p ON c.province_id = p.id
                ORDER BY p.name, c.name, sc.courier, sc.service_name";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                fputcsv($output, $row);
            }
        }
        break;
        
    default:
        echo "Tipe data tidak valid.";
        exit;
        break;
}

// Tutup file
fclose($output);
exit;
?> 