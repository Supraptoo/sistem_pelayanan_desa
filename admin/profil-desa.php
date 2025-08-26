<?php
session_start();

require_once '../config/database.php';
require_once '../config/functions.php';

// Koneksi database
$db = new database();
$conn = $db->getConnection();

// Inisialisasi variabel
$success = '';
$error = '';

// Ambil data tentang desa
$tentang_desa = [];
try {
    $query = "SELECT * FROM tentang_desa LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $tentang_desa = $stmt->fetch(PDO::FETCH_ASSOC);

    // Jika belum ada data, inisialisasi dengan array kosong
    if (!$tentang_desa) {
        $tentang_desa = [
            'id' => '',
            'sejarah' => '',
            'visi' => '',
            'misi' => '',
            'geografis' => '',
            'struktur_pemerintahan' => '',
            'kepala_desa' => '',
            'sekretaris_desa' => '',
            'kasi_pemerintahan' => '',
            'kasi_kesejahteraan' => '',
            'kasi_pelayanan' => '',
            'kaur_keuangan' => '',
            'kaur_tata_usaha' => '',
            'kaur_perencanaan' => '',
            'linimasa_1920' => '',
            'linimasa_1945' => '',
            'linimasa_1970' => '',
            'linimasa_1995' => '',
            'linimasa_2010' => '',
            'linimasa_2020' => '',
            'alamat' => '',
            'telepon' => '',
            'email' => '',
            'motto' => '',
            'motto_deskripsi' => '',
            'dukuh_plumbon' => '',
            'dukuh_winduaji' => '',
            'dukuh_simbang' => '',
            'dukuh_sidomas' => '',
            'pertanian' => '',
            'perkebunan' => '',
            'peternakan' => '',
            'pariwisata' => '',
            'kerajinan' => ''
        ];
    }
} catch (Exception $e) {
    $error = "Gagal memuat data tentang desa: " . $e->getMessage();
}

// Proses update data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Kumpulkan semua data dari form
    $data = [
        'sejarah' => $_POST['sejarah'] ?? '',
        'visi' => $_POST['visi'] ?? '',
        'misi' => $_POST['misi'] ?? '',
        'geografis' => $_POST['geografis'] ?? '',
        'struktur_pemerintahan' => $_POST['struktur_pemerintahan'] ?? '',
        'kepala_desa' => $_POST['kepala_desa'] ?? '',
        'sekretaris_desa' => $_POST['sekretaris_desa'] ?? '',
        'kasi_pemerintahan' => $_POST['kasi_pemerintahan'] ?? '',
        'kasi_kesejahteraan' => $_POST['kasi_kesejahteraan'] ?? '',
        'kasi_pelayanan' => $_POST['kasi_pelayanan'] ?? '',
        'kaur_keuangan' => $_POST['kaur_keuangan'] ?? '',
        'kaur_tata_usaha' => $_POST['kaur_tata_usaha'] ?? '',
        'kaur_perencanaan' => $_POST['kaur_perencanaan'] ?? '',
        'linimasa_1920' => $_POST['linimasa_1920'] ?? '',
        'linimasa_1945' => $_POST['linimasa_1945'] ?? '',
        'linimasa_1970' => $_POST['linimasa_1970'] ?? '',
        'linimasa_1995' => $_POST['linimasa_1995'] ?? '',
        'linimasa_2010' => $_POST['linimasa_2010'] ?? '',
        'linimasa_2020' => $_POST['linimasa_2020'] ?? '',
        'alamat' => $_POST['alamat'] ?? '',
        'telepon' => $_POST['telepon'] ?? '',
        'email' => $_POST['email'] ?? '',
        'motto' => $_POST['motto'] ?? '',
        'motto_deskripsi' => $_POST['motto_deskripsi'] ?? '',
        'dukuh_plumbon' => $_POST['dukuh_plumbon'] ?? '',
        'dukuh_winduaji' => $_POST['dukuh_winduaji'] ?? '',
        'dukuh_simbang' => $_POST['dukuh_simbang'] ?? '',
        'dukuh_sidomas' => $_POST['dukuh_sidomas'] ?? '',
        'pertanian' => $_POST['pertanian'] ?? '',
        'perkebunan' => $_POST['perkebunan'] ?? '',
        'peternakan' => $_POST['peternakan'] ?? '',
        'pariwisata' => $_POST['pariwisata'] ?? '',
        'kerajinan' => $_POST['kerajinan'] ?? ''
    ];

    try {
        if (!empty($tentang_desa['id'])) {
            // UPDATE data yang sudah ada
            $query = "UPDATE tentang_desa SET 
                sejarah = :sejarah,
                visi = :visi,
                misi = :misi,
                geografis = :geografis,
                struktur_pemerintahan = :struktur_pemerintahan,
                kepala_desa = :kepala_desa,
                sekretaris_desa = :sekretaris_desa,
                kasi_pemerintahan = :kasi_pemerintahan,
                kasi_kesejahteraan = :kasi_kesejahteraan,
                kasi_pelayanan = :kasi_pelayanan,
                kaur_keuangan = :kaur_keuangan,
                kaur_tata_usaha = :kaur_tata_usaha,
                kaur_perencanaan = :kaur_perencanaan,
                linimasa_1920 = :linimasa_1920,
                linimasa_1945 = :linimasa_1945,
                linimasa_1970 = :linimasa_1970,
                linimasa_1995 = :linimasa_1995,
                linimasa_2010 = :linimasa_2010,
                linimasa_2020 = :linimasa_2020,
                alamat = :alamat,
                telepon = :telepon,
                email = :email,
                motto = :motto,
                motto_deskripsi = :motto_deskripsi,
                dukuh_plumbon = :dukuh_plumbon,
                dukuh_winduaji = :dukuh_winduaji,
                dukuh_simbang = :dukuh_simbang,
                dukuh_sidomas = :dukuh_sidomas,
                pertanian = :pertanian,
                perkebunan = :perkebunan,
                peternakan = :peternakan,
                pariwisata = :pariwisata,
                kerajinan = :kerajinan,
                updated_at = CURRENT_TIMESTAMP
                WHERE id = :id";

            $stmt = $conn->prepare($query);

            // Bind semua parameter
            $params = $data;
            $params['id'] = $tentang_desa['id'];

            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
        } else {
            // INSERT data baru
            $query = "INSERT INTO tentang_desa (
                sejarah, visi, misi, geografis, struktur_pemerintahan,
                kepala_desa, sekretaris_desa, kasi_pemerintahan, kasi_kesejahteraan, kasi_pelayanan,
                kaur_keuangan, kaur_tata_usaha, kaur_perencanaan,
                linimasa_1920, linimasa_1945, linimasa_1970, linimasa_1995, linimasa_2010, linimasa_2020,
                alamat, telepon, email, motto, motto_deskripsi,
                dukuh_plumbon, dukuh_winduaji, dukuh_simbang, dukuh_sidomas,
                pertanian, perkebunan, peternakan, pariwisata, kerajinan
            ) VALUES (
                :sejarah, :visi, :misi, :geografis, :struktur_pemerintahan,
                :kepala_desa, :sekretaris_desa, :kasi_pemerintahan, :kasi_kesejahteraan, :kasi_pelayanan,
                :kaur_keuangan, :kaur_tata_usaha, :kaur_perencanaan,
                :linimasa_1920, :linimasa_1945, :linimasa_1970, :linimasa_1995, :linimasa_2010, :linimasa_2020,
                :alamat, :telepon, :email, :motto, :motto_deskripsi,
                :dukuh_plumbon, :dukuh_winduaji, :dukuh_simbang, :dukuh_sidomas,
                :pertanian, :perkebunan, :peternakan, :pariwisata, :kerajinan
            )";

            $stmt = $conn->prepare($query);

            // Bind semua parameter
            foreach ($data as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
        }

        if ($stmt->execute()) {
            $success = "Data tentang desa berhasil disimpan!";

            // Refresh data
            $query = "SELECT * FROM tentang_desa LIMIT 1";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $tentang_desa = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error = "Gagal menyimpan data tentang desa.";
        }
    } catch (Exception $e) {
        $error = "Terjadi kesalahan: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Desa - Admin Desa Winduaji</title>
    <link rel="shortcut icon" href="../assets/images/logo.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
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
            font-family: 'Poppins', sans-serif;
            background: #f8fafc;
            color: var(--text-primary);
        }

        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, var(--primary-light) 0%, var(--primary) 100%);
            transition: transform 0.3s ease;
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1);
        }

        .main-content {
            margin-left: 280px;
            transition: margin-left 0.3s ease;
        }

        .card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .sidebar a {
            transition: all 0.2s ease;
            border-left: 4px solid transparent;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: rgba(255, 255, 255, 0.1);
            border-left-color: white;
            padding-left: 1.5rem;
        }

        .stats-card {
            position: relative;
            overflow: hidden;
        }

        .stats-card::after {
            content: '';
            position: absolute;
            top: -20px;
            right: -20px;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.15);
        }

        .quick-action {
            transition: all 0.2s ease;
        }

        .quick-action:hover {
            background: #fef2f2;
            transform: translateX(5px);
        }

        .news-card img {
            transition: transform 0.3s ease;
        }

        .news-card:hover img {
            transform: scale(1.08);
        }

        .header-icon {
            transition: transform 0.2s ease;
        }

        .header-icon:hover {
            transform: scale(1.1);
        }

        .gradient-bg {
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
        }

        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-280px);
            }

            .main-content {
                margin-left: 0;
            }

            .sidebar.active {
                transform: translateX(0);
            }
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .tab-button {
            transition: all 0.3s ease;
        }

        .tab-button.active {
            background-color: #fef2f2;
            color: #dc2626;
            border-left: 4px solid #dc2626;
        }

        .fade-in {
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .structure-item {
            transition: all 0.2s ease;
        }

        .structure-item:hover {
            background-color: #fef2f2;
        }
    </style>
</head>

<body class="flex">
    <!-- Sidebar -->
    <div class="sidebar fixed h-full text-white z-50">
        <div class="p-6 border-b border-red-900/30">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 rounded-full bg-white/10 flex items-center justify-center mr-3">
                    <i class="fas fa-landmark text-xl"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-white">Desa Winduaji</h1>
                    <p class="text-xs text-red-200">Administration Panel</p>
                </div>
            </div>
            <p class="text-sm text-red-200 mt-2"><?php echo $_SESSION['admin_name'] ?? 'Administrator'; ?></p>
        </div>

        <nav class="mt-6 space-y-1 p-2">
            <a href="dashboard.php" class="flex items-center px-4 py-3 text-red-200">
                <i class="fas fa-tachometer-alt w-5 mr-3"></i>
                Dashboard
            </a>
            <a href="tentang-desa.php" class="flex items-center px-4 py-3 text-white active">
                <i class="fas fa-info-circle w-5 mr-3"></i>
                Tentang Desa
            </a>
            <a href="kependudukan.php" class="flex items-center px-4 py-3 text-red-200">
                <i class="fas fa-users w-5 mr-3"></i>
                Kependudukan
            </a>
            <a href="berita.php" class="flex items-center px-4 py-3 text-red-200">
                <i class="fas fa-newspaper w-5 mr-3"></i>
                Berita
            </a>
            <a href="umkm.php" class="flex items-center px-4 py-3 text-red-200">
                <i class="fas fa-store w-5 mr-3"></i>
                UMKM
            </a>
            <a href="galeri.php" class="flex items-center px-4 py-3 text-red-200">
                <i class="fas fa-images w-5 mr-3"></i>
                Galeri
            </a>
            <a href="../logout.php" class="flex items-center px-4 py-3 text-red-200 mt-10">
                <i class="fas fa-sign-out-alt w-5 mr-3"></i>
                Keluar
            </a>
        </nav>

        <div class="absolute bottom-0 w-full p-4 text-center text-red-200 text-xs">
            <p>© 2023 Desa Winduaji</p>
            <p>v1.2.0</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm py-4 px-6 flex justify-between items-center">
            <button id="sidebarToggle" class="md:hidden text-gray-600">
                <i class="fas fa-bars text-xl"></i>
            </button>
            <div class="flex items-center space-x-4">
                <div class="relative">
                    <button class="flex items-center focus:outline-none">
                        <i class="fas fa-bell text-gray-600"></i>
                        <span class="absolute -top-1 -right-1 bg-red-600 text-white rounded-full text-xs w-4 h-4 flex items-center justify-center">3</span>
                    </button>
                </div>
                <div class="relative">
                    <button class="flex items-center focus:outline-none">
                        <div class="w-8 h-8 rounded-full bg-red-600 flex items-center justify-center text-white">
                            <?php echo substr($_SESSION['admin_name'] ?? 'A', 0, 1); ?>
                        </div>
                    </button>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="flex-1 p-6 bg-gray-50">
            <div class="bg-white shadow rounded-lg p-6 w-full">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold">Tentang Desa Winduaji</h1>
                    <a href="../pages/sejarah.php" target="_blank"
                        class="text-sm text-red-600 hover:text-red-800 inline-flex items-center">
                        <i class="fas fa-external-link-alt mr-1"></i>
                        Lihat Halaman Tentang Desa
                    </a>
                </div>

                <?php if (!empty($success)): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="bg-white rounded-lg shadow">
                    <div class="flex flex-col md:flex-row">
                        <!-- Tab Navigation -->
                        <div class="w-full md:w-64 bg-gray-50 rounded-l-lg p-4">
                            <h2 class="font-semibold text-lg mb-4 text-gray-800">Bagian Konten</h2>
                            <div class="space-y-2">
                                <button type="button" class="tab-button w-full text-left px-4 py-2 rounded-md" data-tab="sejarah">
                                    <i class="fas fa-history mr-2"></i> Sejarah
                                </button>
                                <button type="button" class="tab-button w-full text-left px-4 py-2 rounded-md" data-tab="visi-misi">
                                    <i class="fas fa-bullseye mr-2"></i> Visi & Misi
                                </button>
                                <button type="button" class="tab-button w-full text-left px-4 py-2 rounded-md" data-tab="geografis">
                                    <i class="fas fa-map mr-2"></i> Geografis
                                </button>
                                <button type="button" class="tab-button w-full text-left px-4 py-2 rounded-md" data-tab="pemerintahan">
                                    <i class="fas fa-landmark mr-2"></i> Pemerintahan
                                </button>
                                <button type="button" class="tab-button w-full text-left px-4 py-2 rounded-md" data-tab="linimasa">
                                    <i class="fas fa-stream mr-2"></i> Linimasa
                                </button>
                                <button type="button" class="tab-button w-full text-left px-4 py-2 rounded-md" data-tab="kontak">
                                    <i class="fas fa-address-card mr-2"></i> Kontak
                                </button>
                                <button type="button" class="tab-button w-full text-left px-4 py-2 rounded-md" data-tab="motto">
                                    <i class="fas fa-quote-left mr-2"></i> Motto
                                </button>
                                <button type="button" class="tab-button w-full text-left px-4 py-2 rounded-md" data-tab="wilayah">
                                    <i class="fas fa-map-marked-alt mr-2"></i> Wilayah
                                </button>
                                <button type="button" class="tab-button w-full text-left px-4 py-2 rounded-md" data-tab="potensi">
                                    <i class="fas fa-seedling mr-2"></i> Potensi
                                </button>
                            </div>
                        </div>

                        <!-- Tab Content -->
                        <div class="flex-1 p-6">
                            <!-- Tab Sejarah -->
                            <div id="sejarah" class="tab-content active">
                                <h2 class="text-xl font-semibold mb-4 text-red-700">Sejarah Desa</h2>
                                <div class="mb-4">
                                    <label for="sejarah" class="block text-gray-700 text-sm font-medium mb-2">Deskripsi Sejarah</label>
                                    <textarea
                                        id="sejarah"
                                        name="sejarah"
                                        rows="8"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent"><?php echo htmlspecialchars($tentang_desa['sejarah'] ?? ''); ?></textarea>
                                </div>
                            </div>

                            <!-- Tab Visi & Misi -->
                            <div id="visi-misi" class="tab-content">
                                <h2 class="text-xl font-semibold mb-4 text-red-700">Visi & Misi Desa</h2>
                                <div class="mb-4">
                                    <label for="visi" class="block text-gray-700 text-sm font-medium mb-2">Visi Desa</label>
                                    <textarea
                                        id="visi"
                                        name="visi"
                                        rows="3"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent"><?php echo htmlspecialchars($tentang_desa['visi'] ?? ''); ?></textarea>
                                </div>
                                <div class="mb-4">
                                    <label for="misi" class="block text-gray-700 text-sm font-medium mb-2">Misi Desa</label>
                                    <textarea
                                        id="misi"
                                        name="misi"
                                        rows="6"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent"><?php echo htmlspecialchars($tentang_desa['misi'] ?? ''); ?></textarea>
                                    <p class="text-xs text-gray-500 mt-1">Gunakan format list dengan angka (1., 2., 3., dst)</p>
                                </div>
                            </div>

                            <!-- Tab Geografis -->
                            <div id="geografis" class="tab-content">
                                <h2 class="text-xl font-semibold mb-4 text-red-700">Geografis Desa</h2>
                                <div class="mb-4">
                                    <label for="geografis" class="block text-gray-700 text-sm font-medium mb-2">Deskripsi Geografis</label>
                                    <textarea
                                        id="geografis"
                                        name="geografis"
                                        rows="4"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent"><?php echo htmlspecialchars($tentang_desa['geografis'] ?? ''); ?></textarea>
                                </div>
                            </div>

                            <!--Tab Pemerintahan -->
                            <div id="pemerintahan" class="tab-content">
                                <h2 class="text-xl font-semibold mb-4 text-red-700">Struktur Pemerintahan</h2>
                                <div class="mb-4">
                                    <label for="struktur_pemerintahan" class="block text-gray-700 text-sm font-medium mb-2">Deskripsi Pemerintahan</label>
                                    <textarea
                                        id="struktur_pemerintahan"
                                        name="struktur_pemerintahan"
                                        rows="3"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent"><?php echo htmlspecialchars($tentang_desa['struktur_pemerintahan'] ?? ''); ?></textarea>
                                </div>

                                <h3 class="text-lg font-medium text-gray-700 mb-4">Daftar Jabatan</h3>

                                <!-- Kepala Desa -->
                                <div class="structure-item grid grid-cols-1 md:grid-cols-12 gap-4 mb-4 p-3 border border-gray-200 rounded-md">
                                    <div class="md:col-span-5">
                                        <label class="block text-gray-700 text-sm font-medium mb-2">Jabatan</label>
                                        <input
                                            type="text"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100"
                                            value="Kepala Desa" readonly>
                                    </div>
                                    <div class="md:col-span-5">
                                        <label class="block text-gray-700 text-sm font-medium mb-2">Nama Pejabat</label>
                                        <input
                                            type="text"
                                            name="kepala_desa"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent"
                                            value="<?php echo htmlspecialchars($tentang_desa['kepala_desa'] ?? ''); ?>"
                                            placeholder="Nama Kepala Desa">
                                    </div>
                                    <div class="md:col-span-2 flex items-end">
                                        <button type="button" class="remove-btn w-full bg-gray-300 text-gray-800 py-2 px-4 rounded-md opacity-50 cursor-not-allowed" disabled>
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Sekretaris Desa -->
                                <div class="structure-item grid grid-cols-1 md:grid-cols-12 gap-4 mb-4 p-3 border border-gray-200 rounded-md">
                                    <div class="md:col-span-5">
                                        <label class="block text-gray-700 text-sm font-medium mb-2">Jabatan</label>
                                        <input
                                            type="text"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100"
                                            value="Sekretaris Desa" readonly>
                                    </div>
                                    <div class="md:col-span-5">
                                        <label class="block text-gray-700 text-sm font-medium mb-2">Nama Pejabat</label>
                                        <input
                                            type="text"
                                            name="sekretaris_desa"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent"
                                            value="<?php echo htmlspecialchars($tentang_desa['sekretaris_desa'] ?? ''); ?>"
                                            placeholder="Nama Sekretaris Desa">
                                    </div>
                                    <div class="md:col-span-2 flex items-end">
                                        <button type="button" class="remove-btn w-full bg-gray-300 text-gray-800 py-2 px-4 rounded-md opacity-50 cursor-not-allowed" disabled>
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Kasi Pemerintahan -->
                                <div class="structure-item grid grid-cols-1 md:grid-cols-12 gap-4 mb-4 p-3 border border-gray-200 rounded-md">
                                    <div class="md:col-span-5">
                                        <label class="block text-gray-700 text-sm font-medium mb-2">Jabatan</label>
                                        <input
                                            type="text"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100"
                                            value="Kasi Pemerintahan" readonly>
                                    </div>
                                    <div class="md:col-span-5">
                                        <label class="block text-gray-700 text-sm font-medium mb-2">Nama Pejabat</label>
                                        <input
                                            type="text"
                                            name="kasi_pemerintahan"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent"
                                            value="<?php echo htmlspecialchars($tentang_desa['kasi_pemerintahan'] ?? ''); ?>"
                                            placeholder="Nama Kasi Pemerintahan">
                                    </div>
                                    <div class="md:col-span-2 flex items-end">
                                        <button type="button" class="remove-btn w-full bg-gray-300 text-gray-800 py-2 px-4 rounded-md opacity-50 cursor-not-allowed" disabled>
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Kasi Kesejahteraan -->
                                <div class="structure-item grid grid-cols-1 md:grid-cols-12 gap-4 mb-4 p-3 border border-gray-200 rounded-md">
                                    <div class="md:col-span-5">
                                        <label class="block text-gray-700 text-sm font-medium mb-2">Jabatan</label>
                                        <input
                                            type="text"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100"
                                            value="Kasi Kesejahteraan" readonly>
                                    </div>
                                    <div class="md:col-span-5">
                                        <label class="block text-gray-700 text-sm font-medium mb-2">Nama Pejabat</label>
                                        <input
                                            type="text"
                                            name="kasi_kesejahteraan"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent"
                                            value="<?php echo htmlspecialchars($tentang_desa['kasi_kesejahteraan'] ?? ''); ?>"
                                            placeholder="Nama Kasi Kesejahteraan">
                                    </div>
                                    <div class="md:col-span-2 flex items-end">
                                        <button type="button" class="remove-btn w-full bg-gray-300 text-gray-800 py-2 px-4 rounded-md opacity-50 cursor-not-allowed" disabled>
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Kasi Pelayanan -->
                                <div class="structure-item grid grid-cols-1 md:grid-cols-12 gap-4 mb-4 p-3 border border-gray-200 rounded-md">
                                    <div class="md:col-span-5">
                                        <label class="block text-gray-700 text-sm font-medium mb-2">Jabatan</label>
                                        <input
                                            type="text"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100"
                                            value="Kasi Pelayanan" readonly>
                                    </div>
                                    <div class="md:col-span-5">
                                        <label class="block text-gray-700 text-sm font-medium mb-2">Nama Pejabat</label>
                                        <input
                                            type="text"
                                            name="kasi_pelayanan"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent"
                                            value="<?php echo htmlspecialchars($tentang_desa['kasi_pelayanan'] ?? ''); ?>"
                                            placeholder="Nama Kasi Pelayanan">
                                    </div>
                                    <div class="md:col-span-2 flex items-end">
                                        <button type="button" class="remove-btn w-full bg-gray-300 text-gray-800 py-2 px-4 rounded-md opacity-50 cursor-not-allowed" disabled>
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Kaur Keuangan -->
                                <div class="structure-item grid grid-cols-1 md:grid-cols-12 gap-4 mb-4 p-3 border border-gray-200 rounded-md">
                                    <div class="md:col-span-5">
                                        <label class="block text-gray-700 text-sm font-medium mb-2">Jabatan</label>
                                        <input
                                            type="text"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100"
                                            value="Kaur Keuangan" readonly>
                                    </div>
                                    <div class="md:col-span-5">
                                        <label class="block text-gray-700 text-sm font-medium mb-2">Nama Pejabat</label>
                                        <input
                                            type="text"
                                            name="kaur_keuangan"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent"
                                            value="<?php echo htmlspecialchars($tentang_desa['kaur_keuangan'] ?? ''); ?>"
                                            placeholder="Nama Kaur Keuangan">
                                    </div>
                                    <div class="md:col-span-2 flex items-end">
                                        <button type="button" class="remove-btn w-full bg-gray-300 text-gray-800 py-2 px-4 rounded-md opacity-50 cursor-not-allowed" disabled>
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Kaur Tata Usaha -->
                                <div class="structure-item grid grid-cols-1 md:grid-cols-12 gap-4 mb-4 p-3 border border-gray-200 rounded-md">
                                    <div class="md:col-span-5">
                                        <label class="block text-gray-700 text-sm font-medium mb-2">Jabatan</label>
                                        <input
                                            type="text"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100"
                                            value="Kaur Tata Usaha" readonly>
                                    </div>
                                    <div class="md:col-span-5">
                                        <label class="block text-gray-700 text-sm font-medium mb-2">Nama Pejabat</label>
                                        <input
                                            type="text"
                                            name="kaur_tata_usaha"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent"
                                            value="<?php echo htmlspecialchars($tentang_desa['kaur_tata_usaha'] ?? ''); ?>"
                                            placeholder="Nama Kaur Tata Usaha">
                                    </div>
                                    <div class="md:col-span-2 flex items-end">
                                        <button type="button" class="remove-btn w-full bg-gray-300 text-gray-800 py-2 px-4 rounded-md opacity-50 cursor-not-allowed" disabled>
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Kaur Perencanaan -->
                                <div class="structure-item grid grid-cols-1 md:grid-cols-12 gap-4 mb-4 p-3 border border-gray-200 rounded-md">
                                    <div class="md:col-span-5">
                                        <label class="block text-gray-700 text-sm font-medium mb-2">Jabatan</label>
                                        <input
                                            type="text"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100"
                                            value="Kaur Perencanaan" readonly>
                                    </div>
                                    <div class="md:col-span-5">
                                        <label class="block text-gray-700 text-sm font-medium mb-2">Nama Pejabat</label>
                                        <input
                                            type="text"
                                            name="kaur_perencanaan"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent"
                                            value="<?php echo htmlspecialchars($tentang_desa['kaur_perencanaan'] ?? ''); ?>"
                                            placeholder="Nama Kaur Perencanaan">
                                    </div>
                                    <div class="md:col-span-2 flex items-end">
                                        <button type="button" class="remove-btn w-full bg-gray-300 text-gray-800 py-2 px-4 rounded-md opacity-50 cursor-not-allowed" disabled>
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Tombol untuk menambahkan jabatan baru -->
                                <div class="mt-6 mb-4">
                                    <button type="button" id="tambah-jabatan" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                        <i class="fas fa-plus-circle mr-2"></i>Tambah Jabatan Baru
                                    </button>
                                </div>

                                <!-- Container untuk jabatan tambahan -->
                                <div id="additional-positions"></div>
                            </div>

                            <!-- Tab Linimasa -->
                            <div id="linimasa" class="tab-content">
                                <h2 class="text-xl font-semibold mb-4 text-red-700">Linimasa Sejarah Desa</h2>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label for="linimasa_1920" class="block text-gray-700 text-sm font-medium mb-2">Tahun 1920</label>
                                        <textarea
                                            id="linimasa_1920"
                                            name="linimasa_1920"
                                            rows="3"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent"><?php echo htmlspecialchars($tentang_desa['linimasa_1920'] ?? ''); ?></textarea>
                                    </div>
                                    <div>
                                        <label for="linimasa_1945" class="block text-gray-700 text-sm font-medium mb-2">Tahun 1945</label>
                                        <textarea
                                            id="linimasa_1945"
                                            name="linimasa_1945"
                                            rows="3"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent"><?php echo htmlspecialchars($tentang_desa['linimasa_1945'] ?? ''); ?></textarea>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label for="linimasa_1970" class="block text-gray-700 text-sm font-medium mb-2">Tahun 1970</label>
                                        <textarea
                                            id="linimasa_1970"
                                            name="linimasa_1970"
                                            rows="3"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent"><?php echo htmlspecialchars($tentang_desa['linimasa_1970'] ?? ''); ?></textarea>
                                    </div>
                                    <div>
                                        <label for="linimasa_1995" class="block text-gray-700 text-sm font-medium mb-2">Tahun 1995</label>
                                        <textarea
                                            id="linimasa_1995"
                                            name="linimasa_1995"
                                            rows="3"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent"><?php echo htmlspecialchars($tentang_desa['linimasa_1995'] ?? ''); ?></textarea>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label for="linimasa_2010" class="block text-gray-700 text-sm font-medium mb-2">Tahun 2010</label>
                                        <textarea
                                            id="linimasa_2010"
                                            name="linimasa_2010"
                                            rows="3"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent"><?php echo htmlspecialchars($tentang_desa['linimasa_2010'] ?? ''); ?></textarea>
                                    </div>
                                    <div>
                                        <label for="linimasa_2020" class="block text-gray-700 text-sm font-medium mb-2">Tahun 2020</label>
                                        <textarea
                                            id="linimasa_2020"
                                            name="linimasa_2020"
                                            rows="3"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent"><?php echo htmlspecialchars($tentang_desa['linimasa_2020'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Tab Kontak -->
                            <div id="kontak" class="tab-content">
                                <h2 class="text-xl font-semibold mb-4 text-red-700">Informasi Kontak</h2>

                                <div class="mb-4">
                                    <label for="alamat" class="block text-gray-700 text-sm font-medium mb-2">Alamat</label>
                                    <textarea
                                        id="alamat"
                                        name="alamat"
                                        rows="2"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent"><?php echo htmlspecialchars($tentang_desa['alamat'] ?? ''); ?></textarea>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label for="telepon" class="block text-gray-700 text-sm font-medium mb-2">Telepon</label>
                                        <input
                                            type="text"
                                            id="telepon"
                                            name="telepon"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent"
                                            value="<?php echo htmlspecialchars($tentang_desa['telepon'] ?? ''); ?>">
                                    </div>
                                    <div>
                                        <label for="email" class="block text-gray-700 text-sm font-medium mb-2">Email</label>
                                        <input
                                            type="email"
                                            id="email"
                                            name="email"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent"
                                            value="<?php echo htmlspecialchars($tentang_desa['email'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- Tab Motto -->
                            <div id="motto" class="tab-content">
                                <h2 class="text-xl font-semibold mb-4 text-red-700">Motto Desa</h2>

                                <div class="mb-4">
                                    <label for="motto" class="block text-gray-700 text-sm font-medium mb-2">Motto</label>
                                    <input
                                        type="text"
                                        id="motto"
                                        name="motto"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent"
                                        value="<?php echo htmlspecialchars($tentang_desa['motto'] ?? ''); ?>">
                                </div>

                                <div class="mb-4">
                                    <label for="motto_deskripsi" class="block text-gray-700 text-sm font-medium mb-2">Deskripsi Motto</label>
                                    <textarea
                                        id="motto_deskripsi"
                                        name="motto_deskripsi"
                                        rows="3"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent"><?php echo htmlspecialchars($tentang_desa['motto_deskripsi'] ?? ''); ?></textarea>
                                </div>
                            </div>

                            <!-- Tab Wilayah -->
                            <div id="wilayah" class="tab-content">
                                <h2 class="text-xl font-semibold mb-4 text-red-700">Wilayah Desa</h2>
                                <p class="text-gray-600 mb-4">Deskripsi untuk masing-masing dukuh di Desa Winduaji</p>

                                <div class="mb-4">
                                    <label for="dukuh_plumbon" class="block text-gray-700 text-sm font-medium mb-2">Dukuh Plumbon</label>
                                    <textarea
                                        id="dukuh_plumbon"
                                        name="dukuh_plumbon"
                                        rows="3"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent"><?php echo htmlspecialchars($tentang_desa['dukuh_plumbon'] ?? ''); ?></textarea>
                                </div>

                                <div class="mb-4">
                                    <label for="dukuh_winduaji" class="block text-gray-700 text-sm font-medium mb-2">Dukuh Winduaji</label>
                                    <textarea
                                        id="dukuh_winduaji"
                                        name="dukuh_winduaji"
                                        rows="3"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent"><?php echo htmlspecialchars($tentang_desa['dukuh_winduaji'] ?? ''); ?></textarea>
                                </div>

                                <div class="mb-4">
                                    <label for="dukuh_simbang" class="block text-gray-700 text-sm font-medium mb-2">Dukuh Simbang</label>
                                    <textarea
                                        id="dukuh_simbang"
                                        name="dukuh_simbang"
                                        rows="3"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent"><?php echo htmlspecialchars($tentang_desa['dukuh_simbang'] ?? ''); ?></textarea>
                                </div>

                                <div class="mb-4">
                                    <label for="dukuh_sidomas" class="block text-gray-700 text-sm font-medium mb-2">Dukuh Sidomas</label>
                                    <textarea
                                        id="dukuh_sidomas"
                                        name="dukuh_sidomas"
                                        rows="3"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent"><?php echo htmlspecialchars($tentang_desa['dukuh_sidomas'] ?? ''); ?></textarea>
                                </div>
                            </div>

                            <!-- Tab Potensi -->
                            <div id="potensi" class="tab-content">
                                <h2 class="text-xl font-semibold mb-4 text-red-700">Potensi Desa</h2>

                                <div class="mb-4">
                                    <label for="pertanian" class="block text-gray-700 text-sm font-medium mb-2">Pertanian</label>
                                    <textarea
                                        id="pertanian"
                                        name="pertanian"
                                        rows="2"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent"><?php echo htmlspecialchars($tentang_desa['pertanian'] ?? ''); ?></textarea>
                                </div>

                                <div class="mb-4">
                                    <label for="perkebunan" class="block text-gray-700 text-sm font-medium mb-2">Perkebunan</label>
                                    <textarea
                                        id="perkebunan"
                                        name="perkebunan"
                                        rows="2"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent"><?php echo htmlspecialchars($tentang_desa['perkebunan'] ?? ''); ?></textarea>
                                </div>

                                <div class="mb-4">
                                    <label for="peternakan" class="block text-gray-700 text-sm font-medium mb-2">Peternakan</label>
                                    <textarea
                                        id="peternakan"
                                        name="peternakan"
                                        rows="2"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent"><?php echo htmlspecialchars($tentang_desa['peternakan'] ?? ''); ?></textarea>
                                </div>

                                <div class="mb-4">
                                    <label for="pariwisata" class="block text-gray-700 text-sm font-medium mb-2">Pariwisata</label>
                                    <textarea
                                        id="pariwisata"
                                        name="pariwisata"
                                        rows="2"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent"><?php echo htmlspecialchars($tentang_desa['pariwisata'] ?? ''); ?></textarea>
                                </div>

                                <div class="mb-4">
                                    <label for="kerajinan" class="block text-gray-700 text-sm font-medium mb-2">Kerajinan</label>
                                    <textarea
                                        id="kerajinan"
                                        name="kerajinan"
                                        rows="2"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent"><?php echo htmlspecialchars($tentang_desa['kerajinan'] ?? ''); ?></textarea>
                                </div>
                            </div>

                            <!-- Tombol Simpan -->
                            <div class="flex justify-end mt-8">
                                <button
                                    type="submit"
                                    class="bg-red-700 hover:bg-red-800 text-white font-medium py-2 px-6 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-600 transition-colors duration-200">
                                    <i class="fas fa-save mr-2"></i>Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
        </main>
    </div>

    <script>
        // Toggle sidebar on mobile
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        // Tab functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');

            // Activate first tab by default
            if (tabButtons.length > 0) {
                tabButtons[0].classList.add('active');
            }

            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const tabId = this.getAttribute('data-tab');

                    // Remove active class from all buttons and contents
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    tabContents.forEach(content => content.classList.remove('active'));

                    // Add active class to current button and content
                    this.classList.add('active');
                    document.getElementById(tabId).classList.add('active');
                });
            });

            // Fungsi untuk menambahkan jabatan baru
            const tambahJabatanBtn = document.getElementById('tambah-jabatan');
            const additionalPositions = document.getElementById('additional-positions');
            let counter = 0;

            tambahJabatanBtn.addEventListener('click', function() {
                counter++;
                const newPosition = document.createElement('div');
                newPosition.className = 'structure-item grid grid-cols-1 md:grid-cols-12 gap-4 mb-4 p-3 border border-gray-200 rounded-md fade-in';
                newPosition.innerHTML = `
                <div class="md:col-span-5">
                    <label class="block text-gray-700 text-sm font-medium mb-2">Jabatan</label>
                    <input 
                        type="text" 
                        name="jabatan_tambah[]" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent"
                        placeholder="Masukkan nama jabatan">
                </div>
                <div class="md:col-span-5">
                    <label class="block text-gray-700 text-sm font-medium mb-2">Nama Pejabat</label>
                    <input 
                        type="text" 
                        name="nama_pejabat_tambah[]" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent"
                        placeholder="Nama pejabat">
                </div>
                <div class="md:col-span-2 flex items-end">
                    <button type="button" class="remove-btn w-full bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-md">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;

                additionalPositions.appendChild(newPosition);

                // Tambahkan event listener untuk tombol hapus
                const removeBtn = newPosition.querySelector('.remove-btn');
                removeBtn.addEventListener('click', function() {
                    newPosition.classList.add('fade-out');
                    setTimeout(() => {
                        additionalPositions.removeChild(newPosition);
                    }, 300);
                });
            });

            // Tampilkan tombol hapus pada item yang sudah ada saat dihover
            const structureItems = document.querySelectorAll('.structure-item');
            structureItems.forEach(item => {
                const removeBtn = item.querySelector('.remove-btn');

                item.addEventListener('mouseenter', function() {
                    if (removeBtn && !removeBtn.disabled) removeBtn.classList.remove('hidden');
                });

                item.addEventListener('mouseleave', function() {
                    if (removeBtn && !removeBtn.disabled) removeBtn.classList.add('hidden');
                });
            });
        });
    </script>