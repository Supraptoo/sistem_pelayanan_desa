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
$umkm_data = [];
$kategori_umkm = [];

// Ambil data kategori UMKM
try {
    $query = "SELECT * FROM umkm_kategori ORDER BY nama_kategori";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $kategori_umkm = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = "Gagal memuat kategori UMKM: " . $e->getMessage();
}

// Ambil data UMKM
try {
    $query = "SELECT u.*, uk.nama_kategori, uk.icon 
              FROM umkm u 
              LEFT JOIN umkm_kategori uk ON u.kategori_id = uk.id 
              ORDER BY u.created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $umkm_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = "Gagal memuat data UMKM: " . $e->getMessage();
}

// Proses tambah UMKM
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_umkm'])) {
    $nama_umkm = $_POST['nama_umkm'] ?? '';
    $kategori_id = $_POST['kategori_id'] ?? '';
    $pemilik = $_POST['pemilik'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $no_telp = $_POST['telepon'] ?? '';
    $deskripsi = $_POST['deskripsi'] ?? '';
    $harga = $_POST['harga'] ?? '';
    $status = $_POST['status'] ?? 'draft';
    
    // Upload gambar utama
    $foto_utama_path = '';
    if (!empty($_FILES['foto_utama']['name'])) {
        $upload_dir = '../assets/images/umkm/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = time() . '_' . basename($_FILES['foto_utama']['name']);
        $target_file = $upload_dir . $file_name;
        
        // Validasi file
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES['foto_utama']['tmp_name'], $target_file)) {
                $foto_utama_path = 'assets/images/umkm/' . $file_name;
            } else {
                $error = "Gagal mengupload gambar utama.";
            }
        } else {
            $error = "Hanya file JPG, JPEG, PNG & GIF yang diperbolehkan.";
        }
    }
    
    if (empty($error)) {
        try {
            $query = "INSERT INTO umkm (nama_umkm, kategori_id, pemilik, alamat, no_telp, deskripsi, harga, foto_utama_path, status) 
                      VALUES (:nama_umkm, :kategori_id, :pemilik, :alamat, :telepon, :deskripsi, :harga, :foto_utama_path, :status)";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':nama_umkm', $nama_umkm);
            $stmt->bindParam(':kategori_id', $kategori_id);
            $stmt->bindParam(':pemilik', $pemilik);
            $stmt->bindParam(':alamat', $alamat);
            $stmt->bindParam(':telepon', $no_telp);
            $stmt->bindParam(':deskripsi', $deskripsi);
            $stmt->bindParam(':harga', $harga);
            $stmt->bindParam(':foto_utama_path', $foto_utama_path);
            $stmt->bindParam(':status', $status);
            
            if ($stmt->execute()) {
                $success = "UMKM berhasil ditambahkan!";
                
                // Refresh data UMKM
                $query = "SELECT u.*, uk.nama_kategori, uk.icon 
                          FROM umkm u 
                          LEFT JOIN umkm_kategori uk ON u.kategori_id = uk.id 
                          ORDER BY u.created_at DESC";
                $stmt = $conn->prepare($query);
                $stmt->execute();
                $umkm_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $error = "Gagal menambahkan UMKM.";
            }
        } catch (Exception $e) {
            $error = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}

// Proses update status UMKM
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = $_POST['id'] ?? '';
    $status = $_POST['status'] ?? '';
    
    try {
        $query = "UPDATE umkm SET status = :status WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            $success = "Status UMKM berhasil diupdate!";
            
            // Refresh data UMKM
            $query = "SELECT u.*, uk.nama_kategori, uk.icon 
                      FROM umkm u 
                      LEFT JOIN umkm_kategori uk ON u.kategori_id = uk.id 
                      ORDER BY u.created_at DESC";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $umkm_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) {
        $error = "Gagal mengupdate status: " . $e->getMessage();
    }
}

// Proses hapus UMKM
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus_umkm'])) {
    $id = $_POST['id'] ?? '';
    
    try {
        // Hapus gambar terkait jika ada
        $query = "SELECT foto_utama_path FROM umkm WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $umkm = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($umkm && !empty($umkm['foto_utama_path'])) {
            $file_path = '../' . $umkm['foto_utama_path'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        
        // Hapus UMKM dari database
        $query = "DELETE FROM umkm WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            $success = "UMKM berhasil dihapus!";
            
            // Refresh data UMKM
            $query = "SELECT u.*, uk.nama_kategori, uk.icon 
                      FROM umkm u 
                      LEFT JOIN umkm_kategori uk ON u.kategori_id = uk.id 
                      ORDER BY u.created_at DESC";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $umkm_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) {
        $error = "Gagal menghapus UMKM: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <title>Manajemen UMKM - Admin Desa Winduaji</title>
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
            <a href="umkm.php" class="flex items-center px-4 py-3 text-white active">
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
                        <i class="fas fa-bell text-gray-600 header-icon"></i>
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
                <h1 class="text-2xl font-bold">Manajemen UMKM</h1>
                <a href="../index.php#umkm" target="_blank" class="text-sm text-red-600 hover:text-red-800 inline-flex items-center">
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
            
            <!-- Form Tambah UMKM -->
            <div class="bg-white rounded-lg shadow p-6 mb-6 card">
                <h2 class="text-lg font-semibold mb-4 text-red-700">Tambah UMKM Baru</h2>
                
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <div>
                            <label for="nama_umkm" class="block text-gray-700 text-sm font-medium mb-2">Nama UMKM *</label>
                            <input 
                                type="text" 
                                id="nama_umkm" 
                                name="nama_umkm" 
                                required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent"
                                placeholder="Masukkan nama UMKM">
                        </div>
                        
                        <div>
                            <label for="kategori_id" class="block text-gray-700 text-sm font-medium mb-2">Kategori *</label>
                            <select 
                                id="kategori_id" 
                                name="kategori_id" 
                                required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent">
                                <option value="">Pilih Kategori</option>
                                <?php foreach ($kategori_umkm as $kategori): ?>
                                    <option value="<?php echo $kategori['id']; ?>"><?php echo htmlspecialchars($kategori['nama_kategori']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <div>
                            <label for="pemilik" class="block text-gray-700 text-sm font-medium mb-2">Nama Pemilik *</label>
                            <input 
                                type="text" 
                                id="pemilik" 
                                name="pemilik" 
                                required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent"
                                placeholder="Nama pemilik UMKM">
                        </div>
                        
                        <div>
                            <label for="telepon" class="block text-gray-700 text-sm font-medium mb-2">Telepon/WhatsApp *</label>
                            <input 
                                type="text" 
                                id="telepon" 
                                name="telepon" 
                                required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent"
                                placeholder="Contoh: 081234567890">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="alamat" class="block text-gray-700 text-sm font-medium mb-2">Alamat *</label>
                        <textarea 
                            id="alamat" 
                            name="alamat" 
                            rows="2"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent"
                            placeholder="Alamat lengkap UMKM"></textarea>
                    </div>
                    
                    <div class="mb-4">
                        <label for="deskripsi" class="block text-gray-700 text-sm font-medium mb-2">Deskripsi Produk/Layanan *</label>
                        <textarea 
                            id="deskripsi" 
                            name="deskripsi" 
                            rows="4"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent"
                            placeholder="Deskripsikan produk atau layanan yang ditawarkan"></textarea>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
                        <div>
                            <label for="harga" class="block text-gray-700 text-sm font-medium mb-2">Harga/Range Harga</label>
                            <input 
                                type="text" 
                                id="harga" 
                                name="harga" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent"
                                placeholder="Contoh: Rp 10.000 - Rp 50.000">
                        </div>
                        
                        <div>
                            <label for="foto_utama" class="block text-gray-700 text-sm font-medium mb-2">Foto Utama</label>
                            <input 
                                type="file" 
                                id="foto_utama" 
                                name="foto_utama" 
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
                                <option value="draft">Draft</option>
                                <option value="published" selected>Published</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="flex justify-end">
                        <button 
                            type="submit" 
                            name="tambah_umkm"
                            class="bg-red-700 hover:bg-red-800 text-white font-medium py-2 px-6 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-600 transition-colors duration-200">
                            <i class="fas fa-plus mr-2"></i>Tambah UMKM
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Daftar UMKM -->
            <div class="bg-white rounded-lg shadow p-6 card">
                <h2 class="text-lg font-semibold mb-4 text-red-700">Daftar UMKM</h2>
                
                <?php if (empty($umkm_data)): ?>
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-store fa-3x mb-4"></i>
                        <p>Belum ada UMKM.</p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($umkm_data as $umkm): ?>
                            <div class="bg-gray-50 rounded-lg overflow-hidden shadow card-hover">
                                <div class="h-48 bg-gray-200 overflow-hidden">
                                    <?php if (!empty($umkm['foto_utama_path'])): ?>
                                        <img src="../<?php echo $umkm['foto_utama_path']; ?>" alt="<?php echo htmlspecialchars($umkm['nama_umkm']); ?>" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <div class="w-full h-full flex items-center justify-center bg-gray-100">
                                            <i class="fas fa-store text-gray-400 text-4xl"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-xs font-semibold px-2 py-1 rounded 
                                            <?php echo $umkm['status'] === 'published' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                            <?php echo $umkm['status'] === 'published' ? 'Published' : 'Draft'; ?>
                                        </span>
                                        <span class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded">
                                            <?php echo htmlspecialchars($umkm['nama_kategori']); ?>
                                        </span>
                                    </div>
                                    
                                    <h3 class="font-bold text-lg mb-2"><?php echo htmlspecialchars($umkm['nama_umkm']); ?></h3>
                                    
                                    <p class="text-sm text-gray-600 mb-2">
                                        <i class="fas fa-user mr-1"></i> <?php echo htmlspecialchars($umkm['pemilik']); ?>
                                    </p>
                                    
                                    <p class="text-sm text-gray-600 mb-3 truncate-2">
                                        <i class="fas fa-map-marker-alt mr-1"></i> <?php echo htmlspecialchars($umkm['alamat']); ?>
                                    </p>
                                    
                                    <p class="text-sm text-gray-700 mb-3 truncate-2">
                                        <?php echo htmlspecialchars($umkm['deskripsi']); ?>
                                    </p>
                                    
                                    <?php if (!empty($umkm['harga'])): ?>
                                        <p class="text-red-600 font-bold mb-3"><?php echo htmlspecialchars($umkm['harga']); ?></p>
                                    <?php endif; ?>
                                    
                                    <div class="flex justify-between items-center">
                                        <div class="flex space-x-2">
                                            <form method="POST" action="" class="inline">
                                                <input type="hidden" name="id" value="<?php echo $umkm['id']; ?>">
                                                <select name="status" onchange="this.form.submit()" class="text-xs px-2 py-1 rounded <?php echo $umkm['status'] === 'published' ? 'status-published' : 'status-draft'; ?>">
                                                    <option value="draft" <?php echo $umkm['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                                    <option value="published" <?php echo $umkm['status'] === 'published' ? 'selected' : ''; ?>>Published</option>
                                                </select>
                                                <input type="hidden" name="update_status" value="1">
                                            </form>
                                            
                                            <a href="edit-umkm.php?id=<?php echo $umkm['id']; ?>" class="text-blue-600 hover:text-blue-800 text-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                        
                                        <form method="POST" action="" class="inline">
                                            <input type="hidden" name="id" value="<?php echo $umkm['id']; ?>">
                                            <button type="submit" name="hapus_umkm" onclick="return confirm('Apakah Anda yakin ingin menghapus UMKM ini?')" class="text-red-600 hover:text-red-800 text-sm">
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
        
        // Format input harga
        document.getElementById('harga').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value) {
                value = parseInt(value, 10).toLocaleString('id-ID');
                e.target.value = 'Rp ' + value;
            }
        });
    </script>
</body>
</html>