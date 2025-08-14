<?php
session_start();
include 'koneksi.php';

// Cek akses admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Cek parameter ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: admin_kelas.php");
    exit;
}

$id = $_GET['id'];
$pesan = "";

// Ambil data kelas berdasarkan ID
$stmt = mysqli_prepare($koneksi, "SELECT * FROM kelas WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$kelas = mysqli_fetch_assoc($result);

// Cek jika data tidak ditemukan
if (!$kelas) {
    header("Location: admin_kelas.php");
    exit;
}

// Ambil data jurusan untuk dropdown
$jurusan_query = mysqli_query($koneksi, "SELECT id, nama_jurusan FROM jurusan");

// Proses update saat form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_kelas = trim($_POST['nama_kelas']);
    $tingkat = $_POST['tingkat'];
    $jurusan_id = $_POST['jurusan_id'];

    if (!empty($nama_kelas) && !empty($tingkat) && !empty($jurusan_id)) {
        $update = mysqli_prepare($koneksi, "UPDATE kelas SET nama_kelas = ?, tingkat = ?, jurusan_id = ? WHERE id = ?");
        mysqli_stmt_bind_param($update, "ssii", $nama_kelas, $tingkat, $jurusan_id, $id);
        $berhasil = mysqli_stmt_execute($update);

        if ($berhasil) {
            // Redirect dengan pesan sukses
            header("Location: admin_kelas.php?msg=update_success");
            exit;
        } else {
            $pesan = "Gagal mengupdate kelas: " . mysqli_error($koneksi);
        }
    } else {
        $pesan = "Semua field wajib diisi.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Kelas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { background-color: #f0f2f5; font-family: 'Poppins', sans-serif; }
        .navbar {
            background-color: #ffffff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border-bottom: 3px solid #007bff;
        }
        .navbar .navbar-brand { font-weight: 700; color: #007bff !important; font-size: 1.5rem; }
        .navbar .nav-link { color: #555 !important; font-weight: 500; margin-right: 15px; transition: color 0.3s ease-in-out; }
        .navbar .nav-link:hover { color: #007bff !important; }
        .container { max-width: 800px; }
        .card { 
            border: none; 
            border-radius: 15px; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.1); 
            padding: 20px;
        }
        h3 {
            font-weight: 700;
            color: #2c3e50;
            border-left: 5px solid #007bff;
            padding-left: 15px;
            margin-bottom: 25px;
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
        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
            border-radius: 8px;
            font-weight: 500;
            padding: 10px 15px;
            transition: background-color 0.3s ease;
        }
        .btn-success:hover {
            background-color: #218838;
            border-color: #218838;
        }
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
            border-radius: 8px;
            font-weight: 500;
            padding: 10px 15px;
            transition: background-color 0.3s ease;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #5a6268;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="dashboard_admin.php">Akademik SMK</a>
        <div class="d-flex align-items-center">
            <a class="nav-link" href="dashboard_admin.php">Dashboard</a>
            <a class="nav-link" href="logout.php">Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h3><i class="fas fa-edit me-2"></i> Edit Data Kelas</h3>
    <div class="card p-4">
        <?php if (!empty($pesan)): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($pesan) ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="mb-3">
                <label for="nama_kelas" class="form-label">Nama Kelas</label>
                <input type="text" name="nama_kelas" id="nama_kelas" value="<?= htmlspecialchars($kelas['nama_kelas']) ?>" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="tingkat" class="form-label">Tingkat</label>
                <select name="tingkat" id="tingkat" class="form-select" required>
                    <option value="X" <?= $kelas['tingkat'] === 'X' ? 'selected' : '' ?>>X</option>
                    <option value="XI" <?= $kelas['tingkat'] === 'XI' ? 'selected' : '' ?>>XI</option>
                    <option value="XII" <?= $kelas['tingkat'] === 'XII' ? 'selected' : '' ?>>XII</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="jurusan_id" class="form-label">Jurusan</label>
                <select name="jurusan_id" id="jurusan_id" class="form-select" required>
                    <?php while ($jurusan = mysqli_fetch_assoc($jurusan_query)): ?>
                        <option value="<?= $jurusan['id'] ?>" <?= $jurusan['id'] == $kelas['jurusan_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($jurusan['nama_jurusan']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-success me-2"><i class="fas fa-save me-1"></i> Simpan Perubahan</button>
            <a href="admin_kelas.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>