<?php
// File untuk menguji sistem pengiriman alternatif

// Inisialisasi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include konfigurasi shipping
require_once 'config/shipping.php';

// Fungsi untuk menampilkan data dengan format yang lebih baik
function printData($data, $title = '') {
    echo "<div style='margin-bottom: 20px;'>";
    if (!empty($title)) {
        echo "<h3>$title</h3>";
    }
    echo "<pre>";
    print_r($data);
    echo "</pre>";
    echo "</div>";
}

// Buat halaman HTML sederhana
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Shipping - Pustakanusa</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        h1 {
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        h2 {
            margin-top: 30px;
            padding-bottom: 5px;
            border-bottom: 1px solid #eee;
        }
        pre {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        select, input {
            padding: 8px;
            width: 100%;
            max-width: 300px;
        }
        button {
            padding: 8px 15px;
            background: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background: #45a049;
        }
        .card {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Test Sistem Pengiriman Alternatif</h1>
        
        <div class="card">
            <h2>Daftar Provinsi</h2>
            <?php
            $provinces = getProvinces();
            if (isset($provinces['error'])) {
                echo "<p>Error: " . $provinces['error'] . "</p>";
            } else {
                echo "<p>Total: " . count($provinces) . " provinsi</p>";
                printData($provinces);
            }
            ?>
        </div>
        
        <div class="card">
            <h2>Hitung Ongkos Kirim</h2>
            <form method="post" action="">
                <div class="form-group">
                    <label for="province">Provinsi:</label>
                    <select id="province" name="province" onchange="loadCities(this.value)">
                        <option value="">Pilih Provinsi</option>
                        <?php 
                        foreach ($provinces as $province) {
                            echo "<option value='" . $province['province_id'] . "'>" . $province['province'] . "</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="city">Kota:</label>
                    <select id="city" name="city" disabled>
                        <option value="">Pilih Kota</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="weight">Berat (gram):</label>
                    <input type="number" id="weight" name="weight" value="1000" min="100">
                </div>
                
                <div class="form-group">
                    <label for="courier">Kurir:</label>
                    <select id="courier" name="courier">
                        <?php
                        $couriers = getCouriers();
                        foreach ($couriers as $courier) {
                            echo "<option value='" . $courier['id'] . "'>" . $courier['name'] . "</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <button type="button" onclick="calculateShipping()">Hitung Ongkos Kirim</button>
            </form>
            
            <div id="shipping-result" style="margin-top: 20px;"></div>
        </div>
    </div>
    
    <script>
        function loadCities(provinceId) {
            if (!provinceId) {
                document.getElementById('city').innerHTML = '<option value="">Pilih Kota</option>';
                document.getElementById('city').disabled = true;
                return;
            }
            
            // AJAX request to get cities
            fetch('get-cities.php?province_id=' + provinceId)
                .then(response => response.json())
                .then(data => {
                    let citySelect = document.getElementById('city');
                    citySelect.innerHTML = '<option value="">Pilih Kota</option>';
                    
                    if (data.success && data.data && data.data.length) {
                        data.data.forEach(city => {
                            let option = document.createElement('option');
                            option.value = city.city_id;
                            option.textContent = city.city_name;
                            citySelect.appendChild(option);
                        });
                        citySelect.disabled = false;
                    } else {
                        citySelect.disabled = true;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }
        
        function calculateShipping() {
            const cityId = document.getElementById('city').value;
            const weight = document.getElementById('weight').value;
            const courier = document.getElementById('courier').value;
            
            if (!cityId) {
                alert('Silakan pilih kota terlebih dahulu');
                return;
            }
            
            // AJAX request to calculate shipping
            fetch('calculate-shipping.php?city_id=' + cityId + '&weight=' + weight + '&courier=' + courier)
                .then(response => response.json())
                .then(data => {
                    let resultDiv = document.getElementById('shipping-result');
                    resultDiv.innerHTML = '';
                    
                    if (data.success && data.data && data.data.length) {
                        let html = '<h3>Hasil Perhitungan Ongkos Kirim</h3>';
                        html += '<table border="1" cellpadding="10" cellspacing="0" style="border-collapse: collapse; width: 100%;">';
                        html += '<thead><tr><th>Layanan</th><th>Deskripsi</th><th>Biaya</th><th>Estimasi</th></tr></thead>';
                        html += '<tbody>';
                        
                        data.data.forEach(service => {
                            html += '<tr>';
                            html += '<td>' + service.service + '</td>';
                            html += '<td>' + service.description + '</td>';
                            html += '<td>Rp ' + parseInt(service.cost).toLocaleString('id-ID') + '</td>';
                            html += '<td>' + service.etd + '</td>';
                            html += '</tr>';
                        });
                        
                        html += '</tbody></table>';
                        resultDiv.innerHTML = html;
                    } else {
                        resultDiv.innerHTML = '<p>Tidak ada layanan pengiriman yang tersedia</p>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('shipping-result').innerHTML = '<p>Terjadi kesalahan saat menghitung ongkos kirim</p>';
                });
        }
    </script>
</body>
</html> 