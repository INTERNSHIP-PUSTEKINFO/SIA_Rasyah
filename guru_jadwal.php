<?php
session_start();
include 'koneksi.php';

// Cek apakah user adalah guru
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'guru') {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$guru_id = $user['id'];

// Query untuk mengambil jadwal pelajaran berdasarkan guru yang login
// Query ini telah disederhanakan agar lebih efisien dan sesuai dengan skema database
$query = "
    SELECT 
        jp.*, 
        k.nama_kelas,
        m.nama_mapel
    FROM jadwal_pelajaran jp
    JOIN kelas k ON jp.kelas_id = k.id
    JOIN mapel m ON jp.mapel_id = m.id
    WHERE jp.guru_id = '$guru_id'
    ORDER BY FIELD(jp.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'), jp.jam_ke ASC
";

$result = mysqli_query($koneksi, $query);

if (!$result) {
    die("Query Error: " . mysqli_error($koneksi));
}

// Mengelompokkan jadwal berdasarkan hari
$jadwal_per_hari = [];
while ($row = mysqli_fetch_assoc($result)) {
    $jadwal_per_hari[$row['hari']][] = $row;
}

$hari_urut = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Mengajar</title>
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
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
            border-radius: 8px;
            font-weight: 500;
            padding: 10px 15px;
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
        .jadwal-header {
            background-color: #007bff;
            color: #fff;
            padding: 15px 20px;
            font-weight: 600;
            font-size: 1.25rem;
            border-radius: 10px 10px 0 0;
            margin-top: 20px;
        }
        .card-jadwal {
            margin-bottom: 20px;
            border-radius: 15px;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="dashboard_guru.php">Akademik SMK</a>
        <div class="d-flex align-items-center">
            <a class="nav-link" href="dashboard_guru.php">Dashboard</a>
            <a class="nav-link" href="logout.php">Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h3><i class="fas fa-calendar-alt me-2"></i>Jadwal Mengajar Anda</h3>
    <div class="d-flex mb-4">
        <a href="dashboard_guru.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
    </div>

    <?php if (empty($jadwal_per_hari)): ?>
        <div class="alert alert-info text-center" role="alert">
            Belum ada jadwal mengajar yang ditentukan.
        </div>
    <?php else: ?>
        <?php foreach ($hari_urut as $hari): ?>
            <?php if (isset($jadwal_per_hari[$hari])): ?>
                <div class="card card-jadwal">
                    <div class="jadwal-header">
                        <?= $hari; ?>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="text-center" style="display: none;">
                                <tr>
                                    <th>Jam Ke</th>
                                    <th>Mata Pelajaran</th>
                                    <th>Kelas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($jadwal_per_hari[$hari] as $jadwal): ?>
                                    <tr>
                                        <td class="text-center" style="width: 15%;"><?= htmlspecialchars($jadwal['jam_ke']); ?></td>
                                        <td><?= htmlspecialchars($jadwal['nama_mapel']); ?></td>
                                        <td><?= htmlspecialchars($jadwal['nama_kelas']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
