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

// Mengambil data siswa menggunakan prepared statement untuk keamanan
$stmt = mysqli_prepare($koneksi, "
    SELECT s.*, k.nama_kelas, j.nama_jurusan 
    FROM siswa s
    LEFT JOIN kelas k ON s.kelas_id = k.id
    LEFT JOIN jurusan j ON s.jurusan_id = j.id
    WHERE s.user_id = ?
");

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $siswa = mysqli_fetch_assoc($result);
} else {
    // Handle error jika prepared statement gagal
    die("Query Error: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Mengatur body dan font */
        body { 
            background-color: #f0f2f5; 
            font-family: 'Poppins', sans-serif; 
            color: #333;
        }

        /* Navbar yang lebih elegan */
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
        .navbar-nav .nav-link {
            color: #555 !important;
            font-weight: 500;
            margin-right: 15px;
            transition: color 0.3s ease-in-out;
        }
        .navbar-nav .nav-link:hover {
            color: #007bff !important;
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

        /* Bagian header (judul halaman) */
        .container h2 {
            font-weight: 700;
            color: #2c3e50;
            border-left: 5px solid #007bff;
            padding-left: 15px;
        }

        /* Desain kartu (card) yang lebih modern */
        .card {
            border-radius: 12px;
            color: #ffffff;
            padding: 25px;
            min-height: 180px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }
        .card h5 { 
            font-weight: 700; 
            font-size: 1.5rem;
            margin-bottom: 10px;
        }
        .card p {
            font-size: 1rem;
            opacity: 0.9;
        }
        .card a.btn-masuk {
            background: #ffffff;
            color: #007bff;
            font-weight: bold;
            border-radius: 50px;
            padding: 8px 25px;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            align-self: flex-start;
            transition: all 0.3s ease-in-out;
        }
        .card a.btn-masuk:hover { 
            background: #0056b3; 
            color: #fff;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
        }
        
        /* Warna kartu yang berbeda */
        .card-nilai {
            background: linear-gradient(45deg, #007bff, #00c6ff);
        }
        .card-absensi {
            background: linear-gradient(45deg, #28a745, #2ecc71);
        }
        .card-jadwal {
            background: linear-gradient(45deg, #ffc107, #f39c12);
        }
        .card .icon-background {
            position: absolute;
            bottom: -20px;
            right: -20px;
            font-size: 8rem;
            opacity: 0.15;
            color: #fff;
        }
        /* Style untuk info detail siswa */
        .card-info {
            background-color: #fff;
            color: #333;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border: none;
            border-left: 5px solid #007bff;
        }
        .card-info p {
            margin-bottom: 0.5rem;
        }
        .card-info p i {
            width: 25px;
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
    <h2>Selamat Datang, <?= htmlspecialchars($user['nama']); ?> (Siswa)</h2>
    <p class="text-muted">Ini adalah informasi dan menu utama kamu.</p>

    <div class="card card-info p-4 mb-4">
        <div class="row">
            <div class="col-md-6">
                <p><i class="fas fa-id-card me-2"></i><strong>NIS:</strong> <?= htmlspecialchars($siswa['nis'] ?? '-'); ?></p>
                <p><i class="fas fa-chalkboard-teacher me-2"></i><strong>Kelas:</strong> <?= htmlspecialchars($siswa['nama_kelas'] ?? '-'); ?></p>
            </div>
            <div class="col-md-6">
                <p><i class="fas fa-sitemap me-2"></i><strong>Jurusan:</strong> <?= htmlspecialchars($siswa['nama_jurusan'] ?? '-'); ?></p>
                <p><i class="fas fa-envelope me-2"></i><strong>Email:</strong> <?= htmlspecialchars($user['email'] ?? '-'); ?></p>
            </div>
        </div>
    </div>

    <div class="row mt-4 g-4">
        <div class="col-md-4">
            <div class="card card-nilai">
                <div>
                    <h5>Nilai</h5>
                    <p>Lihat nilai semua mata pelajaran.</p>
                </div>
                <a href="siswa_nilai.php" class="btn btn-masuk">Lihat Nilai</a>
                <i class="fas fa-star icon-background"></i>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-absensi">
                <div>
                    <h5>Absensi</h5>
                    <p> Isi kehadiran kamu di setiap kelas.</p>
                </div>
                <a href="absensi_siswa.php" class="btn btn-masuk">Mengisi Absensi</a>
                <i class="fas fa-clipboard-check icon-background"></i>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-jadwal">
                <div>
                    <h5>Jadwal Pelajaran</h5>
                    <p>Lihat jadwal pelajaran harian kamu.</p>
                </div>
                <a href="jadwal_siswa.php" class="btn btn-masuk">Lihat Jadwal</a>
                <i class="fas fa-calendar-alt icon-background"></i>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>