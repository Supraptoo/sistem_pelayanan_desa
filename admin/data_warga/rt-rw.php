<?php
session_start();

// Pastikan path ke config/database.php benar
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';

// Data desa
$desa_nama = "Desa Harapan Maju";
$desa_lokasi = "Kecamatan Sejahtera, Kabupaten Makmur, Provinsi Bahagia";
$desa_motto = "Bersama Membangun Desa yang Mandiri dan Berbudaya";

// Data statistik (contoh)
$total_rt = 75;
$total_rw = 15;
$rw_terisi = 12;
$rt_terisi = 68;
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data RT/RW - <?php echo $desa_nama; ?></title>
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

        /* Search and Filter */
        .search-filter {
            background: white;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            margin-bottom: 1.5rem;
        }

        /* Pagination */
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
        }

        .pagination-info {
            color: var(--gray-medium);
            font-size: 0.9rem;
        }

        /* Action buttons */
        .action-btns .btn {
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }
    </style>
</head>

<body>
     <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <a href="../admin/dashboard.php" class="sidebar-brand">
                <img src="../assets/images/logo.png" alt="Logo <?php echo $desa_nama; ?>">
                <span><?php echo $desa_nama; ?></span>
            </a>
        </div>
        <div class="sidebar-menu-container">
            <ul class="sidebar-menu">
                <li>
                    <a href="dashboard.php" class="active">
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
                        <li><a href="../galeri/foto.php">Foto</a></li>
                        
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
                <span class="d-none d-md-inline">Data RT/RW</span>
            </div>
            <div class="user-menu">
                <div class="dropdown">
                    <button class="dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="../../assets/images/logo.png" alt="User Avatar" class="user-avatar">
                        <span class="user-name d-none d-md-inline">Admin</span>
                        <i class="fas fa-chevron-down d-none d-md-inline"></i>
                    </button>
                </div>
            </div>
        </nav>

        <!-- Content Section -->
        <div class="content-section">
            <div class="section-header">
                <h1 class="section-title">Data RT/RW</h1>
                <div>
                    <a href="tambah-rt-rw.php" class="btn btn-primary me-2">
                        <i class="fas fa-plus me-2"></i>Tambah RT
                    </a>
                    <a href="tambah-rt-rw.php?type=rw" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Tambah RW
                    </a>
                </div>
            </div>

            <!-- Statistik RT/RW -->
            <div class="resident-stats">
                <div class="resident-stat-item">
                    <h5>Total RT</h5>
                    <p><?php echo number_format($total_rt, 0, ',', '.'); ?></p>
                    <small><?php echo number_format($rt_terisi, 0, ',', '.'); ?> RT terisi</small>
                </div>
                <div class="resident-stat-item">
                    <h5>Total RW</h5>
                    <p><?php echo number_format($total_rw, 0, ',', '.'); ?></p>
                    <small><?php echo number_format($rw_terisi, 0, ',', '.'); ?> RW terisi</small>
                </div>
                <div class="resident-stat-item">
                    <h5>RT Kosong</h5>
                    <p><?php echo number_format($total_rt - $rt_terisi, 0, ',', '.'); ?></p>
                    <small><?php echo round((($total_rt - $rt_terisi)/$total_rt)*100, 2); ?>% dari total</small>
                </div>
                <div class="resident-stat-item">
                    <h5>RW Kosong</h5>
                    <p><?php echo number_format($total_rw - $rw_terisi, 0, ',', '.'); ?></p>
                    <small><?php echo round((($total_rw - $rw_terisi)/$total_rw)); ?>% dari total</small>
                </div>
            </div>

            <!-- Tab Navigation -->
            <ul class="nav nav-tabs mb-4" id="rtRwTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="rt-tab" data-bs-toggle="tab" data-bs-target="#rt-tab-pane" type="button" role="tab">
                        <i class="fas fa-home me-2"></i>Data RT
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="rw-tab" data-bs-toggle="tab" data-bs-target="#rw-tab-pane" type="button" role="tab">
                        <i class="fas fa-city me-2"></i>Data RW
                    </button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="rtRwTabContent">
                <!-- RT Tab -->
                <div class="tab-pane fade show active" id="rt-tab-pane" role="tabpanel" tabindex="0">
                    <div class="card">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Daftar RT</h5>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="exportRtDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-download me-1"></i> Export
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="exportRtDropdown">
                                    <li><a class="dropdown-item" href="#"><i class="fas fa-file-excel me-2"></i> Excel</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="fas fa-file-pdf me-2"></i> PDF</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nomor RT</th>
                                            <th>RW</th>
                                            <th>Ketua RT</th>
                                            <th>Jumlah KK</th>
                                            <th>Jumlah Penduduk</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td>RT 001</td>
                                            <td>RW 001</td>
                                            <td>Budi Santoso</td>
                                            <td>42</td>
                                            <td>168</td>
                                            <td><span class="badge bg-success bg-opacity-10 text-success">Aktif</span></td>
                                            <td class="action-btns">
                                                <a href="detail-rt.php?id=1" class="btn btn-sm btn-outline-primary" title="Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="edit-rt.php?id=1" class="btn btn-sm btn-outline-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn btn-sm btn-outline-danger" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>2</td>
                                            <td>RT 002</td>
                                            <td>RW 001</td>
                                            <td>Ani Lestari</td>
                                            <td>38</td>
                                            <td>152</td>
                                            <td><span class="badge bg-success bg-opacity-10 text-success">Aktif</span></td>
                                            <td class="action-btns">
                                                <a href="detail-rt.php?id=2" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="edit-rt.php?id=2" class="btn btn-sm btn-outline-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>3</td>
                                            <td>RT 003</td>
                                            <td>RW 001</td>
                                            <td>-</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td><span class="badge bg-secondary bg-opacity-10 text-secondary">Kosong</span></td>
                                            <td class="action-btns">
                                                <button class="btn btn-sm btn-outline-primary" disabled>
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <a href="edit-rt.php?id=3" class="btn btn-sm btn-outline-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="pagination-container mt-3">
                                <div class="pagination-info">
                                    Menampilkan 1 sampai 3 dari <?php echo number_format($total_rt, 0, ',', '.'); ?> data
                                </div>
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-end">
                                        <li class="page-item disabled">
                                            <a class="page-link" href="#" tabindex="-1">Previous</a>
                                        </li>
                                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                                        <li class="page-item">
                                            <a class="page-link" href="#">Next</a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RW Tab -->
                <div class="tab-pane fade" id="rw-tab-pane" role="tabpanel" tabindex="0">
                    <div class="card">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Daftar RW</h5>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="exportRwDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-download me-1"></i> Export
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="exportRwDropdown">
                                    <li><a class="dropdown-item" href="#"><i class="fas fa-file-excel me-2"></i> Excel</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="fas fa-file-pdf me-2"></i> PDF</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nomor RW</th>
                                            <th>Ketua RW</th>
                                            <th>Jumlah RT</th>
                                            <th>Jumlah KK</th>
                                            <th>Jumlah Penduduk</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td>RW 001</td>
                                            <td>Citra Dewi</td>
                                            <td>5</td>
                                            <td>210</td>
                                            <td>840</td>
                                            <td><span class="badge bg-success bg-opacity-10 text-success">Aktif</span></td>
                                            <td class="action-btns">
                                                <a href="detail-rw.php?id=1" class="btn btn-sm btn-outline-primary" title="Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="edit-rw.php?id=1" class="btn btn-sm btn-outline-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn btn-sm btn-outline-danger" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>2</td>
                                            <td>RW 002</td>
                                            <td>Dodi Prasetyo</td>
                                            <td>5</td>
                                            <td>195</td>
                                            <td>780</td>
                                            <td><span class="badge bg-success bg-opacity-10 text-success">Aktif</span></td>
                                            <td class="action-btns">
                                                <a href="detail-rw.php?id=2" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="edit-rw.php?id=2" class="btn btn-sm btn-outline-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>3</td>
                                            <td>RW 015</td>
                                            <td>-</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td><span class="badge bg-secondary bg-opacity-10 text-secondary">Kosong</span></td>
                                            <td class="action-btns">
                                                <button class="btn btn-sm btn-outline-primary" disabled>
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <a href="edit-rw.php?id=15" class="btn btn-sm btn-outline-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="pagination-container mt-3">
                                <div class="pagination-info">
                                    Menampilkan 1 sampai 3 dari <?php echo number_format($total_rw, 0, ',', '.'); ?> data
                                </div>
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-end">
                                        <li class="page-item disabled">
                                            <a class="page-link" href="#" tabindex="-1">Previous</a>
                                        </li>
                                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                                        <li class="page-item">
                                            <a class="page-link" href="#">Next</a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Toggle Sidebar
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

        // Confirm delete action
        $('.btn-outline-danger').click(function() {
            if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                // Action to delete
                $(this).closest('tr').fadeOut();
                alert('Data berhasil dihapus');
            }
        });

        // Initialize tabs
        var rtRwTab = new bootstrap.Tab(document.getElementById('rt-tab'));
        var rwTab = new bootstrap.Tab(document.getElementById('rw-tab'));
    </script>
</body>
</html>