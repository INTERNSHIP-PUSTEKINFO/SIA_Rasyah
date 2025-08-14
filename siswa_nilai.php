<?php
session_start();
include 'koneksi.php';

// Cek apakah user sudah login dan perannya adalah siswa
// Jika tidak, redirect ke halaman login
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
    header("Location: login.php");
    exit;
}

// Mengambil user_id dari session
$user_id = mysqli_real_escape_string($koneksi, $_SESSION['user']['id']);

// Mendapatkan NIS siswa dari tabel 'siswa' menggunakan user_id
// Ini adalah langkah penting karena NIS tidak disimpan di session pada kode login Anda
$query_nis = "SELECT nis FROM siswa WHERE user_id = '$user_id'";
$result_nis = mysqli_query($koneksi, $query_nis);

if (!$result_nis || mysqli_num_rows($result_nis) === 0) {
    // Jika NIS tidak ditemukan, tampilkan pesan error atau redirect
    die("Data siswa tidak ditemukan.");
}

$data_siswa = mysqli_fetch_assoc($result_nis);
$nis_siswa_login = $data_siswa['nis'];

// Query untuk mengambil data nilai siswa berdasarkan NIS yang ditemukan
$query_nilai = "
    SELECT 
        n.*, 
        m.nama_mapel 
    FROM nilai n
    JOIN mapel m ON n.mapel_id = m.id
    WHERE n.siswa_nis = '$nis_siswa_login'
    ORDER BY n.tahun_ajaran DESC, n.semester ASC, m.nama_mapel ASC
";

$result_nilai = mysqli_query($koneksi, $query_nilai);

if (!$result_nilai) {
    die("Query Error: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nilai Akademik Saya</title>
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
        .container { max-width: 1200px; }
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
        .table {
            background-color: #fff;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .table thead tr th {
            background-color: #007bff;
            color: #fff;
            border-color: #007bff;
        }
        .table tbody tr:hover {
            background-color: #f1f5ff;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .no-data {
            text-align: center;
            padding: 20px;
            color: #888;
        }
        .data-siswa {
            background-color: #e9ecef;
            border-left: 4px solid #007bff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="dashboard_siswa.php">Akademik SMK</a>
        <div class="d-flex align-items-center">
            <a class="nav-link" href="dashboard_siswa.php">Dashboard</a>
            <a class="nav-link" href="logout.php">Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h3><i class="fas fa-chart-bar me-2"></i>Nilai Akademik Saya</h3>

    <div class="data-siswa">
        <p class="mb-0"><strong>Nama:</strong> <?= htmlspecialchars($_SESSION['user']['nama']); ?></p>
        <p class="mb-0"><strong>NIS:</strong> <?= htmlspecialchars($nis_siswa_login); ?></p>
    </div>
    
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-primary text-center">
                    <tr>
                        <th>No</th>
                        <th>Mata Pelajaran</th>
                        <th>Semester</th>
                        <th>Tahun Ajaran</th>
                        <th>Nilai Tugas</th>
                        <th>Nilai UTS</th>
                        <th>Nilai UAS</th>
                        <th>Nilai Akhir</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (mysqli_num_rows($result_nilai) > 0): $no = 1; while ($row = mysqli_fetch_assoc($result_nilai)): ?>
                    <tr>
                        <td class="text-center"><?= $no++; ?></td>
                        <td><?= htmlspecialchars($row['nama_mapel']); ?></td>
                        <td class="text-center"><?= htmlspecialchars($row['semester']); ?></td>
                        <td class="text-center"><?= htmlspecialchars($row['tahun_ajaran']); ?></td>
                        <td class="text-center"><?= htmlspecialchars($row['nilai_tugas']); ?></td>
                        <td class="text-center"><?= htmlspecialchars($row['nilai_uts']); ?></td>
                        <td class="text-center"><?= htmlspecialchars($row['nilai_uas']); ?></td>
                        <td class="text-center fw-bold"><?= htmlspecialchars($row['nilai_akhir']); ?></td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted no-data">Belum ada data nilai yang tersedia untuk Anda.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>