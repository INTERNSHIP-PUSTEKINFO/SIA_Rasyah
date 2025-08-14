<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'guru') {
    header("Location: login.php");
    exit;
}

$nis = $_GET['nis'] ?? '';
if ($nis == '') {
    header("Location: guru_data_siswa.php");
    exit;
}

// Ambil data siswa
$query = "SELECT s.*, u.nama, u.email 
          FROM siswa s 
          JOIN user u ON s.user_id = u.id 
          WHERE s.nis = '$nis'";
$result = mysqli_query($koneksi, $query);
$siswa = mysqli_fetch_assoc($result);

// Ambil data kelas dan jurusan
$kelasResult = mysqli_query($koneksi, "SELECT * FROM kelas ORDER BY nama_kelas");
$jurusanResult = mysqli_query($koneksi, "SELECT * FROM jurusan ORDER BY nama_jurusan");

if (isset($_POST['update'])) {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $kelas_id = $_POST['kelas_id'];
    $jurusan_id = $_POST['jurusan_id'];
    $status = $_POST['status'];

    mysqli_query($koneksi, "UPDATE user SET nama='$nama', email='$email' WHERE id='{$siswa['user_id']}'");
    mysqli_query($koneksi, "UPDATE siswa SET kelas_id='$kelas_id', jurusan_id='$jurusan_id', status='$status' WHERE nis='$nis'");

    echo "<script>alert('Data siswa berhasil diperbarui!'); window.location='guru_data_siswa.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
    <h3>✏️ Edit Siswa</h3>
    <form method="post" class="card p-3 mt-3">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">NIS</label>
                <input type="text" class="form-control" value="<?= $siswa['nis']; ?>" readonly>
            </div>
            <div class="col-md-3">
                <label class="form-label">Nama</label>
                <input type="text" name="nama" class="form-control" value="<?= $siswa['nama']; ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?= $siswa['email']; ?>" required>
            </div>
        </div>
        <div class="row g-3 mt-2">
            <div class="col-md-3">
                <label class="form-label">Kelas</label>
                <select name="kelas_id" class="form-select" required>
                    <?php while ($k = mysqli_fetch_assoc($kelasResult)): ?>
                        <option value="<?= $k['id']; ?>" <?= ($k['id'] == $siswa['kelas_id']) ? 'selected' : ''; ?>>
                            <?= $k['nama_kelas']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Jurusan</label>
                <select name="jurusan_id" class="form-select" required>
                    <?php while ($j = mysqli_fetch_assoc($jurusanResult)): ?>
                        <option value="<?= $j['id']; ?>" <?= ($j['id'] == $siswa['jurusan_id']) ? 'selected' : ''; ?>>
                            <?= $j['nama_jurusan']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="Aktif" <?= ($siswa['status'] == 'Aktif') ? 'selected' : ''; ?>>Aktif</option>
                    <option value="Lulus" <?= ($siswa['status'] == 'Lulus') ? 'selected' : ''; ?>>Lulus</option>
                    <option value="Keluar" <?= ($siswa['status'] == 'Keluar') ? 'selected' : ''; ?>>Keluar</option>
                </select>
            </div>
        </div>
        <button type="submit" name="update" class="btn btn-primary mt-3">Update</button>
        <a href="guru_data_siswa.php" class="btn btn-secondary mt-3">Kembali</a>
    </form>
</div>
</body>
</html>
