<?php
session_start();
include 'koneksi.php';

// Periksa apakah pengguna sudah login dan memiliki peran 'admin'
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Ambil ID dari URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: admin_jurusan.php");
    exit;
}

$id = $_GET['id'];
$status_message = "";
$alert_type = "";

// Ambil data jurusan berdasarkan ID
$query = "SELECT * FROM jurusan WHERE id = ?";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

// Jika data tidak ditemukan
if (!$data) {
    header("Location: admin_jurusan.php");
    exit;
}

// Proses update saat form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_jurusan = trim($_POST['kode_jurusan']);
    $nama_jurusan = trim($_POST['nama_jurusan']);

    if (!empty($kode_jurusan) && !empty($nama_jurusan)) {
        $update = mysqli_prepare($koneksi, "UPDATE jurusan SET kode_jurusan = ?, nama_jurusan = ? WHERE id = ?");
        mysqli_stmt_bind_param($update, "ssi", $kode_jurusan, $nama_jurusan, $id);
        $berhasil = mysqli_stmt_execute($update);

        if ($berhasil) {
            header("Location: admin_jurusan.php?msg=edit_sukses");
            exit;
        } else {
            $status_message = "Gagal mengupdate jurusan: " . mysqli_error($koneksi);
            $alert_type = 'danger';
        }
    } else {
        $status_message = "Kode dan Nama Jurusan tidak boleh kosong.";
        $alert_type = 'warning';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Jurusan - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { 
            background-color: #f0f2f5; 
            font-family: 'Poppins', sans-serif; 
            color: #333;
        }
        .navbar {
            background-color: #ffffff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border-bottom: 3px solid #007bff;
        }
        .navbar .navbar-brand {
            font-weight: bold;
            color: #007bff !important;
            font-size: 1.5rem;
        }
        .nav-user {
            color: #007bff;
            font-weight: 600;
            margin-right: 20px;
            display: flex;
            align-items: center;
        }
        .nav-user span {
            margin-left: 8px;
        }
        .nav-link {
            color: #555 !important;
            font-weight: 500;
            transition: color 0.3s ease-in-out;
        }
        .nav-link:hover {
            color: #007bff !important;
        }
        .container h2 {
            font-weight: 700;
            color: #2c3e50;
            border-left: 5px solid #007bff;
            padding-left: 15px;
            margin-bottom: 25px;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            padding: 20px;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            border-radius: 8px;
            font-weight: 500;
            padding: 10px 15px;
            transition: background-color 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
            border-radius: 8px;
            font-weight: 500;
            padding: 10px 15px;
            color: #fff;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #5a6268;
            color: #fff;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="dashboard_admin.php">Akademik SMK</a>
        <div class="d-flex align-items-center">
            <span class="nav-user"><i class="fas fa-user-shield"></i><span><?= htmlspecialchars($_SESSION['user']['nama']); ?></span></span>
            <a class="nav-link" href="dashboard_admin.php">Dashboard</a>
            <a class="nav-link" href="logout.php">Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h2><i class="fas fa-edit me-2"></i>Edit Jurusan</h2>
    <div class="card p-4">
        <?php if (!empty($status_message)): ?>
            <div class="alert alert-<?= $alert_type ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($status_message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="mb-3">
                <label for="kode_jurusan" class="form-label">Kode Jurusan</label>
                <input type="text" name="kode_jurusan" id="kode_jurusan" value="<?= htmlspecialchars($data['kode_jurusan']) ?>" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="nama_jurusan" class="form-label">Nama Jurusan</label>
                <input type="text" name="nama_jurusan" id="nama_jurusan" value="<?= htmlspecialchars($data['nama_jurusan']) ?>" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Simpan Perubahan</button>
            <a href="admin_jurusan.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Kembali</a>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>