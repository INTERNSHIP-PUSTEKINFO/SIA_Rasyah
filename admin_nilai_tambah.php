<?php
session_start();
include 'koneksi.php';

// Cek akses admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Ambil data siswa dan mapel
$siswaResult = mysqli_query($koneksi, "SELECT s.nis, u.nama FROM siswa s JOIN user u ON s.user_id = u.id ORDER BY u.nama ASC");
$mapelResult = mysqli_query($koneksi, "SELECT * FROM mapel ORDER BY nama_mapel ASC");

$pesan = "";

if (isset($_POST['simpan'])) {
    $siswa_nis = $_POST['siswa_nis'];
    $mapel_id = $_POST['mapel_id'];
    $nilai_tugas = $_POST['nilai_tugas'];
    $nilai_uts = $_POST['nilai_uts'];
    $nilai_uas = $_POST['nilai_uas'];
    $semester = $_POST['semester'];
    $tahun_ajaran = $_POST['tahun_ajaran'];

    // Hitung nilai akhir
    $nilai_akhir = round(($nilai_tugas + $nilai_uts + $nilai_uas) / 3, 2);

    // Simpan ke DB
    $query = "INSERT INTO nilai (siswa_nis, mapel_id, nilai_tugas, nilai_uts, nilai_uas, nilai_akhir, semester, tahun_ajaran)
              VALUES ('$siswa_nis', '$mapel_id', '$nilai_tugas', '$nilai_uts', '$nilai_uas', '$nilai_akhir', '$semester', '$tahun_ajaran')";
    $simpan = mysqli_query($koneksi, $query);

    if ($simpan) {
        header("Location: admin_nilai.php?msg=berhasil");
        exit;
    } else {
        $pesan = "❌ Gagal menyimpan nilai: " . mysqli_error($koneksi);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Nilai</title>
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
            padding: 30px;
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
            padding: 10px 20px;
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
            padding: 10px 20px;
        }
        .form-control, .form-select {
            border-radius: 8px;
            padding: 12px;
        }
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.25);
            border-color: #007bff;
        }
        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
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
    <h3><i class="fas fa-plus-square me-2"></i>Tambah Nilai Siswa</h3>
    <a href="admin_nilai.php" class="btn btn-secondary mb-4"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
    
    <?php if (!empty($pesan)): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($pesan); ?></div>
    <?php endif; ?>

    <div class="card">
        <form method="post">
            <div class="mb-3">
                <label for="siswa_nis" class="form-label">Nama Siswa</label>
                <select name="siswa_nis" id="siswa_nis" class="form-select" required>
                    <option value="">-- Pilih Siswa --</option>
                    <?php while ($s = mysqli_fetch_assoc($siswaResult)): ?>
                        <option value="<?= htmlspecialchars($s['nis']); ?>"><?= htmlspecialchars($s['nama']); ?> (<?= htmlspecialchars($s['nis']); ?>)</option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="mapel_id" class="form-label">Mata Pelajaran</label>
                <select name="mapel_id" id="mapel_id" class="form-select" required>
                    <option value="">-- Pilih Mapel --</option>
                    <?php while ($m = mysqli_fetch_assoc($mapelResult)): ?>
                        <option value="<?= htmlspecialchars($m['id']); ?>"><?= htmlspecialchars($m['nama_mapel']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="nilai_tugas" class="form-label">Nilai Tugas</label>
                    <input type="number" step="0.01" name="nilai_tugas" id="nilai_tugas" class="form-control" required min="0" max="100">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="nilai_uts" class="form-label">Nilai UTS</label>
                    <input type="number" step="0.01" name="nilai_uts" id="nilai_uts" class="form-control" required min="0" max="100">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="nilai_uas" class="form-label">Nilai UAS</label>
                    <input type="number" step="0.01" name="nilai_uas" id="nilai_uas" class="form-control" required min="0" max="100">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="semester" class="form-label">Semester</label>
                    <select name="semester" id="semester" class="form-select" required>
                        <option value="">-- Pilih Semester --</option>
                        <option value="Ganjil">Ganjil</option>
                        <option value="Genap">Genap</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="tahun_ajaran" class="form-label">Tahun Ajaran</label>
                    <input type="text" name="tahun_ajaran" id="tahun_ajaran" class="form-control" placeholder="Contoh: 2024/2025" required>
                </div>
            </div>

            <div class="d-flex">
                <button type="submit" name="simpan" class="btn btn-success me-2"><i class="fas fa-save me-1"></i> Simpan</button>
                <a href="admin_nilai.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
