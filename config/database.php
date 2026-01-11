<?php
// Load environment variables
require_once __DIR__ . '/env.php';
loadEnv(__DIR__ . '/../.env');

// Konfigurasi database
$host = env('DB_HOST', 'localhost');
$username = env('DB_USERNAME', 'root');
$password = env('DB_PASSWORD', '');
$database = env('DB_DATABASE', 'pustakanusa');

// Membuat koneksi
$conn = new mysqli($host, $username, $password, $database);

// Memeriksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Set karakter encoding
$conn->set_charset("utf8");
?> 