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
$berita = [];
$kategori_berita = [];

// Ambil ID berita dari parameter URL
$id = isset($_GET['id']) ? $_GET['id'] : '';

if (empty($id)) {
    header('Location: berita.php');
    exit;
}

// Ambil data berita berdasarkan ID
try {
    $query = "SELECT b.*, bk.nama_kategori 
              FROM berita b 
              LEFT JOIN berita_kategori bk ON b.kategori_id = bk.id 
              WHERE b.id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $berita = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$berita) {
        $error = "Berita tidak ditemukan.";
    }
} catch (Exception $e) {
    $error = "Gagal memuat data berita: " . $e->getMessage();
}

// Ambil data kategori berita
try {
    $query = "SELECT * FROM berita_kategori ORDER BY nama_kategori";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $kategori_berita = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = "Gagal memuat kategori berita: " . $e->getMessage();
}

// Proses update berita
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_berita'])) {
    $judul = $_POST['judul'] ?? '';
    $kategori_id = $_POST['kategori_id'] ?? '';
    $isi = $_POST['isi'] ?? '';
    $status = $_POST['status'] ?? 'draft';
    $tanggal_publish = $_POST['tanggal_publish'] ?? date('Y-m-d H:i:s');
    $hapus_gambar = isset($_POST['hapus_gambar']) ? true : false;
    
    // Upload gambar baru jika ada
    $gambar_path = $berita['gambar_path'] ?? '';
    
    if ($hapus_gambar && !empty($gambar_path)) {
        // Hapus gambar lama
        $file_path = '../' . $gambar_path;
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        $gambar_path = '';
    }
    
    if (!empty($_FILES['gambar']['name'])) {
        // Hapus gambar lama jika ada
        if (!empty($berita['gambar_path'])) {
            $old_file_path = '../' . $berita['gambar_path'];
            if (file_exists($old_file_path)) {
                unlink($old_file_path);
            }
        }
        
        // Upload gambar baru
        $upload_dir = '../assets/images/berita/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = time() . '_' . basename($_FILES['gambar']['name']);
        $target_file = $upload_dir . $file_name;
        
        // Validasi file
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
                $gambar_path = 'assets/images/berita/' . $file_name;
            } else {
                $error = "Gagal mengupload gambar.";
            }
        } else {
            $error = "Hanya file JPG, JPEG, PNG & GIF yang diperbolehkan.";
        }
    }
    
    if (empty($error)) {
        try {
            if (!empty($gambar_path)) {
                $query = "UPDATE berita 
                          SET judul = :judul, kategori_id = :kategori_id, isi = :isi, 
                              gambar_path = :gambar_path, status = :status, tanggal_publish = :tanggal_publish 
                          WHERE id = :id";
            } else {
                $query = "UPDATE berita 
                          SET judul = :judul, kategori_id = :kategori_id, isi = :isi, 
                              status = :status, tanggal_publish = :tanggal_publish 
                          WHERE id = :id";
            }
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':judul', $judul);
            $stmt->bindParam(':kategori_id', $kategori_id);
            $stmt->bindParam(':isi', $isi);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':tanggal_publish', $tanggal_publish);
            $stmt->bindParam(':id', $id);
            
            if (!empty($gambar_path)) {
                $stmt->bindParam(':gambar_path', $gambar_path);
            }
            
            if ($stmt->execute()) {
                $success = "Berita berhasil diupdate!";
                
                // Refresh data berita
                $query = "SELECT b.*, bk.nama_kategori 
                          FROM berita b 
                          LEFT JOIN berita_kategori bk ON b.kategori_id = bk.id 
                          WHERE b.id = :id";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                $berita = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $error = "Gagal mengupdate berita.";
            }
        } catch (Exception $e) {
            $error = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <title>Edit Berita - Admin Desa Winduaji</title>
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
            <a href="berita.php" class="flex items-center px-4 py-3 text-white active">
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
                <h1 class="text-2xl font-bold">Edit Berita</h1>
                <a href="berita.php" class="text-sm text-red-600 hover:text-red-800 inline-flex items-center">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar Berita
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
            
            <?php if (!empty($berita)): ?>
            <!-- Form Edit Berita -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold mb-4 text-red-700">Edit Berita: <?php echo htmlspecialchars($berita['judul']); ?></h2>
                
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <div>
                            <label for="judul" class="block text-gray-700 text-sm font-medium mb-2">Judul Berita *</label>
                            <input 
                                type="text" 
                                id="judul" 
                                name="judul" 
                                required 
                                value="<?php echo htmlspecialchars($berita['judul']); ?>"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent"
                                placeholder="Masukkan judul berita">
                        </div>
                        
                        <div>
                            <label for="kategori_id" class="block text-gray-700 text-sm font-medium mb-2">Kategori *</label>
                            <select 
                                id="kategori_id" 
                                name="kategori_id" 
                                required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent">
                                <option value="">Pilih Kategori</option>
                                <?php foreach ($kategori_berita as $kategori): ?>
                                    <option value="<?php echo $kategori['id']; ?>" <?php echo ($kategori['id'] == $berita['kategori_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($kategori['nama_kategori']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="isi" class="block text-gray-700 text-sm font-medium mb-2">Isi Berita *</label>
                        <textarea 
                            id="isi" 
                            name="isi" 
                            rows="6"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent"
                            placeholder="Tulis isi berita di sini..."><?php echo htmlspecialchars($berita['isi']); ?></textarea>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2">Gambar Saat Ini</label>
                        <?php if (!empty($berita['gambar_path'])): ?>
                            <div class="flex items-center space-x-4">
                                <img src="../<?php echo $berita['gambar_path']; ?>" alt="Gambar Berita" class="w-32 h-32 object-cover rounded-md">
                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="hapus_gambar" value="1" class="mr-2">
                                        <span class="text-sm text-red-600">Hapus gambar ini</span>
                                    </label>
                                    <p class="text-xs text-gray-500 mt-1">Centang untuk menghapus gambar saat ini</p>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="text-sm text-gray-500">Tidak ada gambar</p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
                        <div>
                            <label for="gambar" class="block text-gray-700 text-sm font-medium mb-2">Gambar Baru (Opsional)</label>
                            <input 
                                type="file" 
                                id="gambar" 
                                name="gambar" 
                                accept="image/*"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent">
                            <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG, GIF (Maks. 2MB)</p>
                        </div>
                        
                        <div>
                            <label for="status" class="block text-gray-700 text-sm font-medium mb-2">Status *</label>
                            <select 
                                id="status" 
                                name="status" 
                                required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent">
                                <option value="draft" <?php echo ($berita['status'] === 'draft') ? 'selected' : ''; ?>>Draft</option>
                                <option value="published" <?php echo ($berita['status'] === 'published') ? 'selected' : ''; ?>>Published</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="tanggal_publish" class="block text-gray-700 text-sm font-medium mb-2">Tanggal Publikasi</label>
                            <input 
                                type="datetime-local" 
                                id="tanggal_publish" 
                                name="tanggal_publish" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent"
                                value="<?php echo date('Y-m-d\TH:i', strtotime($berita['tanggal_publish'])); ?>">
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <a href="berita.php" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-6 rounded-md focus:outline-none transition-colors duration-200">
                            Batal
                        </a>
                        <button 
                            type="submit" 
                            name="update_berita"
                            class="bg-red-700 hover:bg-red-800 text-white font-medium py-2 px-6 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-600 transition-colors duration-200">
                            <i class="fas fa-save mr-2"></i>Update Berita
                        </button>
                    </div>
                </form>
            </div>
            <?php else: ?>
                <div class="bg-white rounded-lg shadow p-6 text-center">
                    <p class="text-gray-600">Berita tidak ditemukan.</p>
                    <a href="berita.php" class="inline-block mt-4 text-red-600 hover:text-red-800">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar Berita
                    </a>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        // Toggle sidebar on mobile
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
    </script>
</body>
</html>