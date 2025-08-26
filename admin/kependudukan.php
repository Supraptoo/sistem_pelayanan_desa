<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';

// Koneksi database
$db = new database();
$conn = $db->getConnection();

// Handle import data
if (isset($_POST['import'])) {
    if ($_FILES['file']['name']) {
        $filename = explode(".", $_FILES['file']['name']);
        if (end($filename) == "csv") {
            $handle = fopen($_FILES['file']['tmp_name'], "r");
            $skipFirstRow = true;
            
            while ($data = fgetcsv($handle)) {
                if ($skipFirstRow) {
                    $skipFirstRow = false;
                    continue;
                }
                
                // Map CSV columns to database fields
                $nama = $data[1] ?? '';
                $tempat_lahir = $data[2] ?? '';
                $tanggal_lahir = !empty($data[3]) ? date('Y-m-d', strtotime($data[3])) : '0000-00-00';
                $nik = $data[4] ?? '';
                $jenis_kelamin = $data[5] ?? '';
                $status = $data[6] ?? '';
                $no_kk = $data[7] ?? '';
                $rt = $data[8] ?? null;
                $rw = $data[9] ?? null;
                $dusun = $data[10] ?? '';
                $alamat = $data[11] ?? '';
                $status_hidup = $data[12] ?? 'Hidup';
                $pendidikan_terakhir = $data[13] ?? '';
                $pekerjaan = $data[14] ?? '';
                
                // Calculate age from date of birth
                $usia = 0;
                if (!empty($data[3])) {
                    $birthDate = new DateTime($data[3]);
                    $today = new DateTime();
                    $usia = $birthDate->diff($today)->y;
                }
                
                // Check if NIK already exists
                $checkQuery = "SELECT id FROM kependudukan WHERE nik = :nik";
                $checkStmt = $conn->prepare($checkQuery);
                $checkStmt->bindParam(':nik', $nik);
                $checkStmt->execute();
                
                if ($checkStmt->rowCount() > 0) {
                    // Update existing record
                    $query = "UPDATE kependudukan SET nama = :nama, tempat_lahir = :tempat_lahir, 
                             tanggal_lahir = :tanggal_lahir, jenis_kelamin = :jenis_kelamin, 
                             status = :status, usia = :usia, no_kk = :no_kk, rt = :rt, rw = :rw,
                             dusun = :dusun, alamat = :alamat, status_hidup = :status_hidup,
                             pendidikan_terakhir = :pendidikan_terakhir, pekerjaan = :pekerjaan,
                             updated_at = NOW() WHERE nik = :nik";
                } else {
                    // Insert new record - no akan diisi otomatis oleh database
                    $query = "INSERT INTO kependudukan (nama, tempat_lahir, tanggal_lahir, nik, 
                             jenis_kelamin, status, usia, no_kk, rt, rw, dusun, alamat, status_hidup,
                             pendidikan_terakhir, pekerjaan) 
                             VALUES (:nama, :tempat_lahir, :tanggal_lahir, :nik, 
                             :jenis_kelamin, :status, :usia, :no_kk, :rt, :rw, :dusun, :alamat, 
                             :status_hidup, :pendidikan_terakhir, :pekerjaan)";
                }
                
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':nama', $nama);
                $stmt->bindParam(':tempat_lahir', $tempat_lahir);
                $stmt->bindParam(':tanggal_lahir', $tanggal_lahir);
                $stmt->bindParam(':nik', $nik);
                $stmt->bindParam(':jenis_kelamin', $jenis_kelamin);
                $stmt->bindParam(':status', $status);
                $stmt->bindParam(':usia', $usia);
                $stmt->bindParam(':no_kk', $no_kk);
                $stmt->bindParam(':rt', $rt);
                $stmt->bindParam(':rw', $rw);
                $stmt->bindParam(':dusun', $dusun);
                $stmt->bindParam(':alamat', $alamat);
                $stmt->bindParam(':status_hidup', $status_hidup);
                $stmt->bindParam(':pendidikan_terakhir', $pendidikan_terakhir);
                $stmt->bindParam(':pekerjaan', $pekerjaan);
                $stmt->execute();
            }
            
            fclose($handle);
            
            // Update nomor urut setelah import
            updateNomorUrut($conn);
            
            $success_message = "Data berhasil diimpor!";
        } else {
            $error_message = "Format file harus CSV!";
        }
    }
}

// Handle export data
if (isset($_POST['export'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=data_kependudukan_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    
    // Header row
    fputcsv($output, array('No', 'Nama', 'Tempat Lahir', 'Tanggal Lahir', 'NIK', 'Jenis Kelamin', 
                           'Status', 'No KK', 'RT', 'RW', 'Dusun', 'Alamat', 'Status Hidup', 
                           'Pendidikan Terakhir', 'Pekerjaan'));
    
    // Data rows
    $query = "SELECT no, nama, tempat_lahir, tanggal_lahir, nik, jenis_kelamin, status, 
                     no_kk, rt, rw, dusun, alamat, status_hidup, pendidikan_terakhir, pekerjaan
              FROM kependudukan ORDER BY no";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit;
}

// Handle delete data
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $query = "DELETE FROM kependudukan WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id);
    
    if ($stmt->execute()) {
        // Update nomor urut setelah delete
        updateNomorUrut($conn);
        $success_message = "Data berhasil dihapus!";
    } else {
        $error_message = "Gagal menghapus data!";
    }
}

// Handle edit data
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    $tempat_lahir = $_POST['tempat_lahir'];
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $nik = $_POST['nik'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $status = $_POST['status'];
    $no_kk = $_POST['no_kk'];
    $rt = $_POST['rt'];
    $rw = $_POST['rw'];
    $dusun = $_POST['dusun'];
    $alamat = $_POST['alamat'];
    $status_hidup = $_POST['status_hidup'];
    $pendidikan_terakhir = $_POST['pendidikan_terakhir'];
    $pekerjaan = $_POST['pekerjaan'];
    
    // Calculate age
    $usia = 0;
    if (!empty($tanggal_lahir)) {
        $birthDate = new DateTime($tanggal_lahir);
        $today = new DateTime();
        $usia = $birthDate->diff($today)->y;
    }
    
    $query = "UPDATE kependudukan SET nama = :nama, tempat_lahir = :tempat_lahir, 
              tanggal_lahir = :tanggal_lahir, nik = :nik, jenis_kelamin = :jenis_kelamin, 
              status = :status, usia = :usia, no_kk = :no_kk, rt = :rt, rw = :rw, dusun = :dusun, 
              alamat = :alamat, status_hidup = :status_hidup, pendidikan_terakhir = :pendidikan_terakhir, 
              pekerjaan = :pekerjaan, updated_at = NOW() WHERE id = :id";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':nama', $nama);
    $stmt->bindParam(':tempat_lahir', $tempat_lahir);
    $stmt->bindParam(':tanggal_lahir', $tanggal_lahir);
    $stmt->bindParam(':nik', $nik);
    $stmt->bindParam(':jenis_kelamin', $jenis_kelamin);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':usia', $usia);
    $stmt->bindParam(':no_kk', $no_kk);
    $stmt->bindParam(':rt', $rt);
    $stmt->bindParam(':rw', $rw);
    $stmt->bindParam(':dusun', $dusun);
    $stmt->bindParam(':alamat', $alamat);
    $stmt->bindParam(':status_hidup', $status_hidup);
    $stmt->bindParam(':pendidikan_terakhir', $pendidikan_terakhir);
    $stmt->bindParam(':pekerjaan', $pekerjaan);
    
    if ($stmt->execute()) {
        $success_message = "Data berhasil diperbarui!";
    } else {
        $error_message = "Gagal memperbarui data!";
    }
}

// Handle add new data
if (isset($_POST['add'])) {
    $nama = $_POST['nama'];
    $tempat_lahir = $_POST['tempat_lahir'];
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $nik = $_POST['nik'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $status = $_POST['status'];
    $no_kk = $_POST['no_kk'];
    $rt = $_POST['rt'];
    $rw = $_POST['rw'];
    $dusun = $_POST['dusun'];
    $alamat = $_POST['alamat'];
    $status_hidup = $_POST['status_hidup'];
    $pendidikan_terakhir = $_POST['pendidikan_terakhir'];
    $pekerjaan = $_POST['pekerjaan'];
    
    // Calculate age
    $usia = 0;
    if (!empty($tanggal_lahir)) {
        $birthDate = new DateTime($tanggal_lahir);
        $today = new DateTime();
        $usia = $birthDate->diff($today)->y;
    }
    
    // Check if NIK already exists
    $checkQuery = "SELECT id FROM kependudukan WHERE nik = :nik";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bindParam(':nik', $nik);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() > 0) {
        $error_message = "NIK sudah terdaftar!";
    } else {
        // Insert new record - no akan diisi otomatis oleh database
        $query = "INSERT INTO kependudukan (nama, tempat_lahir, tanggal_lahir, nik, 
                 jenis_kelamin, status, usia, no_kk, rt, rw, dusun, alamat, status_hidup,
                 pendidikan_terakhir, pekerjaan) 
                 VALUES (:nama, :tempat_lahir, :tanggal_lahir, :nik, 
                 :jenis_kelamin, :status, :usia, :no_kk, :rt, :rw, :dusun, :alamat, 
                 :status_hidup, :pendidikan_terakhir, :pekerjaan)";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':nama', $nama);
        $stmt->bindParam(':tempat_lahir', $tempat_lahir);
        $stmt->bindParam(':tanggal_lahir', $tanggal_lahir);
        $stmt->bindParam(':nik', $nik);
        $stmt->bindParam(':jenis_kelamin', $jenis_kelamin);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':usia', $usia);
        $stmt->bindParam(':no_kk', $no_kk);
        $stmt->bindParam(':rt', $rt);
        $stmt->bindParam(':rw', $rw);
        $stmt->bindParam(':dusun', $dusun);
        $stmt->bindParam(':alamat', $alamat);
        $stmt->bindParam(':status_hidup', $status_hidup);
        $stmt->bindParam(':pendidikan_terakhir', $pendidikan_terakhir);
        $stmt->bindParam(':pekerjaan', $pekerjaan);
        
        if ($stmt->execute()) {
            // Update nomor urut setelah menambah data
            updateNomorUrut($conn);
            $success_message = "Data berhasil ditambahkan!";
        } else {
            $error_message = "Gagal menambahkan data!";
        }
    }
}

// Fungsi untuk update nomor urut
function updateNomorUrut($conn) {
    $query = "SET @row_number = 0";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    $query = "UPDATE kependudukan SET no = (@row_number:=@row_number + 1) ORDER BY id";
    $stmt = $conn->prepare($query);
    $stmt->execute();
}

// Get filter parameters
$filter_dusun = $_GET['dusun'] ?? '';
$filter_rt = $_GET['rt'] ?? '';
$filter_rw = $_GET['rw'] ?? '';
$filter_status = $_GET['status_hidup'] ?? '';

// Konfigurasi pagination
$records_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $records_per_page;

// Build query with filters
$query = "SELECT SQL_CALC_FOUND_ROWS * FROM kependudukan WHERE 1=1";
$count_query = "SELECT COUNT(*) FROM kependudukan WHERE 1=1";
$params = [];

if (!empty($filter_dusun)) {
    $query .= " AND dusun = :dusun";
    $count_query .= " AND dusun = :dusun";
    $params[':dusun'] = $filter_dusun;
}

if (!empty($filter_rt)) {
    $query .= " AND rt = :rt";
    $count_query .= " AND rt = :rt";
    $params[':rt'] = $filter_rt;
}

if (!empty($filter_rw)) {
    $query .= " AND rw = :rw";
    $count_query .= " AND rw = :rw";
    $params[':rw'] = $filter_rw;
}

if (!empty($filter_status)) {
    $query .= " AND status_hidup = :status_hidup";
    $count_query .= " AND status_hidup = :status_hidup";
    $params[':status_hidup'] = $filter_status;
}

$query .= " ORDER BY no LIMIT :offset, :records_per_page";

$stmt = $conn->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':records_per_page', $records_per_page, PDO::PARAM_INT);
$stmt->execute();
$penduduk = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total records for pagination
$count_stmt = $conn->prepare($count_query);
foreach ($params as $key => $value) {
    if ($key !== ':offset' && $key !== ':records_per_page') {
        $count_stmt->bindValue($key, $value);
    }
}
$count_stmt->execute();
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $records_per_page);

// Get unique values for filters
$dusun_list = [];
$rt_list = [];
$rw_list = [];

$unique_query = "SELECT DISTINCT dusun, rt, rw FROM kependudukan WHERE dusun IS NOT NULL AND dusun != '' ORDER BY dusun, rt, rw";
$unique_stmt = $conn->prepare($unique_query);
$unique_stmt->execute();
$unique_data = $unique_stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($unique_data as $data) {
    if (!empty($data['dusun']) && !in_array($data['dusun'], $dusun_list)) {
        $dusun_list[] = $data['dusun'];
    }
    if (!empty($data['rt']) && !in_array($data['rt'], $rt_list)) {
        $rt_list[] = $data['rt'];
    }
    if (!empty($data['rw']) && !in_array($data['rw'], $rw_list)) {
        $rw_list[] = $data['rw'];
    }
}

// Get statistics for dashboard
$total_penduduk_query = "SELECT COUNT(*) FROM kependudukan";
$total_penduduk_stmt = $conn->prepare($total_penduduk_query);
$total_penduduk_stmt->execute();
$total_penduduk = $total_penduduk_stmt->fetchColumn();

$laki_laki_query = "SELECT COUNT(*) FROM kependudukan WHERE jenis_kelamin = 'Laki-laki'";
$laki_laki_stmt = $conn->prepare($laki_laki_query);
$laki_laki_stmt->execute();
$laki_laki = $laki_laki_stmt->fetchColumn();

$perempuan_query = "SELECT COUNT(*) FROM kependudukan WHERE jenis_kelamin = 'Perempuan'";
$perempuan_stmt = $conn->prepare($perempuan_query);
$perempuan_stmt->execute();
$perempuan = $perempuan_stmt->fetchColumn();

$hidup_query = "SELECT COUNT(*) FROM kependudukan WHERE status_hidup = 'Hidup'";
$hidup_stmt = $conn->prepare($hidup_query);
$hidup_stmt->execute();
$hidup = $hidup_stmt->fetchColumn();

$meninggal_query = "SELECT COUNT(*) FROM kependudukan WHERE status_hidup = 'Meninggal'";
$meninggal_stmt = $conn->prepare($meninggal_query);
$meninggal_stmt->execute();
$meninggal = $meninggal_stmt->fetchColumn();

// Update demografi table with new data
$updateDemografi = "UPDATE demografi SET total_penduduk = :total, laki_laki = :laki, perempuan = :perempuan WHERE id = 1";
$stmt = $conn->prepare($updateDemografi);
$stmt->bindParam(':total', $total_penduduk);
$stmt->bindParam(':laki', $laki_laki);
$stmt->bindParam(':perempuan', $perempuan);
$stmt->execute();

// Get data for editing
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $edit_query = "SELECT * FROM kependudukan WHERE id = :id";
    $edit_stmt = $conn->prepare($edit_query);
    $edit_stmt->bindParam(':id', $id);
    $edit_stmt->execute();
    $edit_data = $edit_stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <title>Data Kependudukan - Desa Winduaji</title>
    <link rel="shortcut icon" href="../assets/images/logo.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
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
        
        .header-icon {
            transition: transform 0.2s ease;
        }
        
        .header-icon:hover {
            transform: scale(1.1);
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        
        th {
            background-color: #f9fafb;
            font-weight: 600;
            color: #374151;
        }
        
        tr:hover {
            background-color: #f9fafb;
        }
        
        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-light);
        }
        
        .btn-danger {
            background-color: #ef4444;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #dc2626;
        }
        
        .btn-success {
            background-color: #10b981;
            color: white;
        }
        
        .btn-success:hover {
            background-color: #059669;
        }
        
        .btn-warning {
            background-color: #f59e0b;
            color: white;
        }
        
        .btn-warning:hover {
            background-color: #d97706;
        }
        
        .filter-section {
            background-color: #f9fafb;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #374151;
        }
        
        .form-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            transition: border-color 0.2s;
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(190, 24, 24, 0.1);
        }
        
        .form-select {
            width: 100%;
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background-color: white;
            transition: border-color 0.2s;
        }
        
        .form-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(190, 24, 24, 0.1);
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 100;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            border-radius: 8px;
            width: 80%;
            max-width: 700px;
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
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
            
            .modal-content {
                width: 95%;
                margin: 10% auto;
            }
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        
        .pagination a {
            color: var(--primary);
            padding: 8px 16px;
            text-decoration: none;
            transition: background-color 0.3s;
            border: 1px solid #ddd;
            margin: 0 4px;
            border-radius: 4px;
        }
        
        .pagination a.active {
            background-color: var(--primary);
            color: white;
            border: 1px solid var(--primary);
        }
        
        .pagination a:hover:not(.active) {
            background-color: #ddd;
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
            <a href="kependudukan.php" class="flex items-center px-4 py-3 text-white active">
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
    <div class="main-content flex-1 min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm py-4 px-6 flex justify-between items-center sticky top-0 z-40">
            <div class="flex items-center">
                <button id="sidebarToggle" class="md:hidden text-gray-600 header-icon mr-4">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <h2 class="text-xl font-semibold text-gray-800 hidden md:block">Data Kependudukan</h2>
            </div>
            
            <div class="flex items-center space-x-4">
                <div class="relative">
                    <div class="flex items-center space-x-4">
                        <div class="relative header-icon">
                            <button class="flex items-center focus:outline-none">
                                <i class="fas fa-bell text-gray-600 text-lg"></i>
                                <span class="absolute -top-1 -right-1 bg-red-600 text-white rounded-full text-xs w-4 h-4 flex items-center justify-center">3</span>
                            </button>
                        </div>
                        
                        <div class="relative">
                            <button class="flex items-center focus:outline-none space-x-2">
                                <div class="w-9 h-9 rounded-full gradient-bg flex items-center justify-center text-white font-medium shadow-md">
                                    <?php echo substr($_SESSION['admin_name'] ?? 'A', 0, 1); ?>
                                </div>
                                <span class="text-gray-700 hidden md:inline-block"><?php echo $_SESSION['admin_name'] ?? 'Admin'; ?></span>
                                <i class="fas fa-chevron-down text-gray-500 text-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="p-6 md:p-8 bg-gray-50 min-h-screen">
            <!-- Welcome Banner -->
            <div class="gradient-bg rounded-2xl p-6 text-white shadow-lg mb-8">
                <div class="flex flex-col md:flex-row items-center justify-between">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold mb-2">Data Kependudukan Desa Winduaji</h1>
                        <p class="text-red-100">Kelola data penduduk dengan fitur import, export, filter, dan edit data.</p>
                    </div>
                    <div class="mt-4 md:mt-0">
                        <div class="bg-white/20 py-2 px-4 rounded-lg">
                            <p class="text-sm">Total Penduduk: <?php echo $total_penduduk; ?> Jiwa</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
                <div class="card stats-card p-6 relative overflow-hidden">
                    <div class="flex items-center">
                        <div class="p-3 rounded-xl bg-red-100 text-red-600 mr-4">
                            <i class="fas fa-users fa-lg"></i>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-800"><?php echo $total_penduduk; ?></p>
                            <p class="text-sm text-gray-600">Total Penduduk</p>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-red-500"></div>
                </div>
                
                <div class="card stats-card p-6 relative overflow-hidden">
                    <div class="flex items-center">
                        <div class="p-3 rounded-xl bg-blue-100 text-blue-600 mr-4">
                            <i class="fas fa-male fa-lg"></i>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-800"><?php echo $laki_laki; ?></p>
                            <p class="text-sm text-gray-600">Laki-laki</p>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-blue-500"></div>
                </div>
                
                <div class="card stats-card p-6 relative overflow-hidden">
                    <div class="flex items-center">
                        <div class="p-3 rounded-xl bg-pink-100 text-pink-600 mr-4">
                            <i class="fas fa-female fa-lg"></i>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-800"><?php echo $perempuan; ?></p>
                            <p class="text-sm text-gray-600">Perempuan</p>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-pink-500"></div>
                </div>
                
                <div class="card stats-card p-6 relative overflow-hidden">
                    <div class="flex items-center">
                        <div class="p-3 rounded-xl bg-green-100 text-green-600 mr-4">
                            <i class="fas fa-heart fa-lg"></i>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-800"><?php echo $hidup; ?></p>
                            <p class="text-sm text-gray-600">Status Hidup</p>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-green-500"></div>
                </div>
            </div>
            
            <!-- Filter Section -->
            <div class="card p-6 mb-8">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Filter Data</h2>
                
                <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="form-group">
                        <label class="form-label">Dusun</label>
                        <select name="dusun" class="form-select">
                            <option value="">Semua Dusun</option>
                            <?php foreach ($dusun_list as $dusun): ?>
                                <option value="<?php echo $dusun; ?>" <?php echo ($filter_dusun == $dusun) ? 'selected' : ''; ?>>
                                    <?php echo $dusun; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">RT</label>
                        <select name="rt" class="form-select">
                            <option value="">Semua RT</option>
                            <?php foreach ($rt_list as $rt): ?>
                                <option value="<?php echo $rt; ?>" <?php echo ($filter_rt == $rt) ? 'selected' : ''; ?>>
                                    RT <?php echo $rt; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">RW</label>
                        <select name="rw" class="form-select">
                            <option value="">Semua RW</option>
                            <?php foreach ($rw_list as $rw): ?>
                                <option value="<?php echo $rw; ?>" <?php echo ($filter_rw == $rw) ? 'selected' : ''; ?>>
                                    RW <?php echo $rw; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Status Hidup</label>
                        <select name="status_hidup" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="Hidup" <?php echo ($filter_status == 'Hidup') ? 'selected' : ''; ?>>Hidup</option>
                            <option value="Meninggal" <?php echo ($filter_status == 'Meninggal') ? 'selected' : ''; ?>>Meninggal</option>
                        </select>
                    </div>
                    
                    <div class="md:col-span-4 flex justify-end space-x-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter mr-2"></i> Terapkan Filter
                        </button>
                        <a href="kependudukan.php" class="btn btn-warning">
                            <i class="fas fa-sync-alt mr-2"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
            
            <!-- Import/Export Section -->
            <div class="card p-6 mb-8">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Import/Export Data</h2>
                
                <?php if (isset($success_message)): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Import Form -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h3 class="text-lg font-medium text-gray-800 mb-3">Import Data</h3>
                        <p class="text-sm text-gray-600 mb-4">Unggah file CSV dengan format yang sesuai untuk mengimpor data kependudukan.</p>
                        
                        <form method="post" enctype="multipart/form-data">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">File CSV</label>
                                <input type="file" name="file" accept=".csv" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                                <p class="text-xs text-gray-500 mt-1">Format: Nama, Tempat Lahir, Tanggal Lahir, NIK, Jenis Kelamin, Status, No KK, RT, RW, Dusun, Alamat, Status Hidup, Pendidikan Terakhir, Pekerjaan</p>
                            </div>
                            
                            <button type="submit" name="import" class="btn btn-primary w-full">
                                <i class="fas fa-upload mr-2"></i> Import Data
                            </button>
                        </form>
                        
                        <div class="mt-4 p-3 bg-gray-100 rounded-md">
                            <p class="text-sm font-medium text-gray-700 mb-2">Download Template:</p>
                            <a href="template_kependudukan.csv" download class="inline-flex items-center text-red-600 hover:text-red-800 text-sm">
                                <i class="fas fa-download mr-2"></i> Template CSV
                            </a>
                        </div>
                    </div>
                    
                    <!-- Export Form -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h3 class="text-lg font-medium text-gray-800 mb-3">Export Data</h3>
                        <p class="text-sm text-gray-600 mb-4">Unduh data kependudukan dalam format CSV untuk keperluan backup atau analisis.</p>
                        
                        <form method="post">
                            <button type="submit" name="export" class="btn btn-success w-full">
                                <i class="fas fa-download mr-2"></i> Export Data
                            </button>
                        </form>
                        
                        <div class="mt-6">
                            <h4 class="text-md font-medium text-gray-700 mb-2">Statistik Data:</h4>
                            <ul class="text-sm text-gray-600 space-y-1">
                                <li>Total Data: <?php echo $total_penduduk; ?> record</li>
                                <li>Laki-laki: <?php echo $laki_laki; ?> orang</li>
                                <li>Perempuan: <?php echo $perempuan; ?> orang</li>
                                <li>Status Hidup: <?php echo $hidup; ?> orang</li>
                                <li>Status Meninggal: <?php echo $meninggal; ?> orang</li>
                                <li>Terakhir diupdate: <?php echo date('d/m/Y H:i'); ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-800">Data Penduduk</h2>
                <div class="flex space-x-2">
                    <button onclick="openModal('addModal')" class="btn btn-primary">
                        <i class="fas fa-plus mr-2"></i> Tambah Data
                    </button>
                </div>
            </div>
            
            <!-- Data Table -->
            <div class="card p-6">
                <div class="flex justify-between items-center mb-6">
                    <div class="text-sm text-gray-600">
                        Menampilkan <?php echo count($penduduk); ?> data
                        <?php if ($filter_dusun || $filter_rt || $filter_rw || $filter_status): ?>
                            (difilter)
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if (count($penduduk) > 0): ?>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>NIK</th>
                                    <th>TTL</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Status</th>
                                    <th>Dusun</th>
                                    <th>RT/RW</th>
                                    <th>Status Hidup</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($penduduk as $index => $data): ?>
                                    <tr>
                                        <td><?php echo $data['no'] ?? (($page - 1) * $records_per_page + $index + 1); ?></td>
                                        <td><?php echo htmlspecialchars($data['nama']); ?></td>
                                        <td><?php echo htmlspecialchars($data['nik']); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($data['tempat_lahir']); ?>, 
                                            <?php echo date('d/m/Y', strtotime($data['tanggal_lahir'])); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($data['jenis_kelamin']); ?></td>
                                        <td><?php echo htmlspecialchars($data['status']); ?></td>
                                        <td><?php echo htmlspecialchars($data['dusun']); ?></td>
                                        <td><?php echo $data['rt']; ?>/<?php echo $data['rw']; ?></td>
                                        <td>
                                            <span class="px-2 py-1 rounded-full text-xs <?php echo $data['status_hidup'] == 'Hidup' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                                <?php echo htmlspecialchars($data['status_hidup']); ?>
                                            </span>
                                        </td>
                                        <td class="flex space-x-2">
                                            <button onclick="openEditModal(<?php echo $data['id']; ?>)" class="text-blue-600 hover:text-blue-800">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="?delete=<?php echo $data['id']; ?>" 
                                               onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')"
                                               class="text-red-600 hover:text-red-800">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="pagination mt-6">
                        <?php if ($page > 1): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>">&laquo; First</a>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">&lsaquo; Prev</a>
                        <?php endif; ?>

                        <?php
                        // Tampilkan maksimal 5 halaman di sekitar halaman aktif
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $start_page + 4);
                        $start_page = max(1, $end_page - 4);
                        
                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" class="<?php echo $i == $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">Next &rsaquo;</a>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $total_pages])); ?>">Last &raquo;</a>
                        <?php endif; ?>
                    </div>

                    <div class="text-center text-sm text-gray-600 mt-4">
                        Halaman <?php echo $page; ?> dari <?php echo $total_pages; ?> | 
                        Total <?php echo $total_records; ?> data
                    </div>
                <?php else: ?>
                    <div class="text-center py-8">
                        <i class="fas fa-users text-4xl text-gray-300 mb-3"></i>
                        <p class="text-gray-500">Belum ada data kependudukan.</p>
                        <p class="text-gray-500 text-sm mt-1">Gunakan fitur import atau tambah data untuk menambahkan data.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
        
        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 py-4 px-8">
            <div class="flex flex-col md:flex-row items-center justify-between">
                <p class="text-sm text-gray-600">© 2023 Desa Winduaji. All rights reserved.</p>
                <p class="text-sm text-gray-600 mt-2 md:mt-0">Version 1.2.0 | Last updated: <?php echo date('d M Y'); ?></p>
            </div>
        </footer>
    </div>

    <!-- Add Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('addModal')">&times;</span>
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Tambah Data Penduduk</h2>
            
            <form method="post" action="">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group md:col-span-2">
                        <p class="text-sm text-gray-600 bg-blue-50 p-2 rounded-md">
                            <i class="fas fa-info-circle mr-1"></i> Nomor urut akan diisi otomatis oleh sistem.
                        </p>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Nama <span class="text-red-600">*</span></label>
                        <input type="text" name="nama" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Tempat Lahir <span class="text-red-600">*</span></label>
                        <input type="text" name="tempat_lahir" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Tanggal Lahir <span class="text-red-600">*</span></label>
                        <input type="date" name="tanggal_lahir" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">NIK <span class="text-red-600">*</span></label>
                        <input type="text" name="nik" class="form-input" required maxlength="16" pattern="[0-9]{16}" title="NIK harus 16 digit angka">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Jenis Kelamin <span class="text-red-600">*</span></label>
                        <select name="jenis_kelamin" class="form-select" required>
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="Laki-laki">Laki-laki</option>
                            <option value="Perempuan">Perempuan</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Pilih Status</option>
                            <option value="Belum Kawin">Belum Kawin</option>
                            <option value="Kawin">Kawin</option>
                            <option value="Cerai">Cerai</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">No KK</label>
                        <input type="text" name="no_kk" class="form-input" maxlength="20" pattern="[0-9]{16}" title="No KK harus 16 digit angka">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">RT</label>
                        <input type="number" name="rt" class="form-input" min="1">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">RW</label>
                        <input type="number" name="rw" class="form-input" min="1">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Dusun</label>
                        <input type="text" name="dusun" class="form-input">
                    </div>
                    
                    <div class="form-group md:col-span-2">
                        <label class="form-label">Alamat</label>
                        <textarea name="alamat" class="form-input" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Status Hidup</label>
                        <select name="status_hidup" class="form-select">
                            <option value="Hidup">Hidup</option>
                            <option value="Meninggal">Meninggal</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Pendidikan Terakhir</label>
                        <select name="pendidikan_terakhir" class="form-select">
                            <option value="">Pilih Pendidikan</option>
                            <option value="SD">SD</option>
                            <option value="SMP">SMP</option>
                            <option value="SMA">SMA</option>
                            <option value="Diploma">Diploma</option>
                            <option value="S1">S1</option>
                            <option value="S2">S2</option>
                            <option value="S3">S3</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Pekerjaan</label>
                        <input type="text" name="pekerjaan" class="form-input">
                    </div>
                </div>
                
                <div class="flex justify-end space-x-2 mt-6">
                    <button type="button" onclick="closeModal('addModal')" class="btn btn-warning">
                        Batal
                    </button>
                    <button type="submit" name="add" class="btn btn-primary">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('editModal')">&times;</span>
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Edit Data Penduduk</h2>
            
            <?php if ($edit_data): ?>
            <form method="post" action="">
                <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">No</label>
                        <input type="text" class="form-input" value="<?php echo $edit_data['no']; ?>" disabled>
                        <p class="text-xs text-gray-500 mt-1">Nomor urut diatur otomatis oleh sistem</p>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Nama <span class="text-red-600">*</span></label>
                        <input type="text" name="nama" class="form-input" value="<?php echo $edit_data['nama']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Tempat Lahir <span class="text-red-600">*</span></label>
                        <input type="text" name="tempat_lahir" class="form-input" value="<?php echo $edit_data['tempat_lahir']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Tanggal Lahir <span class="text-red-600">*</span></label>
                        <input type="date" name="tanggal_lahir" class="form-input" value="<?php echo $edit_data['tanggal_lahir']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">NIK <span class="text-red-600">*</span></label>
                        <input type="text" name="nik" class="form-input" value="<?php echo $edit_data['nik']; ?>" required maxlength="16" pattern="[0-9]{16}" title="NIK harus 16 digit angka">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Jenis Kelamin <span class="text-red-600">*</span></label>
                        <select name="jenis_kelamin" class="form-select" required>
                            <option value="Laki-laki" <?php echo ($edit_data['jenis_kelamin'] == 'Laki-laki') ? 'selected' : ''; ?>>Laki-laki</option>
                            <option value="Perempuan" <?php echo ($edit_data['jenis_kelamin'] == 'Perempuan') ? 'selected' : ''; ?>>Perempuan</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="Belum Kawin" <?php echo ($edit_data['status'] == 'Belum Kawin') ? 'selected' : ''; ?>>Belum Kawin</option>
                            <option value="Kawin" <?php echo ($edit_data['status'] == 'Kawin') ? 'selected' : ''; ?>>Kawin</option>
                            <option value="Cerai" <?php echo ($edit_data['status'] == 'Cerai') ? 'selected' : ''; ?>>Cerai</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">No KK</label>
                        <input type="text" name="no_kk" class="form-input" value="<?php echo $edit_data['no_kk']; ?>" maxlength="20" pattern="[0-9]{16}" title="No KK harus 16 digit angka">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">RT</label>
                        <input type="number" name="rt" class="form-input" value="<?php echo $edit_data['rt']; ?>" min="1">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">RW</label>
                        <input type="number" name="rw" class="form-input" value="<?php echo $edit_data['rw']; ?>" min="1">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Dusun</label>
                        <input type="text" name="dusun" class="form-input" value="<?php echo $edit_data['dusun']; ?>">
                    </div>
                    
                    <div class="form-group md:col-span-2">
                        <label class="form-label">Alamat</label>
                        <textarea name="alamat" class="form-input" rows="3"><?php echo $edit_data['alamat']; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Status Hidup</label>
                        <select name="status_hidup" class="form-select">
                            <option value="Hidup" <?php echo ($edit_data['status_hidup'] == 'Hidup') ? 'selected' : ''; ?>>Hidup</option>
                            <option value="Meninggal" <?php echo ($edit_data['status_hidup'] == 'Meninggal') ? 'selected' : ''; ?>>Meninggal</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Pendidikan Terakhir</label>
                        <select name="pendidikan_terakhir" class="form-select">
                            <option value="SD" <?php echo ($edit_data['pendidikan_terakhir'] == 'SD') ? 'selected' : ''; ?>>SD</option>
                            <option value="SMP" <?php echo ($edit_data['pendidikan_terakhir'] == 'SMP') ? 'selected' : ''; ?>>SMP</option>
                            <option value="SMA" <?php echo ($edit_data['pendidikan_terakhir'] == 'SMA') ? 'selected' : ''; ?>>SMA</option>
                            <option value="Diploma" <?php echo ($edit_data['pendidikan_terakhir'] == 'Diploma') ? 'selected' : ''; ?>>Diploma</option>
                            <option value="S1" <?php echo ($edit_data['pendidikan_terakhir'] == 'S1') ? 'selected' : ''; ?>>S1</option>
                            <option value="S2" <?php echo ($edit_data['pendidikan_terakhir'] == 'S2') ? 'selected' : ''; ?>>S2</option>
                            <option value="S3" <?php echo ($edit_data['pendidikan_terakhir'] == 'S3') ? 'selected' : ''; ?>>S3</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Pekerjaan</label>
                        <input type="text" name="pekerjaan" class="form-input" value="<?php echo $edit_data['pekerjaan']; ?>">
                    </div>
                </div>
                
                <div class="flex justify-end space-x-2 mt-6">
                    <button type="button" onclick="closeModal('editModal')" class="btn btn-warning">
                        Batal
                    </button>
                    <button type="submit" name="update" class="btn btn-primary">
                        Perbarui
                    </button>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Toggle sidebar on mobile
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
        
        // Add active class to current page in navigation
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split('/').pop();
            const navLinks = document.querySelectorAll('.sidebar a');
            
            navLinks.forEach(link => {
                const linkPage = link.getAttribute('href');
                if (linkPage === currentPage) {
                    link.classList.add('active');
                }
            });
            
            // Check if there's an edit parameter in URL
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('edit')) {
                openEditModal(urlParams.get('edit'));
            }
        });
        
        // Modal functions
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
            // Remove edit parameter from URL
            if (modalId === 'editModal') {
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        }
        
        function openEditModal(id) {
            window.location.href = 'kependudukan.php?edit=' + id;
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modals = document.getElementsByClassName('modal');
            for (let i = 0; i < modals.length; i++) {
                if (event.target == modals[i]) {
                    modals[i].style.display = 'none';
                    // Remove edit parameter from URL
                    window.history.replaceState({}, document.title, window.location.pathname);
                }
            }
        }
    </script>
</body>
</html>