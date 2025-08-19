<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';

// Pesan operasi berhasil/tidak
$message = '';
$message_type = '';

// Handle delete berita
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    try {
        // Hapus gambar terlebih dahulu jika ada
        $stmt = $pdo->prepare("SELECT gambar_path FROM berita WHERE id = ?");
        $stmt->execute([$id]);
        $gambar = $stmt->fetchColumn();
        
        if ($gambar && file_exists("../uploads/berita/" . $gambar)) {
            unlink("../uploads/berita/" . $gambar);
        }
        
        // Hapus dari database
        $stmt = $pdo->prepare("DELETE FROM berita WHERE id = ?");
        $stmt->execute([$id]);
        
        $message = 'Berita berhasil dihapus!';
        $message_type = 'success';
    } catch (PDOException $e) {
        $message = 'Gagal menghapus berita: ' . $e->getMessage();
        $message_type = 'danger';
    }
}

// Handle publish/unpublish
if (isset($_GET['toggle_status'])) {
    $id = (int)$_GET['toggle_status'];
    
    try {
        $stmt = $pdo->prepare("UPDATE berita SET status = IF(status='published', 'draft', 'published') WHERE id = ?");
        $stmt->execute([$id]);
        
        $message = 'Status berita berhasil diubah!';
        $message_type = 'success';
    } catch (PDOException $e) {
        $message = 'Gagal mengubah status berita: ' . $e->getMessage();
        $message_type = 'danger';
    }
}

// Handle form tambah berita
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = $_POST['judul'] ?? '';
    $isi = $_POST['isi'] ?? '';
    $embed_link = $_POST['embed_link'] ?? '';
    $status = $_POST['status'] ?? 'draft';
    $kategori_id = $_POST['kategori_id'] ?? 1;
    $tanggal = date('Y-m-d H:i:s');
    $content_type = $_POST['content_type'] ?? 'manual'; // manual atau link
    
    try {
        // Handle file upload
        $gambar = '';
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['gambar'];
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $gambar = 'berita_' . time() . '.' . $ext;
            $upload_dir = '../uploads/berita/';
            
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            if (!move_uploaded_file($file['tmp_name'], $upload_dir . $gambar)) {
                throw new Exception("Gagal mengupload gambar");
            }
        }
        
        // Jika input berupa link, proses link tersebut
        if ($content_type === 'link' && !empty($embed_link)) {
            $isi = processEmbedLink($embed_link);
            if (empty($judul)) {
                $judul = "Berita dari " . parse_url($embed_link, PHP_URL_HOST);
            }
        }
        
        // Insert ke database
 $slug = createSlug($judul);
    
    $stmt = $pdo->prepare("INSERT INTO berita (judul, slug, isi, gambar_path, embed_link, status, tanggal_publish, kategori_id, created_by) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $judul, 
        $slug,
        $isi, 
        $gambar, 
        $embed_link, 
        $status, 
        $tanggal, 
        $kategori_id, 
        $_SESSION['admin_id'] ?? null
    ]);
    
    $message = 'Berita berhasil ditambahkan!';
    $message_type = 'success';
    
    // Redirect untuk menghindari resubmit
    header("Location ../../pages/berita.php:?success=1");
    exit();
} catch (Exception $e) {
    $message = 'Gagal menambahkan berita: ' . $e->getMessage();
    $message_type = 'danger';
}

}

// Fungsi untuk memproses embed link
function processEmbedLink($url) {
    // Instagram
    if (preg_match('/instagram\.com\/p\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
        $post_id = $matches[1];
        return '<blockquote class="instagram-media" data-instgrm-permalink="https://www.instagram.com/p/'.$post_id.'/" 
                style="background:#FFF; border:0; border-radius:3px; box-shadow:0 0 1px 0 rgba(0,0,0,0.5),0 1px 10px 0 rgba(0,0,0,0.15); 
                margin: 1px; max-width:540px; min-width:326px; padding:0; width:99.375%; width:-webkit-calc(100% - 2px); 
                width:calc(100% - 2px);"></blockquote><script async src="//www.instagram.com/embed.js"></script>';
    }
    // YouTube
    elseif (preg_match('/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/', $url, $matches) || 
            preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
        $video_id = $matches[1];
        return '<div class="embed-responsive embed-responsive-16by9">
                <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/'.$video_id.'" 
                frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                allowfullscreen></iframe></div>';
    }
    // Twitter
    elseif (preg_match('/twitter\.com\/[a-zA-Z0-9_]+\/status\/([0-9]+)/', $url, $matches)) {
        $tweet_id = $matches[1];
        return '<blockquote class="twitter-tweet"><a href="'.$url.'"></a></blockquote> 
                <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>';
    }
    // Default untuk link biasa
    else {
        return '<p><a href="'.$url.'" target="_blank">'.$url.'</a></p>';
    }
}

// Ambil semua berita untuk ditampilkan
try {
    $stmt = $pdo->query("SELECT b.*, bk.nama_kategori 
                        FROM berita b
                        LEFT JOIN berita_kategori bk ON b.kategori_id = bk.id
                        ORDER BY b.tanggal_publish DESC");
    $all_berita = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Ambil kategori berita untuk dropdown
    $stmt = $pdo->query("SELECT * FROM berita_kategori");
    $kategori_berita = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error mengambil data berita: " . $e->getMessage());
}

// Data desa
$desa_nama = "Desa Winduaji";
$desa_lokasi = "Kecamatan Paninggaran, Kabupaten Pekalongan, Provinsi Jawa Tengah";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Berita - Admin <?php echo $desa_nama; ?></title>
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

        /* Table Styles */
        .table-responsive {
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            background: white;
        }

        .table {
            margin-bottom: 0;
            width: 100%;
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
            border-top: 1px solid var(--gray-light);
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
        
        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            margin-right: 5px;
            transition: var(--transition);
        }
        
        .action-btn:hover {
            transform: scale(1.1);
        }
        
        .btn-add {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius);
            display: inline-flex;
            align-items: center;
            transition: var(--transition);
        }
        
        .btn-add:hover {
            background-color: var(--secondary-color);
            color: white;
            transform: translateY(-2px);
        }
        
        .btn-add i {
            margin-right: 8px;
        }

        /* Alert Message */
        .alert {
            border-radius: var(--border-radius);
            border-left: 4px solid;
        }

        /* Form Styles */
        .form-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            padding: 2rem;
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
        
        .btn-submit {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            transition: var(--transition);
        }
        
        .btn-submit:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        .preview-image {
            max-width: 100%;
            height: auto;
            border-radius: var(--border-radius);
            margin-top: 1rem;
            display: none;
        }
        
        .embed-preview {
            margin-top: 1rem;
            border: 1px dashed var(--gray-medium);
            padding: 1rem;
            border-radius: var(--border-radius);
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

        /* Animation for sidebar toggle */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .sidebar-menu-container::-webkit-scrollbar {
            width: 4px;
        }
   .nav-tabs .nav-link {
            border: none;
            color: var(--gray-dark);
            font-weight: 500;
            padding: 0.75rem 1.5rem;
        }
        
        .nav-tabs .nav-link.active {
            color: var(--primary-color);
            border-bottom: 3px solid var(--primary-color);
            background-color: transparent;
        }
        
        .tab-content {
            padding: 1.5rem 0;
        }
        
        .embed-preview {
            background-color: var(--primary-light);
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-top: 1rem;
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
                    <a href="javascript:void(0);" class="has-submenu active">
                        <i class="fas fa-newspaper"></i>
                        <span class="menu-text">Berita</span>
                        <i class="fas fa-chevron-down menu-arrow"></i>
                    </a>
                    <ul class="submenu show">
                        <li><a href="../admin/berita/berita.php" class="active">Kelola Berita</a></li>
                        <li><a href="../admin/berita/kategori_berita.php">Kategori</a></li>
                    </ul>
                </li>
                <li>
                    <a href="javascript:void(0);" class="has-submenu">
                        <i class="fas fa-store"></i>
                        <span class="menu-text">UMKM</span>
                        <i class="fas fa-chevron-down menu-arrow"></i>
                    </a>
                    <ul class="submenu">
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
                <span class="d-none d-md-inline">Tambah Berita Baru</span>
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

       <div class="content-section">
            <div class="section-header">
                <h1 class="section-title">Kelola Berita</h1>
                <div>
                    <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#tambahBeritaModal">
                        <i class="fas fa-plus me-2"></i>Tambah Berita
                    </button>
                </div>
            </div>

            <!-- Alert Message -->
            <?php if ($message): ?>
            <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert" style="border-left-color: var(--<?= $message_type ?>-color)">
                <?= $message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="50">#</th>
                                    <th>Judul</th>
                                    <th>Kategori</th>
                                    <th width="150">Tanggal</th>
                                    <th width="120">Status</th>
                                    <th width="150">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($all_berita) > 0): ?>
                                    <?php foreach ($all_berita as $index => $berita): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($berita['judul']) ?></strong><br>
                                            <small class="text-muted"><?= excerpt($berita['isi'], 50) ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($berita['nama_kategori'] ?? 'Umum') ?></td>
                                        <td><?= date('d M Y', strtotime($berita['tanggal_publish'])) ?></td>
                                        <td>
                                            <span class="badge <?= $berita['status'] === 'published' ? 'badge-published' : 'badge-draft' ?>">
                                                <?= ucfirst($berita['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="edit_berita.php?id=<?= $berita['id'] ?>" class="btn btn-sm btn-primary action-btn" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?toggle_status=<?= $berita['id'] ?>" class="btn btn-sm btn-warning action-btn" title="<?= $berita['status'] === 'published' ? 'Unpublish' : 'Publish' ?>">
                                                <i class="fas fa-<?= $berita['status'] === 'published' ? 'eye-slash' : 'eye' ?>"></i>
                                            </a>
                                            <a href="?delete=<?= $berita['id'] ?>" class="btn btn-sm btn-danger action-btn" title="Hapus" onclick="return confirm('Yakin ingin menghapus berita ini?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4">Tidak ada berita ditemukan</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Berita -->
    <div class="modal fade" id="tambahBeritaModal" tabindex="-1" aria-labelledby="tambahBeritaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tambahBeritaModalLabel">Tambah Berita Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" id="beritaTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="manual-tab" data-bs-toggle="tab" data-bs-target="#manual" type="button" role="tab" aria-controls="manual" aria-selected="true">
                                <i class="fas fa-keyboard me-2"></i>Manual
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="link-tab" data-bs-toggle="tab" data-bs-target="#link" type="button" role="tab" aria-controls="link" aria-selected="false">
                                <i class="fas fa-link me-2"></i>Tempel Link
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content" id="beritaTabsContent">
                        <div class="tab-pane fade show active" id="manual" role="tabpanel" aria-labelledby="manual-tab">
                            <form id="formManual" action="berita.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="content_type" value="manual">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label for="judul" class="form-label">Judul Berita</label>
                                            <input type="text" class="form-control" id="judul" name="judul" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="isi" class="form-label">Isi Berita</label>
                                            <textarea class="form-control" id="isi" name="isi" rows="8" required></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="kategori_id" class="form-label">Kategori</label>
                                            <select class="form-select" id="kategori_id" name="kategori_id" required>
                                                <?php foreach ($kategori_berita as $kategori): ?>
                                                <option value="<?= $kategori['id'] ?>"><?= htmlspecialchars($kategori['nama_kategori']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="gambar" class="form-label">Gambar Berita</label>
                                            <input type="file" class="form-control" id="gambar" name="gambar" accept="image/*">
                                            <img id="imagePreview" class="preview-image mt-2" src="#" alt="Preview Gambar" style="max-width: 100%; display: none;">
                                        </div>
                                        <div class="mb-3">
                                            <label for="status" class="form-label">Status</label>
                                            <select class="form-select" id="status" name="status">
                                                <option value="draft">Draft</option>
                                                <option value="published" selected>Published</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="tab-pane fade" id="link" role="tabpanel" aria-labelledby="link-tab">
                            <form id="formLink" action="berita.php" method="POST">
                                <input type="hidden" name="content_type" value="link">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label for="judul_link" class="form-label">Judul Berita (Opsional)</label>
                                            <input type="text" class="form-control" id="judul_link" name="judul">
                                            <small class="text-muted">Biarkan kosong untuk menggunakan judul default</small>
                                        </div>
                                        <div class="mb-3">
                                            <label for="embed_link" class="form-label">Tempel Link</label>
                                            <input type="url" class="form-control" id="embed_link" name="embed_link" 
                                                   placeholder="https://www.instagram.com/p/..." required>
                                            <small class="text-muted">Contoh: Instagram, YouTube, Twitter, dll.</small>
                                            <div id="embedPreview" class="embed-preview mt-2" style="display: none;"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="kategori_id_link" class="form-label">Kategori</label>
                                            <select class="form-select" id="kategori_id_link" name="kategori_id" required>
                                                <?php foreach ($kategori_berita as $kategori): ?>
                                                <option value="<?= $kategori['id'] ?>"><?= htmlspecialchars($kategori['nama_kategori']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="status_link" class="form-label">Status</label>
                                            <select class="form-select" id="status_link" name="status">
                                                <option value="draft">Draft</option>
                                                <option value="published" selected>Published</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" id="submitManual" class="btn btn-primary">Simpan Berita Manual</button>
                    <button type="button" id="submitLink" class="btn btn-primary" style="display: none;">Simpan Berita dari Link</button>
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
            $(this).find('.menu-arrow').toggleClass('fa-chevron-down fa-chevron-up');
            $(this).siblings('.submenu').toggleClass('show');
        });

        // Auto close alert after 5 seconds
        $(document).ready(function() {
            setTimeout(function() {
                $('.alert').alert('close');
            }, 5000);
            
            // Image preview
            $('#gambar').change(function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#imagePreview').attr('src', e.target.result).show();
                    }
                    reader.readAsDataURL(file);
                } else {
                    $('#imagePreview').hide();
                }
            });
            
            // Embed link preview
            $('#embed_link').on('input', function() {
                const link = $(this).val();
                if (link) {
                    $('#embedPreview').html('<div class="text-center py-3"><i class="fas fa-spinner fa-spin fa-2x"></i><p>Memproses link...</p></div>').show();
                    
                    // Simulasi preview (di implementasi nyata bisa menggunakan API)
                    setTimeout(function() {
                        if (link.includes('instagram.com')) {
                            $('#embedPreview').html('<div class="text-center"><i class="fab fa-instagram fa-3x text-danger"></i><p class="mt-2">Postingan Instagram</p></div>');
                        } 
                        else if (link.includes('youtube.com') || link.includes('youtu.be')) {
                            $('#embedPreview').html('<div class="text-center"><i class="fab fa-youtube fa-3x text-danger"></i><p class="mt-2">Video YouTube</p></div>');
                        } 
                        else if (link.includes('twitter.com')) {
                            $('#embedPreview').html('<div class="text-center"><i class="fab fa-twitter fa-3x text-primary"></i><p class="mt-2">Tweet Twitter</p></div>');
                        } 
                        else {
                            $('#embedPreview').html('<div class="text-center"><i class="fas fa-link fa-3x"></i><p class="mt-2">Link Eksternal</p></div>');
                        }
                    }, 800);
                } else {
                    $('#embedPreview').hide();
                }
            });
            
            // Handle tab switch
            $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                if (e.target.id === 'manual-tab') {
                    $('#submitManual').show();
                    $('#submitLink').hide();
                } else if (e.target.id === 'link-tab') {
                    $('#submitManual').hide();
                    $('#submitLink').show();
                }
            });
            
            // Form submission
            $('#submitManual').click(function() {
                $('#formManual').submit();
            });
            
            $('#submitLink').click(function() {
                $('#formLink').submit();
            });
        });
    </script>
</body>
</html>