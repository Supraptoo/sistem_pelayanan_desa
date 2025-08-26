<?php
require_once '../config/database.php';
require_once '../config/functions.php';

// Koneksi database
$db = new database();
$conn = $db->getConnection();

// Inisialisasi variabel
$umkm_data = [];
$kategori_umkm = [];
$kategori_filter = isset($_GET['kategori']) ? (int)$_GET['kategori'] : '';

// Ambil data kategori UMKM
try {
    $query = "SELECT * FROM umkm_kategori ORDER BY nama_kategori";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $kategori_umkm = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = "Gagal memuat kategori UMKM: " . $e->getMessage();
}

// Query untuk mengambil data UMKM
$query_umkm = "SELECT u.*, uk.nama_kategori, uk.icon 
               FROM umkm u 
               LEFT JOIN umkm_kategori uk ON u.kategori_id = uk.id 
               WHERE u.status = 'published'";
$params_umkm = [];

if (!empty($kategori_filter)) {
    $query_umkm .= " AND u.kategori_id = :kategori_id";
    $params_umkm[':kategori_id'] = $kategori_filter;
}

$query_umkm .= " ORDER BY u.created_at DESC";

try {
    $stmt = $conn->prepare($query_umkm);
    
    // Bind parameters
    foreach ($params_umkm as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    $umkm_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = "Gagal memuat data UMKM: " . $e->getMessage();
}

// Ambil data kontak
$kontak_data = [];
try {
    $query = "SELECT * FROM kontak LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $kontak_data = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Fallback jika tabel kontak tidak ada, coba ambil dari no_telp
    try {
        $query = "SELECT * FROM no_telp LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $no_telp_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Format data untuk konsistensi
        if ($no_telp_data) {
            $kontak_data = [
                'telepon' => $no_telp_data['no_telp'] ?? '',
                'whatsapp_number' => $no_telp_data['no_telp'] ?? '',
                'email' => '',
                'facebook_url' => '',
                'instagram_url' => '',
                'youtube_url' => ''
            ];
        }
    } catch (Exception $e2) {
        $error = "Gagal memuat data kontak: " . $e2->getMessage();
    }
}

// Fungsi untuk membersihkan nomor telepon (menghapus karakter non-digit)
function cleanPhoneNumber($phone) {
    return preg_replace('/[^0-9]/', '', $phone);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UMKM Desa Winduaji - Kecamatan Paninggaran, Kabupaten Pekalongan</title>
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
        .umkm-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .umkm-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        .category-btn {
            transition: all 0.3s ease;
        }
        .category-btn.active, .category-btn:hover {
            background-color: #dc2626;
            color: white;
        }
        .whatsapp-btn {
            background-color: #25D366;
            transition: background-color 0.3s ease;
        }
        .whatsapp-btn:hover {
            background-color: #128C7E;
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
            <h1 class="text-4xl md:text-5xl font-bold mb-4">UMKM Desa Winduaji</h1>
            <p class="text-xl mb-8">Produk Unggulan Khas Kecamatan Paninggaran, Kabupaten Pekalongan</p>
            <a href="#produk" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-6 rounded-lg inline-flex items-center">
                <span>Jelajahi Produk</span>
                <i class="fas fa-arrow-down ml-2"></i>
            </a>
        </div>
    </section>

    <!-- Kategori Section -->
    <section class="py-12 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12">Kategori Produk</h2>
            <div class="flex flex-wrap justify-center gap-4 mb-12">
                <a href="?kategori=" class="category-btn px-6 py-2 rounded-full border <?php echo empty($kategori_filter) ? 'border-red-600 text-red-600 active bg-red-600 text-white' : 'border-gray-300 text-gray-700'; ?> font-medium">Semua</a>
                <?php foreach ($kategori_umkm as $kategori): ?>
                    <a href="?kategori=<?php echo $kategori['id']; ?>" class="category-btn px-6 py-2 rounded-full border <?php echo $kategori_filter == $kategori['id'] ? 'border-red-600 text-red-600 active bg-red-600 text-white' : 'border-gray-300 text-gray-700'; ?> font-medium">
                        <?php echo htmlspecialchars($kategori['nama_kategori']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Produk UMKM Section -->
    <section id="produk" class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-4">Produk Unggulan</h2>
            <p class="text-center text-gray-600 mb-12 max-w-2xl mx-auto">Temukan berbagai produk UMKM unggulan dari warga Desa Winduaji yang dibuat dengan bahan-bahan pilihan dan proses tradisional.</p>
            
            <?php if (empty($umkm_data)): ?>
                <div class="bg-white rounded-xl p-8 text-center">
                    <i class="fas fa-store-alt text-4xl text-gray-400 mb-4"></i>
                    <p class="text-gray-600">Belum ada produk UMKM yang tersedia.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach ($umkm_data as $umkm): ?>
                        <div class="umkm-card bg-white rounded-xl overflow-hidden shadow-md">
                            <div class="h-48 overflow-hidden">
                                <?php if (!empty($umkm['foto_utama_path'])): ?>
                                    <img src="../<?php echo $umkm['foto_utama_path']; ?>" alt="<?php echo htmlspecialchars($umkm['nama_umkm']); ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                        <i class="fas fa-store-alt text-4xl text-gray-400"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="p-6">
                                <div class="flex items-center mb-4">
                                    <?php if (!empty($umkm['icon'])): ?>
                                        <div class="bg-red-50 text-red-600 rounded-full p-3 mr-3"><i class="<?php echo $umkm['icon'] ?? 'fas fa-store'; ?> fa-lg"></i></div>
                                    <?php endif; ?>
                                    <span class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full"><?php echo htmlspecialchars($umkm['nama_kategori']); ?></span>
                                </div>
                                
                                <h3 class="text-xl font-bold mb-2"><?php echo htmlspecialchars($umkm['nama_umkm']); ?></h3>
                                <p class="text-gray-600 mb-4 line-clamp-2"><?php echo htmlspecialchars($umkm['deskripsi'] ?? 'Tidak ada deskripsi'); ?></p>
                                
                                <div class="flex justify-between items-center">
                                    <?php if (!empty($umkm['no_telp'])): 
                                        $clean_phone = cleanPhoneNumber($umkm['no_telp']);
                                    ?>
                                        <a href="https://wa.me/<?php echo $clean_phone; ?>?text=Saya%20tertarik%20dengan%20produk%20<?php echo urlencode($umkm['nama_umkm']); ?>%20dari%20Desa%20Winduaji" 
                                           target="_blank" class="whatsapp-btn text-white px-4 py-2 rounded-lg flex items-center">
                                            <i class="fab fa-whatsapp mr-2"></i>
                                            <span>Hubungi</span>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-sm">Tidak ada kontak</span>
                                    <?php endif; ?>
                                    
                                    <a href="detail-umkm-single.php?id=<?php echo $umkm['id']; ?>" class="text-red-600 hover:text-red-800 font-medium flex items-center">
                                        <span>Detail</span>
                                        <i class="fas fa-arrow-right ml-2"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 bg-red-600 text-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold mb-4">Tertarik dengan Produk Kami?</h2>
            <p class="text-xl mb-8 max-w-2xl mx-auto">Pesan sekarang juga dan dapatkan produk UMKM khas Desa Winduaji dengan kualitas terbaik.</p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <?php if (!empty($kontak_data['whatsapp_number'])): 
                    $clean_whatsapp = cleanPhoneNumber($kontak_data['whatsapp_number']);
                ?>
                    <a href="https://wa.me/<?php echo $clean_whatsapp; ?>" class="whatsapp-btn px-6 py-3 rounded-lg font-semibold flex items-center justify-center">
                        <i class="fab fa-whatsapp text-xl mr-2"></i>
                        <span>Hubungi via WhatsApp</span>
                    </a>
                <?php endif; ?>
                <?php if (!empty($kontak_data['email'])): ?>
                    <a href="mailto:<?php echo htmlspecialchars($kontak_data['email']); ?>" class="bg-white text-red-600 px-6 py-3 rounded-lg font-semibold flex items-center justify-center">
                        <i class="fas fa-envelope mr-2"></i>
                        <span>Kirim Email</span>
                    </a>
                <?php endif; ?>
            </div>
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
                        <li><a href="../pages/berita.php" class="text-gray-400 hover:text-white">Berita</a></li>
                        <li><a href="../pages/umkm.php" class="text-gray-400 hover:text-white">UMKM</a></li>
                        <li><a href="../pages/galeri.php" class="text-gray-400 hover:text-white">Galeri</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-xl font-bold mb-4">Kontak Kami</h3>
                    <ul class="space-y-2">
                        <?php if (!empty($kontak_data['telepon'])): ?>
                            <li class="flex items-center">
                                <i class="fas fa-phone-alt mr-3 text-gray-400"></i>
                                <span class="text-gray-400"><?php echo htmlspecialchars($kontak_data['telepon']); ?></span>
                            </li>
                        <?php endif; ?>
                        <?php if (!empty($kontak_data['email'])): ?>
                            <li class="flex items-center">
                                <i class="fas fa-envelope mr-3 text-gray-400"></i>
                                <span class="text-gray-400"><?php echo htmlspecialchars($kontak_data['email']); ?></span>
                            </li>
                        <?php endif; ?>
                        <?php if (!empty($kontak_data['whatsapp_number'])): ?>
                            <li class="flex items-center">
                                <i class="fab fa-whatsapp mr-3 text-gray-400"></i>
                                <span class="text-gray-400"><?php echo htmlspecialchars($kontak_data['whatsapp_number']); ?></span>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-xl font-bold mb-4">Ikuti Kami</h3>
                    <div class="flex space-x-4">
                        <?php if (!empty($kontak_data['facebook_url'])): ?>
                            <a href="<?php echo htmlspecialchars($kontak_data['facebook_url']); ?>" class="w-10 h-10 bg-gray-700 rounded-full flex items-center justify-center hover:bg-red-600">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($kontak_data['instagram_url'])): ?>
                            <a href="<?php echo htmlspecialchars($kontak_data['instagram_url']); ?>" class="w-10 h-10 bg-gray-700 rounded-full flex items-center justify-center hover:bg-red-600">
                                <i class="fab fa-instagram"></i>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($kontak_data['youtube_url'])): ?>
                            <a href="<?php echo htmlspecialchars($kontak_data['youtube_url']); ?>" class="w-10 h-10 bg-gray-700 rounded-full flex items-center justify-center hover:bg-red-600">
                                <i class="fab fa-youtube"></i>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($kontak_data['whatsapp_number'])): ?>
                            <a href="https://wa.me/<?php echo cleanPhoneNumber($kontak_data['whatsapp_number']); ?>" class="w-10 h-10 bg-gray-700 rounded-full flex items-center justify-center hover:bg-red-600">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2023 Desa Winduaji. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // JavaScript untuk interaksi UMKM
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