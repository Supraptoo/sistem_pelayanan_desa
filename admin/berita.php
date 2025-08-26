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
$berita = [];
$kategori_berita = [];

// Ambil data kategori berita
try {
    $query = "SELECT * FROM berita_kategori ORDER BY nama_kategori";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $kategori_berita = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = "Gagal memuat kategori berita: " . $e->getMessage();
}

// Ambil data berita
try {
    $query = "SELECT b.*, bk.nama_kategori 
              FROM berita b 
              LEFT JOIN berita_kategori bk ON b.kategori_id = bk.id 
              ORDER BY b.tanggal_publish DESC, b.created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $berita = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = "Gagal memuat data berita: " . $e->getMessage();
}

// Proses tambah berita
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_berita'])) {
    $judul = $_POST['judul'] ?? '';
    $kategori_id = $_POST['kategori_id'] ?? '';
    $isi = $_POST['isi'] ?? '';
    $status = $_POST['status'] ?? 'draft';
    $tanggal_publish = $_POST['tanggal_publish'] ?? date('Y-m-d H:i:s');
    
    // Upload gambar
    $gambar_path = '';
    if (!empty($_FILES['gambar']['name'])) {
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
            $query = "INSERT INTO berita (judul, kategori_id, isi, gambar_path, status, tanggal_publish) 
                      VALUES (:judul, :kategori_id, :isi, :gambar_path, :status, :tanggal_publish)";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':judul', $judul);
            $stmt->bindParam(':kategori_id', $kategori_id);
            $stmt->bindParam(':isi', $isi);
            $stmt->bindParam(':gambar_path', $gambar_path);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':tanggal_publish', $tanggal_publish);
            
            if ($stmt->execute()) {
                $success = "Berita berhasil ditambahkan!";
                
                // Refresh data berita
                $query = "SELECT b.*, bk.nama_kategori 
                          FROM berita b 
                          LEFT JOIN berita_kategori bk ON b.kategori_id = bk.id 
                          ORDER BY b.tanggal_publish DESC, b.created_at DESC";
                $stmt = $conn->prepare($query);
                $stmt->execute();
                $berita = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $error = "Gagal menambahkan berita.";
            }
        } catch (Exception $e) {
            $error = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}

// Proses update status berita
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = $_POST['id'] ?? '';
    $status = $_POST['status'] ?? '';
    
    try {
        $query = "UPDATE berita SET status = :status WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            $success = "Status berita berhasil diupdate!";
            
            // Refresh data berita
            $query = "SELECT b.*, bk.nama_kategori 
                      FROM berita b 
                      LEFT JOIN berita_kategori bk ON b.kategori_id = bk.id 
                      ORDER BY b.tanggal_publish DESC, b.created_at DESC";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $berita = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) {
        $error = "Gagal mengupdate status: " . $e->getMessage();
    }
}

// Proses hapus berita
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus_berita'])) {
    $id = $_POST['id'] ?? '';
    
    try {
        // Hapus gambar terkait jika ada
        $query = "SELECT gambar_path FROM berita WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $berita_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($berita_data && !empty($berita_data['gambar_path'])) {
            $file_path = '../' . $berita_data['gambar_path'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        
        // Hapus berita dari database
        $query = "DELETE FROM berita WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            $success = "Berita berhasil dihapus!";
            
            // Refresh data berita
            $query = "SELECT b.*, bk.nama_kategori 
                      FROM berita b 
                      LEFT JOIN berita_kategori bk ON b.kategori_id = bk.id 
                      ORDER BY b.tanggal_publish DESC, b.created_at DESC";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $berita = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) {
        $error = "Gagal menghapus berita: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <title>Manajemen Berita - Admin Desa Winduaji</title>
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
                <h1 class="text-2xl font-bold">Manajemen Berita</h1>
                <a href="../index.php#berita" target="_blank" class="text-sm text-red-600 hover:text-red-800 inline-flex items-center">
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
            
            <!-- Form Tambah Berita -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold mb-4 text-red-700">Tambah Berita Baru</h2>
                
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <div>
                            <label for="judul" class="block text-gray-700 text-sm font-medium mb-2">Judul Berita *</label>
                            <input 
                                type="text" 
                                id="judul" 
                                name="judul" 
                                required 
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
                                    <option value="<?php echo $kategori['id']; ?>"><?php echo htmlspecialchars($kategori['nama_kategori']); ?></option>
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
                            placeholder="Tulis isi berita di sini..."></textarea>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
                        <div>
                            <label for="gambar" class="block text-gray-700 text-sm font-medium mb-2">Gambar Berita</label>
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
                                <option value="draft">Draft</option>
                                <option value="published" selected>Published</option>
                            </select>
                        </div>
                        
                        <div>
                           <label for="tanggal_publish" class="block text-gray-700 text-sm font-medium mb-2">
        Tanggal Publikasi
    </label>
    <input 
        type="datetime-local" 
        id="tanggal_publish" 
        name="tanggal_publish" 
        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-colors duration-200"
    >
</div>
                    </div>
                    
                    <div class="flex justify-end">
                        <button 
                            type="submit" 
                            name="tambah_berita"
                            class="bg-red-700 hover:bg-red-800 text-white font-medium py-2 px-6 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-600 transition-colors duration-200">
                            <i class="fas fa-plus mr-2"></i>Tambah Berita
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Daftar Berita -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4 text-red-700">Daftar Berita</h2>
                
                <?php if (empty($berita)): ?>
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-newspaper fa-3x mb-4"></i>
                        <p>Belum ada berita.</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white">
                            <thead>
                                <tr>
                                    <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul</th>
                                    <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                                    <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                    <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($berita as $item): ?>
                                    <tr>
                                        <td class="py-4 px-4">
                                            <div class="flex items-center">
                                                <?php if (!empty($item['gambar_path'])): ?>
                                                    <img src="../<?php echo $item['gambar_path']; ?>" alt="<?php echo htmlspecialchars($item['judul']); ?>" class="w-12 h-12 object-cover rounded mr-3">
                                                <?php else: ?>
                                                    <div class="w-12 h-12 bg-gray-200 rounded mr-3 flex items-center justify-center">
                                                        <i class="fas fa-newspaper text-gray-400"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <p class="font-medium text-gray-900"><?php echo htmlspecialchars($item['judul']); ?></p>
                                                    <p class="text-sm text-gray-500 truncate-2"><?php echo strip_tags(substr($item['isi'], 0, 100)); ?>...</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-4 px-4"><?php echo htmlspecialchars($item['nama_kategori'] ?? 'Tidak ada kategori'); ?></td>
                                        <td class="py-4 px-4">
                                            <p class="text-sm text-gray-900"><?php echo date('d M Y', strtotime($item['tanggal_publish'])); ?></p>
                                            <p class="text-xs text-gray-500"><?php echo date('H:i', strtotime($item['tanggal_publish'])); ?></p>
                                        </td>
                                        <td class="py-4 px-4">
                                            <form method="POST" action="" class="inline">
                                                <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                                <select name="status" onchange="this.form.submit()" class="text-xs px-2 py-1 rounded <?php echo $item['status'] === 'published' ? 'status-published' : 'status-draft'; ?>">
                                                    <option value="draft" <?php echo $item['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                                    <option value="published" <?php echo $item['status'] === 'published' ? 'selected' : ''; ?>>Published</option>
                                                </select>
                                                <input type="hidden" name="update_status" value="1">
                                            </form>
                                        </td>
                                        <td class="py-4 px-4">
                                            <div class="flex space-x-2">
                                                <a href="edit-berita.php?id=<?php echo $item['id']; ?>" class="text-blue-600 hover:text-blue-800">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" action="" class="inline">
                                                    <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                                    <button type="submit" name="hapus_berita" onclick="return confirm('Apakah Anda yakin ingin menghapus berita ini?')" class="text-red-600 hover:text-red-800">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        // Toggle sidebar on mobile
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });function setDateTimeNow() {
    const now = new Date();
    const local = new Date(now.getTime() - (now.getTimezoneOffset() * 60000))
        .toISOString()
        .slice(0,16); // format YYYY-MM-DDTHH:MM
    document.getElementById("tanggal_publish").value = local;
}

// jalankan saat halaman load
setDateTimeNow();

// jika mau terus update tiap menit:
setInterval(setDateTimeNow, 60000);
</script>
</body>
</html>