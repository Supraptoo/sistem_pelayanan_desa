<?php
session_start();

// // Redirect jika belum login
// if (!isset($_SESSION['admin_logged_in'])) {
//     header('Location: login.php');
//     exit;
// }

require_once '../config/database.php';
require_once '../config/functions.php';

// Koneksi database
$db = new database();
$conn = $db->getConnection();

// Inisialisasi variabel
$success = '';
$error = '';
$galeri_data = [];
$kategori_galeri = [];

// Ambil data kategori Galeri
try {
    $query = "SELECT * FROM galeri_kategori ORDER BY nama_kategori";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $kategori_galeri = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = "Gagal memuat kategori Galeri: " . $e->getMessage();
}

// Ambil data Galeri
try {
    $query = "SELECT g.*, gk.nama_kategori 
              FROM galeri g 
              LEFT JOIN galeri_kategori gk ON g.kategori_id = gk.id 
              ORDER BY g.created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $galeri_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = "Gagal memuat data Galeri: " . $e->getMessage();
}

// Proses tambah Galeri
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_galeri'])) {
    $judul = $_POST['judul'] ?? '';
    $kategori_id = $_POST['kategori_id'] ?? '';
    $deskripsi = $_POST['deskripsi'] ?? '';
    $status = $_POST['status'] ?? 'draft';
    
    // Upload gambar
    $file_path = '';
    if (!empty($_FILES['gambar']['name'])) {
        $upload_dir = '../uploads/galeri/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = time() . '_' . basename($_FILES['gambar']['name']);
        $target_file = $upload_dir . $file_name;
        
        // Validasi file
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        // Cek ukuran file (maks 5MB)
        if ($_FILES['gambar']['size'] > 5 * 1024 * 1024) {
            $error = "Ukuran file terlalu besar. Maksimal 5MB.";
        }
        elseif (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
                $file_path = 'uploads/galeri/' . $file_name;
                
                // Buat thumbnail jika diperlukan
                createThumbnail($target_file, $upload_dir . 'thumb_' . $file_name, 300, 200);
            } else {
                $error = "Gagal mengupload gambar.";
            }
        } else {
            $error = "Hanya file JPG, JPEG, PNG, GIF & WEBP yang diperbolehkan.";
        }
    } else {
        $error = "Harap pilih gambar untuk diupload.";
    }
    
    if (empty($error)) {
        try {
            $query = "INSERT INTO galeri (judul, kategori_id, deskripsi, file_path, status) 
                      VALUES (:judul, :kategori_id, :deskripsi, :file_path, :status)";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':judul', $judul);
            $stmt->bindParam(':kategori_id', $kategori_id);
            $stmt->bindParam(':deskripsi', $deskripsi);
            $stmt->bindParam(':file_path', $file_path);
            $stmt->bindParam(':status', $status);
            
            if ($stmt->execute()) {
                $success = "Gambar berhasil ditambahkan!";
                
                // Refresh data Galeri
                $query = "SELECT g.*, gk.nama_kategori 
                          FROM galeri g 
                          LEFT JOIN galeri_kategori gk ON g.kategori_id = gk.id 
                          ORDER BY g.created_at DESC";
                $stmt = $conn->prepare($query);
                $stmt->execute();
                $galeri_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $error = "Gagal menambahkan gambar.";
            }
        } catch (Exception $e) {
            $error = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}

// Proses update status Galeri
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = $_POST['id'] ?? '';
    $status = $_POST['status'] ?? '';
    
    try {
        $query = "UPDATE galeri SET status = :status WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            $success = "Status gambar berhasil diupdate!";
            
            // Refresh data Galeri
            $query = "SELECT g.*, gk.nama_kategori 
                      FROM galeri g 
                      LEFT JOIN galeri_kategori gk ON g.kategori_id = gk.id 
                      ORDER BY g.created_at DESC";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $galeri_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) {
        $error = "Gagal mengupdate status: " . $e->getMessage();
    }
}

// Proses hapus Galeri
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus_galeri'])) {
    $id = $_POST['id'] ?? '';
    
    try {
        // Hapus gambar terkait jika ada
        $query = "SELECT file_path FROM galeri WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $galeri = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($galeri && !empty($galeri['file_path'])) {
            $file_path = '../' . $galeri['file_path'];
            $thumb_path = '../' . dirname($galeri['file_path']) . '/thumb_' . basename($galeri['file_path']);
            
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            if (file_exists($thumb_path)) {
                unlink($thumb_path);
            }
        }
        
        // Hapus galeri dari database
        $query = "DELETE FROM galeri WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            $success = "Gambar berhasil dihapus!";
            
            // Refresh data Galeri
            $query = "SELECT g.*, gk.nama_kategori 
                      FROM galeri g 
                      LEFT JOIN galeri_kategori gk ON g.kategori_id = gk.id 
                      ORDER BY g.created_at DESC";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $galeri_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) {
        $error = "Gagal menghapus gambar: " . $e->getMessage();
    }
}

// Fungsi untuk membuat thumbnail
function createThumbnail($source_path, $dest_path, $width, $height) {
    $image_info = getimagesize($source_path);
    $source_type = $image_info[2];
    
    switch ($source_type) {
        case IMAGETYPE_JPEG:
            $source_image = imagecreatefromjpeg($source_path);
            break;
        case IMAGETYPE_PNG:
            $source_image = imagecreatefrompng($source_path);
            break;
        case IMAGETYPE_GIF:
            $source_image = imagecreatefromgif($source_path);
            break;
        case IMAGETYPE_WEBP:
            $source_image = imagecreatefromwebp($source_path);
            break;
        default:
            return false;
    }
    
    $source_width = imagesx($source_image);
    $source_height = imagesy($source_image);
    
    // Hitung rasio untuk resize
    $source_ratio = $source_width / $source_height;
    $thumb_ratio = $width / $height;
    
    if ($source_ratio > $thumb_ratio) {
        // Gambar lebih lebar
        $new_height = $height;
        $new_width = (int)($height * $source_ratio);
    } else {
        // Gambar lebih tinggi
        $new_width = $width;
        $new_height = (int)($width / $source_ratio);
    }
    
    $thumb_image = imagecreatetruecolor($width, $height);
    $background = imagecolorallocate($thumb_image, 255, 255, 255);
    imagefill($thumb_image, 0, 0, $background);
    
    // Resize dan center image
    $x_offset = (int)(($width - $new_width) / 2);
    $y_offset = (int)(($height - $new_height) / 2);
    
    imagecopyresampled($thumb_image, $source_image, $x_offset, $y_offset, 0, 0, $new_width, $new_height, $source_width, $source_height);
    
    // Simpan thumbnail
    switch ($source_type) {
        case IMAGETYPE_JPEG:
            imagejpeg($thumb_image, $dest_path, 85);
            break;
        case IMAGETYPE_PNG:
            imagepng($thumb_image, $dest_path, 8);
            break;
        case IMAGETYPE_GIF:
            imagegif($thumb_image, $dest_path);
            break;
        case IMAGETYPE_WEBP:
            imagewebp($thumb_image, $dest_path, 85);
            break;
    }
    
    imagedestroy($source_image);
    imagedestroy($thumb_image);
    
    return true;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <title>Manajemen Galeri - Admin Desa Winduaji</title>
      <link rel="shortcut icon" href="../assets/images/logo.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&amp;display=swap"
        rel="stylesheet" />
    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"
        rel="stylesheet" />
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
            <a href="dashboard.php" class="flex items-center px-4 py-3 text-red-200">
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
            <a href="galeri.php" class="flex items-center px-4 py-3 text-white active">
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
        <main class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Manajemen Galeri</h1>
                <a href="../index.php#galeri" target="_blank" class="text-sm text-red-600 hover:text-red-800 inline-flex items-center">
                    <i class="fas fa-external-link-alt mr-1"></i> Lihat di Website
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
            
            <!-- Form Tambah Galeri -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold mb-4 text-red-700">Tambah Gambar Baru</h2>
                
                <form method="POST" action="" enctype="multipart/form-data" id="formGaleri">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <div>
                            <label for="judul" class="block text-gray-700 text-sm font-medium mb-2">Judul Gambar *</label>
                            <input 
                                type="text" 
                                id="judul" 
                                name="judul" 
                                required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent"
                                placeholder="Masukkan judul gambar">
                        </div>
                        
                        <div>
                            <label for="kategori_id" class="block text-gray-700 text-sm font-medium mb-2">Kategori</label>
                            <select 
                                id="kategori_id" 
                                name="kategori_id" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent">
                                <option value="">Pilih Kategori</option>
                                <?php foreach ($kategori_galeri as $kategori): ?>
                                    <option value="<?php echo $kategori['id']; ?>"><?php echo htmlspecialchars($kategori['nama_kategori']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="deskripsi" class="block text-gray-700 text-sm font-medium mb-2">Deskripsi</label>
                        <textarea 
                            id="deskripsi" 
                            name="deskripsi" 
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent"
                            placeholder="Deskripsi gambar (opsional)"></textarea>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <div>
                            <label for="gambar" class="block text-gray-700 text-sm font-medium mb-2">Gambar *</label>
                            <input 
                                type="file" 
                                id="gambar" 
                                name="gambar" 
                                accept="image/*"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent"
                                onchange="previewImage(this)">
                            <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG, GIF, WEBP (Maks. 5MB)</p>
                            <img id="imagePreview" class="preview-image" alt="Preview Gambar">
                        </div>
                        
                        <div>
                            <label for="status" class="block text-gray-700 text-sm font-medium mb-2">Status *</label>
                            <select 
                                id="status" 
                                name="status" 
                                required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent">
                                <option value="draft">Draft</option>
                                <option value="published" selected>Published</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="flex justify-end">
                        <button 
                            type="submit" 
                            name="tambah_galeri"
                            class="bg-red-700 hover:bg-red-800 text-white font-medium py-2 px-6 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-600 transition-colors duration-200">
                            <i class="fas fa-plus mr-2"></i>Tambah Gambar
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Daftar Galeri -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4 text-red-700">Daftar Galeri</h2>
                
                <?php if (empty($galeri_data)): ?>
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-images fa-3x mb-4"></i>
                        <p>Belum ada gambar di galeri.</p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($galeri_data as $galeri): ?>
                            <div class="bg-gray-50 rounded-lg overflow-hidden shadow card-hover">
                                <div class="h-48 bg-gray-200 overflow-hidden">
                                    <?php if (!empty($galeri['file_path'])): ?>
                                        <img src="../<?php echo $galeri['file_path']; ?>" alt="<?php echo htmlspecialchars($galeri['judul']); ?>" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <div class="w-full h-full flex items-center justify-center bg-gray-100">
                                            <i class="fas fa-image text-gray-400 text-4xl"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-xs font-semibold px-2 py-1 rounded 
                                            <?php echo $galeri['status'] === 'published' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                            <?php echo $galeri['status'] === 'published' ? 'Published' : 'Draft'; ?>
                                        </span>
                                        <?php if (!empty($galeri['nama_kategori'])): ?>
                                            <span class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded">
                                                <?php echo htmlspecialchars($galeri['nama_kategori']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <h3 class="font-bold text-lg mb-2"><?php echo htmlspecialchars($galeri['judul']); ?></h3>
                                    
                                    <?php if (!empty($galeri['deskripsi'])): ?>
                                        <p class="text-sm text-gray-700 mb-3 truncate-2">
                                            <?php echo htmlspecialchars($galeri['deskripsi']); ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <p class="text-xs text-gray-500 mb-3">
                                        <?php echo date('d M Y', strtotime($galeri['created_at'])); ?>
                                    </p>
                                    
                                    <div class="flex justify-between items-center">
                                        <div class="flex space-x-2">
                                            <form method="POST" action="" class="inline">
                                                <input type="hidden" name="id" value="<?php echo $galeri['id']; ?>">
                                                <select name="status" onchange="this.form.submit()" class="text-xs px-2 py-1 rounded <?php echo $galeri['status'] === 'published' ? 'status-published' : 'status-draft'; ?>">
                                                    <option value="draft" <?php echo $galeri['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                                    <option value="published" <?php echo $galeri['status'] === 'published' ? 'selected' : ''; ?>>Published</option>
                                                </select>
                                                <input type="hidden" name="update_status" value="1">
                                            </form>
                                            
                                            <a href="edit-galeri.php?id=<?php echo $galeri['id']; ?>" class="text-blue-600 hover:text-blue-800 text-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                        
                                        <form method="POST" action="" class="inline">
                                            <input type="hidden" name="id" value="<?php echo $galeri['id']; ?>">
                                            <button type="submit" name="hapus_galeri" onclick="return confirm('Apakah Anda yakin ingin menghapus gambar ini?')" class="text-red-600 hover:text-red-800 text-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        // Toggle sidebar on mobile
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        // Preview image sebelum upload
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            const file = input.files[0];
            
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                
                reader.readAsDataURL(file);
                
                // Validasi ukuran file
                if (file.size > 5 * 1024 * 1024) {
                    alert('Ukuran file terlalu besar. Maksimal 5MB.');
                    input.value = '';
                    preview.style.display = 'none';
                }
            } else {
                preview.style.display = 'none';
            }
        }

        // Validasi form sebelum submit
        document.getElementById('formGaleri').addEventListener('submit', function(e) {
            const fileInput = document.getElementById('gambar');
            const file = fileInput.files[0];
            
            if (file && file.size > 5 * 1024 * 1024) {
                e.preventDefault();
                alert('Ukuran file terlalu besar. Maksimal 5MB.');
                fileInput.value = '';
            }
        });
    </script>
</body>
</html>