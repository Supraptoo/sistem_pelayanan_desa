<?php
require_once '../config/database.php';
require_once '../config/functions.php';

// Koneksi database
$db = new database();
$conn = $db->getConnection();

// Inisialisasi variabel
$berita_data = [];
$berita_populer = [];
$kategori_berita = [];
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 6; // Jumlah berita per halaman
$offset = ($current_page - 1) * $limit;
$total_berita = 0;
$total_pages = 1;

// Ambil data kategori berita
try {
    $query = "SELECT * FROM berita_kategori ORDER BY nama_kategori";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $kategori_berita = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = "Gagal memuat kategori berita: " . $e->getMessage();
}

// Filter berdasarkan kategori jika ada
$kategori_filter = isset($_GET['kategori']) ? $_GET['kategori'] : '';

// Query untuk mengambil total berita
$query_total = "SELECT COUNT(*) as total FROM berita WHERE status = 'published'";
$params = [];

if (!empty($kategori_filter)) {
    $query_total .= " AND kategori_id = :kategori_id";
    $params[':kategori_id'] = $kategori_filter;
}

try {
    $stmt = $conn->prepare($query_total);
    $stmt->execute($params);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_berita = $result['total'];
    $total_pages = ceil($total_berita / $limit);
} catch (Exception $e) {
    $error = "Gagal memuat total berita: " . $e->getMessage();
}

// Query untuk mengambil berita
$query_berita = "SELECT b.*, bk.nama_kategori 
                 FROM berita b 
                 LEFT JOIN berita_kategori bk ON b.kategori_id = bk.id 
                 WHERE b.status = 'published'";
$params_berita = [];

if (!empty($kategori_filter)) {
    $query_berita .= " AND b.kategori_id = :kategori_id";
    $params_berita[':kategori_id'] = $kategori_filter;
}

$query_berita .= " ORDER BY b.tanggal_publish DESC LIMIT :limit OFFSET :offset";

try {
    $stmt = $conn->prepare($query_berita);

    // Bind parameters
    foreach ($params_berita as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();
    $berita_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = "Gagal memuat data berita: " . $e->getMessage();
}

// Ambil berita populer (berdasarkan views)
try {
    $query = "SELECT b.*, bk.nama_kategori 
              FROM berita b 
              LEFT JOIN berita_kategori bk ON b.kategori_id = bk.id 
              WHERE b.status = 'published' 
              ORDER BY b.views DESC 
              LIMIT 4";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $berita_populer = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = "Gagal memuat berita populer: " . $e->getMessage();
}

// Fungsi untuk memotong teks
// function excerpt($text, $limit = 150) {
//     if (strlen($text) > $limit) {
//         $text = substr($text, 0, $limit) . '...';
//     }
//     return $text;
// }

// Fungsi untuk format tanggal Indonesia
function formatTanggalIndonesia($date)
{
    $bulan = array(
        1 => 'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    );

    $tanggal = date('j', strtotime($date));
    $bulan_num = date('n', strtotime($date));
    $tahun = date('Y', strtotime($date));

    return $tanggal . ' ' . $bulan[$bulan_num] . ' ' . $tahun;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita Desa Winduaji - Kecamatan Paninggaran, Kabupaten Pekalongan</title>
      <link rel="shortcut icon" href="../assets/images/logo.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8fafc;
        }

        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('../assets/images/landingpage/bg.png');
            background-size: cover;
            background-position: center;
            color: white;
        }

        .news-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .news-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .category-btn {
            transition: all 0.3s ease;
        }

        .category-btn.active,
        .category-btn:hover {
            background-color: #dc2626;
            color: white;
        }

        .pagination-btn {
            transition: all 0.3s ease;
        }

        .pagination-btn.active,
        .pagination-btn:hover {
            background-color: #dc2626;
            color: white;
        }
    </style>
</head>

<body>
    <!-- Header/Navbar -->
     <header class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center">
                <img src="../assets/images/logo.png" alt="Logo Desa Winduaji" class="w-12 h-12 mr-3">
                <span class="text-xl font-bold text-red-600">Desa Winduaji</span>
            </div>
            
           <nav class="hidden md:flex space-x-8">
                <a href="../landingpage.php" class="text-gray-700 hover:text-red-600">Beranda</a>
                 <a href="../pages/sejarah.php" class="text-gray-700 hover:text-red-600">Profil Desa</a>
                <a href="../pages/berita.php" class="text-gray-700 font-semibold">Berita</a>
                <a href="../pages/umkm.php" class="text-gray-700 hover:text-red-600">UMKM</a>
                <a href="../pages/galeri.php" class="text-gray-700 hover:text-red-600">Galeri</a>
                <a href="../login.php" class="text-gray-700 hover:text-red-600">admin</a>
            </nav>
            
            <button class="md:hidden text-gray-700">
                <i class="fas fa-bars text-xl"></i>
            </button>
        </div>
    </header>


    <!-- Hero Section -->
    <section class="hero-section py-20">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Berita Desa Winduaji</h1>
            <p class="text-xl mb-8">Informasi Terkini Seputar Kegiatan dan Perkembangan Desa Winduaji</p>
            <a href="#berita" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-6 rounded-lg inline-flex items-center">
                <span>Baca Berita</span>
                <i class="fas fa-arrow-down ml-2"></i>
            </a>
        </div>
    </section>

    <!-- Kategori Section -->
    <section class="py-12 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12">Kategori Berita</h2>
            <div class="flex flex-wrap justify-center gap-4 mb-12">
                <a href="?kategori=" class="category-btn px-6 py-2 rounded-full border <?php echo empty($kategori_filter) ? 'border-red-600 text-red-600 active bg-red-600 text-white' : 'border-gray-300 text-gray-700'; ?> font-medium">Semua</a>
                <?php foreach ($kategori_berita as $kategori): ?>
                    <a href="?kategori=<?php echo $kategori['id']; ?>" class="category-btn px-6 py-2 rounded-full border <?php echo $kategori_filter == $kategori['id'] ? 'border-red-600 text-red-600 active bg-red-600 text-white' : 'border-gray-300 text-gray-700'; ?> font-medium">
                        <?php echo htmlspecialchars($kategori['nama_kategori']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Berita Section -->
    <section id="berita" class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Berita Utama -->
                <div class="lg:col-span-2">
                    <h2 class="text-3xl font-bold mb-8">Berita Terbaru</h2>

                    <?php if (empty($berita_data)): ?>
                        <div class="bg-white rounded-xl p-8 text-center">
                            <i class="fas fa-newspaper text-4xl text-gray-400 mb-4"></i>
                            <p class="text-gray-600">Belum ada berita yang tersedia.</p>
                        </div>
                    <?php else: ?>
                        <!-- Berita utama (yang pertama) -->
                        <?php $berita_utama = $berita_data[0]; ?>
                        <div class="news-card bg-white rounded-xl overflow-hidden shadow-md mb-8">
                            <div class="h-64 md:h-80 overflow-hidden">
                                <?php if (!empty($berita_utama['gambar_path'])): ?>
                                    <img src="../<?php echo $berita_utama['gambar_path']; ?>" alt="<?php echo htmlspecialchars($berita_utama['judul']); ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                        <i class="fas fa-newspaper text-4xl text-gray-400"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="p-6">
                                <div class="flex items-center mb-4">
                                    <span class="bg-red-100 text-red-600 text-sm font-semibold px-3 py-1 rounded-full mr-3">
                                        <?php echo htmlspecialchars($berita_utama['nama_kategori']); ?>
                                    </span>
                                    <span class="text-gray-500 text-sm">
                                        <i class="far fa-calendar-alt mr-1"></i>
                                        <?php echo formatTanggalIndonesia($berita_utama['tanggal_publish']); ?>
                                    </span>
                                </div>
                                <h3 class="text-2xl font-bold mb-3"><?php echo htmlspecialchars($berita_utama['judul']); ?></h3>

                                <a href="detail-berita.php?id=<?php echo $berita_utama['id']; ?>" class="text-red-600 font-semibold inline-flex items-center">
                                    <span>Baca Selengkapnya</span>
                                    <i class="fas fa-arrow-right ml-2"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Dua berita berikutnya -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <?php for ($i = 1; $i < min(3, count($berita_data)); $i++): ?>
                                <?php $berita = $berita_data[$i]; ?>
                                <div class="news-card bg-white rounded-xl overflow-hidden shadow-md">
                                    <div class="h-48 overflow-hidden">
                                        <?php if (!empty($berita['gambar_path'])): ?>
                                            <img src="../<?php echo $berita['gambar_path']; ?>" alt="<?php echo htmlspecialchars($berita['judul']); ?>" class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                                <i class="fas fa-newspaper text-3xl text-gray-400"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="p-5">
                                        <div class="flex items-center mb-3">
                                            <span class="bg-red-100 text-red-600 text-xs font-semibold px-2 py-1 rounded-full mr-2">
                                                <?php echo htmlspecialchars($berita['nama_kategori']); ?>
                                            </span>
                                            <span class="text-gray-500 text-xs">
                                                <i class="far fa-calendar-alt mr-1"></i>
                                                <?php echo formatTanggalIndonesia($berita['tanggal_publish']); ?>
                                            </span>
                                        </div>
                                        <h3 class="text-lg font-bold mb-2"><?php echo htmlspecialchars($berita['judul']); ?></h3>
                                        <p class="text-gray-600 text-sm mb-3"><?php echo excerpt(strip_tags($berita['isi']), 100); ?></p>
                                        <a href="detail-berita.php?id=<?php echo $berita['id']; ?>" class="text-red-600 text-sm font-semibold inline-flex items-center">
                                            <span>Baca Selengkapnya</span>
                                            <i class="fas fa-arrow-right ml-1 text-xs"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-1">
                    <h2 class="text-2xl font-bold mb-6">Berita Populer</h2>

                    <div class="bg-white rounded-xl shadow-md p-5 mb-6">
                        <?php if (empty($berita_populer)): ?>
                            <p class="text-gray-600 text-center py-4">Belum ada berita populer.</p>
                        <?php else: ?>
                            <?php foreach ($berita_populer as $index => $berita): ?>
                                <div class="flex items-start mb-4 <?php echo $index < count($berita_populer) - 1 ? 'pb-4 border-b border-gray-100' : ''; ?>">
                                    <div class="w-16 h-16 overflow-hidden rounded-md mr-4 flex-shrink-0">
                                        <?php if (!empty($berita['gambar_path'])): ?>
                                            <img src="../<?php echo $berita['gambar_path']; ?>" alt="<?php echo htmlspecialchars($berita['judul']); ?>" class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                                <i class="fas fa-newspaper text-gray-400"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold mb-1"><?php echo htmlspecialchars($berita['judul']); ?></h3>
                                        <p class="text-gray-500 text-sm">
                                            <i class="far fa-calendar-alt mr-1"></i>
                                            <?php echo formatTanggalIndonesia($berita['tanggal_publish']); ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>


                </div>

                <!-- Daftar Berita Lainnya -->
                <?php if (count($berita_data) > 3): ?>
                    <h2 class="text-3xl font-bold mt-16 mb-8">Berita Lainnya</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
                        <?php for ($i = 3; $i < count($berita_data); $i++): ?>
                            <?php $berita = $berita_data[$i]; ?>
                            <div class="news-card bg-white rounded-xl overflow-hidden shadow-md">
                                <div class="h-48 overflow-hidden">
                                    <?php if (!empty($berita['gambar_path'])): ?>
                                        <img src="../<?php echo $berita['gambar_path']; ?>" alt="<?php echo htmlspecialchars($berita['judul']); ?>" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                            <i class="fas fa-newspaper text-3xl text-gray-400"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="p-5">
                                    <div class="flex items-center mb-3">
                                        <span class="bg-red-100 text-red-600 text-xs font-semibold px-2 py-1 rounded-full mr-2">
                                            <?php echo htmlspecialchars($berita['nama_kategori']); ?>
                                        </span>
                                        <span class="text-gray-500 text-xs">
                                            <i class="far fa-calendar-alt mr-1"></i>
                                            <?php echo formatTanggalIndonesia($berita['tanggal_publish']); ?>
                                        </span>
                                    </div>
                                    <h3 class="text-lg font-bold mb-2"><?php echo htmlspecialchars($berita['judul']); ?></h3>

                                    <a href="detail-berita.php?id=<?php echo $berita['id']; ?>" class="text-red-600 text-sm font-semibold inline-flex items-center">
                                        <span>Baca Selengkapnya</span>
                                        <i class="fas fa-arrow-right ml-1 text-xs"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="flex justify-center mt-8">
                        <div class="flex space-x-2">
                            <?php if ($current_page > 1): ?>
                                <a href="?page=<?php echo $current_page - 1; ?><?php echo !empty($kategori_filter) ? '&kategori=' . $kategori_filter : ''; ?>" class="pagination-btn w-10 h-10 rounded-full border border-gray-300 text-gray-700 font-semibold flex items-center justify-center">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="?page=<?php echo $i; ?><?php echo !empty($kategori_filter) ? '&kategori=' . $kategori_filter : ''; ?>" class="pagination-btn w-10 h-10 rounded-full border <?php echo $i == $current_page ? 'bg-red-600 text-white' : 'border-gray-300 text-gray-700'; ?> font-semibold flex items-center justify-center">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($current_page < $total_pages): ?>
                                <a href="?page=<?php echo $current_page + 1; ?><?php echo !empty($kategori_filter) ? '&kategori=' . $kategori_filter : ''; ?>" class="pagination-btn w-10 h-10 rounded-full border border-gray-300 text-gray-700 font-semibold flex items-center justify-center">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
    </section>

    <!-- Newsletter Section -->
    <section class="py-16 bg-red-600 text-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold mb-4">Berlangganan Newsletter</h2>
            <p class="text-xl mb-8 max-w-2xl mx-auto">Dapatkan update berita terbaru dari Desa Winduaji langsung ke email Anda.</p>
            <form method="POST" action="subscribe.php" class="max-w-md mx-auto flex flex-col sm:flex-row gap-4">
                <input type="email" name="email" placeholder="Alamat Email Anda" required class="flex-grow px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-300 text-gray-900">
                <button type="submit" class="bg-white text-red-600 px-6 py-3 rounded-lg font-semibold">Berlangganan</button>
            </form>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">Desa Winduaji</h3>
                    <p class="text-gray-400">Kecamatan Paninggaran, Kabupaten Pekalongan, Provinsi Jawa Tengah</p>
                </div>

                <div>
                    <h3 class="text-xl font-bold mb-4">Tautan Cepat</h3>
                    <ul class="space-y-2">
                        <li><a href="../landingpage.php" class="text-gray-400 hover:text-white">Beranda</a></li>
                        <li><a href="berita.php" class="text-gray-400 hover:text-white">Berita</a></li>
                        <li><a href="umkm.php" class="text-gray-400 hover:text-white">UMKM</a></li>
                        <li><a href="galeri.php" class="text-gray-400 hover:text-white">Galeri</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-xl font-bold mb-4">Kontak Kami</h3>
                    <ul class="space-y-2">
                        <li class="flex items-center">
                            <i class="fas fa-phone-alt mr-3 text-gray-400"></i>
                            <span class="text-gray-400">(021) 1234-5678</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-envelope mr-3 text-gray-400"></i>
                            <span class="text-gray-400">info@desawinduaji.id</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fab fa-whatsapp mr-3 text-gray-400"></i>
                            <span class="text-gray-400">+62 812 3456 7890</span>
                        </li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-xl font-bold mb-4">Ikuti Kami</h3>
                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 bg-gray-700 rounded-full flex items-center justify-center hover:bg-red-600">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-700 rounded-full flex items-center justify-center hover:bg-red-600">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-700 rounded-full flex items-center justify-center hover:bg-red-600">
                            <i class="fab fa-youtube"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-700 rounded-full flex items-center justify-center hover:bg-red-600">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2023 Desa Winduaji. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // JavaScript untuk interaksi berita
        document.addEventListener('DOMContentLoaded', function() {
            const categoryButtons = document.querySelectorAll('.category-btn');

            // Filter kategori
            categoryButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    // Remove active class from all buttons
                    categoryButtons.forEach(btn => btn.classList.remove('active', 'bg-red-600', 'text-white'));
                    categoryButtons.forEach(btn => btn.classList.add('border-gray-300', 'text-gray-700'));

                    // Add active class to clicked button
                    this.classList.remove('border-gray-300', 'text-gray-700');
                    this.classList.add('active', 'bg-red-600', 'text-white');
                });
            });
        });
    </script>
</body>

</html>