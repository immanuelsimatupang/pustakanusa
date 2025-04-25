<?php
$title = "Langganan Premium - PustakaNusa";
include 'templates/header.php';
?>

<!-- Hero Section -->
<section id="subscription-hero" class="py-5 bg-primary bg-opacity-10">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="mb-2 small">
                    <span class="badge bg-primary text-white me-2">
                        <i class="fas fa-crown me-1"></i> Premium
                    </span>
                </div>
                <h1 class="display-4 fw-bold mb-4">Nikmati Akses Tanpa Batas ke Dunia Literasi</h1>
                <p class="lead mb-4">Pilih paket langganan sesuai kebutuhan Anda dan jelajahi ribuan buku premium, audiobook, dan fitur eksklusif PustakaNusa.</p>
                <p class="mb-4">Mulai perjalanan literasi digital Anda hari ini dan dapatkan lebih dari sekadar membaca.</p>
                <a href="#pricing" class="btn btn-primary btn-lg rounded-pill">
                    <i class="fas fa-tag me-2"></i> Lihat Semua Paket
                </a>
            </div>
            <div class="col-lg-6">
                <div class="card border-0 bg-white shadow-lg p-4">
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <span class="badge bg-warning text-dark py-2 px-3 mb-2">Penawaran Terbatas</span>
                            <h4 class="fw-bold">Berlangganan Paket 12 Bulan</h4>
                            <p class="text-muted mb-0">Hemat 30% & dapatkan akses ke 5.000+ buku premium</p>
                        </div>
                        <hr class="my-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <span>Uji coba gratis 7 hari</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <span>Batalkan kapan saja</span>
                            </div>
                        </div>
                        <a href="#pricing" class="btn btn-success btn-lg w-100 rounded-pill mb-2">
                            Mulai Uji Coba Gratis
                        </a>
                        <div class="text-center small text-muted">
                            Tanpa komitmen, batalkan kapan saja
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Pricing Section -->
<section id="pricing" class="py-5">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-lg-8 mx-auto">
                <h2 class="display-5 fw-bold mb-3">Paket Berlangganan</h2>
                <p class="lead">Pilih paket yang paling sesuai dengan kebutuhan literasi Anda</p>
                
                <!-- Billing Period Toggle -->
                <div class="billing-toggle mt-4">
                    <span class="me-3 fw-medium">Bulanan</span>
                    <div class="form-check form-switch d-inline-block align-middle">
                        <input class="form-check-input" type="checkbox" id="billingToggle">
                        <label class="form-check-label" for="billingToggle"></label>
                    </div>
                    <span class="ms-3 fw-medium">Tahunan</span>
                    <span class="badge bg-danger ms-2">Hemat 30%</span>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Free Plan -->
            <div class="col-lg-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header text-center border-0 bg-transparent pt-4">
                        <h5 class="fw-bold">Gratis</h5>
                    </div>
                    <div class="card-body text-center">
                        <div class="price-value mb-4">
                            <h2 class="display-5 mb-0 fw-bold">Rp0</h2>
                            <span class="text-muted">/bulan</span>
                        </div>
                        <p class="text-muted mb-4">Akses terbatas ke konten pengetahuan dasar.</p>
                        <div class="d-grid">
                            <a href="register.php" class="btn btn-outline-primary rounded-pill">
                                Daftar Gratis
                            </a>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0 pb-4">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-3 d-flex align-items-center">
                                <i class="fas fa-check text-success me-3"></i>
                                <span>Akses ke buku gratis terbatas</span>
                            </li>
                            <li class="mb-3 d-flex align-items-center">
                                <i class="fas fa-check text-success me-3"></i>
                                <span>Pembaca digital standar</span>
                            </li>
                            <li class="mb-3 d-flex align-items-center">
                                <i class="fas fa-check text-success me-3"></i>
                                <span>Akses forum diskusi dasar</span>
                            </li>
                            <li class="mb-3 d-flex align-items-center text-muted">
                                <i class="fas fa-times text-danger me-3"></i>
                                <span>Akses ke buku premium</span>
                            </li>
                            <li class="mb-3 d-flex align-items-center text-muted">
                                <i class="fas fa-times text-danger me-3"></i>
                                <span>Download buku</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Basic Plan -->
            <div class="col-lg-4 mb-4">
                <div class="card h-100 border-0 shadow">
                    <div class="card-header text-center border-0 bg-transparent pt-4">
                        <h5 class="fw-bold">Basic</h5>
                        <span class="badge bg-primary">Populer</span>
                    </div>
                    <div class="card-body text-center">
                        <div class="price-value mb-4">
                            <div class="monthly-price">
                                <h2 class="display-5 mb-0 fw-bold">Rp59.000</h2>
                                <span class="text-muted">/bulan</span>
                            </div>
                            <div class="annual-price d-none">
                                <h2 class="display-5 mb-0 fw-bold">Rp41.300</h2>
                                <span class="text-muted">/bulan, dibayar tahunan</span>
                            </div>
                        </div>
                        <p class="text-muted mb-4">Akses ke 500+ buku premium & semua fitur dasar.</p>
                        <div class="d-grid">
                            <a href="#" class="btn btn-primary rounded-pill">
                                Mulai 7 Hari Uji Coba Gratis
                            </a>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0 pb-4">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-3 d-flex align-items-center">
                                <i class="fas fa-check text-success me-3"></i>
                                <span>Akses 500+ buku premium</span>
                            </li>
                            <li class="mb-3 d-flex align-items-center">
                                <i class="fas fa-check text-success me-3"></i>
                                <span>Pembaca digital lengkap</span>
                            </li>
                            <li class="mb-3 d-flex align-items-center">
                                <i class="fas fa-check text-success me-3"></i>
                                <span>Download 10 buku per bulan</span>
                            </li>
                            <li class="mb-3 d-flex align-items-center">
                                <i class="fas fa-check text-success me-3"></i>
                                <span>Simpan highlight & catatan</span>
                            </li>
                            <li class="mb-3 d-flex align-items-center text-muted">
                                <i class="fas fa-times text-danger me-3"></i>
                                <span>Audiobook</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Premium Plan -->
            <div class="col-lg-4 mb-4">
                <div class="card h-100 border-0 shadow-sm border-primary">
                    <div class="card-header text-center border-0 bg-transparent pt-4">
                        <h5 class="fw-bold">Premium</h5>
                        <span class="badge bg-success">Terlengkap</span>
                    </div>
                    <div class="card-body text-center">
                        <div class="price-value mb-4">
                            <div class="monthly-price">
                                <h2 class="display-5 mb-0 fw-bold">Rp99.000</h2>
                                <span class="text-muted">/bulan</span>
                            </div>
                            <div class="annual-price d-none">
                                <h2 class="display-5 mb-0 fw-bold">Rp69.300</h2>
                                <span class="text-muted">/bulan, dibayar tahunan</span>
                            </div>
                        </div>
                        <p class="text-muted mb-4">Akses tak terbatas & pengalaman literasi premium.</p>
                        <div class="d-grid">
                            <a href="#" class="btn btn-primary rounded-pill">
                                Mulai 7 Hari Uji Coba Gratis
                            </a>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0 pb-4">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-3 d-flex align-items-center">
                                <i class="fas fa-check text-success me-3"></i>
                                <span>Akses semua buku premium</span>
                            </li>
                            <li class="mb-3 d-flex align-items-center">
                                <i class="fas fa-check text-success me-3"></i>
                                <span>Pembaca digital premium</span>
                            </li>
                            <li class="mb-3 d-flex align-items-center">
                                <i class="fas fa-check text-success me-3"></i>
                                <span>Download tak terbatas</span>
                            </li>
                            <li class="mb-3 d-flex align-items-center">
                                <i class="fas fa-check text-success me-3"></i>
                                <span>Akses audiobook lengkap</span>
                            </li>
                            <li class="mb-3 d-flex align-items-center">
                                <i class="fas fa-check text-success me-3"></i>
                                <span>Prioritas akses buku baru</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Feature Comparison Table -->
<section id="feature-comparison" class="py-5 bg-light">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-lg-8 mx-auto">
                <h2 class="display-5 fw-bold mb-3">Perbandingan Fitur</h2>
                <p class="lead">Lihat fitur lengkap dari setiap paket berlangganan</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table class="table table-bordered bg-white">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th scope="col" class="feature-column">Fitur</th>
                                <th scope="col" class="text-center">Gratis</th>
                                <th scope="col" class="text-center">Basic</th>
                                <th scope="col" class="text-center">Premium</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th scope="row">Akses Buku</th>
                                <td class="text-center">Terbatas (50+ buku)</td>
                                <td class="text-center">500+ buku premium</td>
                                <td class="text-center">Semua buku (5.000+)</td>
                            </tr>
                            <tr>
                                <th scope="row">Audiobook</th>
                                <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                                <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                                <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            </tr>
                            <tr>
                                <th scope="row">Download Buku</th>
                                <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                                <td class="text-center">10 buku/bulan</td>
                                <td class="text-center">Tak terbatas</td>
                            </tr>
                            <tr>
                                <th scope="row">Mode Offline</th>
                                <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                                <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            </tr>
                            <tr>
                                <th scope="row">Highlight & Catatan</th>
                                <td class="text-center">Terbatas</td>
                                <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            </tr>
                            <tr>
                                <th scope="row">Ekspor Catatan</th>
                                <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                                <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                                <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            </tr>
                            <tr>
                                <th scope="row">Mode Baca Malam</th>
                                <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                                <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            </tr>
                            <tr>
                                <th scope="row">Sinkronisasi Multi-Perangkat</th>
                                <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                                <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            </tr>
                            <tr>
                                <th scope="row">Forum Diskusi Premium</th>
                                <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                                <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            </tr>
                            <tr>
                                <th scope="row">Klub Buku Virtual</th>
                                <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                                <td class="text-center">2 klub</td>
                                <td class="text-center">Tak terbatas</td>
                            </tr>
                            <tr>
                                <th scope="row">Dukungan Prioritas</th>
                                <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                                <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                                <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            </tr>
                            <tr>
                                <th scope="row">Iklan</th>
                                <td class="text-center">Ya</td>
                                <td class="text-center">Tidak</td>
                                <td class="text-center">Tidak</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section id="testimonials" class="py-5">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-lg-8 mx-auto">
                <h2 class="display-5 fw-bold mb-3">Apa Kata Mereka</h2>
                <p class="lead">Pengalaman pembaca yang telah berlangganan PustakaNusa Premium</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex mb-4">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                        <p class="mb-4">"PustakaNusa Premium mengubah bagaimana saya membaca. Akses ke ribuan buku premium dan fitur audiobook sangat membantu saya yang selalu sibuk. Langganan terbaik yang pernah saya miliki!"</p>
                        <div class="d-flex align-items-center">
                            <img src="assets/img/user1.jpg" alt="User" class="rounded-circle me-3" width="60">
                            <div>
                                <h6 class="mb-1">Dian Sastrowardoyo</h6>
                                <p class="small text-muted mb-0">Pengguna Premium - 1 tahun</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex mb-4">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                        <p class="mb-4">"Sebagai mahasiswa, paket Basic sangat terjangkau dan membantu studi saya. Saya bisa mengakses ratusan buku referensi dan menyimpan highlight penting. Sangat direkomendasikan!"</p>
                        <div class="d-flex align-items-center">
                            <img src="assets/img/user2.jpg" alt="User" class="rounded-circle me-3" width="60">
                            <div>
                                <h6 class="mb-1">Reza Rahadian</h6>
                                <p class="small text-muted mb-0">Pengguna Basic - 6 bulan</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex mb-4">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star-half-alt text-warning"></i>
                        </div>
                        <p class="mb-4">"Saya bergabung dengan paket Premium setahun lalu dan koleksi bukunya terus bertambah. Fitur audiobook sangat berguna saat berkendara. Worth it dengan harga yang ditawarkan!"</p>
                        <div class="d-flex align-items-center">
                            <img src="assets/img/user3.jpg" alt="User" class="rounded-circle me-3" width="60">
                            <div>
                                <h6 class="mb-1">Chelsea Islan</h6>
                                <p class="small text-muted mb-0">Pengguna Premium - 8 bulan</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section id="faq" class="py-5 bg-light">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-lg-8 mx-auto">
                <h2 class="display-5 fw-bold mb-3">Pertanyaan Umum</h2>
                <p class="lead">Jawaban atas pertanyaan yang sering ditanyakan</p>
            </div>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item border-0 mb-3 shadow-sm">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                Bagaimana cara memulai berlangganan?
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Untuk mulai berlangganan, Anda cukup memilih paket yang sesuai dengan kebutuhan, klik tombol "Mulai Uji Coba Gratis", isi formulir pendaftaran, dan masukkan informasi pembayaran Anda. Semua paket berbayar menawarkan uji coba gratis 7 hari, di mana Anda tidak akan dikenakan biaya apapun jika membatalkan sebelum periode uji coba berakhir.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item border-0 mb-3 shadow-sm">
                        <h2 class="accordion-header" id="headingTwo">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                Apakah saya bisa membatalkan langganan kapan saja?
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Ya, Anda dapat membatalkan langganan kapan saja. Jika Anda membatalkan selama periode uji coba gratis, Anda tidak akan dikenakan biaya. Jika membatalkan setelah masa uji coba, Anda masih dapat mengakses layanan premium hingga akhir periode penagihan saat ini. Tidak ada pengembalian dana untuk pembayaran yang telah dilakukan.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item border-0 mb-3 shadow-sm">
                        <h2 class="accordion-header" id="headingThree">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                Apa perbedaan utama antara paket Basic dan Premium?
                            </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Paket Basic menawarkan akses ke 500+ buku premium dan fitur dasar seperti highlight, catatan, dan download terbatas (10 buku/bulan). Paket Premium memberikan akses ke semua buku (5.000+), audiobook, download tak terbatas, dukungan prioritas, dan fitur eksklusif lainnya. Premium adalah pilihan terbaik bagi pembaca serius yang ingin pengalaman literasi digital lengkap.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item border-0 mb-3 shadow-sm">
                        <h2 class="accordion-header" id="headingFour">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                Apakah saya dapat menggunakan PustakaNusa di berbagai perangkat?
                            </button>
                        </h2>
                        <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Ya, pengguna Basic dan Premium dapat mengakses PustakaNusa dari beberapa perangkat dan sinkronisasi catatan, bookmark, dan kemajuan membaca mereka secara otomatis. Anda dapat menggunakan browser web, aplikasi seluler (Android dan iOS), serta aplikasi desktop (Windows dan Mac).
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item border-0 mb-3 shadow-sm">
                        <h2 class="accordion-header" id="headingFive">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                Apa metode pembayaran yang diterima?
                            </button>
                        </h2>
                        <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                PustakaNusa menerima berbagai metode pembayaran, termasuk kartu kredit/debit (Visa, Mastercard, American Express), transfer bank, e-wallet (GoPay, OVO, DANA, LinkAja), dan pembayaran melalui mini market (Alfamart, Indomaret). Untuk berlangganan tahunan, kami juga menawarkan opsi cicilan 0% untuk kartu kredit tertentu.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item border-0 shadow-sm">
                        <h2 class="accordion-header" id="headingSix">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
                                Bagaimana jika saya memiliki pertanyaan lain?
                            </button>
                        </h2>
                        <div id="collapseSix" class="accordion-collapse collapse" aria-labelledby="headingSix" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Untuk pertanyaan lebih lanjut, Anda dapat menghubungi tim dukungan kami melalui <a href="contact.php">halaman kontak</a>, email di support@pustakanusa.id, atau chat langsung di situs web ini. Tim dukungan pelanggan kami tersedia setiap hari dari pukul 08.00 hingga 22.00 WIB.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section id="cta" class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mb-4 mb-lg-0">
                <h2 class="display-5 fw-bold mb-3">Mulai Perjalanan Literasi Digital Anda</h2>
                <p class="lead mb-0">Nikmati 7 hari uji coba gratis dan jelajahi ribuan buku premium hari ini</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="#pricing" class="btn btn-light btn-lg text-primary me-2">
                    <i class="fas fa-crown me-2"></i> Pilih Paket
                </a>
                <a href="contact.php" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-question-circle me-2"></i> Tanya Kami
                </a>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle between monthly and annual pricing
    const billingToggle = document.getElementById('billingToggle');
    const monthlyPrices = document.querySelectorAll('.monthly-price');
    const annualPrices = document.querySelectorAll('.annual-price');
    
    billingToggle.addEventListener('change', function() {
        if (this.checked) {
            // Annual pricing
            monthlyPrices.forEach(el => el.classList.add('d-none'));
            annualPrices.forEach(el => el.classList.remove('d-none'));
        } else {
            // Monthly pricing
            monthlyPrices.forEach(el => el.classList.remove('d-none'));
            annualPrices.forEach(el => el.classList.add('d-none'));
        }
    });
});
</script>

<style>
.feature-column {
    min-width: 200px;
}

.billing-toggle .form-check-input {
    width: 3rem;
    height: 1.5rem;
    cursor: pointer;
}

.price-value h2 {
    color: var(--primary-color);
}

@media (max-width: 767px) {
    .pricing-card {
        margin-bottom: 30px;
    }
}
</style>

<?php include 'templates/footer.php'; ?> 