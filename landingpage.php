<?php
session_start();
require_once './config/database.php';
require_once './config/functions.php';

// Data desa
$desa_nama = "Desa Winduaji";
$desa_lokasi = "Kecamatan Paninggaran, Kabupaten Pekalongan, Provinsi Jawa Tengah";
$desa_motto = "Bersama Membangun Desa yang Mandiri dan Berbudaya";

// Data kependudukan
$penduduk = 12500;
$laki_laki = 6200;
$perempuan = 6300;
$kk = 3200;
$rw = 15;
$rt = 75;
$luas = 8.75;

// Sejarah desa
$sejarah = "Desa Winduaji, yang terletak di kecamatan Paninggaran, Kabupaten Pekalongan, Jawa Tengah, memiliki sejarah yang berkaitan erat dengan keberadaan wilayah tersebut. Desa ini terdiri dari empat dukuh: Plumbon, Winduaji, Simbang, dan Sidomas. Pada Januari 2009, desa ini memiliki 3.235 penduduk dengan mata pencaharian sebagian besar di sektor pertanian, menurut P2K Stekom. 
Sejarah Desa Winduaji tidak secara spesifik dijelaskan dalam informasi yang tersedia, namun dapat diasumsikan bahwa sejarahnya terkait dengan sejarah pembentukan kecamatan Paninggaran dan perkembangan wilayah Kabupaten Pekalongan secara umum. Kecamatan Paninggaran sendiri merupakan salah satu dari 19 kecamatan di Kabupaten Pekalongan. ";

// Potensi UMKM
$umkm_types = [
    [
        'icon' => 'fas fa-cookie-bite', 
        'nama' => 'Kerupuk Paninggaran', 
        'deskripsi' => 'Kerupuk renyah khas Paninggaran dengan cita rasa gurih dan tekstur kriuk.'
    ],
    [
        'icon' => 'fas fa-mug-hot', 
        'nama' => 'Teh Biyung', 
        'deskripsi' => 'Teh alami dengan aroma khas dan rasa yang menenangkan, diproses secara tradisional.'
    ],
    [
        'icon' => 'fas fa-wine-bottle', 
        'nama' => 'Oga Jahe', 
        'deskripsi' => 'Minuman jahe tradisional yang hangat, sehat, dan menyegarkan.'
    ],
    [
        'icon' => 'fas fa-bread-slice', 
        'nama' => 'Opak', 
        'deskripsi' => 'Camilan opak gurih dan renyah, cocok untuk teman minum teh atau kopi.'
    ]
];

// Galeri
$galeri = [
    'assets/images/landingpage/bgutama.jepg',
    'assets/images/landingpage/bgutama.jepg',
    'assets/images/landingpage/bgutama.jepg',
    'assets/images/landingpage/bgutama.jepg',
    'assets/images/landingpage/bgutama.jepg',
    'assets/images/landingpage/bgutama.jepg',
];

// Berita
$berita = [
    [
        'judul' => 'Gebyar Festival Kemerdekaan Indonesia',
        'tanggal' => '2023-08-15',
        'isi' => 'Gebyar Festival Kemerdekaan Indonesia berhasil menarik lebih dari 5.000 pengunjung dengan berbagai pertunjukan seni dan kuliner tradisional.',
        'gambar' => 'assets/images/berita-1.jpg'
    ],
    [
        'judul' => 'Desa Winduaji Raih Penghargaan Adipura',
        'tanggal' => '2023-07-28',
        'isi' => 'Atas prestasinya dalam pengelolaan lingkungan, desa kita meraih penghargaan Adipura tingkat kabupaten.',
        'gambar' => 'assets/images/berita-2.jpg'
    ],
    [
        'judul' => 'Pelatihan Digital Marketing untuk UMKM dari KKN ITSNU Pekalongan',
        'tanggal' => '2023-09-02',
        'isi' => 'Pemerintah desa bekerjasama dengan dinas terkait menyelenggarakan pelatihan pemasaran digital bagi pelaku UMKM.',
        'gambar' => 'assets/images/berita-3.jpg'
    ]
];

// Testimoni
$testimonials = [
    [
        'name' => 'Bapak Sutrisno',
        'role' => 'Ketua RW 03',
        'comment' => 'Desa kita semakin maju dengan berbagai program inovatif dari pemerintah desa dan partisipasi aktif warga.',
        'rating' => 5
    ],
    [
        'name' => 'Ibu Siti Aminah',
        'role' => 'Pengusaha Kerajinan',
        'comment' => 'Berkat pelatihan dan pendampingan dari desa, produk kerajinan kami sekarang bisa dipasarkan hingga luar daerah.',
        'rating' => 4
    ],
    [
        'name' => 'Pak Darman',
        'role' => 'Ketua Kelompok Tani',
        'comment' => 'Sistem pertanian organik yang dikembangkan desa benar-benar meningkatkan hasil panen kami secara signifikan.',
        'rating' => 5
    ]
];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Desa <?php echo $desa_nama; ?></title>
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

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
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
                url('assets/images/landingpage/bgutama.jpg') center/cover no-repeat;
            color: var(--pure-white);
            padding: 180px 0 150px;
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
            font-size: 3.8rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 1.5rem;
            animation: fadeInDown 1s both;
        }

        .hero-content .lead {
            font-size: 1.4rem;
            margin-bottom: 2.5rem;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
            animation: fadeIn 1s both 0.3s;
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

        .btn-outline-custom {
            border: 2px solid var(--pure-white);
            color: var(--pure-white);
            background: transparent;
            padding: 10px 28px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.4s ease;
        }

        .btn-outline-custom:hover {
            background: var(--pure-white);
            color: var(--primary-red);
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
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

        /* Stats Section */
        .stats-section {
            background: linear-gradient(135deg, var(--primary-red), var(--dark-red));
            color: var(--pure-white);
            padding: 70px 0;
            margin-top: -70px;
            clip-path: polygon(0 10%, 100% 0, 100% 90%, 0 100%);
            position: relative;
            z-index: 2;
        }

        .stats-item {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(8px);
            border-radius: 16px;
            padding: 2rem;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.4s ease;
            box-shadow: var(--shadow-sm);
        }

        .stats-item:hover {
            transform: translateY(-8px);
            background: rgba(255, 255, 255, 0.25);
            box-shadow: var(--shadow-lg);
        }

        .stats-number {
            font-size: 3rem;
            font-weight: 800;
            color: var(--pure-white);
            margin-bottom: 0.5rem;
        }

        .stats-label {
            font-size: 1.1rem;
            color: var(--pure-white);
            opacity: 0.9;
            font-weight: 500;
        }

        /* UMKM Card */
        .umkm-card {
            background: var(--pure-white);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: all 0.4s ease;
            border: none;
            height: 100%;
        }

        .umkm-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
        }

        .umkm-icon {
            font-size: 2.8rem;
            color: var(--primary-red);
            margin-bottom: 1.2rem;
            transition: all 0.4s ease;
        }

        .umkm-card:hover .umkm-icon {
            transform: scale(1.1);
            color: var(--dark-red);
        }

        .umkm-card .card-body {
            padding: 2rem;
        }

        /* Galeri Section */
        .gallery-item {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: all 0.4s ease;
            position: relative;
        }

        .gallery-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.5), transparent 50%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .gallery-item:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-lg);
        }

        .gallery-item:hover::before {
            opacity: 1;
        }

        .gallery-item img {
            height: 220px;
            width: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .gallery-item:hover img {
            transform: scale(1.05);
        }

        /* Testimoni Card */
        .testimonial-card {
            background: var(--pure-white);
            border-radius: 12px;
            padding: 2rem;
            box-shadow: var(--shadow-sm);
            transition: all 0.4s ease;
            position: relative;
            border-top: 4px solid var(--primary-red);
            height: 100%;
        }

        .testimonial-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
        }

        .rating {
            color: var(--primary-red);
            font-size: 1rem;
            margin-bottom: 1rem;
        }

        /* Berita Card */
        .news-card {
            background: var(--pure-white);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: all 0.4s ease;
            height: 100%;
        }

        .news-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-lg);
        }

        .news-card .card-img-top {
            height: 200px;
            object-fit: cover;
        }

        .news-card .card-body {
            padding: 1.5rem;
        }

        .news-date {
            color: var(--primary-red);
            font-weight: 500;
            font-size: 0.9rem;
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
            background: url('assets/images/pattern.png') center/cover;
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
            transition: all 0.4s ease;

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

        /* Contact Info in Footer */
        .contact-info-footer {
            background: rgba(0, 0, 0, 0.1);
            padding: 2rem;
            border-radius: 10px;
            margin-top: 2rem;
        }

        .contact-icon-footer {
            font-size: 1.5rem;
            color: var(--pure-white);
            margin-right: 1rem;
        }

        /* Map Container */
        .map-container {
            height: 300px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            margin-top: 2rem;
            position: relative;
            transition: all 0.4s ease;
            
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

        /* Contact Card */
        .contact-card {
            background: var(--pure-white);
            border-radius: 12px;
            padding: 2rem;
            box-shadow: var(--shadow-sm);
            transition: all 0.4s ease;
        }

        .contact-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .contact-icon {
            font-size: 2.5rem;
            color: var(--primary-red);
            margin-bottom: 1rem;
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

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        /* Responsive Design */
        @media (max-width: 1199.98px) {
            .hero-content h1 {
                font-size: 3.2rem;
            }
        }

        @media (max-width: 991.98px) {
            .hero-section {
                padding: 150px 0 120px;
                clip-path: polygon(0 0, 100% 0, 100% 95%, 0 100%);
            }

            .hero-content h1 {
                font-size: 2.8rem;
            }

            .section-title {
                font-size: 2.2rem;
            }

            .footer-links {
                margin-bottom: 2rem;
            }
        }

        @media (max-width: 767.98px) {
            .hero-section {
                padding: 120px 0 100px;
                background-attachment: scroll;
            }

            .hero-content h1 {
                font-size: 2.4rem;
            }

            .hero-content .lead {
                font-size: 1.2rem;
            }

            .stats-section {
                padding: 60px 0;
                margin-top: -60px;
            }

            .stats-number {
                font-size: 2.5rem;
            }

            /* Center align navbar items on mobile */
            .navbar-collapse {
                text-align: center;
            }

            .nav-link {
                display: inline-block;
                margin: 0.5rem 0;
            }

            .map-container {
                height: 200px;
            }
        }

        @media (max-width: 575.98px) {
            .hero-section {
                padding: 100px 0 80px;
                clip-path: polygon(0 0, 100% 0, 100% 97%, 0 100%);
            }

            .hero-content h1 {
                font-size: 2rem;
                line-height: 1.3;
            }

            .section-title {
                font-size: 1.8rem;
            }

            .btn-primary-custom,
            .btn-outline-custom {
                width: 100%;
                margin-bottom: 1rem;
            }

            .map-container {
                height: 180px;
            }
        }
    </style>
</head>

<body data-bs-spy="scroll" data-bs-target=".navbar" data-bs-offset="100">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#home">
                <img src="./assets/images/logo.png" alt="Logo <?php echo $desa_nama; ?>" class="img-fluid">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#home">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#sejarah">Sejarah</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#demografi">Demografi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#umkm">UMKM</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="./pages/galeri.php">Galeri</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="./pages/berita.php">Berita</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="./login.php">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-section">
        <div class="container hero-content text-center">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <h1 class="display-3 fw-bold mb-4 animate__animated animate__fadeInDown"><?php echo $desa_nama; ?></h1>
                    <p class="lead mb-5 animate__animated animate__fadeIn animate__delay-1s"><?php echo $desa_motto; ?></p>
                    <div class="animate__animated animate__fadeIn animate__delay-2s d-flex justify-content-center gap-3 flex-wrap">
                        <a href="#sejarah" class="btn btn-primary-custom btn-lg">Pelajari Sejarah</a>
                        <a href="#umkm" class="btn btn-outline-custom btn-lg">Lihat UMKM</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-3" data-aos="fade-up">
                    <div class="stats-item">
                        <div class="stats-number" data-count="<?php echo $penduduk; ?>">0</div>
                        <div class="stats-label">Jumlah Penduduk</div>
                    </div>
                </div>
                <div class="col-md-3" data-aos="fade-up" data-aos-delay="200">
                    <div class="stats-item">
                        <div class="stats-number" data-count="<?php echo $rw; ?>">0</div>
                        <div class="stats-label">Jumlah RW</div>
                    </div>
                </div>
                <div class="col-md-3" data-aos="fade-up" data-aos-delay="400">
                    <div class="stats-item">
                        <div class="stats-number" data-count="<?php echo $rt; ?>">0</div>
                        <div class="stats-label">Jumlah RT</div>
                    </div>
                </div>
                <div class="col-md-3" data-aos="fade-up" data-aos-delay="600">
                    <div class="stats-item">
                        <div class="stats-number" data-count="<?php echo $luas; ?>">0</div>
                        <div class="stats-label">Luas Wilayah (km²)</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Sejarah Section -->
    <section id="sejarah" class="py-5 bg-white">
        <div class="container">
            <div class="text-center mb-5" data-aos="zoom-in">
                <h2 class="fw-bold section-title">Sejarah Desa</h2>
                <p class="section-subtitle">Jejak perjalanan panjang <?php echo $desa_nama; ?></p>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-10" data-aos="fade-up">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4 p-lg-5">
                            <div class="row align-items-center">
                                <div class="col-lg-6 mb-4 mb-lg-0">
                                    <img src="./assets/images/landingpage/bgutama.jpeg" alt="Sejarah <?php echo $desa_nama; ?>" class="img-fluid rounded-3 shadow">
                                </div>
                                <div class="col-lg-6">
                                    <p><?php echo nl2br($sejarah); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Demografi Section -->
    <section id="demografi" class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5" data-aos="zoom-in">
                <h2 class="fw-bold section-title">Demografi Desa</h2>
                <p class="section-subtitle">Data kependudukan dan struktur masyarakat <?php echo $desa_nama; ?></p>
            </div>

            <div class="row g-4 justify-content-center">
                <div class="col-lg-10">
                    <div class="row g-4">
                        <div class="col-md-6" data-aos="fade-right">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body p-4">
                                    <h4 class="text-center mb-4" style="color: var(--primary-red);">Struktur Kependudukan</h4>
                                    <div class="row">
                                        <div class="col-6 mb-4">
                                            <div class="text-center">
                                                <div class="fs-2 fw-bold" style="color: var(--primary-red);"><?php echo number_format($penduduk); ?></div>
                                                <div class="text-muted">Total Penduduk</div>
                                            </div>
                                        </div>
                                        <div class="col-6 mb-4">
                                            <div class="text-center">
                                                <div class="fs-2 fw-bold" style="color: var(--primary-red);"><?php echo number_format($laki_laki); ?></div>
                                                <div class="text-muted">Laki-laki</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-center">
                                                <div class="fs-2 fw-bold" style="color: var(--primary-red);"><?php echo number_format($perempuan); ?></div>
                                                <div class="text-muted">Perempuan</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-center">
                                                <div class="fs-2 fw-bold" style="color: var(--primary-red);"><?php echo number_format($kk); ?></div>
                                                <div class="text-muted">Keluarga</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6" data-aos="fade-left">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body p-4">
                                    <h4 class="text-center mb-4" style="color: var(--primary-red);">Struktur Administratif</h4>
                                    <div class="row">
                                        <div class="col-6 mb-4">
                                            <div class="text-center">
                                                <div class="fs-2 fw-bold" style="color: var(--primary-red);">8</div>
                                                <div class="text-muted">Dusun</div>
                                            </div>
                                        </div>
                                        <div class="col-6 mb-4">
                                            <div class="text-center">
                                                <div class="fs-2 fw-bold" style="color: var(--primary-red);"><?php echo $rw; ?></div>
                                                <div class="text-muted">RW</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-center">
                                                <div class="fs-2 fw-bold" style="color: var(--primary-red);"><?php echo $rt; ?></div>
                                                <div class="text-muted">RT</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-center">
                                                <div class="fs-2 fw-bold" style="color: var(--primary-red);"><?php echo $luas; ?></div>
                                                <div class="text-muted">Luas (km²)</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- UMKM Section -->
    <section id="umkm" class="py-5 bg-white">
        <div class="container">
            <div class="text-center mb-5" data-aos="zoom-in">
                <h2 class="fw-bold section-title">UMKM Desa</h2>
                <p class="section-subtitle">Potensi usaha kecil menengah di <?php echo $desa_nama; ?></p>
            </div>

            <div class="row g-4">
                <?php foreach ($umkm_types as $index => $umkm): ?>
                    <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                        <div class="umkm-card card h-100">
                            <div class="card-body text-center p-4">
                                <div class="umkm-icon">
                                    <i class="<?php echo $umkm['icon']; ?>"></i>
                                </div>
                                <h4 class="mb-3"><?php echo $umkm['nama']; ?></h4>
                                <p class="text-muted mb-4"><?php echo $umkm['deskripsi']; ?></p>
                                <a href="./pages/umkm.php" class="btn btn-outline-primary rounded-pill px-4">Lihat Detail</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Galeri Section -->
    <section id="galeri" class="py-5 bg-white">
        <div class="container">
            <div class="text-center mb-5" data-aos="zoom-in">
                <h2 class="fw-bold section-title">Galeri Desa</h2>
                <p class="section-subtitle">Momen dan aktivitas warga <?php echo $desa_nama; ?></p>
            </div>

            <div class="row g-3">
                <?php foreach ($galeri as $index => $gambar): ?>
                    <div class="col-6 col-md-4 col-lg-2" data-aos="zoom-in" data-aos-delay="<?php echo $index * 100; ?>">
                        <div class="gallery-item">
                            <img src="<?php echo $gambar; ?>" class="img-fluid w-100" alt="Galeri <?php echo $index + 1; ?>">
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Berita Section -->
    <section id="berita" class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5" data-aos="zoom-in">
                <h2 class="fw-bold section-title">Berita Terkini</h2>
                <p class="section-subtitle">Informasi dan kegiatan terbaru dari <?php echo $desa_nama; ?></p>
            </div>

            <div class="row g-4">
                <?php foreach ($berita as $index => $item): ?>
                    <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="<?php echo $index * 150; ?>">
                        <div class="news-card card h-100 border-0 shadow-sm">
                            <img src="<?php echo $item['gambar']; ?>" class="card-img-top" alt="<?php echo $item['judul']; ?>">
                            <div class="card-body">
                                <span class="news-date d-block mb-2">
                                    <i class="far fa-calendar-alt me-2"></i><?php echo date('d F Y', strtotime($item['tanggal'])); ?>
                                </span>
                                <h5 class="card-title mb-3"><?php echo $item['judul']; ?></h5>
                                <p class="card-text text-muted"><?php echo $item['isi']; ?></p>
                            </div>
                         
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-5" data-aos="fade-up">
                <a href="./berita.php" class="btn btn-primary-custom px-4 py-2">Lihat Semua Berita</a>
            </div>
        </div>
    </section>

    <!-- Testimoni Section -->
    <section class="py-5 bg-white">
        <div class="container">
            <div class="text-center mb-5" data-aos="zoom-in">
                <h2 class="fw-bold section-title">Apa Kata Mereka</h2>
                <p class="section-subtitle">Testimoni warga tentang perkembangan <?php echo $desa_nama; ?></p>
            </div>

            <div class="row g-4">
                <?php foreach ($testimonials as $index => $testi): ?>
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="<?php echo $index * 150; ?>">
                        <div class="testimonial-card h-100">
                            <div class="rating mb-3">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star<?php echo $i > $testi['rating'] ? '-empty' : ''; ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="mb-4">"<?php echo $testi['comment']; ?>"</p>
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <img src="assets/images/testi-<?php echo $index + 1; ?>.jpg" class="rounded-circle" width="50" height="50" alt="<?php echo $testi['name']; ?>">
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-0"><?php echo $testi['name']; ?></h6>
                                    <small class="text-muted"><?php echo $testi['role']; ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

   

    <!-- Footer with Map -->
    <footer class="footer">
        <div class="container footer-content">
            <div class="row g-4">
                <div class="col-lg-3">
                    <div class="mb-4">
                        <img src="./assets/images/logo.png" alt="Logo <?php echo $desa_nama; ?>" class="footer-logo">
                    </div>
                    <p class="mb-4"><?php echo $desa_motto; ?></p>
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
                            <li><a href="#home">Beranda</a></li>
                            <li><a href="#sejarah">Sejarah</a></li>
                            <li><a href="#demografi">Demografi</a></li>
                            <li><a href="#umkm">UMKM</a></li>
                            <li><a href="./galeri.php">Galeri</a></li>
                            <li><a href="./berita.php">Berita</a></li>
                            
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
                                <?php echo $desa_lokasi; ?>
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
                <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo $desa_nama; ?>. Seluruh hak cipta dilindungi.</p>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Counter-Up/1.0.0/jquery.counterup.min.js"></script>

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

        // Smooth scrolling for navigation links
        $('a[href*="#"]').on('click', function(e) {
            e.preventDefault();
            $('html, body').animate({
                scrollTop: $($(this).attr('href')).offset().top - 70
            }, 500, 'linear');
        });

        // Back to top button
        $(window).scroll(function() {
            if ($(this).scrollTop() > 300) {
                $('.back-to-top').fadeIn('slow');
            } else {
                $('.back-to-top').fadeOut('slow');
            }
        });

        // Counter animation for stats
        $('.stats-number').each(function() {
            $(this).prop('Counter', 0).animate({
                Counter: $(this).text()
            }, {
                duration: 2000,
                easing: 'swing',
                step: function(now) {
                    if ($(this).data('count') > 1000) {
                        $(this).text(Math.ceil(now).toLocaleString());
                    } else if ($(this).data('count').toString().includes('.')) {
                        $(this).text(Math.ceil(now * 100) / 100);
                    } else {
                        $(this).text(Math.ceil(now));
                    }
                }
            });
        });

        // Active nav link on scroll
        $(window).scroll(function() {
            var scrollDistance = $(window).scrollTop() + 100;
            
            $('section').each(function(i) {
                if ($(this).position().top <= scrollDistance && $(this).position().top + $(this).height() > scrollDistance) {
                    $('.navbar a.nav-link').removeClass('active');
                    $('.navbar a.nav-link[href="#' + $(this).attr('id') + '"]').addClass('active');
                }
            });
        }).scroll();
    </script>
</body>
</html>