<?php
session_start();
include 'koneksi.php';

// Cek apakah user adalah admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Inisialisasi variabel filter
$filter_nama = isset($_GET['nama']) ? mysqli_real_escape_string($koneksi, $_GET['nama']) : '';
$filter_kelas_id = isset($_GET['kelas_id']) ? mysqli_real_escape_string($koneksi, $_GET['kelas_id']) : '';
$filter_jurusan_id = isset($_GET['jurusan_id']) ? mysqli_real_escape_string($koneksi, $_GET['jurusan_id']) : '';

// Query untuk mengambil data kelas dan jurusan untuk dropdown filter
$query_kelas = "SELECT * FROM kelas ORDER BY nama_kelas ASC";
$result_kelas = mysqli_query($koneksi, $query_kelas);

$query_jurusan = "SELECT * FROM jurusan ORDER BY nama_jurusan ASC";
$result_jurusan = mysqli_query($koneksi, $query_jurusan);

// Query utama untuk menampilkan data siswa
$query = "
    SELECT 
        s.nis, 
        u.id as user_id, 
        u.nama, 
        u.email, 
        k.nama_kelas, 
        j.nama_jurusan,
        s.tempat_lahir, 
        s.tanggal_lahir, 
        s.jenis_kelamin, 
        s.alamat, 
        s.status
    FROM siswa s
    JOIN user u ON s.user_id = u.id
    JOIN kelas k ON s.kelas_id = k.id
    JOIN jurusan j ON s.jurusan_id = j.id
";

// Logika filter yang diperbarui
$where_clauses = [];

if (!empty($filter_nama)) {
    $where_clauses[] = "u.nama LIKE '%$filter_nama%'";
}
if (!empty($filter_kelas_id)) {
    $where_clauses[] = "k.id = '$filter_kelas_id'";
}
if (!empty($filter_jurusan_id)) {
    $where_clauses[] = "j.id = '$filter_jurusan_id'";
}

if (!empty($where_clauses)) {
    $query .= " WHERE " . implode(' AND ', $where_clauses);
}

$query .= " ORDER BY u.nama ASC";

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
    <title>Data Siswa</title>
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
        .btn-secondary {
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
        <a class="navbar-brand" href="dashboard_admin.php">Akademik SMK</a>
        <div class="d-flex align-items-center">
            <a class="nav-link" href="dashboard_admin.php">Dashboard</a>
            <a class="nav-link" href="logout.php">Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h3><i class="fas fa-user-graduate me-2"></i>Data Siswa</h3>
    <div class="d-flex mb-4">
        <a href="admin_siswa_tambah.php" class="btn btn-primary me-2"><i class="fas fa-plus-circle me-1"></i> Tambah Siswa</a>
        <a href="dashboard_admin.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
    </div>

    <!-- Filter Form -->
    <div class="card mb-4">
        <h5 class="mb-3">Filter Data</h5>
        <form method="GET" action="admin_siswa.php">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="nama" placeholder="Cari Nama Siswa" value="<?= htmlspecialchars($filter_nama); ?>">
                </div>
                <div class="col-md-4">
                    <select name="kelas_id" class="form-select">
                        <option value="">-- Semua Kelas --</option>
                        <?php mysqli_data_seek($result_kelas, 0); while ($kelas = mysqli_fetch_assoc($result_kelas)): ?>
                            <option value="<?= $kelas['id']; ?>" <?= ($filter_kelas_id == $kelas['id']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($kelas['nama_kelas']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <select name="jurusan_id" class="form-select">
                        <option value="">-- Semua Jurusan --</option>
                        <?php mysqli_data_seek($result_jurusan, 0); while ($jurusan = mysqli_fetch_assoc($result_jurusan)): ?>
                            <option value="<?= $jurusan['id']; ?>" <?= ($filter_jurusan_id == $jurusan['id']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($jurusan['nama_jurusan']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i> Cari</button>
                    <a href="admin_siswa.php" class="btn btn-secondary ms-2"><i class="fas fa-sync-alt me-1"></i> Reset</a>
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
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Tempat / Tgl Lahir</th>
                        <th>JK</th>
                        <th>Alamat</th>
                        <th>Kelas</th>
                        <th>Jurusan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($result) > 0): $no = 1; while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= htmlspecialchars($row['nis']); ?></td>
                            <td><?= htmlspecialchars($row['nama']); ?></td>
                            <td><?= htmlspecialchars($row['email']); ?></td>
                            <td><?= htmlspecialchars($row['tempat_lahir']); ?> / <?= date('d-m-Y', strtotime($row['tanggal_lahir'])); ?></td>
                            <td><?= htmlspecialchars($row['jenis_kelamin']); ?></td>
                            <td><?= htmlspecialchars($row['alamat']); ?></td>
                            <td><?= htmlspecialchars($row['nama_kelas']); ?></td>
                            <td><?= htmlspecialchars($row['nama_jurusan']); ?></td>
                            <td class="text-center text-capitalize"><?= htmlspecialchars($row['status']); ?></td>
                            <td class="text-center">
                                <a href="admin_siswa_edit.php?nis=<?= htmlspecialchars($row['nis']); ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Edit</a>
                                <a href="admin_siswa_hapus.php?nis=<?= htmlspecialchars($row['nis']); ?>&user_id=<?= htmlspecialchars($row['user_id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus siswa ini?')"><i class="fas fa-trash-alt"></i> Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="11" class="text-center text-muted">Belum ada data siswa</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
