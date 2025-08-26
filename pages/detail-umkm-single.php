<?php
require_once '../config/database.php';
require_once '../config/functions.php';

// Koneksi database
$db = new database();
$conn = $db->getConnection();

// Pastikan parameter ID ada
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: umkm.php');
    exit();
}

$umkm_id = (int)$_GET['id'];

// Ambil data UMKM berdasarkan ID
$umkm_data = [];
$galeri_umkm = [];

try {
    // Query untuk mengambil data UMKM
    $query_umkm = "SELECT u.*, uk.nama_kategori, uk.icon 
                   FROM umkm u 
                   LEFT JOIN umkm_kategori uk ON u.kategori_id = uk.id 
                   WHERE u.id = :id AND u.status = 'published'";

    $stmt = $conn->prepare($query_umkm);
    $stmt->bindValue(':id', $umkm_id, PDO::PARAM_INT);
    $stmt->execute();
    $umkm_data = $stmt->fetch(PDO::FETCH_ASSOC);

    // Jika data tidak ditemukan, redirect ke halaman UMKM
    if (!$umkm_data) {
        header('Location: umkm.php');
        exit();
    }

    // Query untuk mengambil galeri UMKM
    $query_galeri = "SELECT * FROM umkm_galeri WHERE umkm_id = :umkm_id ORDER BY created_at DESC";
    $stmt_galeri = $conn->prepare($query_galeri);
    $stmt_galeri->bindValue(':umkm_id', $umkm_id, PDO::PARAM_INT);
    $stmt_galeri->execute();
    $galeri_umkm = $stmt_galeri->fetchAll(PDO::FETCH_ASSOC);
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
function cleanPhoneNumber($phone)
{
    return preg_replace('/[^0-9]/', '', $phone);
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($umkm_data['nama_umkm']); ?> - UMKM Desa Winduaji</title>
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

        .whatsapp-btn {
            background-color: #25D366;
            transition: background-color 0.3s ease;
        }

        .whatsapp-btn:hover {
            background-color: #128C7E;
        }

        .gallery-item {
            transition: transform 0.3s ease;
        }

        .gallery-item:hover {
            transform: scale(1.05);
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

    <!-- Breadcrumb -->
    <div class="bg-gray-100 py-4">
        <div class="container mx-auto px-4">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2">
                    <li>
                        <a href="../landingpage.php" class="text-gray-500 hover:text-red-600">Beranda</a>
                    </li>
                    <li>
                        <span class="text-gray-400 mx-2">/</span>
                    </li>
                    <li>
                        <a href="umkm.php" class="text-gray-500 hover:text-red-600">UMKM</a>
                    </li>
                    <li>
                        <span class="text-gray-400 mx-2">/</span>
                    </li>
                    <li class="text-gray-700 truncate" aria-current="page">
                        <?php echo htmlspecialchars($umkm_data['nama_umkm']); ?>
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Detail UMKM Section -->
    <section class="py-12">
        <div class="container mx-auto px-4">
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="md:flex">
                    <!-- Gambar Utama -->
                    <div class="md:w-1/2">
                        <?php if (!empty($umkm_data['foto_utama_path'])): ?>
                            <img src="../<?php echo $umkm_data['foto_utama_path']; ?>" alt="<?php echo htmlspecialchars($umkm_data['nama_umkm']); ?>" class="w-full h-96 object-cover">
                        <?php else: ?>
                            <div class="w-full h-96 bg-gray-200 flex items-center justify-center">
                                <i class="fas fa-store-alt text-6xl text-gray-400"></i>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Informasi UMKM -->
                    <div class="md:w-1/2 p-8">
                        <div class="flex items-center mb-4">
                            <?php if (!empty($umkm_data['icon'])): ?>
                                <div class="bg-red-50 text-red-600 rounded-full p-3 mr-3"><i class="<?php echo $umkm_data['icon'] ?? 'fas fa-store'; ?> fa-lg"></i></div>
                            <?php endif; ?>
                            <span class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full"><?php echo htmlspecialchars($umkm_data['nama_kategori']); ?></span>
                        </div>

                        <h1 class="text-3xl font-bold mb-4"><?php echo htmlspecialchars($umkm_data['nama_umkm']); ?></h1>

                        <div class="mb-6">
                            <p class="text-gray-700 leading-relaxed"><?php echo nl2br(htmlspecialchars($umkm_data['deskripsi'] ?? 'Tidak ada deskripsi')); ?></p>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-lg font-semibold mb-2">Informasi Kontak</h3>
                            <?php if (!empty($umkm_data['no_telp'])): ?>
                                <div class="flex items-center mb-2">
                                    <i class="fas fa-phone-alt text-red-600 mr-3"></i>
                                    <span>+<?php echo htmlspecialchars($umkm_data['no_telp']); ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($umkm_data['alamat'])): ?>
                                <div class="flex items-start mb-2">
                                    <i class="fas fa-map-marker-alt text-red-600 mr-3 mt-1"></i>   
                                    <span><?php echo nl2br(htmlspecialchars($umkm_data['alamat'])); ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($umkm_data['harga'])): ?>
                                <div class="flex items-center mb-2">
                                    <i class="fas fa-solid fa-money-bill-1-wave text-red-600 mr-3"></i>
                                    <span><?php echo htmlspecialchars($umkm_data['harga']); ?> / pcs</span>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($umkm_data['varian'])): ?>
                                <div class="flex items-center mb-2">
                                    <i class="fas fa-solid fa-list-check text-red-600 mr-3"></i>
                                    <span><?php echo htmlspecialchars($umkm_data['varian']); ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($umkm_data['email'])): ?>
                                <div class="flex items-center mb-2">
                                    <i class="fas fa-envelope text-red-600 mr-3"></i>
                                    <span><?php echo htmlspecialchars($umkm_data['email']); ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($umkm_data['media_sosial'])): ?>
                                <div class="flex items-center mb-2">
                                    <i class="fas fa-hashtag text-red-600 mr-3"></i>
                                    <span><?php echo htmlspecialchars($umkm_data['media_sosial']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($umkm_data['no_telp'])):
                            $clean_phone = cleanPhoneNumber($umkm_data['no_telp']);
                        ?>
                            <a href="https://wa.me/<?php echo $clean_phone; ?>?text=Saya%20tertarik%20dengan%20produk%20<?php echo urlencode($umkm_data['nama_umkm']); ?>%20dari%20Desa%20Winduaji"
                                target="_blank" class="whatsapp-btn text-white px-6 py-3 rounded-lg font-semibold inline-flex items-center">
                                <i class="fab fa-whatsapp mr-2 text-xl"></i>
                                <span>Hubungi via WhatsApp</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Galeri UMKM -->
                <?php if (!empty($galeri_umkm)): ?>
                    <div class="px-8 pb-8">
                        <h3 class="text-2xl font-bold mb-6">Galeri Produk</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            <?php foreach ($galeri_umkm as $gambar): ?>
                                <div class="gallery-item rounded-lg overflow-hidden shadow-md">
                                    <img src="../<?php echo $gambar['gambar_path']; ?>" alt="Galeri <?php echo htmlspecialchars($umkm_data['nama_umkm']); ?>" class="w-full h-40 object-cover">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Tombol Kembali -->
            <div class="mt-8">
                <a href="umkm.php" class="inline-flex items-center text-red-600 hover:text-red-800 font-semibold">
                    <i class="fas fa-arrow-left mr-2"></i>
                    <span>Kembali ke Daftar UMKM</span>
                </a>
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
</body>

</html>