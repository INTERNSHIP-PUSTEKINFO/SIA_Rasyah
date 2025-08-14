<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: admin_absensi.php");
    exit;
}

$id = $_GET['id'];

// Ambil data absensi
$query_absen = "
    SELECT * FROM absensi WHERE id = '$id'
";
$result_absen = mysqli_query($koneksi, $query_absen);
$data = mysqli_fetch_assoc($result_absen);

if (!$data) {
    echo "Data tidak ditemukan.";
    exit;
}

// Ambil data siswa dan mapel
$siswa = mysqli_query($koneksi, "SELECT email, nama FROM user WHERE role = 'siswa'");
$mapel = mysqli_query($koneksi, "SELECT * FROM mapel");

$pesan = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $siswa_nis = $_POST['siswa_nis'];
    $mapel_id = $_POST['mapel_id'];
    $tanggal = $_POST['tanggal'];
    $keterangan = $_POST['keterangan'];

    if ($siswa_nis && $mapel_id && $tanggal && $keterangan) {
        $query_update = "
            UPDATE absensi 
            SET siswa_nis = '$siswa_nis', mapel_id = '$mapel_id', tanggal = '$tanggal', keterangan = '$keterangan'
            WHERE id = '$id'
        ";
        if (mysqli_query($koneksi, $query_update)) {
            header("Location: admin_absensi.php");
            exit;
        } else {
            $pesan = "Gagal mengubah data.";
        }
    } else {
        $pesan = "Harap lengkapi semua field.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Absensi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
    <h3>✏️ Edit Absensi</h3>
    <a href="admin_absensi.php" class="btn btn-secondary mb-3">⬅ Kembali</a>

    <?php if ($pesan): ?>
        <div class="alert alert-warning"><?= htmlspecialchars($pesan); ?></div>
    <?php endif; ?>

    <form method="POST" class="card p-4 bg-white shadow-sm">
        <div class="mb-3">
            <label>Nama Siswa</label>
            <select name="siswa_nis" class="form-select" required>
                <option value="">-- Pilih Siswa --</option>
                <?php while ($s = mysqli_fetch_assoc($siswa)): ?>
                    <option value="<?= $s['email'] ?>" <?= $s['email'] == $data['siswa_nis'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['nama']) ?> (<?= $s['email'] ?>)
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="mb-3">
            <label>Mata Pelajaran</label>
            <select name="mapel_id" class="form-select" required>
                <option value="">-- Pilih Mapel --</option>
                <?php while ($m = mysqli_fetch_assoc($mapel)): ?>
                    <option value="<?= $m['id'] ?>" <?= $m['id'] == $data['mapel_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($m['nama_mapel']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="mb-3">
            <label>Tanggal</label>
            <input type="date" name="tanggal" class="form-control" value="<?= $data['tanggal'] ?>" required>
        </div>
        <div class="mb-3">
            <label>Keterangan</label>
            <select name="keterangan" class="form-select" required>
                <?php
                $opsi = ['Hadir', 'Sakit', 'Izin', 'Alpa'];
                foreach ($opsi as $o):
                ?>
                    <option value="<?= $o ?>" <?= $o == $data['keterangan'] ? 'selected' : '' ?>><?= $o ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    </form>
</div>
</body>
</html>
