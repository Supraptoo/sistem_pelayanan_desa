<?php
session_start();
require_once './config/database.php';
require_once './config/functions.php';

// Koneksi database
$db = new database();
$conn = $db->getConnection();

// Data desa dari database
$query = "SELECT * FROM desa_profil LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->execute();
$desa_profil = $stmt->fetch(PDO::FETCH_ASSOC);

$desa_nama = $desa_profil['nama_desa'] ?? "Desa Winduaji";
$desa_lokasi = $desa_profil['lokasi'] ?? "Kecamatan Paninggaran, Kabupaten Pekalongan, Provinsi Jawa Tengah";
$desa_motto = $desa_profil['motto'] ?? "Bersama Membangun Desa yang Mandiri dan Berbudaya";
$sejarah = $desa_profil['sejarah'] ?? "Desa Winduaji, yang terletak di kecamatan Paninggaran...";

// Data demografi dari database
$query = "SELECT * FROM demografi LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->execute();
$demografi = $stmt->fetch(PDO::FETCH_ASSOC);

$penduduk = $demografi['total_penduduk'] ?? 12500;
$laki_laki = $demografi['laki_laki'] ?? 6200;
$perempuan = $demografi['perempuan'] ?? 6300;
$kk = $demografi['jumlah_kk'] ?? 3200;
$rw = $demografi['jumlah_rw'] ?? 15;
$rt = $demografi['jumlah_rt'] ?? 75;
$luas = $demografi['luas_wilayah'] ?? 8.75;

// Data UMKM dari database
$query = "SELECT uk.nama_kategori, uk.icon, COUNT(u.id) as jumlah 
          FROM umkm_kategori uk 
          LEFT JOIN umkm u ON uk.id = u.kategori_id 
          GROUP BY uk.id";
$stmt = $conn->prepare($query);
$stmt->execute();
$umkm_kategori = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Data galeri dari database (ambil 6 terbaru)
$query = "SELECT * FROM galeri WHERE status = 'published' ORDER BY created_at DESC LIMIT 6";
$stmt = $conn->prepare($query);
$stmt->execute();
$galeri = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Data berita dari database (ambil 3 terbaru)
$query = "SELECT b.*, bk.nama_kategori 
          FROM berita b 
          JOIN berita_kategori bk ON b.kategori_id = bk.id 
          WHERE b.status = 'published' 
          ORDER BY b.tanggal_publish DESC LIMIT 3";
$stmt = $conn->prepare($query);
$stmt->execute();
$berita = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Data testimoni dari database
$query = "SELECT * FROM testimoni ORDER BY created_at DESC LIMIT 3";
$stmt = $conn->prepare($query);
$stmt->execute();
$testimoni = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Data kontak dari database
$query = "SELECT * FROM kontak LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->execute();
$kontak = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Desa Winduaji - Paninggaran, Pekalongan</title>
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --accent: #e74c3c;
            --light: #ecf0f1;
            --dark: #2c3e50;
            --text: #333;
            --text-light: #7f8c8d;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f9f9f9;
            color: var(--text);
            line-height: 1.6;
            overflow-x: hidden;
        }
        
        /* Header Styles */
        header {
            background: linear-gradient(135deg, var(--primary), var(--dark));
            color: white;
            padding: 1rem 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        header.scrolled {
            padding: 0.5rem 0;
            background: rgba(44, 62, 80, 0.95);
        }
        
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .logo img {
            height: 50px;
            width: auto;
        }
        
        .logo-text h1 {
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .logo-text p {
            font-size: 0.8rem;
            opacity: 0.8;
        }
        
        nav ul {
            display: flex;
            list-style: none;
            gap: 2rem;
        }
        
        nav a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            position: relative;
            padding: 0.5rem 0;
            transition: all 0.3s ease;
        }
        
        nav a:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background-color: var(--accent);
            transition: width 0.3s ease;
        }
        
        nav a:hover:after {
            width: 100%;
        }
        
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
        }
        
        /* Hero Section */
        .hero {
            height: 100vh;
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('./assets/images/landingpage/bgutama.jpeg');
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            margin-top: 72px;
        }
        
        .hero-content {
            max-width: 800px;
            padding: 0 2rem;
            animation: fadeInUp 1s ease;
        }
        
        .hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 5px rgba(0,0,0,0.5);
        }
        
        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.5);
        }
        
        .btn {
            display: inline-block;
            padding: 0.8rem 2rem;
            background-color: var(--accent);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 2px solid var(--accent);
        }
        
        .btn:hover {
            background-color: transparent;
            color: var(--accent);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .btn-outline {
            background-color: transparent;
            color: white;
            border: 2px solid white;
        }
        
        .btn-outline:hover {
            background-color: white;
            color: var(--primary);
        }
        
        /* Section Styles */
        section {
            padding: 5rem 0;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .section-title h2 {
            font-size: 2.5rem;
            color: var(--primary);
            position: relative;
            display: inline-block;
            padding-bottom: 1rem;
        }
        
        .section-title h2:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background-color: var(--accent);
        }
        
        .section-title p {
            color: var(--text-light);
            max-width: 700px;
            margin: 1rem auto 0;
        }
        
        /* Profil Desa Section */
        .profil-desa {
            background-color: white;
        }
        
        .profil-container {
            display: flex;
            align-items: center;
            gap: 3rem;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        .profil-image {
            flex: 1;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            animation: fadeInLeft 1s ease;
        }
        
        .profil-image img {
            width: 100%;
            height: auto;
            display: block;
        }
        
        .profil-content {
            flex: 1;
            animation: fadeInRight 1s ease;
        }
        
        .profil-content h3 {
            font-size: 1.8rem;
            color: var(--primary);
            margin-bottom: 1.5rem;
        }
        
        .profil-content p {
            margin-bottom: 1.5rem;
        }
        
        /* UMKM Section */
        .umkm {
            background-color: var(--light);
        }
        
        .umkm-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        .umkm-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
        }
        
        .umkm-card {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .umkm-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        
        .umkm-img {
            height: 200px;
            overflow: hidden;
        }
        
        .umkm-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .umkm-card:hover .umkm-img img {
            transform: scale(1.1);
        }
        
        .umkm-content {
            padding: 1.5rem;
        }
        
        .umkm-content h3 {
            font-size: 1.3rem;
            margin-bottom: 0.5rem;
            color: var(--primary);
        }
        
        .umkm-content p {
            color: var(--text-light);
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }
        
        .umkm-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
        }
        
        .umkm-kategori {
            background-color: var(--secondary);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 50px;
            font-size: 0.8rem;
        }
        
        .lihat-semua {
            text-align: center;
            margin-top: 3rem;
        }
        
        /* Berita Section */
        .berita-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        .berita-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
        }
        
        .berita-card {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .berita-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .berita-img {
            height: 200px;
            overflow: hidden;
        }
        
        .berita-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .berita-card:hover .berita-img img {
            transform: scale(1.1);
        }
        
        .berita-content {
            padding: 1.5rem;
        }
        
        .berita-date {
            display: block;
            color: var(--secondary);
            font-size: 0.8rem;
            margin-bottom: 0.5rem;
        }
        
        .berita-content h3 {
            font-size: 1.2rem;
            margin-bottom: 1rem;
            color: var(--primary);
        }
        
        .berita-content p {
            color: var(--text-light);
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }
        
        /* Galeri Section */
        .galeri {
            background-color: var(--light);
        }
        
        .galeri-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        .galeri-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        
        .galeri-item {
            position: relative;
            border-radius: 10px;
            overflow: hidden;
            height: 250px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .galeri-item:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .galeri-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .galeri-item:hover img {
            transform: scale(1.1);
        }
        
        .galeri-caption {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.7));
            color: white;
            padding: 1rem;
            transform: translateY(100%);
            transition: transform 0.3s ease;
        }
        
        .galeri-item:hover .galeri-caption {
            transform: translateY(0);
        }
        
        /* Kontak Section */
        .kontak {
            background-color: white;
        }
        
        .kontak-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            gap: 3rem;
        }
        
        .kontak-info {
            flex: 1;
        }
        
        .kontak-info h3 {
            font-size: 1.5rem;
            color: var(--primary);
            margin-bottom: 1.5rem;
        }
        
        .kontak-detail {
            margin-bottom: 2rem;
        }
        
        .kontak-detail p {
            margin-bottom: 1rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }
        
        .kontak-detail i {
            color: var(--secondary);
            font-size: 1.2rem;
        }
        
        .social-media {
            display: flex;
            gap: 1rem;
        }
        
        .social-media a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: var(--primary);
            color: white;
            border-radius: 50%;
            transition: all 0.3s ease;
        }
        
        .social-media a:hover {
            background-color: var(--secondary);
            transform: translateY(-3px);
        }
        
        .kontak-form {
            flex: 1;
            background-color: var(--light);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .kontak-form h3 {
            font-size: 1.5rem;
            color: var(--primary);
            margin-bottom: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            border-color: var(--secondary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }
        
        .form-group textarea {
            min-height: 150px;
            resize: vertical;
        }
        
        /* Footer */
        footer {
            background-color: var(--dark);
            color: white;
            padding: 3rem 0 1rem;
        }
        
        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }
        
        .footer-col h3 {
            font-size: 1.3rem;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 0.5rem;
        }
        
        .footer-col h3:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 2px;
            background-color: var(--accent);
        }
        
        .footer-col p {
            margin-bottom: 1rem;
            opacity: 0.8;
        }
        
        .footer-links {
            list-style: none;
        }
        
        .footer-links li {
            margin-bottom: 0.8rem;
        }
        
        .footer-links a {
            color: white;
            text-decoration: none;
            opacity: 0.8;
            transition: all 0.3s ease;
            display: block;
        }
        
        .footer-links a:hover {
            opacity: 1;
            padding-left: 5px;
            color: var(--secondary);
        }
        
        .copyright {
            text-align: center;
            padding-top: 2rem;
            margin-top: 2rem;
            border-top: 1px solid rgba(255,255,255,0.1);
            opacity: 0.7;
            font-size: 0.9rem;
        }
        
        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
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
        
        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .animated {
            animation-duration: 1s;
            animation-fill-mode: both;
        }
        
        .fadeIn {
            animation-name: fadeIn;
        }
        
        .fadeInUp {
            animation-name: fadeInUp;
        }
        
        .fadeInDown {
            animation-name: fadeInDown;
        }
        
        .fadeInLeft {
            animation-name: fadeInLeft;
        }
        
        .fadeInRight {
            animation-name: fadeInRight;
        }
        
        /* Responsive Styles */
        @media (max-width: 992px) {
            .profil-container {
                flex-direction: column;
            }
            
            .kontak-container {
                flex-direction: column;
            }
        }
        
        @media (max-width: 768px) {
            nav ul {
                position: fixed;
                top: 72px;
                left: -100%;
                width: 100%;
                height: calc(100vh - 72px);
                background-color: var(--primary);
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 3rem;
                transition: all 0.5s ease;
            }
            
            nav ul.active {
                left: 0;
            }
            
            .mobile-menu-btn {
                display: block;
            }
            
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .hero p {
                font-size: 1rem;
            }
            
            .section-title h2 {
                font-size: 2rem;
            }
        }
        
        @media (max-width: 576px) {
            .hero h1 {
                font-size: 2rem;
            }
            
            .btn {
                padding: 0.6rem 1.5rem;
            }
            
            .section-title h2 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header id="header">
        <div class="header-container">
            <div class="logo">
                <img src="./assets/images/logo.png" alt="Logo Desa Winduaji">
                <div class="logo-text">
                    <h1>Desa Winduaji</h1>
                    <p>Kec. Paninggaran, Kab. Pekalongan</p>
                </div>
            </div>
            <nav>
                <button class="mobile-menu-btn" id="mobileMenuBtn">☰</button>
                <ul id="navMenu">
                    <li><a href="#home">Beranda</a></li>
                    <li><a href="#profil">Profil Desa</a></li>
                    <li><a href="#umkm">UMKM</a></li>
                    <li><a href="#berita">Berita</a></li>
                    <li><a href="#galeri">Galeri</a></li>
                    <li><a href="#kontak">Kontak</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-content">
            <h1 class="animated fadeInDown">Selamat Datang di Desa Winduaji</h1>
            <p class="animated fadeInDown">Desa yang asri di kaki Gunung Rogojembangan, Kecamatan Paninggaran, Kabupaten Pekalongan</p>
            <a href="./assets/images/landingpage/bgutama.jpeg" class="btn btn-outline animated fadeInUp">Jelajahi Desa Kami</a>
        </div>
    </section>

    <!-- Profil Desa Section -->
    <section class="profil-desa section" id="profil">
        <div class="section-title">
            <h2>Profil Desa</h2>
            <p>Mengenal lebih dekat Desa Winduaji, sejarah, geografis, dan potensinya</p>
        </div>
        <div class="profil-container">
            <div class="profil-image">
                <img src="./assets/images/landingpage/bgutama.jpeg" alt="Desa Winduaji">
            </div>
            <div class="profil-content">
                <h3>Desa Winduaji</h3>
                <p><?= $profil['sejarah_singkat'] ?? 'Desa Winduaji terletak di Kecamatan Paninggaran, Kabupaten Pekalongan, Jawa Tengah. Desa ini dikenal dengan keindahan alamnya yang mempesona dan masyarakatnya yang ramah.' ?></p>
                <p>Luas wilayah: <?= $profil['luas_wilayah'] ?? '250 Ha' ?>, dengan topografi berbukit-bukit dan dikelilingi oleh perkebunan dan pertanian yang subur.</p>
                <p>Jumlah penduduk: <?= $profil['jumlah_penduduk'] ?? '2.500 jiwa' ?> dengan mata pencaharian utama di sektor pertanian, perkebunan, dan UMKM.</p>
                <a href="#" class="btn">Baca Selengkapnya</a>
            </div>
        </div>
    </section>

    <!-- UMKM Section -->
    <section class="umkm section" id="umkm">
        <div class="section-title">
            <h2>UMKM Desa</h2>
            <p>Produk unggulan dan usaha masyarakat Desa Winduaji</p>
        </div>
        <div class="umkm-container">
            <div class="umkm-grid">
                <?php foreach($umkm_kategori as $kategori): ?>
                <div class="umkm-card animated fadeInUp">
                    <div class="umkm-img">
                        <img src="assets/images/umkm/<?= $kategori['icon'] ?>" alt="<?= $kategori['nama_kategori'] ?>">
                    </div>
                    <div class="umkm-content">
                        <h3><?= $kategori['nama_kategori'] ?></h3>
                        <p>Jumlah UMKM: <?= $kategori['jumlah'] ?></p>
                        <div class="umkm-meta">
                            <span class="umkm-kategori"><?= $kategori['nama_kategori'] ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <div class="lihat-semua">
                <a href="./pages/umkm.php" class="btn">Lihat Semua UMKM (<?= count($umkm_kategori) ?>)</a>
            </div>
        </div>
    </section>

    <!-- Berita Section -->
    <section class="berita section" id="berita">
        <div class="section-title">
            <h2>Berita Terkini</h2>
            <p>Update kegiatan dan informasi terbaru dari Desa Winduaji</p>
        </div>
        <div class="berita-container">
            <div class="berita-grid">
                <?php foreach($berita as $b): ?>
                <div class="berita-card animated fadeInUp">
                    <div class="berita-img">
                        <img src="assets/images/berita/<?= $b['gambar'] ?>" alt="<?= $b['judul'] ?>">
                    </div>
                    <div class="berita-content">
                        <span class="berita-date"><?= date('d M Y', strtotime($b['tanggal'])) ?></span>
                        <h3><?= $b['judul'] ?></h3>
                        <p><?= substr($b['isi'], 0, 150) ?>...</p>
                        <a href="#" class="btn">Baca Selengkapnya</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="lihat-semua">
                <a href="./pages/berita.php" class="btn">Lihat Semua Berita (<?= count($berita) ?>)</a>
        </div>
    </section>

    <!-- Galeri Section -->
    <section class="galeri section" id="galeri">
        <div class="section-title">
            <h2>Galeri Desa</h2>
            <p>Momen dan keindahan Desa Winduaji dalam gambar</p>
        </div>
        <div class="galeri-container">
            <div class="galeri-grid">
                <?php foreach($galeri as $g): ?>
                <div class="galeri-item animated fadeInUp">
                    <img src="assets/images/galeri/<?= $g['gambar'] ?>" alt="<?= $g['judul'] ?>">
                    <div class="galeri-caption">
                        <h3><?= $g['judul'] ?></h3>
                    </div>
                </div>
              <?php endforeach; ?>
            </div>
        </div>
    </section>

   

    <!-- Footer -->
    <footer>
        <div class="footer-container">
            <div class="footer-col">
                <h3>Tentang Desa</h3>
                <p>Desa Winduaji adalah desa yang terletak di Kecamatan Paninggaran, Kabupaten Pekalongan, Jawa Tengah dengan potensi alam dan UMKM yang beragam.</p>
            </div>
            <div class="footer-col">
                <h3>Link Cepat</h3>
                <ul class="footer-links">
                    <li><a href="#home">Beranda</a></li>
                    <li><a href="#profil">Profil Desa</a></li>
                    <li><a href="#umkm">UMKM</a></li>
                    <li><a href="#berita">Berita</a></li>
                    <li><a href="#galeri">Galeri</a></li>
                    <li><a href="#kontak">Kontak</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h3>Kontak</h3>
                <p>Jl. Raya Winduaji No. 123</p>
                <p>Paninggaran, Pekalongan</p>
                <p>Jawa Tengah 51164</p>
                <p>Email: desa.winduaji@paninggaran.go.id</p>
                <p>Telp: (0285) 1234567</p>
            </div>
        </div>
        <div class="copyright">
            <p>&copy; <?= date('Y') ?> Desa Winduaji. Semua hak dilindungi.</p>
        </div>
    </footer>

    <script>
        // Mobile Menu Toggle
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const navMenu = document.getElementById('navMenu');
        
        mobileMenuBtn.addEventListener('click', () => {
            navMenu.classList.toggle('active');
        });
        
        // Header Scroll Effect
        const header = document.getElementById('header');
        
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
        
        // Smooth Scrolling for Anchor Links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                const targetElement = document.querySelector(targetId);
                
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 70,
                        behavior: 'smooth'
                    });
                    
                    // Close mobile menu if open
                    if (navMenu.classList.contains('active')) {
                        navMenu.classList.remove('active');
                    }
                }
            });
        });
        
        // Animation on Scroll
        const animateOnScroll = () => {
            const elements = document.querySelectorAll('.animated');
            
            elements.forEach(element => {
                const elementPosition = element.getBoundingClientRect().top;
                const windowHeight = window.innerHeight;
                
                if (elementPosition < windowHeight - 100) {
                    element.style.opacity = '1';
                }
            });
        };
        
        window.addEventListener('scroll', animateOnScroll);
        window.addEventListener('load', animateOnScroll);
    </script>
</body>
</html>