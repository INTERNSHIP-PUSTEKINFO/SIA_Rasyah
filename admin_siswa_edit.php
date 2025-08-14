<?php
session_start();
include 'koneksi.php';

// Cek akses admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Ambil data siswa berdasarkan NIS
if (!isset($_GET['nis'])) {
    header("Location: admin_siswa.php");
    exit;
}

$nis = htmlspecialchars($_GET['nis']);
$query = "
    SELECT s.*, u.nama, u.email
    FROM siswa s
    JOIN user u ON s.user_id = u.id
    WHERE s.nis = '$nis'
";
$result = mysqli_query($koneksi, $query);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    // Menggunakan alert kustom atau pesan di halaman jika data tidak ditemukan
    // untuk menghindari penggunaan `alert()` yang dibatasi.
    // Contoh: header("Location: admin_siswa.php?msg=notfound");
    echo "<script>window.location='admin_siswa.php';</script>";
    exit;
}

// Ambil data kelas dan jurusan untuk dropdown
$kelasResult = mysqli_query($koneksi, "SELECT * FROM kelas ORDER BY nama_kelas ASC");
$jurusanResult = mysqli_query($koneksi, "SELECT * FROM jurusan ORDER BY nama_jurusan ASC");

$pesan = "";

// Proses update saat form disubmit
if (isset($_POST['simpan'])) {
    $nama            = $_POST['nama'];
    $email           = $_POST['email'];
    $kelas_id        = $_POST['kelas_id'];
    $jurusan_id      = $_POST['jurusan_id'];
    $tempat_lahir    = $_POST['tempat_lahir'];
    $tanggal_lahir   = $_POST['tanggal_lahir'];
    $jenis_kelamin   = $_POST['jenis_kelamin'];
    $alamat          = $_POST['alamat'];
    $status          = $_POST['status'];

    $user_id = $data['user_id'];

    // Update ke tabel user
    $updateUser = mysqli_query($koneksi, "UPDATE user SET nama='$nama', email='$email' WHERE id='$user_id'");

    // Update ke tabel siswa
    $updateSiswa = mysqli_query($koneksi, "UPDATE siswa SET
        kelas_id='$kelas_id',
        jurusan_id='$jurusan_id',
        tempat_lahir='$tempat_lahir',
        tanggal_lahir='$tanggal_lahir',
        jenis_kelamin='$jenis_kelamin',
        alamat='$alamat',
        status='$status'
        WHERE nis='$nis'
    ");

    if ($updateUser && $updateSiswa) {
        header("Location: admin_siswa.php?msg=updated");
        exit;
    } else {
        $pesan = "❌ Gagal mengupdate data: " . mysqli_error($koneksi);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Siswa</title>
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
        .container { max-width: 800px; }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            padding: 30px;
        }
        h3 {
            font-weight: 700;
            color: #2c3e50;
            border-left: 5px solid #007bff;
            padding-left: 15px;
            margin-bottom: 25px;
        }
        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
            border-radius: 8px;
            font-weight: 500;
            padding: 10px 20px;
            transition: background-color 0.3s ease;
        }
        .btn-success:hover {
            background-color: #218838;
            border-color: #218838;
        }
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
            border-radius: 8px;
            font-weight: 500;
            padding: 10px 20px;
        }
        .form-control, .form-select {
            border-radius: 8px;
            padding: 12px;
        }
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.25);
            border-color: #007bff;
        }
        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
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
    <h3><i class="fas fa-user-edit me-2"></i>Edit Data Siswa</h3>
    <a href="admin_siswa.php" class="btn btn-secondary mb-4"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
    
    <?php if (!empty($pesan)): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($pesan); ?></div>
    <?php endif; ?>

    <div class="card">
        <form method="post">
            <div class="mb-3">
                <label for="nis" class="form-label">NIS (Tidak Bisa Diubah)</label>
                <input type="text" name="nis_disabled" id="nis" class="form-control" value="<?= htmlspecialchars($data['nis']); ?>" readonly>
            </div>
            <div class="mb-3">
                <label for="nama" class="form-label">Nama</label>
                <input type="text" name="nama" id="nama" class="form-control" value="<?= htmlspecialchars($data['nama']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($data['email']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="tempat_lahir" class="form-label">Tempat Lahir</label>
                <input type="text" name="tempat_lahir" id="tempat_lahir" class="form-control" value="<?= htmlspecialchars($data['tempat_lahir']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir" id="tanggal_lahir" class="form-control" value="<?= htmlspecialchars($data['tanggal_lahir']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                <select name="jenis_kelamin" id="jenis_kelamin" class="form-select" required>
                    <option value="L" <?= $data['jenis_kelamin'] == 'L' ? 'selected' : ''; ?>>Laki-laki</option>
                    <option value="P" <?= $data['jenis_kelamin'] == 'P' ? 'selected' : ''; ?>>Perempuan</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="alamat" class="form-label">Alamat</label>
                <textarea name="alamat" id="alamat" class="form-control" required><?= htmlspecialchars($data['alamat']); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="kelas_id" class="form-label">Kelas</label>
                <select name="kelas_id" id="kelas_id" class="form-select" required>
                    <?php while ($k = mysqli_fetch_assoc($kelasResult)): ?>
                        <option value="<?= htmlspecialchars($k['id']); ?>" <?= $data['kelas_id'] == $k['id'] ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($k['nama_kelas']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="jurusan_id" class="form-label">Jurusan</label>
                <select name="jurusan_id" id="jurusan_id" class="form-select" required>
                    <?php while ($j = mysqli_fetch_assoc($jurusanResult)): ?>
                        <option value="<?= htmlspecialchars($j['id']); ?>" <?= $data['jurusan_id'] == $j['id'] ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($j['nama_jurusan']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select" required>
                    <option value="aktif" <?= $data['status'] == 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                    <option value="lulus" <?= $data['status'] == 'lulus' ? 'selected' : ''; ?>>Lulus</option>
                    <option value="keluar" <?= $data['status'] == 'keluar' ? 'selected' : ''; ?>>Keluar</option>
                </select>
            </div>
            <div class="d-flex">
                <button type="submit" name="simpan" class="btn btn-success me-2"><i class="fas fa-save me-1"></i> Simpan Perubahan</button>
                <a href="admin_siswa.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
