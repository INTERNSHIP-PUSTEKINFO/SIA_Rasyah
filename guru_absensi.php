<?php
// Memulai sesi untuk mengelola data pengguna.
session_start();
// Menyertakan file koneksi database.
include 'koneksi.php';

// Validasi role, hanya guru yang bisa mengakses halaman ini.
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'guru') {
    header("Location: login.php");
    exit;
}

// Ambil user_id dan nama dari sesi.
$user_id = $_SESSION['user']['id'];
$nama_guru = $_SESSION['user']['nama'];
$mapel_id_guru = null;

// Cari mapel_id dari tabel guru berdasarkan user_id.
$mapel_query = "SELECT mapel_id FROM guru WHERE user_id = ?";
if ($mapel_stmt = mysqli_prepare($koneksi, $mapel_query)) {
    mysqli_stmt_bind_param($mapel_stmt, "i", $user_id);
    mysqli_stmt_execute($mapel_stmt);
    $mapel_result = mysqli_stmt_get_result($mapel_stmt);
    if ($mapel_data = mysqli_fetch_assoc($mapel_result)) {
        $mapel_id_guru = $mapel_data['mapel_id'];
    }
    mysqli_stmt_close($mapel_stmt);
}

// Jika mapel_id tidak ditemukan, hentikan eksekusi dan tampilkan pesan.
if (empty($mapel_id_guru)) {
    die("Anda tidak memiliki mata pelajaran yang terdaftar. Silakan hubungi admin.");
}

// Inisialisasi variabel filter
$search_keyword = $_GET['search'] ?? '';

// Query dasar untuk mengambil data absensi.
// Filter berdasarkan mapel_id guru yang login.
// Bergabung dengan tabel 'user' untuk mendapatkan nama siswa.
$query = "SELECT 
            a.id, 
            a.tanggal, 
            a.keterangan, 
            u.nama AS nama_siswa,
            s.nis AS nis_siswa, 
            m.nama_mapel 
          FROM absensi a
          JOIN siswa s ON a.siswa_nis = s.nis
          JOIN user u ON s.user_id = u.id
          JOIN mapel m ON a.mapel_id = m.id
          WHERE a.mapel_id = ? ";

// Menambahkan filter pencarian jika ada
if (!empty($search_keyword)) {
    $query .= " AND (u.nama LIKE ? OR s.nis LIKE ?)";
}

$query .= " ORDER BY a.tanggal DESC";

// Siapkan dan jalankan query dengan prepared statement
$absensi_stmt = mysqli_prepare($koneksi, $query);

if ($absensi_stmt) {
    if (!empty($search_keyword)) {
        $search_param = "%" . $search_keyword . "%";
        mysqli_stmt_bind_param($absensi_stmt, "iss", $mapel_id_guru, $search_param, $search_param);
    } else {
        mysqli_stmt_bind_param($absensi_stmt, "i", $mapel_id_guru);
    }
    mysqli_stmt_execute($absensi_stmt);
    $absensi_result = mysqli_stmt_get_result($absensi_stmt);
} else {
    die("Query Error: " . mysqli_error($koneksi));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Guru - Absensi</title>
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
        .container { max-width: 1200px; margin-top: 50px; }
        .card { 
            border: none; 
            border-radius: 15px; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.1); 
            padding: 20px;
            margin-bottom: 30px;
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
        .btn-secondary, .btn-outline-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
            border-radius: 8px;
            font-weight: 500;
            padding: 10px 15px;
            color: #fff;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        .btn-secondary:hover, .btn-outline-secondary:hover {
            background-color: #5a6268;
            border-color: #5a6268;
            color: #fff;
        }
        .btn-warning, .btn-danger {
            border-radius: 8px;
            font-weight: 500;
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
            vertical-align: middle;
            text-align: center;
        }
        .table tbody tr:hover {
            background-color: #f1f5ff;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard_guru.php">Akademik SMK</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard_guru.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h3><i class="fas fa-list-ul me-2"></i>Absensi Siswa - <?= htmlspecialchars($absensi_data['nama_mapel'] ?? "Data Absensi") ?></h3>
    
    <div class="card p-4">
        <!-- Form pencarian -->
        <form method="get" class="mb-4">
            <div class="input-group">
                <input type="text" class="form-control" placeholder="Cari NIS atau Nama Siswa..." name="search" value="<?= htmlspecialchars($search_keyword) ?>">
                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> Cari</button>
            </div>
        </form>

        <a href="dashboard_guru.php" class="btn btn-secondary mb-3"><i class="fas fa-arrow-left me-1"></i> Kembali ke Dashboard</a>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-primary">
                    <tr>
                        <th>NIS</th>
                        <th>Nama Siswa</th>
                        <th>Mata Pelajaran</th>
                        <th>Tanggal</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($absensi_result) > 0): ?>
                        <?php while ($absensi_data = mysqli_fetch_assoc($absensi_result)): ?>
                            <tr>
                                <td><?= htmlspecialchars($absensi_data['nis_siswa']) ?></td>
                                <td><?= htmlspecialchars($absensi_data['nama_siswa']) ?></td>
                                <td><?= htmlspecialchars($absensi_data['nama_mapel']) ?></td>
                                <td><?= htmlspecialchars($absensi_data['tanggal']) ?></td>
                                <td>
                                    <?php
                                    $keterangan = htmlspecialchars($absensi_data['keterangan']);
                                    $badge_class = '';
                                    switch ($keterangan) {
                                        case 'Hadir':
                                            $badge_class = 'bg-success';
                                            break;
                                        case 'Sakit':
                                            $badge_class = 'bg-warning text-dark';
                                            break;
                                        case 'Izin':
                                            $badge_class = 'bg-info text-dark';
                                            break;
                                        case 'Alpa':
                                            $badge_class = 'bg-danger';
                                            break;
                                        default:
                                            $badge_class = 'bg-secondary';
                                            break;
                                    }
                                    ?>
                                    <span class="badge <?= $badge_class ?>"><?= $keterangan ?></span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">Tidak ada data absensi yang ditemukan.</td>
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
<?php
// Tutup koneksi database
if (isset($absensi_stmt)) {
    mysqli_stmt_close($absensi_stmt);
}
mysqli_close($koneksi);
?>
