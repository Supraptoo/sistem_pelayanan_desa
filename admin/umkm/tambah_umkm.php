<?php
session_start();

// Pastikan path ke config/database.php benar
require_once __DIR__ . '/../../config/database.php';

// Data desa
$desa_nama = "Desa Winduaji";
$desa_lokasi = "Kecamatan Sejahtera, Kabupaten Makmur, Provinsi Bahagia";
$desa_motto = "Bersama Membangun Desa yang Mandiri dan Berbudaya";

// Proses form tambah UMKM
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $nama = $_POST['nama'] ?? '';
    $pemilik = $_POST['pemilik'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $telepon = $_POST['telepon'] ?? '';
    $deskripsi = $_POST['deskripsi'] ?? '';
    $status = $_POST['status'] ?? 'Aktif';
    
    // Proses upload gambar
    $gambar = '';
    if (isset($_FILES['gambar'])) {
        $file = $_FILES['gambar'];
        $filename = $file['name'];
        $filetmp = $file['tmp_name'];
        $filesize = $file['size'];
        $fileerror = $file['error'];
        
        // Cek apakah ada file yang diupload
        if ($fileerror === 0) {
            // Generate nama file unik
            $fileext = pathinfo($filename, PATHINFO_EXTENSION);
            $fileext = strtolower($fileext);
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($fileext, $allowed)) {
                $newfilename = uniqid('umkm_', true) . '.' . $fileext;
                $filedestination = '../../assets/images/umkm/' . $newfilename;
                
                if (move_uploaded_file($filetmp, $filedestination)) {
                    $gambar = $newfilename;
                }
            }
        }
    }
    
    // Simpan data ke database (contoh, sesuaikan dengan database Anda)
    // $query = "INSERT INTO umkm (nama, pemilik, alamat, telepon, deskripsi, gambar, status, tanggal_daftar) 
    //           VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    // $stmt = $pdo->prepare($query);
    // $stmt->execute([$nama, $pemilik, $alamat, $telepon, $deskripsi, $gambar, $status]);
    
    // Setelah berhasil disimpan, redirect ke halaman UMKM
    // header('Location: umkm.php');
    // exit;
    
    // Untuk contoh, kita tampilkan pesan sukses
    $success_message = "UMKM berhasil ditambahkan!";
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah UMKM - <?php echo $desa_nama; ?></title>
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

        /* Form Styles */
        .form-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--shadow-sm);
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            border-radius: var(--border-radius);
            padding: 0.75rem 1rem;
            border: 1px solid var(--gray-light);
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            border-radius: var(--border-radius);
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .preview-image {
            max-width: 200px;
            max-height: 200px;
            border-radius: var(--border-radius);
            border: 1px solid var(--gray-light);
            margin-top: 1rem;
            display: none;
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
            .content-section {
                padding: 1.5rem;
            }
        }

        @media (max-width: 576px) {
            .content-section {
                padding: 1rem;
            }
            
            .section-header {
                flex-direction: column;
                align-items: flex-start;
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
                    <a href="../admin/dashboard.php">
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
                        <li><a href="../admin/data_warga/keluarga.php">Data Keluarga</a></li>
                        <li><a href="../admin/data_warga/rt-rw.php">Data RT/RW</a></li>
                        <li><a href="../admin/data_warga/penduduk.php">Data Penduduk</a></li>
                    </ul>
                </li>
                <li>
                    <a href="javascript:void(0);" class="has-submenu">
                        <i class="fas fa-newspaper"></i>
                        <span class="menu-text">Berita</span>
                        <i class="fas fa-chevron-down menu-arrow"></i>
                    </a>
                    <ul class="submenu">
                        <li><a href="../admin/berita/berita.php">Kelola Berita</a></li>
                        <li><a href="../admin/berita/kategori_berita.php">Kategori</a></li>
                    </ul>
                </li>
                <li>
                    <a href="javascript:void(0);" class="has-submenu active">
                        <i class="fas fa-store"></i>
                        <span class="menu-text">UMKM</span>
                        <i class="fas fa-chevron-down menu-arrow"></i>
                    </a>
                    <ul class="submenu show">
                        <li><a href="../admin/umkm/umkm.php">Daftar UMKM</a></li>
                        <li><a href="../admin/umkm/kategori-umkm.php">Kategori</a></li>
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
                    <a href="../logout.php">
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
                <span class="d-none d-md-inline">Tambah UMKM</span>
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
                <h1 class="section-title">Tambah UMKM Baru</h1>
                <div>
                    <a href="umkm.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i> Kembali
                    </a>
                </div>
            </div>

            <?php if (isset($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="form-card">
                <form action="tambah_umkm.php" method="POST" enctype="multipart/form-data">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nama" class="form-label">Nama UMKM</label>
                                <input type="text" class="form-control" id="nama" name="nama" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="pemilik" class="form-label">Nama Pemilik</label>
                                <input type="text" class="form-control" id="pemilik" name="pemilik" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="alamat" class="form-label">Alamat</label>
                                <input type="text" class="form-control" id="alamat" name="alamat" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="telepon" class="form-label">Nomor Telepon</label>
                                <input type="tel" class="form-control" id="telepon" name="telepon" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="gambar" class="form-label">Gambar UMKM</label>
                                <input type="file" class="form-control" id="gambar" name="gambar" accept="image/*" onchange="previewImage(this)">
                                <small class="text-muted">Format: JPG, PNG, GIF. Maksimal 2MB.</small>
                                <img id="imagePreview" class="preview-image" alt="Preview Gambar">
                            </div>
                            
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="Aktif" selected>Aktif</option>
                                    <option value="Nonaktif">Nonaktif</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi UMKM</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="5" required></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-end mt-4">
                        <button type="reset" class="btn btn-outline-secondary me-3">
                            <i class="fas fa-undo me-2"></i> Reset
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Simpan UMKM
                        </button>
                    </div>
                </form>
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

        // Image Preview
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            const file = input.files[0];
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            
            if (file) {
                reader.readAsDataURL(file);
            } else {
                preview.src = '';
                preview.style.display = 'none';
            }
        }
    </script>
</body>
</html>