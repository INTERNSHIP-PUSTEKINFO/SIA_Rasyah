<?php
session_start();
include 'koneksi.php';

// Cek apakah user adalah admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Ambil data kelas, mapel, dan guru
$kelasResult = mysqli_query($koneksi, "SELECT id, nama_kelas FROM kelas ORDER BY nama_kelas");
$mapelResult = mysqli_query($koneksi, "SELECT id, nama_mapel FROM mapel ORDER BY nama_mapel");

// Query yang diperbaiki untuk mengambil user_id guru, bukan NIP
$guruResult = mysqli_query($koneksi, "
    SELECT u.id, u.nama 
    FROM guru g
    JOIN user u ON g.user_id = u.id
    ORDER BY u.nama
");

$pesan = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $kelas_id = $_POST['kelas_id'];
    $mapel_id = $_POST['mapel_id'];
    $guru_id = $_POST['guru_id']; // user_id
    $hari = $_POST['hari'];
    $jam_ke = $_POST['jam_ke'];

    if ($kelas_id && $mapel_id && $guru_id && $hari && $jam_ke) {
        $query = "INSERT INTO jadwal_pelajaran (kelas_id, mapel_id, guru_id, hari, jam_ke) 
                  VALUES ('$kelas_id', '$mapel_id', '$guru_id', '$hari', '$jam_ke')";
        $sukses = mysqli_query($koneksi, $query);

        if ($sukses) {
            header("Location: admin_jadwal.php?msg=berhasil");
            exit;
        } else {
            $pesan = "Gagal menambahkan jadwal: " . mysqli_error($koneksi);
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
    <title>Tambah Jadwal Pelajaran</title>
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
        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
            border-radius: 8px;
            font-weight: 500;
        }
        .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
            border-radius: 8px;
            font-weight: 500;
        }
        .form-control, .form-select {
            border-radius: 8px;
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
    <h3><i class="fas fa-plus-circle me-2"></i>Tambah Jadwal Pelajaran</h3>
    <div class="card">
        <?php if ($pesan): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($pesan); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Kelas</label>
                <select name="kelas_id" class="form-select" required>
                    <option value="">-- Pilih Kelas --</option>
                    <?php while($k = mysqli_fetch_assoc($kelasResult)): ?>
                        <option value="<?= htmlspecialchars($k['id']) ?>"><?= htmlspecialchars($k['nama_kelas']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Mata Pelajaran</label>
                <select name="mapel_id" class="form-select" required>
                    <option value="">-- Pilih Mapel --</option>
                    <?php while($m = mysqli_fetch_assoc($mapelResult)): ?>
                        <option value="<?= htmlspecialchars($m['id']) ?>"><?= htmlspecialchars($m['nama_mapel']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Guru</label>
                <select name="guru_id" class="form-select" required>
                    <option value="">-- Pilih Guru --</option>
                    <?php while($g = mysqli_fetch_assoc($guruResult)): ?>
                        <option value="<?= htmlspecialchars($g['id']) ?>"><?= htmlspecialchars($g['nama']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Hari</label>
                <select name="hari" class="form-select" required>
                    <option value="">-- Pilih Hari --</option>
                    <?php
                    $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                    foreach ($hariList as $hari) {
                        echo "<option value='$hari'>$hari</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Jam Ke</label>
                <input type="number" name="jam_ke" class="form-control" required min="1">
            </div>

            <button type="submit" class="btn btn-success me-2"><i class="fas fa-save me-1"></i> Simpan</button>
            <a href="admin_jadwal.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
