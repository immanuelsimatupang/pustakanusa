<?php
// File untuk menguji API RajaOngkir dengan API key demo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// API key demo (ini adalah contoh API key RajaOngkir untuk testing)
$api_key = "your_api_key"; // Isi dengan API key yang benar

// Fungsi untuk menguji API
function testRajaOngkirAPI($api_key) {
    echo "<h2>Tes Koneksi API RajaOngkir</h2>";
    
    // Ambil daftar provinsi
    $curl = curl_init();
    
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.rajaongkir.com/starter/province",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "key: " . $api_key
        ],
    ]);
    
    $response = curl_exec($curl);
    $err = curl_error($curl);
    
    curl_close($curl);
    
    echo "<h3>Hasil Test Provinsi</h3>";
    if ($err) {
        echo "<p style='color:red'>cURL Error: " . $err . "</p>";
    } else {
        $result = json_decode($response, true);
        echo "<pre>";
        if (isset($result['rajaongkir']['status']['code']) && $result['rajaongkir']['status']['code'] == 200) {
            echo "<p style='color:green'>Koneksi Berhasil!</p>";
            echo "Total Provinsi: " . count($result['rajaongkir']['results']);
            
            // Tampilkan 3 provinsi pertama
            echo "<h4>Sample Data Provinsi:</h4>";
            $counter = 0;
            foreach ($result['rajaongkir']['results'] as $province) {
                echo "ID: " . $province['province_id'] . " - " . $province['province'] . "<br>";
                $counter++;
                if ($counter >= 3) break;
            }
        } else {
            echo "<p style='color:red'>Koneksi Gagal:</p>";
            print_r($result);
        }
        echo "</pre>";
    }
}

// Form untuk mengisi API key
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['api_key'])) {
    $api_key = $_POST['api_key'];
    testRajaOngkirAPI($api_key);
} else {
?>
    <form method="post" action="">
        <h2>Tes API RajaOngkir</h2>
        <p>Masukkan API key RajaOngkir Anda untuk menguji koneksi:</p>
        <input type="text" name="api_key" value="<?php echo $api_key; ?>" style="width: 400px;">
        <button type="submit">Tes Koneksi</button>
    </form>
<?php
}
?> 