<?php
require_once '../config/database.php';
require_once '../config/functions.php';

// Koneksi database
$db = new database();
$conn = $db->getConnection();

// Ambil ID berita dari parameter URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Inisialisasi variabel
$berita = null;
$berita_terkait = [];
$error = '';

// Ambil data desa untuk header
$desa_nama = "Winduaji"; // Default value, bisa diganti dengan mengambil dari database

// Ambil data berita berdasarkan ID
try {
    $query = "SELECT b.*, bk.nama_kategori 
              FROM berita b 
              LEFT JOIN berita_kategori bk ON b.kategori_id = bk.id 
              WHERE b.id = :id AND b.status = 'published'";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $berita = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($berita) {
        // Update jumlah dilihat
        $query_update = "UPDATE berita SET dilihat = dilihat + 1 WHERE id = :id";
        $stmt_update = $conn->prepare($query_update);
        $stmt_update->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt_update->execute();
        
        // Ambil data terbaru setelah update
        $stmt->execute();
        $berita = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Ambil berita terkait (dalam kategori yang sama)
        $query_terkait = "SELECT b.*, bk.nama_kategori 
                          FROM berita b 
                          LEFT JOIN berita_kategori bk ON b.kategori_id = bk.id 
                          WHERE b.kategori_id = :kategori_id 
                          AND b.id != :id 
                          AND b.status = 'published'
                          ORDER BY b.tanggal_publish DESC 
                          LIMIT 3";
        $stmt_terkait = $conn->prepare($query_terkait);
        $stmt_terkait->bindParam(':kategori_id', $berita['kategori_id'], PDO::PARAM_INT);
        $stmt_terkait->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt_terkait->execute();
        $berita_terkait = $stmt_terkait->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $error = "Gagal memuat data berita: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <title><?php echo $berita ? htmlspecialchars($berita['judul']) : 'Berita Tidak Ditemukan'; ?> - Desa Winduaji</title>
    <link rel="shortcut icon" href="../assets/images/logo.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&amp;display=swap"
        rel="stylesheet" />
    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"
        rel="stylesheet" />
    <style>
        :root {
            --primary: #7f1d1d;
            --primary-light: #b91c1c;
            --secondary: #1e3a8a;
            --accent: #dc2626;
            --neutral: #f5f5f5;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
        }
        
        body {
            font-family: "Poppins", sans-serif;
            scroll-behavior: smooth;
        }

        .card-hover {
            position: relative;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .gradient-bg {
            background: linear-gradient(135deg, #fcf8f8ff 0%, #e2e8f0 100%);
        }

        .mobile-menu {
            transition: transform 0.3s ease-in-out;
            transform: translateX(-100%);
        }

        .mobile-menu.active {
            transform: translateX(0);
        }

        .back-to-top {
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        
        .wiki-style {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Lato, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #202122;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .wiki-style h1, .wiki-style h2, .wiki-style h3, .wiki-style h4, .wiki-style h5, .wiki-style h6 {
            border-bottom: 1px solid #eaecf0;
            padding-bottom: 0.3em;
            margin-top: 1.5em;
            margin-bottom: 0.5em;
            font-weight: 600;
        }
        
        .wiki-style h1 {
            font-size: 1.8em;
            border-bottom: 2px solid #eaecf0;
        }
        
        .wiki-style h2 {
            font-size: 1.5em;
        }
        
        .wiki-style h3 {
            font-size: 1.3em;
        }
        
        .wiki-style p {
            margin-bottom: 1em;
            line-height: 1.7;
        }
        
        .wiki-style ul, .wiki-style ol {
            margin: 0.3em 0 0 1.6em;
            padding: 0;
        }
        
        .wiki-style li {
            margin-bottom: 0.5em;
        }
        
        .wiki-style blockquote {
            margin: 1em 0;
            padding: 0.5em 1em;
            background-color: #f8f9fa;
            border-left: 4px solid #eaecf0;
            font-style: italic;
        }
        
        .wiki-style table {
            border-collapse: collapse;
            width: 100%;
            margin: 1em 0;
            font-size: 0.9em;
        }
        
        .wiki-style table th, .wiki-style table td {
            border: 1px solid #a2a9b1;
            padding: 0.4em 0.6em;
        }
        
        .wiki-style table th {
            background-color: #eaecf0;
            font-weight: 600;
            text-align: center;
        }
        
        .wiki-style img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 1em 0;
        }
        
        .wiki-style .infobox {
            background: #f8f9fa;
            border: 1px solid #a2a9b1;
            border-collapse: collapse;
            font-size: 0.9em;
            line-height: 1.5;
            margin: 0 0 1em 1em;
            padding: 0.2em;
            float: right;
            clear: right;
            width: 300px;
        }
        
        .wiki-style .infobox th {
            background: #eaecf0;
            text-align: center;
            font-size: 1.1em;
            padding: 0.4em;
        }
        
        .wiki-style .infobox td {
            padding: 0.4em;
            vertical-align: top;
        }
        
        .wiki-style .infobox .label {
            font-weight: 600;
            width: 40%;
        }
        
        .breadcrumb {
            font-size: 0.9em;
            color: #6b7280;
        }
        
        .breadcrumb a {
            color: #b91c1c;
            text-decoration: none;
        }
        
        .breadcrumb a:hover {
            text-decoration: underline;
        }
        
        .news-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            font-size: 0.9em;
            color: #6b7280;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .news-meta div {
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        
        .share-buttons {
            display: flex;
            gap: 0.5rem;
            margin: 2rem 0;
        }
        
        .share-button {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.5rem 1rem;
            background: #f3f4f6;
            border-radius: 6px;
            color: #4b5563;
            text-decoration: none;
            font-size: 0.9em;
            transition: all 0.2s;
        }
        
        .share-button:hover {
            background: #e5e7eb;
            color: #374151;
        }
        
        .related-news {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            margin-top: 3rem;
        }
        
        .news-card {
            display: block;
            padding: 1rem;
            border-radius: 8px;
            transition: all 0.2s;
            text-decoration: none;
            color: inherit;
        }
        
        .news-card:hover {
            background: #f9fafb;
            transform: translateY(-2px);
        }
        
        .news-card img {
            width: 100%;
            height: 160px;
            object-fit: cover;
            border-radius: 6px;
            margin-bottom: 0.8rem;
        }
        
        .news-card h3 {
            font-size: 1.1em;
            margin: 0 0 0.5rem 0;
            color: #1f2937;
            line-height: 1.4;
        }
        
        .news-card p {
            font-size: 0.9em;
            color: #6b7280;
            margin: 0;
            line-height: 1.5;
        }
        
        @media (max-width: 768px) {
            .wiki-style .infobox {
                float: none;
                width: 100%;
                margin: 0 0 1em 0;
            }
            
            .news-meta {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .share-buttons {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
   <!-- Mobile Menu Overlay -->
    <div id="mobile-menu-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden transition-opacity duration-300"></div>
    
    <header class="w-full border-b border-gray-200 bg-white shadow-sm sticky top-0 z-50 transition-all duration-300">
        <nav class="container mx-auto flex items-center justify-between py-3 px-4 md:px-6 lg:px-8">
            <a class="flex items-center space-x-2" href="../landingpage.php">
                <img alt="Logo Desa <?php echo $desa_nama; ?>" class="h-12 w-auto transition-transform duration-300 hover:scale-105" height="50" src="../assets/images/logo.png" width="150" />
                <span class="text-xl font-bold text-red-600 hidden md:block"><?php echo $desa_nama; ?></span>
            </a>
            <ul class="hidden md:flex space-x-6 lg:space-x-8 text-sm font-medium">
                <li><a class="text-red-600 hover:text-red-700 transition-colors duration-200" href="../landingpage.php">Beranda</a></li>
                <li><a class="text-gray-700 hover:text-red-600 transition-colors duration-200" href="../pages/sejarah.php">Profil Desa</a></li>
                <li><a class="text-gray-700 hover:text-red-600 transition-colors duration-200" href="../pages/umkm.php">UMKM</a></li>
                <li><a class="text-gray-700 hover:text-red-600 transition-colors duration-200" href="../pages/berita.php">Berita</a></li>
                <li><a class="text-gray-700 hover:text-red-600 transition-colors duration-200" href="../pages/galeri.php">Galeri</a></li>
                <li><a class="text-gray-700 hover:text-red-600 transition-colors duration-200" href="../login.php">Admin</a></li>
            </ul>
            <div class="md:hidden">
                <button class="text-gray-600 hover:text-red-600 focus:outline-none transition-colors duration-200" id="mobile-menu-button">
                    <i class="fas fa-bars fa-lg"></i>
                </button>
            </div>
        </nav>
        
        <!-- Mobile Menu -->
        <div id="mobile-menu" class="mobile-menu fixed top-0 left-0 h-full w-72 bg-white shadow-2xl z-50 p-6 overflow-y-auto">
            <div class="flex justify-between items-center mb-8">
                <img alt="Logo Desa <?php echo $desa_nama; ?>" class="h-10 w-auto" src="../assets/images/logo.png" />
                <button id="mobile-menu-close" class="text-gray-600 hover:text-red-600 transition-colors duration-200">
                    <i class="fas fa-times fa-lg"></i>
                </button>
            </div>
            <ul class="space-y-6">
                <li><a class="text-red-600 hover:text-red-700 font-medium block py-2 transition-colors duration-200" href="../landingpage.php">Beranda</a></li>
                <li><a class="text-gray-700 hover:text-red-600 font-medium block py-2 transition-colors duration-200" href="../pages/sejarah.php">Profil Desa</a></li>
                <li><a class="text-gray-700 hover:text-red-600 font-medium block py-2 transition-colors duration-200" href="../pages/umkm.php">UMKM</a></li>
                <li><a class="text-gray-700 hover:text-red-600 font-medium block py-2 transition-colors duration-200" href="../pages/berita.php">Berita</a></li>
                <li><a class="text-gray-700 hover:text-red-600 font-medium block py-2 transition-colors duration-200" href="../pages/galeri.php">Galeri</a></li>
                <li><a class="text-gray-700 hover:text-red-600 font-medium block py-2 transition-colors duration-200" href="../login.php">Admin</a></li>
            </ul>
        </div>
    </header>


    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <?php if ($berita): ?>
            <!-- Breadcrumb -->
            <div class="breadcrumb mb-6">
                <a href="../landingpage.php">Beranda</a> > 
                <a href="../pages/berita.php">Berita</a> > 
                <a href="../pages/berita.php?kategori=<?php echo $berita['kategori_id']; ?>"><?php echo htmlspecialchars($berita['nama_kategori']); ?></a> > 
                <span><?php echo htmlspecialchars($berita['judul']); ?></span>
            </div>
            
            <!-- Artikel Berita -->
            <article class="bg-white rounded-xl shadow-sm overflow-hidden">
                <!-- Gambar Utama -->
                <?php if (!empty($berita['gambar_path'])): ?>
                    <div class="w-full h-64 md:h-96 overflow-hidden">
                        <img src="../<?php echo $berita['gambar_path']; ?>" alt="<?php echo htmlspecialchars($berita['judul']); ?>" class="w-full h-full object-cover">
                    </div>
                <?php endif; ?>
                
                <div class="p-6 md:p-8">
                    <!-- Judul -->
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-4"><?php echo htmlspecialchars($berita['judul']); ?></h1>
                    
                    <!-- Meta Info -->
                    <div class="news-meta">
                        <div>
                            <i class="fas fa-folder text-red-600"></i>
                            <span><?php echo htmlspecialchars($berita['nama_kategori']); ?></span>
                        </div>
                        <div>
                            <i class="fas fa-calendar-alt text-red-600"></i>
                            <span><?php echo date('d F Y', strtotime($berita['tanggal_publish'])); ?></span>
                        </div>
                        <div>
                            <i class="fas fa-clock text-red-600"></i>
                            <span><?php echo date('H:i', strtotime($berita['tanggal_publish'])); ?> WIB</span>
                        </div>
                        <div>
                            <i class="fas fa-eye text-red-600"></i>
                            <span><?php echo isset($berita['dilihat']) ? $berita['dilihat'] : 0; ?> Dilihat</span>
                        </div>
                    </div>
                    
                    <!-- Konten Berita -->
                    <div class="wiki-style">
                        <?php echo $berita['isi']; ?>
                    </div>
                    
                    <!-- Share Buttons -->
                    <div class="share-buttons">
                        <span class="text-gray-700 mr-2">Bagikan:</span>
                        <a href="#" class="share-button" id="share-facebook">
                            <i class="fab fa-facebook-f"></i> Facebook
                        </a>
                        <a href="#" class="share-button" id="share-twitter">
                            <i class="fab fa-twitter"></i> Twitter
                        </a>
                        <a href="#" class="share-button" id="share-whatsapp">
                            <i class="fab fa-whatsapp"></i> WhatsApp
                        </a>
                        <a href="#" class="share-button" id="share-copy">
                            <i class="fas fa-link"></i> Salin Tautan
                        </a>
                    </div>
                </div>
            </article>
            
            <!-- Berita Terkait -->
            <?php if (!empty($berita_terkait)): ?>
                <div class="related-news">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-newspaper mr-2 text-red-600"></i> Berita Terkait
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <?php foreach ($berita_terkait as $terkait): ?>
                            <a href="detail-berita.php?id=<?php echo $terkait['id']; ?>" class="news-card">
                                <?php if (!empty($terkait['gambar_path'])): ?>
                                    <img src="../<?php echo $terkait['gambar_path']; ?>" alt="<?php echo htmlspecialchars($terkait['judul']); ?>">
                                <?php else: ?>
                                    <div class="w-full h-40 bg-gray-200 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-newspaper text-gray-400 text-3xl"></i>
                                    </div>
                                <?php endif; ?>
                                <h3><?php echo htmlspecialchars($terkait['judul']); ?></h3>
                                <p><?php echo date('d M Y', strtotime($terkait['tanggal_publish'])); ?></p>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <!-- Berita tidak ditemukan -->
            <div class="text-center py-16">
                <i class="fas fa-exclamation-circle text-5xl text-red-600 mb-4"></i>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Berita Tidak Ditemukan</h1>
                <p class="text-gray-600 mb-6">Maaf, berita yang Anda cari tidak ditemukan atau mungkin telah dihapus.</p>
                <a href="../pages/berita.php" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar Berita
                </a>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8 mt-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">Desa Winduaji</h3>
                    <p class="text-gray-300">Desa yang membangun bersama untuk kesejahteraan masyarakat dan kemajuan daerah.</p>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-4">Tautan Cepat</h3>
                    <ul class="space-y-2">
                        <li><a href="../landingpage.php" class="text-gray-300 hover:text-white">Beranda</a></li>
                        <li><a href="../pages/sejarah.php" class="text-gray-300 hover:text-white">Profil Desa</a></li>
                        <li><a href="../pages/berita.php" class="text-gray-300 hover:text-white">Berita</a></li>
                        <li><a href="../pages/galeri.php" class="text-gray-300 hover:text-white">Galeri</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-4">Kontak</h3>
                    <ul class="space-y-2 text-gray-300">
                        <li class="flex items-center">
                            <i class="fas fa-map-marker-alt mr-2"></i> Jl. Desa Winduaji No. 123
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-phone mr-2"></i> (021) 1234-5678
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-envelope mr-2"></i> info@winduaji.desa.id
                        </li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-6 text-center text-gray-400">
                <p>&copy; 2023 Desa Winduaji. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile Menu Toggle
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenuClose = document.getElementById('mobile-menu-close');
        const mobileMenu = document.getElementById('mobile-menu');
        const mobileMenuOverlay = document.getElementById('mobile-menu-overlay');
        
        function toggleMobileMenu() {
            mobileMenu.classList.toggle('active');
            mobileMenuOverlay.classList.toggle('hidden');
            document.body.classList.toggle('overflow-hidden');
        }
        
        mobileMenuButton.addEventListener('click', toggleMobileMenu);
        mobileMenuClose.addEventListener('click', toggleMobileMenu);
        mobileMenuOverlay.addEventListener('click', toggleMobileMenu);
        
        // Close mobile menu on link click
        mobileMenu.querySelectorAll('a').forEach(link => link.addEventListener('click', toggleMobileMenu));
        
        // Fungsi untuk share berita
        document.addEventListener('DOMContentLoaded', function() {
            // Salin tautan
            const copyLinkBtn = document.getElementById('share-copy');
            if (copyLinkBtn) {
                copyLinkBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const url = window.location.href;
                    navigator.clipboard.writeText(url).then(function() {
                        alert('Tautan berhasil disalin!');
                    });
                });
            }
            
            // Share ke Facebook
            const facebookBtn = document.getElementById('share-facebook');
            if (facebookBtn) {
                facebookBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const url = encodeURIComponent(window.location.href);
                    window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank');
                });
            }
            
            // Share ke Twitter
            const twitterBtn = document.getElementById('share-twitter');
            if (twitterBtn) {
                twitterBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const url = encodeURIComponent(window.location.href);
                    const text = encodeURIComponent(document.title);
                    window.open(`https://twitter.com/intent/tweet?text=${text}&url=${url}`, '_blank');
                });
            }
            
            // Share ke WhatsApp
            const whatsappBtn = document.getElementById('share-whatsapp');
            if (whatsappBtn) {
                whatsappBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const url = encodeURIComponent(window.location.href);
                    const text = encodeURIComponent(document.title);
                    window.open(`https://wa.me/?text=${text} ${url}`, '_blank');
                });
            }
        });
    </script>
</body>
</html>