<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';

// Data desa
$desa_nama = "Desa Winduaji";
$desa_lokasi = "Kecamatan Paninggaran, Kabupaten Pekalongan, Provinsi Jawa Tengah";

// Pesan operasi
$message = '';
$message_type = '';

// Ambil kategori galeri
try {
    $stmt = $pdo->query("SELECT * FROM galeri_kategori");
    $kategori = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error mengambil data kategori: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = $_POST['judul'] ?? '';
    $deskripsi = $_POST['deskripsi'] ?? '';
    $kategori_id = $_POST['kategori_id'] ?? '';
    $tags = $_POST['tags'] ?? '';
    $status = $_POST['status'] ?? 'published';
    $tanggal_upload = date('Y-m-d');

    // Validasi input
    if (empty($judul)) {
        $message = 'Judul foto harus diisi!';
        $message_type = 'danger';
    } elseif (empty($kategori_id)) {
        $message = 'Kategori harus dipilih!';
        $message_type = 'danger';
    } else {
        try {
            // Handle file upload
            $file_path = '';
            $thumbnail_path = '';
            
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = __DIR__ . '/../../uploads/galeri/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $file_ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
                $file_name = 'foto_' . time() . '_' . uniqid() . '.' . $file_ext;
                $file_target = $upload_dir . $file_name;

                // Validasi ekstensi file
                $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
                if (!in_array(strtolower($file_ext), $allowed_ext)) {
                    $message = 'Hanya file JPG, JPEG, PNG, dan GIF yang diperbolehkan!';
                    $message_type = 'danger';
                } elseif (move_uploaded_file($_FILES['foto']['tmp_name'], $file_target)) {
                    $file_path = 'uploads/galeri/' . $file_name;
                    
                    // Buat thumbnail (contoh sederhana)
                    $thumbnail_name = 'thumb_' . $file_name;
                    $thumbnail_target = $upload_dir . $thumbnail_name;
                    createThumbnail($file_target, $thumbnail_target, 300, 200);
                    $thumbnail_path = 'uploads/galeri/' . $thumbnail_name;
                } else {
                    $message = 'Gagal mengupload file!';
                    $message_type = 'danger';
                }
            }

            if ($file_path) {
                // Simpan ke database
                $stmt = $pdo->prepare("INSERT INTO galeri (
                    kategori_id, judul, deskripsi, tags, file_path, 
                    thumbnail_path, tipe, status, tanggal_upload
                ) VALUES (
                    :kategori_id, :judul, :deskripsi, :tags, :file_path,
                    :thumbnail_path, 'foto', :status, :tanggal_upload
                )");

                $stmt->execute([
                    ':kategori_id' => $kategori_id,
                    ':judul' => $judul,
                    ':deskripsi' => $deskripsi,
                    ':tags' => $tags,
                    ':file_path' => $file_path,
                    ':thumbnail_path' => $thumbnail_path,
                    ':status' => $status,
                    ':tanggal_upload' => $tanggal_upload
                ]);

                $message = 'Foto berhasil ditambahkan!';
                $message_type = 'success';
                
                // Redirect setelah 2 detik
                header("Refresh: 2; URL=foto.php");
            }
        } catch (PDOException $e) {
            $message = 'Gagal menyimpan data: ' . $e->getMessage();
            $message_type = 'danger';
        }
    }
}

// Fungsi untuk membuat thumbnail sederhana
function createThumbnail($source, $destination, $width, $height) {
    $info = getimagesize($source);
    $source_image = null;

    switch ($info['mime']) {
        case 'image/jpeg':
            $source_image = imagecreatefromjpeg($source);
            break;
        case 'image/png':
            $source_image = imagecreatefrompng($source);
            break;
        case 'image/gif':
            $source_image = imagecreatefromgif($source);
            break;
    }

    if ($source_image) {
        $thumb = imagecreatetruecolor($width, $height);
        imagecopyresampled($thumb, $source_image, 0, 0, 0, 0, $width, $height, $info[0], $info[1]);
        
        switch ($info['mime']) {
            case 'image/jpeg':
                imagejpeg($thumb, $destination);
                break;
            case 'image/png':
                imagepng($thumb, $destination);
                break;
            case 'image/gif':
                imagegif($thumb, $destination);
                break;
        }
        
        imagedestroy($thumb);
        imagedestroy($source_image);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Foto - <?php echo $desa_nama; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        /* Sidebar - Sesuaikan dengan dashboard.php */
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

        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: var(--transition);
            background-color: #f5f7fb;
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

        /* Form Styles */
        .form-container {
            padding: 2rem;
            max-width: 800px;
            margin: 0 auto;
        }

        .form-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .form-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark-color);
        }

        .form-card {
            border-radius: var(--border-radius);
            padding: 2rem;
            background: white;
            box-shadow: var(--shadow-sm);
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

        .preview-container {
            margin-top: 1rem;
            text-align: center;
        }

        .preview-image {
            max-width: 100%;
            max-height: 300px;
            border-radius: var(--border-radius);
            border: 1px dashed var(--gray-medium);
            padding: 1rem;
        }

        .btn-submit {
            background-color: var(--primary-color);
            border: none;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
        }

        .btn-submit:hover {
            background-color: var(--secondary-color);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .form-container {
                padding: 1rem;
            }
            
            .form-card {
                padding: 1.5rem;
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
                <span class="d-none d-md-inline">Tambah Foto Galeri</span>
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
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="../../logout.php"><i class="fas fa-sign-out-alt me-2"></i> Keluar</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Form Content -->
        <div class="form-container">
            <!-- Alert Message -->
            <?php if ($message): ?>
            <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
                <?= $message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <div class="form-header">
                <h1 class="form-title">Tambah Foto Baru</h1>
                <a href="foto.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
            </div>

            <div class="form-card">
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="judul" class="form-label">Judul Foto</label>
                        <input type="text" class="form-control" id="judul" name="judul" required>
                    </div>

                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="kategori_id" class="form-label">Kategori</label>
                        <select class="form-select" id="kategori_id" name="kategori_id" required>
                            <option value="">Pilih Kategori</option>
                            <?php foreach ($kategori as $kat): ?>
                                <option value="<?= $kat['id'] ?>"><?= htmlspecialchars($kat['nama_kategori']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="tags" class="form-label">Tags (pisahkan dengan koma)</label>
                        <input type="text" class="form-control" id="tags" name="tags" placeholder="Contoh: budaya, festival, desa">
                    </div>

                    <div class="mb-3">
                        <label for="foto" class="form-label">File Foto</label>
                        <input type="file" class="form-control" id="foto" name="foto" accept="image/*" required>
                        <div class="form-text">Format yang didukung: JPG, JPEG, PNG, GIF. Ukuran maksimal: 5MB.</div>
                        <div class="preview-container">
                            <img id="preview" class="preview-image" style="display: none;">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="status" id="status_published" value="published" checked>
                            <label class="form-check-label" for="status_published">
                                Published
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="status" id="status_draft" value="draft">
                            <label class="form-check-label" for="status_draft">
                                Draft
                            </label>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="reset" class="btn btn-outline-secondary me-md-2">
                            <i class="fas fa-undo me-2"></i>Reset
                        </button>
                        <button type="submit" class="btn btn-primary btn-submit">
                            <i class="fas fa-save me-2"></i>Simpan Foto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Preview image before upload
        document.getElementById('foto').addEventListener('change', function(e) {
            const preview = document.getElementById('preview');
            const file = e.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                
                reader.readAsDataURL(file);
            } else {
                preview.src = '';
                preview.style.display = 'none';
            }
        });

        // Toggle Sidebar
        $('.toggle-btn').click(function() {
            $('.sidebar').toggleClass('collapsed');
            $('.main-content').toggleClass('expanded');
        });

        // Auto close alert
        setTimeout(function() {
            $('.alert').alert('close');
        }, 5000);
    </script>
</body>
</html>