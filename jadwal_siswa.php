<?php
session_start();
include 'koneksi.php';

// Validasi role dan sesi
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$user_id = $user['id'];

// Mengambil data siswa untuk mendapatkan kelas_id
$stmt_siswa = mysqli_prepare($koneksi, "
    SELECT s.kelas_id 
    FROM siswa s
    WHERE s.user_id = ?
");
mysqli_stmt_bind_param($stmt_siswa, "i", $user_id);
mysqli_stmt_execute($stmt_siswa);
$result_siswa = mysqli_stmt_get_result($stmt_siswa);
$siswa = mysqli_fetch_assoc($result_siswa);
$kelas_id = $siswa['kelas_id'] ?? null;

$jadwal_per_hari = []; // Menginisialisasi array untuk jadwal per hari
if ($kelas_id) {
    // Mengambil data jadwal pelajaran berdasarkan kelas_id siswa
    $stmt_jadwal = mysqli_prepare($koneksi, "
        SELECT 
            jp.hari,
            jp.jam_ke,
            m.nama_mapel,
            u.nama AS nama_guru
        FROM jadwal_pelajaran jp
        JOIN mapel m ON jp.mapel_id = m.id
        JOIN user u ON jp.guru_id = u.id
        WHERE jp.kelas_id = ?
        ORDER BY FIELD(jp.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'), jp.jam_ke ASC
    ");
    mysqli_stmt_bind_param($stmt_jadwal, "i", $kelas_id);
    mysqli_stmt_execute($stmt_jadwal);
    $result_jadwal = mysqli_stmt_get_result($stmt_jadwal);
    
    // Mengelompokkan data berdasarkan hari
    while ($row = mysqli_fetch_assoc($result_jadwal)) {
        $hari = $row['hari'];
        if (!isset($jadwal_per_hari[$hari])) {
            $jadwal_per_hari[$hari] = [];
        }
        $jadwal_per_hari[$hari][] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Pelajaran - Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* CSS yang sama seperti sebelumnya */
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
            margin-bottom: 20px; /* Menambah margin bawah untuk setiap card jadwal */
        }
        .table-custom {
            border-radius: 12px;
            overflow: hidden;
        }
        .table-custom thead {
            background-color: #007bff;
            color: #fff;
        }
        .table-custom th, .table-custom td {
            padding: 15px;
            vertical-align: middle;
        }
        .table-custom tbody tr:hover {
            background-color: #f1f1f1;
        }
        .btn-back {
            background-color: #6c757d;
            border-color: #6c757d;
            color: #fff;
        }
        .btn-back:hover {
            background-color: #5a6268;
            border-color: #545b62;
        }
        /* Tambahan styling untuk judul hari */
        .day-heading {
            background-color: #007bff;
            color: #fff;
            padding: 15px 20px;
            margin-bottom: 0;
            border-radius: 12px 12px 0 0;
            font-size: 1.25rem;
        }
        .card-with-heading {
            padding: 0; /* Hapus padding pada card utama karena sudah ada di dalam tabel */
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg shadow">
    <div class="container">
        <a class="navbar-brand" href="dashboard_siswa.php">Akademik SMK</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <span class="nav-user"><i class="fas fa-user-graduate"></i><span><?= htmlspecialchars($user['nama']); ?></span></span>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard_siswa.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h2><i class="fas fa-calendar-alt me-2"></i> Jadwal Pelajaran</h2>
    
    <?php if ($kelas_id && !empty($jadwal_per_hari)): ?>
        <?php foreach ($jadwal_per_hari as $hari => $jadwal_hari_ini): ?>
            <div class="card p-0 mb-4">
                <h5 class="day-heading"><i class="fas fa-calendar-day me-2"></i><?= htmlspecialchars($hari); ?></h5>
                <div class="table-responsive">
                    <table class="table table-hover table-custom m-0">
                        <thead>
                            <tr>
                                <th>Jam ke-</th>
                                <th>Mata Pelajaran</th>
                                <th>Guru Pengajar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($jadwal_hari_ini as $data): ?>
                                <tr>
                                    <td><?= htmlspecialchars($data['jam_ke']); ?></td>
                                    <td><?= htmlspecialchars($data['nama_mapel']); ?></td>
                                    <td><?= htmlspecialchars($data['nama_guru']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="card p-4">
            <div class="alert alert-info text-center m-0" role="alert">
                <i class="fas fa-info-circle me-2"></i>Belum ada jadwal yang tersedia untuk kelas Anda.
            </div>
        </div>
    <?php endif; ?>
    
    <a href="dashboard_siswa.php" class="btn btn-back mt-3"><i class="fas fa-arrow-left me-2"></i> Kembali</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>