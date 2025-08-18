<?php
session_start();

// Pastikan path ke config/database.php benar
require_once __DIR__ . '/../../config/database.php';

// Data desa
$desa_nama = "Desa Winduaji";
$desa_lokasi = "Kecamatan Sejahtera, Kabupaten Makmur, Provinsi Bahagia";
$desa_motto = "Bersama Membangun Desa yang Mandiri dan Berbudaya";

// Ambil data UMKM dari database
// Contoh data statis, Anda harus mengganti dengan query database
$umkm_list = [
    [
        'id' => 1,
        'nama' => 'Kerajinan Tenun Sari',
        'pemilik' => 'Ibu Siti',
        'kategori' => 'Kerajinan',
        'alamat' => 'RT 03 RW 05',
        'telepon' => '081234567890',
        'deskripsi' => 'Menjual berbagai produk tenun tradisional',
        'gambar' => 'umkm1.jpg',
        'status' => 'Aktif',
        'tanggal_daftar' => '2023-01-15'
    ],
    [
        'id' => 2,
        'nama' => 'Warung Makan Sederhana',
        'pemilik' => 'Bapak Joko',
        'kategori' => 'Makanan',
        'alamat' => 'RT 02 RW 04',
        'telepon' => '082345678901',
        'deskripsi' => 'Menyediakan makanan rumahan dengan harga terjangkau',
        'gambar' => 'umkm2.jpg',
        'status' => 'Aktif',
        'tanggal_daftar' => '2023-02-20'
    ],
    [
        'id' => 3,
        'nama' => 'Toko Bangunan Maju Jaya',
        'pemilik' => 'Bapak Ahmad',
        'kategori' => 'Material Bangunan',
        'alamat' => 'RT 01 RW 03',
        'telepon' => '083456789012',
        'deskripsi' => 'Menyediakan berbagai material bangunan berkualitas',
        'gambar' => 'umkm3.jpg',
        'status' => 'Nonaktif',
        'tanggal_daftar' => '2023-03-10'
    ],
    [
        'id' => 4,
        'nama' => 'Jasa Service Elektronik',
        'pemilik' => 'Bapak Budi',
        'kategori' => 'Jasa',
        'alamat' => 'RT 04 RW 02',
        'telepon' => '084567890123',
        'deskripsi' => 'Melayani perbaikan berbagai peralatan elektronik',
        'gambar' => 'umkm4.jpg',
        'status' => 'Aktif',
        'tanggal_daftar' => '2023-04-05'
    ],
    [
        'id' => 5,
        'nama' => 'Toko Kelontong Sejahtera',
        'pemilik' => 'Ibu Rina',
        'kategori' => 'Perdagangan',
        'alamat' => 'RT 05 RW 01',
        'telepon' => '085678901234',
        'deskripsi' => 'Menyediakan berbagai kebutuhan sehari-hari',
        'gambar' => 'umkm5.jpg',
        'status' => 'Aktif',
        'tanggal_daftar' => '2023-05-12'
    ]
];

// Jumlah data untuk statistik dashboard
$total_umkm = count($umkm_list);
$active_umkm = count(array_filter($umkm_list, function($umkm) { return $umkm['status'] === 'Aktif'; }));
$inactive_umkm = $total_umkm - $active_umkm;
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen UMKM - <?php echo $desa_nama; ?></title>
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

        /* Stats Cards */
        .stats-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--primary-color);
            transition: var(--transition);
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .stats-card .card-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .stats-card .card-title {
            font-size: 0.875rem;
            color: var(--gray-medium);
            margin-bottom: 0.5rem;
        }

        .stats-card .card-value {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0;
        }

        .stats-card.total .card-icon {
            background-color: rgba(67, 97, 238, 0.1);
            color: var(--primary-color);
        }

        .stats-card.active .card-icon {
            background-color: rgba(76, 201, 240, 0.1);
            color: var(--success-color);
        }

        .stats-card.inactive .card-icon {
            background-color: rgba(239, 35, 60, 0.1);
            color: var(--danger-color);
        }

        /* Search and Filter */
        .search-filter {
            background: white;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            margin-bottom: 1.5rem;
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

        /* UMKM Image */
        .umkm-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: var(--border-radius);
            border: 1px solid var(--gray-light);
        }

        /* Badges */
        .badge {
            padding: 0.5em 0.75em;
            font-weight: 600;
            border-radius: 4px;
            font-size: 0.75rem;
        }

        .badge-active {
            background-color: rgba(76, 201, 240, 0.1);
            color: var(--success-color);
        }
        
        .badge-inactive {
            background-color: rgba(239, 35, 60, 0.1);
            color: var(--danger-color);
        }

        /* Action Buttons */
        .action-btns .btn {
            padding: 0.375rem 0.5rem;
            font-size: 0.875rem;
            margin-right: 0.25rem;
        }

        /* Add Button */
        .add-umkm-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Pagination */
        .pagination .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .pagination .page-link {
            color: var(--primary-color);
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
            .stats-card {
                margin-bottom: 1rem;
            }
            
            .table-responsive {
                overflow-x: auto;
            }
            
            .umkm-img {
                width: 40px;
                height: 40px;
            }
        }

        @media (max-width: 576px) {
            .content-section {
                padding: 1.5rem 1rem;
            }
            
            .section-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .search-filter .row > div {
                margin-bottom: 1rem;
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
                <span class="d-none d-md-inline">Manajemen UMKM</span>
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
                <h1 class="section-title">Manajemen UMKM</h1>
                <div>
                    <span class="text-muted"><?php echo date('d F Y, H:i'); ?></span>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row">
                <div class="col-md-4">
                    <div class="stats-card total">
                        <div class="card-icon">
                            <i class="fas fa-store"></i>
                        </div>
                        <h6 class="card-title">Total UMKM</h6>
                        <h3 class="card-value"><?php echo $total_umkm; ?></h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card active">
                        <div class="card-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h6 class="card-title">UMKM Aktif</h6>
                        <h3 class="card-value"><?php echo $active_umkm; ?></h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card inactive">
                        <div class="card-icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <h6 class="card-title">UMKM Nonaktif</h6>
                        <h3 class="card-value"><?php echo $inactive_umkm; ?></h3>
                    </div>
                </div>
            </div>

            <!-- Search and Filter Section -->
            <div class="search-filter">
                <div class="row">
                    <div class="col-md-8">
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" placeholder="Cari UMKM berdasarkan nama, pemilik, atau alamat...">
                            <button class="btn btn-primary" type="button">
                                <i class="fas fa-search"></i> Cari
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex justify-content-end">
                            <a href="tambah-umkm.php" class="btn btn-primary add-umkm-btn">
                                <i class="fas fa-plus"></i> Tambah UMKM
                            </a>
                        </div>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-3">
                        <select class="form-select">
                            <option selected>Semua Kategori</option>
                            <option>Makanan</option>
                            <option>Kerajinan</option>
                            <option>Jasa</option>
                            <option>Material Bangunan</option>
                            <option>Perdagangan</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select">
                            <option selected>Semua Status</option>
                            <option>Aktif</option>
                            <option>Nonaktif</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select">
                            <option selected>Urutkan Berdasarkan</option>
                            <option>Nama (A-Z)</option>
                            <option>Nama (Z-A)</option>
                            <option>Terbaru</option>
                            <option>Terlama</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-outline-secondary w-100">
                            <i class="fas fa-filter"></i> Terapkan Filter
                        </button>
                    </div>
                </div>
            </div>

            <!-- UMKM List Table -->
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Daftar UMKM</h5>
                    <div>
                        <span class="text-muted me-3">Menampilkan <?php echo count($umkm_list); ?> dari <?php echo $total_umkm; ?> UMKM</span>
                        <button class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="50px">#</th>
                                    <th>Nama UMKM</th>
                                    <th>Pemilik</th>
                                    <th>Kategori</th>
                                    <th>Kontak</th>
                                    <th>Status</th>
                                    <th>Tanggal Daftar</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($umkm_list as $index => $umkm): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="../assets/images/umkm/<?php echo $umkm['gambar']; ?>" alt="<?php echo $umkm['nama']; ?>" class="umkm-img me-3">
                                            <div>
                                                <h6 class="mb-1"><?php echo $umkm['nama']; ?></h6>
                                                <small class="text-muted"><?php echo $umkm['alamat']; ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo $umkm['pemilik']; ?></td>
                                    <td>
                                        <span class="badge bg-light text-dark"><?php echo $umkm['kategori']; ?></span>
                                    </td>
                                    <td>
                                        <a href="tel:<?php echo $umkm['telepon']; ?>" class="text-primary">
                                            <i class="fas fa-phone-alt me-1"></i> <?php echo $umkm['telepon']; ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $umkm['status'] == 'Aktif' ? 'badge-active' : 'badge-inactive'; ?>">
                                            <?php echo $umkm['status']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d M Y', strtotime($umkm['tanggal_daftar'])); ?></td>
                                    <td class="action-btns">
                                        <a href="edit-umkm.php?id=<?php echo $umkm['id']; ?>" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="#" class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip" title="Hapus" onclick="confirmDelete(<?php echo $umkm['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                        <a href="detail-umkm.php?id=<?php echo $umkm['id']; ?>" class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1" aria-disabled="true">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item">
                                <a class="page-link" href="#">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Main JavaScript -->
    <script>
        // Toggle Sidebar
        $('.toggle-btn').click(function() {
            $('.sidebar').toggleClass('collapsed');
            $('.main-content').toggleClass('expanded');
        });

        // Submenu Toggle
        $('.has-submenu').click(function(e) {
            e.preventDefault();
            $(this).toggleClass('active');
            $(this).find('.menu-arrow').toggleClass('fa-chevron-down fa-chevron-up');
            $(this).siblings('.submenu').toggleClass('show');
        });

        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // Confirmation for delete action
        function confirmDelete(id) {
            if (confirm('Apakah Anda yakin ingin menghapus UMKM ini?')) {
                // Action to delete the UMKM
                alert('UMKM dengan ID ' + id + ' berhasil dihapus!');
                // window.location.href = 'hapus-umkm.php?id=' + id;
            }
        }
        
        // Search functionality
        $('#searchInput').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $('table tbody tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
    </script>
</body>
</html>