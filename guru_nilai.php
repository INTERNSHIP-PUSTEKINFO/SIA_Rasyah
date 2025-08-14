<?php
session_start();
include 'koneksi.php';

// Cek apakah user adalah guru
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'guru') {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$user_id = $user['id'];

// Mengambil NIP dan mapel_id guru yang sedang login
$stmt_guru = mysqli_prepare($koneksi, "SELECT nip, mapel_id FROM guru WHERE user_id = ?");
mysqli_stmt_bind_param($stmt_guru, "i", $user_id);
mysqli_stmt_execute($stmt_guru);
$result_guru = mysqli_stmt_get_result($stmt_guru);
$guru = mysqli_fetch_assoc($result_guru);
$guru_nip = $guru['nip'];
$guru_mapel_id = $guru['mapel_id'];

// Inisialisasi variabel filter
$filter_nama = isset($_GET['nama']) ? mysqli_real_escape_string($koneksi, $_GET['nama']) : '';
$filter_semester = isset($_GET['semester']) ? mysqli_real_escape_string($koneksi, $_GET['semester']) : '';
$filter_tahun_ajaran = isset($_GET['tahun_ajaran']) ? mysqli_real_escape_string($koneksi, $_GET['tahun_ajaran']) : '';

// Query untuk mengambil data semester dan tahun ajaran untuk dropdown filter
$query_semester = "
    SELECT DISTINCT semester 
    FROM nilai 
    WHERE mapel_id = '$guru_mapel_id' 
    ORDER BY semester ASC
";
$result_semester = mysqli_query($koneksi, $query_semester);

$query_tahun = "
    SELECT DISTINCT tahun_ajaran 
    FROM nilai 
    WHERE mapel_id = '$guru_mapel_id' 
    ORDER BY tahun_ajaran DESC
";
$result_tahun = mysqli_query($koneksi, $query_tahun);

// Query untuk mengambil nama mata pelajaran yang diampu guru
$query_mapel_guru = "SELECT nama_mapel FROM mapel WHERE id = '$guru_mapel_id'";
$result_mapel_guru = mysqli_query($koneksi, $query_mapel_guru);
$mapel_guru = mysqli_fetch_assoc($result_mapel_guru);
$nama_mapel_guru = $mapel_guru['nama_mapel'];

// Query utama untuk menampilkan data nilai siswa
$query = "
    SELECT 
        n.*, 
        u.nama AS nama_siswa, 
        m.nama_mapel 
    FROM nilai n
    JOIN siswa s ON n.siswa_nis = s.nis
    JOIN user u ON s.user_id = u.id
    JOIN mapel m ON n.mapel_id = m.id
";

// Logika filter yang diperbaiki
$where_clauses = ["n.mapel_id = '$guru_mapel_id'"];

if (!empty($filter_nama)) {
    $where_clauses[] = "u.nama LIKE '%$filter_nama%'";
}
if (!empty($filter_semester)) {
    $where_clauses[] = "n.semester = '$filter_semester'";
}
if (!empty($filter_tahun_ajaran)) {
    $where_clauses[] = "n.tahun_ajaran = '$filter_tahun_ajaran'";
}

// Menggabungkan semua klausa WHERE menjadi satu
if (!empty($where_clauses)) {
    $query .= " WHERE " . implode(' AND ', $where_clauses);
}

$query .= " ORDER BY n.tahun_ajaran DESC, u.nama ASC";

// Baris 82: Eksekusi query
$result = mysqli_query($koneksi, $query);

if (!$result) {
    die("Query Error: " . mysqli_error($koneksi));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Nilai Siswa (Guru)</title>
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
        .btn-secondary, .btn-info {
            background-color: #6c757d;
            border-color: #6c757d;
            border-radius: 8px;
            font-weight: 500;
            padding: 10px 15px;
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
        }
        .table tbody tr:hover {
            background-color: #f1f5ff;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .form-control, .form-select {
            border-radius: 8px;
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
    <h3><i class="fas fa-chart-bar me-2"></i>Kelola Nilai Siswa (Mata Pelajaran: <?= htmlspecialchars($nama_mapel_guru); ?>)</h3>
    <div class="d-flex mb-4">
        <a href="guru_nilai_tambah.php" class="btn btn-primary me-2"><i class="fas fa-plus-circle me-1"></i> Tambah Nilai</a>
        <a href="dashboard_guru.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_GET['msg'] === 'hapus' ? '✅ Nilai berhasil dihapus.' : ($_GET['msg'] === 'update' ? '✅ Nilai diperbarui.' : '✅ Nilai ditambahkan.') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="card mb-4">
        <h5 class="mb-3">Filter Data</h5>
        <form method="GET" action="guru_nilai.php">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="nama" placeholder="Cari Nama Siswa" value="<?= htmlspecialchars($filter_nama); ?>">
                </div>
                <div class="col-md-3">
                    <select name="semester" class="form-select">
                        <option value="">-- Semua Semester --</option>
                        <?php mysqli_data_seek($result_semester, 0); // Reset pointer ?>
                        <?php while ($semester = mysqli_fetch_assoc($result_semester)): ?>
                            <option value="<?= $semester['semester']; ?>" <?= ($filter_semester == $semester['semester']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($semester['semester']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="tahun_ajaran" class="form-select">
                        <option value="">-- Semua Tahun Ajaran --</option>
                        <?php mysqli_data_seek($result_tahun, 0); // Reset pointer ?>
                        <?php while ($tahun = mysqli_fetch_assoc($result_tahun)): ?>
                            <option value="<?= $tahun['tahun_ajaran']; ?>" <?= ($filter_tahun_ajaran == $tahun['tahun_ajaran']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($tahun['tahun_ajaran']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i> Cari</button>
                    <?php if (!empty($filter_nama) || !empty($filter_semester) || !empty($filter_tahun_ajaran)): ?>
                        <a href="guru_nilai.php" class="btn btn-secondary mt-2"><i class="fas fa-sync-alt me-1"></i> Reset</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-primary text-center">
                    <tr>
                        <th>No</th>
                        <th>NIS</th>
                        <th>Nama Siswa</th>
                        <th>Mata Pelajaran</th>
                        <th>Semester</th>
                        <th>Tahun Ajaran</th>
                        <th>Tugas</th>
                        <th>UTS</th>
                        <th>UAS</th>
                        <th>Nilai Akhir</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (mysqli_num_rows($result) > 0): $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td class="text-center"><?= $no++; ?></td>
                        <td><?= htmlspecialchars($row['siswa_nis']); ?></td>
                        <td><?= htmlspecialchars($row['nama_siswa']); ?></td>
                        <td><?= htmlspecialchars($row['nama_mapel']); ?></td>
                        <td class="text-center"><?= htmlspecialchars($row['semester']); ?></td>
                        <td class="text-center"><?= htmlspecialchars($row['tahun_ajaran']); ?></td>
                        <td class="text-center"><?= htmlspecialchars($row['nilai_tugas']); ?></td>
                        <td class="text-center"><?= htmlspecialchars($row['nilai_uts']); ?></td>
                        <td class="text-center"><?= htmlspecialchars($row['nilai_uas']); ?></td>
                        <td class="text-center fw-bold"><?= htmlspecialchars($row['nilai_akhir']); ?></td>
                        <td class="text-center">
                            <a href="guru_nilai_edit.php?id=<?= htmlspecialchars($row['id']); ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Edit</a>
                            <a href="guru_nilai_hapus.php?id=<?= htmlspecialchars($row['id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus nilai ini?')"><i class="fas fa-trash-alt"></i> Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="11" class="text-center text-muted">Belum ada data nilai</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>