<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';

// Data desa
$desa_nama = "Desa Winduaji";
$desa_lokasi = "Kecamatan Paninggaran, Kabupaten Pekalongan, Provinsi Jawa Tengah";
$desa_motto = "Bersama Membangun Desa yang Mandiri dan Berbudaya";

try {
    // Ambil data berita dari database dengan JOIN yang benar
    $query = "SELECT b.*, k.nama_kategori 
              FROM berita b 
              JOIN berita_kategori k ON b.kategori_id = k.id 
              ORDER BY b.tanggal_publish DESC";
    
    // Eksekusi query dengan penanganan error
    $stmt = $db->prepare($query);
    $stmt->execute();
    $berita = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($berita === false) {
        throw new PDOException("Gagal mengambil data berita");
    }
    
} catch (PDOException $e) {
    // Log error dan tampilkan pesan yang ramah pengguna
    error_log("Database error: " . $e->getMessage());
    die("Terjadi kesalahan saat mengambil data berita. Silakan coba lagi nanti.");
}

// Fungsi untuk menambahkan berita dari URL
function add_news_from_url($url, $kategori_id, $db) {
    // Validasi URL
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return ['success' => false, 'message' => 'URL tidak valid'];
    }

    // Dapatkan konten dari URL (simplified - dalam produksi gunakan library seperti Goutte)
    $html = @file_get_contents($url);
    if (!$html) {
        return ['success' => false, 'message' => 'Gagal mengambil konten dari URL'];
    }

    // Parsing sederhana (dalam produksi gunakan DOMDocument atau library parsing HTML)
    preg_match('/<title>(.*?)<\/title>/i', $html, $titleMatches);
    $judul = $titleMatches[1] ?? 'Berita dari ' . parse_url($url, PHP_URL_HOST);
    
    // Ambil konten utama (simplified)
    preg_match('/<body.*?>(.*?)<\/body>/si', $html, $bodyMatches);
    $isi = strip_tags($bodyMatches[1] ?? '');
    $isi = substr($isi, 0, 5000); // Batasi konten

    // Generate slug
    $slug = create_slug($judul);

    // Cek duplikat
    $existing = $db->prepare("SELECT id FROM berita WHERE slug = ?");
    $existing->execute([$slug]);
    
    if ($existing->fetch()) {
        return ['success' => false, 'message' => 'Berita dengan judul serupa sudah ada'];
    }

    // Simpan ke database
    try {
        $stmt = $db->prepare("INSERT INTO berita (kategori_id, judul, slug, isi, penulis, tanggal_publish, status, sumber) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $kategori_id,
            $judul,
            $slug,
            $isi,
            'Sumber Eksternal',
            date('Y-m-d'),
            'published',
            $url
        ]);
        
        return ['success' => true, 'message' => 'Berita berhasil diimpor dari URL'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Error database: ' . $e->getMessage()];
    }
}

// Proses tambah berita
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_berita'])) {
    $judul = clean_input($_POST['judul']);
    $slug = create_slug($judul);
    $kategori_id = (int)$_POST['kategori_id'];
    $isi = clean_input($_POST['isi']);
    $penulis = clean_input($_POST['penulis']);
    $tanggal_publish = $_POST['tanggal_publish'];
    $status = $_POST['status'];
    $meta_title = clean_input($_POST['meta_title']);
    $meta_description = clean_input($_POST['meta_description']);
    $meta_keywords = clean_input($_POST['meta_keywords']);
    $sumber = clean_input($_POST['sumber']);

    // Upload gambar
    $gambar_path = null;
    if ($_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $gambar_path = upload_image($_FILES['gambar'], 'berita');
    }

    $stmt = $db->prepare("INSERT INTO berita (kategori_id, judul, slug, isi, penulis, tanggal_publish, status, gambar_path, meta_title, meta_description, meta_keywords, sumber) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$kategori_id, $judul, $slug, $isi, $penulis, $tanggal_publish, $status, $gambar_path, $meta_title, $meta_description, $meta_keywords, $sumber]);

    $_SESSION['success_message'] = 'Berita berhasil ditambahkan';
    header('Location: berita.php');
    exit;
}

// Proses tambah berita dari URL
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_berita_url'])) {
    $url = clean_input($_POST['url']);
    $kategori_id = (int)$_POST['url_kategori_id'];
    
    $result = add_news_from_url($url, $kategori_id, $db);
    
    $_SESSION[$result['success'] ? 'success_message' : 'error_message'] = $result['message'];
    header('Location: berita.php');
    exit;
}

// Proses edit berita
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_berita'])) {
    $id = (int)$_POST['id'];
    $judul = clean_input($_POST['judul']);
    $slug = create_slug($judul);
    $kategori_id = (int)$_POST['kategori_id'];
    $isi = $_POST['isi'];
    $penulis = clean_input($_POST['penulis']);
    $tanggal_publish = $_POST['tanggal_publish'];
    $status = $_POST['status'];
    $meta_title = clean_input($_POST['meta_title']);
    $meta_description = clean_input($_POST['meta_description']);
    $meta_keywords = clean_input($_POST['meta_keywords']);
    $sumber = clean_input($_POST['sumber']);

    // Cek apakah ada gambar baru diupload
    $gambar_path = $_POST['gambar_lama'];
    if ($_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        try {
            if ($gambar_path && file_exists($gambar_path)) {
                unlink($gambar_path);
            }
            $gambar_path = upload_image($_FILES['gambar'], 'berita');
        } catch (Exception $e) {
            $_SESSION['error_message'] = $e->getMessage();
            header('Location: berita.php?edit=' . $id);
            exit;
        }
    }

    $stmt = $db->prepare("UPDATE berita SET 
                          kategori_id = ?, judul = ?, slug = ?, isi = ?, penulis = ?, 
                          tanggal_publish = ?, status = ?, gambar_path = ?, meta_title = ?, 
                          meta_description = ?, meta_keywords = ?, sumber = ?, updated_at = NOW() 
                          WHERE id = ?");
    $stmt->execute([$kategori_id, $judul, $slug, $isi, $penulis, $tanggal_publish, $status, $gambar_path, $meta_title, $meta_description, $meta_keywords, $sumber, $id]);

    $_SESSION['success_message'] = 'Berita berhasil diperbarui';
    header('Location: berita.php');
    exit;
}

// Proses hapus berita
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];

    // Ambil data berita untuk menghapus gambar
    $berita = $db->query("SELECT gambar_path FROM berita WHERE id = $id")->fetch(PDO::FETCH_ASSOC);

    if ($berita['gambar_path'] && file_exists($berita['gambar_path'])) {
        unlink($berita['gambar_path']);
    }

    $db->query("DELETE FROM berita WHERE id = $id");
    $_SESSION['success_message'] = 'Berita berhasil dihapus';
    header('Location: berita.php');
    exit;
}

// Proses impor berita dari Google News
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import_news'])) {
    $query = urlencode(clean_input($_POST['news_query']));
    $kategori_id = (int)$_POST['import_kategori_id'];
    
    $rss_url = "https://news.google.com/rss/search?q={$query}&hl=id&gl=ID&ceid=ID:id";
    $news = fetch_google_news($rss_url);
    
    if ($news) {
        $imported_count = 0;
        
        foreach ($news as $item) {
            $existing = $db->prepare("SELECT id FROM berita WHERE judul = ?");
            $existing->execute([$item['title']]);
            
            if (!$existing->fetch()) {
                $stmt = $db->prepare("INSERT INTO berita (kategori_id, judul, slug, isi, penulis, tanggal_publish, status, meta_title, meta_description, sumber) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $kategori_id,
                    $item['title'],
                    create_slug($item['title']),
                    $item['description'] . '<p><a href="' . $item['link'] . '" target="_blank">Baca selengkapnya</a></p>',
                    'Google News',
                    date('Y-m-d', strtotime($item['pubDate'])),
                    'published',
                    $item['title'],
                    substr($item['description'], 0, 160),
                    $item['link']
                ]);
                $imported_count++;
            }
        }
        
        $_SESSION['success_message'] = "Berhasil mengimpor {$imported_count} berita dari Google News";
    } else {
        $_SESSION['error_message'] = "Gagal mengambil berita dari Google News";
    }
    
    header('Location: berita.php');
    exit;
}

// Ambil data berita untuk edit
$edit_berita = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $edit_berita = $db->query("SELECT * FROM berita WHERE id = $id")->fetch(PDO::FETCH_ASSOC);
}

// Fungsi untuk mengambil berita dari Google News RSS
function fetch_google_news($rss_url) {
    $news = [];
    
    try {
        $xml = simplexml_load_file($rss_url);
        if ($xml) {
            foreach ($xml->channel->item as $item) {
                $news[] = [
                    'title' => (string)$item->title,
                    'link' => (string)$item->link,
                    'description' => (string)$item->description,
                    'pubDate' => (string)$item->pubDate
                ];
            }
        }
    } catch (Exception $e) {
        error_log("Error fetching Google News: " . $e->getMessage());
        return false;
    }
    
    return $news;
}

// Fungsi untuk membersihkan input
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Fungsi untuk membuat slug dari judul
function create_slug($string) {
    $slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $string);
    $slug = strtolower($slug);
    return $slug;
}

// Fungsi untuk upload gambar
function upload_image($file, $folder) {
    $target_dir = "assets/images/$folder/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $imageFileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = uniqid() . '.' . $imageFileType;
    $target_file = $target_dir . $filename;

    $check = getimagesize($file['tmp_name']);
    if ($check === false) {
        throw new Exception("File bukan gambar");
    }

    if ($file['size'] > 2000000) {
        throw new Exception("Ukuran gambar terlalu besar (maks 2MB)");
    }

    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($imageFileType, $allowed)) {
        throw new Exception("Hanya format JPG, JPEG, PNG & GIF yang diperbolehkan");
    }

    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return $target_file;
    } else {
        throw new Exception("Gagal mengupload gambar");
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Berita - <?php echo $desa_nama; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" rel="stylesheet">
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

        /* Card Styles */
        .card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            margin-bottom: 1.5rem;
        }

        .card-header {
            background-color: white;
            border-bottom: 1px solid var(--gray-light);
            font-weight: 600;
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
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

        /* Badges */
        .badge {
            padding: 0.5em 0.75em;
            font-weight: 600;
            border-radius: 4px;
            font-size: 0.75rem;
        }

        .badge-published {
            background-color: rgba(76, 201, 240, 0.1);
            color: var(--success-color);
            border: 1px solid var(--success-color);
        }

        .badge-draft {
            background-color: rgba(248, 150, 30, 0.1);
            color: var(--warning-color);
            border: 1px solid var(--warning-color);
        }

        .badge-category {
            background-color: var(--primary-light);
            color: var(--primary-color);
        }

        /* Buttons */
        .btn-primary-custom {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        .btn-primary-custom:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .btn-import {
            background-color: #34a853;
            border-color: #34a853;
            color: white;
        }

        .btn-import:hover {
            background-color: #2d8e47;
            border-color: #2d8e47;
        }

        /* Source Badge */
        .source-badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
            background-color: var(--gray-light);
            border-radius: 4px;
            display: inline-block;
            margin-top: 0.5rem;
            color: var(--gray-dark);
        }

        .source-badge a {
            color: var(--primary-color);
            text-decoration: none;
        }

        /* Preview Image */
        .preview-image {
            max-height: 200px;
            object-fit: contain;
            border-radius: var(--border-radius);
            margin-bottom: 1rem;
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

        @media (max-width: 576px) {
            .content-section {
                padding: 1.5rem 1rem;
            }
            
            .section-header {
                flex-direction: column;
                align-items: flex-start;
            }
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
                <h1 class="section-title">Kelola Berita</h1>
                <div class="d-flex gap-2">
                    <button class="btn btn-import" data-bs-toggle="modal" data-bs-target="#importNewsModal">
                        <i class="fas fa-cloud-download-alt"></i> Impor Berita
                    </button>
                    <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#tambahBeritaModal">
                        <i class="fas fa-plus"></i> Tambah Berita
                    </button>
                </div>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['success_message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['error_message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <div>
                        <i class="fas fa-list"></i> Daftar Berita
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="form-check form-switch me-3">
                            <input class="form-check-input" type="checkbox" id="showSourceToggle" checked>
                            <label class="form-check-label" for="showSourceToggle">Tampilkan Sumber</label>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="beritaTable" class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Judul</th>
                                    <th>Kategori</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($berita as $index => $item): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td>
                                            <?php echo $item['judul']; ?>
                                            <?php if ($item['sumber']): ?>
                                                <div class="source-badge source-info">
                                                    Sumber: 
                                                    <?php if (filter_var($item['sumber'], FILTER_VALIDATE_URL)): ?>
                                                        <a href="<?php echo $item['sumber']; ?>" target="_blank">Link Eksternal</a>
                                                    <?php else: ?>
                                                        <?php echo $item['sumber']; ?>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="badge badge-category"><?php echo $item['nama_kategori']; ?></span></td>
                                        <td><?php echo date('d M Y', strtotime($item['tanggal_publish'])); ?></td>
                                        <td>
                                            <span class="badge <?php echo $item['status'] == 'published' ? 'badge-published' : 'badge-draft'; ?>">
                                                <?php echo ucfirst($item['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="?edit=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editBeritaModal">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?hapus=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus berita ini?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

   
<div class="modal fade" id="tambahBeritaUrlModal" tabindex="-1" aria-labelledby="tambahBeritaUrlModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="tambahBeritaUrlModalLabel">Tambah Berita dari URL</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="url" class="form-label">URL Berita</label>
                        <input type="url" class="form-control" id="url" name="url" required placeholder="https://example.com/berita-terbaru">
                    </div>
                    <div class="mb-3">
                        <label for="url_kategori_id" class="form-label">Kategori</label>
                        <select class="form-select" id="url_kategori_id" name="url_kategori_id" required>
                            <option value="">Pilih Kategori</option>
                            <?php foreach ($kategori as $kat): ?>
                                <option value="<?php echo $kat['id']; ?>"><?php echo $kat['nama_kategori']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Sistem akan mencoba mengambil konten berita dari URL yang dimasukkan.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary-custom" name="tambah_berita_url">
                        <i class="fas fa-link"></i> Tambah Berita
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

    <!-- Modal Edit Berita -->
    <?php if ($edit_berita): ?>
        <div class="modal fade" id="editBeritaModal" tabindex="-1" aria-labelledby="editBeritaModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?php echo $edit_berita['id']; ?>">
                        <input type="hidden" name="gambar_lama" value="<?php echo $edit_berita['gambar_path']; ?>">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editBeritaModalLabel">Edit Berita</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="edit_judul" class="form-label">Judul Berita</label>
                                <input type="text" class="form-control" id="edit_judul" name="judul" value="<?php echo $edit_berita['judul']; ?>" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="edit_kategori_id" class="form-label">Kategori</label>
                                    <select class="form-select" id="edit_kategori_id" name="kategori_id" required>
                                        <?php foreach ($kategori as $kat): ?>
                                            <option value="<?php echo $kat['id']; ?>" <?php echo $kat['id'] == $edit_berita['kategori_id'] ? 'selected' : ''; ?>>
                                                <?php echo $kat['nama_kategori']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_penulis" class="form-label">Penulis</label>
                                    <input type="text" class="form-control" id="edit_penulis" name="penulis" value="<?php echo $edit_berita['penulis'] ?: 'Admin'; ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="edit_tanggal_publish" class="form-label">Tanggal Publikasi</label>
                                    <input type="date" class="form-control" id="edit_tanggal_publish" name="tanggal_publish" value="<?php echo $edit_berita['tanggal_publish']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_status" class="form-label">Status</label>
                                    <select class="form-select" id="edit_status" name="status" required>
                                        <option value="published" <?php echo $edit_berita['status'] == 'published' ? 'selected' : ''; ?>>Published</option>
                                        <option value="draft" <?php echo $edit_berita['status'] == 'draft' ? 'selected' : ''; ?>>Draft</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="edit_sumber" class="form-label">Sumber Berita (Opsional)</label>
                                <input type="text" class="form-control" id="edit_sumber" name="sumber" value="<?php echo $edit_berita['sumber']; ?>" placeholder="Contoh: https://example.com/berita">
                                <small class="text-muted">Isi dengan URL jika berita dari sumber eksternal</small>
                            </div>
                            <div class="mb-3">
                                <label for="edit_gambar" class="form-label">Gambar Utama</label>
                                <?php if ($edit_berita['gambar_path']): ?>
                                    <div class="mb-2">
                                        <img src="<?php echo $edit_berita['gambar_path']; ?>" class="img-thumbnail preview-image" id="edit_preview_gambar">
                                    </div>
                                <?php endif; ?>
                                <input type="file" class="form-control" id="edit_gambar" name="gambar" accept="image/*">
                                <small class="text-muted">Biarkan kosong jika tidak ingin mengubah gambar</small>
                            </div>
                            <div class="mb-3">
                                <label for="edit_isi" class="form-label">Isi Berita</label>
                                <textarea class="form-control" id="edit_isi" name="isi" rows="8" required><?php echo $edit_berita['isi']; ?></textarea>
                            </div>
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <i class="fas fa-search"></i> Pengaturan SEO
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="edit_meta_title" class="form-label">Meta Title</label>
                                        <input type="text" class="form-control" id="edit_meta_title" name="meta_title" value="<?php echo $edit_berita['meta_title']; ?>" maxlength="60">
                                        <small class="text-muted">Maksimal 60 karakter</small>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_meta_description" class="form-label">Meta Description</label>
                                        <textarea class="form-control" id="edit_meta_description" name="meta_description" rows="3" maxlength="160"><?php echo $edit_berita['meta_description']; ?></textarea>
                                        <small class="text-muted">Maksimal 160 karakter</small>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_meta_keywords" class="form-label">Meta Keywords</label>
                                        <input type="text" class="form-control" id="edit_meta_keywords" name="meta_keywords" value="<?php echo $edit_berita['meta_keywords']; ?>">
                                        <small class="text-muted">Pisahkan dengan koma (contoh: berita, desa, winduaji)</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary-custom" name="edit_berita">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Modal Impor Berita -->
    <div class="modal fade" id="importNewsModal" tabindex="-1" aria-labelledby="importNewsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="importNewsModalLabel">Impor Berita dari Google News</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="news_query" class="form-label">Kata Kunci Pencarian</label>
                            <input type="text" class="form-control" id="news_query" name="news_query" required placeholder="Contoh: berita desa, kabupaten pekalongan">
                            <small class="text-muted">Masukkan kata kunci untuk mencari berita terkait</small>
                        </div>
                        <div class="mb-3">
                            <label for="import_kategori_id" class="form-label">Kategori Berita</label>
                            <select class="form-select" id="import_kategori_id" name="import_kategori_id" required>
                                <option value="">Pilih Kategori</option>
                                <?php foreach ($kategori as $kat): ?>
                                    <option value="<?php echo $kat['id']; ?>"><?php echo $kat['nama_kategori']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Sistem akan mengimpor berita dari Google News berdasarkan kata kunci yang dimasukkan.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-import" name="import_news">
                            <i class="fas fa-cloud-download-alt"></i> Impor Berita
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>

    <script>
        // Inisialisasi DataTable
        $(document).ready(function() {
            $('#beritaTable').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
                },
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-success btn-sm'
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-danger btn-sm'
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> Print',
                        className: 'btn btn-info btn-sm'
                    }
                ],
                responsive: true
            });

            // Inisialisasi CKEditor
            CKEDITOR.replace('isi');
            CKEDITOR.replace('edit_isi');

            // Preview gambar saat edit
            $('#edit_gambar').change(function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#edit_preview_gambar').attr('src', e.target.result);
                    }
                    reader.readAsDataURL(file);
                }
            });

            // Toggle sidebar
            $('.toggle-btn').click(function() {
                $('.sidebar').toggleClass('collapsed');
                $('.main-content').toggleClass('expanded');
            });

            // Toggle show/hide source info
            $('#showSourceToggle').change(function() {
                if ($(this).is(':checked')) {
                    $('.source-info').show();
                } else {
                    $('.source-info').hide();
                }
            });

            // Auto show edit modal if edit parameter exists
            <?php if ($edit_berita): ?>
                $(window).on('load', function() {
                    $('#editBeritaModal').modal('show');
                });
            <?php endif; ?>
        });
    </script>
</body>

</html>