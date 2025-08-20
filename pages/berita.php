<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';

// Pastikan koneksi database berhasil
if (!isset($pdo)) {
    die("Koneksi database gagal. Silakan periksa konfigurasi database.");
}

// Data desa
$desa_nama = "Desa Winduaji";
$desa_lokasi = "Kecamatan Paninggaran, Kabupaten Pekalongan, Provinsi Jawa Tengah";
$desa_motto = "Bersama Membangun Desa yang Mandiri dan Berbudaya";

// Inisialisasi variabel berita
$berita = [];
$total_berita = 0;
$total_pages = 1;

try {
    // Mengambil data berita dari database
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'terbaru';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $per_page = 6; // Jumlah berita per halaman

    // Query untuk mendapatkan total berita (untuk pagination)
    $count_query = "SELECT COUNT(*) as total FROM berita WHERE status = 'published'";
    if (!empty($search)) {
        $count_query .= " AND (judul LIKE :search OR isi LIKE :search)";
    }

    $stmt = $pdo->prepare($count_query);
    if (!empty($search)) {
        $searchParam = "%$search%";
        $stmt->bindParam(':search', $searchParam);
    }
    $stmt->execute();
    $total_berita = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_berita / $per_page);

    // Validasi halaman
    if ($page < 1) $page = 1;
    if ($page > $total_pages) $page = $total_pages;

    $offset = ($page - 1) * $per_page;

    // Query untuk mendapatkan berita dengan fitur pencarian dan pengurutan
    $query = "SELECT * FROM berita WHERE status = 'published'";
    if (!empty($search)) {
        $query .= " AND (judul LIKE :search OR isi LIKE :search)";
    }
    if ($sort == 'terbaru') {
        $query .= " ORDER BY tanggal_publish  DESC";
    } else {
        $query .= " ORDER BY tanggal_publish  ASC";
    }
    $query .= " LIMIT :offset, :per_page";

    $stmt = $pdo->prepare($query);
    if (!empty($search)) {
        $searchParam = "%$search%";
        $stmt->bindParam(':search', $searchParam);
    }
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':per_page', $per_page, PDO::PARAM_INT);
    $stmt->execute();
    $berita = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Tangani error database dengan lebih elegan
    die("Terjadi kesalahan database: " . $e->getMessage());
} catch (Exception $e) {
    die("Terjadi kesalahan: " . $e->getMessage());
}

// Fungsi untuk mendapatkan path gambar yang benar
function getImagePath($gambar) {
    // Jika gambar kosong, gunakan gambar default
    if (empty($gambar)) {
        return '../assets/images/landingpage/bgutama.jpg';
    }
    
    // Cek apakah gambar ada di folder uploads
    $uploadPath = '../uploads/berita/' . $gambar;
    if (file_exists($uploadPath)) {
        return $uploadPath;
    }
    
    // Jika tidak ditemukan, gunakan gambar default
    return '../assets/images/landingpage/bgutama.jpg';
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita - <?php echo $desa_nama; ?></title>
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
                url('../assets/images/landingpage/bgutama.jpg') center/cover no-repeat;
            color: var(--pure-white);
            padding: 120px 0 80px;
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
            font-size: 2.8rem;
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

        /* Search and Sort Section */
        .search-sort-section {
            background: var(--pure-white);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 2rem;
        }

        /* Berita Card */
        .news-card {
            background: var(--pure-white);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: all 0.4s ease;
            height: 100%;
            margin-bottom: 1.5rem;
        }

        .news-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-lg);
        }

        .news-card .card-img-top {
            height: 220px;
            object-fit: cover;
            width: 100%;
        }

        .news-card .card-body {
            padding: 1.5rem;
        }

        .news-date {
            color: var(--primary-red);
            font-weight: 500;
            font-size: 0.9rem;
        }

        .news-title {
            font-size: 1.3rem;
            margin-bottom: 1rem;
            transition: color 0.3s ease;
        }

        .news-card:hover .news-title {
            color: var(--primary-red);
        }

        .news-excerpt {
            color: var(--medium-gray);
            margin-bottom: 1.5rem;
        }

        .read-more {
            color: var(--primary-red);
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s ease;
        }

        .read-more:hover {
            color: var(--dark-red);
            transform: translateX(5px);
        }

        .read-more i {
            margin-left: 5px;
            transition: all 0.3s ease;
        }

        .read-more:hover i {
            transform: translateX(3px);
        }

        /* Pagination */
        .pagination .page-item .page-link {
            color: var(--primary-red);
            border: 1px solid var(--light-gray);
            margin: 0 5px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .pagination .page-item.active .page-link {
            background: var(--primary-red);
            border-color: var(--primary-red);
            color: white;
        }

        .pagination .page-item .page-link:hover {
            background: var(--light-red);
            border-color: var(--light-red);
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

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        /* Responsive Design */
        @media (max-width: 991.98px) {
            .hero-content h1 {
                font-size: 2.4rem;
            }

            .section-title {
                font-size: 2rem;
            }
        }

        @media (max-width: 767.98px) {
            .hero-section {
                padding: 100px 0 60px;
            }

            .hero-content h1 {
                font-size: 2rem;
            }

            .section-title {
                font-size: 1.8rem;
            }
        }

        @media (max-width: 575.98px) {
            .hero-content h1 {
                font-size: 1.8rem;
            }

            .section-title {
                font-size: 1.6rem;
            }
        }
    </style>
</head>

<body data-bs-spy="scroll" data-bs-target=".navbar" data-bs-offset="100">
     <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand" href="../landingpage.php">
                <img src="../assets/images/logo.png" alt="Logo <?php echo $desa_nama; ?>" class="img-fluid">
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
                    <h1 class="display-4 fw-bold mb-4 animate__animated animate__fadeInDown">Berita Desa</h1>
                    <p class="lead mb-5 animate__animated animate__fadeIn animate__delay-1s">Informasi terkini seputar kegiatan dan perkembangan <?php echo $desa_nama; ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Berita Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <!-- Search and Sort Section -->
                    <div class="search-sort-section mb-5" data-aos="fade-up">
                        <form action="berita.php" method="get" class="row g-3">
                            <div class="col-md-8">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" placeholder="Cari berita..." value="<?php echo htmlspecialchars($search); ?>">
                                    <button class="btn btn-primary-custom" type="submit">
                                        <i class="fas fa-search"></i> Cari
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="input-group">
                                    <label class="input-group-text" for="sort">Urutkan</label>
                                    <select class="form-select" name="sort" id="sort" onchange="this.form.submit()">
                                        <option value="terbaru" <?php echo $sort == 'terbaru' ? 'selected' : ''; ?>>Terbaru</option>
                                        <option value="terlama" <?php echo $sort == 'terlama' ? 'selected' : ''; ?>>Terlama</option>
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Results Info -->
                    <?php if (!empty($search)): ?>
                        <div class="alert alert-info mb-4" data-aos="fade-up">
                            Menampilkan hasil pencarian untuk: <strong>"<?php echo htmlspecialchars($search); ?>"</strong>
                            <a href="berita.php" class="float-end text-danger">Tampilkan semua berita</a>
                        </div>
                    <?php endif; ?>

                    <!-- Berita List -->
                    <?php if (count($berita) > 0): ?>
                        <div class="row">
                            <?php foreach ($berita as $item): ?>
                                <div class="col-md-6 col-lg-4" data-aos="fade-up">
                                    <div class="news-card card h-100 border-0 shadow-sm">
                                        <img src="<?php echo getImagePath($item['gambar_path']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($item['judul']); ?>">
                                        <div class="card-body">
                                            <span class="news-date d-block mb-2">
                                                <i class="far fa-calendar-alt me-2"></i>
                                                <?php
                                                // Remove space from array key and add null check
                                                $publishDate = isset($item['tanggal_publish']) ? $item['tanggal_publish'] : date('Y-m-d');
                                                echo date('d F Y', strtotime($publishDate));
                                                ?>
                                            </span>
                                            <h5 class="news-title"><?php echo htmlspecialchars($item['judul']); ?></h5>
                                            <p class="news-excerpt"><?php echo excerpt($item['isi'], 100); ?></p>
                                            <a href="detail_berita.php?id=<?php echo $item['id']; ?>" class="read-more">
                                                Baca selengkapnya <i class="fas fa-arrow-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="Page navigation" class="mt-5" data-aos="fade-up">
                                <ul class="pagination justify-content-center">
                                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>" tabindex="-1" aria-disabled="true">Sebelumnya</a>
                                    </li>

                                    <?php
                                    // Tampilkan maksimal 5 nomor halaman
                                    $start_page = max(1, min($page - 2, $total_pages - 4));
                                    $end_page = min($total_pages, $start_page + 4);

                                    for ($i = $start_page; $i <= $end_page; $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>

                                    <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>">Selanjutnya</a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center py-5" data-aos="fade-up">
                            <img src="../assets/images/landingpage/bgutama.jpg" alt="No data" class="img-fluid mb-4" style="max-width: 300px;">
                            <h4 class="mb-3">Berita tidak ditemukan</h4>
                            <p class="text-muted mb-4">Tidak ada berita yang sesuai dengan pencarian Anda</p>
                            <a href="berita.php" class="btn btn-primary-custom px-4">Lihat Semua Berita</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container footer-content">
            <div class="row g-4">
                <div class="col-lg-3">
                    <div class="mb-4">
                        <img src="../assets/images/logo.png" alt="Logo <?php echo $desa_nama; ?>" class="footer-logo">
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
                            <li><a href="../landingpage.php">Beranda</a></li>
                            <li><a href="../landingpage.php#sejarah">Sejarah</a></li>
                            <li><a href="../landingpage.php#demografi">Demografi</a></li>
                            <li><a href="../landingpage.php#umkm">UMKM</a></li>
                            <li><a href="galeri.php">Galeri</a></li>
                            <li><a href="berita.php">Berita</a></li>
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

        $('.back-to-top').click(function(e) {
            e.preventDefault();
            $('html, body').animate({
                scrollTop: 0
            }, '300');
        });
    </script>
</body>

</html>