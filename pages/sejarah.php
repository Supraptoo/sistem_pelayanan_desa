<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';

// Koneksi database
$db = new database();
$conn = $db->getConnection();

// Ambil data tentang_desa (hanya 1 record terbaru)
$query = "SELECT td.* 
          FROM tentang_desa td
          ORDER BY td.updated_at DESC
          LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->execute();
$tentang_desa = $stmt->fetch(PDO::FETCH_ASSOC);

// Jika tidak ada data, buat array kosong agar aman
if (!$tentang_desa) {
    $tentang_desa = [];
}

// Ambil field sesuai tabel tentang_desa
$sejarah          = $tentang_desa['sejarah'] ?? "";
$visi             = $tentang_desa['visi'] ?? "";
$misi             = $tentang_desa['misi'] ?? "";
$geografis        = $tentang_desa['geografis'] ?? "";
$luas_wilayah     = $tentang_desa['luas_wilayah'] ?? "";
$struktur_pemerintahan = $tentang_desa['struktur_pemerintahan'] ?? "";

// Data kepemimpinan
$kepala_desa        = $tentang_desa['kepala_desa'] ?? "";
$sekretaris_desa    = $tentang_desa['sekretaris_desa'] ?? "";
$kasi_pemerintahan  = $tentang_desa['kasi_pemerintahan'] ?? "";
$kasi_kesejahteraan = $tentang_desa['kasi_kesejahteraan'] ?? "";
$kasi_pelayanan     = $tentang_desa['kasi_pelayanan'] ?? "";
$kaur_keuangan      = $tentang_desa['kaur_keuangan'] ?? "";
$kaur_tata_usaha    = $tentang_desa['kaur_tata_usaha'] ?? "";
$kaur_perencanaan   = $tentang_desa['kaur_perencanaan'] ?? "";

// Linimasa
$linimasa_1920    = $tentang_desa['linimasa_1920'] ?? "Pembentukan awal Desa Winduaji";
$linimasa_1945    = $tentang_desa['linimasa_1945'] ?? "Peran aktif dalam perjuangan kemerdekaan";
$linimasa_1970    = $tentang_desa['linimasa_1970'] ?? "Pengembangan sektor pertanian dan perkebunan";
$linimasa_1995    = $tentang_desa['linimasa_1995'] ?? "Pembangunan infrastruktur dasar desa";
$linimasa_2010    = $tentang_desa['linimasa_2010'] ?? "Pengembangan UMKM dan ekonomi kreatif";
$linimasa_2020    = $tentang_desa['linimasa_2020'] ?? "Digitalisasi pelayanan dan pengembangan wisata desa";

// Dukuh
$dukuh_plumbon    = $tentang_desa['dukuh_plumbon'] ?? "Dukuh Plumbon merupakan pusat pemerintahan desa dengan berbagai fasilitas umum";
$dukuh_winduaji   = $tentang_desa['dukuh_winduaji'] ?? "Dukuh Winduaji dikenal dengan potensi pertanian dan perkebunannya";
$dukuh_simbang    = $tentang_desa['dukuh_simbang'] ?? "Dukuh Simbang memiliki keindahan alam yang menjadi daya tarik wisata";
$dukuh_sidomas    = $tentang_desa['dukuh_sidomas'] ?? "Dukuh Sidomas berkembang dengan sentra kerajinan dan UMKM";

// Potensi
$pertanian        = $tentang_desa['pertanian'] ?? "Komoditas utama: padi, jagung, dan palawija dengan sistem irigasi yang baik";
$perkebunan       = $tentang_desa['perkebunan'] ?? "Perkebunan kopi, cengkeh, dan tanaman rempah lainnya yang menjadi unggulan";
$peternakan       = $tentang_desa['peternakan'] ?? "Peternakan sapi, kambing, dan unggas dengan sistem pemeliharaan modern";
$pariwisata       = $tentang_desa['pariwisata'] ?? "Wisata alam, agrowisata, dan homestay dengan pemandangan pegunungan";
$kerajinan        = $tentang_desa['kerajinan'] ?? "Kerajinan tangan anyaman bambu, kerupuk, dan produk olahan kopi";

// Info kontak (ambil dari tabel desa_profil sebagai fallback)
$query_desa = "SELECT * FROM desa_profil ORDER BY updated_at DESC LIMIT 1";
$stmt_desa = $conn->prepare($query_desa);
$stmt_desa->execute();
$desa_profil = $stmt_desa->fetch(PDO::FETCH_ASSOC) ?: [];

$desa_nama        = $desa_profil['nama_desa'] ?? "Desa Winduaji";
$desa_lokasi      = $desa_profil['lokasi'] ?? "Kecamatan Paninggaran, Kabupaten Pekalongan, Provinsi Jawa Tengah";
$desa_motto       = $tentang_desa['motto'] ?? $desa_profil['motto'] ?? "Bersama Membangun Desa yang Mandiri dan Berbudaya";
$alamat_kantor    = $tentang_desa['alamat'] ?? $desa_profil['alamat_kantor'] ?? "Plumbon, Winduaji, Kec. Paninggaran, Kabupaten Pekalongan, Jawa Tengah 51164";
$telepon          = $tentang_desa['telepon'] ?? $desa_profil['telepon'] ?? "087766554433";
$email            = $tentang_desa['email'] ?? $desa_profil['email'] ?? "tes@gmail.com";

// Info update admin
$updated_at       = $tentang_desa['updated_at'] ?? null;

// Fungsi untuk tampil aman
function safeDisplay($value, $default = '') {
    if (is_array($value)) return $default;
    return htmlspecialchars($value ?? $default);
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil <?php echo safeDisplay($desa_nama); ?> - Kecamatan Paninggaran, Kabupaten Pekalongan</title>
    <link rel="shortcut icon" href="../assets/images/logo.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
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
        .back-to-top.hidden {
            opacity: 0;
            visibility: hidden;
        }
        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('../assets/images/landingpage/bg.png');
            background-size: cover;
            background-position: center;
            color: white;
        }
        .stat-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        .tab-button {
            transition: all 0.3s ease;
        }
        .tab-button.active, .tab-button:hover {
            background-color: #dc2626;
            color: white;
        }
        .timeline {
            position: relative;
            padding-left: 3rem;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            height: 100%;
            width: 2px;
            background-color: #dc2626;
        }
        .timeline-item {
            position: relative;
            margin-bottom: 2rem;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -3rem;
            top: 5px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background-color: #dc2626;
            border: 4px solid #fecaca;
        }
    </style>
</head>
<body>
   <!-- Mobile Menu Overlay -->
    <div id="mobile-menu-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden transition-opacity duration-300"></div>
    
    <header class="w-full border-b border-gray-200 bg-white shadow-sm sticky top-0 z-50 transition-all duration-300">
        <nav class="container mx-auto flex items-center justify-between py-3 px-4 md:px-6 lg:px-8">
            <a class="flex items-center space-x-2" href="#">
                <img alt="Logo Desa <?php echo safeDisplay($desa_nama); ?>" class="h-12 w-auto transition-transform duration-300 hover:scale-105" height="50" src="../assets/images/logo.png" width="150" />
                <span class="text-xl font-bold text-red-600 hidden md:block"><?php echo safeDisplay($desa_nama); ?></span>
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
                <img alt="Logo Desa <?php echo safeDisplay($desa_nama); ?>" class="h-10 w-auto" src="../assets/images/logo.png" />
                <button id="mobile-menu-close" class="text-gray-600 hover:text-red-600 transition-colors duration-200">
                    <i class="fas fa-times fa-lg"></i>
                </button>
            </div>
            <ul class="space-y-6">
                <li><a class="text-red-600 hover:text-red-700 font-medium block py-2 transition-colors duration-200" href="../landingpage.php">Beranda</a></li>
                <li><a class="text-gray-700 hover:text-red-600 font-medium block py-2 transition-colors duration-200" href="../pages/umkm.php">UMKM</a></li>
                <li><a class="text-gray-700 hover:text-red-600 font-medium block py-2 transition-colors duration-200" href="../pages/berita.php">Berita</a></li>
                <li><a class="text-gray-700 hover:text-red-600 font-medium block py-2 transition-colors duration-200" href="../pages/galeri.php">Galeri</a></li>
                <li><a class="text-gray-700 hover:text-red-600 font-medium block py-2 transition-colors duration-200" href="../login.php">Admin</a></li>
            </ul>
        </div>
    </header>
  
    <!-- Hero Section -->
    <section class="hero-section py-20">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Profil <?php echo safeDisplay($desa_nama); ?></h1>
            <p class="text-xl mb-8">Mengenal Lebih Dekat Keindahan dan Kehidupan Masyarakat <?php echo safeDisplay($desa_nama); ?></p>
            <a href="#profil" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-6 rounded-lg inline-flex items-center">
                <span>Jelajahi Profil</span>
                <i class="fas fa-arrow-down ml-2"></i>
            </a>
        </div>
    </section>

     <!-- Profil Section -->
    <section id="profil" class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="flex flex-col lg:flex-row gap-12">
                <!-- Main Content -->
                <div class="lg:w-2/3">
                    <h2 class="text-3xl font-bold mb-6">Tentang <?php echo safeDisplay($desa_nama); ?></h2>
                    <p class="text-gray-600 mb-6">
                        <?php echo safeDisplay($desa_nama); ?>, yang terletak di <?php echo safeDisplay($desa_lokasi); ?>, memiliki sejarah yang berkaitan erat dengan keberadaan wilayah tersebut. Desa ini terdiri dari empat dukuh: Plumbon, Winduaji, Simbang, dan Sidomas.
                    </p>
                    
                    <p class="text-gray-600 mb-8">
                        <?php echo safeDisplay($sejarah); ?>
                    </p>
                    
                    <!-- Tabs -->
                    <div class="mb-8">
                        <div class="flex flex-wrap border-b border-gray-200 mb-6">
                            <button class="tab-button active px-6 py-3 font-medium text-white bg-red-600" data-tab="sejarah">Sejarah</button>
                            <button class="tab-button px-6 py-3 font-medium text-gray-600" data-tab="visi-misi">Visi & Misi</button>
                            <button class="tab-button px-6 py-3 font-medium text-gray-600" data-tab="geografis">Geografis</button>
                            <button class="tab-button px-6 py-3 font-medium text-gray-600" data-tab="pemerintahan">Pemerintahan</button>
                        </div>
                        
                        <div id="sejarah" class="tab-content active">
                            <h3 class="text-2xl font-bold mb-4">Sejarah <?php echo safeDisplay($desa_nama); ?></h3>
                            <p class="text-gray-600 mb-4">
                                <?php echo safeDisplay($sejarah); ?>
                            </p>
                            <p class="text-gray-600">
                                Pada awalnya, <?php echo safeDisplay($desa_nama); ?> merupakan bagian dari wilayah yang lebih besar yang kemudian dimekarkan menjadi beberapa desa. Pemekaran ini terjadi pada tahun 1920-an, menjadikan <?php echo safeDisplay($desa_nama); ?> sebagai desa mandiri dengan identitas dan pemerintahan sendiri.
                            </p>
                        </div>
                        
                        <div id="visi-misi" class="tab-content hidden">
                            <h3 class="text-2xl font-bold mb-4">Visi <?php echo safeDisplay($desa_nama); ?></h3>
                            <p class="text-gray-600 mb-6">
                                "<?php echo safeDisplay($visi); ?>"
                            </p>
                            
                            <h3 class="text-2xl font-bold mb-4">Misi <?php echo safeDisplay($desa_nama); ?></h3>
                            <ul class="list-disc list-inside text-gray-600 space-y-2">
                                <?php
                                $misi_points = explode("\n", $misi);
                                foreach ($misi_points as $point) {
                                    if (!empty(trim($point))) {
                                        echo "<li>" . safeDisplay(trim($point)) . "</li>";
                                    }
                                }
                                ?>
                            </ul>
                        </div>
                        
                        <div id="geografis" class="tab-content hidden">
                            <h3 class="text-2xl font-bold mb-4">Kondisi Geografis</h3>
                            <p class="text-gray-600 mb-4">
                                <?php echo safeDisplay($desa_nama); ?> terletak di <?php echo safeDisplay($desa_lokasi); ?>. Topografi desa ini didominasi oleh perbukitan dengan ketinggian antara 400-800 meter di atas permukaan laut.
                            </p>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <h4 class="font-semibold mb-2">Batas Wilayah:</h4>
                                    <ul class="text-gray-600 space-y-1">
                                        <li><strong>Utara:</strong> Desa Simego</li>
                                        <li><strong>Timur:</strong> Desa Lebakbarang</li>
                                        <li><strong>Selatan:</strong> Desa Tlagomulyo</li>
                                        <li><strong>Barat:</strong> Desa Sidasari</li>
                                    </ul>
                                </div>
                                
                                <div>
                                    <h4 class="font-semibold mb-2">Luas Wilayah:</h4>
                                    <ul class="text-gray-600 space-y-1">
                                        <li><strong>Total:</strong> <?php echo safeDisplay($luas_wilayah); ?> Ha</li>
                                        <li><strong>Pemukiman:</strong> 85 Ha</li>
                                        <li><strong>Pertanian:</strong> 320 Ha</li>
                                        <li><strong>Perkebunan:</strong> 180 Ha</li>
                                        <li><strong>Hutan:</strong> 250 Ha</li>
                                        <li><strong>Lain-lain:</strong> 40 Ha</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div id="pemerintahan" class="tab-content hidden">
                            <h3 class="text-2xl font-bold mb-4">Struktur Pemerintahan</h3>
                            <p class="text-gray-600 mb-6">
                                Pemerintahan <?php echo safeDisplay($desa_nama); ?> dipimpin oleh seorang Kepala Desa yang dibantu oleh perangkat desa dan Badan Permusyawaratan Desa (BPD).
                            </p>
                            
                            <h4 class="text-xl font-semibold mb-3">Struktur Kepemimpinan:</h4>
                            <ul class="text-gray-600 space-y-2">
                                <li><strong>Kepala Desa:</strong> <?php echo safeDisplay($kepala_desa); ?></li>
                                <li><strong>Sekretaris Desa:</strong> <?php echo safeDisplay($sekretaris_desa); ?></li>
                                <li><strong>Kasi Pemerintahan:</strong> <?php echo safeDisplay($kasi_pemerintahan); ?></li>
                                <li><strong>Kasi Kesejahteraan:</strong> <?php echo safeDisplay($kasi_kesejahteraan); ?></li>
                                <li><strong>Kasi Pelayanan:</strong> <?php echo safeDisplay($kasi_pelayanan); ?></li>
                                <li><strong>Kaur Keuangan:</strong> <?php echo safeDisplay($kaur_keuangan); ?></li>
                                <li><strong>Kaur Tata Usaha:</strong> <?php echo safeDisplay($kaur_tata_usaha); ?></li>
                                <li><strong>Kaur Perencanaan:</strong> <?php echo safeDisplay($kaur_perencanaan); ?></li>                            </ul>
                        </div>
                    </div>
                    
                    <!-- Timeline Sejarah -->
                    <h3 class="text-2xl font-bold mb-6">Linimasa Sejarah Desa</h3>
                    <div class="timeline mb-12">
                        <div class="timeline-item">
                            <h4 class="text-xl font-semibold mb-2">1920</h4>
                            <p class="text-gray-600"><?php echo safeDisplay($linimasa_1920); ?></p>
                        </div>
                        
                        <div class="timeline-item">
                            <h4 class="text-xl font-semibold mb-2">1945</h4>
                            <p class="text-gray-600"><?php echo safeDisplay($linimasa_1945); ?></p>
                        </div>
                        
                        <div class="timeline-item">
                            <h4 class="text-xl font-semibold mb-2">1970</h4>
                            <p class="text-gray-600"><?php echo safeDisplay($linimasa_1970); ?></p>
                        </div>
                        
                        <div class="timeline-item">
                            <h4 class="text-xl font-semibold mb-2">1995</h4>
                            <p class="text-gray-600"><?php echo safeDisplay($linimasa_1995); ?></p>
                        </div>
                        
                        <div class="timeline-item">
                            <h4 class="text-xl font-semibold mb-2">2010</h4>
                            <p class="text-gray-600"><?php echo safeDisplay($linimasa_2010); ?></p>
                        </div>
                        
                        <div class="timeline-item">
                            <h4 class="text-xl font-semibold mb-2">2020</h4>
                            <p class="text-gray-600"><?php echo safeDisplay($linimasa_2020); ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar -->
                <div class="lg:w-1/3">
                    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                        <h3 class="text-xl font-bold mb-4">Informasi Kontak</h3>
                        <ul class="space-y-3">
                            <li class="flex items-start">
                                <div class="bg-red-100 p-2 rounded-full mr-3">
                                    <i class="fas fa-map-marker-alt text-red-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold">Alamat</h4>
                                    <p class="text-gray-600"><?php echo safeDisplay($alamat_kantor); ?></p>
                                </div>
                            </li>
                            
                            <li class="flex items-start">
                                <div class="bg-red-100 p-2 rounded-full mr-3">
                                    <i class="fas fa-phone-alt text-red-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold">Telepon</h4>
                                    <p class="text-gray-600"><?php echo safeDisplay($telepon); ?></p>
                                </div>
                            </li>
                            
                            <li class="flex items-start">
                                <div class="bg-red-100 p-2 rounded-full mr-3">
                                    <i class="fas fa-envelope text-red-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold">Email</h4>
                                    <p class="text-gray-600"><?php echo safeDisplay($email); ?></p>
                                </div>
                            </li>
                            
                            
                        </ul>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                        <h3 class="text-xl font-bold mb-4">Lokasi Desa</h3>
                        <div class="h-64 bg-gray-200 rounded-lg overflow-hidden">
                            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3955.030574473294!2d109.5566585!3d-7.161912299999999!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e6fe3ceb99d111b%3A0x11bb0af36ed14b2f!2sBalai%20desa%20winduaji!5e0!3m2!1sid!2sid!4v1724395200000!5m2!1sid!2sid" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                    </div>
                    
                    <div class="bg-red-50 border border-red-200 rounded-xl shadow-md p-6">
                        <h3 class="text-xl font-bold mb-4">Motto Desa</h3>
                        <blockquote class="text-red-700 italic text-center font-semibold">
                            "<?php echo safeDisplay($desa_motto); ?>"
                        </blockquote>
                        <p class="text-gray-600 text-sm text-center mt-3">
                            Motto ini mencerminkan semangat gotong royong masyarakat <?php echo safeDisplay($desa_nama); ?> dalam membangun desa yang mandiri secara ekonomi sekaligus menjaga kelestarian budaya lokal.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Wilayah Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12">Wilayah Desa</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
                <div>
                    <h3 class="text-2xl font-bold mb-4">Dukuh di <?php echo safeDisplay($desa_nama); ?></h3>
                    <p class="text-gray-600 mb-6">
                        <?php echo safeDisplay($desa_nama); ?> terdiri dari 4 dukuh yang masing-masing memiliki karakteristik dan potensi unggulan:
                    </p>
                    
                    <div class="space-y-4">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-lg font-semibold mb-2 text-red-600">Dukuh Plumbon</h4>
                            <p class="text-gray-600"><?php echo safeDisplay($dukuh_plumbon); ?></p>
                        </div>
                        
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-lg font-semibold mb-2 text-red-600">Dukuh Winduaji</h4>
                            <p class="text-gray-600"><?php echo safeDisplay($dukuh_winduaji); ?></p>
                        </div>
                        
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-lg font-semibold mb-2 text-red-600">Dukuh Simbang</h4>
                            <p class="text-gray-600"><?php echo safeDisplay($dukuh_simbang); ?></p>
                        </div>
                        
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-lg font-semibold mb-2 text-red-600">Dukuh Sidomas</h4>
                            <p class="text-gray-600"><?php echo safeDisplay($dukuh_sidomas); ?></p>
                        </div>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-2xl font-bold mb-4">Potensi Desa</h3>
                    <p class="text-gray-600 mb-6">
                        <?php echo safeDisplay($desa_nama); ?> memiliki berbagai potensi yang dapat dikembangkan untuk kesejahteraan masyarakat:
                    </p>
                    
                    <div class="space-y-4">
                        <div class="bg-red-50 p-4 rounded-lg">
                            <h4 class="text-lg font-semibold mb-2">Pertanian</h4>
                            <p class="text-gray-600"><?php echo safeDisplay($pertanian); ?></p>
                        </div>
                        
                        <div class="bg-red-50 p-4 rounded-lg">
                            <h4 class="text-lg font-semibold mb-2">Perkebunan</h4>
                            <p class="text-gray-600"><?php echo safeDisplay($perkebunan); ?></p>
                        </div>
                        
                        <div class="bg-red-50 p-4 rounded-lg">
                            <h4 class="text-lg font-semibold mb-2">Peternakan</h4>
                            <p class="text-gray-600"><?php echo safeDisplay($peternakan); ?></p>
                        </div>
                        
                        <div class="bg-red-50 p-4 rounded-lg">
                            <h4 class="text-lg font-semibold mb-2">Pariwisata</h4>
                            <p class="text-gray-600"><?php echo safeDisplay($pariwisata); ?></p>
                        </div>
                        
                        <div class="bg-red-50 p-4 rounded-lg">
                            <h4 class="text-lg font-semibold mb-2">Kerajinan</h4>
                            <p class="text-gray-600"><?php echo safeDisplay($kerajinan); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4"><?php echo safeDisplay($desa_nama); ?></h3>
                    <p class="text-gray-400"><?php echo safeDisplay($desa_lokasi); ?></p>
                </div>
                
                <div>
                    <h3 class="text-xl font-bold mb-4">Tautan Cepat</h3>
                    <ul class="space-y-2">
                        <li><a href="../landingpage.php" class="text-gray-400 hover:text-white">Beranda</a></li>
                        <li><a href="sejarah.php" class="text-gray-400 hover:text-white">Profil Desa</a></li>
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
                            <span class="text-gray-400"><?php echo safeDisplay($telepon); ?></span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-envelope mr-3 text-gray-400"></i>
                            <span class="text-gray-400"><?php echo safeDisplay($email); ?></span>
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
                <p>&copy; 2023 <?php echo safeDisplay($desa_nama); ?>. All rights reserved.</p>
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
        
        // JavaScript untuk tab system
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const tabId = this.getAttribute('data-tab');
                    
                    // Remove active class from all buttons
                    tabButtons.forEach(btn => {
                        btn.classList.remove('active', 'bg-red-600', 'text-white');
                        btn.classList.add('text-gray-600');
                    });
                    
                    // Add active class to clicked button
                    this.classList.remove('text-gray-600');
                    this.classList.add('active', 'bg-red-600', 'text-white');
                    
                    // Hide all tab contents
                    tabContents.forEach(content => {
                        content.classList.add('hidden');
                        content.classList.remove('active');
                    });
                    
                    // Show the selected tab content
                    document.getElementById(tabId).classList.remove('hidden');
                    document.getElementById(tabId).classList.add('active');
                });
            });
        });
    </script>
</body>
</html>