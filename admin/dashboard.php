<?php
session_start();

require_once '../config/database.php';
require_once '../config/functions.php';

// Koneksi database
$db = new database();
$conn = $db->getConnection();

// Ambil data statistik untuk dashboard
$stats = [];
$queries = [
    'berita' => "SELECT COUNT(*) as count FROM berita WHERE status = 'published'",
    'umkm' => "SELECT COUNT(*) as count FROM umkm WHERE status = 'published'",
    'galeri' => "SELECT COUNT(*) as count FROM galeri WHERE status = 'published'",
];

foreach ($queries as $key => $query) {
    try {
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $stats[$key] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    } catch (Exception $e) {
        $stats[$key] = 0;
    }
}

// Ambil berita terbaru
$recent_news = [];
try {
    $query = "SELECT b.*, bk.nama_kategori 
              FROM berita b 
              JOIN berita_kategori bk ON b.kategori_id = bk.id 
              ORDER BY b.tanggal_publish DESC LIMIT 5";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $recent_news = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Tangani error
}

// Cek jika ada parameter success untuk menampilkan SweetAlert
$showSuccessAlert = isset($_GET['login']) && $_GET['login'] === 'success';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <title>Dashboard Admin - Desa Winduaji</title>
    <link rel="shortcut icon" href="../assets/images/logo.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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
        
        .sidebar a:hover, .sidebar a.active {
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
        
        .realtime-clock {
            font-family: 'Courier New', monospace;
            font-weight: 600;
            letter-spacing: 1px;
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
            <a href="dashboard.php" class="flex items-center px-4 py-3 text-white active">
                <i class="fas fa-tachometer-alt w-5 mr-3"></i>
                Dashboard
            </a>
            <a href="profil-desa.php" class="flex items-center px-4 py-3 text-red-200">
                <i class="fas fa-landmark w-5 mr-3"></i>
                Profil Desa
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
    <div class="main-content flex-1 min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm py-4 px-6 flex justify-between items-center sticky top-0 z-40">
            <div class="flex items-center">
                <button id="sidebarToggle" class="md:hidden text-gray-600 header-icon mr-4">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <h2 class="text-xl font-semibold text-gray-800 hidden md:block">Dashboard Overview</h2>
            </div>
            
            <div class="flex items-center space-x-4">
                <div class="relative">
                    <div class="flex items-center space-x-4">
                        <div class="relative header-icon">
                            <button class="flex items-center focus:outline-none">
                                <i class="fas fa-bell text-gray-600 text-lg"></i>
                                <span class="absolute -top-1 -right-1 bg-red-600 text-white rounded-full text-xs w-4 h-4 flex items-center justify-center">3</span>
                            </button>
                        </div>
                        
                        <div class="relative">
                            <button class="flex items-center focus:outline-none space-x-2">
                                <div class="w-9 h-9 rounded-full gradient-bg flex items-center justify-center text-white font-medium shadow-md">
                                    <?php echo substr($_SESSION['admin_name'] ?? 'A', 0, 1); ?>
                                </div>
                                <span class="text-gray-700 hidden md:inline-block"><?php echo $_SESSION['admin_name'] ?? 'Admin'; ?></span>
                                <i class="fas fa-chevron-down text-gray-500 text-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="p-6 md:p-8 bg-gray-50 min-h-screen">
            <!-- Welcome Banner -->
            <div class="gradient-bg rounded-2xl p-6 text-white shadow-lg mb-8">
                <div class="flex flex-col md:flex-row items-center justify-between">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold mb-2">Selamat Datang, <?php echo $_SESSION['admin_name'] ?? 'Administrator'; ?>!</h1>
                        <p class="text-red-100">Kelola konten website Desa Winduaji dengan mudah dan efisien.</p>
                    </div>
                    <div class="mt-4 md:mt-0">
                        <div class="bg-white/20 py-2 px-4 rounded-lg text-center">
                            <p class="text-sm" id="realtime-date"><?php echo date('d F Y'); ?></p>
                            <p class="text-xs font-mono" id="realtime-clock"><?php echo date('H:i:s'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-10">
                <div class="card stats-card p-6 relative overflow-hidden">
                    <div class="flex items-center">
                        <div class="p-3 rounded-xl bg-red-100 text-red-600 mr-4">
                            <i class="fas fa-newspaper fa-lg"></i>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-800"><?php echo $stats['berita']; ?></p>
                            <p class="text-sm text-gray-600">Total Berita</p>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-red-500"></div>
                </div>
                
                <div class="card stats-card p-6 relative overflow-hidden">
                    <div class="flex items-center">
                        <div class="p-3 rounded-xl bg-blue-100 text-blue-600 mr-4">
                            <i class="fas fa-store fa-lg"></i>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-800"><?php echo $stats['umkm']; ?></p>
                            <p class="text-sm text-gray-600">UMKM Terdaftar</p>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-blue-500"></div>
                </div>
                
                <div class="card stats-card p-6 relative overflow-hidden">
                    <div class="flex items-center">
                        <div class="p-3 rounded-xl bg-green-100 text-green-600 mr-4">
                            <i class="fas fa-images fa-lg"></i>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-800"><?php echo $stats['galeri']; ?></p>
                            <p class="text-sm text-gray-600">Item Galeri</p>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-green-500"></div>
                </div>
            </div>
            
            <!-- Recent News and Quick Actions -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Recent News -->
                <div class="lg:col-span-2 card p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-semibold text-gray-800">Berita Terbaru</h2>
                        <a href="berita.php" class="text-sm text-red-600 hover:text-red-800 font-medium flex items-center">
                            Lihat Semua <i class="fas fa-arrow-right ml-1 text-xs"></i>
                        </a>
                    </div>
                    
                    <div class="space-y-4">
                        <?php if (!empty($recent_news)): ?>
                            <?php foreach ($recent_news as $news): ?>
                                <div class="news-card flex items-start pb-4 border-b border-gray-100 last:border-0 last:pb-0">
                                    <?php if (!empty($news['gambar_path'])): ?>
                                        <div class="w-16 h-16 overflow-hidden rounded-xl mr-4 shadow-md">
                                            <img src="../<?php echo $news['gambar_path']; ?>" alt="<?php echo $news['judul']; ?>" class="w-full h-full object-cover">
                                        </div>
                                    <?php else: ?>
                                        <div class="w-16 h-16 bg-gray-100 rounded-xl mr-4 flex items-center justify-center shadow-md">
                                            <i class="fas fa-newspaper text-gray-400 text-lg"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-base text-gray-800 mb-1"><?php echo $news['judul']; ?></h3>
                                        <p class="text-sm text-gray-500 mb-2"><?php echo date('d M Y', strtotime($news['tanggal_publish'])); ?> • <?php echo $news['nama_kategori']; ?></p>
                                        <div class="flex space-x-3">
                                            <a href="edit-berita.php?id=<?php echo $news['id']; ?>" class="text-xs py-1 px-3 bg-blue-100 text-blue-700 rounded-full hover:bg-blue-200 transition">Edit</a>
                                            <a href="hapus-berita.php?id=<?php echo $news['id']; ?>" class="text-xs py-1 px-3 bg-red-100 text-red-700 rounded-full hover:bg-red-200 transition">Hapus</a>
                                           
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-8">
                                <i class="fas fa-newspaper text-4xl text-gray-300 mb-3"></i>
                                <p class="text-gray-500">Belum ada berita.</p>
                                <a href="tambah-berita.php" class="inline-block mt-3 text-red-600 hover:text-red-800 text-sm font-medium">Tambah Berita Pertama</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Quick Actions and Activity -->
                <div class="space-y-6">
                    <!-- Quick Actions -->
                    <div class="card p-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-6">Aksi Cepat</h2>
                        <div class="space-y-3">
                            <a href="tambah-berita.php" class="quick-action flex items-center p-3 rounded-xl border border-gray-100 hover:border-red-200">
                                <div class="w-10 h-10 rounded-lg bg-red-100 text-red-600 flex items-center justify-center mr-3">
                                    <i class="fas fa-plus"></i>
                                </div>
                                <span class="font-medium text-gray-800">Tambah Berita Baru</span>
                            </a>
                            <a href="tambah-umkm.php" class="quick-action flex items-center p-3 rounded-xl border border-gray-100 hover:border-blue-200">
                                <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center mr-3">
                                    <i class="fas fa-plus"></i>
                                </div>
                                <span class="font-medium text-gray-800">Tambah UMKM Baru</span>
                            </a>
                            <a href="tambah-galeri.php" class="quick-action flex items-center p-3 rounded-xl border border-gray-100 hover:border-green-200">
                                <div class="w-10 h-10 rounded-lg bg-green-100 text-green-600 flex items-center justify-center mr-3">
                                    <i class="fas fa-plus"></i>
                                </div>
                                <span class="font-medium text-gray-800">Tambah ke Galeri</span>
                            </a>
                            <a href="pengaturan.php" class="quick-action flex items-center p-3 rounded-xl border border-gray-100 hover:border-purple-200">
                                <div class="w-10 h-10 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center mr-3">
                                    <i class="fas fa-cog"></i>
                                </div>
                                <span class="font-medium text-gray-800">Pengaturan Website</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        
        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 py-4 px-8">
            <div class="flex flex-col md:flex-row items-center justify-between">
                <p class="text-sm text-gray-600">© 2023 Desa Winduaji. All rights reserved.</p>
                <p class="text-sm text-gray-600 mt-2 md:mt-0">Version 1.2.0 | Last updated: <?php echo date('d M Y'); ?></p>
            </div>
        </footer>
    </div>

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
        // Toggle sidebar on mobile
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
        
        // Add active class to current page in navigation
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split('/').pop();
            const navLinks = document.querySelectorAll('.sidebar a');
            
            navLinks.forEach(link => {
                const linkPage = link.getAttribute('href');
                if (linkPage === currentPage) {
                    link.classList.add('active');
                }
            });
            
            // Tampilkan notifikasi login berhasil jika ada
            <?php if ($showSuccessAlert): ?>
            Swal.fire({
                title: 'Login Berhasil!',
                text: 'Selamat datang di Dashboard Admin Desa Winduaji',
                icon: 'success',
                confirmButtonText: 'Lanjutkan',
                confirmButtonColor: '#7f1d1d',
                timer: 3000
            });
            <?php endif; ?>
        });
        
        // Fungsi untuk update waktu realtime
        function updateRealtimeClock() {
            const now = new Date();
            
            // Format tanggal
            const options = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            };
            const dateString = now.toLocaleDateString('id-ID', options);
            
            // Format waktu
            const timeString = now.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            
            // Update elemen
            document.getElementById('realtime-date').textContent = dateString;
            document.getElementById('realtime-clock').textContent = timeString;
        }
        
        // Update waktu setiap detik
        setInterval(updateRealtimeClock, 1000);
        
        // Jalankan sekali saat halaman dimuat
        updateRealtimeClock();
    </script>
</body>
</html>