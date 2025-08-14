<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'guru') {
    header("Location: login.php");
    exit;
}

// Ambil data kelas dan jurusan
$kelasResult = mysqli_query($koneksi, "SELECT * FROM kelas ORDER BY nama_kelas");
$jurusanResult = mysqli_query($koneksi, "SELECT * FROM jurusan ORDER BY nama_jurusan");

if (isset($_POST['simpan'])) {
    $nis = $_POST['nis'];
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $kelas_id = $_POST['kelas_id'];
    $jurusan_id = $_POST['jurusan_id'];

    // Simpan ke tabel user
    mysqli_query($koneksi, "INSERT INTO user (nama, email, password, role) VALUES ('$nama', '$email', '$password', 'siswa')");
    $user_id = mysqli_insert_id($koneksi);

    // Simpan ke tabel siswa
    mysqli_query($koneksi, "INSERT INTO siswa (nis, user_id, kelas_id, jurusan_id, status) VALUES ('$nis', '$user_id', '$kelas_id', '$jurusan_id', 'Aktif')");

    echo "<script>alert('Siswa berhasil ditambahkan!'); window.location='guru_data_siswa.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
    <h3>➕ Tambah Siswa</h3>
    <form method="post" class="card p-3 mt-3">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">NIS</label>
                <input type="text" name="nis" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Nama</label>
                <input type="text" name="nama" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
        </div>
        <div class="row g-3 mt-2">
            <div class="col-md-3">
                <label class="form-label">Kelas</label>
                <select name="kelas_id" class="form-select" required>
                    <option value="">-- Pilih Kelas --</option>
                    <?php while ($k = mysqli_fetch_assoc($kelasResult)): ?>
                        <option value="<?= $k['id']; ?>"><?= $k['nama_kelas']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Jurusan</label>
                <select name="jurusan_id" class="form-select" required>
                    <option value="">-- Pilih Jurusan --</option>
                    <?php while ($j = mysqli_fetch_assoc($jurusanResult)): ?>
                        <option value="<?= $j['id']; ?>"><?= $j['nama_jurusan']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
        <button type="submit" name="simpan" class="btn btn-success mt-3">Simpan</button>
        <a href="guru_data_siswa.php" class="btn btn-secondary mt-3">Kembali</a>
    </form>
</div>
</body>
</html>
