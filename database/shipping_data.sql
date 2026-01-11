-- Tabel Provinsi
CREATE TABLE IF NOT EXISTS provinces (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

-- Tabel Kota
CREATE TABLE IF NOT EXISTS cities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    province_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    FOREIGN KEY (province_id) REFERENCES provinces(id)
);

-- Tabel Tarif Pengiriman
CREATE TABLE IF NOT EXISTS shipping_costs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    city_id INT NOT NULL,
    courier VARCHAR(10) NOT NULL,
    service_name VARCHAR(100) NOT NULL,
    cost_per_kg INT NOT NULL,
    etd VARCHAR(20) NOT NULL,
    FOREIGN KEY (city_id) REFERENCES cities(id)
);

-- Tambahkan data Provinsi
INSERT INTO provinces (name) VALUES 
('Bali'), 
('Bangka Belitung'),
('Banten'), 
('Bengkulu'), 
('DI Yogyakarta'),
('DKI Jakarta'), 
('Gorontalo'), 
('Jambi'), 
('Jawa Barat'), 
('Jawa Tengah'),
('Jawa Timur'), 
('Kalimantan Barat'), 
('Kalimantan Selatan'), 
('Kalimantan Tengah'),
('Kalimantan Timur'),
('Kalimantan Utara'),
('Kepulauan Riau'),
('Lampung'),
('Maluku'),
('Maluku Utara'),
('Nusa Tenggara Barat'),
('Nusa Tenggara Timur'),
('Papua'),
('Papua Barat'),
('Riau'),
('Sulawesi Barat'),
('Sulawesi Selatan'),
('Sulawesi Tengah'),
('Sulawesi Tenggara'),
('Sulawesi Utara'),
('Sumatera Barat'),
('Sumatera Selatan'),
('Sumatera Utara');

-- Tambahkan data Kota utama
INSERT INTO cities (province_id, name) VALUES 
(6, 'Jakarta Pusat'),
(6, 'Jakarta Utara'),
(6, 'Jakarta Barat'),
(6, 'Jakarta Selatan'),
(6, 'Jakarta Timur'),
(9, 'Bandung'),
(9, 'Bekasi'),
(9, 'Bogor'),
(9, 'Depok'),
(10, 'Semarang'),
(10, 'Solo'),
(10, 'Magelang'),
(11, 'Surabaya'),
(11, 'Malang'),
(11, 'Sidoarjo'),
(5, 'Yogyakarta'),
(5, 'Sleman'),
(5, 'Bantul'),
(1, 'Denpasar'),
(3, 'Tangerang'),
(3, 'Tangerang Selatan'),
(3, 'Serang'),
(3, 'Cilegon');

-- Tambahkan data tarif pengiriman untuk JNE
INSERT INTO shipping_costs (city_id, courier, service_name, cost_per_kg, etd) VALUES
-- Jakarta
(1, 'jne', 'REG', 10000, '1-2'),
(1, 'jne', 'YES', 16000, '1'),
(1, 'jne', 'OKE', 8000, '2-3'),
(2, 'jne', 'REG', 10000, '1-2'),
(2, 'jne', 'YES', 16000, '1'),
(2, 'jne', 'OKE', 8000, '2-3'),
(3, 'jne', 'REG', 10000, '1-2'),
(3, 'jne', 'YES', 16000, '1'),
(3, 'jne', 'OKE', 8000, '2-3'),
(4, 'jne', 'REG', 10000, '1-2'),
(4, 'jne', 'YES', 16000, '1'),
(4, 'jne', 'OKE', 8000, '2-3'),
(5, 'jne', 'REG', 10000, '1-2'),
(5, 'jne', 'YES', 16000, '1'),
(5, 'jne', 'OKE', 8000, '2-3'),

-- Bandung
(6, 'jne', 'REG', 12000, '1-2'),
(6, 'jne', 'YES', 18000, '1'),
(6, 'jne', 'OKE', 10000, '2-3'),

-- Bekasi
(7, 'jne', 'REG', 11000, '1-2'),
(7, 'jne', 'YES', 17000, '1'),
(7, 'jne', 'OKE', 9000, '2-3'),

-- Surabaya
(13, 'jne', 'REG', 18000, '2-3'),
(13, 'jne', 'YES', 24000, '1-2'),
(13, 'jne', 'OKE', 15000, '3-4'),

-- Yogyakarta
(16, 'jne', 'REG', 16000, '2-3'),
(16, 'jne', 'YES', 22000, '1-2'),
(16, 'jne', 'OKE', 14000, '3-4'),

-- Denpasar
(19, 'jne', 'REG', 26000, '3-4'),
(19, 'jne', 'YES', 36000, '2-3'),
(19, 'jne', 'OKE', 22000, '4-5');

-- Tambahkan data tarif pengiriman untuk TIKI
INSERT INTO shipping_costs (city_id, courier, service_name, cost_per_kg, etd) VALUES
-- Jakarta
(1, 'tiki', 'REG', 11000, '1-2'),
(1, 'tiki', 'ECO', 9000, '2-3'),
(1, 'tiki', 'ONS', 20000, '1'),
(2, 'tiki', 'REG', 11000, '1-2'),
(2, 'tiki', 'ECO', 9000, '2-3'),
(2, 'tiki', 'ONS', 20000, '1'),

-- Bandung
(6, 'tiki', 'REG', 13000, '1-2'),
(6, 'tiki', 'ECO', 11000, '2-3'),
(6, 'tiki', 'ONS', 22000, '1'),

-- Surabaya
(13, 'tiki', 'REG', 19000, '2-3'),
(13, 'tiki', 'ECO', 16000, '3-4'),
(13, 'tiki', 'ONS', 28000, '1-2'),

-- Yogyakarta
(16, 'tiki', 'REG', 17000, '2-3'),
(16, 'tiki', 'ECO', 15000, '3-4'),
(16, 'tiki', 'ONS', 25000, '1-2');

-- Tambahkan data tarif pengiriman untuk POS
INSERT INTO shipping_costs (city_id, courier, service_name, cost_per_kg, etd) VALUES
-- Jakarta
(1, 'pos', 'Kilat Khusus', 11000, '1-2'),
(1, 'pos', 'Express Next Day', 18000, '1'),
(2, 'pos', 'Kilat Khusus', 11000, '1-2'),
(2, 'pos', 'Express Next Day', 18000, '1'),

-- Bandung
(6, 'pos', 'Kilat Khusus', 13000, '1-2'),
(6, 'pos', 'Express Next Day', 20000, '1'),

-- Surabaya
(13, 'pos', 'Kilat Khusus', 19000, '2-3'),
(13, 'pos', 'Express Next Day', 28000, '1-2'),

-- Yogyakarta
(16, 'pos', 'Kilat Khusus', 17000, '2-3'),
(16, 'pos', 'Express Next Day', 24000, '1-2'); 