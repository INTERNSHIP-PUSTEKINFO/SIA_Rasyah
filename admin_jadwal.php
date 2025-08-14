<?php
session_start();
include 'koneksi.php';

// Cek apakah user adalah admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Query yang diperbaiki untuk menggabungkan tabel dengan benar
$query = "
    SELECT 
        jp.id, jp.hari, jp.jam_ke,
        m.nama_mapel, g.nip, u.nama AS nama_guru,
        k.nama_kelas
    FROM jadwal_pelajaran jp
    JOIN mapel m ON jp.mapel_id = m.id
    JOIN user u ON jp.guru_id = u.id
    JOIN guru g ON u.id = g.user_id
    JOIN kelas k ON jp.kelas_id = k.id
    ORDER BY FIELD(jp.hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'), jp.jam_ke
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
    <title>Jadwal Pelajaran</title>
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
    <h3><i class="fas fa-calendar-alt me-2"></i>Jadwal Pelajaran</h3>
    <div class="d-flex mb-4">
        <a href="admin_jadwal_tambah.php" class="btn btn-primary me-2"><i class="fas fa-plus-circle me-1"></i> Tambah Jadwal</a>
        <a href="dashboard_admin.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'berhasil'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            ✅ Jadwal berhasil ditambahkan.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-primary text-center">
                    <tr>
                        <th>No</th>
                        <th>Hari</th>
                        <th>Jam Ke</th>
                        <th>Mata Pelajaran</th>
                        <th>Guru</th>
                        <th>Kelas</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td class="text-center"><?= $no++; ?></td>
                            <td><?= htmlspecialchars($row['hari']); ?></td>
                            <td class="text-center"><?= htmlspecialchars($row['jam_ke']); ?></td>
                            <td><?= htmlspecialchars($row['nama_mapel']); ?></td>
                            <td><?= htmlspecialchars($row['nama_guru']); ?> (NIP: <?= htmlspecialchars($row['nip']); ?>)</td>
                            <td><?= htmlspecialchars($row['nama_kelas']); ?></td>
                            <td class="text-center">
                                <a href="admin_jadwal_edit.php?id=<?= htmlspecialchars($row['id']); ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Edit</a>
                                <a href="admin_jadwal_hapus.php?id=<?= htmlspecialchars($row['id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus jadwal ini?')"><i class="fas fa-trash-alt"></i> Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="7" class="text-center text-muted">Belum ada jadwal</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
