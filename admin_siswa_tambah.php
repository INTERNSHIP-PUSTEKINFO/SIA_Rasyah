<?php
session_start();
include 'koneksi.php';

// Cek apakah user adalah admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$kelasResult = mysqli_query($koneksi, "SELECT * FROM kelas ORDER BY nama_kelas ASC");
$jurusanResult = mysqli_query($koneksi, "SELECT * FROM jurusan ORDER BY nama_jurusan ASC");

$pesan = "";

// Proses saat form disubmit
if (isset($_POST['simpan'])) {
    $nama            = htmlspecialchars(trim($_POST['nama']));
    $email           = htmlspecialchars(trim($_POST['email']));
    $password        = $_POST['password']; // Password tidak di-sanitize sebelum hash
    $nis             = htmlspecialchars(trim($_POST['nis']));
    $kelas_id        = htmlspecialchars($_POST['kelas_id']);
    $jurusan_id      = htmlspecialchars($_POST['jurusan_id']);
    $tempat_lahir    = htmlspecialchars(trim($_POST['tempat_lahir']));
    $tanggal_lahir   = htmlspecialchars($_POST['tanggal_lahir']);
    $jenis_kelamin   = htmlspecialchars($_POST['jenis_kelamin']);
    $alamat          = htmlspecialchars(trim($_POST['alamat']));
    $tahun_masuk     = date('Y');
    $status          = 'aktif';

    $created_at = date('Y-m-d H:i:s');
    $updated_at = $created_at;

    // Cek apakah email atau NIS sudah digunakan
    $cekUser = mysqli_query($koneksi, "SELECT id FROM user WHERE email = '$email'");
    $cekSiswa = mysqli_query($koneksi, "SELECT nis FROM siswa WHERE nis = '$nis'");

    if (mysqli_num_rows($cekUser) > 0) {
        $pesan = "❌ Email sudah digunakan. Silakan gunakan email lain.";
    } elseif (mysqli_num_rows($cekSiswa) > 0) {
        $pesan = "❌ NIS sudah digunakan. Silakan gunakan NIS lain.";
    } else {
        // 1. Insert ke tabel user
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $insertUser = mysqli_query($koneksi, "INSERT INTO user
            (nama, email, password, role, created_at, updated_at)
            VALUES ('$nama','$email','$hashed_password','siswa','$created_at','$updated_at')");

        if ($insertUser) {
            $user_id = mysqli_insert_id($koneksi);

            // 2. Insert ke tabel siswa
            $insertSiswa = mysqli_query($koneksi, "INSERT INTO siswa
                (nis, user_id, tempat_lahir, tanggal_lahir, jenis_kelamin, alamat, jurusan_id, kelas_id, tahun_masuk, status)
                VALUES ('$nis', '$user_id', '$tempat_lahir', '$tanggal_lahir', '$jenis_kelamin', '$alamat', '$jurusan_id', '$kelas_id', '$tahun_masuk', '$status')");

            if ($insertSiswa) {
                header("Location: admin_siswa.php?msg=berhasil");
                exit;
            } else {
                $pesan = "❌ Gagal menyimpan data siswa: " . mysqli_error($koneksi);
            }
        } else {
            $pesan = "❌ Gagal menyimpan data user: " . mysqli_error($koneksi);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Siswa</title>
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
    <h3><i class="fas fa-user-plus me-2"></i>Tambah Siswa</h3>
    
    <?php if (!empty($pesan)): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($pesan); ?></div>
    <?php endif; ?>

    <div class="card">
        <form method="post">
            <div class="mb-3">
                <label for="nama" class="form-label">Nama</label>
                <input type="text" name="nama" id="nama" class="form-control" placeholder="Nama Lengkap" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="Email aktif" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="Password (min. 4 karakter)" required minlength="4">
            </div>
            <div class="mb-3">
                <label for="nis" class="form-label">NIS</label>
                <input type="text" name="nis" id="nis" class="form-control" placeholder="Nomor Induk Siswa" required>
            </div>
            <div class="mb-3">
                <label for="tempat_lahir" class="form-label">Tempat Lahir</label>
                <input type="text" name="tempat_lahir" id="tempat_lahir" class="form-control" placeholder="Contoh: Jakarta" required>
            </div>
            <div class="mb-3">
                <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir" id="tanggal_lahir" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                <select name="jenis_kelamin" id="jenis_kelamin" class="form-select" required>
                    <option value="">-- Pilih --</option>
                    <option value="L">Laki-laki</option>
                    <option value="P">Perempuan</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="alamat" class="form-label">Alamat</label>
                <textarea name="alamat" id="alamat" class="form-control" placeholder="Alamat lengkap" required></textarea>
            </div>
            <div class="mb-3">
                <label for="kelas_id" class="form-label">Kelas</label>
                <select name="kelas_id" id="kelas_id" class="form-select" required>
                    <option value="">-- Pilih Kelas --</option>
                    <?php while ($k = mysqli_fetch_assoc($kelasResult)): ?>
                        <option value="<?= htmlspecialchars($k['id']); ?>"><?= htmlspecialchars($k['nama_kelas']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="jurusan_id" class="form-label">Jurusan</label>
                <select name="jurusan_id" id="jurusan_id" class="form-select" required>
                    <option value="">-- Pilih Jurusan --</option>
                    <?php while ($j = mysqli_fetch_assoc($jurusanResult)): ?>
                        <option value="<?= htmlspecialchars($j['id']); ?>"><?= htmlspecialchars($j['nama_jurusan']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="d-flex">
                <button type="submit" name="simpan" class="btn btn-success me-2"><i class="fas fa-save me-1"></i> Simpan</button>
                <a href="admin_siswa.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
