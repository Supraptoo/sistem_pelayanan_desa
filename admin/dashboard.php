<?php
session_start();

// Pastikan path ke config/database.php benar
require_once __DIR__ . '/../config/database.php';

// Data desa
$desa_nama = "Desa Winduaji";
$desa_lokasi = "Kecamatan Sejahtera, Kabupaten Makmur, Provinsi Bahagia";
$desa_motto = "Bersama Membangun Desa yang Mandiri dan Berbudaya";

// Ambil data statistik dari database
$penduduk = 12500;
$laki_laki = 6200;
$perempuan = 6300;
$kk = 3200;
$rw = 15;
$rt = 75;
$luas = 8.75;

// Jumlah data untuk statistik dashboard
$total_berita = 24;
$total_umkm = 48;
$total_galeri = 36;
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo $desa_nama; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4361ee;
            --primary-light: #eef2ff;
            --secondary-color: #3f37c9;
            --accent-color: #f72585;
            --success-color: #4cc9f0;
            --warning-color: #f8961e;
            --danger-color: #ef233c;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --gray-light: #e9ecef;
            --gray-medium: #adb5bd;
            --gray-dark: #495057;
            --sidebar-width: 280px;
            --sidebar-collapsed-width: 80px;
            --topbar-height: 60px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --border-radius: 0.5rem;
        }

        body {
            font-family: 'Open Sans', sans-serif;
            color: var(--dark-color);
            background-color: #f5f7fb;
            line-height: 1.6;
            overflow-x: hidden;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            color: var(--dark-color);
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: white;
            color: var(--gray-dark);
            box-shadow: var(--shadow-md);
            z-index: 1000;
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            border-right: 1px solid var(--gray-light);
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--gray-light);
            height: var(--topbar-height);
            display: flex;
            align-items: center;
            flex-shrink: 0;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 700;
            font-size: 1.2rem;
            white-space: nowrap;
        }

        .sidebar-brand img {
            height: 32px;
            margin-right: 10px;
            transition: var(--transition);
        }

        .sidebar.collapsed .sidebar-brand img {
            margin-right: 0;
        }

        .sidebar.collapsed .sidebar-brand span {
            display: none;
        }

        .sidebar-menu-container {
            flex: 1;
            overflow-y: auto;
            padding: 1rem 0;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu li {
            position: relative;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: var(--gray-dark);
            text-decoration: none;
            transition: var(--transition);
            white-space: nowrap;
        }

        .sidebar-menu a:hover {
            background-color: var(--primary-light);
            color: var(--primary-color);
        }

        .sidebar-menu a.active {
            background-color: var(--primary-light);
            color: var(--primary-color);
            font-weight: 600;
        }

        .sidebar-menu a.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background-color: var(--primary-color);
        }

        .sidebar-menu i {
            width: 24px;
            text-align: center;
            margin-right: 12px;
            font-size: 1.1rem;
            transition: var(--transition);
        }

        .sidebar.collapsed .sidebar-menu i {
            margin-right: 0;
        }

        .menu-text {
            transition: var(--transition);
        }

        .sidebar.collapsed .menu-text {
            opacity: 0;
            width: 0;
            display: none;
        }

        .sidebar-menu .menu-arrow {
            transition: transform 0.3s;
            margin-left: auto;
        }

        .sidebar-menu .has-submenu.active .menu-arrow {
            transform: rotate(180deg);
        }

        .submenu {
            list-style: none;
            padding-left: 2.5rem;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            background-color: rgba(239, 246, 255, 0.5);
        }

        .sidebar.collapsed .submenu {
            display: none;
        }

        .sidebar-menu .submenu.show {
            max-height: 500px;
        }

        .sidebar-menu .submenu a {
            padding: 0.6rem 1.5rem 0.6rem 0;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: var(--transition);
            background-color: #f5f7fb;
        }

        .main-content.expanded {
            margin-left: var(--sidebar-collapsed-width);
        }

        /* Top Navbar */
        .top-navbar {
            position: sticky;
            top: 0;
            z-index: 999;
            height: var(--topbar-height);
            background: white;
            box-shadow: var(--shadow-sm);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 1.5rem;
            border-bottom: 1px solid var(--gray-light);
        }

        .toggle-btn {
            background: none;
            border: none;
            color: var(--gray-dark);
            font-size: 1.25rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        .toggle-btn:hover {
            background-color: var(--gray-light);
            color: var(--primary-color);
        }

        .user-menu {
            display: flex;
            align-items: center;
        }

        .user-menu .dropdown-toggle {
            display: flex;
            align-items: center;
            background: none;
            border: none;
            color: var(--gray-dark);
            padding: 0.25rem 0.5rem;
            border-radius: var(--border-radius);
            transition: var(--transition);
        }

        .user-menu .dropdown-toggle:hover {
            background-color: var(--gray-light);
        }

        .user-menu .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            margin-right: 8px;
            object-fit: cover;
            border: 2px solid var(--gray-light);
        }

        .user-menu .user-name {
            margin-right: 6px;
            font-weight: 500;
        }

        /* Dashboard Cards */
        .dashboard-card {
            border-radius: var(--border-radius);
            border: none;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            margin-bottom: 1.5rem;
            background: white;
            border-left: 4px solid var(--primary-color);
            overflow: hidden;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .card-icon {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .card-title {
            font-size: 0.9rem;
            color: var(--gray-medium);
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        .card-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 1rem;
        }

        .card-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .card-link:hover {
            color: var(--secondary-color);
        }

        .card-link i {
            margin-left: 5px;
            transition: transform 0.3s;
        }

        .card-link:hover i {
            transform: translateX(3px);
        }

        /* Card Colors */
        .card-news {
            border-left-color: var(--success-color);
        }
        .card-news .card-icon {
            color: var(--success-color);
        }
        
        .card-umkm {
            border-left-color: var(--warning-color);
        }
        .card-umkm .card-icon {
            color: var(--warning-color);
        }
        
        .card-gallery {
            border-left-color: var(--accent-color);
        }
        .card-gallery .card-icon {
            color: var(--accent-color);
        }
        
        .card-complaint {
            border-left-color: var(--danger-color);
        }
        .card-complaint .card-icon {
            color: var(--danger-color);
        }
        
        .card-resident {
            border-left-color: #7209b7;
        }
        .card-resident .card-icon {
            color: #7209b7;
        }

        /* Content Section */
        .content-section {
            padding: 2rem;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark-color);
            margin: 0;
        }

        /* Table Styles */
        .table-responsive {
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            background: white;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background: var(--primary-color);
            color: white;
            border-bottom: none;
            font-weight: 600;
            padding: 1rem;
        }

        .table tbody td {
            padding: 0.75rem 1rem;
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background-color: var(--primary-light);
        }

        .badge {
            padding: 0.5em 0.75em;
            font-weight: 600;
            border-radius: 4px;
            font-size: 0.75rem;
        }

        /* Status Badges */
        .badge-published {
            background-color: rgba(76, 201, 240, 0.1);
            color: var(--success-color);
        }
        
        .badge-draft {
            background-color: rgba(248, 150, 30, 0.1);
            color: var(--warning-color);
        }
        
        .badge-pending {
            background-color: rgba(67, 97, 238, 0.1);
            color: var(--primary-color);
        }

        /* Resident Stats */
        .resident-stats {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .resident-stat-item {
            flex: 1;
            min-width: 200px;
            background: white;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            border-left: 4px solid var(--primary-color);
            transition: var(--transition);
        }
        
        .resident-stat-item:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }
        
        .resident-stat-item h5 {
            color: var(--gray-medium);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .resident-stat-item p {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 0.25rem;
        }
        
        .resident-stat-item small {
            color: var(--gray-medium);
            font-size: 0.8rem;
        }

        /* Quick Action Buttons */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 1rem;
        }

        .quick-action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.25rem 0.5rem;
            border-radius: var(--border-radius);
            background: white;
            color: var(--dark-color);
            text-decoration: none;
            transition: var(--transition);
            box-shadow: var(--shadow-sm);
            text-align: center;
            border: none;
        }
        
        .quick-action-btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
            color: var(--primary-color);
        }
        
        .quick-action-btn i {
            font-size: 1.5rem;
            margin-bottom: 0.75rem;
            color: var(--primary-color);
        }
        
        .quick-action-btn span {
            font-size: 0.85rem;
            font-weight: 500;
        }

        /* Activity List */
        .activity-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .activity-item {
            padding: 1rem 0;
            border-bottom: 1px solid var(--gray-light);
            display: flex;
            transition: var(--transition);
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-item:hover {
            background-color: var(--primary-light);
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            flex-shrink: 0;
            font-size: 1rem;
        }

        .activity-content {
            flex-grow: 1;
        }

        .activity-time {
            font-size: 0.8rem;
            color: var(--gray-medium);
            margin-top: 0.25rem;
        }

        /* Responsive Adjustments */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .sidebar.collapsed {
                transform: translateX(-100%);
            }
            
            .sidebar.collapsed.show {
                transform: translateX(0);
                width: var(--sidebar-collapsed-width);
            }
            
            .main-content.expanded {
                margin-left: 0;
            }
        }

        @media (max-width: 768px) {
            .resident-stat-item {
                min-width: calc(50% - 0.5rem);
            }
            
            .quick-actions {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {
            .resident-stat-item {
                min-width: 100%;
            }
            
            .quick-actions {
                grid-template-columns: 1fr;
            }
            
            .content-section {
                padding: 1.5rem 1rem;
            }
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: var(--gray-light);
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--secondary-color);
        }

        /* Animation for sidebar toggle */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .sidebar-menu-container::-webkit-scrollbar {
            width: 4px;
        }
    </style>
</head>

<body>
     <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <a href="dashboard.php" class="sidebar-brand">
                <img src="../assets/images/logo.png" alt="Logo <?php echo $desa_nama; ?>">
                <span><?php echo $desa_nama; ?></span>
            </a>
        </div>
        <div class="sidebar-menu-container">
            <ul class="sidebar-menu">
                <li>
                    <a href="../admin/dashboard.php" class="active">
                        <i class="fas fa-tachometer-alt"></i>
                        <span class="menu-text">Dashboard</span>
                    </a>
                </li>
                
                <li>
                    <a href="javascript:void(0);" class="has-submenu">
                        <i class="fas fa-users"></i>
                        <span class="menu-text">Data Warga</span>
                        <i class="fas fa-chevron-down menu-arrow"></i>
                    </a>
                    <ul class="submenu">
                        <li><a href="../data_warga/penduduk.php">Data Penduduk</a></li>
                        <li><a href="../data_warga/keluarga.php">Data Keluarga</a></li>
                        <li><a href="../data_warga/rt-rw.php">Data RT/RW</a></li>
                    </ul>
                </li>
                <li>
                    <a href="javascript:void(0);" class="has-submenu">
                        <i class="fas fa-newspaper"></i>
                        <span class="menu-text">Berita</span>
                        <i class="fas fa-chevron-down menu-arrow"></i>
                    </a>
                    <ul class="submenu">
                        <li><a href="../berita/berita.php">Kelola Berita</a></li>
                        <li><a href="../berita/kategori_berita.php">Kategori</a></li>
                    </ul>
                </li>
                <li>
                    <a href="javascript:void(0);" class="has-submenu">
                        <i class="fas fa-store"></i>
                        <span class="menu-text">UMKM</span>
                        <i class="fas fa-chevron-down menu-arrow"></i>
                    </a>
                    <ul class="submenu">
                        <li><a href="../umkm/umkm.php">Daftar UMKM</a></li>
                        <li><a href="../umkm/kategori-umkm.php">Kategori</a></li>
                    </ul>
                </li>
                <li>
                    <a href="javascript:void(0);" class="has-submenu">
                        <i class="fas fa-camera"></i>
                        <span class="menu-text">Galeri</span>
                        <i class="fas fa-chevron-down menu-arrow"></i>
                    </a>
                    <ul class="submenu">
                        <li><a href="../admin/galeri/foto.php">Foto</a></li>
                        
                    </ul>
                </li>
             
                <li>
                    <a href="../../logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        <span class="menu-text">Keluar</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <nav class="top-navbar">
            <div class="d-flex align-items-center">
                <button class="toggle-btn me-3">
                    <i class="fas fa-bars"></i>
                </button>
                <span class="d-none d-md-inline">Dashboard Admin</span>
            </div>
            <div class="user-menu">
                <div class="dropdown">
                    <button class="dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="../assets/images/logo.png" alt="User Avatar" class="user-avatar">
                        <span class="user-name d-none d-md-inline">Admin</span>
                        <i class="fas fa-chevron-down d-none d-md-inline"></i>
                    </button>
                   
                </div>
            </div>
        </nav>

        <!-- Content Section -->
        <div class="content-section">
            <div class="section-header">
                <h1 class="section-title">Dashboard Admin</h1>
                <div>
                    <span class="text-muted"><?php echo date('d F Y, H:i'); ?></span>
                </div>
            </div>

           

            <!-- Resident Statistics -->
            <div class="resident-stats">
                <div class="resident-stat-item">
                    <h5>Total Penduduk</h5>
                    <p><?php echo number_format($penduduk, 0, ',', '.'); ?></p>
                    <small><?php echo number_format($laki_laki, 0, ',', '.'); ?> Laki-laki • <?php echo number_format($perempuan, 0, ',', '.'); ?> Perempuan</small>
                </div>
                <div class="resident-stat-item">
                    <h5>Kepala Keluarga</h5>
                    <p><?php echo number_format($kk, 0, ',', '.'); ?></p>
                    <small>Rata-rata <?php echo round($penduduk/$kk, 2); ?> orang per keluarga</small>
                </div>
                <div class="resident-stat-item">
                    <h5>Wilayah</h5>
                    <p><?php echo $rw; ?> RW • <?php echo $rt; ?> RT</p>
                    <small>Luas wilayah <?php echo $luas; ?> km²</small>
                </div>
                <div class="resident-stat-item">
                    <h5>Kepadatan Penduduk</h5>
                    <p><?php echo number_format($penduduk/$luas, 2); ?>/km²</p>
                    <small><?php echo number_format($kk/$luas, 2); ?> KK per km²</small>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row">
                <div class="col-md-6 col-lg-3">
                    <div class="dashboard-card card-news">
                        <div class="card-body text-center">
                            <div class="card-icon">
                                <i class="fas fa-newspaper"></i>
                            </div>
                            <h5 class="card-title">Total Berita</h5>
                            <h3 class="card-value"><?php echo $total_berita; ?></h3>
                            <a href="berita.php" class="card-link">
                                Lihat Semua <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="dashboard-card card-umkm">
                        <div class="card-body text-center">
                            <div class="card-icon">
                                <i class="fas fa-store"></i>
                            </div>
                            <h5 class="card-title">Total UMKM</h5>
                            <h3 class="card-value"><?php echo $total_umkm; ?></h3>
                            <a href="umkm.php" class="card-link">
                                Lihat Semua <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="dashboard-card card-gallery">
                        <div class="card-body text-center">
                            <div class="card-icon">
                                <i class="fas fa-camera"></i>
                            </div>
                            <h5 class="card-title">Total Galeri</h5>
                            <h3 class="card-value"><?php echo $total_galeri; ?></h3>
                            <a href="galeri.php" class="card-link">
                                Lihat Semua <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                            </div>

            <!-- Recent Activity and Quick Actions -->
            <div class="row mt-4">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Aktivitas Terkini</h5>
                            <a href="#" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                        </div>
                        <div class="card-body">
                            <ul class="activity-list">
                                <li class="activity-item">
                                    <div class="activity-icon">
                                        <i class="fas fa-user-plus"></i>
                                    </div>
                                    <div class="activity-content">
                                        <p class="mb-1">5 penduduk baru telah ditambahkan ke RT 03</p>
                                        <small class="activity-time">30 menit yang lalu</small>
                                    </div>
                                </li>
                                <li class="activity-item">
                                    <div class="activity-icon">
                                        <i class="fas fa-newspaper"></i>
                                    </div>
                                    <div class="activity-content">
                                        <p class="mb-1">Berita baru "Festival Budaya Desa 2023" telah dipublikasikan</p>
                                        <small class="activity-time">2 jam yang lalu</small>
                                    </div>
                                </li>
                                <li class="activity-item">
                                    <div class="activity-icon">
                                        <i class="fas fa-store"></i>
                                    </div>
                                    <div class="activity-content">
                                        <p class="mb-1">UMKM "Kerajinan Tenun Sari" telah terdaftar</p>
                                        <small class="activity-time">5 jam yang lalu</small>
                                    </div>
                                </li>
                                <li class="activity-item">
                                    <div class="activity-icon">
                                        <i class="fas fa-comment"></i>
                                    </div>
                                    
                                </li>
                                <li class="activity-item">
                                    <div class="activity-icon">
                                        <i class="fas fa-camera"></i>
                                    </div>
                                    <div class="activity-content">
                                        <p class="mb-1">5 foto baru ditambahkan ke galeri "Festival Budaya"</p>
                                        <small class="activity-time">2 hari yang lalu</small>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Aksi Cepat</h5>
                        </div>
                        <div class="card-body">
                            <div class="quick-actions">
                                <a href="tambah-berita.php" class="quick-action-btn">
                                    <i class="fas fa-plus"></i>
                                    <span>Tambah Berita</span>
                                </a>
                                <a href="tambah-umkm.php" class="quick-action-btn">
                                    <i class="fas fa-store"></i>
                                    <span>Tambah UMKM</span>
                                </a>
                                <a href="tambah-galeri.php" class="quick-action-btn">
                                    <i class="fas fa-camera"></i>
                                    <span>Tambah Foto</span>
                                </a>
                                <a href="tambah-penduduk.php" class="quick-action-btn">
                                    <i class="fas fa-user-plus"></i>
                                    <span>Tambah Warga</span>
                                </a>
                                <a href="statistik-penduduk.php" class="quick-action-btn">
                                    <i class="fas fa-chart-bar"></i>
                                    <span>Statistik</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Berita -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Berita Terbaru</h5>
                            <a href="berita.php" class="btn btn-sm btn-primary">Lihat Semua</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Judul</th>
                                            <th>Kategori</th>
                                            <th>Tanggal</th>
                                            <th>Penulis</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Festival Budaya Desa 2023 Sukses Digelar</td>
                                            <td>Event</td>
                                            <td>15 Agu 2023</td>
                                            <td>Admin</td>
                                            <td><span class="badge badge-published">Published</span></td>
                                            <td>
                                                <a href="#" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                                <a href="#" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Desa Winduaji Raih Penghargaan Adipura</td>
                                            <td>Prestasi</td>
                                            <td>28 Jul 2023</td>
                                            <td>Admin</td>
                                            <td><span class="badge badge-published">Published</span></td>
                                            <td>
                                                <a href="#" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                                <a href="#" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Pelatihan Digital Marketing untuk UMKM</td>
                                            <td>Pelatihan</td>
                                            <td>02 Sep 2023</td>
                                            <td>Admin</td>
                                            <td><span class="badge badge-draft">Draft</span></td>
                                            <a href="#" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                                <a href="#" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Main JavaScript -->
    <script>
        // Toggle Sidebar
        $(document).ready(function() {
    $('.toggle-btn').on('click', function() {
        $('.sidebar').toggleClass('hidden');
        $('.main-content').toggleClass('full');
    });
});
        $('.toggle-btn').click(function() {
            $('.sidebar').toggleClass('collapsed');
            $('.main-content').toggleClass('expanded');
        });

        // Submenu Toggle
        $('.has-submenu').click(function(e) {
            e.preventDefault();
            $(this).find('.menu-arrow').toggleClass('fa-chevron-down fa-chevron-up');
            $(this).siblings('.submenu').toggleClass('show');
        });

        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // Initialize charts
        document.addEventListener('DOMContentLoaded', function() {
            // Resident Chart
            const residentCtx = document.createElement('canvas');
            residentCtx.id = 'residentChart';
            document.querySelector('.resident-stats').after(residentCtx);
            
            const residentChart = new Chart(residentCtx, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                    datasets: [{
                        label: 'Penduduk Baru',
                        data: [12, 19, 8, 15, 12, 10, 18, 14, 16, 12, 10, 8],
                        backgroundColor: 'rgba(52, 152, 219, 0.7)',
                        borderColor: 'rgba(52, 152, 219, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Pertumbuhan Penduduk 2023',
                            font: {
                                size: 16
                            }
                        },
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>