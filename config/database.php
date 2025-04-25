<?php
// Konfigurasi database
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'pustakanusa';

// Membuat koneksi
$conn = new mysqli($host, $username, $password, $database);

// Memeriksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Set karakter encoding
$conn->set_charset("utf8");
?> 