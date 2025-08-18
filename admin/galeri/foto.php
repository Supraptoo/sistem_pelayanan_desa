<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';



// Data desa
$desa_nama = "Desa Winduaji";
$desa_lokasi = "Kecamatan Paninggaran, Kabupaten Pekalongan, Provinsi Jawa Tengah";
$desa_motto = "Bersama Membangun Desa yang Mandiri dan Berbudaya";

// Pesan operasi
$message = '';
$message_type = '';

// Handle upload foto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['foto'])) {
    $judul = $_POST['judul'] ?? basename($_FILES['foto']['name']);
    $kategori_id = (int)($_POST['kategori_id'] ?? 1); // Default ke kategori 1 (Kegiatan Desa)
    $deskripsi = $_POST['deskripsi'] ?? '';
    $tags = $_POST['tags'] ?? '';
    
    // Direktori upload
    $uploadDir = __DIR__ . '/../../uploads/galeri/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Validasi file
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $fileType = $_FILES['foto']['type'];
    $fileSize = $_FILES['foto']['size'];
    $fileExt = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    $fileName = 'galeri_' . time() . '_' . uniqid() . '.' . $fileExt;
    $targetPath = $uploadDir . $fileName;
    
    if (!in_array($fileType, $allowedTypes)) {
        $message = 'Format file tidak didukung. Hanya JPEG, PNG, GIF, dan WebP yang diperbolehkan.';
        $message_type = 'danger';
    } elseif ($fileSize > 5 * 1024 * 1024) { // 5MB
        $message = 'Ukuran file terlalu besar. Maksimal 5MB.';
        $message_type = 'danger';
    } elseif (move_uploaded_file($_FILES['foto']['tmp_name'], $targetPath)) {
        // Buat thumbnail
        $thumbnailPath = $uploadDir . 'thumb_' . $fileName;
        createThumbnail($targetPath, $thumbnailPath, 300, 300);
        
        // Simpan ke database
        try {
            $stmt = $pdo->prepare("INSERT INTO galeri (kategori_id, judul, deskripsi, tags, file_path, thumbnail_path, tipe, status, tanggal_upload) 
                                  VALUES (?, ?, ?, ?, ?, ?, 'foto', 'published', CURDATE())");
            $stmt->execute([$kategori_id, $judul, $deskripsi, $tags, 'uploads/galeri/' . $fileName, 'uploads/galeri/thumb_' . $fileName]);
            
            $message = 'Foto berhasil diunggah!';
            $message_type = 'success';
        } catch (PDOException $e) {
            // Hapus file yang sudah diupload jika gagal menyimpan ke database
            unlink($targetPath);
            if (file_exists($thumbnailPath)) {
                unlink($thumbnailPath);
            }
            $message = 'Gagal menyimpan data foto: ' . $e->getMessage();
            $message_type = 'danger';
        }
    } else {
        $message = 'Gagal mengunggah file.';
        $message_type = 'danger';
    }
}

// Handle delete foto
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    try {
        // Hapus file gambar terlebih dahulu jika ada
        $stmt = $pdo->prepare("SELECT file_path, thumbnail_path FROM galeri WHERE id = ?");
        $stmt->execute([$id]);
        $gambar = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($gambar) {
            if ($gambar['file_path'] && file_exists('../../' . $gambar['file_path'])) {
                unlink('../../' . $gambar['file_path']);
            }
            if ($gambar['thumbnail_path'] && file_exists('../../' . $gambar['thumbnail_path'])) {
                unlink('../../' . $gambar['thumbnail_path']);
            }
        }

        // Hapus dari database
        $stmt = $pdo->prepare("DELETE FROM galeri WHERE id = ?");
        $stmt->execute([$id]);

        $message = 'Foto berhasil dihapus!';
        $message_type = 'success';
    } catch (PDOException $e) {
        $message = 'Gagal menghapus foto: ' . $e->getMessage();
        $message_type = 'danger';
    }
}

// Ambil semua foto dari database
try {
    $stmt = $pdo->query("SELECT g.*, gk.nama_kategori 
                         FROM galeri g 
                         JOIN galeri_kategori gk ON g.kategori_id = gk.id 
                         WHERE g.tipe = 'foto' 
                         ORDER BY g.tanggal_upload DESC");
    $foto = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error mengambil data galeri: " . $e->getMessage());
}

// Ambil kategori galeri untuk dropdown
try {
    $stmt = $pdo->query("SELECT * FROM galeri_kategori ORDER BY nama_kategori");
    $kategori = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error mengambil kategori galeri: " . $e->getMessage());
}

// Fungsi untuk membuat thumbnail
function createThumbnail($sourcePath, $destPath, $width, $height) {
    $info = getimagesize($sourcePath);
    $sourceType = $info[2];
    
    switch ($sourceType) {
        case IMAGETYPE_JPEG:
            $source = imagecreatefromjpeg($sourcePath);
            break;
        case IMAGETYPE_PNG:
            $source = imagecreatefrompng($sourcePath);
            break;
        case IMAGETYPE_GIF:
            $source = imagecreatefromgif($sourcePath);
            break;
        default:
            return false;
    }
    
    $sourceWidth = imagesx($source);
    $sourceHeight = imagesy($source);
    
    // Hitung rasio
    $ratio = min($width / $sourceWidth, $height / $sourceHeight);
    $newWidth = (int)($sourceWidth * $ratio);
    $newHeight = (int)($sourceHeight * $ratio);
    
    $thumb = imagecreatetruecolor($newWidth, $newHeight);
    
    // Handle transparansi untuk PNG
    if ($sourceType == IMAGETYPE_PNG || $sourceType == IMAGETYPE_GIF) {
        imagecolortransparent($thumb, imagecolorallocatealpha($thumb, 0, 0, 0, 127));
        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);
    }
    
    imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $sourceWidth, $sourceHeight);
    
    switch ($sourceType) {
        case IMAGETYPE_JPEG:
            imagejpeg($thumb, $destPath, 90);
            break;
        case IMAGETYPE_PNG:
            imagepng($thumb, $destPath, 9);
            break;
        case IMAGETYPE_GIF:
            imagegif($thumb, $destPath);
            break;
    }
    
    imagedestroy($source);
    imagedestroy($thumb);
    
    return true;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Galeri Foto - <?php echo htmlspecialchars($desa_nama); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
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

        /* Gallery Styles */
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .gallery-card {
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            background: white;
            border-left: 4px solid var(--accent-color);
        }

        .gallery-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .gallery-img-container {
            height: 200px;
            overflow: hidden;
            position: relative;
        }

        .gallery-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .gallery-card:hover .gallery-img {
            transform: scale(1.05);
        }

        .gallery-info {
            padding: 1rem;
        }

        .gallery-title-photo {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--dark-color);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .gallery-date {
            font-size: 0.8rem;
            color: var(--gray-medium);
            margin-bottom: 0.5rem;
        }

        .gallery-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 0.5rem;
            border-top: 1px solid var(--gray-light);
        }

        .action-btn {
            width: 30px;
            height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin-right: 5px;
        }

        /* Upload Area */
        .upload-area {
            border: 2px dashed var(--gray-medium);
            border-radius: var(--border-radius);
            padding: 2rem;
            text-align: center;
            margin-bottom: 2rem;
            background: white;
            transition: var(--transition);
        }

        .upload-area:hover {
            border-color: var(--primary-color);
            background: var(--primary-light);
        }

        .upload-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        /* No Data State */
        .no-data-state {
            text-align: center;
            padding: 3rem 0;
            grid-column: 1 / -1;
        }

        .no-data-state img {
            max-width: 300px;
            margin-bottom: 1.5rem;
        }

        /* Responsive */
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
            .gallery-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
        }

        @media (max-width: 576px) {
            .gallery-grid {
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

        .sidebar-menu-container::-webkit-scrollbar {
            width: 4px;
        }

        .animate__animated {
            animation-duration: 0.5s;
        }

        .swal2-popup {
            border-radius: 12px;
        }

        .swal2-title {
            font-size: 1.5rem;
        }

        .swal2-html-container {
            font-size: 1.1rem;
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
                <span class="d-none d-md-inline">Kelola Galeri Foto</span>
            </div>
            <div class="user-menu">
                <div class="dropdown">
                    <button class="dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="../assets/images/logo.png" alt="User Avatar" class="user-avatar">
                        <span class="user-name d-none d-md-inline">Admin</span>
                        <i class="fas fa-chevron-down d-none d-md-inline"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i> Profil</a></li>
                        <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-2"></i> Pengaturan</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="../../logout.php"><i class="fas fa-sign-out-alt me-2"></i> Keluar</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Gallery Content -->
        <div class="content-section">
            <!-- Alert Message -->
            <?php if ($message): ?>
                <div class="alert alert-<?= htmlspecialchars($message_type) ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="section-header">
                <h1 class="section-title">Kelola Galeri Foto</h1>
                <a href="tambah_foto.php" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Tambah Foto
                </a>
            </div>

            <!-- Upload Area -->
          <!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadModalLabel">Unggah Foto Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="judul" class="form-label">Judul Foto</label>
                        <input type="text" class="form-control" id="judul" name="judul" required>
                    </div>
                    <div class="mb-3">
                        <label for="kategori_id" class="form-label">Kategori</label>
                        <select class="form-select" id="kategori_id" name="kategori_id" required>
                            <?php foreach ($kategori as $kat): ?>
                                <option value="<?= $kat['id'] ?>"><?= htmlspecialchars($kat['nama_kategori']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="tags" class="form-label">Tags (pisahkan dengan koma)</label>
                        <input type="text" class="form-control" id="tags" name="tags">
                    </div>
                    <div class="mb-3">
                        <label for="foto" class="form-label">File Foto</label>
                        <input type="file" class="form-control" id="foto" name="foto" accept="image/*" required>
                        <div class="form-text">Format: JPEG, PNG, GIF, WebP (Maks. 5MB)</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Unggah</button>
                </div>
            </form>
        </div>
    </div>
</div>

          <!-- Gallery Grid -->
<div class="gallery-grid">
    <?php if (count($foto) > 0): ?>
        <?php foreach ($foto as $item): ?>
            <div class="gallery-card">
                <div class="gallery-img-container">
                    <img src="<?= '../../' . htmlspecialchars($item['file_path']) ?>" 
                        alt="<?= htmlspecialchars($item['judul']) ?>" 
                        class="gallery-img">
                </div>
                <div class="gallery-info">
                    <h5 class="gallery-title-photo" title="<?= htmlspecialchars($item['judul']) ?>">
                        <?= htmlspecialchars($item['judul']) ?>
                    </h5>
                    <div class="gallery-category">
                        <span class="badge bg-primary"><?= htmlspecialchars($item['nama_kategori']) ?></span>
                    </div>
                    <div class="gallery-date">
                        <i class="far fa-calendar-alt me-2"></i>
                        <?= date('d M Y', strtotime($item['tanggal_upload'])) ?>
                    </div>
                    <div class="gallery-actions">
                        <div>
                            <?php if ($item['tags']): ?>
                                <?php foreach (explode(',', $item['tags']) as $tag): ?>
                                    <span class="badge bg-secondary me-1"><?= htmlspecialchars(trim($tag)) ?></span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <div>
                            <button class="action-btn btn btn-sm btn-outline-danger delete-btn"
                                title="Hapus"
                                data-id="<?= $item['id'] ?>"
                                data-filename="<?= htmlspecialchars($item['judul']) ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="no-data-state">
            <img src="../assets/images/no-data.svg" alt="No data" class="img-fluid mb-4">
            <h4 class="mb-3">Belum ada foto</h4>
            <p class="text-muted mb-4">Tambahkan foto pertama Anda untuk mengisi galeri</p>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                <i class="fas fa-plus me-2"></i>Tambah Foto
            </button>
        </div>
    <?php endif; ?>
</div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Toggle Sidebar
        $('.toggle-btn').on('click', function() {
            $('.sidebar').toggleClass('collapsed');
            $('.main-content').toggleClass('expanded');
        });

        // Submenu Toggle
        $('.has-submenu').click(function(e) {
            e.preventDefault();
            $(this).find('.menu-arrow').toggleClass('fa-chevron-down fa-chevron-up');
            $(this).siblings('.submenu').toggleClass('show');
        });

        // Auto close alert
        setTimeout(function() {
            $('.alert').alert('close');
        }, 5000);

        // Konfirmasi hapus dengan SweetAlert
        $('.delete-btn').click(function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            const filename = $(this).data('filename');

            Swal.fire({
                title: 'Konfirmasi Hapus',
                html: `Anda akan menghapus foto: <strong>${filename}</strong><br><br>Foto yang dihapus tidak dapat dikembalikan.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                backdrop: `
                    rgba(0,0,0,0.7)
                    url("../../assets/images/trash-icon.png")
                    center top
                    no-repeat
                `,
                showClass: {
                    popup: 'animate__animated animate__fadeInDown'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutUp'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `?delete=${id}`;
                }
            });
        });

        // Enhanced Drag and Drop functionality
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.createElement('input');
        fileInput.type = 'file';
        fileInput.name = 'foto';
        fileInput.accept = 'image/*';
        fileInput.multiple = false;
        fileInput.style.display = 'none';

        document.getElementById('selectFileBtn').addEventListener('click', () => fileInput.click());

        // Add event listeners for drag and drop
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            uploadArea.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, unhighlight, false);
        });

        function highlight() {
            uploadArea.classList.add('bg-primary-light');
            uploadArea.style.borderColor = 'var(--primary-color)';
            uploadArea.querySelector('h5').textContent = 'Lepaskan file untuk mengunggah';
        }

        function unhighlight() {
            uploadArea.classList.remove('bg-primary-light');
            uploadArea.style.borderColor = 'var(--gray-medium)';
            uploadArea.querySelector('h5').textContent = 'Seret dan lepas file foto di sini';
        }

        uploadArea.addEventListener('drop', handleDrop, false);
        fileInput.addEventListener('change', handleFiles);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            handleFiles({
                target: {
                    files
                }
            });
        }

        function handleFiles(e) {
            const files = e.target.files || (e.dataTransfer && e.dataTransfer.files);

            if (!files || files.length === 0) return;

            // Handle only the first file (for single upload)
            const file = files[0];

            // Validate file type
            const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                alert('Format file tidak didukung. Hanya JPEG, PNG, GIF, dan WebP yang diperbolehkan.');
                return;
            }

            // Validate file size (max 5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('Ukuran file terlalu besar. Maksimal 5MB.');
                return;
            }

            // Create a FormData object and submit via AJAX
            const formData = new FormData();
            formData.append('foto', file);
            formData.append('judul', file.name.split('.')[0]);
            formData.append('kategori', 'umum');

            // Show loading state
            uploadArea.innerHTML = `
                    <div class="upload-icon">
                        <i class="fas fa-spinner fa-spin"></i>
                    </div>
                    <h5>Mengunggah ${file.name}</h5>
                    <div class="progress mt-3">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                    </div>
                `;

            // AJAX upload
            const xhr = new XMLHttpRequest();

            xhr.upload.addEventListener('progress', (e) => {
                if (e.lengthComputable) {
                    const percentComplete = (e.loaded / e.total) * 100;
                    document.querySelector('.progress-bar').style.width = `${percentComplete}%`;
                }
            }, false);

            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        // Reload the page to show the new photo
                        window.location.reload();
                    } else {
                        uploadArea.innerHTML = `
                                <div class="upload-icon text-danger">
                                    <i class="fas fa-exclamation-circle"></i>
                                </div>
                                <h5>Gagal mengunggah</h5>
                                <p class="text-muted">${xhr.responseText || 'Terjadi kesalahan'}</p>
                                <button class="btn btn-outline-primary" onclick="window.location.reload()">
                                    Coba Lagi
                                </button>
                            `;
                    }
                }
            };

            xhr.open('POST', 'upload_foto.php', true);
            xhr.send(formData);
        }

        $('.delete-btn').on('click', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        const filename = $(this).data('filename');

        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Anda akan menghapus foto ini secara permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `?delete=${id}`;
            }
        });
        });

        // Alternatif jika tidak menggunakan SweetAlert (menggunakan confirm bawaan browser)
        /*
        $('.delete-btn').on('click', function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            const filename = $(this).data('filename');
            
            const confirmDelete = confirm(`Apakah Anda yakin ingin menghapus foto ${filename}?`);
            if (confirmDelete) {
                window.location.href = `?delete=${id}`;
            }
        });
        */
        ;
    </script>
</body>

</html>