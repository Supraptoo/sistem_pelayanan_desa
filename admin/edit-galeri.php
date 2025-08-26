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
$galeri = [];
$kategori_galeri = [];

// Ambil ID dari parameter URL
$id = isset($_GET['id']) ? $_GET['id'] : '';

if (empty($id)) {
    header('Location: galeri.php');
    exit;
}

// Ambil data kategori Galeri
try {
    $query = "SELECT * FROM galeri_kategori ORDER BY nama_kategori";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $kategori_galeri = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = "Gagal memuat kategori Galeri: " . $e->getMessage();
}

// Ambil data Galeri berdasarkan ID
try {
    $query = "SELECT g.*, gk.nama_kategori 
              FROM galeri g 
              LEFT JOIN galeri_kategori gk ON g.kategori_id = gk.id 
              WHERE g.id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $galeri = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$galeri) {
        $error = "Data galeri tidak ditemukan.";
    }
} catch (Exception $e) {
    $error = "Gagal memuat data Galeri: " . $e->getMessage();
}

// Proses update Galeri
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_galeri'])) {
    $judul = $_POST['judul'] ?? '';
    $kategori_id = $_POST['kategori_id'] ?? '';
    $deskripsi = $_POST['deskripsi'] ?? '';
    $status = $_POST['status'] ?? 'draft';
    
    // Validasi input
    if (empty($judul)) {
        $error = "Judul gambar harus diisi.";
    } else {
        // Upload gambar baru jika ada
        $file_path = $galeri['file_path']; // Default ke path lama
        
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
                    // Hapus file lama jika ada
                    if (!empty($galeri['file_path'])) {
                        $old_file_path = '../' . $galeri['file_path'];
                        $old_thumb_path = '../' . dirname($galeri['file_path']) . '/thumb_' . basename($galeri['file_path']);
                        
                        if (file_exists($old_file_path)) {
                            unlink($old_file_path);
                        }
                        if (file_exists($old_thumb_path)) {
                            unlink($old_thumb_path);
                        }
                    }
                    
                    $file_path = 'uploads/galeri/' . $file_name;
                    
                    // Buat thumbnail jika diperlukan
                    createThumbnail($target_file, $upload_dir . 'thumb_' . $file_name, 300, 200);
                } else {
                    $error = "Gagal mengupload gambar.";
                }
            } else {
                $error = "Hanya file JPG, JPEG, PNG, GIF & WEBP yang diperbolehkan.";
            }
        }
        
        if (empty($error)) {
            try {
                $query = "UPDATE galeri 
                          SET judul = :judul, kategori_id = :kategori_id, deskripsi = :deskripsi, 
                              file_path = :file_path, status = :status, updated_at = NOW()
                          WHERE id = :id";
                
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':judul', $judul);
                $stmt->bindParam(':kategori_id', $kategori_id);
                $stmt->bindParam(':deskripsi', $deskripsi);
                $stmt->bindParam(':file_path', $file_path);
                $stmt->bindParam(':status', $status);
                $stmt->bindParam(':id', $id);
                
                if ($stmt->execute()) {
                    $success = "Gambar berhasil diupdate!";
                    
                    // Refresh data Galeri
                    $query = "SELECT g.*, gk.nama_kategori 
                              FROM galeri g 
                              LEFT JOIN galeri_kategori gk ON g.kategori_id = gk.id 
                              WHERE g.id = :id";
                    $stmt = $conn->prepare($query);
                    $stmt->bindParam(':id', $id);
                    $stmt->execute();
                    $galeri = $stmt->fetch(PDO::FETCH_ASSOC);
                } else {
                    $error = "Gagal mengupdate gambar.";
                }
            } catch (Exception $e) {
                $error = "Terjadi kesalahan: " . $e->getMessage();
            }
        }
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
    <title>Edit Galeri - Admin Desa Winduaji</title>
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
                <h1 class="text-2xl font-bold">Edit Galeri</h1>
                <a href="galeri.php" class="text-sm text-red-600 hover:text-red-800 inline-flex items-center">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali ke Galeri
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
            
            <?php if (empty($galeri)): ?>
                <div class="bg-white rounded-lg shadow p-6 text-center">
                    <p class="text-gray-500">Data galeri tidak ditemukan.</p>
                    <a href="galeri.php" class="inline-block mt-4 bg-red-600 text-white px-4 py-2 rounded-md">Kembali ke Galeri</a>
                </div>
            <?php else: ?>
                <!-- Form Edit Galeri -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h2 class="text-lg font-semibold mb-4 text-red-700">Edit Gambar</h2>
                    
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
                                    value="<?php echo htmlspecialchars($galeri['judul']); ?>"
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
                                        <option value="<?php echo $kategori['id']; ?>" <?php echo ($kategori['id'] == $galeri['kategori_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($kategori['nama_kategori']); ?>
                                        </option>
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
                                placeholder="Deskripsi gambar (opsional)"><?php echo htmlspecialchars($galeri['deskripsi']); ?></textarea>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                            <div>
                                <label for="gambar" class="block text-gray-700 text-sm font-medium mb-2">Gambar Baru (Opsional)</label>
                                <input 
                                    type="file" 
                                    id="gambar" 
                                    name="gambar" 
                                    accept="image/*"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent"
                                    onchange="previewImage(this)">
                                <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG, GIF, WEBP (Maks. 5MB)</p>
                                
                                <?php if (!empty($galeri['file_path'])): ?>
                                    <div class="mt-4">
                                        <p class="text-sm text-gray-700 mb-2">Gambar Saat Ini:</p>
                                        <img src="../<?php echo $galeri['file_path']; ?>" alt="Preview Gambar" class="w-full h-48 object-cover rounded-md border">
                                    </div>
                                <?php endif; ?>
                                
                                <img id="imagePreview" class="preview-image mt-2" alt="Preview Gambar Baru">
                            </div>
                            
                            <div>
                                <label for="status" class="block text-gray-700 text-sm font-medium mb-2">Status *</label>
                                <select 
                                    id="status" 
                                    name="status" 
                                    required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent">
                                    <option value="draft" <?php echo ($galeri['status'] === 'draft') ? 'selected' : ''; ?>>Draft</option>
                                    <option value="published" <?php echo ($galeri['status'] === 'published') ? 'selected' : ''; ?>>Published</option>
                                </select>
                                
                                <div class="mt-6 p-4 bg-gray-50 rounded-md">
                                    <p class="text-sm text-gray-700 font-medium">Informasi:</p>
                                    <p class="text-xs text-gray-600 mt-1">Dibuat: <?php echo date('d M Y H:i', strtotime($galeri['created_at'])); ?></p>
                                    <?php if (!empty($galeri['updated_at'])): ?>
                                        <p class="text-xs text-gray-600">Diupdate: <?php echo date('d M Y H:i', strtotime($galeri['updated_at'])); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-end space-x-3">
                            <a href="galeri.php" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-6 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400 transition-colors duration-200">
                                Batal
                            </a>
                            <button 
                                type="submit" 
                                name="update_galeri"
                                class="bg-red-700 hover:bg-red-800 text-white font-medium py-2 px-6 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-600 transition-colors duration-200">
                                <i class="fas fa-save mr-2"></i>Update Gambar
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
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