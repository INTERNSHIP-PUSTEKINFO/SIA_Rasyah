<?php
session_start();
include 'koneksi.php';

// Periksa apakah pengguna sudah login dan memiliki peran 'admin'
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Tampilkan pesan sukses jika ada
$status_message = '';
$alert_type = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'berhasil') {
        $status_message = "Kelas berhasil disimpan.";
        $alert_type = 'success';
    } elseif ($_GET['msg'] === 'gagal') {
        $status_message = "Aksi gagal dilakukan.";
        $alert_type = 'danger';
    } elseif ($_GET['msg'] === 'update_success') {
        $status_message = "Data kelas berhasil diperbarui!";
        $alert_type = 'success';
    } elseif ($_GET['msg'] === 'delete_success') {
        $status_message = "Data kelas berhasil dihapus!";
        $alert_type = 'success';
    }
}

// Ambil data kelas beserta jurusannya
$query = "
    SELECT k.id, k.nama_kelas, k.tingkat, j.nama_jurusan 
    FROM kelas k 
    JOIN jurusan j ON k.jurusan_id = j.id 
    ORDER BY k.nama_kelas ASC
";
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
    <title>Manajemen Data Kelas</title>
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
        }
        .table tbody tr:hover {
            background-color: #f1f5ff;
        }
        .table-responsive {
            overflow-x: auto;
        }
        /* Tambahan CSS untuk perataan tabel */
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
    <div class="container">
        <a class="navbar-brand" href="dashboard_admin.php">Akademik SMK</a>
        <div class="d-flex align-items-center">
            <a class="nav-link" href="dashboard_admin.php">Dashboard</a>
            <a class="nav-link" href="logout.php">Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h3><i class="fas fa-school me-2"></i> Manajemen Data Kelas</h3>
    <div class="d-flex mb-4">
        <a href="admin_kelas_tambah.php" class="btn btn-primary me-2"><i class="fas fa-plus-circle me-1"></i> Tambah Kelas</a>
        <a href="dashboard_admin.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
    </div>

    <?php if ($status_message): ?>
        <div class="alert alert-<?= $alert_type ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($status_message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-primary">
                    <tr>
                        <th class="text-center">No</th>
                        <th>Nama Kelas</th>
                        <th class="text-center">Tingkat</th>
                        <th>Jurusan</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): $no = 1; ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td><?= htmlspecialchars($row['nama_kelas']) ?></td>
                                <td class="text-center"><?= htmlspecialchars($row['tingkat']) ?></td>
                                <td><?= htmlspecialchars($row['nama_jurusan']) ?></td>
                                <td class="text-center">
                                    <a href="admin_kelas_edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Edit</a>
                                    <a href="admin_kelas_hapus.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin hapus data kelas ini?')"><i class="fas fa-trash-alt"></i> Hapus</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center text-muted">Belum ada data kelas.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>