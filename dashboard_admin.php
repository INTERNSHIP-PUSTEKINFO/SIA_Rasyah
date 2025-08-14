<?php
/**
 * Dashboard Admin Akademik SMK
 * Mengacu pada struktur tabel di file SQL db_akademik.
 */
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];

// Ambil total data untuk setiap entitas
$counts = [];
$tables = [
    'user'              => "SELECT COUNT(*) AS total FROM user",
    'siswa'             => "SELECT COUNT(*) AS total FROM siswa",
    'guru'              => "SELECT COUNT(*) AS total FROM guru",
    'jurusan'           => "SELECT COUNT(*) AS total FROM jurusan",
    'kelas'             => "SELECT COUNT(*) AS total FROM kelas",
    'mapel'             => "SELECT COUNT(*) AS total FROM mapel",
    'jadwal_pelajaran'  => "SELECT COUNT(*) AS total FROM jadwal_pelajaran",
    'absensi'           => "SELECT COUNT(*) AS total FROM absensi",
    'nilai'             => "SELECT COUNT(*) AS total FROM nilai",
];

foreach ($tables as $key => $sql) {
    $res = mysqli_query($koneksi, $sql);
    $counts[$key] = ($res && $row = mysqli_fetch_assoc($res)) ? (int)$row['total'] : 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Dashboard Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { background: #f0f2f5; font-family: 'Poppins', sans-serif; }
        .navbar {
            background: #ffffff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border-bottom: 3px solid #007bff;
        }
        .navbar .navbar-brand { font-weight: 700; color: #007bff !important; }
        .navbar .nav-link { color: #555 !important; margin-right: 15px; transition: color 0.3s ease-in-out; }
        .navbar .nav-link:hover { color: #007bff !important; }
        .nav-user { color: #007bff; font-weight: 600; margin-right: 20px; display: flex; align-items: center; }
        .nav-user span { margin-left: 8px; }
        
        .sidebar {
            height: 100vh; position: fixed; top: 0; left: 0; width: 250px; background: #fff;
            border-right: 1px solid #e9ecef; padding-top: 80px; transition: all 0.3s;
        }
        .sidebar-brand { padding: 15px 20px; font-weight: 700; color: #007bff; font-size: 1.2rem; }
        .sidebar a {
            display: block; color: #555; text-decoration: none; padding: 15px 20px; border-left: 4px solid transparent;
            transition: all 0.2s ease; font-weight: 500;
        }
        .sidebar a:hover, .sidebar a.active {
            background: #e9f0ff; border-left-color: #007bff; color: #007bff;
        }

        .content { margin-left: 250px; padding: 90px 20px 30px 20px; transition: all 0.3s; }
        
        .card-stat {
            border: none; border-radius: 14px; color: #fff; padding: 25px; min-height: 120px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.1); transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
            position: relative; overflow: hidden;
        }
        .card-stat:hover { transform: translateY(-8px); box-shadow: 0 10px 30px rgba(0,0,0,0.15); }
        .card-stat .card-title { font-size: 1rem; font-weight: 600; margin-bottom: 4px; }
        .card-stat .card-value { font-size: 2.5rem; font-weight: 700; }
        .card-stat .icon-background {
            position: absolute; bottom: -20px; right: -20px; font-size: 6rem; opacity: 0.1;
        }

        .list-group-item { border-radius: 8px; transition: all 0.2s; }
        .list-group-item:hover { background: #e9f0ff; color: #007bff; transform: translateX(5px); }
        
        @media (max-width: 991.98px) {
            .sidebar { display: none; }
            .content { margin-left: 0; padding-top: 80px; }
            .navbar-brand, .nav-user { font-size: 1.1rem; }
            .navbar-nav { margin-top: 10px; }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg fixed-top shadow">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Akademik SMK — Admin</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="topNav">
            <span class="nav-user me-3"><i class="fas fa-shield-alt"></i><span><?= htmlspecialchars($user['nama']); ?></span></span>
            <ul class="navbar-nav">
                <li class="nav-item d-lg-none"><a class="nav-link" href="dashboard_admin.php">🏠 Dashboard</a></li>
                <li class="nav-item d-lg-none"><a class="nav-link" href="admin_user.php">👤 Manajemen User</a></li>
                <li class="nav-item d-lg-none"><a class="nav-link" href="admin_siswa.php">👨‍🎓 Data Siswa</a></li>
                <li class="nav-item d-lg-none"><a class="nav-link" href="admin_guru.php">👨‍🏫 Data Guru</a></li>
                <li class="nav-item d-lg-none"><a class="nav-link" href="admin_jurusan.php">🏷️ Jurusan</a></li>
                <li class="nav-item d-lg-none"><a class="nav-link" href="admin_kelas.php">🏫 Kelas</a></li>
                <li class="nav-item d-lg-none"><a class="nav-link" href="admin_mapel.php">📘 Mata Pelajaran</a></li>
                <li class="nav-item d-lg-none"><a class="nav-link" href="admin_jadwal.php">🗓️ Jadwal Pelajaran</a></li>
                <li class="nav-item d-lg-none"><a class="nav-link" href="admin_absensi.php">📝 Absensi</a></li>
                <li class="nav-item d-lg-none"><a class="nav-link" href="admin_nilai.php">📊 Nilai</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php" onclick="return confirm('Yakin ingin keluar?')">🚪 Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="sidebar d-none d-lg-block">
    <div class="sidebar-brand">Menu Admin</div>
    <a href="dashboard_admin.php" class="active"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a>
    <a href="admin_user.php"><i class="fas fa-users me-2"></i>Manajemen User</a>
    <a href="admin_siswa.php"><i class="fas fa-user-graduate me-2"></i>Data Siswa</a>
    <a href="admin_guru.php"><i class="fas fa-chalkboard-teacher me-2"></i>Data Guru</a>
    <a href="admin_jurusan.php"><i class="fas fa-sitemap me-2"></i>Jurusan</a>
    <a href="admin_kelas.php"><i class="fas fa-school me-2"></i>Kelas</a>
    <a href="admin_mapel.php"><i class="fas fa-book me-2"></i>Mata Pelajaran</a>
    <a href="admin_jadwal.php"><i class="fas fa-calendar-alt me-2"></i>Jadwal Pelajaran</a>
    <a href="admin_absensi.php"><i class="fas fa-clipboard-list me-2"></i>Absensi</a>
    <a href="admin_nilai.php"><i class="fas fa-chart-bar me-2"></i>Nilai</a>
</div>

<div class="content">
    <h2 class="mb-4">Dashboard Admin</h2>

    <div class="row g-4 mb-5">
        <div class="col-md-6 col-xl-3">
            <div class="card-stat" style="background:linear-gradient(45deg,#007bff,#00c6ff);">
                <div class="card-title">User</div>
                <div class="card-value"><?= $counts['user']; ?></div>
                <a href="admin_user.php" class="text-white text-decoration-underline small">Kelola User <i class="fas fa-arrow-right"></i></a>
                <i class="fas fa-users icon-background"></i>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card-stat" style="background:linear-gradient(45deg,#28a745,#2ecc71);">
                <div class="card-title">Siswa</div>
                <div class="card-value"><?= $counts['siswa']; ?></div>
                <a href="admin_siswa.php" class="text-white text-decoration-underline small">Kelola Siswa <i class="fas fa-arrow-right"></i></a>
                <i class="fas fa-user-graduate icon-background"></i>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card-stat" style="background:linear-gradient(45deg,#17a2b8,#3498db);">
                <div class="card-title">Guru</div>
                <div class="card-value"><?= $counts['guru']; ?></div>
                <a href="admin_guru.php" class="text-white text-decoration-underline small">Kelola Guru <i class="fas fa-arrow-right"></i></a>
                <i class="fas fa-chalkboard-teacher icon-background"></i>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card-stat" style="background:linear-gradient(45deg,#6f42c1,#8e44ad);">
                <div class="card-title">Jurusan</div>
                <div class="card-value"><?= $counts['jurusan']; ?></div>
                <a href="admin_jurusan.php" class="text-white text-decoration-underline small">Kelola Jurusan <i class="fas fa-arrow-right"></i></a>
                <i class="fas fa-sitemap icon-background"></i>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card-stat" style="background:linear-gradient(45deg,#fd7e14,#e67e22);">
                <div class="card-title">Kelas</div>
                <div class="card-value"><?= $counts['kelas']; ?></div>
                <a href="admin_kelas.php" class="text-white text-decoration-underline small">Kelola Kelas <i class="fas fa-arrow-right"></i></a>
                <i class="fas fa-school icon-background"></i>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card-stat" style="background:linear-gradient(45deg,#20c997,#1abc9c);">
                <div class="card-title">Mapel</div>
                <div class="card-value"><?= $counts['mapel']; ?></div>
                <a href="admin_mapel.php" class="text-white text-decoration-underline small">Kelola Mapel <i class="fas fa-arrow-right"></i></a>
                <i class="fas fa-book icon-background"></i>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card-stat" style="background:linear-gradient(45deg,#e83e8c,#c0392b);">
                <div class="card-title">Jadwal Pelajaran</div>
                <div class="card-value"><?= $counts['jadwal_pelajaran']; ?></div>
                <a href="admin_jadwal.php" class="text-white text-decoration-underline small">Kelola Jadwal <i class="fas fa-arrow-right"></i></a>
                <i class="fas fa-calendar-alt icon-background"></i>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card-stat" style="background:linear-gradient(45deg,#343a40,#2c3e50);">
                <div class="card-title">Absensi</div>
                <div class="card-value"><?= $counts['absensi']; ?></div>
                <a href="admin_absensi.php" class="text-white text-decoration-underline small">Kelola Absensi <i class="fas fa-arrow-right"></i></a>
                <i class="fas fa-clipboard-list icon-background"></i>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card-stat" style="background:linear-gradient(45deg,#dc3545,#e74c3c);">
                <div class="card-title">Nilai</div>
                <div class="card-value"><?= $counts['nilai']; ?></div>
                <a href="admin_nilai.php" class="text-white text-decoration-underline small">Kelola Nilai <i class="fas fa-arrow-right"></i></a>
                <i class="fas fa-chart-bar icon-background"></i>
            </div>
        </div>
    </div>

    <div class="mt-5">
        <h4 class="mb-3">Quick Actions</h4>
        <div class="row g-3">
            <div class="col-md-6 col-lg-4">
                <a href="admin_user_tambah.php" class="list-group-item list-group-item-action d-flex align-items-center">
                    <i class="fas fa-plus-circle me-3 text-primary"></i><span>Tambah User</span>
                </a>
            </div>
            <div class="col-md-6 col-lg-4">
                <a href="admin_siswa_tambah.php" class="list-group-item list-group-item-action d-flex align-items-center">
                    <i class="fas fa-plus-circle me-3 text-success"></i><span>Tambah Siswa</span>
                </a>
            </div>
            <div class="col-md-6 col-lg-4">
                <a href="admin_guru_tambah.php" class="list-group-item list-group-item-action d-flex align-items-center">
                    <i class="fas fa-plus-circle me-3 text-info"></i><span>Tambah Guru</span>
                </a>
            </div>
            <div class="col-md-6 col-lg-4">
                <a href="admin_mapel_tambah.php" class="list-group-item list-group-item-action d-flex align-items-center">
                    <i class="fas fa-plus-circle me-3 text-warning"></i><span>Tambah Mata Pelajaran</span>
                </a>
            </div>
            <div class="col-md-6 col-lg-4">
                <a href="admin_kelas_tambah.php" class="list-group-item list-group-item-action d-flex align-items-center">
                    <i class="fas fa-plus-circle me-3 text-secondary"></i><span>Tambah Kelas</span>
                </a>
            </div>
            <div class="col-md-6 col-lg-4">
                <a href="admin_jurusan_tambah.php" class="list-group-item list-group-item-action d-flex align-items-center">
                    <i class="fas fa-plus-circle me-3 text-danger"></i><span>Tambah Jurusan</span>
                </a>
            </div>
            <div class="col-md-6 col-lg-4">
                <a href="admin_jadwal_tambah.php" class="list-group-item list-group-item-action d-flex align-items-center">
                    <i class="fas fa-plus-circle me-3 text-dark"></i><span>Tambah Jadwal</span>
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>