<?php
// Import data pengiriman ke database

// Inisialisasi koneksi database
require_once 'config/database.php';

// Baca file SQL
$sql_file = file_get_contents('database/shipping_data.sql');

// Pisahkan query-query SQL
$queries = explode(';', $sql_file);

// Eksekusi setiap query
$success = true;
$errors = [];

foreach ($queries as $query) {
    $query = trim($query);
    
    if (empty($query)) {
        continue;
    }
    
    echo "Menjalankan query: " . substr($query, 0, 50) . "...<br>";
    
    if (!$conn->query($query)) {
        $success = false;
        $errors[] = "Error: " . $conn->error . " pada query: " . substr($query, 0, 100) . "...";
    }
}

// Tampilkan hasil
if ($success) {
    echo "<h2>Import data berhasil!</h2>";
    echo "<p>Data provinsi, kota, dan tarif pengiriman telah berhasil diimpor ke database.</p>";
} else {
    echo "<h2>Import data gagal!</h2>";
    echo "<p>Terjadi error saat mengimpor data:</p>";
    echo "<ul>";
    foreach ($errors as $error) {
        echo "<li>$error</li>";
    }
    echo "</ul>";
}

// Selesai
$conn->close();
?> 