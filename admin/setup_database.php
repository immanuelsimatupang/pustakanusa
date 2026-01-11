<?php
// File untuk membuat tabel-tabel di database
session_start();

// Koneksi ke database
require_once '../config/database.php';

// Status hasil
$messages = [];

// Buat tabel users jika belum ada
$users_table_sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(50),
    postal_code VARCHAR(10),
    province VARCHAR(50),
    profile_image VARCHAR(255) DEFAULT 'assets/images/users/default.jpg',
    is_admin TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($users_table_sql) === TRUE) {
    $messages[] = "Tabel users berhasil dibuat.";
} else {
    $messages[] = "Error membuat tabel users: " . $conn->error;
}

// Fungsi untuk membuat atau memperbarui tabel
function createOrUpdateTable($conn, $table_name, $create_query, $skip_if_exists = true) {
    $table_exists = tableExists($conn, $table_name);
    
    if (!$table_exists) {
        if ($conn->query($create_query)) {
            return ["success" => true, "message" => "Tabel $table_name berhasil dibuat."];
        } else {
            return ["success" => false, "message" => "Error saat membuat tabel $table_name: " . $conn->error];
        }
    } else if (!$skip_if_exists) {
        // Lakukan perbarui terhadap tabel yang sudah ada
        $alter_queries = generateAlterQueries($conn, $table_name, $create_query);
        $results = [];
        
        foreach ($alter_queries as $alter_query) {
            if ($conn->query($alter_query)) {
                $results[] = ["success" => true, "message" => "Tabel $table_name berhasil diperbarui."];
            } else {
                $results[] = ["success" => false, "message" => "Error saat memperbarui tabel $table_name: " . $conn->error];
            }
        }
        
        return $results;
    } else {
        return ["success" => true, "message" => "Tabel $table_name sudah ada, dilewati."];
    }
}

// Fungsi untuk menghasilkan query ALTER TABLE berdasarkan perbedaan skema
function generateAlterQueries($conn, $table_name, $create_query) {
    $alter_queries = [];
    
    // Ekstrak definisi kolom dari CREATE TABLE query
    preg_match('/CREATE TABLE.*?\((.*)\)/s', $create_query, $matches);
    $column_definitions = $matches[1];
    
    // Pisahkan definisi kolom
    $columns = [];
    $column_parts = explode(',', $column_definitions);
    
    foreach ($column_parts as $part) {
        $part = trim($part);
        if (empty($part) || strpos($part, 'PRIMARY KEY') === 0 || strpos($part, 'FOREIGN KEY') === 0) {
            continue;
        }
        
        $column_name = preg_split('/\s+/', $part)[0];
        $column_name = str_replace('`', '', $column_name);
        $columns[$column_name] = $part;
    }
    
    // Dapatkan skema kolom yang ada
    $existing_columns = [];
    $result = $conn->query("SHOW COLUMNS FROM `$table_name`");
    while ($row = $result->fetch_assoc()) {
        $existing_columns[] = $row['Field'];
    }
    
    // Cek kolom yang belum ada dan tambahkan query ALTER TABLE
    foreach ($columns as $column_name => $column_def) {
        if (!in_array($column_name, $existing_columns)) {
            $alter_queries[] = "ALTER TABLE `$table_name` ADD $column_def";
        }
    }
    
    return $alter_queries;
}

// Buat tabel categories jika belum ada
$create_categories = "CREATE TABLE `categories` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    `parent_id` INT DEFAULT 0,
    `display_order` INT DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

// Periksa dan perbarui tabel categories
$categories_result = createOrUpdateTable($conn, "categories", $create_categories, false);
if (is_array($categories_result) && isset($categories_result[0])) {
    foreach ($categories_result as $result) {
        if ($result['success']) {
            $success_messages[] = $result['message'];
        } else {
            $error_messages[] = $result['message'];
        }
    }
} else {
    if ($categories_result['success']) {
        $success_messages[] = $categories_result['message'];
    } else {
        $error_messages[] = $categories_result['message'];
    }
}

// Buat tabel authors
$authors_table_sql = "CREATE TABLE IF NOT EXISTS authors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    bio TEXT,
    photo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($authors_table_sql) === TRUE) {
    $messages[] = "Tabel authors berhasil dibuat.";
} else {
    $messages[] = "Error membuat tabel authors: " . $conn->error;
}

// Buat tabel publishers
$publishers_table_sql = "CREATE TABLE IF NOT EXISTS publishers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    logo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($publishers_table_sql) === TRUE) {
    $messages[] = "Tabel publishers berhasil dibuat.";
} else {
    $messages[] = "Error membuat tabel publishers: " . $conn->error;
}

// Buat tabel books
$books_table_sql = "CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    isbn VARCHAR(20),
    synopsis TEXT,
    cover_image VARCHAR(255) DEFAULT 'assets/images/books/default.jpg',
    price DECIMAL(10,2) NOT NULL,
    discount_price DECIMAL(10,2),
    publisher_id INT,
    publish_date DATE,
    page_count INT,
    weight INT COMMENT 'Berat dalam gram',
    dimensions VARCHAR(50) COMMENT 'PxLxT dalam cm',
    stock INT NOT NULL DEFAULT 0,
    is_featured TINYINT(1) DEFAULT 0,
    is_bestseller TINYINT(1) DEFAULT 0,
    is_new TINYINT(1) DEFAULT 0,
    preview_file VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (publisher_id) REFERENCES publishers(id) ON DELETE SET NULL
)";

if ($conn->query($books_table_sql) === TRUE) {
    $messages[] = "Tabel books berhasil dibuat.";
} else {
    $messages[] = "Error membuat tabel books: " . $conn->error;
}

// Buat tabel book_categories
$book_categories_table_sql = "CREATE TABLE IF NOT EXISTS book_categories (
    book_id INT,
    category_id INT,
    PRIMARY KEY (book_id, category_id),
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
)";

if ($conn->query($book_categories_table_sql) === TRUE) {
    $messages[] = "Tabel book_categories berhasil dibuat.";
} else {
    $messages[] = "Error membuat tabel book_categories: " . $conn->error;
}

// Buat tabel book_authors
$book_authors_table_sql = "CREATE TABLE IF NOT EXISTS book_authors (
    book_id INT,
    author_id INT,
    PRIMARY KEY (book_id, author_id),
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (author_id) REFERENCES authors(id) ON DELETE CASCADE
)";

if ($conn->query($book_authors_table_sql) === TRUE) {
    $messages[] = "Tabel book_authors berhasil dibuat.";
} else {
    $messages[] = "Error membuat tabel book_authors: " . $conn->error;
}

// Buat tabel orders
$orders_table_sql = "CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) NOT NULL UNIQUE,
    user_id INT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'completed', 'cancelled') DEFAULT 'pending',
    total_amount DECIMAL(10,2) NOT NULL,
    shipping_cost DECIMAL(10,2) NOT NULL,
    shipping_address TEXT NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    payment_status VARCHAR(50) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
)";

if ($conn->query($orders_table_sql) === TRUE) {
    $messages[] = "Tabel orders berhasil dibuat.";
} else {
    $messages[] = "Error membuat tabel orders: " . $conn->error;
}

// Buat tabel order_items
$order_items_table_sql = "CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    book_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE SET NULL
)";

if ($conn->query($order_items_table_sql) === TRUE) {
    $messages[] = "Tabel order_items berhasil dibuat.";
} else {
    $messages[] = "Error membuat tabel order_items: " . $conn->error;
}

// Buat tabel provinces
$provinces_table_sql = "CREATE TABLE IF NOT EXISTS provinces (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
)";

if ($conn->query($provinces_table_sql) === TRUE) {
    $messages[] = "Tabel provinces berhasil dibuat.";
} else {
    $messages[] = "Error membuat tabel provinces: " . $conn->error;
}

// Buat tabel cities
$cities_table_sql = "CREATE TABLE IF NOT EXISTS cities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    province_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    FOREIGN KEY (province_id) REFERENCES provinces(id) ON DELETE CASCADE
)";

if ($conn->query($cities_table_sql) === TRUE) {
    $messages[] = "Tabel cities berhasil dibuat.";
} else {
    $messages[] = "Error membuat tabel cities: " . $conn->error;
}

// Buat tabel shipping_costs
$shipping_costs_table_sql = "CREATE TABLE IF NOT EXISTS shipping_costs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    city_id INT NOT NULL,
    courier VARCHAR(50) NOT NULL,
    service_name VARCHAR(100) NOT NULL,
    cost_per_kg DECIMAL(10,2) NOT NULL,
    etd VARCHAR(50) NOT NULL,
    FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE CASCADE
)";

if ($conn->query($shipping_costs_table_sql) === TRUE) {
    $messages[] = "Tabel shipping_costs berhasil dibuat.";
} else {
    $messages[] = "Error membuat tabel shipping_costs: " . $conn->error;
}

// Buat admin default jika tabel berhasil dibuat
if (!empty($messages) && strpos($messages[0], "berhasil") !== false) {
    // Cek apakah sudah ada user admin
    $check_admin = $conn->query("SELECT * FROM users WHERE is_admin = 1 LIMIT 1");
    
    if ($check_admin && $check_admin->num_rows == 0) {
        // Buat admin default
        $admin_name = "Admin Pustakanusa";
        $admin_email = "admin@pustakanusa.com";
        $admin_password = password_hash("admin123", PASSWORD_DEFAULT);
        
        $insert_admin_sql = "INSERT INTO users (name, email, password, is_admin) VALUES (?, ?, ?, 1)";
        $stmt = $conn->prepare($insert_admin_sql);
        $stmt->bind_param("sss", $admin_name, $admin_email, $admin_password);
        
        if ($stmt->execute()) {
            $messages[] = "Admin default berhasil dibuat dengan email: admin@pustakanusa.com dan password: admin123";
        } else {
            $messages[] = "Error membuat admin default: " . $stmt->error;
        }
    } else {
        $messages[] = "Admin sudah ada di database.";
    }
}

// Tambahkan beberapa data contoh untuk kategori, penulis, dan penerbit
if ($conn->query("SELECT * FROM categories")->num_rows == 0) {
    // Tambahkan kategori contoh
    $sample_categories = [
        ["Fiksi", "fiksi", "Buku-buku fiksi populer"],
        ["Non-Fiksi", "non-fiksi", "Buku-buku non-fiksi"],
        ["Pendidikan", "pendidikan", "Buku-buku pendidikan"],
        ["Agama", "agama", "Buku-buku keagamaan"],
        ["Anak-anak", "anak-anak", "Buku untuk anak-anak"]
    ];
    
    $cat_insert_sql = "INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)";
    $cat_stmt = $conn->prepare($cat_insert_sql);
    
    foreach ($sample_categories as $category) {
        $cat_stmt->bind_param("sss", $category[0], $category[1], $category[2]);
        $cat_stmt->execute();
    }
    
    $messages[] = "Data contoh kategori berhasil ditambahkan.";
}

// Tampilkan pesan sukses dan error
if (isset($success_messages) && !empty($success_messages)) {
    echo "<div style='color: green; margin-bottom: 10px;'>";
    echo "<strong>Sukses:</strong><br>";
    foreach ($success_messages as $message) {
        echo "- $message<br>";
    }
    echo "</div>";
}

if (isset($error_messages) && !empty($error_messages)) {
    echo "<div style='color: red; margin-bottom: 10px;'>";
    echo "<strong>Error:</strong><br>";
    foreach ($error_messages as $message) {
        echo "- $message<br>";
    }
    echo "</div>";
}

// Link untuk kembali ke halaman admin
echo "<div style='margin-top: 20px;'>";
echo "<a href='dashboard.php'>Kembali ke Dashboard</a>";
echo "</div>";

// Tampilkan hasil
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Database - Pustakanusa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Setup Database Pustakanusa</h4>
                    </div>
                    <div class="card-body">
                        <h5>Hasil Setup:</h5>
                        <div class="alert alert-info">
                            <p>Setup database sedang berjalan. Mohon tunggu hingga semua tabel berhasil dibuat.</p>
                            <p>Proses ini mungkin memakan waktu beberapa saat tergantung dari kecepatan server.</p>
                        </div>
                        
                        <ul class="list-group mb-4">
                            <?php foreach ($messages as $message): ?>
                                <li class="list-group-item"><?php echo $message; ?></li>
                            <?php endforeach; ?>
                        </ul>
                        
                        <div class="alert alert-info">
                            <h5>Informasi Login Admin:</h5>
                            <p>Email: admin@pustakanusa.com<br>Password: admin123</p>
                            <p class="mb-0 text-danger"><strong>Catatan:</strong> Segera ubah password default untuk keamanan!</p>
                        </div>
                        
                        <div class="mt-4">
                            <h5>Langkah selanjutnya:</h5>
                            <ol>
                                <li>Login ke halaman admin di <a href="login.php">Login Admin</a>.</li>
                                <li>Akses dashboard admin di <a href="index.php">Dashboard Admin</a>.</li>
                                <li>Anda juga bisa membuat admin lain melalui <a href="create_admin.php">Create Admin</a> jika diperlukan.</li>
                            </ol>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-between">
                        <a href="check_system.php" class="btn btn-secondary">Cek Sistem</a>
                        <a href="dashboard.php" class="btn btn-primary">Dashboard Admin</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 