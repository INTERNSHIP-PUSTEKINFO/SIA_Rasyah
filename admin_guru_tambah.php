<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Ambil daftar mapel untuk dropdown
$mapelResult = mysqli_query($koneksi, "SELECT * FROM mapel ORDER BY nama_mapel ASC");

$pesan = "";

if (isset($_POST['simpan'])) {
    $nip            = $_POST['nip'];
    $nama           = $_POST['nama'];
    $email          = $_POST['email'];
    $password       = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $tempat_lahir   = $_POST['tempat_lahir'];
    $tanggal_lahir  = $_POST['tanggal_lahir'];
    $jenis_kelamin  = $_POST['jenis_kelamin'];
    $alamat         = $_POST['alamat'];
    $mapel_id       = $_POST['mapel_id'];
    $created_at     = date('Y-m-d H:i:s');
    $updated_at     = $created_at;

    // Cek email sudah digunakan belum
    $cek = mysqli_query($koneksi, "SELECT id FROM user WHERE email = '$email'");
    if (mysqli_num_rows($cek) > 0) {
        $pesan = "Email sudah digunakan. Silakan gunakan email lain.";
    } else {
        // Insert ke tabel user
        $insertUser = mysqli_query($koneksi, "INSERT INTO user (nama, email, password, role, created_at, updated_at)
            VALUES ('$nama', '$email', '$password', 'guru', '$created_at', '$updated_at')");

        if ($insertUser) {
            $user_id = mysqli_insert_id($koneksi);

            // Insert ke tabel guru
            $insertGuru = mysqli_query($koneksi, "INSERT INTO guru (nip, user_id, tempat_lahir, tanggal_lahir, jenis_kelamin, alamat, mapel_id)
                VALUES ('$nip', '$user_id', '$tempat_lahir', '$tanggal_lahir', '$jenis_kelamin', '$alamat', '$mapel_id')");

            if ($insertGuru) {
                header("Location: admin_guru.php?msg=berhasil");
                exit;
            } else {
                $pesan = "Gagal menyimpan data guru: " . mysqli_error($koneksi);
            }
        } else {
            $pesan = "Gagal menyimpan data user: " . mysqli_error($koneksi);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Guru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
    <h3>➕ Tambah Data Guru</h3>

    <?php if (!empty($pesan)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($pesan); ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label>NIP</label>
            <input type="text" name="nip" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Nama</label>
            <input type="text" name="nama" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Tempat Lahir</label>
            <input type="text" name="tempat_lahir" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Tanggal Lahir</label>
            <input type="date" name="tanggal_lahir" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Jenis Kelamin</label>
            <select name="jenis_kelamin" class="form-select" required>
                <option value="L">Laki-laki</option>
                <option value="P">Perempuan</option>
            </select>
        </div>
        <div class="mb-3">
            <label>Alamat</label>
            <textarea name="alamat" class="form-control" required></textarea>
        </div>
        <div class="mb-3">
            <label>Mata Pelajaran</label>
            <select name="mapel_id" class="form-select" required>
                <option value="">-- Pilih Mapel --</option>
                <?php while ($m = mysqli_fetch_assoc($mapelResult)): ?>
                    <option value="<?= $m['id']; ?>"><?= $m['nama_mapel']; ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <button type="submit" name="simpan" class="btn btn-success">Simpan</button>
        <a href="admin_guru.php" class="btn btn-secondary">Kembali</a>
    </form>
</div>
</body>
</html>
