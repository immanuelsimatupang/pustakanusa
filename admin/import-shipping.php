<?php
// File untuk import data pengiriman dari CSV
session_start();

// Cek apakah pengguna sudah login sebagai admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit;
}

// Include konfigurasi
require_once '../config/database.php';

// Fungsi untuk sanitasi input
function sanitize($input) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($input));
}

// Inisialisasi variabel
$success_message = '';
$error_message = '';
$rows_imported = 0;

// Proses form import
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Cek apakah tipe import valid
    if (!isset($_POST['import_type']) || empty($_POST['import_type'])) {
        $error_message = "Tipe data tidak valid.";
    } else {
        // Cek apakah file ada
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] != 0) {
            $error_message = "File tidak valid atau terjadi kesalahan saat upload.";
        } else {
            $import_type = $_POST['import_type'];
            $file = $_FILES['csv_file'];
            
            // Cek ekstensi file
            $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if ($file_ext != 'csv') {
                $error_message = "File harus berformat CSV.";
            } else {
                // Baca file CSV
                $handle = fopen($file['tmp_name'], 'r');
                
                // Baca header (baris pertama)
                $header = fgetcsv($handle);
                
                // Proses import berdasarkan tipe
                switch ($import_type) {
                    case 'provinces':
                        // Import provinsi
                        // Cek header
                        if ($header[0] != 'id' || $header[1] != 'name') {
                            $error_message = "Format header CSV tidak sesuai untuk data provinsi.";
                            break;
                        }
                        
                        // Baca data
                        while (($data = fgetcsv($handle)) !== FALSE) {
                            $name = sanitize($data[1]);
                            
                            if (!empty($name)) {
                                // Cek apakah provinsi sudah ada
                                $check_sql = "SELECT id FROM provinces WHERE name = ?";
                                $check_stmt = $conn->prepare($check_sql);
                                $check_stmt->bind_param("s", $name);
                                $check_stmt->execute();
                                $check_result = $check_stmt->get_result();
                                
                                if ($check_result->num_rows > 0) {
                                    // Update provinsi (opsional)
                                    // Dalam hal ini kita lewati saja
                                } else {
                                    // Tambah provinsi baru
                                    $insert_sql = "INSERT INTO provinces (name) VALUES (?)";
                                    $insert_stmt = $conn->prepare($insert_sql);
                                    $insert_stmt->bind_param("s", $name);
                                    
                                    if ($insert_stmt->execute()) {
                                        $rows_imported++;
                                    }
                                }
                            }
                        }
                        
                        $success_message = "Berhasil mengimpor $rows_imported data provinsi.";
                        break;
                        
                    case 'cities':
                        // Import kota
                        // Cek header
                        if (!in_array('province_id', $header) || !in_array('name', $header)) {
                            $error_message = "Format header CSV tidak sesuai untuk data kota.";
                            break;
                        }
                        
                        // Baca data
                        while (($data = fgetcsv($handle)) !== FALSE) {
                            // Cari indeks kolom
                            $province_id_idx = array_search('province_id', $header);
                            $name_idx = array_search('name', $header);
                            
                            $province_id = intval($data[$province_id_idx]);
                            $name = sanitize($data[$name_idx]);
                            
                            if ($province_id > 0 && !empty($name)) {
                                // Cek apakah provinsi ada
                                $check_province_sql = "SELECT id FROM provinces WHERE id = ?";
                                $check_province_stmt = $conn->prepare($check_province_sql);
                                $check_province_stmt->bind_param("i", $province_id);
                                $check_province_stmt->execute();
                                $check_province_result = $check_province_stmt->get_result();
                                
                                if ($check_province_result->num_rows > 0) {
                                    // Cek apakah kota sudah ada
                                    $check_sql = "SELECT id FROM cities WHERE province_id = ? AND name = ?";
                                    $check_stmt = $conn->prepare($check_sql);
                                    $check_stmt->bind_param("is", $province_id, $name);
                                    $check_stmt->execute();
                                    $check_result = $check_stmt->get_result();
                                    
                                    if ($check_result->num_rows > 0) {
                                        // Update kota (opsional)
                                        // Dalam hal ini kita lewati saja
                                    } else {
                                        // Tambah kota baru
                                        $insert_sql = "INSERT INTO cities (province_id, name) VALUES (?, ?)";
                                        $insert_stmt = $conn->prepare($insert_sql);
                                        $insert_stmt->bind_param("is", $province_id, $name);
                                        
                                        if ($insert_stmt->execute()) {
                                            $rows_imported++;
                                        }
                                    }
                                }
                            }
                        }
                        
                        $success_message = "Berhasil mengimpor $rows_imported data kota.";
                        break;
                        
                    case 'shipping_costs':
                        // Import tarif pengiriman
                        // Cek header
                        $required_fields = ['city_id', 'courier', 'service_name', 'cost_per_kg', 'etd'];
                        $missing_fields = array_diff($required_fields, $header);
                        
                        if (!empty($missing_fields)) {
                            $error_message = "Format header CSV tidak sesuai untuk data tarif pengiriman. Field yang kurang: " . implode(', ', $missing_fields);
                            break;
                        }
                        
                        // Baca data
                        while (($data = fgetcsv($handle)) !== FALSE) {
                            // Cari indeks kolom
                            $city_id_idx = array_search('city_id', $header);
                            $courier_idx = array_search('courier', $header);
                            $service_name_idx = array_search('service_name', $header);
                            $cost_per_kg_idx = array_search('cost_per_kg', $header);
                            $etd_idx = array_search('etd', $header);
                            
                            $city_id = intval($data[$city_id_idx]);
                            $courier = sanitize($data[$courier_idx]);
                            $service_name = sanitize($data[$service_name_idx]);
                            $cost_per_kg = intval($data[$cost_per_kg_idx]);
                            $etd = sanitize($data[$etd_idx]);
                            
                            if ($city_id > 0 && !empty($courier) && !empty($service_name) && $cost_per_kg > 0 && !empty($etd)) {
                                // Cek apakah kota ada
                                $check_city_sql = "SELECT id FROM cities WHERE id = ?";
                                $check_city_stmt = $conn->prepare($check_city_sql);
                                $check_city_stmt->bind_param("i", $city_id);
                                $check_city_stmt->execute();
                                $check_city_result = $check_city_stmt->get_result();
                                
                                if ($check_city_result->num_rows > 0) {
                                    // Cek apakah tarif sudah ada
                                    $check_sql = "SELECT id FROM shipping_costs WHERE city_id = ? AND courier = ? AND service_name = ?";
                                    $check_stmt = $conn->prepare($check_sql);
                                    $check_stmt->bind_param("iss", $city_id, $courier, $service_name);
                                    $check_stmt->execute();
                                    $check_result = $check_stmt->get_result();
                                    
                                    if ($check_result->num_rows > 0) {
                                        // Update tarif
                                        $row = $check_result->fetch_assoc();
                                        $id = $row['id'];
                                        
                                        $update_sql = "UPDATE shipping_costs SET cost_per_kg = ?, etd = ? WHERE id = ?";
                                        $update_stmt = $conn->prepare($update_sql);
                                        $update_stmt->bind_param("isi", $cost_per_kg, $etd, $id);
                                        
                                        if ($update_stmt->execute()) {
                                            $rows_imported++;
                                        }
                                    } else {
                                        // Tambah tarif baru
                                        $insert_sql = "INSERT INTO shipping_costs (city_id, courier, service_name, cost_per_kg, etd) VALUES (?, ?, ?, ?, ?)";
                                        $insert_stmt = $conn->prepare($insert_sql);
                                        $insert_stmt->bind_param("issis", $city_id, $courier, $service_name, $cost_per_kg, $etd);
                                        
                                        if ($insert_stmt->execute()) {
                                            $rows_imported++;
                                        }
                                    }
                                }
                            }
                        }
                        
                        $success_message = "Berhasil mengimpor $rows_imported data tarif pengiriman.";
                        break;
                        
                    default:
                        $error_message = "Tipe data tidak valid.";
                        break;
                }
                
                // Tutup file
                fclose($handle);
            }
        }
    }
    
    // Redirect kembali ke halaman manajemen
    if (!empty($success_message)) {
        header("Location: shipping-management.php?success=" . urlencode($success_message));
        exit;
    } else if (!empty($error_message)) {
        header("Location: shipping-management.php?error=" . urlencode($error_message));
        exit;
    }
}

// Jika tidak ada POST data, redirect
header("Location: shipping-management.php");
exit;
?> 