<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';

// Data desa
$desa_nama = "Desa Winduaji";
$desa_lokasi = "Kecamatan Paninggaran, Kabupaten Pekalongan, Provinsi Jawa Tengah";
$desa_motto = "Bersama Membangun Desa yang Mandiri dan Berbudaya";

// Ambil data galeri dari database
try {
    $stmt = $pdo->query("SELECT g.*, gk.nama_kategori 
                         FROM galeri g 
                         JOIN galeri_kategori gk ON g.kategori_id = gk.id 
                         WHERE g.tipe = 'foto' AND g.status = 'published'
                         ORDER BY g.tanggal_upload DESC 
                         LIMIT 12");
    $galeri = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Fallback jika terjadi error
    $galeri = [];
    error_log("Error mengambil data galeri: " . $e->getMessage());
}

// Ambil foto utama untuk background hero section
$bg_utama = '../assets/images/landingpage/default-bg.jpg'; // Default background
try {
    $stmt = $pdo->query("SELECT file_path FROM galeri WHERE tipe = 'foto' AND status = 'published' ORDER BY RAND() LIMIT 1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result && file_exists('../' . $result['file_path'])) {
        $bg_utama = '../' . $result['file_path'];
    }
} catch (PDOException $e) {
    error_log("Error mengambil background utama: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeri Desa <?php echo htmlspecialchars($desa_nama); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-red: #DC2626;
            --dark-red: #B91C1C;
            --light-red: #FEE2E2;
            --pure-white: #FFFFFF;
            --off-white: #F9FAFB;
            --light-gray: #F3F4F6;
            --medium-gray: #6B7280;
            --dark-gray: #1F2937;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Open Sans', sans-serif;
            color: var(--dark-gray);
            background-color: var(--off-white);
            line-height: 1.8;
            scroll-behavior: smooth;
            overflow-x: hidden;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
        }

        /* Navbar Modern */
        .navbar {
            background: rgba(255, 255, 255, 0.98);
            padding: 1rem 0;
            box-shadow: var(--shadow-sm);
            transition: all 0.4s ease;
            border-bottom: 1px solid var(--light-gray);
        }

        .navbar.scrolled {
            padding: 0.6rem 0;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(5px);
        }

        .navbar-brand img {
            height: 50px;
            transition: all 0.4s ease;
        }

        .navbar.scrolled .navbar-brand img {
            height: 40px;
        }

        .nav-link {
            color: var(--dark-gray) !important;
            font-weight: 600;
            padding: 0.5rem 1.2rem;
            margin: 0 0.2rem;
            border-radius: 30px;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 3px;
            background: var(--primary-red);
            transition: width 0.3s ease;
        }

        .nav-link:hover::before,
        .nav-link.active::before {
            width: 70%;
        }

        .nav-link:hover,
        .nav-link.active {
            color: var(--primary-red) !important;
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(rgba(220, 38, 38, 0.85), rgba(185, 28, 28, 0.85)),
                url('<?php echo $bg_utama; ?>') center/cover no-repeat;
            color: var(--pure-white);
            padding: 150px 0 100px;
            position: relative;
            overflow: hidden;
            clip-path: polygon(0 0, 100% 0, 100% 90%, 0 100%);
        }

        .hero-section::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 100px;
            background: var(--off-white);
            clip-path: polygon(0 80%, 100% 0, 100% 100%, 0 100%);
            z-index: 1;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .hero-content h1 {
            font-size: 3.2rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 1.5rem;
            animation: fadeInDown 1s both;
        }

        /* Button Styles */
        .btn-primary-custom {
            background: linear-gradient(135deg, var(--primary-red), var(--dark-red));
            color: var(--pure-white);
            border: none;
            padding: 12px 32px;
            border-radius: 50px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.4s ease;
            box-shadow: var(--shadow-sm);
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .btn-primary-custom:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
            color: var(--pure-white);
            background: linear-gradient(135deg, var(--dark-red), var(--primary-red));
        }

        /* Section Title */
        .section-title {
            position: relative;
            display: inline-block;
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-red);
            margin-bottom: 1.5rem;
            padding-bottom: 0.8rem;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(to right, var(--primary-red), var(--dark-red));
            border-radius: 2px;
        }

        .section-subtitle {
            color: var(--medium-gray);
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }

        /* Galeri Styles */
        .gallery-section {
            padding: 80px 0;
            background-color: var(--off-white);
        }

        .gallery-container {
            padding: 0 15px;
        }

        .gallery-item {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: all 0.4s ease;
            position: relative;
            margin-bottom: 20px;
            height: 250px;
        }

        .gallery-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.7), transparent 50%);
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 1;
        }

        .gallery-item:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-lg);
        }

        .gallery-item:hover::before {
            opacity: 1;
        }

        .gallery-item img {
            height: 100%;
            width: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .gallery-item:hover img {
            transform: scale(1.05);
        }

        .gallery-caption {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            padding: 15px;
            color: white;
            z-index: 2;
            transform: translateY(100%);
            transition: transform 0.3s ease;
            background: linear-gradient(to top, rgba(220, 38, 38, 0.9), transparent);
        }

        .gallery-item:hover .gallery-caption {
            transform: translateY(0);
        }

        .gallery-caption h5 {
            font-weight: 600;
            margin-bottom: 0.3rem;
        }

        /* Filter Controls */
        .filter-controls {
            margin-bottom: 30px;
            text-align: center;
        }

        .filter-btn {
            background: none;
            border: none;
            color: var(--medium-gray);
            font-weight: 600;
            padding: 8px 20px;
            margin: 0 5px;
            border-radius: 30px;
            transition: all 0.3s ease;
        }

        .filter-btn:hover,
        .filter-btn.active {
            background: var(--primary-red);
            color: white;
        }

        /* Footer */
        .footer {
            background: linear-gradient(135deg, var(--dark-red), var(--primary-red));
            color: var(--pure-white);
            padding: 4rem 0 2rem;
            position: relative;
            clip-path: polygon(0 5%, 100% 0, 100% 100%, 0 100%);
        }

        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('../assets/images/pattern.png') center/cover;
            opacity: 0.05;
            z-index: 1;
        }

        .footer-content {
            position: relative;
            z-index: 2;
        }

        .footer-logo {
            height: 50px;
            margin-bottom: 1rem;
        }

        .footer-links h5 {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1.2rem;
            position: relative;
            display: inline-block;
        }

        .footer-links h5::after {
            content: '';
            position: absolute;
            bottom: -6px;
            left: 0;
            width: 40px;
            height: 2px;
            background: var(--pure-white);
        }

        .footer-links ul {
            list-style: none;
            padding: 0;
        }

        .footer-links li {
            margin-bottom: 0.6rem;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .footer-links a:hover {
            color: var(--pure-white);
            padding-left: 5px;
        }

        .social-icons a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            color: var(--pure-white);
            margin-right: 0.6rem;
            transition: all 0.3s ease;
        }

        .social-icons a:hover {
            background: var(--pure-white);
            color: var(--primary-red);
            transform: translateY(-3px);
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 1.5rem;
            margin-top: 2rem;
        }

        /* Back to Top Button */
        .back-to-top {
            position: fixed;
            bottom: 25px;
            right: 25px;
            width: 45px;
            height: 45px;
            background: var(--primary-red);
            color: var(--pure-white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-md);
            transition: all 0.4s ease;
            z-index: 99;
            border: 2px solid var(--pure-white);
        }

        .back-to-top:hover {
            background: var(--dark-red);
            transform: translateY(-3px);
        }

        /* Animations */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Design */
        @media (max-width: 991.98px) {
            .hero-section {
                padding: 120px 0 80px;
                clip-path: polygon(0 0, 100% 0, 100% 95%, 0 100%);
            }

            .hero-content h1 {
                font-size: 2.5rem;
            }

            .section-title {
                font-size: 2rem;
            }
        }

        @media (max-width: 767.98px) {
            .hero-section {
                padding: 100px 0 60px;
                background-attachment: scroll;
            }

            .hero-content h1 {
                font-size: 2rem;
            }

            .gallery-item {
                height: 200px;
            }
        }

        @media (max-width: 575.98px) {
            .hero-section {
                padding: 90px 0 50px;
                clip-path: polygon(0 0, 100% 0, 100% 97%, 0 100%);
            }

            .hero-content h1 {
                font-size: 1.8rem;
                line-height: 1.3;
            }

            .section-title {
                font-size: 1.6rem;
            }

            .filter-btn {
                padding: 6px 12px;
                margin: 0 3px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand" href="../landingpage.php">
                <img src="../assets/images/logo.png" alt="Logo <?php echo htmlspecialchars($desa_nama); ?>" class="img-fluid">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../landingpage.php">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#sejarah">Sejarah</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#demografi">Demografi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="#umkm">UMKM</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../pages/galeri.php">Galeri</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../pages/berita.php">Berita</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../login.php">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container hero-content text-center">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <h1 class="display-4 fw-bold mb-4 animate__animated animate__fadeInDown">Galeri Desa <?php echo htmlspecialchars($desa_nama); ?></h1>
                    <p class="lead mb-5 animate__animated animate__fadeIn animate__delay-1s">Momen dan aktivitas warga <?php echo htmlspecialchars($desa_nama); ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Gallery Section -->
    <section class="gallery-section">
        <div class="container">
            <div class="text-center mb-5" data-aos="zoom-in">
                <h2 class="fw-bold section-title">Koleksi Foto Desa</h2>
                <p class="section-subtitle">Dokumentasi kegiatan dan keindahan <?php echo htmlspecialchars($desa_nama); ?></p>
            </div>

            <!-- Filter Controls -->
            <div class="filter-controls" data-aos="fade-up">
                <button class="filter-btn active" data-filter="all">Semua</button>
                <button class="filter-btn" data-filter="kegiatan">Kegiatan</button>
                <button class="filter-btn" data-filter="alam">Pemandangan</button>
                <button class="filter-btn" data-filter="fasilitas">Fasilitas</button>
            </div>

            <!-- Gallery Grid -->
            <div class="row gallery-container">
                <?php if (!empty($galeri)): ?>
                    <?php foreach ($galeri as $item): ?>
                        <div class="col-md-6 col-lg-4 col-xl-3 gallery-item" data-category="<?php echo strtolower(str_replace(' ', '-', $item['nama_kategori'])); ?>">
                            <img src="../<?php echo htmlspecialchars($item['file_path']); ?>" class="img-fluid w-100" alt="<?php echo htmlspecialchars($item['judul']); ?>">
                            <div class="gallery-caption">
                                <h5><?php echo htmlspecialchars($item['judul']); ?></h5>
                                <small class="d-block"><i class="fas fa-calendar-alt me-1"></i> <?php echo date('d F Y', strtotime($item['tanggal_upload'])); ?></small>
                                <small class="d-block"><i class="fas fa-tag me-1"></i> <?php echo htmlspecialchars($item['nama_kategori']); ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> Belum ada foto yang tersedia
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Tombol Lihat Semua -->
            <div class="text-center mt-4">
                <a href="../pages/galeri.php" class="btn btn-primary-custom">
                    <i class="fas fa-images me-2"></i> Lihat Semua Foto
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container footer-content">
            <div class="row g-4">
                <div class="col-lg-3">
                    <div class="mb-4">
                        <img src="../assets/images/logo.png" alt="Logo <?php echo htmlspecialchars($desa_nama); ?>" class="footer-logo">
                    </div>
                    <p class="mb-4"><?php echo htmlspecialchars($desa_motto); ?></p>
                    <div class="social-icons">
                        <a href="#" class="me-2"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="me-2"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="me-2"><i class="fab fa-youtube"></i></a>
                        <a href="#" class="me-2"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4">
                    <div class="footer-links">
                        <h5>Menu</h5>
                        <ul>
                            <li><a href="../landingpage.php">Beranda</a></li>
                            <li><a href="index.php#sejarah">Sejarah</a></li>
                            <li><a href="index.php#demografi">Demografi</a></li>
                            <li><a href="index.php#umkm">UMKM</a></li>
                            <li><a href="../pages/galeri.php">Galeri</a></li>
                            <li><a href="../pages/berita.php">Berita</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4">
                    <div class="footer-links">
                        <h5>Layanan</h5>
                        <ul>
                            <li><a href="#">Administrasi</a></li>
                            <li><a href="#">Kesehatan</a></li>
                            <li><a href="#">Pendidikan</a></li>
                            <li><a href="#">Pelaporan</a></li>
                            <li><a href="#">Pengaduan</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-5 col-md-4">
                    <div class="footer-links">
                        <h5>Kontak & Lokasi</h5>
                        <ul>
                            <li>
                                <i class="fas fa-map-marker-alt me-2"></i>
                                <?php echo htmlspecialchars($desa_lokasi); ?>
                            </li>
                            <li>
                                <i class="fas fa-phone-alt me-2"></i>
                                (021) 1234-5678
                            </li>
                            <li>
                                <i class="fas fa-envelope me-2"></i>
                                info@<?php echo strtolower(str_replace(' ', '', $desa_nama)); ?>.id
                            </li>
                        </ul>
                        <div class="map-container mt-3">
                            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3953.123456789012!2d109.5597842!3d-7.1641082!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e6fe2bac929ba3d%3A0x5027a76e35648c0!2sWinduaji%2C%20Kec.%20Paninggaran%2C%20Kabupaten%20Pekalongan%2C%20Jawa%20Tengah!5e0!3m2!1sen!2sid!4v1710000000000!5m2!1sen!2sid" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-bottom text-center pt-4">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($desa_nama); ?>. Seluruh hak cipta dilindungi.</p>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <a href="#" class="back-to-top animate__animated animate__fadeIn"><i class="fas fa-arrow-up"></i></a>

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/waypoints/4.0.1/jquery.waypoints.min.js"></script>

    <!-- Main JavaScript -->
    <script>
        // Initialize AOS animation
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });

        // Navbar scroll effect
        $(window).scroll(function() {
            if ($(this).scrollTop() > 100) {
                $('.navbar').addClass('scrolled');
            } else {
                $('.navbar').removeClass('scrolled');
            }
        });

        // Back to top button
        $(window).scroll(function() {
            if ($(this).scrollTop() > 300) {
                $('.back-to-top').fadeIn('slow');
            } else {
                $('.back-to-top').fadeOut('slow');
            }
        });

        // Filter gallery items
        $('.filter-btn').on('click', function() {
            // Remove active class from all buttons
            $('.filter-btn').removeClass('active');
            // Add active class to clicked button
            $(this).addClass('active');
            
            const filterValue = $(this).attr('data-filter');
            
            if (filterValue === 'all') {
                $('.gallery-item').show();
            } else {
                $('.gallery-item').hide();
                $(`.gallery-item[data-category*="${filterValue}"]`).show();
            }
        });

        // Smooth scrolling for navigation links
        $('a[href*="#"]').on('click', function(e) {
            e.preventDefault();
            $('html, body').animate({
                scrollTop: $($(this).attr('href')).offset().top - 70
            }, 500, 'linear');
        });
    </script>
</body>
</html>