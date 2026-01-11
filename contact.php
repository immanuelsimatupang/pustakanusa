<?php
$title = "Kontak Kami - Kahfi Education";
include 'templates/header.php';
?>

<!-- Page Header -->
<header class="page-header bg-pattern py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-8 mx-auto text-center">
                <h1 class="display-4 fw-bold text-white mb-3">Kontak Kami</h1>
                <p class="lead text-white mb-0">Hubungi kami untuk informasi lebih lanjut atau jika Anda memiliki pertanyaan.</p>
            </div>
        </div>
    </div>
</header>

<!-- Main Content -->
<section class="py-5">
    <div class="container">
        <div class="row g-5">
            <!-- Contact Form -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4 p-lg-5">
                        <h2 class="fw-bold mb-4">Kirim Pesan</h2>
                        
                        <form id="contactForm">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="phone" class="form-label">Nomor Telepon</label>
                                        <input type="tel" class="form-control" id="phone" name="phone">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="subject" class="form-label">Subjek <span class="text-danger">*</span></label>
                                        <select class="form-select" id="subject" name="subject" required>
                                            <option value="" selected disabled>Pilih subjek</option>
                                            <option value="Informasi Program">Informasi Program</option>
                                            <option value="Pendaftaran">Pendaftaran</option>
                                            <option value="Pembayaran">Pembayaran</option>
                                            <option value="Kerja Sama">Kerja Sama</option>
                                            <option value="Lainnya">Lainnya</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group mb-3">
                                        <label for="message" class="form-label">Pesan <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="privacy" required>
                                        <label class="form-check-label small" for="privacy">
                                            Saya menyetujui <a href="#">kebijakan privasi</a> dan bersedia data saya diproses untuk keperluan kontak.
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-success rounded-pill px-4">
                                        <i class="fas fa-paper-plane me-2"></i> Kirim Pesan
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Contact Information -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h3 class="fw-bold mb-4">Informasi Kontak</h3>
                        
                        <div class="d-flex mb-4">
                            <div class="flex-shrink-0 me-3">
                                <div class="feature-icon bg-success text-white rounded-circle">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1">Alamat</h5>
                                <p class="mb-0">Jl. Pendidikan No. 123, Kebayoran Baru, Jakarta Selatan 12170</p>
                            </div>
                        </div>
                        
                        <div class="d-flex mb-4">
                            <div class="flex-shrink-0 me-3">
                                <div class="feature-icon bg-success text-white rounded-circle">
                                    <i class="fas fa-phone-alt"></i>
                                </div>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1">Telepon</h5>
                                <p class="mb-0"><a href="tel:+628123456789" class="text-dark">+62 812-3456-7890</a></p>
                                <p class="mb-0"><a href="tel:+621234567890" class="text-dark">+62 21-3456-7890</a> (Kantor)</p>
                            </div>
                        </div>
                        
                        <div class="d-flex mb-4">
                            <div class="flex-shrink-0 me-3">
                                <div class="feature-icon bg-success text-white rounded-circle">
                                    <i class="fas fa-envelope"></i>
                                </div>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1">Email</h5>
                                <p class="mb-0"><a href="mailto:info@kahfieducation.com" class="text-dark">info@kahfieducation.com</a></p>
                                <p class="mb-0"><a href="mailto:admin@kahfieducation.com" class="text-dark">admin@kahfieducation.com</a></p>
                            </div>
                        </div>
                        
                        <div class="d-flex">
                            <div class="flex-shrink-0 me-3">
                                <div class="feature-icon bg-success text-white rounded-circle">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1">Jam Operasional</h5>
                                <p class="mb-0">Senin - Jum'at: 08.00 - 16.30 WIB</p>
                                <p class="mb-0">Sabtu: 08.00 - 13.00 WIB</p>
                                <p class="mb-0">Minggu & Hari Libur: Tutup</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Social Media -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h3 class="fw-bold mb-3">Media Sosial</h3>
                        <p class="mb-3">Ikuti kami di media sosial untuk mendapatkan update terbaru:</p>
                        
                        <div class="d-flex flex-wrap">
                            <a href="#" class="btn btn-outline-dark me-2 mb-2" target="_blank">
                                <i class="fab fa-facebook-f me-2"></i> Facebook
                            </a>
                            <a href="#" class="btn btn-outline-dark me-2 mb-2" target="_blank">
                                <i class="fab fa-instagram me-2"></i> Instagram
                            </a>
                            <a href="#" class="btn btn-outline-dark me-2 mb-2" target="_blank">
                                <i class="fab fa-youtube me-2"></i> YouTube
                            </a>
                            <a href="#" class="btn btn-outline-dark mb-2" target="_blank">
                                <i class="fab fa-telegram me-2"></i> Telegram
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Map Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h2 class="text-center fw-bold mb-4">Lokasi Kami</h2>
                <div class="map-container shadow-sm rounded overflow-hidden">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d253840.65294540207!2d106.68942795379939!3d-6.229386714551755!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f3e945e34b9d%3A0x5371bf0fdad786a2!2sJakarta%2C%20Daerah%20Khusus%20Ibukota%20Jakarta!5e0!3m2!1sid!2sid!4v1684000000000!5m2!1sid!2sid" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-10 mx-auto">
                <h2 class="text-center fw-bold mb-5">Pertanyaan yang Sering Diajukan</h2>
                
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item mb-3 border">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                Bagaimana cara mendaftar program di Kahfi Education?
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Untuk mendaftar program di Kahfi Education, Anda dapat mengisi formulir pendaftaran secara online di website kami atau menghubungi kami melalui WhatsApp atau telepon. Tim kami akan menghubungi Anda untuk konfirmasi dan memberikan informasi lebih lanjut tentang proses pendaftaran.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item mb-3 border">
                        <h2 class="accordion-header" id="headingTwo">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                Berapa biaya untuk mengikuti program di Kahfi Education?
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Biaya program bervariasi tergantung pada jenis dan durasi program yang Anda pilih. Untuk informasi biaya yang akurat, Anda dapat menghubungi kami melalui kontak yang tersedia atau mengisi formulir pendaftaran. Kami juga menyediakan beberapa beasiswa dan program bantuan biaya untuk peserta yang memenuhi syarat.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item mb-3 border">
                        <h2 class="accordion-header" id="headingThree">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                Apakah program Kahfi Education tersedia secara online?
                            </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Ya, kami menyediakan program pembelajaran baik secara online maupun offline. Program online kami dirancang dengan platform interaktif yang memungkinkan siswa belajar dari mana saja dengan akses internet. Kami juga menawarkan kelas hybrid yang menggabungkan pembelajaran online dan tatap muka.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item mb-3 border">
                        <h2 class="accordion-header" id="headingFour">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                Bagaimana metode pembayaran di Kahfi Education?
                            </button>
                        </h2>
                        <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Kami menerima pembayaran melalui transfer bank, virtual account, e-wallet (OVO, GoPay, Dana, LinkAja), dan pembayaran langsung di kantor kami. Pembayaran dapat dilakukan secara penuh atau dengan cicilan sesuai dengan kebijakan program yang diikuti.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item border">
                        <h2 class="accordion-header" id="headingFive">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                Apakah Kahfi Education menyediakan sertifikat setelah menyelesaikan program?
                            </button>
                        </h2>
                        <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Ya, setiap peserta yang berhasil menyelesaikan program dengan baik akan mendapatkan sertifikat resmi dari Kahfi Education. Untuk beberapa program tertentu, kami juga menyediakan sertifikasi berstandar nasional atau internasional yang diakui oleh industri terkait.
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <p>Masih punya pertanyaan? Jangan ragu untuk menghubungi kami.</p>
                    <a href="https://wa.me/628123456789" class="btn btn-success rounded-pill px-4" target="_blank">
                        <i class="fab fa-whatsapp me-2"></i> Chat WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-5 bg-success bg-gradient text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="fw-bold mb-3">Bergabunglah dengan Kahfi Education</h2>
                <p class="lead mb-4">Mari bersama-sama membangun generasi Qurani yang cerdas, berkarakter, dan sukses dunia akhirat.</p>
                <div class="d-flex justify-content-center flex-wrap">
                    <a href="register.php" class="btn btn-light text-success rounded-pill px-4 me-2 mb-2 mb-md-0">
                        <i class="fas fa-user-plus me-2"></i> Daftar Sekarang
                    </a>
                    <a href="programs.php" class="btn btn-outline-light rounded-pill px-4">
                        <i class="fas fa-book-open me-2"></i> Lihat Program
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- JavaScript for Contact Form -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const contactForm = document.getElementById('contactForm');
        
        if (contactForm) {
            contactForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Simple validation
                const name = document.getElementById('name').value;
                const email = document.getElementById('email').value;
                const subject = document.getElementById('subject').value;
                const message = document.getElementById('message').value;
                
                if (!name || !email || !subject || !message) {
                    alert('Silakan lengkapi semua kolom yang ditandai wajib (*)');
                    return;
                }
                
                // Here you would typically send the form data to your server
                // For demo purposes, we'll just show a success message
                alert('Terima kasih! Pesan Anda telah terkirim. Kami akan menghubungi Anda segera.');
                contactForm.reset();
            });
        }
    });
</script>

<?php include 'templates/footer.php'; ?> 