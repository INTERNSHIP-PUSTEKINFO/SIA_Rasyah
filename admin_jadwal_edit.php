<?php
session_start();
include 'koneksi.php';

// Cek apakah user adalah admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Pastikan ID jadwal ada di URL
if (!isset($_GET['id'])) {
    header("Location: admin_jadwal.php?status=error_id_not_found");
    exit;
}

$id = $_GET['id'];

// Ambil data jadwal yang akan diedit
$stmt_jadwal = mysqli_prepare($koneksi, "SELECT * FROM jadwal_pelajaran WHERE id = ?");
mysqli_stmt_bind_param($stmt_jadwal, "i", $id);
mysqli_stmt_execute($stmt_jadwal);
$result_jadwal = mysqli_stmt_get_result($stmt_jadwal);
$data = mysqli_fetch_assoc($result_jadwal);

if (!$data) {
    header("Location: admin_jadwal.php?status=error_id_not_found");
    exit;
}

// Ambil data referensi untuk dropdown
$mapel  = mysqli_query($koneksi, "SELECT id, nama_mapel FROM mapel ORDER BY nama_mapel ASC");
$guru  = mysqli_query($koneksi, "SELECT g.nip AS guru_id, u.nama FROM guru g JOIN user u ON g.user_id = u.id ORDER BY u.nama ASC");
$kelas  = mysqli_query($koneksi, "SELECT id, nama_kelas FROM kelas ORDER BY nama_kelas ASC");
$hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

// Proses update data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mapel_id    = $_POST['mapel_id'];
    $guru_id     = $_POST['guru_id'];
    $kelas_id    = $_POST['kelas_id'];
    $hari        = $_POST['hari'];
    $jam_ke      = $_POST['jam_ke'];

    // Gunakan prepared statement untuk update
    $stmt_update = mysqli_prepare($koneksi, "UPDATE jadwal_pelajaran SET 
        mapel_id = ?, 
        guru_id = ?, 
        kelas_id = ?, 
        hari = ?, 
        jam_ke = ?
        WHERE id = ?");

    // Bind parameter untuk menghindari SQL Injection (i=integer, s=string)
    mysqli_stmt_bind_param($stmt_update, "iissii", $mapel_id, $guru_id, $kelas_id, $hari, $jam_ke, $id);

    // Jalankan statement
    if (mysqli_stmt_execute($stmt_update)) {
        header("Location: admin_jadwal.php?status=success_update");
    } else {
        header("Location: admin_jadwal.php?status=error_update");
    }
    mysqli_stmt_close($stmt_update);
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Jadwal Pelajaran</title>
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
        .form-control { border-radius: 8px; }
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
    <h3><i class="fas fa-edit me-2"></i>Edit Jadwal Pelajaran</h3>
    <div class="card p-4">
        <form action="" method="POST">
            <div class="mb-3">
                <label for="mapel_id" class="form-label">Mata Pelajaran:</label>
                <select name="mapel_id" id="mapel_id" class="form-control" required>
                    <?php while ($row = mysqli_fetch_assoc($mapel)): ?>
                        <option value="<?= htmlspecialchars($row['id']) ?>" <?= ($row['id'] == $data['mapel_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($row['nama_mapel']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="guru_id" class="form-label">Guru:</label>
                <select name="guru_id" id="guru_id" class="form-control" required>
                    <?php while ($row = mysqli_fetch_assoc($guru)): ?>
                        <option value="<?= htmlspecialchars($row['guru_id']) ?>" <?= ($row['guru_id'] == $data['guru_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($row['nama']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="kelas_id" class="form-label">Kelas:</label>
                <select name="kelas_id" id="kelas_id" class="form-control" required>
                    <?php while ($row = mysqli_fetch_assoc($kelas)): ?>
                        <option value="<?= htmlspecialchars($row['id']) ?>" <?= ($row['id'] == $data['kelas_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($row['nama_kelas']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="hari" class="form-label">Hari:</label>
                <select name="hari" id="hari" class="form-control" required>
                    <?php foreach ($hariList as $hari): ?>
                        <option value="<?= htmlspecialchars($hari) ?>" <?= ($hari == $data['hari']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($hari) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="jam_ke" class="form-label">Jam Ke:</label>
                <input type="number" name="jam_ke" id="jam_ke" class="form-control" value="<?= htmlspecialchars($data['jam_ke'] ?? '') ?>" required>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Simpan Perubahan</button>
                <a href="admin_jadwal.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Batal</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>